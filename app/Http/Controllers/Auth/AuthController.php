<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Models\User;
use App\Models\Admin;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Str;
use Illuminate\Validation\Rules\Password;

class AuthController extends Controller
{
    public function register(Request $request)
    {
        $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'string', 'email', 'max:255', 'unique:users'],
            'password' => ['required', 'confirmed', Password::defaults()],
        ]);

        $user = User::create([
            'name' => $request->name,
            'email' => $request->email,
            'password' => Hash::make($request->password),
        ]);

        $otp = Str::random(6);
        session(['registration_otp' => $otp, 'user_id' => $user->id]);

        Mail::raw("Your OTP for registration is: {$otp}", function($message) use ($user) {
            $message->to($user->email)
                    ->subject('Email Verification OTP');
        });

        return response()->json([
            'message' => 'Registration successful. Please verify your email with the OTP sent.',
            'user_id' => $user->id
        ]);
    }

    public function verifyOtp(Request $request)
    {
        $request->validate([
            'otp' => 'required|string|size:6'
        ]);

        if ($request->otp !== session('registration_otp')) {
            return response()->json([
                'message' => 'Invalid OTP'
            ], 422);
        }

        $user = User::find(session('user_id'));
        $user->email_verified_at = now();
        $user->save();

        session()->forget(['registration_otp', 'user_id']);

        return response()->json([
            'message' => 'Email verified successfully',
            'user' => $user
        ]);
    }

    public function login(Request $request)
    {
        $request->validate([
            'email' => 'required|email',
            'password' => 'required',
        ]);

        if (!auth()->attempt($request->only('email', 'password'))) {
            return response()->json([
                'message' => 'Invalid credentials'
            ], 401);
        }

        $user = User::where('email', $request->email)->first();
        $token = $user->createToken('auth_token')->plainTextToken;

        return response()->json([
            'message' => 'Login successful',
            'user' => $user,
            'token' => $token
        ]);
    }

    public function logout(Request $request)
    {
        $request->user()->currentAccessToken()->delete();

        return response()->json([
            'message' => 'Logged out successfully'
        ]);
    }

    public function adminLogin(Request $request)
    {
        $request->validate([
            'email' => 'required|email',
            'password' => 'required',
        ]);

        $admin = Admin::where('email', $request->email)->first();

        if (!$admin || !Hash::check($request->password, $admin->password)) {
            return response()->json([
                'message' => 'Invalid admin credentials'
            ], 401);
        }

        $token = $admin->createToken('admin_token')->plainTextToken;

        return response()->json([
            'message' => 'Admin login successful',
            'admin' => $admin,
            'token' => $token
        ]);
    }

    public function adminLogout(Request $request)
    {
        $request->user()->currentAccessToken()->delete();

        return response()->json([
            'message' => 'Admin logged out successfully'
        ]);
    }
}