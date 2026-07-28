<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Str;

class Course extends Model
{
    protected $fillable = [
        'title',
        'slug',
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

    protected static function booted(): void
    {
        static::creating(function (Course $course) {
            if (! $course->slug) {
                $course->slug = static::generateUniqueSlug($course->title);
            }
        });
    }

    protected static function generateUniqueSlug(string $title): string
    {
        $base = Str::slug($title);
        $slug = $base;
        $suffix = 2;

        while (static::where('slug', $slug)->exists()) {
            $slug = "{$base}-{$suffix}";
            $suffix++;
        }

        return $slug;
    }

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