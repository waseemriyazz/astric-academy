<?php

return [

    /*
    |--------------------------------------------------------------------------
    | Easebuzz Payment Gateway Configuration
    |--------------------------------------------------------------------------
    |
    | This file contains the configuration for the Easebuzz payment gateway.
    | Values are read from the .env file. Never hardcode credentials.
    |
    */

    'key' => env('EASEBUZZ_KEY', env('EASEBUZZ_MERCHANT_KEY')),

    'salt' => env('EASEBUZZ_SALT'),

    'env' => env('EASEBUZZ_ENV', 'test'),

    'success_url' => env('EASEBUZZ_SUCCESS_URL'),

    'failure_url' => env('EASEBUZZ_FAILURE_URL'),

    'webhook_url' => env('EASEBUZZ_WEBHOOK_URL'),

];