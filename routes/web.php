<?php

use App\Http\Controllers\ProfileController;
// disabled dark mode: use Illuminate\Http\JsonResponse;
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
    // disabled dark mode
    // Route::patch('/theme', [ProfileController::class, 'updateTheme'])->name('theme.update');
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
    Route::post('/courses/{course}/quiz/{lesson}', [\App\Http\Controllers\Student\StudentQuizController::class, 'attempt'])->name('courses.quiz.attempt');
    Route::get('/courses/{course}/quiz/{lesson}/play', [\App\Http\Controllers\Student\StudentQuizController::class, 'play'])->name('quizzes.play');
    Route::get('/courses/{course}/quiz/{lesson}/result/{attempt}', [\App\Http\Controllers\Student\StudentQuizController::class, 'result'])->name('quizzes.result');
    Route::post('/courses/{course}/lesson/{lesson}/play', [\App\Http\Controllers\Student\StudentProgressController::class, 'markPlayed'])->name('courses.lesson.play');
    Route::get('/certificates', [\App\Http\Controllers\Student\StudentCertificateController::class, 'index'])->name('certificates.index');
    Route::get('/certificates/{course}', [\App\Http\Controllers\Student\StudentCertificateController::class, 'show'])->name('certificates.show');
    Route::get('/certificates/{course}/download', [\App\Http\Controllers\Student\StudentCertificateController::class, 'download'])->name('certificates.download');
    Route::post('/certificates/{course}/generate', [\App\Http\Controllers\Student\StudentCertificateController::class, 'checkAndGenerate'])->name('certificates.generate');
    Route::get('/settings', [\App\Http\Controllers\Student\StudentSettingsController::class, 'index'])->name('settings');
});

// Admin Certificate Routes
Route::middleware(['auth', 'is_admin'])->prefix('admin')->name('admin.')->group(function () {
    Route::get('/certificates', [\App\Http\Controllers\Admin\AdminCertificateController::class, 'index'])->name('certificates.index');
    Route::post('/certificates/issue', [\App\Http\Controllers\Admin\AdminCertificateController::class, 'issue'])->name('certificates.issue');
    Route::put('/certificates/{certificate}/revoke', [\App\Http\Controllers\Admin\AdminCertificateController::class, 'revoke'])->name('certificates.revoke');
    Route::get('/certificates/{certificate}/download', [\App\Http\Controllers\Admin\AdminCertificateController::class, 'download'])->name('certificates.download');
    Route::post('/courses/{course}/certificate-config', [\App\Http\Controllers\Admin\AdminCertificateController::class, 'updateConfig'])->name('courses.certificate-config');
});
