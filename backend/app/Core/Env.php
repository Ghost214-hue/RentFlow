<?php
/**
 * Simple .env Loader
 */
namespace App\Core;

class Env
{
    private static array $loaded = [];

    /**
     * Load environment variables from .env file
     */
    public static function load(?string $path = null): void
    {
        if ($path === null && !empty(self::$loaded)) {
            return;
        }

        if ($path === null) {
            $base = dirname(__DIR__, 3);
            $path = file_exists($base . '/.env.production') ? $base . '/.env.production' : $base . '/.env';
        }

        if (!file_exists($path)) return;

        $lines = file($path, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);
        foreach ($lines as $line) {
            $line = trim($line);
            if ($line === '' || str_starts_with($line, '#')) continue;

            $parts = explode('=', $line, 2);
            if (count($parts) !== 2) continue;

            $key = trim($parts[0]);
            $value = trim($parts[1]);

            // Remove surrounding quotes if present
            if ((str_starts_with($value, '"') && str_ends_with($value, '"')) ||
                (str_starts_with($value, "'") && str_ends_with($value, "'"))) {
                $value = substr($value, 1, -1);
            }

            putenv("{$key}={$value}");
            $_ENV[$key] = $value;
        }

        self::$loaded = $_ENV;
    }

    /**
     * Get an environment variable with a default fallback
     */
    public static function get(string $key, mixed $default = null): mixed
    {
        self::load();
        return $_ENV[$key] ?? getenv($key) ?: $default;
    }
}