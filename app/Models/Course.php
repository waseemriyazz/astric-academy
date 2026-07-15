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
        'certificate_config',
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
}
