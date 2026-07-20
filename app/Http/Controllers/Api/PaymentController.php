<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\PaymentInitiateRequest;
use App\Models\Course;
use App\Models\Payment;
use App\Models\User;
use App\Notifications\CourseEnrolled;
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
    private const CURRENCY_RATES = [
        'USD' => 1,
        'CAD' => 1.36,
        'EUR' => 0.92,
        'GBP' => 0.79,
        'AUD' => 1.52,
        'INR' => 83.33,
        'AED' => 3.67,
    ];

    public function __construct(private readonly EasebuzzService $easebuzz) {}

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
            'payload' => $request->validated(),
        ]);

        $course = Course::findOrFail($request->course_id);
        $amountUsd = (float) $course->price_max;
        $rate = self::CURRENCY_RATES['INR'];
        $amountInr = round($amountUsd * $rate, 2);

        Log::info('PAYMENT: Price calculated', [
            'course_id' => $course->id,
            'course_title' => $course->title,
            'amount_inr' => $amountInr,
        ]);

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
                'email' => $existingUser->email,
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

        $txnid = $this->easebuzz->generateTxnId();

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

        $surl = config('app.payment_success_url');
        $furl = config('app.payment_failure_url');

        $productinfo = preg_replace('/[^a-zA-Z0-9\s\-|]/', '', $course->title);
        $productinfo = trim(substr($productinfo, 0, 45));

        $params = [
            'txnid' => $txnid,
            'amount' => number_format($amountInr, 2, '.', ''),
            'productinfo' => $productinfo,
            'firstname' => $request->buyer_name,
            'email' => $request->buyer_email,
            'phone' => $request->buyer_phone,
            'surl' => $surl,
            'furl' => $furl,
            'udf1' => (string) $course->id,
            'udf2' => $txnid,
            'udf3' => '',
            'udf4' => '',
            'udf5' => '',
            'udf6' => '',
            'udf7' => '',
            'udf8' => '',
            'udf9' => '',
            'udf10' => '',
        ];

        $initiateResult = $this->easebuzz->initiatePayment($params);

        if (!$initiateResult['success']) {
            Log::error('PAYMENT: Easebuzz initiate API failed', [
                'txnid' => $txnid,
                'error' => $initiateResult['error'],
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Failed to initiate payment with gateway: ' . $initiateResult['error'],
            ], 500);
        }

        Log::info('=== PAYMENT INITIATE COMPLETED ===', [
            'txnid' => $txnid,
            'amount_inr' => $amountInr,
            'redirect_url' => $initiateResult['redirect_url'],
            'course' => $course->title,
            'customer_email' => $request->buyer_email,
        ]);

        return response()->json([
            'success' => true,
            'data' => [
                'txnid' => $txnid,
                'amount' => (string) $amountInr,
                'productinfo' => $course->title,
                'redirect_url' => $initiateResult['redirect_url'],
                'course_title' => $course->title,
                'demo_mode' => false,
            ],
        ]);
    }

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

        $payment = Payment::where('txnid', $txnid)->first();

        if (!$payment) {
            Log::error('PAYMENT SUCCESS: Payment not found', ['txnid' => $txnid]);
            return $this->redirectToFrontend('failed', $txnid, 'Transaction not found');
        }

        // Atomic check/update to prevent race condition
        $updated = Payment::where('txnid', $txnid)
            ->where('status', Payment::STATUS_PENDING)
            ->update([
                'status' => Payment::STATUS_PROCESSING,
                'payment_id' => $request->input('easebuzz_id', $request->input('payment_id')),
                'gateway_response' => array_merge(
                    $payment->gateway_response ?? [],
                    [
                        'callback_data' => $request->all(),
                        'callback_received_at' => now()->toIso8601String(),
                    ]
                ),
            ]);

        if (!$updated) {
            // Already processed by another request
            Log::info('PAYMENT SUCCESS: Already processed (race condition handled)', ['txnid' => $txnid]);
            $currentStatus = $payment->fresh()->status;
            $redirectStatus = $currentStatus === Payment::STATUS_PAID ? 'success' : 'failed';
            return $this->redirectToFrontend($redirectStatus, $txnid);
        }

        // Verify response hash
        $hashValid = $this->easebuzz->verifyResponseHash($request->all());

        if (!$hashValid) {
            Log::warning('PAYMENT SUCCESS: Hash verification failed', ['txnid' => $txnid]);

            Payment::where('txnid', $txnid)->update([
                'status' => Payment::STATUS_FAILED,
                'gateway_response' => array_merge(
                    $payment->gateway_response ?? [],
                    ['error' => 'Hash verification failed']
                ),
            ]);

            return $this->redirectToFrontend('failed', $txnid, 'Security verification failed');
        }

        // Verify transaction with Easebuzz API
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

        if (!$isTransactionSuccess && $this->easebuzz->isConfigured()) {
            Log::warning('PAYMENT SUCCESS: Transaction verification returned non-success', [
                'txnid' => $txnid,
                'verification' => $verification,
            ]);
        }

        // Amount validation — prevent tampering
        $easebuzzAmount = $request->input('amount');
        $ourAmount = number_format((float) $payment->amount, 2, '.', '');

        if (abs((float) $easebuzzAmount - (float) $ourAmount) > 0.01) {
            Log::warning('PAYMENT SUCCESS: Amount mismatch', [
                'txnid' => $txnid,
                'easebuzz_amount' => $easebuzzAmount,
                'our_amount' => $ourAmount,
            ]);

            Payment::where('txnid', $txnid)->update([
                'status' => Payment::STATUS_FAILED,
                'gateway_response' => array_merge(
                    $payment->gateway_response ?? [],
                    ['error' => 'Amount mismatch: expected ' . $ourAmount . ' but got ' . $easebuzzAmount]
                ),
            ]);

            return $this->redirectToFrontend('failed', $txnid, 'Payment amount validation failed. Please contact support.');
        }

        // Process the successful payment
        try {
            DB::beginTransaction();

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

            $payment->update(['user_id' => $user->id]);

            $course = $payment->course;
            $user->enrollIn($course);

            DB::commit();

            // Send appropriate email based on user type
            try {
                if ($isNewUser) {
                    $user->notify(new CoursePurchased(
                        payment: $payment,
                        course: $course,
                        isNewUser: true,
                        password: $plainPassword
                    ));
                } else {
                    $user->notify(new CourseEnrolled(
                        payment: $payment,
                        course: $course
                    ));
                }

                Log::info('PAYMENT SUCCESS: Email notification sent', [
                    'user_id' => $user->id,
                    'email' => $user->email,
                    'is_new_user' => $isNewUser,
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
            }
        }

        return $this->redirectToFrontend('failed', $txnid);
    }

    public function webhook(Request $request)
    {
        Log::info('=== EASEBUZZ WEBHOOK RECEIVED ===', [
            'all_params' => $request->all(),
            'ip' => $request->ip(),
        ]);

        $txnid = $request->input('txnid');
        $status = $request->input('status');

        if (!$txnid || !$status) {
            return response()->json(['error' => 'Invalid request'], 400);
        }

        $payment = Payment::where('txnid', $txnid)->first();

        if (!$payment) {
            return response()->json(['error' => 'Transaction not found'], 404);
        }

        // Atomic check/update to prevent race condition
        $updated = Payment::where('txnid', $txnid)
            ->where('status', Payment::STATUS_PENDING)
            ->update([
                'status' => Payment::STATUS_PROCESSING,
                'gateway_response' => array_merge(
                    $payment->gateway_response ?? [],
                    [
                        'webhook_data' => $request->all(),
                        'webhook_received_at' => now()->toIso8601String(),
                    ]
                ),
            ]);

        if (!$updated) {
            Log::info('WEBHOOK: Already processed (race condition handled)', ['txnid' => $txnid]);
            return response()->json(['status' => 'ok', 'message' => 'Already processed']);
        }

        // Verify hash
        $hashValid = $this->easebuzz->verifyResponseHash($request->all());

        if (!$hashValid) {
            Log::warning('WEBHOOK: Hash mismatch', ['txnid' => $txnid]);
            Payment::where('txnid', $txnid)->update(['status' => Payment::STATUS_FAILED]);
            return response()->json(['error' => 'Hash mismatch'], 400);
        }

        $isSuccess = strtolower($status) === 'success';

        if (!$isSuccess) {
            Payment::where('txnid', $txnid)->update(['status' => Payment::STATUS_FAILED]);
            return response()->json(['status' => 'ok']);
        }

        // Amount validation
        $easebuzzAmount = $request->input('amount');
        $ourAmount = number_format((float) $payment->amount, 2, '.', '');

        if (abs((float) $easebuzzAmount - (float) $ourAmount) > 0.01) {
            Log::warning('WEBHOOK: Amount mismatch', [
                'txnid' => $txnid,
                'easebuzz_amount' => $easebuzzAmount,
                'our_amount' => $ourAmount,
            ]);

            Payment::where('txnid', $txnid)->update(['status' => Payment::STATUS_FAILED]);
            return response()->json(['error' => 'Amount mismatch'], 400);
        }

        try {
            DB::beginTransaction();

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
            }

            $payment->update(['user_id' => $user->id]);

            $course = $payment->course;
            $user->enrollIn($course);

            DB::commit();

            try {
                if ($isNewUser) {
                    $user->notify(new CoursePurchased(
                        payment: $payment,
                        course: $course,
                        isNewUser: true,
                        password: $plainPassword
                    ));
                } else {
                    $user->notify(new CourseEnrolled(
                        payment: $payment,
                        course: $course
                    ));
                }

                Log::info('WEBHOOK: Email sent', [
                    'user_id' => $user->id,
                    'email' => $user->email,
                    'is_new_user' => $isNewUser,
                ]);
            } catch (\Exception $e) {
                Log::error('WEBHOOK: Email failed', ['error' => $e->getMessage()]);
            }

            return response()->json(['status' => 'ok']);

        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('WEBHOOK: Database error', ['error' => $e->getMessage()]);
            return response()->json(['error' => 'Internal server error'], 500);
        }
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
            $params['message'] = urlencode($message);
        }

        if (!empty($params)) {
            $url .= '?' . http_build_query($params);
        }

        Log::info('Redirecting to frontend', ['url' => $url]);

        return redirect()->away($url);
    }
}