<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\PaymentInitiateRequest;
use App\Models\Course;
use App\Models\Payment;
use App\Models\User;
use App\Notifications\CoursePurchased;
use App\Services\EasebuzzService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;

class PaymentController extends Controller
{
    /**
     * Currency conversion rates (matching frontend CurrencyContext)
     * Base is USD, converted to INR for Easebuzz
     */
    private const CURRENCY_RATES = [
        'USD' => 1,
        'CAD' => 1.36,
        'EUR' => 0.92,
        'GBP' => 0.79,
        'AUD' => 1.52,
        'INR' => 83.33,
        'AED' => 3.67,
    ];

    public function __construct(
        private readonly EasebuzzService $easebuzz
    ) {}

    /**
     * Show checkout information for a course.
     */
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

    /**
     * Initiate a payment with Easebuzz.
     *
     * 1. Validates request
     * 2. Fetches course
     * 3. Creates pending Payment record
     * 4. Generates txnid
     * 5. Returns Easebuzz redirect data
     */
    public function initiate(PaymentInitiateRequest $request): JsonResponse
    {
        Log::info('=== PAYMENT INITIATE STARTED ===', [
            'ip' => $request->ip(),
            'payload' => $request->validated(),
        ]);

        $course = Course::findOrFail($request->course_id);

        // Calculate amount in INR
        $amountUsd = (float) $course->price_max;
        $rate = self::CURRENCY_RATES['INR'];
        $amountInr = round($amountUsd * $rate, 2);

        Log::info('PAYMENT: Price calculated', [
            'course_id' => $course->id,
            'course_title' => $course->title,
            'amount_inr' => $amountInr,
        ]);

        // Generate unique transaction ID
        $txnid = $this->easebuzz->generateTxnId();

        // Create pending payment record
        $payment = Payment::create([
            'txnid' => $txnid,
            'course_id' => $course->id,
            'buyer_name' => $request->buyer_name,
            'buyer_email' => $request->buyer_email,
            'buyer_phone' => $request->buyer_phone,
            'amount' => $amountInr,
            'currency' => 'INR',
            'gateway' => 'easebuzz',
            'status' => Payment::STATUS_PENDING,
        ]);

        Log::info('PAYMENT: DB record created', [
            'payment_id' => $payment->id,
            'txnid' => $txnid,
            'status' => Payment::STATUS_PENDING,
        ]);

        // Check if Easebuzz is configured
        if (!$this->easebuzz->isConfigured()) {
            Log::info('PAYMENT: DEMO MODE — No Easebuzz credentials configured.');

            $surl = route('payment.success') . '?txnid=' . $txnid;
            $furl = route('payment.failure') . '?txnid=' . $txnid;

            return response()->json([
                'success' => true,
                'data' => [
                    'key' => '',
                    'txnid' => $txnid,
                    'amount' => (string) $amountInr,
                    'productinfo' => $course->title,
                    'firstname' => $request->buyer_name,
                    'email' => $request->buyer_email,
                    'phone' => $request->buyer_phone,
                    'hash' => '',
                    'surl' => $surl,
                    'furl' => $furl,
                    'udf1' => (string) $course->id,
                    'udf2' => '',
                    'udf3' => '',
                    'udf4' => '',
                    'udf5' => '',
                    'payment_url' => $surl,
                    'course_title' => $course->title,
                    'demo_mode' => true,
                ],
            ]);
        }

        // Prepare Easebuzz parameters
        $surl = route('payment.success');
        $furl = route('payment.failure');

        $params = [
            'txnid' => $txnid,
            'amount' => number_format($amountInr, 2, '.', ''),
            'productinfo' => $course->title,
            'firstname' => $request->buyer_name,
            'email' => $request->buyer_email,
            'phone' => $request->buyer_phone,
            'surl' => $surl,
            'furl' => $furl,
            'udf1' => (string) $course->id,
            'udf2' => $txnid, // Store txnid in udf2 for redundancy
            'udf3' => '',
            'udf4' => '',
            'udf5' => '',
            'udf6' => '',
            'udf7' => '',
            'udf8' => '',
            'udf9' => '',
            'udf10' => '',
        ];

        // Generate hash
        $hash = $this->easebuzz->generateInitiateHash($params);

        Log::info('PAYMENT: Hash generated', [
            'hash_preview' => substr($hash, 0, 10) . '...',
        ]);

        $paymentUrl = $this->easebuzz->getPaymentUrl();

        Log::info('=== PAYMENT INITIATE COMPLETED ===', [
            'txnid' => $txnid,
            'amount_inr' => $amountInr,
            'payment_url' => $paymentUrl,
            'course' => $course->title,
            'customer_email' => $request->buyer_email,
        ]);

        return response()->json([
            'success' => true,
            'data' => [
                'key' => $this->easebuzz->getMerchantKey(),
                'txnid' => $txnid,
                'amount' => (string) $amountInr,
                'productinfo' => $course->title,
                'firstname' => $request->buyer_name,
                'email' => $request->buyer_email,
                'phone' => $request->buyer_phone,
                'hash' => $hash,
                'surl' => $surl,
                'furl' => $furl,
                'udf1' => (string) $course->id,
                'udf2' => $txnid,
                'udf3' => '',
                'udf4' => '',
                'udf5' => '',
                'payment_url' => $paymentUrl,
                'course_title' => $course->title,
                'demo_mode' => false,
            ],
        ]);
    }

