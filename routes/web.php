<?php
use App\Http\Controllers\AdminPanelController;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Auth\LoginController;
use App\Http\Controllers\Auth\RegisterController;
use App\Http\Controllers\DashboardController;
use Illuminate\Support\Facades\Auth;
use App\Http\Controllers\NotificationController;

// Login Page Routes
Route::get('/', [LoginController::class, 'showLoginForm'])->name('login'); // Homepage is login page
Route::post('/login', [LoginController::class, 'login']);

// Admin Panel Route (accessible after successful login)
Route::get('/admin/panel', [AdminPanelController::class, 'index'])->name('admin.panel')->middleware('auth', 'admin');

// Dashboard Route (protected)
Route::get('/dashboard', [DashboardController::class, 'index'])->middleware('auth');

// Logout Route
Route::get('/logout', function () {
    Auth::logout();
    return redirect('/');
})->name('logout');
// routes/web.php

Route::middleware('auth')->group(function(){
    Route::get('notifications',[NotificationController::class,'index'])
         ->name('notifications');
    Route::post('notifications/mark-all-read',[NotificationController::class,'markAllRead'])
         ->name('notifications.readAll');
});

// Registration Routes
Route::get('/register', [RegisterController::class, 'showRegistrationForm'])->name('register');
Route::post('/register', [RegisterController::class, 'register']);
//////////////////
// routes/web.php
Route::get('/notify-test', function(){
    $user = auth()->user();
    $student = (object)['id'=>123,'name'=>'Test Student'];
    $user->notify(new \App\Notifications\NewStudentRegistered($student));

    // return last 3 notifications as JSON
    return $user->notifications->take(3);
})->middleware('auth');
//////////
use App\Notifications\AssignmentDueReminder;

Route::get('/test-assignment-notification', function () {
    $user = auth()->user();

    // Dummy assignment object
    $assignment = (object)[
        'id'       => 949,
        'title'    => 'Test Assignment',
        'due_date' => now()->addDay()->toDateString(),
    ];

    $user->notify(new \App\Notifications\AssignmentDueReminder($assignment));

    return $user->notifications()->latest()->first()->data;
})->middleware('auth');
/////
