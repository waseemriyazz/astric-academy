<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Cache;

class CurrencyController extends Controller
{
    public function index()
    {
        return response()->json([
            'data' => [
                'rates' => Cache::get('currency_rates', config('currencies.rates')),
            ],
        ]);
    }
}
