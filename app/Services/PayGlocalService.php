<?php

namespace App\Services;

use App\Contracts\PaymentGatewayContract;
use App\Models\PaymentGatewayConfig;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;
use Jose\Component\Core\AlgorithmManager;
use Jose\Component\Encryption\Algorithm\ContentEncryption\A128CBCHS256;
use Jose\Component\Encryption\Algorithm\KeyEncryption\RSAOAEP256;
use Jose\Component\Encryption\JWEBuilder;
use Jose\Component\Encryption\Serializer\CompactSerializer as JweCompactSerializer;
use Jose\Component\KeyManagement\JWKFactory;
use Jose\Component\Signature\Algorithm\RS256;
use Jose\Component\Signature\JWSBuilder;
use Jose\Component\Signature\Serializer\CompactSerializer as JwsCompactSerializer;
use phpseclib3\Crypt\PublicKeyLoader;
use phpseclib3\Crypt\RSA as PhpseclibRSA;

/**
 * PayGlocal PayCollect requires two stacked JOSE operations, per PayGlocal's official
 * PHP SDK (github.com/PayGlocal-Technologies/payglocal-php-sdk):
 *
 *  1. The payment payload is encrypted into a JWE using PayGlocal's PUBLIC key
 *     (RSA-OAEP-256 key wrapping + A128CBC-HS256 content encryption).
 *  2. That JWE's SHA-256 digest is wrapped in a small JSON payload and signed (JWS,
 *     RS256) with OUR private key. The JWE token is the raw request body; the JWS is
 *     sent separately in the x-gl-token-external header.
 *
 * Earlier versions of this service only did step 2, directly over the plaintext body —
 * every request was rejected with a generic 401 because PayGlocal was never receiving
 * an encrypted body at all.
 */
class PayGlocalService implements PaymentGatewayContract
{
    private ?string $merchantId = null;
    private ?string $privateKid = null;
    private ?string $publicKid = null;
    private ?string $privateKey = null;
    private ?string $publicKey = null;
    private bool $isProductionMode = false;
    private bool $loaded = false;

    /**
     * Deliberately does NOT touch the database here. Laravel's console kernel
     * auto-discovers every command in app/Console/Commands on every artisan
     * invocation — including `migrate` and `serve` — and has to instantiate each one
     * through the container just to register it, regardless of which command is
     * actually being run. Several commands type-hint this service in their
     * constructor, so an eager DB query here means `php artisan migrate` (running
     * for the first time, before payment_gateway_configs exists yet) crashes trying
     * to query a table it hasn't created yet — a chicken-and-egg failure that blocks
     * bootstrapping the app at all on a fresh install. Credentials load lazily on
     * first actual use instead.
     */
    public function __construct()
    {
    }

    private function ensureLoaded(): void
    {
        if ($this->loaded) {
            return;
        }

        $config = PaymentGatewayConfig::where('gateway', PaymentGatewayConfig::GATEWAY_PAYGLOCAL)->first();

        $this->merchantId = $config?->credential('merchant_id');
        // Private KID/key are ours — sign the outer JWS. Public KID/key belong to PayGlocal
        // and encrypt the inner JWE; both pairs are required for every outgoing request.
        $this->privateKid = $config?->credential('private_kid');
        $this->publicKid = $config?->credential('public_kid');
        $this->privateKey = $config?->credential('private_key');
        $this->publicKey = $config?->credential('public_key');
        $this->isProductionMode = (bool) $config?->is_production;
        $this->loaded = true;
    }

    /**
     * Generate a unique merchant transaction ID.
     */
    public function generateTxnId(): string
    {
        return 'ACAD_' . str_replace('-', '', (string) Str::uuid());
    }

    public function isConfigured(): bool
    {
        $this->ensureLoaded();

        return !empty($this->merchantId)
            && !empty($this->privateKid)
            && !empty($this->privateKey)
            && !empty($this->publicKid)
            && !empty($this->publicKey);
    }

    public function isProduction(): bool
    {
        $this->ensureLoaded();

        return $this->isProductionMode;
    }

