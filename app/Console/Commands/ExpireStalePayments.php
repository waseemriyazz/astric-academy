<?php

namespace App\Console\Commands;

use App\Contracts\PaymentGatewayContract;
use App\Models\Payment;
use App\Models\PaymentGatewayConfig;
use App\Services\EasebuzzService;
use App\Services\PayGlocalService;
use App\Services\PaymentFulfillmentService;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Log;

class ExpireStalePayments extends Command
{
    protected $signature = 'payments:expire-stale';

    protected $description = 'Reconcile payments left pending/processing past the checkout timeout window against each gateway\'s status API, so none sit unresolved forever.';

    public function __construct(
        private readonly EasebuzzService $easebuzz,
        private readonly PayGlocalService $payglocal,
        private readonly PaymentFulfillmentService $fulfillment,
    ) {
        parent::__construct();
    }

    public function handle(): int
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
            $this->reconcile($payment);
        }

        return self::SUCCESS;
    }

    private function resolveGatewayService(string $gatewayName): ?PaymentGatewayContract
    {
        return match ($gatewayName) {
            PaymentGatewayConfig::GATEWAY_PAYGLOCAL => $this->payglocal,
            PaymentGatewayConfig::GATEWAY_EASEBUZZ => $this->easebuzz,
            default => null,
        };
    }

    private function reconcile(Payment $payment): void
    {
        $service = $this->resolveGatewayService($payment->gateway);

        if (!$service) {
            Log::warning('PAYMENT SWEEP: Unknown gateway, skipping', ['txnid' => $payment->txnid, 'gateway' => $payment->gateway]);
            return;
        }

        // Atomic claim — identical pattern to the webhook/success handlers — so
        // this can never race a real callback that arrives at the same moment.
        $claimed = Payment::where('id', $payment->id)
            ->whereIn('status', [Payment::STATUS_PENDING, Payment::STATUS_PROCESSING])
            ->update(['status' => Payment::STATUS_PROCESSING]);

        if (!$claimed) {
            Log::info('PAYMENT SWEEP: Skipped, a live callback claimed it first', ['txnid' => $payment->txnid]);
            return;
        }

        // PayGlocal's identifier is the stored statusUrl (carries PayGlocal's own signed
        // token); other gateways use txnid. Without a usable identifier there's nothing to
        // check — leave it processing rather than guessing.
        $identifier = $payment->gateway_response['status_url'] ?? $payment->payment_id ?: $payment->txnid;

        $verification = $service->verifyTransaction($identifier);

        if ($verification['success']) {
            Log::info('PAYMENT SWEEP: Late success confirmed via status API', ['txnid' => $payment->txnid]);
            $this->fulfillment->fulfill($payment->fresh());
            return;
        }

        if ($verification['pending']) {
            // Still genuinely unresolved at the gateway (e.g. PayGlocal SENT_FOR_CAPTURE) —
            // not a failure, just not done yet. Leave it processing for the next sweep run
            // rather than expiring a payment that may still complete.
            Log::info('PAYMENT SWEEP: Still pending at gateway — leaving for next sweep', [
                'txnid' => $payment->txnid,
                'verification' => $verification,
            ]);
            return;
        }

        Log::info('PAYMENT SWEEP: No successful transaction at gateway — marking expired', [
            'txnid' => $payment->txnid,
            'verification' => $verification,
        ]);

        $payment->update([
            'status' => Payment::STATUS_EXPIRED,
            'gateway_response' => array_merge($payment->gateway_response ?? [], [
                'expired_by_sweep_at' => now()->toIso8601String(),
                'last_gateway_check' => $verification['raw'] ?? null,
            ]),
        ]);
    }
}
