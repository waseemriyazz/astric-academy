<?php

namespace App\Services;

use App\Models\Certificate;
use App\Models\Course;
use App\Models\QuizAttempt;
use App\Models\User;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Support\Str;

class CertificateService
{
    public function isCourseComplete(Course $course, User $user): bool
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

    public function getExistingCertificate(User $user, Course $course): ?Certificate
    {
        return Certificate::where('user_id', $user->id)
            ->where('course_id', $course->id)
            ->first();
    }

    public function generateCertificate(User $user, Course $course): Certificate
    {
        return Certificate::create([
            'user_id' => $user->id,
            'course_id' => $course->id,
            'serial_number' => Certificate::generateSerialNumber(),
            'completed_at' => now(),
            'issued_at' => now(),
        ]);
    }

    public function generatePdf(Certificate $certificate)
    {
        $user = $certificate->user;
        $course = $certificate->course;
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

        $filename = 'certificate-' . Str::slug($course->title) . '-' . $certificate->serial_number . '.pdf';

        return $pdf->download($filename);
    }

    public function getStudentCertificatesData(User $user): array
    {
        $courses = $user->courses()->with('lessons.quiz')->get();
        $certificates = [];

        foreach ($courses as $course) {
            $certificate = $this->getExistingCertificate($user, $course);

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

        return $certificates;
    }
}