    private function getBaseUrl(): string
    {
        $this->ensureLoaded();

        return $this->isProductionMode
            ? 'https://api.payglocal.in'
            : 'https://api.uat.payglocal.in';
    }

    /**
     * Encrypt $payload into a JWE compact token using PayGlocal's public key.
     */
    private function buildJwe(array $payload): string
    {
        $this->ensureLoaded();

        $algorithmManager = new AlgorithmManager([
            new RSAOAEP256(),
            new A128CBCHS256(),
        ]);

        $jweBuilder = new JWEBuilder($algorithmManager);

        $encryptionKey = JWKFactory::createFromKey($this->publicKey, null, [
            'kid' => $this->publicKid,
            'use' => 'enc',
            'alg' => 'RSA-OAEP-256',
        ]);

        $header = [
            'issued-by' => $this->merchantId,
            'enc' => 'A128CBC-HS256',
            'exp' => 30000,
            'iat' => (string) round(microtime(true) * 1000),
            'alg' => 'RSA-OAEP-256',
            'kid' => $this->publicKid,
        ];

        $jwe = $jweBuilder
            ->create()
            ->withPayload(json_encode($payload))
            ->withSharedProtectedHeader($header)
            ->addRecipient($encryptionKey)
            ->build();

        return (new JweCompactSerializer())->serialize($jwe, 0);
    }

    /**
     * Sign a SHA-256 digest of $jweToken with our private key. The JWS payload is a small
     * JSON envelope ({digest, digestAlgorithm, exp, iat}), not the digest bytes directly.
     */
    private function buildJws(string $jweToken): string
    {
        $this->ensureLoaded();

        $algorithmManager = new AlgorithmManager([new RS256()]);
        $jwsBuilder = new JWSBuilder($algorithmManager);

        $signingKey = JWKFactory::createFromKey($this->privateKey, null, [
            'kid' => $this->privateKid,
            'use' => 'sig',
        ]);

        $header = [
            'issued-by' => $this->merchantId,
            'is-digested' => 'true',
            'alg' => 'RS256',
            'x-gl-enc' => 'true',
            'x-gl-merchantId' => $this->merchantId,
            'kid' => $this->privateKid,
        ];

        $payload = json_encode([
            'digest' => base64_encode(hash('sha256', $jweToken, true)),
            'digestAlgorithm' => 'SHA-256',
            'exp' => 300000,
            'iat' => (string) round(microtime(true) * 1000),
        ]);

        $jws = $jwsBuilder
            ->create()
            ->withPayload($payload)
            ->addSignature($signingKey, $header)
            ->build();

        return (new JwsCompactSerializer())->serialize($jws, 0);
    }

