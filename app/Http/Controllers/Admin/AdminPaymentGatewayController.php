<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\PaymentGatewayConfig;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\View\View;

class AdminPaymentGatewayController extends Controller
{
    private const GATEWAY_FIELDS = [
        PaymentGatewayConfig::GATEWAY_EASEBUZZ => ['key', 'salt'],
        PaymentGatewayConfig::GATEWAY_PAYGLOCAL => ['merchant_id', 'private_kid', 'public_kid', 'private_key', 'public_key'],
    ];

    public function index(): View
    {
        $gateways = collect(self::GATEWAY_FIELDS)->keys()->mapWithKeys(function (string $gateway) {
            return [$gateway => PaymentGatewayConfig::firstOrCreate(
                ['gateway' => $gateway],
                ['is_active' => false, 'is_production' => false, 'config' => []]
            )];
        });

        return view('admin.gateways.index', ['gateways' => $gateways]);
    }

    public function update(Request $request, string $gateway): RedirectResponse
    {
        if (!array_key_exists($gateway, self::GATEWAY_FIELDS)) {
            abort(404);
        }

        $fields = self::GATEWAY_FIELDS[$gateway];

        $validated = $request->validate([
            'is_active' => ['nullable', 'boolean'],
            'is_production' => ['nullable', 'boolean'],
            ...array_fill_keys(array_map(fn ($f) => "config.$f", $fields), ['nullable', 'string']),
        ]);

        $gatewayConfig = PaymentGatewayConfig::firstOrCreate(
            ['gateway' => $gateway],
            ['is_active' => false, 'is_production' => false, 'config' => []]
        );

        // Masked credential fields: a blank submission means "keep the existing value",
        // not "clear the secret" — the admin never sees the real value to retype it.
        $config = $gatewayConfig->config ?? [];
        foreach ($fields as $field) {
            $value = $validated['config'][$field] ?? null;
            if ($value !== null && $value !== '') {
                $config[$field] = $value;
            }
        }

        $isActive = $request->boolean('is_active');

        if ($isActive) {
            // Only one gateway may be active at a time.
            PaymentGatewayConfig::where('gateway', '!=', $gateway)->update(['is_active' => false]);
        }

        $gatewayConfig->update([
            'is_active' => $isActive,
            'is_production' => $request->boolean('is_production'),
            'config' => $config,
        ]);

        Log::info('Admin updated payment gateway config', [
            'admin_id' => auth()->id(),
            'gateway' => $gateway,
            'is_active' => $isActive,
            'is_production' => $gatewayConfig->is_production,
        ]);

        return redirect()->route('admin.gateways.index')->with('success', ucfirst($gateway) . ' gateway settings saved.');
    }
}
