<?php

namespace App\Http\Controllers\Api;

use App\Contracts\PaymentGatewayContract;
use App\Http\Controllers\Controller;
use App\Http\Requests\PaymentInitiateRequest;
use App\Models\Course;
use App\Models\Payment;
use App\Models\PaymentGatewayConfig;
use App\Models\Plan;
use App\Models\User;
use App\Services\EasebuzzService;
use App\Services\PayGlocalService;
use App\Services\PaymentFulfillmentService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Log;

class PaymentController extends Controller
{
    public function __construct(
        private readonly EasebuzzService $easebuzz,
        private readonly PayGlocalService $payglocal,
        private readonly PaymentFulfillmentService $fulfillment,
    ) {}

    public function checkout(Request $request): JsonResponse
    {
        $request->validate(['course_id' => 'required|integer|exists:courses,id']);
        $course = Course::findOrFail($request->course_id);

        return response()->json([
            'success' => true,
            'data' => [
                'course' => [
                    'id' => $course->id,
                    'title' => $course->title,
                    'price_max' => (float) $course->price_max,
                ],
            ],
        ]);
    }

    public function initiate(PaymentInitiateRequest $request): JsonResponse
    {
        Log::info('=== PAYMENT INITIATE STARTED ===', [
            'ip' => $request->ip(),
            'payload' => $this->redactForLogging($request->validated()),
        ]);

        $course = Course::findOrFail($request->course_id);

        // Resolve selected plans
        $planIds = $request->input('plan_ids', []);
        $plans = collect();
        if (!empty($planIds)) {
            $plans = Plan::whereIn('id', $planIds)->where('course_id', $course->id)->get();
            if ($plans->count() !== count($planIds)) {
                return response()->json([
                    'success' => false,
                    'message' => 'One or more selected plans are invalid.',
                ], 400);
            }
        }

        // Check if user already exists
        $existingUser = User::where('email', $request->buyer_email)->first();

        // Check if user already enrolled in this course
        $alreadyEnrolled = false;
        if ($existingUser) {
            $alreadyEnrolled = $existingUser->courses()->where('courses.id', $course->id)->exists();
        }

        if ($alreadyEnrolled) {
            Log::info('PAYMENT: User already enrolled in this course', [
                'user_id' => $existingUser->id,
                'email' => $this->maskValue($existingUser->email),
                'course_id' => $course->id,
            ]);

            return response()->json([
                'success' => false,
                'message' => 'You are already enrolled in this course. You can access it from your dashboard.',
                'already_enrolled' => true,
                'data' => [
                    'course_id' => $course->id,
                    'course_title' => $course->title,
                ],
            ], 409);
        }

        // Flow-level idempotency: a leftover pending attempt for the same buyer+course
        // (abandoned tab, retried request, double submit) must not stay live alongside
        // a fresh one — supersede it so at most one payment is ever "pending" per
        // buyer+course. If that old attempt is later completed anyway, the fulfillment
        // service still catches it via the duplicate-purchase check and flags it for review.
        $staleAttempts = Payment::where('buyer_email', $request->buyer_email)
            ->where('course_id', $course->id)
            ->where('status', Payment::STATUS_PENDING)
            ->get();

        foreach ($staleAttempts as $stale) {
            $stale->update([
                'status' => Payment::STATUS_EXPIRED,
                'gateway_response' => array_merge($stale->gateway_response ?? [], [
                    'superseded_at' => now()->toIso8601String(),
                    'superseded_reason' => 'A new checkout attempt was started for the same course before this one completed.',
                ]),
            ]);

            Log::info('PAYMENT: Superseded stale pending attempt', [
                'old_payment_id' => $stale->id,
                'old_txnid' => $stale->txnid,
                'course_id' => $course->id,
            ]);
        }

        // Determine which gateway is active — admin controls this, frontend never knows.
        $activeGatewayConfig = PaymentGatewayConfig::where('is_active', true)->first();

        if (!$activeGatewayConfig) {
            Log::error('PAYMENT: No payment gateway is active');

            return response()->json([
                'success' => false,
                'message' => 'No payment gateway is active. Please contact support.',
            ], 503);
        }

        $gatewayName = $activeGatewayConfig->gateway;
        $gatewayService = $this->resolveGatewayService($gatewayName);

        // Ensure payment gateway is configured — no demo/bypass mode allowed
        if (!$gatewayService->isConfigured()) {
            Log::error('PAYMENT: Gateway not configured. Cannot process payments.', [
                'gateway' => $gatewayName,
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Payment gateway is not configured. Please contact support.',
            ], 503);
        }

        $txnid = $gatewayService->generateTxnId();

        // PayGlocal supports multi-currency, defaulting to USD when the frontend didn't
        // send one. Easebuzz in this codebase only ever settles INR — force INR
        // unconditionally regardless of what the frontend sent (or didn't).
        $currencyCode = $gatewayName === PaymentGatewayConfig::GATEWAY_PAYGLOCAL
            ? ($request->currency_code ?? 'USD')
            : 'INR';

        // Live rates refreshed daily by currencies:refresh-rates; static config is only
        // the fallback for a cold cache or a failed refresh.
        $rates = Cache::get('currency_rates', config('currencies.rates'));
        $amountUsd = (float) $course->price_max;
        $amount = round($amountUsd * ($rates[$currencyCode] ?? $rates['INR']), 2);

        Log::info('PAYMENT: Price calculated', [
            'course_id' => $course->id,
            'course_title' => $course->title,
            'currency' => $currencyCode,
            'amount' => $amount,
        ]);

        $payment = Payment::create([
            'txnid' => $txnid,
            'course_id' => $course->id,
            'buyer_name' => $request->buyer_name,
            'buyer_email' => $request->buyer_email,
            'buyer_phone' => $request->buyer_phone,
            'amount' => $amount,
            'currency' => $currencyCode,
            'gateway' => $gatewayName,
            'status' => Payment::STATUS_PENDING,
            'gateway_response' => !empty($planIds) ? ['plan_ids' => $planIds] : null,
        ]);

        Log::info('PAYMENT: DB record created', [
            'payment_id' => $payment->id,
            'txnid' => $txnid,
            'gateway' => $gatewayName,
            'status' => Payment::STATUS_PENDING,
        ]);

        $initiateResult = $gatewayName === PaymentGatewayConfig::GATEWAY_PAYGLOCAL
            ? $this->initiatePayGlocal($payment, $course, $request, $amount, $currencyCode)
            : $this->initiateEasebuzz($payment, $course, $request, $amount);

        if (!$initiateResult['success']) {
            Log::error('PAYMENT: Gateway initiate API failed', [
                'gateway' => $gatewayName,
                'txnid' => $txnid,
                'error' => $initiateResult['error'],
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Failed to initiate payment with gateway: ' . $initiateResult['error'],
            ], 500);
        }

        Log::info('=== PAYMENT INITIATE COMPLETED ===', [
            'gateway' => $gatewayName,
            'txnid' => $txnid,
            'currency' => $currencyCode,
            'amount' => $amount,
            'redirect_url' => $initiateResult['redirect_url'],
            'course' => $course->title,
            'customer_email' => $this->maskValue($request->buyer_email),
        ]);

        return response()->json([
            'success' => true,
            'data' => [
                'txnid' => $txnid,
                'amount' => (string) $amount,
                'currency' => $currencyCode,
                'productinfo' => $course->title,
                'redirect_url' => $initiateResult['redirect_url'],
                'course_title' => $course->title,
            ],
        ]);
    }