    /**
     * Initiate a PayCollect (hosted checkout) payment.
     *
     * @return array{success: bool, gid?: string, redirect_url?: string, status_url?: string, error?: string}
     */
    public function initiatePayment(array $params): array
    {
        $merchantUniqueId = substr(bin2hex(random_bytes(8)), 0, 16);

        $body = [
            'merchantTxnId' => $params['txnid'],
            'merchantUniqueId' => $merchantUniqueId,
            'paymentData' => [
                'totalAmount' => $params['amount'],
                'txnCurrency' => $params['currency'] ?? 'INR',
                'billingData' => [
                    'firstName' => $params['firstname'],
                    'lastName' => $params['lastname'] ?? '.',
                    'addressStreet1' => $params['address_street1'] ?? 'NA',
                    'addressStreet2' => $params['address_street2'] ?? 'NA',
                    'addressCity' => $params['address_city'] ?? 'NA',
                    'addressState' => $params['address_state'] ?? 'NA',
                    'addressPostalCode' => $params['address_postal_code'] ?? '000000',
                    'addressCountry' => 'IN',
                    'emailId' => $params['email'],
                ],
            ],
            'merchantCallbackURL' => $params['callback_url'],
        ];

        try {
            $jweToken = $this->buildJwe($body);
            $jwsToken = $this->buildJws($jweToken);
        } catch (\Throwable $e) {
            Log::error('PayGlocal: Failed to build JWE/JWS for initiate', [
                'txnid' => $params['txnid'] ?? 'N/A',
                'error' => $e->getMessage(),
            ]);
            return ['success' => false, 'error' => 'Failed to sign request: ' . $e->getMessage()];
        }

        $url = $this->getBaseUrl() . '/gl/v1/payments/initiate/paycollect';

        Log::info('PayGlocal: Outgoing request diagnostics', [
            'txnid' => $params['txnid'],
            'merchant_unique_id' => $merchantUniqueId,
            'url' => $url,
            'jwe_token_length' => strlen($jweToken),
            'jws_token_length' => strlen($jwsToken),
        ]);

        $response = $this->postJwe($url, $jweToken, $jwsToken);

        if ($response === null) {
            return ['success' => false, 'error' => 'Failed to reach PayGlocal'];
        }

        [$httpCode, $decoded] = $response;

        if ($httpCode < 200 || $httpCode >= 300 || empty($decoded['data']['redirectUrl'])) {
            Log::error('PayGlocal: Initiate payment API failed', [
                'txnid' => $params['txnid'],
                'http_code' => $httpCode,
                'response' => $decoded,
            ]);
            return [
                'success' => false,
                'error' => $decoded['message'] ?? 'PayGlocal initiate failed',
                'http_code' => $httpCode,
                'gid' => $decoded['gid'] ?? null,
                'reason_code' => $decoded['reasonCode'] ?? null,
            ];
        }

        Log::info('PayGlocal: Payment initiated successfully', [
            'txnid' => $params['txnid'],
            'gid' => $decoded['gid'] ?? null,
        ]);

        return [
            'success' => true,
            'gid' => $decoded['gid'] ?? null,
            'redirect_url' => $decoded['data']['redirectUrl'],
            'status_url' => $decoded['data']['statusUrl'] ?? null,
        ];
    }

    /**
     * Server-to-server status check — the source of truth for a transaction's outcome.
     * Callback data is convenient but never trusted on its own; this call is.
     *
     * $identifier must be the full statusUrl returned by initiatePayment() — it carries a
     * token PayGlocal itself signs, which we cannot reconstruct ourselves from a bare gid.
     *
     * @return array{success: bool, pending: bool, amount: ?string, raw: mixed}
     */
    public function verifyTransaction(string $identifier): array
    {
        if (!str_starts_with($identifier, 'http')) {
            Log::error('PayGlocal: verifyTransaction() needs the statusUrl from initiatePayment(), not a bare gid', [
                'identifier' => $identifier,
            ]);
            return ['success' => false, 'pending' => false, 'amount' => null, 'raw' => 'Missing status URL for verification'];
        }

        Log::info('PayGlocal: Verifying transaction', ['status_url' => $identifier]);

        $response = $this->getStatusUrl($identifier);

        if ($response === null) {
            return ['success' => false, 'pending' => false, 'amount' => null, 'raw' => 'Failed to reach PayGlocal'];
        }

        [$httpCode, $decoded] = $response;

        if ($httpCode < 200 || $httpCode >= 300 || $decoded === null) {
            Log::error('PayGlocal: Transaction verification failed', [
                'status_url' => $identifier,
                'http_code' => $httpCode,
                'response' => $decoded,
            ]);
            return ['success' => false, 'pending' => false, 'amount' => null, 'raw' => $decoded ?? 'Invalid response from PayGlocal'];
        }

        $data = $decoded['data'] ?? $decoded;
        $status = $data['status'] ?? null;
        $normalizedStatus = strtolower((string) $status);
        // sent_for_capture IS the success condition for PayCollect, per PayGlocal's own
        // reference implementation (response.php in their official SDK): the card is
        // authorized and the capture instruction has been dispatched — capture itself
        // completes automatically on their side and isn't something the merchant waits on.
        // Confirmed against real sandbox transactions, all of which reached exactly this
        // status and stayed there — this is the terminal state for a successful PayCollect
        // payment, not an intermediate one.
        $isSuccess = in_array($normalizedStatus, ['success', 'captured', 'completed', 'sent_for_capture'], true);
        // INPROGRESS is genuinely transient — still authenticating/authorizing (e.g. 3DS)
        // when the browser lands back on our callback URL. Not a failure, not resolved yet.
        $isPending = !$isSuccess && in_array($normalizedStatus, ['inprogress', 'pending', 'initiated'], true);

        Log::info('PayGlocal: Transaction verification response', [
            'status_url' => $identifier,
            'status' => $status ?? 'unknown',
            'is_success' => $isSuccess,
            'is_pending' => $isPending,
        ]);

        return [
            'success' => $isSuccess,
            'pending' => $isPending,
            'amount' => $data['Amount'] ?? $data['amount'] ?? null,
            'raw' => $data,
        ];
    }

