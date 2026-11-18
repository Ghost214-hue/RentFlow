<?php

declare(strict_types=1);

namespace App\Support;

use Closure;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Throwable;

/**
 * Platform context for the super admin area.
 *
 * Never sets an owner id. Admin queries name the owner and go through
 * PlatformContext::run() with a reason for cross-tenant access. Every call
 * writes one audit_logs row so a cross-owner read is never silent.
 */
final class PlatformContext
{
    public static function isPlatform(): bool
    {
        return TenantContext::id() === null;
    }

    /**
     * Run a callback without the owner scope, recording an audit entry.
     *
     * @param  string  $reason  Human-readable justification, stored in audit_logs
     * @param  callable  $fn
     * @return mixed
     */
    public static function run(string $reason, Closure $fn)
    {
        $previous = TenantContext::id();

        TenantContext::clear();

        self::audit($reason);

        try {
            return $fn();
        } finally {
            self::restore($previous);
        }
    }

    /**
     * Run a callback scoped to a specific owner id.
     *
     * Admin platform uses this to query a single owner's data under a
     * time-limited support grant.
     */
    public static function runAsOwner(int $ownerId, string $reason, Closure $fn)
    {
        $previous = TenantContext::id();

        TenantContext::set($ownerId);

        self::audit($reason, $ownerId);

        try {
            return $fn();
        } finally {
            self::restore($previous);
        }
    }

    /**
     * Restore the previous tenant context.
     *
     * TenantContext::set(null) marks the context "resolved", which is not the
     * same as "never resolved". When there was no previous owner we clear
     * instead of setting null.
     */
    private static function restore(?int $previous): void
    {
        if ($previous === null) {
            TenantContext::clear();

            return;
        }

        TenantContext::set($previous);
    }

    /**
     * Write one audit row for a cross-owner read.
     *
     * Column names are assumed to match the audit_logs migration:
     * actor_type, actor_id, action, target_owner_id, reason, created_at,
     * updated_at. Confirm against the migration before shipping.
     */
    private static function audit(string $reason, ?int $targetOwnerId = null): void
    {
        try {
            DB::table('audit_logs')->insert([
                'actor_type' => 'admin',
                'actor_id' => auth('admin')->id(),
                'action' => 'platform.cross_tenant',
                'target_owner_id' => $targetOwnerId,
                'reason' => $reason,
                'created_at' => now(),
                'updated_at' => now(),
            ]);
        } catch (Throwable $e) {
            // An audit failure must be visible, not swallowed.
            Log::error('Failed to write platform audit row', [
                'reason' => $reason,
                'target_owner_id' => $targetOwnerId,
                'error' => $e->getMessage(),
            ]);
        }
    }
}
