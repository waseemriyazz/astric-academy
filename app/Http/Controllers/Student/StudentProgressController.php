<?php

namespace App\Http\Controllers\Student;

use App\Http\Controllers\Controller;
use App\Models\Course;
use App\Models\Lesson;
use App\Models\LessonProgress;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;

class StudentProgressController extends Controller
{
    public function markPlayed(Request $request, Course $course, Lesson $lesson)
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

        // Upsert progress record
        LessonProgress::updateOrCreate(
            [
                'lesson_id' => $lesson->id,
                'user_id' => $user->id,
            ],
            [
                'video_played' => true,
            ]
        );

        Log::info('Student marked video as played', [
            'student_id' => $user->id,
            'student_email' => $user->email,
            'course_id' => $course->id,
            'lesson_id' => $lesson->id,
            'lesson_title' => $lesson->title,
        ]);

        return response()->json(['success' => true]);
    }
}