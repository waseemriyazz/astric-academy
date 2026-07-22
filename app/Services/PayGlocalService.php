<?php

namespace App\Services;

use App\Contracts\PaymentGatewayContract;
use App\Models\PaymentGatewayConfig;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;
use phpseclib3\Crypt\PublicKeyLoader;
use phpseclib3\Crypt\RSA;

class PayGlocalService implements PaymentGatewayContract
{
    private ?string $merchantId;
    private ?string $privateKid;
    private ?string $publicKid;
    private ?string $privateKey;
    private ?string $publicKey;
    private bool $isProductionMode;

    public function __construct()
    {
        $config = PaymentGatewayConfig::where('gateway', PaymentGatewayConfig::GATEWAY_PAYGLOCAL)->first();

        $this->merchantId = $config?->credential('merchant_id');
        // Private KID identifies our signing keypair to PayGlocal — goes in x-gl-kid and the
        // JWS 'kid' claim on every outgoing request. Public KID is PayGlocal's own reference
        // for their public key (used to verify their callback signatures); not currently sent
        // on any of our requests, kept here for completeness/record-keeping alongside it.
        $this->privateKid = $config?->credential('private_kid');
        $this->publicKid = $config?->credential('public_kid');
        $this->privateKey = $config?->credential('private_key');
        $this->publicKey = $config?->credential('public_key');
        $this->isProductionMode = (bool) $config?->is_production;
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
        return !empty($this->merchantId) && !empty($this->privateKid) && !empty($this->privateKey);
    }

    public function isProduction(): bool
    {
        return $this->isProductionMode;
    }

    private function getBaseUrl(): string
    {
        return $this->isProductionMode
            ? 'https://api.payglocal.in'
            : 'https://api.uat.payglocal.in';
    }

    /**
     * Build the digest-based JWS token PayGlocal expects in x-gl-token-external.
     * The payload segment carries a SHA-256 digest of the request body, not the body itself.
     */
    private function buildJwsToken(array $body): string
    {
        $header = [
            'alg' => 'RS256',
            'kid' => $this->privateKid,
            'iss' => $this->merchantId,
            'x-gl-enc' => 'false',
            'is-digested' => 'true',
        ];

        $digest = hash('sha256', json_encode($body), true);

        $headerEncoded = $this->base64UrlEncode(json_encode($header));
        $payloadEncoded = $this->base64UrlEncode($digest);
        $signingInput = $headerEncoded . '.' . $payloadEncoded;

        $rsa = PublicKeyLoader::load($this->privateKey)
            ->withPadding(RSA::SIGNATURE_PKCS1)
            ->withHash('sha256');

        $signature = $rsa->sign($signingInput);

        return $signingInput . '.' . $this->base64UrlEncode($signature);
    }

