<?php

namespace App\Services;

use App\Models\Payment;
use App\Models\User;
use App\Notifications\CourseEnrolled;
use App\Notifications\CoursePurchased;
use Illuminate\Database\QueryException;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;

class PaymentFulfillmentService
{
    /**
     * Provision the buyer, enroll them, mark the payment paid, and notify them.
     *
     * Called from the success callback, the webhook, and the stale-payment sweep —
     * the only precondition is that the caller has already atomically claimed the
     * payment (moved it out of pending/processing) so this never runs twice for
     * the same txnid.
     */
    public function fulfill(Payment $payment): void
    {
        [$user, $isNewUser, $plainPassword] = $this->resolveUser($payment);

        DB::beginTransaction();

        try {
            $course = $payment->course;

            // Detected before enrollIn() runs, since enrollIn() is idempotent and
            // would otherwise silently hide that this buyer already owned the course.
            $isDuplicatePurchase = $user->isEnrolledIn($course);

            $payment->update([
                'user_id' => $user->id,
                'status' => Payment::STATUS_PAID,
                'gateway_response' => array_merge($payment->gateway_response ?? [], [
                    'duplicate_purchase' => $isDuplicatePurchase,
                ]),
            ]);

            $user->enrollIn($course);

            DB::commit();

            if ($isDuplicatePurchase) {
                Log::warning('PAYMENT FULFILL: Buyer already owned this course — likely duplicate charge, needs refund review', [
                    'payment_id' => $payment->id,
                    'txnid' => $payment->txnid,
                    'user_id' => $user->id,
                    'course_id' => $course->id,
                ]);
            }

            Log::info('PAYMENT FULFILL: Completed', [
                'payment_id' => $payment->id,
                'txnid' => $payment->txnid,
                'user_id' => $user->id,
                'course_id' => $course->id,
                'is_new_user' => $isNewUser,
                'is_duplicate_purchase' => $isDuplicatePurchase,
            ]);

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
            } catch (\Exception $e) {
                Log::error('PAYMENT FULFILL: Failed to send email', [
                    'payment_id' => $payment->id,
                    'error' => $e->getMessage(),
                ]);
            }
        } catch (\Exception $e) {
            DB::rollBack();

            Log::error('PAYMENT FULFILL: Database error', [
                'payment_id' => $payment->id,
                'txnid' => $payment->txnid,
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
            ]);

            throw $e;
        }
    }

    /**
     * Find or create the buyer's account. Two concurrent successful payments under the
     * same guest email (two tabs, or webhook+surl racing for two different courses) can
     * both see "no user yet" and both attempt User::create() — the loser hits the
     * `users.email` unique constraint. Rather than let that blow up the whole payment,
     * re-fetch the user the winner just created and carry on.
     *
     * @return array{0: User, 1: bool, 2: ?string} [$user, $isNewUser, $plainPassword]
     */
    private function resolveUser(Payment $payment): array
    {
        $user = User::where('email', $payment->buyer_email)->first();
        if ($user) {
            return [$user, false, null];
        }

        $plainPassword = Str::password(16);

        try {
            $user = User::create([
                'name' => $payment->buyer_name,
                'email' => $payment->buyer_email,
                'password' => $plainPassword, // 'hashed' cast handles bcrypt automatically
                'role' => 'student',
            ]);

            return [$user, true, $plainPassword];
        } catch (QueryException $e) {
            if (!$this->isUniqueConstraintViolation($e)) {
                throw $e;
            }

            $user = User::where('email', $payment->buyer_email)->first();

            if (!$user) {
                throw $e; // Not actually a duplicate-email race — rethrow the original error.
            }

            Log::info('PAYMENT FULFILL: Recovered from concurrent user-creation race', [
                'payment_id' => $payment->id,
                'user_id' => $user->id,
            ]);

            return [$user, false, null];
        }
    }

    private function isUniqueConstraintViolation(QueryException $e): bool
    {
        return $e->getCode() === '23000';
    }
}
