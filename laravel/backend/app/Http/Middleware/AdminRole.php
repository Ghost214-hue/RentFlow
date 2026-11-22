<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Symfony\Component\HttpFoundation\Response;

/**
 * Enforce admin_role (super_admin | support | auditor). The roles existed in
 * the database and AdminPolicy but nothing applied them, so an "auditor" could
 * suspend owners. Usage: ->middleware('admin.role:super_admin,support')
 */
class AdminRole
{
    public function handle(Request $request, Closure $next, string ...$roles): Response
    {
        $admin = Auth::guard('admin')->user();

        abort_unless($admin !== null && in_array($admin->admin_role, $roles, true), 403, 'Your admin role cannot do this.');

        return $next($request);
    }
}
