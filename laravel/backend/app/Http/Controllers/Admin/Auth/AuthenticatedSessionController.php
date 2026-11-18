<?php

declare(strict_types=1);

namespace App\Http\Controllers\Admin\Auth;

use App\Http\Controllers\Controller;
use App\Models\Admin;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\View\View;

class AuthenticatedSessionController extends Controller
{
    public function create(): View
    {
        return view('admin.auth.login');
    }

    public function store(Request $request): RedirectResponse
    {
        $request->validate([
            'email' => ['required', 'email', 'max:255'],
            'password' => ['required', 'string'],
        ]);

        $admin = Admin::where('email', $request->email)->first();

        if (! $admin || ! Hash::check($request->password, $admin->password)) {
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
        Auth::guard('admin')->login($admin);

        // Regenerate the session to prevent session fixation.
        $request->session()->regenerate();

        // A fresh login is never pre-verified.
        $request->session()->forget('admin_2fa_verified');

        if ($admin->two_factor_required) {
            if (empty($admin->two_factor_secret)) {
                return redirect()->route('admin.2fa.enroll');
            }

            return redirect()->route('admin.2fa.verify');
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
