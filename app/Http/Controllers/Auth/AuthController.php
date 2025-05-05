<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Models\User;
use App\Models\Admin;
use App\Models\Student;
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

        // Attempt to authenticate using the admin guard
        if (!auth()->guard('admin')->attempt($request->only('email', 'password'))) {
            // Manual fallback - some Laravel versions have issues with multiple guard auth()->attempt()
            $admin = Admin::where('email', $request->email)->first();
            
            if (!$admin || !Hash::check($request->password, $admin->password)) {
                return response()->json([
                    'message' => 'Invalid admin credentials'
                ], 401);
            }
            
            // Manually login the admin
            auth()->guard('admin')->login($admin);
        }

        // Get the authenticated admin
        $admin = auth()->guard('admin')->user() ?: Admin::where('email', $request->email)->first();
        $token = $admin->createToken('admin_token')->plainTextToken;

        // Store a cookie with the token for web routes as a fallback authentication method
        $cookie = cookie('admin_token', $token, 60 * 24, null, null, false, true); // 24 hours, httpOnly

        return response()->json([
            'message' => 'Admin login successful',
            'admin' => $admin,
            'token' => $token
        ])->withCookie($cookie);
    }

    public function adminLogout(Request $request)
    {
        // Delete the current token if it exists
        if ($request->user()) {
            $request->user()->currentAccessToken()->delete();
        }

        // Also logout from session
        auth()->guard('admin')->logout();
        
        // Clear admin token cookie
        $cookie = cookie()->forget('admin_token');

        return response()->json([
            'message' => 'Admin logged out successfully'
        ])->withCookie($cookie);
    }

    public function studentLogin(Request $request)
    {
        $request->validate([
            'email' => 'required|email',
            'password' => 'required',
        ]);

        $student = \App\Models\Student::where('email', $request->email)->first();

        if (!$student || !Hash::check($request->password, $student->password)) {
            return response()->json([
                'message' => 'Invalid student credentials'
            ], 401);
        }

        // Check if the student account has been approved
        if ($student->status !== 'approved') {
            return response()->json([
                'message' => 'Your account has not been approved yet'
            ], 403);
        }

        $token = $student->createToken('student_token')->plainTextToken;

        return response()->json([
            'message' => 'Student login successful',
            'student' => $student,
            'token' => $token
        ]);
    }

    /**
     * Handle web-based admin login.
     */
    public function webAdminLogin(Request $request)
    {
        $request->validate([
            'email' => 'required|email',
            'password' => 'required',
        ]);

        // Attempt to authenticate using the admin guard
        if (auth()->guard('admin')->attempt($request->only('email', 'password'))) {
            // Get the authenticated admin
            $admin = auth()->guard('admin')->user();
            $token = $admin->createToken('admin_token')->plainTextToken;
            
            // Store token in session for API requests
            session(['admin_token' => $token]);
            
            return redirect()->route('admin.dashboard');
        }
        
        // Manual fallback if guard attempt fails
        $admin = Admin::where('email', $request->email)->first();
        
        if ($admin && Hash::check($request->password, $admin->password)) {
            // Manually login the admin
            auth()->guard('admin')->login($admin);
            $token = $admin->createToken('admin_token')->plainTextToken;
            
            // Store token in session for API requests
            session(['admin_token' => $token]);
            
            return redirect()->route('admin.dashboard');
        }
        
        // Authentication failed
        return back()->with('error', 'Invalid admin credentials');
    }
    
    /**
     * Handle web-based student login.
     */
    public function webStudentLogin(Request $request)
    {
        $request->validate([
            'email' => 'required|email',
            'password' => 'required',
        ]);

        $student = Student::where('email', $request->email)->first();

        if (!$student || !Hash::check($request->password, $student->password)) {
            return back()->with('error', 'Invalid student credentials');
        }

        // Check if the student account has been approved
        if ($student->status !== 'approved') {
            return back()->with('error', 'Your account has not been approved yet');
        }

        // Use the student guard for session auth
        auth()->guard('student')->login($student);
        
        // Create token for API access
        $token = $student->createToken('student_token')->plainTextToken;
        
        // Store token in session for easy API access
        session(['student_token' => $token]);

        return redirect()->route('student.dashboard');
    }
}