<?php

namespace App\Console\Commands;

use App\Models\PaymentGatewayConfig;
use App\Services\PayGlocalService;
use Illuminate\Console\Command;

class TestPayGlocalConnection extends Command
{
    protected $signature = 'payglocal:test {--amount=1.00}';

    protected $description = 'Send a real test transaction to PayGlocal and report full diagnostics — does not touch is_active, safe to run repeatedly.';

    public function handle(): int
    {
        $config = PaymentGatewayConfig::where('gateway', PaymentGatewayConfig::GATEWAY_PAYGLOCAL)->first();

        if (!$config) {
            $this->error('No PayGlocal gateway config found. Run: php artisan db:seed --class=PaymentGatewayConfigSeeder');
            return self::FAILURE;
        }

        $this->info('=== PayGlocal Configuration ===');
        $this->line('Merchant ID:  ' . ($config->credential('merchant_id') ?: '(empty)'));
        $this->line('Private KID:  ' . ($config->credential('private_kid') ?: '(empty)'));
        $this->line('Public KID:   ' . ($config->credential('public_kid') ?: '(empty)'));
        $this->line('Private key:  ' . $this->describeSecret($config->credential('private_key')));
        $this->line('Public key:   ' . $this->describeSecret($config->credential('public_key')));
        $this->line('Environment:  ' . ($config->is_production ? 'PRODUCTION' : 'SANDBOX / UAT'));
        $this->line('Live gateway: ' . ($config->is_active ? 'YES — routing real checkouts' : 'no — this test is isolated and safe'));
        $this->newLine();

        $service = app(PayGlocalService::class);

        if (!$service->isConfigured()) {
            $this->error('Not fully configured (need merchant_id, private_kid, and private_key at minimum). Aborting.');
            return self::FAILURE;
        }

        $txnid = $service->generateTxnId();

        $this->info("=== Sending test transaction ===");
        $this->line('txnid: ' . $txnid);
        $this->newLine();

        $result = $service->initiatePayment([
            'txnid' => $txnid,
            'amount' => (string) $this->option('amount'),
            'currency' => 'INR',
            'firstname' => 'Test User',
            'email' => 'test@example.com',
            'callback_url' => config('app.payglocal_callback_url'),
        ]);

        if ($result['success']) {
            $this->info('SUCCESS — PayGlocal accepted the request.');
            $this->line('gid:          ' . $result['gid']);
            $this->line('redirect_url: ' . $result['redirect_url']);
            $this->newLine();
            $this->info('Authentication works end-to-end. Flip PayGlocal active at /admin/gateways when ready to go live.');
            return self::SUCCESS;
        }

        $this->error('FAILED — ' . $result['error']);
        $this->newLine();
        $this->warn('Send this exact block to PayGlocal support:');
        $this->line('--------------------------------------------------');
        $this->line('Merchant ID:  ' . $config->credential('merchant_id'));
        $this->line('Private KID:  ' . $config->credential('private_kid'));
        $this->line('txnid:        ' . $txnid);
        $this->line('gid:          ' . ($result['gid'] ?? 'n/a'));
        $this->line('HTTP status:  ' . ($result['http_code'] ?? 'n/a'));
        $this->line('reasonCode:   ' . ($result['reason_code'] ?? 'n/a'));
        $this->line('timestamp:    ' . now()->toIso8601String());
        $this->line('--------------------------------------------------');
        $this->newLine();
        $this->line('Full request/response detail (including the Cloudflare Ray ID from PayGlocal\'s response headers) is in storage/logs/laravel.log — search for txnid ' . $txnid . '.');

        return self::FAILURE;
    }

    private function describeSecret(?string $value): string
    {
        if (empty($value)) {
            return 'EMPTY';
        }

        return 'SET (' . strlen($value) . ' bytes)';
    }
}
