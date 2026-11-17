<?php

declare(strict_types=1);

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Symfony\Component\HttpFoundation\Response;

/**
 * Require two-factor authentication before any admin page loads.
 * Two-factor is mandatory for super admin staff.
 */
class AdminTwoFactor
{
    public function handle(Request $request, Closure $next): Response
    {
        $admin = Auth::guard('admin')->user();

        if (! $admin) {
            return redirect()->route('admin.login');
        }

        // If 2FA is not yet enrolled, redirect to enrollment.
        if ($admin->two_factor_required && empty($admin->two_factor_secret)) {
            return redirect()->route('admin.2fa.enroll');
        }

        // If 2FA is required but not verified this session, redirect to verify.
        if ($admin->two_factor_required && ! $request->session()->get('admin_2fa_verified')) {
            return redirect()->route('admin.2fa.verify');
        }

        return $next($request);
    }
}
