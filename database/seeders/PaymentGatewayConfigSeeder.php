<?php

namespace Database\Seeders;

use App\Models\PaymentGatewayConfig;
use Illuminate\Database\Seeder;

class PaymentGatewayConfigSeeder extends Seeder
{
    public function run(): void
    {
        // firstOrCreate, not updateOrCreate — once a gateway row exists, credentials are
        // owned by the admin gateway settings page. Reseeding must never clobber them back
        // to (possibly blank) .env values.
        PaymentGatewayConfig::firstOrCreate(
            ['gateway' => PaymentGatewayConfig::GATEWAY_EASEBUZZ],
            [
                'is_active' => true,
                'is_production' => config('easebuzz.env') === 'prod',
                'config' => [
                    'key' => config('easebuzz.key'),
                    'salt' => config('easebuzz.salt'),
                ],
            ]
        );

        PaymentGatewayConfig::firstOrCreate(
            ['gateway' => PaymentGatewayConfig::GATEWAY_PAYGLOCAL],
            [
                'is_active' => false,
                'is_production' => config('payglocal.env') === 'prod',
                'config' => [
                    'merchant_id' => config('payglocal.merchant_id') ?? '',
                    'private_kid' => config('payglocal.private_kid') ?? '',
                    'public_kid' => config('payglocal.public_kid') ?? '',
                    'private_key' => config('payglocal.private_key') ?? '',
                    'public_key' => config('payglocal.public_key') ?? '',
                ],
            ]
        );
    }
}
