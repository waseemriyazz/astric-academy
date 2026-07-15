<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Course;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

class AdminCourseController extends Controller
{
    public function index()
    {
        $courses = Course::latest()->paginate(10);
        return view('admin.courses.index', compact('courses'));
    }

    public function create()
    {
        return view('admin.courses.create');
    }

    public function store(Request $request)
    {
        $request->validate([
            'title' => ['required', 'string', 'max:255'],
            'description' => ['nullable', 'string'],
            'price' => ['required', 'numeric', 'min:0'],
            'tools_count' => ['required', 'integer', 'min:0'],
        ]);

        $course = Course::create($request->only('title', 'description', 'price', 'tools_count'));

        Log::info('Admin created course', [
            'admin_id' => auth()->id(),
            'course_id' => $course->id,
            'title' => $course->title,
            'price' => $course->price,
        ]);

        // Redirect immediately to lessons page so they can add content
        return redirect()->route('admin.courses.lessons.index', $course)->with('success', 'Course created! Now add your lessons.');
    }

    public function edit(Course $course)
    {
        return view('admin.courses.edit', compact('course'));
    }

    public function update(Request $request, Course $course)
    {
        $request->validate([
            'title' => ['required', 'string', 'max:255'],
            'description' => ['nullable', 'string'],
            'price' => ['required', 'numeric', 'min:0'],
            'tools_count' => ['required', 'integer', 'min:0'],
        ]);

        $course->update($request->only('title', 'description', 'price', 'tools_count'));

        Log::info('Admin updated course', [
            'admin_id' => auth()->id(),
            'course_id' => $course->id,
            'title' => $course->title,
        ]);

        return redirect()->route('admin.courses.index')->with('success', 'Course updated successfully.');
    }

    public function destroy(Course $course)
    {
        $courseId = $course->id;
        $courseTitle = $course->title;
        $course->delete();

        Log::info('Admin deleted course', [
            'admin_id' => auth()->id(),
            'course_id' => $courseId,
            'title' => $courseTitle,
        ]);

        return redirect()->route('admin.courses.index')->with('success', 'Course deleted successfully.');
    }
}
