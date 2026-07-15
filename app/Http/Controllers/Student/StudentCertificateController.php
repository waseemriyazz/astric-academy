<?php

namespace App\Http\Controllers\Student;

use App\Http\Controllers\Controller;
use App\Models\Course;
use App\Services\CertificateService;
use App\Services\EnrollmentService;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;

class StudentCertificateController extends Controller
{
    public function __construct(
        protected CertificateService $certificateService,
        protected EnrollmentService $enrollmentService,
    ) {}

    public function index()
    {
        $user = Auth::user();
        $certificates = $this->certificateService->getStudentCertificatesData($user);

        return view('student.certificates.index', compact('certificates'));
    }

    public function show(Course $course)
    {
        $user = Auth::user();

        if (!$this->enrollmentService->isEnrolled($user, $course)) {
            abort(403, 'You are not enrolled in this course.');
        }

        $certificate = $this->certificateService->getExistingCertificate($user, $course);

        if (!$certificate || $certificate->is_revoked) {
            abort(404, 'Certificate not found or has been revoked.');
        }

        return view('student.certificates.show', compact('certificate', 'course'));
    }

    public function download(Course $course)
    {
        $user = Auth::user();

        if (!$this->enrollmentService->isEnrolled($user, $course)) {
            abort(403, 'You are not enrolled in this course.');
        }

        $certificate = $this->certificateService->getExistingCertificate($user, $course);

        if (!$certificate || $certificate->is_revoked) {
            abort(404, 'Certificate not found or has been revoked.');
        }

        return $this->certificateService->generatePdf($certificate);
    }

    public function checkAndGenerate(Course $course)
    {
        $user = Auth::user();

        if (!$this->enrollmentService->isEnrolled($user, $course)) {
            abort(403);
        }

        $existing = $this->certificateService->getExistingCertificate($user, $course);

        if ($existing && !$existing->is_revoked) {
            return redirect()->route('student.certificates.show', $course->id)
                ->with('success', 'Your certificate is already available.');
        }

        if (!$this->certificateService->isCourseComplete($course, $user)) {
            return redirect()->route('student.certificates.index')
                ->with('error', 'Complete all lessons to earn your certificate.');
        }

        $certificate = $this->certificateService->generateCertificate($user, $course);

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
}
