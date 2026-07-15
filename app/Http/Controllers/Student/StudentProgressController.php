<?php

namespace App\Http\Controllers\Student;

use App\Http\Controllers\Controller;
use App\Models\Course;
use App\Models\Lesson;
use App\Models\LessonProgress;
use App\Services\EnrollmentService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;

class StudentProgressController extends Controller
{
    public function __construct(
        protected EnrollmentService $enrollmentService,
    ) {}

    public function markPlayed(Request $request, Course $course, Lesson $lesson)
    {
        $user = Auth::user();

        // Verify enrollment
        if (!$this->enrollmentService->isEnrolled($user, $course)) {
            return response()->json(['error' => 'Not enrolled in this course.'], 403);
        }

        // Verify lesson belongs to course
        if (!$this->enrollmentService->verifyLessonBelongsToCourse($course, $lesson)) {
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
