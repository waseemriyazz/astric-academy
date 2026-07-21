<?php

namespace App\Services;

use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;

class EasebuzzService
{
    private string $merchantKey;
    private string $salt;
    private string $env;

    public function __construct()
    {
        $this->merchantKey = config('easebuzz.key');
        $this->salt = config('easebuzz.salt');
        $this->env = config('easebuzz.env', 'test');
    }

    /**
     * Generate a unique transaction ID using UUID (hyphens removed for Easebuzz compatibility).
     * Easebuzz requires: a-zA-Z0-9_|\-\/ only, max 40 chars.
     */
    public function generateTxnId(): string
    {
        return 'ASTRX_' . str_replace('-', '', (string) Str::uuid());
    }

    /**
     * Get the Easebuzz payment page URL (hosted checkout).
     */
    public function getPaymentUrl(): string
    {
        return $this->env === 'prod'
            ? 'https://pay.easebuzz.in/pay/'
            : 'https://testpay.easebuzz.in/pay/';
    }

    /**
     * Get the API base URL for transaction verification.
     */
    public function getApiBaseUrl(): string
    {
        return $this->env === 'prod'
            ? 'https://dashboard.easebuzz.in/'
            : 'https://testdashboard.easebuzz.in/';
    }

    /**
     * Generate SHA-512 hash for initiate payment API.
     *
     * Hash sequence: key|txnid|amount|productinfo|firstname|email|udf1|udf2|udf3|udf4|udf5|udf6|udf7|udf8|udf9|udf10|salt
     */
    public function generateInitiateHash(array $params): string
    {
        $hashString = $this->merchantKey . '|'
            . ($params['txnid'] ?? '') . '|'
            . ($params['amount'] ?? '') . '|'
            . ($params['productinfo'] ?? '') . '|'
            . ($params['firstname'] ?? '') . '|'
            . ($params['email'] ?? '') . '|'
            . ($params['udf1'] ?? '') . '|'
            . ($params['udf2'] ?? '') . '|'
            . ($params['udf3'] ?? '') . '|'
            . ($params['udf4'] ?? '') . '|'
            . ($params['udf5'] ?? '') . '|'
            . ($params['udf6'] ?? '') . '|'
            . ($params['udf7'] ?? '') . '|'
            . ($params['udf8'] ?? '') . '|'
            . ($params['udf9'] ?? '') . '|'
            . ($params['udf10'] ?? '') . '|'
            . $this->salt;

        return strtolower(hash('sha512', $hashString));
    }

    /**
     * Verify the response hash from Easebuzz callback.
     *
     * Reverse hash sequence: salt|status|udf10|udf9|udf8|udf7|udf6|udf5|udf4|udf3|udf2|udf1|email|firstname|productinfo|amount|txnid|key
     */
    public function verifyResponseHash(array $response): bool
    {
        $hashString = $this->salt . '|'
            . ($response['status'] ?? '') . '|'
            . ($response['udf10'] ?? '') . '|'
            . ($response['udf9'] ?? '') . '|'
            . ($response['udf8'] ?? '') . '|'
            . ($response['udf7'] ?? '') . '|'
            . ($response['udf6'] ?? '') . '|'
            . ($response['udf5'] ?? '') . '|'
            . ($response['udf4'] ?? '') . '|'
            . ($response['udf3'] ?? '') . '|'
            . ($response['udf2'] ?? '') . '|'
            . ($response['udf1'] ?? '') . '|'
            . ($response['email'] ?? '') . '|'
            . ($response['firstname'] ?? '') . '|'
            . ($response['productinfo'] ?? '') . '|'
            . ($response['amount'] ?? '') . '|'
            . ($response['txnid'] ?? '') . '|'
            . $this->merchantKey;

        $expectedHash = strtolower(hash('sha512', $hashString));
        $providedHash = strtolower($response['hash'] ?? '');

        $isValid = hash_equals($expectedHash, $providedHash);

        Log::debug('Easebuzz: Response hash verification', [
            'txnid' => $response['txnid'] ?? 'N/A',
            'expected_hash' => substr($expectedHash, 0, 10) . '...',
            'provided_hash' => substr($providedHash, 0, 10) . '...',
            'is_valid' => $isValid,
        ]);

        return $isValid;
    }

