<?php

use App\Http\Controllers\StudentController;
use App\Http\Controllers\CourseController;
use App\Http\Controllers\TestController;
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
