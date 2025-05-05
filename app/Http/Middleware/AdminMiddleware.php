<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use App\Models\Admin;
use Illuminate\Support\Facades\Log;
use Laravel\Sanctum\PersonalAccessToken;

class AdminMiddleware
{
    public function handle(Request $request, Closure $next)
    {
        $user = $request->user();
        
        // For web routes, check if we have a token in the request
        if (!$user && $request->bearerToken()) {
            $token = PersonalAccessToken::findToken($request->bearerToken());
            if ($token) {
                $user = $token->tokenable;
                // Set the user for this request
                $request->setUserResolver(function() use ($user) {
                    return $user;
                });
            }
        }
        
        // Debug logging
        Log::debug('Admin middleware check', [
            'user' => $user ? get_class($user) : 'No user',
            'is_admin_class' => $user ? ($user instanceof Admin ? 'Yes' : 'No') : 'N/A',
            'user_role' => $user ? ($user->role ?? 'No role') : 'N/A',
            'morph_class' => $user ? $user->getMorphClass() : 'N/A',
            'has_token' => $request->bearerToken() ? 'Yes' : 'No',
        ]);
        
        // Check if the authenticated user is actually an Admin model instance
        // or if they have the admin role
        if (!$user) {
            Log::warning('Admin middleware: No authenticated user');
            
            // For web routes, redirect to login
            if (!$request->expectsJson()) {
                return redirect()->route('admin.login');
            }
            
            return response()->json(['message' => 'Unauthorized. Please log in.'], 401);
        }

        if ($user->getMorphClass() !== 'App\Models\Admin' && $user->role !== 'admin') {
            Log::warning('Admin middleware: User is not an admin', [
                'user_class' => get_class($user),
                'morph_class' => $user->getMorphClass(),
                'role' => $user->role ?? 'No role'
            ]);
            
            // For web routes, redirect to login
            if (!$request->expectsJson()) {
                return redirect()->route('admin.login');
            }
            
            return response()->json(['message' => 'Unauthorized. Admin access required.'], 403);
        }

        return $next($request);
    }
}