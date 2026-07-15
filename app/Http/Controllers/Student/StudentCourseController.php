<?php

namespace App\Http\Controllers\Student;

use App\Http\Controllers\Controller;
use App\Models\Course;
use App\Models\Lesson;
use App\Models\QuizAttempt;
use App\Models\LessonProgress;
use App\Services\EnrollmentService;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;

class StudentCourseController extends Controller
{
    public function __construct(
        protected EnrollmentService $enrollmentService,
    ) {}

    public function show(Course $course, Lesson $lesson = null)
    {
        $user = Auth::user();

        // 1. Verify the student is enrolled in this course
        if (!$this->enrollmentService->isEnrolled($user, $course)) {
            Log::warning('Student attempted to access unenrolled course', [
                'student_id' => $user->id,
                'student_email' => $user->email,
                'course_id' => $course->id,
            ]);
            abort(403, 'You are not enrolled in this course.');
        }

        // 2. Load all lessons for this course, ordered
        $lessons = $course->lessons()->orderBy('order')->get();

        // 3. Determine unlocked lessons
        $unlockedLessonIds = $this->getUnlockedLessonIds($lessons, $user);

        // 4. Determine the active lesson
        if ($lesson) {
            if (!$this->enrollmentService->verifyLessonBelongsToCourse($course, $lesson)) {
                abort(404, 'Lesson not found in this course.');
            }
            if (!in_array($lesson->id, $unlockedLessonIds)) {
                $firstUnlocked = $lessons->firstWhere('id', $unlockedLessonIds[0] ?? null);
                if ($firstUnlocked) {
                    return redirect()->route('student.courses.show', [$course->id, $firstUnlocked->id])
                        ->with('error', 'Please complete the previous lesson\'s quiz to access this lesson.');
                }
                abort(403, 'This lesson is locked.');
            }
            $activeLesson = $lesson;
        } else {
            $activeLesson = $lessons->first();
        }

        // 5. Check if the active lesson has a quiz and if the user has attempted it
        $quizAttempted = false;
        $quizAttempt = null;
        if ($activeLesson && $activeLesson->quiz) {
            $quizAttempt = QuizAttempt::where('quiz_id', $activeLesson->quiz->id)
                ->where('user_id', $user->id)
                ->first();
            $quizAttempted = $quizAttempt !== null;
        }

        // 6. Check if the video has been played for the active lesson
        $videoPlayed = false;
        if ($activeLesson) {
            $videoPlayed = LessonProgress::where('lesson_id', $activeLesson->id)
                ->where('user_id', $user->id)
                ->where('video_played', true)
                ->exists();
        }

        return view('student.courses.show', compact('course', 'lessons', 'activeLesson', 'unlockedLessonIds', 'quizAttempted', 'quizAttempt', 'videoPlayed'));
    }

    private function getUnlockedLessonIds($lessons, $user)
    {
        $unlockedIds = [];

        // Bulk load all attempted quiz IDs for this user in one query
        $attemptedQuizIds = QuizAttempt::where('user_id', $user->id)
            ->pluck('quiz_id')
            ->toArray();

        foreach ($lessons as $index => $lesson) {
            if ($index === 0) {
                $unlockedIds[] = $lesson->id;
            } else {
                $previousLesson = $lessons[$index - 1];
                $previousQuiz = $previousLesson->quiz;

                if ($previousQuiz) {
                    if (in_array($previousQuiz->id, $attemptedQuizIds)) {
                        $unlockedIds[] = $lesson->id;
                    }
                } else {
                    $unlockedIds[] = $lesson->id;
                }
            }
        }

        return $unlockedIds;
    }
}
