<?php

namespace App\Http\Controllers\Student;

use App\Http\Controllers\Controller;
use App\Models\Certificate;
use App\Models\Course;
use App\Models\QuizAttempt;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;

class StudentCertificateController extends Controller
{
    public function index()
    {
        $user = Auth::user();
        $courses = $user->courses()->with('lessons.quiz')->get();

        $certificates = [];
        foreach ($courses as $course) {
            $certificate = Certificate::where('user_id', $user->id)
                ->where('course_id', $course->id)
                ->first();

            $totalLessons = $course->lessons->count();
            $completedLessons = 0;

            foreach ($course->lessons as $lesson) {
                if ($lesson->quiz) {
                    $hasPassed = QuizAttempt::where('quiz_id', $lesson->quiz->id)
                        ->where('user_id', $user->id)
                        ->where('is_correct', true)
                        ->exists();
                    if ($hasPassed) {
                        $completedLessons++;
                    }
                } else {
                    $completedLessons++;
                }
            }

            $allComplete = $totalLessons > 0 && $completedLessons === $totalLessons;

            $certificates[] = [
                'course' => $course,
                'certificate' => $certificate,
                'total_lessons' => $totalLessons,
                'completed_lessons' => $completedLessons,
                'all_complete' => $allComplete,
            ];
        }

        return view('student.certificates.index', compact('certificates'));
    }

    public function show(Course $course)
    {
        $user = Auth::user();

        if (!$user->courses()->where('courses.id', $course->id)->exists()) {
            abort(403, 'You are not enrolled in this course.');
        }

        $certificate = Certificate::where('user_id', $user->id)
            ->where('course_id', $course->id)
            ->first();

        if (!$certificate || $certificate->is_revoked) {
            abort(404, 'Certificate not found or has been revoked.');
        }

        return view('student.certificates.show', compact('certificate', 'course'));
    }

    public function download(Course $course)
    {
        $user = Auth::user();

        if (!$user->courses()->where('courses.id', $course->id)->exists()) {
            abort(403, 'You are not enrolled in this course.');
        }

        $certificate = Certificate::where('user_id', $user->id)
            ->where('course_id', $course->id)
            ->first();

        if (!$certificate || $certificate->is_revoked) {
            abort(404, 'Certificate not found or has been revoked.');
        }

        $config = $course->certificate_config ?? [];
        $logoPath = public_path('images/logo-1.jpeg');

        $pdf = Pdf::loadView('pdf.certificate', [
            'certificate' => $certificate,
            'course' => $course,
            'user' => $user,
            'config' => $config,
            'logoPath' => $logoPath,
        ]);

        $pdf->setPaper('a4', 'landscape');

        $filename = 'certificate-' . \Illuminate\Support\Str::slug($course->title) . '-' . $certificate->serial_number . '.pdf';

        return $pdf->download($filename);
    }

    public function checkAndGenerate(Course $course)
    {
        $user = Auth::user();

        if (!$user->courses()->where('courses.id', $course->id)->exists()) {
            abort(403);
        }

        // Check if certificate already exists
        $existing = Certificate::where('user_id', $user->id)
            ->where('course_id', $course->id)
            ->first();

        if ($existing && !$existing->is_revoked) {
            return redirect()->route('student.certificates.show', $course->id)
                ->with('success', 'Your certificate is already available.');
        }

        // Check if all lessons are complete
        $allComplete = $this->isCourseComplete($course, $user);
        if (!$allComplete) {
            return redirect()->route('student.certificates.index')
                ->with('error', 'Complete all lessons to earn your certificate.');
        }

        // Generate certificate
        $certificate = Certificate::create([
            'user_id' => $user->id,
            'course_id' => $course->id,
            'serial_number' => Certificate::generateSerialNumber(),
            'completed_at' => now(),
            'issued_at' => now(),
        ]);

        Log::info('Student generated certificate', [
            'student_id' => $user->id,
            'student_email' => $user->email,
            'course_id' => $course->id,
            'certificate_id' => $certificate->id,
            'serial_number' => $certificate->serial_number,
        ]);

        return redirect()->route('student.certificates.show', $course->id)
            ->with('success', "Congratulations! Your certificate has been generated (Serial: {$certificate->serial_number}).");
    }

    public static function isCourseComplete($course, $user): bool
    {
        $lessons = $course->lessons;
        if ($lessons->isEmpty()) {
            return false;
        }

        foreach ($lessons as $lesson) {
            if ($lesson->quiz) {
                $hasPassed = QuizAttempt::where('quiz_id', $lesson->quiz->id)
                    ->where('user_id', $user->id)
                    ->where('is_correct', true)
                    ->exists();

                if (!$hasPassed) {
                    return false;
                }
            }
        }

        return true;
    }
}