    /**
     * @return array{success: bool, redirect_url?: string, error?: string}
     */
    private function initiateEasebuzz(Payment $payment, Course $course, PaymentInitiateRequest $request, float $amount): array
    {
        $productinfo = preg_replace('/[^a-zA-Z0-9\s\-|]/', '', $course->title);
        $productinfo = trim(substr($productinfo, 0, 45));

        $params = [
            'txnid' => $payment->txnid,
            'amount' => number_format($amount, 2, '.', ''),
            'productinfo' => $productinfo,
            'firstname' => $request->buyer_name,
            'email' => $request->buyer_email,
            'phone' => $request->buyer_phone,
            'surl' => config('app.payment_success_url'),
            'furl' => config('app.payment_failure_url'),
            'udf1' => (string) $course->id,
            'udf2' => $payment->txnid,
            'udf3' => '',
            'udf4' => '',
            'udf5' => '',
            'udf6' => '',
            'udf7' => '',
            'udf8' => '',
            'udf9' => '',
            'udf10' => '',
        ];

        return $this->easebuzz->initiatePayment($params);
    }

    /**
     * @return array{success: bool, redirect_url?: string, error?: string}
     */
    private function initiatePayGlocal(Payment $payment, Course $course, PaymentInitiateRequest $request, float $amount, string $currencyCode): array
    {
        $result = $this->payglocal->initiatePayment([
            'txnid' => $payment->txnid,
            'amount' => number_format($amount, 2, '.', ''),
            'currency' => $currencyCode,
            'firstname' => $request->buyer_name,
            'email' => $request->buyer_email,
            'callback_url' => config('app.payglocal_callback_url'),
        ]);

        if ($result['success'] && !empty($result['gid'])) {
            $payment->update([
                'payment_id' => $result['gid'],
                'gateway_response' => array_merge($payment->gateway_response ?? [], [
                    'gid' => $result['gid'],
                    'status_url' => $result['status_url'] ?? null,
                ]),
            ]);
        }

        return $result;
    }

