<?php
use App\Http\Controllers\AdminPanelController;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Auth\LoginController;
use App\Http\Controllers\Auth\RegisterController;
use Illuminate\Support\Facades\Auth;

// Login Page Routes
Route::get('/', [LoginController::class, 'showLoginForm'])->name('login'); // Homepage is login page
Route::post('/login', [LoginController::class, 'login']);

// Admin Panel Route (accessible after successful login)
Route::get('/admin/panel', [AdminPanelController::class, 'index'])->name('admin.panel')->middleware('auth', 'admin');



// Logout Route
Route::get('/logout', function () {
    Auth::logout();
    return redirect('/');
})->name('logout');

// Registration Routes
Route::get('/register', [RegisterController::class, 'showRegistrationForm'])->name('register');
Route::post('/register', [RegisterController::class, 'register']);
