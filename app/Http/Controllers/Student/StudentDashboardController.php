<?php

namespace App\Http\Controllers\Student;

use App\Http\Controllers\Controller;
use App\Models\QuizAttempt;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;

class StudentDashboardController extends Controller
{
    public function index()
    {
        $user = Auth::user();
        
        // Fetch courses the student is enrolled in with lessons
        $courses = $user->courses()->with(['lessons' => function ($query) {
            $query->orderBy('order');
        }])->get();
        
        // Bulk load all attempted quiz IDs for this user in one query
        $attemptedQuizIds = QuizAttempt::where('user_id', $user->id)
            ->pluck('quiz_id')
            ->toArray();
        
        // Calculate overall statistics
        $totalCourses = $courses->count();
        $totalLessons = 0;
        $completedLessons = 0;
        
        foreach ($courses as $course) {
            $courseLessons = $course->lessons;
            $totalLessons += $courseLessons->count();
            
            // Get completed lessons for this course (based on quiz completion)
            $courseCompletedLessons = $courseLessons->filter(function ($lesson) use ($attemptedQuizIds) {
                // Lesson is complete if:
                // 1. It has a quiz and user has attempted it (record exists)
                // 2. OR it has no quiz (automatically complete)
                if ($lesson->quiz) {
                    return in_array($lesson->quiz->id, $attemptedQuizIds);
                }
                return true;
            });
            
            $completedLessons += $courseCompletedLessons->count();
            
            // Calculate course progress percentage
            $courseProgress = $courseLessons->count() > 0 
                ? round(($courseCompletedLessons->count() / $courseLessons->count()) * 100) 
                : 0;
            
            // Attach progress data to course
            $course->progress_percentage = $courseProgress;
            $course->completed_lessons = $courseCompletedLessons->count();
        }
        
        // Calculate overall progress
        $overallProgress = $totalLessons > 0 
            ? round(($completedLessons / $totalLessons) * 100) 
            : 0;
        
        Log::info('Student viewed dashboard', [
            'student_id' => $user->id,
            'student_email' => $user->email,
            'total_courses' => $totalCourses,
            'total_lessons' => $totalLessons,
            'completed_lessons' => $completedLessons,
            'overall_progress' => $overallProgress,
        ]);
        
        return view('student.dashboard', compact(
            'courses',
            'totalCourses',
            'totalLessons',
            'completedLessons',
            'overallProgress'
        ));
    }
}
