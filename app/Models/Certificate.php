<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Certificate extends Model
{
    protected $fillable = [
        'user_id',
        'course_id',
        'serial_number',
        'completed_at',
        'issued_at',
        'is_revoked',
    ];

    protected function casts(): array
    {
        return [
            'completed_at' => 'datetime',
            'issued_at' => 'datetime',
            'is_revoked' => 'boolean',
        ];
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function course(): BelongsTo
    {
        return $this->belongsTo(Course::class);
    }

    public static function generateSerialNumber(): string
    {
        $prefix = 'AST-' . date('Y');
        $last = static::where('serial_number', 'like', $prefix . '-%')
            ->orderBy('id', 'desc')
            ->first();

        if ($last) {
            $num = (int) substr($last->serial_number, -4) + 1;
        } else {
            $num = 1;
        }

        return $prefix . '-' . str_pad($num, 4, '0', STR_PAD_LEFT);
    }
}