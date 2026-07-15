<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Certificate;
use App\Models\Course;
use App\Models\User;
use App\Services\CertificateService;
use App\Services\EnrollmentService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

class AdminCertificateController extends Controller
{
    public function __construct(
        protected CertificateService $certificateService,
        protected EnrollmentService $enrollmentService,
    ) {}

    public function index()
    {
        $certificates = Certificate::with(['user', 'course'])
            ->orderBy('created_at', 'desc')
            ->paginate(20);

        $courses = Course::all();
        $students = User::where('role', '!=', 'admin')->orWhereNull('role')->get();

        return view('admin.certificates.index', compact('certificates', 'courses', 'students'));
    }

    public function issue(Request $request)
    {
        $request->validate([
            'user_id' => 'required|exists:users,id',
            'course_id' => 'required|exists:courses,id',
        ]);

        $user = User::findOrFail($request->user_id);
        $course = Course::findOrFail($request->course_id);

        $existing = $this->certificateService->getExistingCertificate($user, $course);

        if ($existing) {
            if ($existing->is_revoked) {
                $existing->update([
                    'is_revoked' => false,
                    'issued_at' => now(),
                    'serial_number' => Certificate::generateSerialNumber(),
                ]);

                Log::info('Admin re-issued certificate', [
                    'admin_id' => auth()->id(),
                    'certificate_id' => $existing->id,
                    'serial_number' => $existing->serial_number,
                    'student_id' => $user->id,
                    'course_id' => $course->id,
                ]);

                return redirect()->route('admin.certificates.index')
                    ->with('success', "Certificate re-issued for {$user->name} - {$course->title}");
            }

            return redirect()->route('admin.certificates.index')
                ->with('error', 'Certificate already exists for this student and course.');
        }

        $certificate = $this->certificateService->generateCertificate($user, $course);

        Log::info('Admin issued certificate', [
            'admin_id' => auth()->id(),
            'certificate_id' => $certificate->id,
            'serial_number' => $certificate->serial_number,
            'student_id' => $user->id,
            'course_id' => $course->id,
        ]);

        return redirect()->route('admin.certificates.index')
            ->with('success', "Certificate issued successfully! Serial: {$certificate->serial_number}");
    }

    public function issueAuto(Course $course, User $user)
    {
        if (!$this->certificateService->isCourseComplete($course, $user)) {
            return redirect()->route('admin.certificates.index')
                ->with('error', 'Student has not completed all lessons in this course.');
        }

        $existing = $this->certificateService->getExistingCertificate($user, $course);

        if ($existing && !$existing->is_revoked) {
            return redirect()->route('admin.certificates.index')
                ->with('error', 'Certificate already exists.');
        }

        $certificate = $this->certificateService->generateCertificate($user, $course);

        Log::info('Admin auto-issued certificate', [
            'admin_id' => auth()->id(),
            'certificate_id' => $certificate->id,
            'serial_number' => $certificate->serial_number,
            'student_id' => $user->id,
            'course_id' => $course->id,
        ]);

        return redirect()->route('admin.certificates.index')
            ->with('success', "Certificate issued automatically! Serial: {$certificate->serial_number}");
    }

    public function revoke(Certificate $certificate)
    {
        $certificate->update(['is_revoked' => true]);

        Log::info('Admin revoked certificate', [
            'admin_id' => auth()->id(),
            'certificate_id' => $certificate->id,
            'serial_number' => $certificate->serial_number,
            'student_id' => $certificate->user_id,
            'course_id' => $certificate->course_id,
        ]);

        return redirect()->route('admin.certificates.index')
            ->with('success', "Certificate ({$certificate->serial_number}) has been revoked.");
    }

    public function download(Certificate $certificate)
    {
        return $this->certificateService->generatePdf($certificate);
    }

    public function updateConfig(Request $request, Course $course)
    {
        $request->validate([
            'signature_name' => 'nullable|string|max:255',
            'signature_title' => 'nullable|string|max:255',
        ]);

        $course->update([
            'certificate_config' => [
                'signature_name' => $request->signature_name ?? 'Astryx Academy',
                'signature_title' => $request->signature_title ?? 'Authorized Signature',
            ],
        ]);

        Log::info('Admin updated certificate config', [
            'admin_id' => auth()->id(),
            'course_id' => $course->id,
            'signature_name' => $request->signature_name,
        ]);

        return redirect()->route('admin.courses.edit', $course->id)
            ->with('success', 'Certificate configuration updated successfully.');
    }
}
