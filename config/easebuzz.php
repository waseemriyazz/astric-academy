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

    /*
    |--------------------------------------------------------------------------
    | Pending Payment Expiry
    |--------------------------------------------------------------------------
    |
    | Easebuzz's hosted checkout session has its own inactivity timeout, but it
    | never notifies us when a customer simply abandons it without attempting
    | payment (no status change occurs, so no webhook fires). The
    | payments:expire-stale scheduled command sweeps payments left pending/
    | processing longer than this window, asks Easebuzz's Transaction API for
    | the authoritative status, and reconciles our record accordingly — so a
    | payment never sits "pending" forever. Keep this comfortably longer than
    | Easebuzz's own session timeout so we don't race a customer still paying.
    |
    */

    'pending_expiry_minutes' => env('EASEBUZZ_PENDING_EXPIRY_MINUTES', 30),

];