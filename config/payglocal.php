<?php

return [

    /*
    |--------------------------------------------------------------------------
    | PayGlocal Payment Gateway Configuration
    |--------------------------------------------------------------------------
    |
    | Credentials are managed via the admin gateway settings page and stored
    | in the payment_gateway_configs table. These env values are only used
    | as fallback defaults when seeding that table for the first time.
    |
    */

    'merchant_id' => env('PAYGLOCAL_MERCHANT_ID'),

    // Private KID identifies our signing keypair to PayGlocal — sent on every request.
    'private_kid' => env('PAYGLOCAL_PRIVATE_KID'),

    // Public KID is PayGlocal's reference for their public key; not currently sent on
    // any request, kept for reconciliation alongside PAYGLOCAL_PUBLIC_KEY.
    'public_kid' => env('PAYGLOCAL_PUBLIC_KID'),

    'private_key' => env('PAYGLOCAL_PRIVATE_KEY'),

    'public_key' => env('PAYGLOCAL_PUBLIC_KEY'),

    'env' => env('PAYGLOCAL_ENV', 'test'),

    'callback_url' => env('PAYGLOCAL_CALLBACK_URL'),

];
