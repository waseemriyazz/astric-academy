<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

use App\Models\Course;
use App\Models\Lesson;

class AdminLessonController extends Controller
{
    public function index(Course $course)
    {
        $lessons = $course->lessons()->orderBy('order')->get();
        return view('admin.lessons.index', compact('course', 'lessons'));
    }

    public function store(Request $request, Course $course)
    {
        $validated = $request->validate([
            'title' => 'required|string|max:255',
            'description' => 'nullable|string',
            'youtube_url' => 'nullable|string',
            'vimeo_url' => 'nullable|string',
            'duration' => 'nullable|string|max:50',
            'order' => 'nullable|integer',
        ]);

        $validated['order'] = $validated['order'] ?? 0;
        
        $course->lessons()->create($validated);

        return redirect()->route('admin.courses.lessons.index', $course)->with('success', 'Lesson added successfully!');
    }

    public function edit(Course $course, Lesson $lesson)
    {
        return view('admin.lessons.edit', compact('course', 'lesson'));
    }

    public function update(Request $request, Course $course, Lesson $lesson)
    {
        $validated = $request->validate([
            'title' => 'required|string|max:255',
            'description' => 'nullable|string',
            'youtube_url' => 'nullable|string',
            'vimeo_url' => 'nullable|string',
            'duration' => 'nullable|string|max:50',
            'order' => 'nullable|integer',
        ]);

        $validated['order'] = $validated['order'] ?? 0;

        $lesson->update($validated);

        return redirect()->route('admin.courses.lessons.index', $course)->with('success', 'Lesson updated successfully!');
    }

    public function destroy(Course $course, Lesson $lesson)
    {
        $lesson->delete();

        return redirect()->route('admin.courses.lessons.index', $course)->with('success', 'Lesson deleted successfully!');
    }
}
