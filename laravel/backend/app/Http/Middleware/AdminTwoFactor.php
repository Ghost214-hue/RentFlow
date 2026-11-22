<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Symfony\Component\HttpFoundation\Response;

/**
 * Require two-factor authentication before any admin page loads.
 *
 * Mandatory for every platform admin: the per-row two_factor_required flag can
 * no longer switch it off (only ADMIN_REQUIRE_2FA=false, for local dev).
 * The "verified" marker stores the ADMIN'S ID, so it cannot carry over from
 * another admin's session.
 *
 * Enrolment/verification routes sit OUTSIDE this middleware (routes/admin.php)
 * so redirecting to them cannot loop.
 */
class AdminTwoFactor
{
    public function handle(Request $request, Closure $next): Response
    {
        $admin = Auth::guard('admin')->user();

        if (! $admin) {
            return redirect()->route('admin.login');
        }

        // A suspended admin loses access immediately, mid-session too.
        if ($admin->status !== 'active') {
            Auth::guard('admin')->logout();
            $request->session()->invalidate();
            $request->session()->regenerateToken();

            return redirect()->route('admin.login')->withErrors(['email' => 'This account is suspended.']);
        }

        if (! config('admin.require_2fa', true)) {
            return $next($request);
        }

        if (empty($admin->two_factor_secret)) {
            return redirect()->route('admin.2fa.enroll');
        }

        if ((int) $request->session()->get('admin_2fa_verified') !== (int) $admin->getKey()) {
            return redirect()->route('admin.2fa.verify');
        }

        return $next($request);
    }
}
