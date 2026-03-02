<?php

use App\Http\Controllers\StudentController;
use Illuminate\Support\Facades\Route;

// Student API Routes
Route::get('/students', [StudentController::class, 'index']);
Route::get('/students/{id}', [StudentController::class, 'show']);
Route::post('/students', [StudentController::class, 'store']);
