<?php

namespace App\Http\Controllers\Student;

use App\Http\Controllers\Controller;
use App\Models\Certificate;
use App\Models\Course;
use App\Models\Lesson;
use App\Models\QuizAttempt;
use App\Services\CertificateService;
use App\Services\EnrollmentService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class StudentQuizController extends Controller
{
    public function __construct(
        protected EnrollmentService $enrollmentService,
        protected CertificateService $certificateService,
    ) {}

    public function attempt(Request $request, Course $course, Lesson $lesson)
    {
        $user = Auth::user();

        if (!$this->enrollmentService->isEnrolled($user, $course)) {
            return response()->json(['error' => 'Not enrolled in this course.'], 403);
        }

        if (!$this->enrollmentService->verifyLessonBelongsToCourse($course, $lesson)) {
            return response()->json(['error' => 'Lesson not found in this course.'], 404);
        }

        $quiz = $lesson->quiz;
        if (!$quiz) {
            return response()->json(['error' => 'No quiz for this lesson.'], 404);
        }

        $existingAttempt = QuizAttempt::where('quiz_id', $quiz->id)
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

        DB::beginTransaction();
        try {
            $attempt = $quiz->attempts()->create([
                'user_id' => $user->id,
                'selected_answer' => $validated['selected_answer'],
                'is_correct' => $isCorrect,
            ]);

            Log::info('Student attempted quiz', [
                'student_id' => $user->id,
                'student_email' => $user->email,
                'course_id' => $course->id,
                'lesson_id' => $lesson->id,
                'quiz_id' => $quiz->id,
                'is_correct' => $isCorrect,
                'selected_answer' => $validated['selected_answer'],
            ]);

            // Auto-generate certificate if student just completed all lessons
            $certificateGenerated = false;
            $certificateUrl = null;
            if ($isCorrect) {
                $allComplete = $this->certificateService->isCourseComplete($course, $user);
                if ($allComplete) {
                    $existingCert = $this->certificateService->getExistingCertificate($user, $course);

                    if (!$existingCert || $existingCert->is_revoked) {
                        $cert = $this->certificateService->generateCertificate($user, $course);
                        $certificateGenerated = true;
                        $certificateUrl = route('student.certificates.download', $course->id);

                        Log::info('Certificate auto-generated for student', [
                            'student_id' => $user->id,
                            'student_email' => $user->email,
                            'course_id' => $course->id,
                            'certificate_id' => $cert->id,
                            'serial_number' => $cert->serial_number,
                        ]);
                    }
                }
            }

            DB::commit();
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Quiz attempt failed', [
                'student_id' => $user->id,
                'course_id' => $course->id,
                'lesson_id' => $lesson->id,
                'error' => $e->getMessage(),
            ]);
            return response()->json(['error' => 'An error occurred processing your quiz attempt.'], 500);
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

        if (!$this->enrollmentService->isEnrolled($user, $course)) {
            abort(403, 'Not enrolled in this course.');
        }

        if (!$this->enrollmentService->verifyLessonBelongsToCourse($course, $lesson)) {
            abort(404, 'Lesson not found in this course.');
        }

        $quiz = $lesson->quiz;
        if (!$quiz) {
            abort(404, 'No quiz for this lesson.');
        }

        $alreadyAttempted = QuizAttempt::where('quiz_id', $quiz->id)
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

        if (!$this->enrollmentService->isEnrolled($user, $course)) {
            abort(403, 'Not enrolled in this course.');
        }

        if (!$this->enrollmentService->verifyLessonBelongsToCourse($course, $lesson)) {
            abort(404, 'Lesson not found in this course.');
        }

        $attempt = QuizAttempt::findOrFail($attempt);

        if ($attempt->user_id !== $user->id) {
            abort(403, 'Unauthorized access.');
        }

        $quiz = $lesson->quiz;
        if (!$quiz || $attempt->quiz_id !== $quiz->id) {
            abort(404, 'Quiz not found.');
        }

        return view('student.quizzes.result', compact('course', 'lesson', 'quiz', 'attempt'));
    }
}
