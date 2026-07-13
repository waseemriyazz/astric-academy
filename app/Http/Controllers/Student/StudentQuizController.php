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

        // Prevent re-attempting if already attempted
        $existingAttempt = \App\Models\QuizAttempt::where('quiz_id', $quiz->id)
            ->where('user_id', $user->id)
            ->first();

        if ($existingAttempt) {
            return response()->json([
                'success' => true,
                'is_correct' => $existingAttempt->is_correct,
                'correct_answer' => $quiz->correct_answer,
                'message' => $existingAttempt->is_correct ? 'You already answered correctly!' : 'You already attempted this quiz.',
                'attempt_id' => $existingAttempt->id,
            ]);
        }

        $validated = $request->validate([
            'selected_answer' => 'required|in:a,b,c,d',
        ]);

        $isCorrect = $validated['selected_answer'] === $quiz->correct_answer;

        // Create attempt record
        $attempt = $quiz->attempts()->create([
            'user_id' => $user->id,
            'selected_answer' => $validated['selected_answer'],
            'is_correct' => $isCorrect,
        ]);

        // Auto-generate certificate if student just completed all lessons
        $certificateGenerated = false;
        $certificateUrl = null;
        if ($isCorrect) {
            $allComplete = StudentCertificateController::isCourseComplete($course, $user);
            if ($allComplete) {
                $existingCert = \App\Models\Certificate::where('user_id', $user->id)
                    ->where('course_id', $course->id)
                    ->first();

                if (!$existingCert || $existingCert->is_revoked) {
                    $cert = \App\Models\Certificate::create([
                        'user_id' => $user->id,
                        'course_id' => $course->id,
                        'serial_number' => \App\Models\Certificate::generateSerialNumber(),
                        'completed_at' => now(),
                        'issued_at' => now(),
                    ]);
                    $certificateGenerated = true;
                    $certificateUrl = route('student.certificates.download', $course->id);
                }
            }
        }

        return response()->json([
            'success' => true,
            'is_correct' => $isCorrect,
            'correct_answer' => $quiz->correct_answer,
            'message' => $isCorrect ? 'Correct answer!' : 'Incorrect answer. Try again!',
            'attempt_id' => $attempt->id,
            'certificate_generated' => $certificateGenerated,
            'certificate_url' => $certificateUrl,
        ]);
    }

    public function play(Course $course, Lesson $lesson)
    {
        $user = Auth::user();

        // Verify enrollment
        if (!$user->courses()->where('courses.id', $course->id)->exists()) {
            abort(403, 'Not enrolled in this course.');
        }

        // Verify lesson belongs to course
        if ($lesson->course_id !== $course->id) {
            abort(404, 'Lesson not found in this course.');
        }

        // Check if lesson has a quiz
        $quiz = $lesson->quiz;
        if (!$quiz) {
            abort(404, 'No quiz for this lesson.');
        }

        // Redirect if already attempted
        $alreadyAttempted = \App\Models\QuizAttempt::where('quiz_id', $quiz->id)
            ->where('user_id', $user->id)
            ->exists();

        if ($alreadyAttempted) {
            return redirect()->route('student.courses.show', [$course->id, $lesson->id])
                ->with('error', 'You have already attempted this quiz.');
        }

        return view('student.quizzes.play', compact('course', 'lesson', 'quiz'));
    }

    public function result(Course $course, Lesson $lesson, $attempt)
    {
        $user = Auth::user();

        // Verify enrollment
        if (!$user->courses()->where('courses.id', $course->id)->exists()) {
            abort(403, 'Not enrolled in this course.');
        }

        // Verify lesson belongs to course
        if ($lesson->course_id !== $course->id) {
            abort(404, 'Lesson not found in this course.');
        }

        // Get the attempt
        $attempt = \App\Models\QuizAttempt::findOrFail($attempt);
        
        // Verify the attempt belongs to the current user
        if ($attempt->user_id !== $user->id) {
            abort(403, 'Unauthorized access.');
        }

        // Verify the attempt is for this quiz
        $quiz = $lesson->quiz;
        if (!$quiz || $attempt->quiz_id !== $quiz->id) {
            abort(404, 'Quiz not found.');
        }

        return view('student.quizzes.result', compact('course', 'lesson', 'quiz', 'attempt'));
    }
}
