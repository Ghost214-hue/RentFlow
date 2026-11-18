<?php

declare(strict_types=1);

namespace App\Http\Controllers\Admin\Auth;

use App\Http\Controllers\Controller;
use App\Services\TwoFactor;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\View\View;

class TwoFactorController extends Controller
{
    public function __construct(private readonly TwoFactor $twoFactor)
    {
    }

    public function enroll(Request $request): View|RedirectResponse
    {
        $admin = Auth::guard('admin')->user();

        if (! empty($admin->two_factor_secret)) {
            return redirect()->route('admin.2fa.verify');
        }

        $secret = $request->session()->get('admin_2fa_pending_secret');

        if (! is_string($secret) || $secret === '') {
            $secret = $this->twoFactor->generateSecret();
            $request->session()->put('admin_2fa_pending_secret', $secret);
        }

        return view('admin.auth.2fa.enroll', [
            'secret' => $secret,
            'qrUrl' => $this->twoFactor->qrCodeUrl($admin, $secret),
        ]);
    }

    public function confirmEnroll(Request $request): RedirectResponse
    {
        $request->validate([
            'code' => ['required', 'string', 'max:10'],
        ]);

        $admin = Auth::guard('admin')->user();
        $secret = $request->session()->get('admin_2fa_pending_secret');

        if (! is_string($secret) || ! $this->twoFactor->verify($secret, $request->string('code')->toString())) {
            return back()->withErrors(['code' => 'That code is not valid. Try again.']);
        }

        $recoveryCodes = $this->twoFactor->generateRecoveryCodes();

        $admin->forceFill([
            'two_factor_secret' => $secret,
            'two_factor_recovery' => $this->twoFactor->hashRecoveryCodes($recoveryCodes),
        ])->save();

        $request->session()->forget('admin_2fa_pending_secret');
        $request->session()->put('admin_2fa_verified', true);

        return redirect()
            ->route('admin.dashboard')
            ->with('status', 'Two-factor authentication enabled.')
            ->with('recovery_codes', $recoveryCodes);
    }

    public function showVerify(): View
    {
        return view('admin.auth.2fa.verify');
    }

    public function verify(Request $request): RedirectResponse
    {
        $request->validate([
            'code' => ['required', 'string', 'max:10'],
        ]);

        $admin = Auth::guard('admin')->user();

        if (! $this->twoFactor->verify((string) $admin->two_factor_secret, $request->string('code')->toString())) {
            return back()->withErrors(['code' => 'That code is not valid.']);
        }

        $request->session()->put('admin_2fa_verified', true);

        return redirect()->intended(route('admin.dashboard'));
    }

    public function recover(Request $request): RedirectResponse
    {
        $request->validate([
            'code' => ['required', 'string', 'max:32'],
        ]);

        $admin = Auth::guard('admin')->user();

        if (! $this->twoFactor->consumeRecoveryCode($admin, $request->string('code')->toString())) {
            return back()->withErrors(['code' => 'That recovery code is not valid.']);
        }

        $request->session()->put('admin_2fa_verified', true);

        return redirect()->intended(route('admin.dashboard'));
    }
}
