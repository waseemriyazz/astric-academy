<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Plan extends Model
{
    protected $fillable = [
        'course_id',
        'tier_name',
        'slug',
        'description',
        'price',
        'features',
        'duration',
        'includes',
        'sort_order',
        'is_active',
    ];

    protected $casts = [
        'features' => 'array',
        'includes' => 'array',
        'is_active' => 'boolean',
        'price' => 'decimal:2',
    ];

    public function course()
    {
        return $this->belongsTo(Course::class);
    }

    public function lessons()
    {
        return $this->hasMany(Lesson::class);
    }

    public function users()
    {
        return $this->belongsToMany(User::class, 'course_user')
            ->withPivot('course_id')
            ->withTimestamps();
    }

    public function payments()
    {
        return $this->belongsToMany(Payment::class, 'payment_plan')
            ->withPivot('price')
            ->withTimestamps();
    }
}