    /**
     * Initiate a PayCollect (hosted checkout) payment.
     *
     * @return array{success: bool, gid?: string, redirect_url?: string, status_url?: string, error?: string}
     */
    public function initiatePayment(array $params): array
    {
        $body = [
            'merchantTxnId' => $params['txnid'],
            'paymentData' => [
                'totalAmount' => $params['amount'],
                'txnCurrency' => $params['currency'] ?? 'INR',
                'billingData' => [
                    'firstName' => $params['firstname'],
                    'lastName' => $params['lastname'] ?? '.',
                    'emailId' => $params['email'],
                    'addressCountry' => 'IND',
                ],
            ],
            'merchantCallbackURL' => $params['callback_url'],
        ];

        try {
            $token = $this->buildJwsToken($body);
        } catch (\Throwable $e) {
            Log::error('PayGlocal: Failed to build JWS token for initiate', [
                'txnid' => $params['txnid'] ?? 'N/A',
                'error' => $e->getMessage(),
            ]);
            return ['success' => false, 'error' => 'Failed to sign request: ' . $e->getMessage()];
        }

        $url = $this->getBaseUrl() . '/gl/v1/payments/initiate/paycollect';

        // Full diagnostic snapshot of exactly what's being sent, minus the private key/raw
        // signature — everything here is safe to hand to PayGlocal support for a definitive
        // server-side reason behind a 401, instead of guessing further client-side.
        $tokenParts = explode('.', $token);
        $decodedHeader = json_decode($this->base64UrlDecode($tokenParts[0] ?? ''), true);

        Log::info('PayGlocal: Outgoing request diagnostics', [
            'txnid' => $params['txnid'],
            'url' => $url,
            'jws_header_claims' => $decodedHeader,
            'request_headers' => [
                'x-gl-merchantid' => $this->merchantId,
                'x-gl-kid' => $this->privateKid,
                'Content-Type' => 'application/json',
            ],
            'request_body' => $body,
            'jws_token_length' => strlen($token),
            'jws_token_segments' => count($tokenParts),
        ]);

        $response = $this->request('POST', $url, $token, $body);

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
     * @return array{success: bool, amount: ?string, raw: mixed}
     */
    public function verifyTransaction(string $identifier): array
    {
        $gid = $identifier;
        $url = $this->getBaseUrl() . '/gl/v1/payments/' . $gid . '/status';

        try {
            $token = $this->buildJwsToken([]);
        } catch (\Throwable $e) {
            Log::error('PayGlocal: Failed to build JWS token for status check', [
                'gid' => $gid,
                'error' => $e->getMessage(),
            ]);
            return ['success' => false, 'amount' => null, 'raw' => 'Failed to sign request: ' . $e->getMessage()];
        }

        Log::info('PayGlocal: Verifying transaction', ['gid' => $gid, 'url' => $url]);

        $response = $this->request('GET', $url, $token);

        if ($response === null) {
            return ['success' => false, 'amount' => null, 'raw' => 'Failed to reach PayGlocal'];
        }

        [$httpCode, $decoded] = $response;

        if ($httpCode < 200 || $httpCode >= 300 || $decoded === null) {
            Log::error('PayGlocal: Transaction verification failed', [
                'gid' => $gid,
                'http_code' => $httpCode,
                'response' => $decoded,
            ]);
            return ['success' => false, 'amount' => null, 'raw' => $decoded ?? 'Invalid response from PayGlocal'];
        }

        $data = $decoded['data'] ?? $decoded;
        $status = $data['status'] ?? null;
        $isSuccess = in_array(strtolower((string) $status), ['success', 'captured', 'completed'], true);

        Log::info('PayGlocal: Transaction verification response', [
            'gid' => $gid,
            'status' => $status ?? 'unknown',
            'is_success' => $isSuccess,
        ]);

        return [
            'success' => $isSuccess,
            'amount' => $data['paymentData']['totalAmount'] ?? null,
            'raw' => $data,
        ];
    }

    /**
     * Cheaply pull {txnid, identifier, data} out of the callback POST body — no
     * authenticity check yet. The actual callback data lives in the JSON body;
     * verifyCallbackAuthenticity() runs after the atomic claim to confirm it's genuine.
     *
     * @return array{txnid: ?string, identifier: ?string, payment_id: ?string, data: array}
     */
    public function parseCallback(Request $request): array
    {
        $payload = json_decode($request->getContent(), true);

        if (!is_array($payload)) {
            return ['txnid' => null, 'identifier' => null, 'payment_id' => null, 'data' => []];
        }

        $gid = $payload['gid'] ?? null;

        return [
            'txnid' => $payload['merchantTxnId'] ?? null,
            'identifier' => $gid,
            'payment_id' => $gid,
            'data' => $payload,
        ];
    }

    /**
     * Verify the digest-based JWS token PayGlocal sends in the x-gl-token-external header.
     * The token's payload segment is a SHA-256 digest of the raw POST body, not the body
     * itself — this confirms $data (already parsed by parseCallback()) wasn't tampered with
     * in transit, then verifies the RSA signature when a PayGlocal public key is configured.
     */
    public function verifyCallbackAuthenticity(array $data, Request $request): bool
    {
        $token = $request->header('x-gl-token-external');

        if (!$token) {
            Log::warning('PayGlocal: Missing x-gl-token-external header on callback');
            return false;
        }

        $parts = explode('.', $token);

        if (count($parts) !== 3) {
            Log::warning('PayGlocal: Malformed callback JWS token');
            return false;
        }

        [$headerEncoded, $payloadEncoded, $signatureEncoded] = $parts;

        $rawBody = $request->getContent();
        $expectedDigest = hash('sha256', $rawBody, true);
        $providedDigest = $this->base64UrlDecode($payloadEncoded);

        if (!hash_equals($expectedDigest, $providedDigest)) {
            Log::warning('PayGlocal: Callback body digest mismatch — possible tampering', [
                'merchant_txn_id' => $data['merchantTxnId'] ?? null,
            ]);
            return false;
        }

        if (empty($this->publicKey)) {
            Log::warning('PayGlocal: No public key configured — callback signature not verified, relying on status API for authoritative outcome');
            return true;
        }

        $signature = $this->base64UrlDecode($signatureEncoded);
        $signingInput = $headerEncoded . '.' . $payloadEncoded;

        try {
            $rsa = PublicKeyLoader::load($this->publicKey)
                ->withPadding(RSA::SIGNATURE_PKCS1)
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

    private function requestHeaders(string $token): array
    {
        return [
            'Content-Type: application/json',
            'x-gl-token-external: ' . $token,
            'x-gl-merchantid: ' . $this->merchantId,
            'x-gl-kid: ' . $this->privateKid,
        ];
    }

    /**
     * @return array{0: int, 1: mixed}|null [httpCode, decodedBody]
     */
    private function request(string $method, string $url, string $token, ?array $body = null): ?array
    {
        $ch = curl_init();

        $options = [
            CURLOPT_URL => $url,
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_HEADER => true,
            CURLOPT_SSL_VERIFYHOST => 2,
            CURLOPT_SSL_VERIFYPEER => true,
            CURLOPT_TIMEOUT => 30,
            CURLOPT_CONNECTTIMEOUT => 10,
            CURLOPT_HTTPHEADER => $this->requestHeaders($token),
        ];

        if ($method === 'POST') {
            $options[CURLOPT_POST] = true;
            $options[CURLOPT_POSTFIELDS] = json_encode($body ?? []);
        } else {
            $options[CURLOPT_HTTPGET] = true;
        }

        curl_setopt_array($ch, $options);

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

    private function base64UrlEncode(string $data): string
    {
        return rtrim(strtr(base64_encode($data), '+/', '-_'), '=');
    }

    private function base64UrlDecode(string $data): string
    {
        $padded = str_pad($data, strlen($data) + (4 - strlen($data) % 4) % 4, '=');

        return base64_decode(strtr($padded, '-_', '+/'));
    }
}
