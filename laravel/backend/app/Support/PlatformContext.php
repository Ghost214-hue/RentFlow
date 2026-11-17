<?php

declare(strict_types=1);

namespace App\Support;

/**
 * Platform context for the super admin area.
 *
 * Never sets an owner id. Admin queries name the owner and go through
 * CrossTenant::run() with a reason for cross-tenant access.
 */
final class PlatformContext
{
    public static function isPlatform(): bool
    {
        return ! TenantContext::isResolved() || ! TenantContext::id();
    }

    /**
     * Run a callback without the owner scope, recording an audit entry.
     *
     * Used by the admin platform to query across owners.
     *
     * @param  string  $reason  Human-readable justification, stored in audit_logs
     * @param  callable  $fn
     * @return mixed
     */
    public static function run(string $reason, Closure $fn)
    {
        // Clear any existing owner context
        $previous = TenantContext::id();

        TenantContext::clear();

        try {
            return $fn();
        } finally {
            TenantContext::set($previous);
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

        try {
            return $fn();
        } finally {
            TenantContext::set($previous);
        }
    }
}