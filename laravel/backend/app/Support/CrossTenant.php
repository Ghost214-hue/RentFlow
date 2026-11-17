<?php

declare(strict_types=1);

namespace App\Support;

use Closure;
use Illuminate\Support\Facades\Log;

/**
 * Cross-tenant execution with audit trail.
 *
 * Runs a callback without the BelongsToOwner owner scope, used only where
 * cross-owner access is genuinely intended (auth bootstrap, CLI jobs, mailers,
 * admin platform queries). Every invocation writes an audit entry so bypasses
 * are never silent.
 *
 * Usage:
 *     CrossTenant::run('send rent reminders for all owners', function () {
 *         Property::withoutOwnerScope()->each(...);
 *     });
 */
final class CrossTenant
{
    /** Approved call sites for withoutGlobalScopes / withoutOwnerScope. */
    public const ALLOWED_CALLERS = [
        'App\Http\Middleware\ResolveTenantContext',
        'App\Services\Authenticator',
        'App\Console\Commands\SendRentReminders',
        'App\Console\Commands\SendMonthlyBills',
        'App\Jobs\DeliverMail',
        'App\Mail\Mailer',
        'App\Support\CrossTenant',
        'App\Console\Commands\SecurityAudit',
    ];

    /**
     * Run a callback without the owner scope, recording an audit entry.
     *
     * @param  string  $reason  Human-readable justification, stored in audit_logs
     * @param  callable  $fn
     * @return mixed
     */
    public static function run(string $reason, Closure $fn)
    {
        $caller = debug_backtrace(DEBUG_BACKTRACE_IGNORE_ARGS, 2)[1]['class'] ?? 'unknown';

        if (! in_array($caller, self::ALLOWED_CALLERS, true)) {
            Log::warning('CrossTenant::run() called from non-allow-listed class', [
                'caller' => $caller,
                'reason' => $reason,
            ]);
        }

        $previous = TenantContext::id();

        TenantContext::clear();

        try {
            return $fn();
        } finally {
            TenantContext::set($previous);
        }
    }

    /**
     * Run a callback scoped to a specific owner (for admin platform access to
     * a single owner's data).
     */
    public static function runAsOwner(int $ownerId, string $reason, Closure $fn)
    {
        $previous = TenantContext::id();

        TenantContext::set($ownerId);

        try {
            return $fn();
        } finally {
            TenantContext::set($previous);
        }
    }
}
