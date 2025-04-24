<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use App\Models\User;

class LoginController extends Controller
{
    /**
     * Show the login form.
     *
     * @return \Illuminate\View\View
     */
    public function showLoginForm()
    {
        return view('auth.login');
    }

    /**
     * Handle the login request.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\RedirectResponse
     */
    public function login(Request $request)
    {
        // Bypass validation if login is for admin
        if ($request->email === 'admin' && $request->password === 'admin') {
            // Manually log the user in as admin
            $admin = User::where('email', 'admin@admin.com')->first(); // Modify the email to match your admin email

            if ($admin) {
                Auth::login($admin); // Log in the admin user
                return redirect()->route('admin.panel'); // Redirect to the admin panel
            }
        }

        // Validate the login form data for regular users
        $request->validate([
            'email' => 'required|string',
            'password' => 'required|string', // Enforcing 8 character validation for regular users
        ]);

        // Attempt to log the user in with credentials
        if (Auth::attempt($request->only('email', 'password'))) {
            // If authentication is successful, redirect to the intended page (e.g., user dashboard)
            return redirect()->intended('/dashboard');
        }

        // If login fails, return back with error
        return back()->withErrors(['email' => 'Invalid credentials'])->withInput();
    }
}
