<?php

use App\Http\Controllers\Api\CourseController;
use App\Http\Controllers\Api\TestimonialController;
use App\Http\Controllers\Api\FaqController;
use App\Http\Controllers\Api\PaymentController;
use Illuminate\Support\Facades\Route;

Route::get('/courses', [CourseController::class, 'index']);
Route::get('/courses/{course}', [CourseController::class, 'show']);
Route::get('/testimonials', [TestimonialController::class, 'index']);
Route::get('/faqs', [FaqController::class, 'index']);

// Payment routes
Route::post('/payment/initiate', [PaymentController::class, 'initiate'])->middleware('throttle:10,1');
Route::post('/payment/success', [PaymentController::class, 'success'])->name('payment.success')->middleware('throttle:20,1');
Route::post('/payment/failure', [PaymentController::class, 'failure'])->name('payment.failure')->middleware('throttle:20,1');
Route::post('/payment/webhook', [PaymentController::class, 'webhook'])->name('payment.webhook')->middleware('throttle:30,1');