    /**
     * Cheaply pull {txnid, identifier, data} out of the callback's x-gl-token form field —
     * no authenticity check yet; verifyCallbackAuthenticity() runs after the atomic claim.
     *
     * Confirmed against a real PayGlocal callback (captured via ngrok inspector): unlike our
     * own outgoing requests, PayGlocal POSTs this as an application/x-www-form-urlencoded
     * field named "x-gl-token" — not a header, not a JSON body. The token is NOT digest-
     * wrapped ("is-digested": "false") — its payload segment IS the callback data directly:
     * merchantTxnId, gid, status, Amount, statusUrl (a fresh ready-to-use status-check link),
     * merchantUniqueId, paymentMethod.
     *
     * @return array{txnid: ?string, identifier: ?string, payment_id: ?string, data: array}
     */
    public function parseCallback(Request $request): array
    {
        $token = $request->input('x-gl-token');

        if (!$token) {
            return ['txnid' => null, 'identifier' => null, 'payment_id' => null, 'data' => []];
        }

        $parts = explode('.', $token);

        if (count($parts) !== 3) {
            return ['txnid' => null, 'identifier' => null, 'payment_id' => null, 'data' => []];
        }

        $payload = json_decode($this->base64UrlDecode($parts[1]), true);

        if (!is_array($payload)) {
            return ['txnid' => null, 'identifier' => null, 'payment_id' => null, 'data' => []];
        }

        $gid = $payload['gid'] ?? null;

        return [
            'txnid' => $payload['merchantTxnId'] ?? null,
            'identifier' => $payload['statusUrl'] ?? $gid,
            'payment_id' => $gid,
            'data' => $payload,
        ];
    }

    /**
     * Verify the JWS token PayGlocal sends in the x-gl-token form field on callbacks.
     * Confirmed against a real callback: signed (RS256/PKCS1v1.5) with the private key
     * counterpart to the PayGlocal public key we hold — same keypair used to encrypt our
     * outgoing requests, reused by PayGlocal to sign their callbacks back to us.
     */
    public function verifyCallbackAuthenticity(array $data, Request $request): bool
    {
        $this->ensureLoaded();

        $token = $request->input('x-gl-token');

        if (!$token) {
            Log::warning('PayGlocal: Missing x-gl-token field on callback');
            return false;
        }

        $parts = explode('.', $token);

        if (count($parts) !== 3) {
            Log::warning('PayGlocal: Malformed callback JWS token');
            return false;
        }

        [$headerEncoded, $payloadEncoded, $signatureEncoded] = $parts;

        $header = json_decode($this->base64UrlDecode($headerEncoded), true);
        $isDigested = ($header['is-digested'] ?? null) === 'true';

        if ($isDigested) {
            $rawBody = $request->getContent();
            $expectedDigest = hash('sha256', $rawBody, true);
            $providedDigest = $this->base64UrlDecode($payloadEncoded);

            if (!hash_equals($expectedDigest, $providedDigest)) {
                Log::warning('PayGlocal: Callback body digest mismatch — possible tampering', [
                    'merchant_txn_id' => $data['merchantTxnId'] ?? null,
                ]);
                return false;
            }
        }

        if (empty($this->publicKey)) {
            Log::warning('PayGlocal: No public key configured — callback signature not verified, relying on status API for authoritative outcome');
            return true;
        }

        $signature = $this->base64UrlDecode($signatureEncoded);
        $signingInput = $headerEncoded . '.' . $payloadEncoded;

        try {
            $rsa = PublicKeyLoader::load($this->publicKey)
                ->withPadding(PhpseclibRSA::SIGNATURE_PKCS1)
                ->withHash('sha256');

            if (!$rsa->verify($signingInput, $signature)) {
                Log::warning('PayGlocal: Callback JWS signature verification failed', [
                    'merchant_txn_id' => $data['merchantTxnId'] ?? null,
                ]);
                return false;
            }
        } catch (\Throwable $e) {
            Log::warning('PayGlocal: Callback JWS signature verification threw', [
                'error' => $e->getMessage(),
            ]);
            return false;
        }

        return true;
    }

