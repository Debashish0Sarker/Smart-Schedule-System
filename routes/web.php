<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Auth\AuthController;

/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
|
| Here is where you can register web routes for your application. These
| routes are loaded by the RouteServiceProvider within a group which
| contains the "web" middleware group. Now create something great!
|
*/

Route::get('/', function () {
    return view('home');
});

// Admin Routes
Route::prefix('admin')->group(function () {
    // Public admin routes
    Route::get('/login', function () {
        // If already authenticated as admin, redirect to dashboard
        if (auth()->guard('admin')->check()) {
            return redirect()->route('admin.dashboard');
        }
        return view('admin.login');
    })->name('admin.login');
    
    // Admin login submission route
    Route::post('/login', [AuthController::class, 'webAdminLogin'])->name('admin.login.submit');

    // Protected admin routes - allow both session and token auth
    Route::middleware(['auth:admin,admin-api'])->group(function () {
        Route::get('/dashboard', function () {
            return view('admin.dashboard');
        })->name('admin.dashboard');
    });
});

// Student Routes
Route::prefix('student')->group(function () {
    Route::get('/login', function () {
        if (auth()->guard('student')->check()) {
            return redirect()->route('student.dashboard');
        }
        return view('student.login');
    })->name('student.login');
    
    Route::get('/register', function () {
        return view('student.register');
    })->middleware('guest')->name('student.register');
    
    // Student login submission route
    Route::post('/login', [AuthController::class, 'webStudentLogin'])->name('student.login.submit');

    // Protected student routes - allow both session and token auth
    Route::middleware(['auth:student,student-api'])->group(function () {
        Route::get('/dashboard', function () {
            return view('student.dashboard');
        })->name('student.dashboard');
    });
});

// Handle CSRF token refresh
Route::get('/refresh-csrf', function () {
    return response()->json(['token' => csrf_token()]);
});

// Development-only debugging route
if (config('app.debug')) {
    Route::get('/auth-debug', function () {
        return response()->json([
            'user' => auth()->check() ? auth()->user() : null,
            'is_authenticated' => auth()->check(),
            'guard' => auth()->getDefaultDriver(),
            'guards' => [
                'admin' => auth()->guard('admin')->check(),
                'student' => auth()->guard('student')->check(),
                'admin-api' => auth()->guard('admin-api')->check(),
                'student-api' => auth()->guard('student-api')->check(),
            ]
        ]);
    });
}