    public function success(Request $request): RedirectResponse
    {
        return $this->handleGatewayCallback(PaymentGatewayConfig::GATEWAY_EASEBUZZ, $request, asRedirect: true);
    }

    public function failure(Request $request): RedirectResponse
    {
        return $this->handleGatewayFailure(PaymentGatewayConfig::GATEWAY_EASEBUZZ, $request);
    }

    public function webhook(Request $request): JsonResponse
    {
        return $this->handleGatewayCallback(PaymentGatewayConfig::GATEWAY_EASEBUZZ, $request, asRedirect: false);
    }

    public function payglocalSuccess(Request $request): RedirectResponse
    {
        return $this->handleGatewayCallback(PaymentGatewayConfig::GATEWAY_PAYGLOCAL, $request, asRedirect: true);
    }

    public function payglocalFailure(Request $request): RedirectResponse
    {
        return $this->handleGatewayFailure(PaymentGatewayConfig::GATEWAY_PAYGLOCAL, $request);
    }

    public function payglocalWebhook(Request $request): JsonResponse
    {
        return $this->handleGatewayCallback(PaymentGatewayConfig::GATEWAY_PAYGLOCAL, $request, asRedirect: false);
    }

    /**
     * Resolve a gateway name to its service. Adding gateway #3: implement
     * PaymentGatewayContract, inject it in the constructor, add one case here.
     * Nothing below this method needs to change.
     */
    private function resolveGatewayService(string $gatewayName): PaymentGatewayContract
    {
        return match ($gatewayName) {
            PaymentGatewayConfig::GATEWAY_PAYGLOCAL => $this->payglocal,
            PaymentGatewayConfig::GATEWAY_EASEBUZZ => $this->easebuzz,
            default => abort(503, 'Unsupported payment gateway.'),
        };
    }

