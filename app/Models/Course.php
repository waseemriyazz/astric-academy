<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Course extends Model
{
    protected $fillable = [
        'title',
        'description',
        'price',
        'tools_count',
        'category',
        'duration',
        'features',
        'icon_name',
        'price_min',
        'price_max',
        'certificate_config',
    ];

    protected $casts = [
        'features' => 'array',
        'certificate_config' => 'array',
    ];

    public function users()
    {
        return $this->belongsToMany(User::class);
    }

    public function lessons()
    {
        return $this->hasMany(Lesson::class)->orderBy('order');
    }

    public function certificates()
    {
        return $this->hasMany(Certificate::class);
    }

    public function plans()
    {
        return $this->hasMany(Plan::class)->orderBy('sort_order');
    }
}