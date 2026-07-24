<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class RefreshCurrencyRates extends Command
{
    protected $signature = 'currencies:refresh-rates';

    protected $description = 'Fetch live USD exchange rates from open.er-api.com and cache them for checkout pricing';

    public function handle(): int
    {
        $supportedCurrencies = array_keys(config('currencies.rates'));

        try {
            $response = Http::timeout(10)->get('https://open.er-api.com/v6/latest/USD');
        } catch (\Throwable $e) {
            Log::warning('Currency rates: fetch failed, keeping previous cached rates', ['error' => $e->getMessage()]);
            $this->error('Fetch failed: ' . $e->getMessage());
            return self::FAILURE;
        }

        $body = $response->json();

        if (!$response->successful() || ($body['result'] ?? null) !== 'success' || empty($body['rates'])) {
            Log::warning('Currency rates: unexpected API response, keeping previous cached rates', ['status' => $response->status()]);
            $this->error('Unexpected API response (HTTP ' . $response->status() . ')');
            return self::FAILURE;
        }

        // All-or-nothing: a partial rate table (mixing stale and fresh entries) is worse
        // than not refreshing at all, since checkout pricing depends on every supported code.
        $rates = [];
        foreach ($supportedCurrencies as $code) {
            if (!isset($body['rates'][$code])) {
                Log::warning("Currency rates: missing rate for $code in API response, keeping previous cached rates");
                $this->error("Missing rate for $code in API response");
                return self::FAILURE;
            }
            $rates[$code] = (float) $body['rates'][$code];
        }
        $rates['USD'] = 1.0;

        Cache::forever('currency_rates', $rates);
        Cache::forever('currency_rates_updated_at', now()->toIso8601String());

        $this->info('Currency rates refreshed: ' . json_encode($rates));

        return self::SUCCESS;
    }
}