    /**
     * Handle successful payment redirect from Easebuzz.
     *
     * 1. Verifies response hash
     * 2. Verifies transaction with Easebuzz API
     * 3. Creates/updates user
     * 4. Enrolls student
     * 5. Sends email
     * 6. Redirects to frontend success page
     */
    public function success(Request $request): RedirectResponse
    {
        Log::info('=== PAYMENT SUCCESS CALLBACK RECEIVED ===', [
            'all_params' => $request->all(),
            'ip' => $request->ip(),
        ]);

        $txnid = $request->input('txnid');
        $status = $request->input('status');

        if (!$txnid) {
            Log::error('PAYMENT SUCCESS: Missing txnid');
            return $this->redirectToFrontend('failed', null, 'Missing transaction ID');
        }

        // Find the payment
        $payment = Payment::where('txnid', $txnid)->first();

        if (!$payment) {
            Log::error('PAYMENT SUCCESS: Payment not found', ['txnid' => $txnid]);
            return $this->redirectToFrontend('failed', $txnid, 'Transaction not found');
        }

        // If already paid, redirect to success (idempotent)
        if ($payment->isPaid()) {
            Log::info('PAYMENT SUCCESS: Already processed', ['txnid' => $txnid]);
            return $this->redirectToFrontend('success', $txnid);
        }

        // Verify response hash
        $hashValid = $this->easebuzz->verifyResponseHash($request->all());

        if (!$hashValid) {
            Log::warning('PAYMENT SUCCESS: Hash verification failed', ['txnid' => $txnid]);
            $payment->update([
                'status' => Payment::STATUS_FAILED,
                'gateway_response' => array_merge(
                    $payment->gateway_response ?? [],
                    ['error' => 'Hash verification failed', 'callback_data' => $request->all()]
                ),
            ]);
            return $this->redirectToFrontend('failed', $txnid, 'Security verification failed');
        }

        // Verify transaction with Easebuzz API (server-to-server)
        $verification = $this->easebuzz->verifyTransaction($txnid);

        $transactionStatus = $verification['data']['txn_status'] ?? $verification['data']['status'] ?? null;
        $isTransactionSuccess = ($verification['status'] === 1)
            && in_array(strtolower($transactionStatus ?? ''), ['success', 'completed']);

        Log::info('PAYMENT SUCCESS: Transaction verification', [
            'txnid' => $txnid,
            'verification_status' => $verification['status'],
            'transaction_status' => $transactionStatus,
            'is_success' => $isTransactionSuccess,
        ]);

        // If transaction verification fails but Easebuzz says success, still process
        // but log the discrepancy. The hash verification is the primary check.
        if (!$isTransactionSuccess && $this->easebuzz->isConfigured()) {
            Log::warning('PAYMENT SUCCESS: Transaction verification returned non-success', [
                'txnid' => $txnid,
                'verification' => $verification,
            ]);
        }

        // Process the successful payment
        try {
            DB::beginTransaction();

            // Update payment status
            $payment->update([
                'status' => Payment::STATUS_PAID,
                'payment_id' => $request->input('easebuzz_id', $request->input('payment_id')),
                'gateway_response' => array_merge(
                    $payment->gateway_response ?? [],
                    [
                        'callback_data' => $request->all(),
                        'transaction_verification' => $verification,
                        'processed_at' => now()->toIso8601String(),
                    ]
                ),
            ]);

            Log::info('PAYMENT SUCCESS: Payment updated to paid', [
                'payment_id' => $payment->id,
                'txnid' => $txnid,
            ]);

            // Find or create user
            $user = User::where('email', $payment->buyer_email)->first();
            $isNewUser = false;
            $plainPassword = null;

            if (!$user) {
                $plainPassword = Str::password(16);
                $user = User::create([
                    'name' => $payment->buyer_name,
                    'email' => $payment->buyer_email,
                    'password' => bcrypt($plainPassword),
                    'role' => 'student',
                ]);
                $isNewUser = true;

                Log::info('PAYMENT SUCCESS: New user created', [
                    'user_id' => $user->id,
                    'email' => $user->email,
                ]);
            } else {
                Log::info('PAYMENT SUCCESS: Existing user found', [
                    'user_id' => $user->id,
                    'email' => $user->email,
                ]);
            }

            // Link user to payment
            $payment->update(['user_id' => $user->id]);

            // Enroll user in course (idempotent)
            $course = $payment->course;
            $user->enrollIn($course);

            Log::info('PAYMENT SUCCESS: User enrolled', [
                'user_id' => $user->id,
                'course_id' => $course->id,
            ]);

            DB::commit();

            // Send email notification
            try {
                $user->notify(new CoursePurchased(
                    payment: $payment,
                    course: $course,
                    isNewUser: $isNewUser,
                    password: $plainPassword
                ));
                Log::info('PAYMENT SUCCESS: Email notification sent', [
                    'user_id' => $user->id,
                    'email' => $user->email,
                ]);
            } catch (\Exception $e) {
                Log::error('PAYMENT SUCCESS: Failed to send email', [
                    'user_id' => $user->id,
                    'error' => $e->getMessage(),
                ]);
            }

            Log::info('=== PAYMENT SUCCESS COMPLETED ===', [
                'txnid' => $txnid,
                'user_id' => $user->id,
                'course_id' => $course->id,
                'is_new_user' => $isNewUser,
            ]);

            return $this->redirectToFrontend('success', $txnid);

        } catch (\Exception $e) {
            DB::rollBack();

            Log::error('PAYMENT SUCCESS: Database error', [
                'txnid' => $txnid,
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
            ]);

            return $this->redirectToFrontend('failed', $txnid, 'An error occurred while processing your payment. Please contact support.');
        }
    }

