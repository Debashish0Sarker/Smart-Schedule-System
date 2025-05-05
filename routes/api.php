<?php

use App\Http\Controllers\StudentController;
use App\Http\Controllers\CourseController;
use App\Http\Controllers\TestController;
use App\Http\Controllers\AdminController;
use App\Http\Controllers\Auth\AuthController;
use Illuminate\Support\Facades\Route;
use Illuminate\Http\Request;

// Test route
Route::get('/test', [TestController::class, 'test']);

// Public routes
Route::post('students/register', [StudentController::class, 'register']);
Route::post('students/login', [AuthController::class, 'studentLogin']);
Route::post('students/logout', [AuthController::class, 'logout'])->middleware('auth:sanctum');

// Authentication check endpoints
Route::get('/admin/auth-check', function (Request $request) {
    $user = $request->user();
    
    // If no user from the request, try to find from token
    if (!$user && $request->bearerToken()) {
        $token = \Laravel\Sanctum\PersonalAccessToken::findToken($request->bearerToken());
        if ($token) {
            $user = $token->tokenable;
        }
    }
    
    $isAdmin = $user && ($user->getMorphClass() === 'App\Models\Admin' || $user->role === 'admin');
    
    return response()->json([
        'authenticated' => (bool) $isAdmin,
        'user' => $isAdmin ? [
            'id' => $user->id,
            'email' => $user->email,
            'name' => $user->name,
            'role' => 'admin'
        ] : null
    ]);
});

// Student authentication check
Route::get('/student/auth-check', function (Request $request) {
    $user = $request->user();
    
    if (!$user && $request->bearerToken()) {
        $token = \Laravel\Sanctum\PersonalAccessToken::findToken($request->bearerToken());
        if ($token) {
            $user = $token->tokenable;
        }
    }
    
    $isStudent = $user && $user->getMorphClass() === 'App\Models\Student';
    
    return response()->json([
        'authenticated' => (bool) $isStudent,
        'user' => $isStudent ? [
            'id' => $user->id,
            'email' => $user->email,
            'name' => $user->name,
            'student_id' => $user->student_id ?? null
        ] : null
    ]);
});

// Protected routes
Route::middleware('auth:sanctum')->group(function () {
    // Student routes
    Route::get('students', [StudentController::class, 'index']);
    Route::get('students/courses', [StudentController::class, 'viewCourses']);
    Route::post('students/courses/{course}/enroll', [StudentController::class, 'enrollCourse']);
    Route::put('students/courses/{course}/grade', [StudentController::class, 'updateGrade']);
    Route::get('students/assignments/upcoming', [\App\Http\Controllers\AssignmentController::class, 'getUpcomingAssignments']);
    Route::get('students/assignments', [\App\Http\Controllers\AssignmentController::class, 'getStudentAssignments']);
    Route::get('students/assignments/course/{course}', [\App\Http\Controllers\AssignmentController::class, 'getStudentCourseAssignments']);
    Route::post('students/assignments/{assignment}/complete', [\App\Http\Controllers\AssignmentController::class, 'markAsComplete']);
    Route::get('students/notifications', [\App\Http\Controllers\NotificationController::class, 'index']);
    Route::post('students/notifications/mark-read', [\App\Http\Controllers\NotificationController::class, 'markAllAsRead']);
    Route::get('students/progress/summary', [\App\Http\Controllers\ProgressReportController::class, 'getProgressSummary']);
    Route::get('students/progress/reports/{type}', [\App\Http\Controllers\ProgressReportController::class, 'getReportsByType']);

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
