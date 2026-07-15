<?php

namespace App\Http\Controllers\Student;

use App\Http\Controllers\Controller;
use App\Models\Course;
use App\Models\Lesson;
use App\Models\QuizAttempt;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;

class StudentCourseController extends Controller
{
    public function show(Course $course, Lesson $lesson = null)
    {
        // 1. Verify the student is enrolled in this course
        $user = Auth::user();
        
        if (!$user->courses()->where('courses.id', $course->id)->exists()) {
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
            // Verify this lesson actually belongs to this course
            if ($lesson->course_id !== $course->id) {
                abort(404, 'Lesson not found in this course.');
            }
            // Check if the lesson is unlocked
            if (!in_array($lesson->id, $unlockedLessonIds)) {
                // Redirect to the first unlocked lesson
                $firstUnlocked = $lessons->firstWhere('id', $unlockedLessonIds[0] ?? null);
                if ($firstUnlocked) {
                    return redirect()->route('student.courses.show', [$course->id, $firstUnlocked->id])
                        ->with('error', 'Please complete the previous lesson\'s quiz to access this lesson.');
                }
                abort(403, 'This lesson is locked.');
            }
            $activeLesson = $lesson;
        } else {
            // Default to the first lesson if none provided
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
            $videoPlayed = \App\Models\LessonProgress::where('lesson_id', $activeLesson->id)
                ->where('user_id', $user->id)
                ->where('video_played', true)
                ->exists();
        }

        return view('student.courses.show', compact('course', 'lessons', 'activeLesson', 'unlockedLessonIds', 'quizAttempted', 'quizAttempt', 'videoPlayed'));
    }

    private function getUnlockedLessonIds($lessons, $user)
    {
        $unlockedIds = [];
        
        foreach ($lessons as $index => $lesson) {
            if ($index === 0) {
                // First lesson is always unlocked
                $unlockedIds[] = $lesson->id;
            } else {
                // Check if the previous lesson has a quiz that the user has attempted
                $previousLesson = $lessons[$index - 1];
                $previousQuiz = $previousLesson->quiz;
                
                if ($previousQuiz) {
                    $hasAttempted = QuizAttempt::where('quiz_id', $previousQuiz->id)
                        ->where('user_id', $user->id)
                        ->exists();
                    
                    if ($hasAttempted) {
                        $unlockedIds[] = $lesson->id;
                    }
                } else {
                    // If previous lesson has no quiz, it's automatically unlocked
                    $unlockedIds[] = $lesson->id;
                }
            }
        }
        
        return $unlockedIds;
    }
}
