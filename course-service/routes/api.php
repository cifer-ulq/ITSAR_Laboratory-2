<?php

use App\Http\Controllers\CourseController;
use Illuminate\Support\Facades\Route;

// Course API Routes
Route::get('/courses', [CourseController::class, 'index']);
Route::get('/courses/{id}', [CourseController::class, 'show']);
Route::post('/courses', [CourseController::class, 'store']);
