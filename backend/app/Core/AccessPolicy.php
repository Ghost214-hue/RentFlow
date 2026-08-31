<?php
/**
 * AccessPolicy
 * Central authorization helpers that must be used by controllers before
 * returning protected data. Every protected request must answer:
 *   WHO ARE YOU?              -> Router::getAuthRole() / getAuthUserId()
 *   WHAT ARE YOU ALLOWED TO?  -> these role/scope assertions
 *
 * A tenant must only ever reach their own tenancy data. An owner only their
 * own portfolio. A caretaker only their assigned properties. These helpers
 * provide a single, auditable choke-point instead of ad-hoc role checks
 * scattered across controllers.
 */
namespace App\Core;

use App\Core\Database;

class AccessPolicy
{
    /**
     * Current auth role (owner | caretaker | tenant).
     */
    public static function role(): string
    {
        return Router::getAuthRole();
    }

    /**
     * True when the current authenticated role is among $allowed.
     */
    public static function roleIs(array $allowed): bool
    {
        return in_array(self::role(), $allowed, true);
    }

    /**
     * Terminate with 403 unless the current role is in $allowed, while
     * recording the denial in the security audit log.
     */
    public static function requireRoles(array $allowed, string $message = 'Insufficient permissions.',
                                        ?string $resourceType = null, $resourceId = null): void
    {
        if (self::roleIs($allowed)) {
            return;
        }
        self::deny($message, 403, $resourceType, $resourceId, 'ROLE_NOT_ALLOWED');
    }

    /**
     * Convenience wrappers matching Router::requireOwner*.
     */
    public static function owner(): void
    {
        self::requireRoles(['owner'], 'Only owners can perform this action');
    }

    public static function ownerOrCaretaker(): void
    {
        self::requireRoles(['owner', 'caretaker'], 'Only owners and caretakers can perform this action');
    }

    /**
     * A general scoped-access assertion for single resources.
     * $ownsResource  -> record matched the owner scope (owner_id).
     * $scopeAllowed  -> record also matched the current actor's role-specific
     *                   scope (e.g. tenant owns the record, or the record is in
     *                   a caretaker's assigned property).
     *
     * Returns 403 when the actor is authenticated but not permitted for the
     * resource, and 404 when the resource simply isn't within the owner scope
     * (avoiding existence leakage for cross-owner lookups).
     */
    public static function assertResource(bool $ownsResource, bool $scopeAllowed,
                                          ?string $resourceType = null, $resourceId = null): void
    {
        if (!$ownsResource) {
            self::deny('Resource not found', 404, $resourceType, $resourceId, 'RESOURCE_NOT_FOUND');
        }
        if (!$scopeAllowed) {
            self::deny('Resource is outside your authorized scope', 403, $resourceType, $resourceId, 'RESOURCE_OUTSIDE_AUTHORIZED_SCOPE');
        }
    }

    /**
     * Emit a compliant error response and record an audit entry.
     */
    public static function deny(string $message, int $status = 403,
                                ?string $resourceType = null, $resourceId = null,
                                string $reason = 'AUTHORIZATION_DENIED'): void
    {
        self::log($resourceType, $resourceId, $reason, 'warning');
        Router::jsonResponse(['error' => $message], $status);
    }

    /**
     * Persist a security audit entry (best effort).
     */
    public static function log(?string $resourceType = null, $resourceId = null,
                               string $reason = 'AUTHORIZATION_DENIED',
                               string $severity = 'warning'): void
    {
        try {
            $db = Database::getInstance();
            $db->insert('security_audit_log', [
                'user_id'       => Router::getAuthUserId(),
                'ip_address'    => $_SERVER['REMOTE_ADDR'] ?? '0.0.0.0',
                'action'        => $reason,
                'resource_type' => $resourceType,
                'resource_id'   => $resourceId !== null ? (int) $resourceId : null,
                'details'       => json_encode([
                    'role'     => self::role(),
                    'actor_id' => Router::getAuthActorId(),
                    'uri'      => parse_url($_SERVER['REQUEST_URI'] ?? '', PHP_URL_PATH) ?? '',
                    'reason'   => $reason,
                ]),
                'severity' => $severity,
            ]);
        } catch (\Throwable $e) {
            error_log('AccessPolicy audit log failed: ' . $e->getMessage());
        }
    }
}