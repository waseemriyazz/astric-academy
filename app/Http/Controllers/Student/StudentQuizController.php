<?php

namespace App\Http\Controllers\Student;

use App\Http\Controllers\Controller;
use App\Models\Course;
use App\Models\Lesson;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class StudentQuizController extends Controller
{
    public function attempt(Request $request, Course $course, Lesson $lesson)
    {
        $user = Auth::user();

        // Verify enrollment
        if (!$user->courses()->where('courses.id', $course->id)->exists()) {
            return response()->json(['error' => 'Not enrolled in this course.'], 403);
        }

        // Verify lesson belongs to course
        if ($lesson->course_id !== $course->id) {
            return response()->json(['error' => 'Lesson not found in this course.'], 404);
        }

        // Check if lesson has a quiz
        $quiz = $lesson->quiz;
        if (!$quiz) {
            return response()->json(['error' => 'No quiz for this lesson.'], 404);
        }

        $validated = $request->validate([
            'selected_answer' => 'required|in:a,b,c,d',
        ]);

        $isCorrect = $validated['selected_answer'] === $quiz->correct_answer;

        // Create attempt record
        $quiz->attempts()->create([
            'user_id' => $user->id,
            'selected_answer' => $validated['selected_answer'],
            'is_correct' => $isCorrect,
        ]);

        return response()->json([
            'success' => true,
            'is_correct' => $isCorrect,
            'correct_answer' => $quiz->correct_answer,
            'message' => $isCorrect ? 'Correct answer!' : 'Incorrect answer. Try again!',
        ]);
    }
}