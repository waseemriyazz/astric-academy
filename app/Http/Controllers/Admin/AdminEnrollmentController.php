<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\User;
use App\Models\Course;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class AdminEnrollmentController extends Controller
{
    public function index(Request $request)
    {
        // For now, we will get all students who have enrolled in at least one course
        $students = User::where('role', 'student')->whereHas('courses')->with('courses')->paginate(10);
        
        $totalEnrollments = DB::table('course_user')->count();
        $activeEnrollments = $totalEnrollments; // Placeholder mapping
        $completed = 0; // Placeholder
        $expired = 0; // Placeholder
        $cancelled = 0; // Placeholder

        return view('admin.enrollments.index', compact(
            'students',
            'totalEnrollments',
            'activeEnrollments',
            'completed',
            'expired',
            'cancelled'
        ));
    }
    public function create()
    {
        $students = User::where('role', 'student')->get();
        $courses = Course::all();
        return view('admin.enrollments.create', compact('students', 'courses'));
    }

    public function store(Request $request)
    {
        $request->validate([
            'user_id' => 'required|exists:users,id',
            'course_id' => 'required|exists:courses,id'
        ]);

        $user = User::findOrFail($request->user_id);
        $user->courses()->syncWithoutDetaching([$request->course_id]);

        Log::info('Admin enrolled student in course', [
            'admin_id' => auth()->id(),
            'student_id' => $user->id,
            'student_email' => $user->email,
            'course_id' => $request->course_id,
        ]);

        return redirect()->route('admin.enrollments.index')->with('success', 'Student enrolled successfully.');
    }

    public function destroy(User $user, Course $course)
    {
        $user->courses()->detach($course->id);

        Log::info('Admin revoked student enrollment', [
            'admin_id' => auth()->id(),
            'student_id' => $user->id,
            'student_email' => $user->email,
            'course_id' => $course->id,
            'course_title' => $course->title,
        ]);
        
        return redirect()->route('admin.enrollments.index')->with('success', 'Enrollment revoked successfully.');
    }
}
