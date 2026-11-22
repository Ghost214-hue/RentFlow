<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\RateLimiter;
use Symfony\Component\HttpFoundation\Response;

class AdminLoginRateLimiter
{
    public function handle(Request $request, Closure $next): Response
    {
        $key = 'admin:login:' . $request->email;

        if (RateLimiter::tooManyAttempts($key, 5)) {
            return back()->withErrors([
                'email' => 'Too many login attempts. Please try again in ' . RateLimiter::availableIn($key) . ' seconds.',
            ])->onlyInput('email');
        }

        RateLimiter::hit($key, 900); // 15 minutes

        return $next($request);
    }
}
