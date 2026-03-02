<?php

use App\Http\Controllers\EnrollmentController;
use Illuminate\Support\Facades\Route;

// Enrollment API Routes
Route::get('/enrollments', [EnrollmentController::class, 'index']);
Route::post('/enrollments', [EnrollmentController::class, 'store']);
