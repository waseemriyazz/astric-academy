<?php

namespace App\Console\Commands;

use App\Models\Payment;
use App\Services\EasebuzzService;
use App\Services\PaymentFulfillmentService;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Log;

class ExpireStalePayments extends Command
{
    protected $signature = 'payments:expire-stale';

    protected $description = 'Reconcile payments left pending/processing past the checkout timeout window against Easebuzz\'s Transaction API, so none sit unresolved forever.';

    public function handle(EasebuzzService $easebuzz, PaymentFulfillmentService $fulfillment): int
    {
        $expiryMinutes = (int) config('easebuzz.pending_expiry_minutes', 30);
        $cutoff = now()->subMinutes($expiryMinutes);

        $stalePayments = Payment::whereIn('status', [Payment::STATUS_PENDING, Payment::STATUS_PROCESSING])
            ->where('updated_at', '<=', $cutoff)
            ->get();

        if ($stalePayments->isEmpty()) {
            $this->info('No stale payments found.');
            return self::SUCCESS;
        }

        $this->info("Reconciling {$stalePayments->count()} stale payment(s)...");

        foreach ($stalePayments as $payment) {
            $this->reconcile($payment, $easebuzz, $fulfillment);
        }

        return self::SUCCESS;
    }

    private function reconcile(Payment $payment, EasebuzzService $easebuzz, PaymentFulfillmentService $fulfillment): void
    {
        // Atomic claim — identical pattern to the webhook/success handlers — so
        // this can never race a real callback that arrives at the same moment.
        $claimed = Payment::where('id', $payment->id)
            ->whereIn('status', [Payment::STATUS_PENDING, Payment::STATUS_PROCESSING])
            ->update(['status' => Payment::STATUS_PROCESSING]);

        if (!$claimed) {
            Log::info('PAYMENT SWEEP: Skipped, a live callback claimed it first', ['txnid' => $payment->txnid]);
            return;
        }

        $verification = $easebuzz->verifyTransaction($payment->txnid);
        $transactionStatus = $verification['data']['txn_status'] ?? $verification['data']['status'] ?? null;
        $isSuccess = ($verification['status'] === 1)
            && in_array(strtolower($transactionStatus ?? ''), ['success', 'completed'], true);

        if ($isSuccess) {
            Log::info('PAYMENT SWEEP: Late success confirmed via Transaction API', ['txnid' => $payment->txnid]);
            $fulfillment->fulfill($payment->fresh());
            return;
        }

        Log::info('PAYMENT SWEEP: No successful transaction at gateway — marking expired', [
            'txnid' => $payment->txnid,
            'gateway_status' => $transactionStatus,
        ]);

        $payment->update([
            'status' => Payment::STATUS_EXPIRED,
            'gateway_response' => array_merge($payment->gateway_response ?? [], [
                'expired_by_sweep_at' => now()->toIso8601String(),
                'last_gateway_check' => $verification['data'] ?? null,
            ]),
        ]);
    }
}
