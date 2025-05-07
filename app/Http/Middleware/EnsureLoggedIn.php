<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;

/**
 * Middleware to ensure the user is logged in.
 *
 * This middleware checks if the user is logged in by verifying the session.
 * If the user is not logged in, they are redirected to the login page.
 * It allows access to the login and login submit routes without requiring a session.
 */

class EnsureLoggedIn
{
    public function handle(Request $request, Closure $next)
    {
        // Allow login and login submit routes without session
        if ($request->is('login') || $request->is('login-submit')) {
            return $next($request);
        }

        // Redirect if session user is not available
        if (!session()->has('user')) {
            return redirect()->route('login');
        }

        return $next($request);
    }
}

