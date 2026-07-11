<?php

use App\Http\Controllers\ProfileController;
use Illuminate\Support\Facades\Route;

Route::redirect('/', '/login');

// Redirect the default dashboard to the admin dashboard so nobody gets lost
Route::redirect('/dashboard', '/admin/dashboard')->name('dashboard');

// We are removing the default Breeze profile routes as requested
// Default Laravel pages are being eliminated

Route::middleware(['auth'])->group(function () {
    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');
});

require __DIR__.'/auth.php';

Route::middleware(['auth', 'is_admin'])->prefix('admin')->name('admin.')->group(function () {
    Route::get('/dashboard', [\App\Http\Controllers\Admin\AdminController::class, 'dashboard'])->name('dashboard');
    
    Route::get('/enrollments', [\App\Http\Controllers\Admin\AdminEnrollmentController::class, 'index'])->name('enrollments.index');
    Route::get('/enrollments/create', [\App\Http\Controllers\Admin\AdminEnrollmentController::class, 'create'])->name('enrollments.create');
    Route::post('/enrollments', [\App\Http\Controllers\Admin\AdminEnrollmentController::class, 'store'])->name('enrollments.store');
    Route::delete('/enrollments/{user}/{course}', [\App\Http\Controllers\Admin\AdminEnrollmentController::class, 'destroy'])->name('enrollments.destroy');
    
    Route::get('/users', [\App\Http\Controllers\Admin\AdminUserController::class, 'index'])->name('users.index');
    Route::post('/users', [\App\Http\Controllers\Admin\AdminUserController::class, 'store'])->name('users.store');
    Route::get('/users/{user}/edit', [\App\Http\Controllers\Admin\AdminUserController::class, 'edit'])->name('users.edit');
    Route::put('/users/{user}', [\App\Http\Controllers\Admin\AdminUserController::class, 'update'])->name('users.update');
    Route::delete('/users/{user}', [\App\Http\Controllers\Admin\AdminUserController::class, 'destroy'])->name('users.destroy');
    
    Route::get('/courses', [\App\Http\Controllers\Admin\AdminCourseController::class, 'index'])->name('courses.index');
    Route::get('/courses/create', [\App\Http\Controllers\Admin\AdminCourseController::class, 'create'])->name('courses.create');
    Route::post('/courses', [\App\Http\Controllers\Admin\AdminCourseController::class, 'store'])->name('courses.store');
    Route::get('/courses/{course}/edit', [\App\Http\Controllers\Admin\AdminCourseController::class, 'edit'])->name('courses.edit');
    Route::put('/courses/{course}', [\App\Http\Controllers\Admin\AdminCourseController::class, 'update'])->name('courses.update');
    Route::delete('/courses/{course}', [\App\Http\Controllers\Admin\AdminCourseController::class, 'destroy'])->name('courses.destroy');
    
    // Lessons
    Route::get('/courses/{course}/lessons', [\App\Http\Controllers\Admin\AdminLessonController::class, 'index'])->name('courses.lessons.index');
    Route::post('/courses/{course}/lessons', [\App\Http\Controllers\Admin\AdminLessonController::class, 'store'])->name('courses.lessons.store');
    Route::get('/courses/{course}/lessons/{lesson}/edit', [\App\Http\Controllers\Admin\AdminLessonController::class, 'edit'])->name('courses.lessons.edit');
    Route::put('/courses/{course}/lessons/{lesson}', [\App\Http\Controllers\Admin\AdminLessonController::class, 'update'])->name('courses.lessons.update');
    Route::delete('/courses/{course}/lessons/{lesson}', [\App\Http\Controllers\Admin\AdminLessonController::class, 'destroy'])->name('courses.lessons.destroy');
});

Route::middleware(['auth'])->prefix('student')->name('student.')->group(function () {
    Route::get('/dashboard', [\App\Http\Controllers\Student\StudentDashboardController::class, 'index'])->name('dashboard');
    Route::get('/courses/{course}/learn/{lesson?}', [\App\Http\Controllers\Student\StudentCourseController::class, 'show'])->name('courses.show');
    Route::post('/courses/{course}/chat/{lesson}', [\App\Http\Controllers\Student\ChatBotController::class, 'sendMessage'])->name('courses.chat');
});
