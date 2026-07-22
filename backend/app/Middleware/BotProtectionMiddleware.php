<?php

namespace App\Middleware;

use App\Core\Database;
use App\Core\Router;

class BotProtectionMiddleware
{
    private static array $suspiciousPatterns = [
        // SQL injection patterns
        'union\s+select',
        'select\s+.*\s+from',
        'insert\s+into',
        'update\s+.*\s+set',
        'delete\s+from',
        'drop\s+table',
        'javascript:',
        'on\w+\s*=\s*[\'"]',
        '<script',
        '</script>',
        'eval\s*\(',
        'document\.cookie',
        // Path traversal
        '\.\.\/',
        '\.\.\\',
        '\/etc\/passwd',
        '\/proc\/',
        // XSS patterns
        '<iframe',
        '<svg\s+on',
        'onload\s*=',
        'onerror\s*=',
        'onclick\s*=',
    ];

    public static function check(): void
    {
        $db = Database::getInstance();
        $ip = self::getClientIP();

        // Check if IP is blocked
        $blocked = $db->fetchOne(
            "SELECT reason, blocked_until FROM blocked_ips WHERE ip_address = ? AND blocked_until > NOW()",
            [$ip]
        );
        
        if ($blocked) {
            Router::jsonResponse([
                'error' => 'Access denied',
                'reason' => $blocked['reason'],
                'blocked_until' => $blocked['blocked_until']
            ], 403);
        }

        // Get request data for analysis
        $userAgent = $_SERVER['HTTP_USER_AGENT'] ?? '';
        $requestMethod = $_SERVER['REQUEST_METHOD'] ?? '';
        $queryString = $_SERVER['QUERY_STRING'] ?? '';
        $contentType = $_SERVER['CONTENT_TYPE'] ?? '';
        $requestBody = '';
        if (stripos($contentType, 'multipart/form-data') === false) {
            // Use Router's cached body to avoid exhausting php://input
            Router::cacheRawBody();
            $requestBody = '';
        }
        
        // Combine all data for scanning
        $allData = strtolower($userAgent . ' ' . $queryString . ' ' . $requestBody);
        
        // Check for suspicious patterns
        foreach (self::$suspiciousPatterns as $pattern) {
            if (@preg_match('~' . $pattern . '~i', $allData)) {
                self::blockIP($ip, 'Suspicious pattern detected: ' . $pattern);
                Router::jsonResponse(['error' => 'Invalid request detected'], 400);
            }
        }

        // Check for common bot user agents
        $suspiciousAgents = ['bot', 'crawler', 'spider', 'scraper', 'curl', 'wget', 'python', 'java', 'php'];
        $isSuspiciousAgent = false;
        
        foreach ($suspiciousAgents as $bot) {
            if (stripos($userAgent, $bot) !== false) {
                $isSuspiciousAgent = true;
                break;
            }
        }
        
        // Allow legitimate bots but track them
        if ($isSuspiciousAgent && !self::isLegitimateBot($userAgent)) {
            $db->query(
                "UPDATE bot_detections SET attempts = attempts + 1, last_seen = ? WHERE ip_address = ?",
                [date('Y-m-d H:i:s'), $ip]
            );
        }

        // Honeypot check for forms
        $honeypotFields = ['email2', 'phone2', 'website', 'url', 'hp'];
        foreach ($honeypotFields as $field) {
            if (!empty($_POST[$field]) || !empty($_GET[$field])) {
                self::blockIP($ip, 'Honeypot triggered: ' . $field);
                // Silently fail to not alert the bot
                Router::jsonResponse(['message' => 'Form submitted successfully'], 200);
            }
        }

        // Track request patterns
        try {
            $db->insert('bot_detections', [
                'ip_address' => $ip,
                'user_agent' => $userAgent,
                'endpoint' => $_SERVER['REQUEST_URI'] ?? '/',
                'method' => $requestMethod,
                'attempts' => 1,
            ]);
        } catch (\Exception $e) {
            // Ignore duplicate key errors
            error_log('Bot detection tracking error: ' . $e->getMessage());
        }

        // Cleanup old records periodically
        self::cleanup();
    }

    private static function blockIP(string $ip, string $reason): void
    {
        $db = Database::getInstance();
        $db->query(
            "INSERT INTO blocked_ips (ip_address, reason, blocked_until) VALUES (?, ?, DATE_ADD(NOW(), INTERVAL 1 HOUR))
             ON DUPLICATE KEY UPDATE reason = VALUES(reason), blocked_until = VALUES(blocked_until)",
            [$ip, $reason]
        );
    }

    private static function isLegitimateBot(string $userAgent): bool
    {
        $legitimateBots = [
            'googlebot',
            'bingbot',
            'slurp',           // Yahoo
            'duckduckbot',
            'baiduspider',
            'yandexbot',
            'facebookexternalhit',
            'twitterbot',
            'linkedinbot',
            'applebot',
            'embedly',
            'quora link preview',
        ];

        $ua = strtolower($userAgent);
        foreach ($legitimateBots as $bot) {
            if (strpos($ua, $bot) !== false) {
                return true;
            }
        }
        return false;
    }

    private static function cleanup(): void
    {
        $db = Database::getInstance();
        // Delete records older than 7 days
        $db->query("DELETE FROM bot_detections WHERE last_seen < DATE_SUB(NOW(), INTERVAL 7 DAY)");
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
}
