<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class AdminMiddleware
{
    /**
     * Handle an incoming request.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \Closure  $next
     * @return mixed
     */
    public function handle(Request $request, Closure $next)
    {
        // Check if the logged-in user is an admin (e.g., check if 'is_admin' is true)
        if (Auth::check() && Auth::user()->is_admin) {
            return $next($request);
        }

        // Redirect if not an admin
        return redirect()->route('login')->with('error', 'You do not have admin access.');
    }
}
