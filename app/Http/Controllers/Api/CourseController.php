<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Course;

class CourseController extends Controller
{
    public function index()
    {
        $courses = Course::orderBy('category')->orderBy('title')->get();

        return response()->json([
            'data' => $courses->map(function ($course) {
                return [
                    'id' => $course->id,
                    'title' => $course->title,
                    'category' => $course->category,
                    'icon_name' => $course->icon_name,
                    'description' => $course->description,
                    'price_min' => (float) $course->price_min,
                    'price_max' => (float) $course->price_max,
                    'features' => $course->features ?? [],
                    'duration' => $course->duration,
                    'created_at' => $course->created_at,
                ];
            }),
        ]);
    }

    public function show(Course $course)
    {
        return response()->json([
            'data' => [
                'id' => $course->id,
                'title' => $course->title,
                'category' => $course->category,
                'icon_name' => $course->icon_name,
                'description' => $course->description,
                'price_min' => (float) $course->price_min,
                'price_max' => (float) $course->price_max,
                'features' => $course->features ?? [],
                'duration' => $course->duration,
                'created_at' => $course->created_at,
            ],
        ]);
    }
}