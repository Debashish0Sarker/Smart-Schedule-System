<?php

use App\Http\Controllers\StudentController;
use App\Http\Controllers\CourseController;
use App\Http\Controllers\TestController;
use App\Http\Controllers\AdminController;
use App\Http\Controllers\AuthController;
use Illuminate\Support\Facades\Route;

// Test route
Route::get('/test', [TestController::class, 'test']);

// Public routes
Route::post('students/register', [StudentController::class, 'register']);

// Protected routes
Route::middleware('auth:sanctum')->group(function () {
    // Student routes
    Route::get('students', [StudentController::class, 'index']);
    Route::get('students/courses', [StudentController::class, 'viewCourses']);
    Route::post('students/courses/{course}/enroll', [StudentController::class, 'enrollCourse']);
    Route::put('students/courses/{course}/grade', [StudentController::class, 'updateGrade']);

    // Course routes
    Route::get('courses', [CourseController::class, 'index']);
    Route::post('courses', [CourseController::class, 'store']);
    Route::get('courses/{course}', [CourseController::class, 'show']);
    Route::get('courses/{course}/students', [CourseController::class, 'getEnrolledStudents']);
    Route::put('courses/{course}/grades', [CourseController::class, 'updateGrades']);
});

// Admin Auth routes
Route::post('admin/login', [AuthController::class, 'adminLogin']);
Route::post('admin/logout', [AuthController::class, 'adminLogout'])->middleware('auth:sanctum');

// Admin routes
Route::middleware(['auth:sanctum', 'admin'])->prefix('admin')->group(function () {
    Route::get('/students/search', [AdminController::class, 'searchStudents']);
    Route::post('/students/{student}/approve', [AdminController::class, 'approveUser']);
    Route::delete('/students/{student}', [AdminController::class, 'deleteUser']);
    Route::get('/complaints', [AdminController::class, 'getComplaints']);
    Route::post('/complaints/{complaint}/reply', [AdminController::class, 'replyToComplaint']);
    Route::get('/students/category', [AdminController::class, 'getUsersByCategory']);
});
