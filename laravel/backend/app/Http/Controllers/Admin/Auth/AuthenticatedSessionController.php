<?php

declare(strict_types=1);

namespace App\Http\Controllers\Admin\Auth;

use App\Http\Controllers\Controller;
use App\Models\Admin;
use App\Services\TwoFactor;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;

class AuthenticatedSessionController extends Controller
{
    public function create(): \Illuminate\View\View
    {
        return view('admin.auth.login');
    }

    public function store(Request $request): \Illuminate\Http\RedirectResponse
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

        // Check 2FA
        if ($admin->two_factor_required && empty($admin->two_factor_secret)) {
            return back()
                ->withInput()
                ->withErrors(['email' => 'Account requires two-factor authentication setup.']);
        }

        if ($admin->two_factor_required && ! $request->session()->get('admin_2fa_verified')) {
            return redirect()->route('admin.2fa.verify')
                ->with('status', 'Please verify your two-factor authentication.');
        }

        // Log the admin in via the admin guard.
        Auth::guard('admin')->login($admin);

        // Regenerate the session to prevent session fixation.
        $request->session()->regenerate();

        // Clear the 2FA verified flag after successful login.
        $request->session()->forget('admin_2fa_verified');

        return redirect()->intended(route('admin.dashboard'));
    }

    public function destroy(Request $request): \Illuminate\Http\RedirectResponse
    {
        // Log out via the admin guard.
        Auth::guard('admin')->logout();

        // Invalidate the session to prevent session fixation attacks.
        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return redirect()->route('admin.login');
    }
}
