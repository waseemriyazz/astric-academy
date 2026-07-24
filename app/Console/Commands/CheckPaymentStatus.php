<?php

namespace App\Console\Commands;

use App\Contracts\PaymentGatewayContract;
use App\Models\Payment;
use App\Models\PaymentGatewayConfig;
use App\Services\EasebuzzService;
use App\Services\PayGlocalService;
use Illuminate\Console\Command;

class CheckPaymentStatus extends Command
{
    protected $signature = 'payments:status {txnid? : Our merchant transaction ID. Omit to list recent pending/processing payments.}';

    protected $description = 'Check a payment\'s live status directly against its gateway — same authoritative check the app itself uses, without waiting for a callback or the sweep.';

    public function __construct(
        private readonly EasebuzzService $easebuzz,
        private readonly PayGlocalService $payglocal,
    ) {
        parent::__construct();
    }

    public function handle(): int
    {
        $txnid = $this->argument('txnid');

        if (!$txnid) {
            return $this->listRecent();
        }

        $payment = Payment::where('txnid', $txnid)->first();

        if (!$payment) {
            $this->error("No payment found with txnid: {$txnid}");
            return self::FAILURE;
        }

        $this->info("=== {$payment->txnid} ===");
        $this->line('Gateway:      ' . $payment->gateway);
        $this->line('Our status:   ' . $payment->status);
        $this->line('Amount:       ' . $payment->amount . ' ' . $payment->currency);
        $this->line('Created:      ' . $payment->created_at);
        $this->line('Last updated: ' . $payment->updated_at);
        $this->newLine();

        $service = $this->resolveGatewayService($payment->gateway);

        if (!$service) {
            $this->error("Unknown gateway: {$payment->gateway}");
            return self::FAILURE;
        }

        $identifier = $payment->gateway_response['status_url'] ?? $payment->payment_id ?: $payment->txnid;

        $this->info('Checking live status at gateway...');
        $verification = $service->verifyTransaction($identifier);

        $this->newLine();
        $this->info('=== Live gateway status ===');
        $this->line('success: ' . ($verification['success'] ? 'YES' : 'no'));
        $this->line('pending: ' . ($verification['pending'] ? 'YES — still settling' : 'no'));
        $this->line('amount:  ' . ($verification['amount'] ?? 'n/a'));
        $this->newLine();

        if (is_array($verification['raw'])) {
            foreach ($verification['raw'] as $key => $value) {
                if (is_scalar($value)) {
                    $this->line("  {$key}: {$value}");
                }
            }
        } else {
            $this->line('raw: ' . $verification['raw']);
        }

        return self::SUCCESS;
    }

    private function listRecent(): int
    {
        $payments = Payment::whereIn('status', [Payment::STATUS_PENDING, Payment::STATUS_PROCESSING])
            ->orderByDesc('created_at')
            ->limit(20)
            ->get(['txnid', 'gateway', 'status', 'amount', 'created_at']);

        if ($payments->isEmpty()) {
            $this->info('No pending/processing payments found.');
            return self::SUCCESS;
        }

        $this->table(
            ['txnid', 'gateway', 'status', 'amount', 'created_at'],
            $payments->map(fn ($p) => [$p->txnid, $p->gateway, $p->status, $p->amount, $p->created_at])->toArray()
        );

        $this->line('Run `php artisan payments:status <txnid>` to check one against its gateway.');

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
}
