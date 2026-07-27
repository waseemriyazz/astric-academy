<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Course;

class PlanController extends Controller
{
    public function index(Course $course)
    {
        $plans = $course->plans()
            ->where('is_active', true)
            ->get()
            ->map(function ($plan) {
                return [
                    'id' => $plan->id,
                    'course_id' => $plan->course_id,
                    'tier_name' => $plan->tier_name,
                    'slug' => $plan->slug,
                    'description' => $plan->description,
                    'price' => (float) $plan->price,
                    'features' => $plan->features ?? [],
                    'duration' => $plan->duration,
                    'includes' => $plan->includes ?? [],
                    'sort_order' => $plan->sort_order,
                ];
            });

        return response()->json([
            'data' => $plans,
        ]);
    }
}