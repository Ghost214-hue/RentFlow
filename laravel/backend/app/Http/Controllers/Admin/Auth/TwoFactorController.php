<?php

declare(strict_types=1);

namespace App\Http\Controllers\Admin\Auth;

use App\Http\Controllers\Controller;
use App\Models\Admin;
use App\Services\TwoFactor;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\RateLimiter;
use Inertia\Inertia;
use Inertia\Response;

class TwoFactorController extends Controller
{
    private const MAX_ATTEMPTS = 5;

    private const LOCK_SECONDS = 300;

    public function __construct(private readonly TwoFactor $twoFactor)
    {
    }

    public function enroll(Request $request): Response|RedirectResponse
    {
        $admin = $this->admin();

        if (! empty($admin->two_factor_secret)) {
            return redirect()->route('admin.2fa.verify');
        }

        $secret = $request->session()->get('admin_2fa_pending_secret');

        if (! is_string($secret) || $secret === '') {
            $secret = $this->twoFactor->generateSecret();
            $request->session()->put('admin_2fa_pending_secret', $secret);
        }

        return Inertia::render('Admin/Auth/TwoFactorEnroll', [
            'secret' => $secret,
            'qrUrl' => $this->twoFactor->qrCodeUrl($admin, $secret),
        ]);
    }

    public function confirmEnroll(Request $request): RedirectResponse
    {
        $request->validate(['code' => ['required', 'string', 'max:12']]);

        $admin = $this->admin();
        $secret = $request->session()->get('admin_2fa_pending_secret');

        if ($this->tooMany($admin, 'enroll')) {
            return $this->locked('enroll', $admin);
        }

        if (! is_string($secret) || ! $this->twoFactor->verify($secret, $request->string('code')->toString())) {
            RateLimiter::hit($this->key($admin, 'enroll'), self::LOCK_SECONDS);

            return back()->withErrors(['code' => 'That code is not valid. Check your phone\'s clock is set to automatic time, then try the next code.']);
        }

        RateLimiter::clear($this->key($admin, 'enroll'));

        $codes = $this->twoFactor->generateRecoveryCodes();

        $admin->forceFill([
            'two_factor_secret' => $secret,
            'two_factor_recovery' => $this->twoFactor->hashRecoveryCodes($codes),
        ])->save();

        $request->session()->forget('admin_2fa_pending_secret');
        $this->markVerified($request, $admin);

        // Shown ONCE on a dedicated page. They used to be flashed to the
        // dashboard, which never rendered them, so nobody ever saw them.
        $request->session()->put('admin_2fa_new_codes', $codes);

        return redirect()->route('admin.2fa.codes');
    }

    public function showCodes(Request $request): Response|RedirectResponse
    {
        $codes = $request->session()->get('admin_2fa_new_codes');

        if (! is_array($codes) || $codes === []) {
            return redirect()->route('admin.dashboard');
        }

        return Inertia::render('Admin/Auth/RecoveryCodes', ['codes' => array_values($codes)]);
    }

    public function acknowledgeCodes(Request $request): RedirectResponse
    {
        $request->session()->forget('admin_2fa_new_codes');

        return redirect()->route('admin.dashboard')->with('success', 'Two-factor authentication is on.');
    }

    /** Issue a fresh set of recovery codes (needs a valid authenticator code). */
    public function regenerateCodes(Request $request): RedirectResponse
    {
        $request->validate(['code' => ['required', 'string', 'max:12']]);

        $admin = $this->admin();

        if ($this->tooMany($admin, 'regen')) {
            return $this->locked('regen', $admin);
        }

        if (! $this->twoFactor->verifyCode($admin, $request->string('code')->toString())) {
            RateLimiter::hit($this->key($admin, 'regen'), self::LOCK_SECONDS);

            return back()->withErrors(['code' => 'That code is not valid.']);
        }

        $codes = $this->twoFactor->generateRecoveryCodes();
        $admin->forceFill(['two_factor_recovery' => $this->twoFactor->hashRecoveryCodes($codes)])->save();
        $request->session()->put('admin_2fa_new_codes', $codes);

        return redirect()->route('admin.2fa.codes');
    }

    public function showVerify(): Response
    {
        return Inertia::render('Admin/Auth/TwoFactorVerify');
    }

    public function verify(Request $request): RedirectResponse
    {
        $request->validate(['code' => ['required', 'string', 'max:12']]);

        $admin = $this->admin();

        if ($this->tooMany($admin, 'verify')) {
            return $this->locked('verify', $admin);
        }

        if (! $this->twoFactor->verifyCode($admin, $request->string('code')->toString())) {
            RateLimiter::hit($this->key($admin, 'verify'), self::LOCK_SECONDS);
            Log::warning('Admin 2FA code rejected', ['admin_id' => $admin->getKey(), 'ip' => $request->ip()]);

            return back()->withErrors(['code' => 'That code is not valid or was already used. Wait for the next code and try again.']);
        }

        RateLimiter::clear($this->key($admin, 'verify'));
        $this->markVerified($request, $admin);

        return redirect()->intended(route('admin.dashboard'));
    }

    public function recover(Request $request): RedirectResponse
    {
        $request->validate(['code' => ['required', 'string', 'max:40']]);

        $admin = $this->admin();

        if ($this->tooMany($admin, 'verify')) {
            return $this->locked('verify', $admin);
        }

        if (! $this->twoFactor->consumeRecoveryCode($admin, $request->string('code')->toString())) {
            RateLimiter::hit($this->key($admin, 'verify'), self::LOCK_SECONDS);

            return back()->withErrors(['code' => 'That recovery code is not valid.']);
        }

        RateLimiter::clear($this->key($admin, 'verify'));
        Log::warning('Admin signed in with a recovery code', ['admin_id' => $admin->getKey(), 'ip' => $request->ip()]);
        $this->markVerified($request, $admin);

        return redirect()->intended(route('admin.dashboard'));
    }

    private function admin(): Admin
    {
        /** @var Admin */
        return Auth::guard('admin')->user();
    }

    /** Bind the verified flag to THIS admin and rotate the session id. */
    private function markVerified(Request $request, Admin $admin): void
    {
        $request->session()->regenerate();
        $request->session()->put('admin_2fa_verified', (int) $admin->getKey());
    }

    private function key(Admin $admin, string $scope): string
    {
        return "admin-2fa:{$scope}:".$admin->getKey();
    }

    private function tooMany(Admin $admin, string $scope): bool
    {
        return RateLimiter::tooManyAttempts($this->key($admin, $scope), self::MAX_ATTEMPTS);
    }

    private function locked(string $scope, Admin $admin): RedirectResponse
    {
        $seconds = RateLimiter::availableIn($this->key($admin, $scope));

        return back()->withErrors(['code' => "Too many attempts. Try again in {$seconds} seconds."]);
    }
}
