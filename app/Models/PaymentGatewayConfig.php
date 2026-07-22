<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class PaymentGatewayConfig extends Model
{
    const GATEWAY_EASEBUZZ = 'easebuzz';
    const GATEWAY_PAYGLOCAL = 'payglocal';

    protected $fillable = [
        'gateway',
        'is_active',
        'is_production',
        'config',
    ];

    protected $casts = [
        'is_active' => 'boolean',
        'is_production' => 'boolean',
        'config' => 'array',
    ];

    /**
     * Get a credential value from this gateway's config blob.
     */
    public function credential(string $key, mixed $default = null): mixed
    {
        return data_get($this->config, $key, $default);
    }
}
