<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\PaymentGatewayConfig;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\View\View;

class AdminPaymentGatewayController extends Controller
{
    private const GATEWAY_TEXT_FIELDS = [
        PaymentGatewayConfig::GATEWAY_EASEBUZZ => ['key', 'salt'],
        PaymentGatewayConfig::GATEWAY_PAYGLOCAL => ['merchant_id', 'private_kid', 'public_kid'],
    ];

    // RSA keys are uploaded as .pem files rather than pasted, so the admin never has to
    // hand-copy multi-line key material through a textarea.
    private const GATEWAY_FILE_FIELDS = [
        PaymentGatewayConfig::GATEWAY_EASEBUZZ => [],
        PaymentGatewayConfig::GATEWAY_PAYGLOCAL => ['private_key', 'public_key'],
    ];

    public function index(): View
    {
        $gateways = collect(self::GATEWAY_TEXT_FIELDS)->keys()->mapWithKeys(function (string $gateway) {
            return [$gateway => PaymentGatewayConfig::firstOrCreate(
                ['gateway' => $gateway],
                ['is_active' => false, 'is_production' => false, 'config' => []]
            )];
        });

        return view('admin.gateways.index', ['gateways' => $gateways]);
    }

    public function update(Request $request, string $gateway): RedirectResponse
    {
        if (!array_key_exists($gateway, self::GATEWAY_TEXT_FIELDS)) {
            abort(404);
        }

        $textFields = self::GATEWAY_TEXT_FIELDS[$gateway];
        $fileFields = self::GATEWAY_FILE_FIELDS[$gateway];

        $validated = $request->validate([
            'is_active' => ['nullable', 'boolean'],
            'is_production' => ['nullable', 'boolean'],
            ...array_fill_keys(array_map(fn ($f) => "config.$f", $textFields), ['nullable', 'string']),
            ...array_fill_keys(array_map(fn ($f) => "config_files.$f", $fileFields), ['nullable', 'file', 'max:10']),
        ]);

        $gatewayConfig = PaymentGatewayConfig::firstOrCreate(
            ['gateway' => $gateway],
            ['is_active' => false, 'is_production' => false, 'config' => []]
        );

        // Masked/blank-means-keep-existing for both: a blank text field or no file chosen
        // means "keep the existing value" — the admin never sees the real value to retype it.
        $config = $gatewayConfig->config ?? [];

        foreach ($textFields as $field) {
            $value = $validated['config'][$field] ?? null;
            if ($value !== null && $value !== '') {
                $config[$field] = $value;
            }
        }

        foreach ($fileFields as $field) {
            if (!$request->hasFile("config_files.$field")) {
                continue;
            }

            $contents = trim($request->file("config_files.$field")->get());

            if (!str_contains($contents, '-----BEGIN')) {
                return redirect()->route('admin.gateways.index')
                    ->with('error', "The uploaded file for \"$field\" doesn't look like a PEM key (missing -----BEGIN header).");
            }

            $config[$field] = $contents;
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

    /**
     * Reveal a single short credential field (merchant IDs, KIDs) on demand — the value is
     * never embedded in the page itself, only fetched when the admin explicitly clicks to
     * view it. Deliberately does NOT cover the RSA key file fields; those stay write-only.
     */
    public function reveal(string $gateway, string $field): JsonResponse
    {
        if (!array_key_exists($gateway, self::GATEWAY_TEXT_FIELDS) || !in_array($field, self::GATEWAY_TEXT_FIELDS[$gateway], true)) {
            abort(404);
        }

        $gatewayConfig = PaymentGatewayConfig::where('gateway', $gateway)->first();

        Log::info('Admin revealed gateway credential', [
            'admin_id' => auth()->id(),
            'gateway' => $gateway,
            'field' => $field,
        ]);

        return response()->json(['value' => $gatewayConfig?->credential($field) ?? '']);
    }
}