    /**
     * The single verification pipeline every gateway's success/webhook callback runs
     * through: atomic race-condition claim, authenticity check, authoritative
     * server-to-server re-verification (never trust callback data alone),
     * environment-aware blocking, amount-tamper check, then fulfillment. Gateway
     * differences are confined to PaymentGatewayContract implementations — this
     * method itself never needs to change when a gateway is added.
     */
    private function handleGatewayCallback(string $gatewayName, Request $request, bool $asRedirect): RedirectResponse|JsonResponse
    {
        $service = $this->resolveGatewayService($gatewayName);
        $label = strtoupper($gatewayName);

        Log::info("=== PAYMENT CALLBACK RECEIVED ($label) ===", ['ip' => $request->ip()]);

        $parsed = $service->parseCallback($request);
        $txnid = $parsed['txnid'];

        if (!$txnid) {
            Log::error("PAYMENT CALLBACK ($label): Missing txnid");
            return $this->respond($asRedirect, 'failed', null, 'Missing transaction ID', 400, 'Invalid request');
        }

        $payment = Payment::where('txnid', $txnid)->first();

        if (!$payment) {
            Log::error("PAYMENT CALLBACK ($label): Payment not found", ['txnid' => $txnid]);
            return $this->respond($asRedirect, 'failed', $txnid, 'Transaction not found', 404, 'Transaction not found');
        }

        // Atomic check/update to prevent race condition. FAILED/EXPIRED are reclaimable
        // too — authenticity/verification still runs fresh below, so a transient earlier
        // failure (e.g. a flaky status-check API call) doesn't permanently strand a real
        // payment. Authenticity is checked *after* this claim, mirroring each gateway's
        // original order, so the claim itself never depends on trusting the callback yet.
        $updated = Payment::where('txnid', $txnid)
            ->whereIn('status', [Payment::STATUS_PENDING, Payment::STATUS_FAILED, Payment::STATUS_EXPIRED])
            ->update([
                'status' => Payment::STATUS_PROCESSING,
                'payment_id' => $parsed['payment_id'],
                'gateway_response' => array_merge(
                    $payment->gateway_response ?? [],
                    [
                        'callback_data' => $parsed['data'],
                        'callback_received_at' => now()->toIso8601String(),
                    ]
                ),
            ]);

        if (!$updated) {
            // Another request (webhook or a concurrent redirect hit) is already handling
            // this txnid. Rather than trusting a possibly mid-flight snapshot, wait briefly
            // for it to reach a terminal state so we don't tell a paying customer it failed.
            Log::info("PAYMENT CALLBACK ($label): Already processed (race condition handled)", ['txnid' => $txnid]);
            $currentStatus = $this->awaitTerminalStatus($payment);
            $redirectStatus = match ($currentStatus) {
                Payment::STATUS_PAID => 'success',
                // Still processing after the wait — the concurrent request left it
                // pending (e.g. PayGlocal INPROGRESS), not failed.
                Payment::STATUS_PROCESSING => 'pending',
                default => 'failed',
            };
            return $asRedirect
                ? $this->redirectToFrontend($redirectStatus, $txnid)
                : response()->json(['status' => $redirectStatus === 'pending' ? 'pending' : 'ok', 'message' => 'Already processed']);
        }

        // Reload — the atomic claim above wrote callback_data into gateway_response
        // via a raw query, which this in-memory model instance doesn't have yet.
        $payment->refresh();

        if (!$service->verifyCallbackAuthenticity($parsed['data'], $request)) {
            Log::warning("PAYMENT CALLBACK ($label): Authenticity verification failed", ['txnid' => $txnid]);

            Payment::where('txnid', $txnid)->update([
                'status' => Payment::STATUS_FAILED,
                'gateway_response' => array_merge($payment->gateway_response ?? [], ['error' => 'Callback authenticity verification failed']),
            ]);

            return $this->respond($asRedirect, 'failed', $txnid, 'Security verification failed', 400, 'Security verification failed');
        }

        // PayGlocal's verification identifier is the full statusUrl stored at initiate time
        // (it carries a token PayGlocal itself signs, which can't be rebuilt from a bare gid).
        // Other gateways fall through to their own identifier/payment_id/txnid chain unaffected.
        $identifier = $payment->gateway_response['status_url']
            ?? $parsed['identifier']
            ?: $payment->payment_id
            ?: $txnid;

        // Never trust the callback's own claimed status — always re-verify server-to-server.
        $verification = $service->verifyTransaction($identifier);

        Log::info("PAYMENT CALLBACK ($label): Transaction verification", [
            'txnid' => $txnid,
            'identifier' => $identifier,
            'is_success' => $verification['success'],
            'is_pending' => $verification['pending'],
        ]);

        // A genuinely in-between state (e.g. PayGlocal's INPROGRESS, still settling
        // asynchronously) is not a failure — don't fail it, don't fulfill it either.
        // Leave the payment in 'processing' (already set by the atomic claim above) for
        // later reconciliation via webhook or the payments:expire-stale sweep.
        if ($verification['pending']) {
            Log::info("PAYMENT CALLBACK ($label): Transaction still pending — leaving for later reconciliation", [
                'txnid' => $txnid,
                'verification' => $verification,
            ]);

            Payment::where('txnid', $txnid)->update([
                'gateway_response' => array_merge(
                    $payment->gateway_response ?? [],
                    ['pending_checked_at' => now()->toIso8601String()]
                ),
            ]);

            return $asRedirect
                ? $this->redirectToFrontend('pending', $txnid, 'Your payment is still being confirmed. You will receive an email once it completes.')
                : response()->json(['status' => 'pending']);
        }

        // Environment-aware transaction verification
        // In production: hard-block if verification fails (real money involved)
        // In test: log a warning but allow (sandbox status APIs are often unreliable)
        if (!$verification['success']) {
            if ($service->isProduction()) {
                Log::warning("PAYMENT CALLBACK ($label): Transaction verification failed — blocking enrollment", [
                    'txnid' => $txnid,
                    'verification' => $verification,
                ]);

                Payment::where('txnid', $txnid)->update([
                    'status' => Payment::STATUS_FAILED,
                    'gateway_response' => array_merge(
                        $payment->gateway_response ?? [],
                        ['error' => "Transaction verification failed with $gatewayName"]
                    ),
                ]);

                return $this->respond($asRedirect, 'failed', $txnid, 'Transaction could not be verified. Please contact support.', 400, 'Transaction verification failed');
            }

            Log::warning("PAYMENT CALLBACK ($label): Transaction verification failed in TEST mode — allowing enrollment", [
                'txnid' => $txnid,
                'verification' => $verification,
            ]);
        }

        // Amount validation — prevent tampering. Skipped only if the gateway's
        // verification response didn't surface an amount at all.
        if ($verification['amount'] !== null) {
            $gatewayAmount = $verification['amount'];
            $ourAmount = number_format((float) $payment->amount, 2, '.', '');

            if (abs((float) $gatewayAmount - (float) $ourAmount) > 0.01) {
                Log::warning("PAYMENT CALLBACK ($label): Amount mismatch", [
                    'txnid' => $txnid,
                    'gateway_amount' => $gatewayAmount,
                    'our_amount' => $ourAmount,
                ]);

                Payment::where('txnid', $txnid)->update([
                    'status' => Payment::STATUS_FAILED,
                    'gateway_response' => array_merge(
                        $payment->gateway_response ?? [],
                        ['error' => 'Amount mismatch: expected ' . $ourAmount . ' but got ' . $gatewayAmount]
                    ),
                ]);

                return $this->respond($asRedirect, 'failed', $txnid, 'Payment amount validation failed. Please contact support.', 400, 'Amount mismatch');
            }
        }

        try {
            $this->fulfillment->fulfill($payment);

            Log::info("=== PAYMENT CALLBACK COMPLETED ($label) ===", ['txnid' => $txnid]);

            return $asRedirect
                ? $this->redirectToFrontend('success', $txnid)
                : response()->json(['status' => 'ok']);

        } catch (\Exception $e) {
            Log::error("PAYMENT CALLBACK ($label): Fulfillment error", [
                'txnid' => $txnid,
                'error' => $e->getMessage(),
            ]);

            return $this->respond($asRedirect, 'failed', $txnid, 'An error occurred while processing your payment. Please contact support.', 500, 'Internal server error');
        }
    }