    /**
     * Verify transaction status with Easebuzz using the Transaction API.
     *
     * Hash sequence: key|txnid|salt
     *
     * @return array{status: int, data: mixed}
     */
    public function verifyTransaction(string $txnid): array
    {
        $hashString = $this->merchantKey . '|' . $txnid . '|' . $this->salt;
        $hash = strtolower(hash('sha512', $hashString));

        $params = [
            'key' => $this->merchantKey,
            'txnid' => $txnid,
            'hash' => $hash,
        ];

        $url = $this->getApiBaseUrl() . 'transaction/v2/retrieve';

        Log::info('Easebuzz: Verifying transaction', ['txnid' => $txnid, 'url' => $url]);

        try {
            $ch = curl_init();
            curl_setopt_array($ch, [
                CURLOPT_URL => $url,
                CURLOPT_POST => true,
                CURLOPT_POSTFIELDS => http_build_query($params),
                CURLOPT_RETURNTRANSFER => true,
                CURLOPT_SSL_VERIFYHOST => 2,
                CURLOPT_SSL_VERIFYPEER => true,
                CURLOPT_TIMEOUT => 30,
                CURLOPT_CONNECTTIMEOUT => 10,
                CURLOPT_HTTPHEADER => ['Content-Type: application/x-www-form-urlencoded'],
            ]);

            $result = curl_exec($ch);
            $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);

            if (curl_errno($ch)) {
                $error = curl_error($ch);
                curl_close($ch);
                Log::error('Easebuzz: Transaction verification cURL error', [
                    'txnid' => $txnid,
                    'error' => $error,
                ]);
                return ['status' => 0, 'data' => 'cURL error: ' . $error];
            }

            curl_close($ch);

            if ($result === false || trim($result) === '') {
                Log::error('Easebuzz: Empty response from transaction API', ['txnid' => $txnid]);
                return ['status' => 0, 'data' => 'Empty response from Easebuzz'];
            }

            $decoded = json_decode($result, true);

            if ($decoded === null) {
                Log::error('Easebuzz: Invalid JSON from transaction API', [
                    'txnid' => $txnid,
                    'response' => substr($result, 0, 500),
                ]);
                return ['status' => 0, 'data' => 'Invalid JSON response'];
            }

            Log::info('Easebuzz: Transaction verification response', [
                'txnid' => $txnid,
                'http_code' => $httpCode,
                'response_status' => $decoded['status'] ?? 'unknown',
                'transaction_status' => $decoded['data']['txn_status'] ?? $decoded['data']['status'] ?? 'unknown',
            ]);

            return [
                'status' => (int) ($decoded['status'] ?? 0),
                'data' => $decoded['data'] ?? $decoded,
            ];

        } catch (\Exception $e) {
            Log::error('Easebuzz: Transaction verification exception', [
                'txnid' => $txnid,
                'error' => $e->getMessage(),
            ]);
            return ['status' => 0, 'data' => 'Exception: ' . $e->getMessage()];
        }
    }

    /**
     * Initiate a payment with Easebuzz API and get the access_key.
     *
     * @param array $params Payment parameters (txnid, amount, productinfo, firstname, email, phone, surl, furl, etc.)
     * @return array{success: bool, access_key?: string, redirect_url?: string, error?: string}
     */
    public function initiatePayment(array $params): array
    {
        // Include the Easebuzz library
        require_once __DIR__ . '/../Lib/Easebuzz/utils.php';

        // Call the Easebuzz initiate API
        $result = _callInitiatePaymentAPI($params, $this->merchantKey, $this->salt, $this->env);

        if ($result['status'] !== 1) {
            $error = is_object($result['data']) ? json_encode($result['data']) : (string) $result['data'];
            Log::error('Easebuzz: Initiate payment API failed', [
                'error' => $error,
                'txnid' => $params['txnid'] ?? 'N/A',
            ]);
            return ['success' => false, 'error' => $error];
        }

        $accessKey = $result['data'];

        // Validate access_key format — must be exactly 64 hex chars
        if (empty($accessKey) || !preg_match('/^[a-f0-9]{64}$/', $accessKey)) {
            Log::error('Easebuzz: Invalid access key received', [
                'access_key' => substr($accessKey, 0, 10) . '...',
                'txnid' => $params['txnid'] ?? 'N/A',
            ]);
            return ['success' => false, 'error' => 'Invalid access key received from Easebuzz'];
        }

        // Build redirect URL
        $redirectUrl = $this->getPaymentUrl() . $accessKey;

        Log::info('Easebuzz: Payment initiated successfully', [
            'txnid' => $params['txnid'] ?? 'N/A',
            'access_key' => substr($accessKey, 0, 10) . '...',
            'redirect_url' => $redirectUrl,
        ]);

        return [
            'success' => true,
            'access_key' => $accessKey,
            'redirect_url' => $redirectUrl,
        ];
    }

    /**
     * Check if Easebuzz credentials are configured.
     */
    public function isConfigured(): bool
    {
        return !empty($this->merchantKey) && !empty($this->salt);
    }

    /**
     * Check if running in production mode.
     */
    public function isProduction(): bool
    {
        return $this->env === 'prod';
    }

    /**
     * Get the merchant key.
     */
    public function getMerchantKey(): string
    {
        return $this->merchantKey;
    }
}