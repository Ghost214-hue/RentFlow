<?php

declare(strict_types=1);

namespace App\Http\Controllers\Admin\Auth;

use App\Http\Controllers\Controller;
use App\Models\Admin;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\RateLimiter;
use Inertia\Inertia;
use Inertia\Response;

class AuthenticatedSessionController extends Controller
{
    public function create(): Response
    {
        return Inertia::render('Admin/Auth/Login');
    }

    public function store(Request $request): RedirectResponse
    {
        $request->validate([
            'email' => ['required', 'email', 'max:255'],
            'password' => ['required', 'string'],
        ]);

        $admin = Admin::where('email', $request->email)->first();
        $limiterKey = 'admin:login:'.strtolower((string) $request->email).'|'.$request->ip();

        if (! $admin || ! Hash::check($request->password, $admin->password)) {
            // Only FAILED logins count towards the lockout.
            RateLimiter::hit($limiterKey, 900);

            return back()
                ->withInput()
                ->withErrors(['email' => 'The provided credentials are incorrect.']);
        }

        // Block login for suspended admins.
        if ($admin->status !== 'active') {
            return back()
                ->withInput()
                ->withErrors(['email' => 'This account is suspended. Contact a super admin.']);
        }

        /*
         * Log the admin in BEFORE the 2FA gate.
         *
         * The previous version redirected to admin.2fa.verify without logging
         * in, so AdminTwoFactor saw no authenticated user and bounced straight
         * back to the login page: an infinite loop.
         */
        RateLimiter::clear($limiterKey);

        Auth::guard('admin')->login($admin);

        // Regenerate the session to prevent session fixation.
        $request->session()->regenerate();

        // A fresh password login ALWAYS starts un-verified. The old code kept a
        // previous 'verified' flag, so a second admin signing in on the same
        // browser session skipped 2FA entirely.
        $request->session()->forget('admin_2fa_verified');

        if (config('admin.require_2fa', true)) {
            return empty($admin->two_factor_secret)
                ? redirect()->route('admin.2fa.enroll')
                : redirect()->route('admin.2fa.verify');
        }

        return redirect()->intended(route('admin.dashboard'));
    }

    public function destroy(Request $request): RedirectResponse
    {
        // Log out via the admin guard.
        Auth::guard('admin')->logout();

        // Invalidate the session to prevent session fixation attacks.
        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return redirect()->route('admin.login');
    }
}
