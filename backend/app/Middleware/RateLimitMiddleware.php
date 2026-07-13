<?php
/**
 * Rate Limiting Middleware
 * Prevents brute force attacks and API abuse
 */
namespace App\Middleware;

use App\Core\Database;
use App\Core\Router;

class RateLimitMiddleware
{
    private static array $limits = [
        // API endpoints
        'login' => ['limit' => 5, 'window' => 60],          // 5 attempts per minute
        'register' => ['limit' => 3, 'window' => 300],      // 3 per 5 minutes
        'forgot_password' => ['limit' => 3, 'window' => 300], // 3 per 5 minutes
        'api' => ['limit' => 100, 'window' => 60],          // 100 API calls per minute
        'otp' => ['limit' => 10, 'window' => 60],           // 10 per minute
    ];

    public static function check(string $type = 'api'): void
    {
        $db = Database::getInstance();
        $ip = self::getClientIP();
        $limit = self::$limits[$type] ?? self::$limits['api'];
        
        $key = "rate_limit_{$type}_{$ip}";
        
        // Check current count
        $record = $db->fetchOne(
            "SELECT attempts, window_start FROM rate_limits WHERE ip_address = ? AND type = ?",
            [$ip, $type]
        );

        $now = time();
        $windowStart = $record ? strtotime($record['window_start']) : $now;
        $attempts = $record ? (int)$record['attempts'] : 0;

        // Reset if window expired
        if ($now - $windowStart >= $limit['window']) {
            $attempts = 0;
            $windowStart = $now;
        }

        // Check if limit exceeded
        if ($attempts >= $limit['limit']) {
            $remaining = $limit['window'] - ($now - $windowStart);
            Router::jsonResponse([
                'error' => 'Too many requests. Please try again later.',
                'retry_after' => $remaining
            ], 429);
        }

        // Increment counter
        if ($record) {
            $db->update(
                'rate_limits',
                ['attempts' => $attempts + 1, 'window_start' => date('Y-m-d H:i:s', $windowStart)],
                'ip_address = ? AND type = ?',
                [$ip, $type]
            );
        } else {
            $db->insert('rate_limits', [
                'ip_address' => $ip,
                'type' => $type,
                'attempts' => 1,
                'window_start' => date('Y-m-d H:i:s', $windowStart)
            ]);
        }

        // Set rate limit headers
        header("X-RateLimit-Limit: {$limit['limit']}");
        header("X-RateLimit-Remaining: " . ($limit['limit'] - $attempts - 1));
        header("X-RateLimit-Reset: " . ($windowStart + $limit['window']));
    }

    public static function reset(string $type = 'api', ?string $ip = null): void
    {
        $db = Database::getInstance();
        $ip = $ip ?? self::getClientIP();
        $db->delete('rate_limits', 'ip_address = ? AND type = ?', [$ip, $type]);
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
     * Clean up old rate limit records (call from cron)
     */
    public static function cleanup(): int
    {
        $db = Database::getInstance();
        $result = $db->query(
            "DELETE FROM rate_limits WHERE window_start < DATE_SUB(NOW(), INTERVAL 1 DAY)"
        );
        return $result ? $result->affected_rows : 0;
    }
}