<?php
/**
 * Security Audit Middleware
 * Logs security-relevant events for monitoring and compliance
 */
namespace App\Middleware;

use App\Core\Database;
use App\Core\Router;

class SecurityAuditMiddleware
{
    public static function log(string $action, ?int $userId = null, ?string $resourceType = null, ?int $resourceId = null, string $severity = 'info', array $details = []): void
    {
        try {
            $db = Database::getInstance();
            $ip = self::getClientIP();
            
            $db->insert('security_audit_log', [
                'user_id' => $userId,
                'ip_address' => $ip,
                'action' => $action,
                'resource_type' => $resourceType,
                'resource_id' => $resourceId,
                'details' => json_encode($details),
                'severity' => $severity
            ]);
        } catch (\Exception $e) {
            error_log('Security audit log failed: ' . $e->getMessage());
        }
    }

    public static function logAuthAttempt(string $email, bool $success, string $role = 'unknown'): void
    {
        self::log(
            $success ? 'login_success' : 'login_failed',
            null,
            'authentication',
            null,
            $success ? 'info' : 'warning',
            ['email' => $email, 'role' => $role, 'success' => $success]
        );
    }

    public static function logPasswordReset(string $email, bool $success): void
    {
        self::log(
            $success ? 'password_reset_requested' : 'password_reset_failed',
            null,
            'password_reset',
            null,
            'info',
            ['email' => $email, 'success' => $success]
        );
    }

    public static function logSensitiveAccess(string $action, ?int $userId, string $resourceType, int $resourceId): void
    {
        self::log(
            $action,
            $userId,
            $resourceType,
            $resourceId,
            'warning',
            ['timestamp' => date('c')]
        );
    }

    public static function logDataModification(string $action, ?int $userId, string $resourceType, int $resourceId, array $changes): void
    {
        self::log(
            $action,
            $userId,
            $resourceType,
            $resourceId,
            'info',
            ['changes' => $changes, 'timestamp' => date('c')]
        );
    }

    private static function getClientIP(): string
    {
        $headers = ['HTTP_CF_CONNECTING_IP', 'HTTP_X_REAL_IP', 'HTTP_X_FORWARDED_FOR', 'REMOTE_ADDR'];
        foreach ($headers as $header) {
            if (!empty($_SERVER[$header])) {
                $ips = explode(',', $_SERVER[$header]);
                return trim($ips[0]);
            }
        }
        return $_SERVER['REMOTE_ADDR'] ?? 'unknown';
    }

    /**
     * Get recent security events
     */
    public static function getRecentEvents(int $limit = 100): array
    {
        try {
            $db = Database::getInstance();
            return $db->fetchAll(
                "SELECT * FROM security_audit_log ORDER BY created_at DESC LIMIT ?",
                [$limit]
            );
        } catch (\Exception $e) {
            error_log('Failed to fetch security events: ' . $e->getMessage());
            return [];
        }
    }

    /**
     * Get security statistics
     */
    public static function getStatistics(int $hours = 24): array
    {
        try {
            $db = Database::getInstance();
            
            $failedLogins = $db->fetchOne(
                "SELECT COUNT(*) as count FROM security_audit_log 
                 WHERE action = 'login_failed' AND created_at >= DATE_SUB(NOW(), INTERVAL ? HOUR)",
                [$hours]
            );
            
            $blockedIPs = $db->fetchOne(
                "SELECT COUNT(*) as count FROM blocked_ips WHERE blocked_until > NOW()"
            );
            
            $criticalEvents = $db->fetchOne(
                "SELECT COUNT(*) as count FROM security_audit_log 
                 WHERE severity = 'critical' AND created_at >= DATE_SUB(NOW(), INTERVAL ? HOUR)",
                [$hours]
            );

            return [
                'failed_logins_24h' => $failedLogins['count'] ?? 0,
                'blocked_ips' => $blockedIPs['count'] ?? 0,
                'critical_events_24h' => $criticalEvents['count'] ?? 0,
                'period_hours' => $hours
            ];
        } catch (\Exception $e) {
            error_log('Failed to fetch security stats: ' . $e->getMessage());
            return [];
        }
    }
}