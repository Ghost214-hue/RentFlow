<?php

declare(strict_types=1);

namespace App\Http\Controllers\Admin\Auth;

use App\Http\Controllers\Controller;
use App\Models\Admin;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rule;

class AuthenticatedSessionController extends Controller
{
    public function create()
    {
        return view('admin.auth.login');
    }

    public function store(Request $request)
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

        $request->session()->put('admin_id', $admin->id);
        $request->session()->put('admin_2fa_verified', true);

        return redirect()->intended(route('admin.dashboard'));
    }

    public function destroy(Request $request)
    {
        $request->session()->forget(['admin_id', 'admin_2fa_verified']);

        return redirect()->route('admin.login');
    }
}