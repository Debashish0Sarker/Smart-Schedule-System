<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;

class RegisterController extends Controller
{
    /**
     * Show the registration form.
     *
     * @return \Illuminate\View\View
     */
    public function showRegistrationForm()
    {
        return view('auth.register');
    }

    /**
     * Handle the registration of a new user.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\RedirectResponse
     */
    public function register(Request $request)
    {
        // Validate the registration form data
        $request->validate([
            'name' => 'required|string|max:255',
            'student_id' => 'required|alpha_num|min:6|unique:student', // Updated 'student' table
            'dob' => 'required|date',
            'email' => 'required|string|email|max:255|unique:student', // Updated 'student' table
            'password' => 'required|string|min:8|confirmed', // Ensure confirmation field exists in form
        ]);

        // Create the user after validation passes
        $user = User::create([
            'name' => $request->name,
            'student_id' => $request->student_id,
            'dob' => $request->dob,
            'email' => $request->email,
            'password' => Hash::make($request->password),  // Hash the password
        ]);
        ////////////////////////////////////////////
        $user->notificationSettings()->createMany([
            [
              'type'         => 'assignment_due',
              'enabled'      => true,
              'frequency'    => 'once',
              'before_hours' => 24,
            ],
            [
              'type'         => 'new_registration',
              'enabled'      => true,
              'frequency'    => 'once',
              'before_hours' => 0,
            ],
        ]);
        /////////////////////////
        // Redirect to login page with success message
        return redirect()->route('login')->with('success', 'Registration successful. Please log in.');
    }
}
