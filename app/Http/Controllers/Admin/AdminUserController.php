<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\User;
use App\Models\Course;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Log;
use Illuminate\Validation\Rules;

class AdminUserController extends Controller
{
    public function index()
    {
        $students = User::where('role', 'student')->with('courses')->latest()->paginate(10);
        $courses = Course::all();
        return view('admin.users.index', compact('students', 'courses'));
    }

    public function store(Request $request)
    {
        $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'string', 'lowercase', 'email', 'max:255', 'unique:'.User::class],
            'password' => ['required', Rules\Password::defaults()],
            'courses' => ['array'],
            'courses.*' => ['exists:courses,id']
        ]);

        $user = User::create([
            'name' => $request->name,
            'email' => $request->email,
            'password' => Hash::make($request->password),
            'role' => 'student',
        ]);

        if ($request->has('courses')) {
            $user->courses()->attach($request->courses);
        }

        Log::info('Admin registered student', [
            'admin_id' => auth()->id(),
            'student_id' => $user->id,
            'student_email' => $user->email,
            'assigned_courses' => $request->courses ?? [],
        ]);

        return redirect()->route('admin.users.index')->with('success', 'Student registered and courses assigned successfully.');
    }

    public function edit(User $user)
    {
        $courses = Course::all();
        return view('admin.users.edit', compact('user', 'courses'));
    }

    public function update(Request $request, User $user)
    {
        $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'string', 'lowercase', 'email', 'max:255', 'unique:'.User::class.',email,'.$user->id],
            'password' => ['nullable', Rules\Password::defaults()],
            'courses' => ['array'],
            'courses.*' => ['exists:courses,id']
        ]);

        $user->name = $request->name;
        $user->email = $request->email;
        if ($request->filled('password')) {
            $user->password = Hash::make($request->password);
        }
        $user->save();

        if ($request->has('courses')) {
            $user->courses()->sync($request->courses);
        } else {
            $user->courses()->detach();
        }

        Log::info('Admin updated student', [
            'admin_id' => auth()->id(),
            'student_id' => $user->id,
            'student_email' => $user->email,
            'assigned_courses' => $request->courses ?? [],
        ]);

        return redirect()->route('admin.users.index')->with('success', 'Student updated successfully.');
    }

    public function destroy(User $user)
    {
        $userId = $user->id;
        $userEmail = $user->email;
        $user->delete();

        Log::info('Admin deleted student', [
            'admin_id' => auth()->id(),
            'student_id' => $userId,
            'student_email' => $userEmail,
        ]);

        return redirect()->route('admin.users.index')->with('success', 'Student deleted successfully.');
    }
}
