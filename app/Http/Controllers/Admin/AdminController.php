<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\User;
use App\Models\Course;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

class AdminController extends Controller
{
    public function dashboard()
    {
        $totalStudents = User::where('role', 'student')->count();
        $totalCourses = Course::count();
        
        $recentStudents = User::where('role', 'student')->latest()->take(5)->get();

        Log::info('Admin viewed dashboard', [
            'admin_id' => auth()->id(),
            'admin_email' => auth()->user()?->email,
            'total_students' => $totalStudents,
            'total_courses' => $totalCourses,
        ]);

        return view('admin.dashboard', compact('totalStudents', 'totalCourses', 'recentStudents'));
    }
}
