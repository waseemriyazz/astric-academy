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
            'summary' => 'nullable|string',
            'youtube_url' => 'nullable|string',
            'vimeo_url' => 'nullable|string',
            'duration' => 'nullable|string|max:50',
            'order' => 'nullable|integer',
            'quiz_question' => 'nullable|string',
            'quiz_option_a' => 'nullable|string',
            'quiz_option_b' => 'nullable|string',
            'quiz_option_c' => 'nullable|string',
            'quiz_option_d' => 'nullable|string',
            'quiz_correct_answer' => 'nullable|in:a,b,c,d',
        ]);

        $validated['order'] = $validated['order'] ?? 0;
        
        $lesson = $course->lessons()->create($validated);

        // Create quiz if quiz data is provided
        if (!empty($validated['quiz_question'])) {
            $lesson->quiz()->create([
                'question' => $validated['quiz_question'],
                'option_a' => $validated['quiz_option_a'],
                'option_b' => $validated['quiz_option_b'],
                'option_c' => $validated['quiz_option_c'],
                'option_d' => $validated['quiz_option_d'],
                'correct_answer' => $validated['quiz_correct_answer'],
            ]);
        }

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
            'summary' => 'nullable|string',
            'youtube_url' => 'nullable|string',
            'vimeo_url' => 'nullable|string',
            'duration' => 'nullable|string|max:50',
            'order' => 'nullable|integer',
            'quiz_question' => 'nullable|string',
            'quiz_option_a' => 'nullable|string',
            'quiz_option_b' => 'nullable|string',
            'quiz_option_c' => 'nullable|string',
            'quiz_option_d' => 'nullable|string',
            'quiz_correct_answer' => 'nullable|in:a,b,c,d',
        ]);

        $validated['order'] = $validated['order'] ?? 0;

        $lesson->update($validated);

        // Update or create quiz
        if (!empty($validated['quiz_question'])) {
            $lesson->quiz()->updateOrCreate(
                ['lesson_id' => $lesson->id],
                [
                    'question' => $validated['quiz_question'],
                    'option_a' => $validated['quiz_option_a'],
                    'option_b' => $validated['quiz_option_b'],
                    'option_c' => $validated['quiz_option_c'],
                    'option_d' => $validated['quiz_option_d'],
                    'correct_answer' => $validated['quiz_correct_answer'],
                ]
            );
        } else {
            // If quiz question is empty, delete any existing quiz
            $lesson->quiz()->delete();
        }

        return redirect()->route('admin.courses.lessons.index', $course)->with('success', 'Lesson updated successfully!');
    }

    public function destroy(Course $course, Lesson $lesson)
    {
        $lesson->delete();

        return redirect()->route('admin.courses.lessons.index', $course)->with('success', 'Lesson deleted successfully!');
    }
}
