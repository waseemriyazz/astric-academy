<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Course;
use App\Models\Plan;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;

class AdminPlanController extends Controller
{
    public function index(Request $request)
    {
        $courseId = $request->get('course_id');
        if ($courseId) {
            $course = Course::findOrFail($courseId);
            $plans = $course->plans()->paginate(20);
        } else {
            $plans = Plan::with('course')->latest()->paginate(20);
        }

        $courses = Course::orderBy('title')->get();

        return view('admin.plans.index', compact('plans', 'courses', 'courseId'));
    }

    public function create(Request $request)
    {
        $courses = Course::orderBy('title')->get();
        $selectedCourseId = $request->get('course_id');

        return view('admin.plans.create', compact('courses', 'selectedCourseId'));
    }

    public function store(Request $request)
    {
        $request->validate([
            'course_id' => ['required', 'exists:courses,id'],
            'tier_name' => ['required', 'string', 'max:255'],
            'description' => ['nullable', 'string'],
            'price' => ['required', 'numeric', 'min:0'],
            'duration' => ['nullable', 'string', 'max:100'],
            'features' => ['nullable', 'string'],
            'includes' => ['nullable', 'string'],
            'sort_order' => ['nullable', 'integer', 'min:0'],
            'is_active' => ['nullable', 'boolean'],
        ]);

        $data = $request->only([
            'course_id', 'tier_name', 'description', 'price',
            'duration', 'sort_order', 'is_active'
        ]);

        // Generate unique slug
        $slug = Str::slug($request->tier_name . '-' . $request->course_id);
        $data['slug'] = $slug;

        // Convert features from textarea to array
        if ($request->filled('features')) {
            $features = array_filter(array_map('trim', explode("\n", $request->features)));
            $data['features'] = $features;
        }

        // Convert includes from textarea to array
        if ($request->filled('includes')) {
            $includes = array_filter(array_map('trim', explode("\n", $request->includes)));
            $data['includes'] = $includes;
        }

        $data['is_active'] = $request->boolean('is_active', true);

        $plan = Plan::create($data);

        Log::info('Admin created plan', [
            'admin_id' => auth()->id(),
            'plan_id' => $plan->id,
            'tier_name' => $plan->tier_name,
            'course_id' => $plan->course_id,
        ]);

        return redirect()->route('admin.plans.index', ['course_id' => $plan->course_id])
            ->with('success', 'Plan created successfully.');
    }

    public function edit(Plan $plan)
    {
        $courses = Course::orderBy('title')->get();

        return view('admin.plans.edit', compact('plan', 'courses'));
    }

    public function update(Request $request, Plan $plan)
    {
        $request->validate([
            'course_id' => ['required', 'exists:courses,id'],
            'tier_name' => ['required', 'string', 'max:255'],
            'description' => ['nullable', 'string'],
            'price' => ['required', 'numeric', 'min:0'],
            'duration' => ['nullable', 'string', 'max:100'],
            'features' => ['nullable', 'string'],
            'includes' => ['nullable', 'string'],
            'sort_order' => ['nullable', 'integer', 'min:0'],
            'is_active' => ['nullable', 'boolean'],
        ]);

        $data = $request->only([
            'course_id', 'tier_name', 'description', 'price',
            'duration', 'sort_order', 'is_active'
        ]);

        // Update slug if tier_name changed
        if ($plan->tier_name !== $request->tier_name) {
            $slug = Str::slug($request->tier_name . '-' . $request->course_id);
            // Ensure uniqueness
            $baseSlug = $slug;
            $counter = 1;
            while (Plan::where('slug', $slug)->where('id', '!=', $plan->id)->exists()) {
                $slug = $baseSlug . '-' . $counter++;
            }
            $data['slug'] = $slug;
        }

        // Convert features from textarea to array
        if ($request->filled('features')) {
            $features = array_filter(array_map('trim', explode("\n", $request->features)));
            $data['features'] = $features;
        } else {
            $data['features'] = [];
        }

        // Convert includes from textarea to array
        if ($request->filled('includes')) {
            $includes = array_filter(array_map('trim', explode("\n", $request->includes)));
            $data['includes'] = $includes;
        } else {
            $data['includes'] = [];
        }

        $data['is_active'] = $request->boolean('is_active', true);

        $plan->update($data);

        Log::info('Admin updated plan', [
            'admin_id' => auth()->id(),
            'plan_id' => $plan->id,
            'tier_name' => $plan->tier_name,
        ]);

        return redirect()->route('admin.plans.index', ['course_id' => $plan->course_id])
            ->with('success', 'Plan updated successfully.');
    }

    public function destroy(Plan $plan)
    {
        $courseId = $plan->course_id;
        $planId = $plan->id;
        $planName = $plan->tier_name;
        $plan->delete();

        Log::info('Admin deleted plan', [
            'admin_id' => auth()->id(),
            'plan_id' => $planId,
            'tier_name' => $planName,
        ]);

        return redirect()->route('admin.plans.index', ['course_id' => $courseId])
            ->with('success', 'Plan deleted successfully.');
    }
}