    /**
     * POST a JWE token as the raw request body, authenticated via the JWS in
     * x-gl-token-external. Content-Type is text/plain — the body is an opaque encrypted
     * string, not JSON.
     *
     * @return array{0: int, 1: mixed}|null [httpCode, decodedBody]
     */
    private function postJwe(string $url, string $jweToken, string $jwsToken): ?array
    {
        $ch = curl_init();

        curl_setopt_array($ch, [
            CURLOPT_URL => $url,
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_HEADER => true,
            CURLOPT_SSL_VERIFYHOST => 2,
            CURLOPT_SSL_VERIFYPEER => true,
            CURLOPT_TIMEOUT => 30,
            CURLOPT_CONNECTTIMEOUT => 10,
            CURLOPT_POST => true,
            CURLOPT_POSTFIELDS => $jweToken,
            CURLOPT_HTTPHEADER => [
                'x-gl-token-external: ' . $jwsToken,
                'Content-Type: text/plain',
            ],
        ]);

        return $this->execute($ch, $url);
    }

    /**
     * Plain GET on a PayGlocal-issued statusUrl — it carries its own signed token in the
     * query string, so no auth headers of ours are needed or expected.
     *
     * @return array{0: int, 1: mixed}|null [httpCode, decodedBody]
     */
    private function getStatusUrl(string $url): ?array
    {
        $ch = curl_init();

        curl_setopt_array($ch, [
            CURLOPT_URL => $url,
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_HEADER => true,
            CURLOPT_SSL_VERIFYHOST => 2,
            CURLOPT_SSL_VERIFYPEER => true,
            CURLOPT_TIMEOUT => 30,
            CURLOPT_CONNECTTIMEOUT => 10,
            CURLOPT_HTTPGET => true,
        ]);

        return $this->execute($ch, $url);
    }

    /**
     * @return array{0: int, 1: mixed}|null [httpCode, decodedBody]
     */
    private function execute(\CurlHandle $ch, string $url): ?array
    {
        $result = curl_exec($ch);

        if (curl_errno($ch)) {
            Log::error('PayGlocal: cURL error', ['error' => curl_error($ch), 'url' => $url]);
            curl_close($ch);
            return null;
        }

        $httpCode = (int) curl_getinfo($ch, CURLINFO_HTTP_CODE);
        $headerSize = (int) curl_getinfo($ch, CURLINFO_HEADER_SIZE);
        curl_close($ch);

        $rawHeaders = substr((string) $result, 0, $headerSize);
        $rawBody = substr((string) $result, $headerSize);

        $responseHeaders = [];
        foreach (explode("\r\n", trim($rawHeaders)) as $line) {
            if (str_contains($line, ':')) {
                [$name, $value] = explode(':', $line, 2);
                $responseHeaders[trim($name)] = trim($value);
            }
        }

        // Response headers often carry a trace/request ID PayGlocal support can search their
        // own logs by — worth surfacing even on success, cheap insurance for the next failure.
        Log::info('PayGlocal: Response headers', ['url' => $url, 'http_code' => $httpCode, 'headers' => $responseHeaders]);

        if ($rawBody === '' || trim($rawBody) === '') {
            Log::error('PayGlocal: Empty response', ['url' => $url]);
            return [$httpCode, null];
        }

        return [$httpCode, json_decode($rawBody, true)];
    }

    private function base64UrlDecode(string $data): string
    {
        $padded = str_pad($data, strlen($data) + (4 - strlen($data) % 4) % 4, '=');

        return base64_decode(strtr($padded, '-_', '+/'));
    }
}