    /**
     * Marks a pending payment failed based on an explicit failure callback. Works for any
     * gateway since it only needs a txnid, which every gateway sends the same way on failure
     * (either as `txnid` or, for PayGlocal, `merchantTxnId`).
     */
    private function handleGatewayFailure(string $gatewayName, Request $request): RedirectResponse
    {
        $label = strtoupper($gatewayName);

        Log::info("=== PAYMENT FAILURE CALLBACK RECEIVED ($label) ===", [
            'all_params' => $this->redactForLogging($request->all()),
            'ip' => $request->ip(),
        ]);

        $txnid = $request->input('txnid', $request->input('merchantTxnId'));

        if ($txnid) {
            $payment = Payment::where('txnid', $txnid)->first();

            if ($payment && $payment->isPending()) {
                $payment->update([
                    'status' => Payment::STATUS_FAILED,
                    'gateway_response' => array_merge(
                        $payment->gateway_response ?? [],
                        [
                            'failure_callback' => $request->all(),
                            'failed_at' => now()->toIso8601String(),
                        ]
                    ),
                ]);

                Log::info("PAYMENT FAILURE ($label): Payment marked as failed", [
                    'payment_id' => $payment->id,
                    'txnid' => $txnid,
                ]);
            }
        }

        return $this->redirectToFrontend('failed', $txnid);
    }

