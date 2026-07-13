<?php
/**
 * Security Middleware Initialization
 * Registers global security middleware
 */
namespace App\Core;

use App\Middleware\BotProtectionMiddleware;
use App\Middleware\RateLimitMiddleware;

class SecurityMiddleware
{
    public static function initialize(): void
    {
        // Apply bot protection to all requests
        BotProtectionMiddleware::check();
        
        // Apply rate limiting (except for certain paths)
        $uri = parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH);
        
        // Skip rate limiting for public pages and static assets
        $publicPaths = ['/signin', '/signup', '/forgot-password', '/terms-and-conditions', '/css/', '/js/', '/images/'];
        $isPublic = false;
        
        foreach ($publicPaths as $path) {
            if (str_starts_with($uri, $path)) {
                $isPublic = true;
                break;
            }
        }
        
        if (!$isPublic) {
            RateLimitMiddleware::check('api');
        }
    }
}