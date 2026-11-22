<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\RateLimiter;
use Symfony\Component\HttpFoundation\Response;

/**
 * Lockout GATE for the admin login POST.
 *
 * Previously this middleware wrapped EVERY admin route and called hit() on
 * every request. For non-login requests $request->email is null, so all of them
 * shared the single key "admin:login:" -- five page loads or 2FA submits in 15
 * minutes locked out every admin at once. Now it only checks, only on the login
 * POST; AuthenticatedSessionController records the failed attempts and clears
 * them on success.
 */
class AdminThrottleLogin
{
    public function handle(Request $request, Closure $next): Response
    {
        if (! $request->isMethod('post')) {
            return $next($request);
        }

        $key = 'admin:login:'.strtolower((string) $request->input('email')).'|'.$request->ip();

        if (RateLimiter::tooManyAttempts($key, 5)) {
            return back()->withErrors([
                'email' => 'Too many failed attempts. Try again in '.RateLimiter::availableIn($key).' seconds.',
            ])->onlyInput('email');
        }

        return $next($request);
    }
}