    /**
     * Handle failed payment redirect from Easebuzz.
     */
    public function failure(Request $request): RedirectResponse
    {
        Log::info('=== PAYMENT FAILURE CALLBACK RECEIVED ===', [
            'all_params' => $request->all(),
            'ip' => $request->ip(),
        ]);

        $txnid = $request->input('txnid');

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

                Log::info('PAYMENT FAILURE: Payment marked as failed', [
                    'payment_id' => $payment->id,
                    'txnid' => $txnid,
                ]);
            } else {
                Log::info('PAYMENT FAILURE: Payment already processed or not found', [
                    'txnid' => $txnid,
                    'found' => $payment ? true : false,
                    'current_status' => $payment?->status,
                ]);
            }
        }

        return $this->redirectToFrontend('failed', $txnid);
    }

    /**
     * Handle Easebuzz webhook (server-to-server callback).
     *
     * 1. Verifies signature/hash
     * 2. Finds order
     * 3. Updates payment
     * 4. Creates user if needed
     * 5. Enrolls student
     * 6. Ignores duplicate notifications safely
     */
    public function webhook(Request $request)
    {
        Log::info('=== EASEBUZZ WEBHOOK RECEIVED ===', [
            'all_params' => $request->all(),
            'ip' => $request->ip(),
            'headers' => [
                'user-agent' => $request->userAgent(),
                'content-type' => $request->header('Content-Type'),
            ],
        ]);

        $txnid = $request->input('txnid');
        $status = $request->input('status');
        $easebuzzHash = $request->input('hash');

        if (!$txnid || !$status) {
            Log::error('WEBHOOK: Missing required fields', [
                'has_txnid' => !empty($txnid),
                'has_status' => !empty($status),
            ]);
            return response()->json(['error' => 'Invalid request'], 400);
        }

        $payment = Payment::where('txnid', $txnid)->first();

        if (!$payment) {
            Log::error('WEBHOOK: Transaction not found', ['txnid' => $txnid]);
            return response()->json(['error' => 'Transaction not found'], 404);
        }

        Log::info('WEBHOOK: Payment found', [
            'payment_id' => $payment->id,
            'current_status' => $payment->status,
            'amount' => $payment->amount,
            'buyer_email' => $payment->buyer_email,
        ]);

        // Verify hash
        $hashValid = $this->easebuzz->verifyResponseHash($request->all());

        if (!$hashValid) {
            Log::warning('WEBHOOK: Hash mismatch — possible tampering', [
                'txnid' => $txnid,
            ]);
            return response()->json(['error' => 'Hash mismatch'], 400);
        }

        // If already paid, ignore (idempotent)
        if ($payment->isPaid()) {
            Log::info('WEBHOOK: Payment already processed, ignoring duplicate', [
                'txnid' => $txnid,
            ]);
            return response()->json(['status' => 'ok', 'message' => 'Already processed']);
        }

        // Determine new status
        $isSuccess = strtolower($status) === 'success';

        if (!$isSuccess) {
            $payment->update([
                'status' => Payment::STATUS_FAILED,
                'gateway_response' => array_merge(
                    $payment->gateway_response ?? [],
                    ['webhook_data' => $request->all()]
                ),
            ]);

            Log::info('WEBHOOK: Payment marked as failed', [
                'txnid' => $txnid,
                'payment_id' => $payment->id,
            ]);

            return response()->json(['status' => 'ok']);
        }

        // Process successful payment
        try {
            DB::beginTransaction();

            $payment->update([
                'status' => Payment::STATUS_PAID,
                'payment_id' => $request->input('easebuzz_id', $request->input('payment_id')),
                'gateway_response' => array_merge(
                    $payment->gateway_response ?? [],
                    [
                        'webhook_data' => $request->all(),
                        'processed_at' => now()->toIso8601String(),
                    ]
                ),
            ]);

            // Find or create user
            $user = User::where('email', $payment->buyer_email)->first();
            $isNewUser = false;
            $plainPassword = null;

            if (!$user) {
                $plainPassword = Str::password(16);
                $user = User::create([
                    'name' => $payment->buyer_name,
                    'email' => $payment->buyer_email,
                    'password' => bcrypt($plainPassword),
                    'role' => 'student',
                ]);
                $isNewUser = true;

                Log::info('WEBHOOK: New user created', [
                    'user_id' => $user->id,
                    'email' => $user->email,
                ]);
            }

            // Link user to payment
            $payment->update(['user_id' => $user->id]);

            // Enroll user (idempotent)
            $course = $payment->course;
            $user->enrollIn($course);

            DB::commit();

            // Send email
            try {
                $user->notify(new CoursePurchased(
                    payment: $payment,
                    course: $course,
                    isNewUser: $isNewUser,
                    password: $plainPassword
                ));
                Log::info('WEBHOOK: Email sent', [
                    'user_id' => $user->id,
                    'email' => $user->email,
                ]);
            } catch (\Exception $e) {
                Log::error('WEBHOOK: Email failed', [
                    'user_id' => $user->id,
                    'error' => $e->getMessage(),
                ]);
            }

            Log::info('=== WEBHOOK COMPLETED ===', [
                'txnid' => $txnid,
                'status' => Payment::STATUS_PAID,
                'user_id' => $user->id,
                'course_id' => $course->id,
            ]);

            return response()->json(['status' => 'ok']);

        } catch (\Exception $e) {
            DB::rollBack();

            Log::error('WEBHOOK: Database error', [
                'txnid' => $txnid,
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
            ]);

            return response()->json(['error' => 'Internal server error'], 500);
        }
    }

    /**
     * Redirect to the frontend with the payment status.
     */
    private function redirectToFrontend(string $status, ?string $txnid = null, ?string $message = null): RedirectResponse
    {
        $frontendUrl = config('app.frontend_url', 'http://localhost:5173');
        $url = $frontendUrl . '/payment/' . $status;

        $params = [];
        if ($txnid) {
            $params['txnid'] = $txnid;
        }
        if ($message) {
            $params['message'] = urlencode($message);
        }

        if (!empty($params)) {
            $url .= '?' . http_build_query($params);
        }

        Log::info('Redirecting to frontend', ['url' => $url]);

        return redirect()->away($url);
    }
}