    /**
     * Return either a frontend redirect or a JSON response depending on which
     * transport this callback came in on (browser redirect vs. server webhook).
     */
    private function respond(bool $asRedirect, string $redirectStatus, ?string $txnid, string $redirectMessage, int $jsonStatus, string $jsonError): RedirectResponse|JsonResponse
    {
        return $asRedirect
            ? $this->redirectToFrontend($redirectStatus, $txnid, $redirectMessage)
            : response()->json(['error' => $jsonError], $jsonStatus);
    }

    private function redirectToFrontend(string $status, ?string $txnid = null, ?string $message = null): RedirectResponse
    {
        $frontendUrl = config('app.frontend_url', 'http://localhost:5173');
        $url = $frontendUrl . '/payment/' . $status;

        $params = [];
        if ($txnid) {
            $params['txnid'] = $txnid;
        }
        if ($message) {
            // http_build_query() already URL-encodes every value — encoding $message
            // here too would double-encode it (e.g. "a b" -> "a+b" -> "a%2Bb").
            $params['message'] = $message;
        }

        if (!empty($params)) {
            $url .= '?' . http_build_query($params);
        }

        Log::info('Redirecting to frontend', ['url' => $url]);

        return redirect()->away($url);
    }

    /**
     * Recursively mask PII fields (name/email/phone) in a payload before it hits the logs.
     * Values are stored raw in the DB (gateway_response) for reconciliation; only log output is redacted.
     */
    private function redactForLogging(array $data): array
    {
        $sensitiveKeys = ['name', 'firstname', 'buyer_name', 'email', 'buyer_email', 'phone', 'buyer_phone'];

        foreach ($data as $key => $value) {
            if (is_array($value)) {
                $data[$key] = $this->redactForLogging($value);
                continue;
            }

            if (in_array(strtolower((string) $key), $sensitiveKeys, true)) {
                $data[$key] = $this->maskValue((string) $value);
            }
        }

        return $data;
    }

    /**
     * Mask a string, keeping only the first and last character (e.g. "j***e@example.com" -> "j***m").
     */
    private function maskValue(?string $value): string
    {
        $value = (string) $value;
        $length = strlen($value);

        if ($length <= 2) {
            return str_repeat('*', $length);
        }

        return $value[0] . str_repeat('*', $length - 2) . $value[$length - 1];
    }

    /**
     * Poll briefly for a payment to leave 'processing'. Used when this request lost the
     * atomic claim to a concurrent webhook/surl hit — the winner is still mid-flight
     * (gateway verification + DB write + email), so an immediate read of a 'processing'
     * snapshot would wrongly report failure to a customer who was actually just charged.
     */
    private function awaitTerminalStatus(Payment $payment, int $maxWaitMs = 6000, int $intervalMs = 300): string
    {
        $status = $payment->fresh()->status;
        $waited = 0;

        while ($status === Payment::STATUS_PROCESSING && $waited < $maxWaitMs) {
            usleep($intervalMs * 1000);
            $waited += $intervalMs;
            $status = $payment->fresh()->status;
        }

        return $status;
    }
}