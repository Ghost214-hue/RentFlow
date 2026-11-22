<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Symfony\Component\HttpFoundation\Response;

/**
 * Require two-factor authentication before any admin page loads.
 * Two-factor is mandatory for super admin staff.
 *
 * The enrolment and verification routes are registered OUTSIDE this
 * middleware (see routes/admin.php), so redirecting to them cannot loop.
 */
class AdminTwoFactor
{
    public function handle(Request $request, Closure $next): Response
    {
        $admin = Auth::guard('admin')->user();

        if (! $admin) {
            return redirect()->route('admin.login');
        }

        if (! $admin->two_factor_required) {
            return $next($request);
        }

        // Not enrolled yet: send them to enrolment.
        if (empty($admin->two_factor_secret)) {
            return redirect()->route('admin.2fa.enroll');
        }

        // Enrolled but not verified this session: send them to verify.
        if (! $request->session()->get('admin_2fa_verified')) {
            return redirect()->route('admin.2fa.verify');
        }

        return $next($request);
    }
}
