<?php
// Shared base path for all pages
// Priority: environment config > computed from actual script location

// Load .env from project root if not already loaded
if (!isset($_ENV['BASE_PATH']) && !getenv('BASE_PATH') && file_exists(__DIR__ . '/../../.env')) {
    foreach (file(__DIR__ . '/../../.env', FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES) as $line) {
        $line = trim($line);
        if ($line === '' || str_starts_with($line, '#')) continue;
        $parts = explode('=', $line, 2);
        if (count($parts) !== 2) continue;
        $key = trim($parts[0]);
        $value = trim($parts[1]);
        if ((str_starts_with($value, '"') && str_ends_with($value, '"')) ||
            (str_starts_with($value, "'") && str_ends_with($value, "'"))) {
            $value = substr($value, 1, -1);
        }
        if (!isset($_ENV[$key])) $_ENV[$key] = $value;
        if (!getenv($key)) putenv("{$key}={$value}");
    }
}

$override = ($_ENV['BASE_PATH'] ?? getenv('BASE_PATH'));
if ($override !== null && $override !== '') {
    $basePath = rtrim((string) $override, '/');
    if ($basePath === '') $basePath = '/';
} else {
    $script = rtrim(str_replace('\\', '/', dirname($_SERVER['SCRIPT_NAME'])), '/');
    if (strpos($script, '/frontend/pages') !== false || strpos($script, '/frontend/public') !== false) {
        // /RentalFlow/frontend/public -> go up 3 levels to /RentalFlow
        // /RentalFlow/frontend/pages -> go up 2 levels to /RentalFlow
        $levels = strpos($script, '/frontend/public') !== false ? 3 : 2;
        $base = $script;
        for ($i = 0; $i < $levels; $i++) {
            $base = dirname($base);
        }
        $basePath = $base === '' || $base === '/' ? '/' : $base;
    } else {
        $basePath = $script === '' ? '/' : $script;
    }
}

if (!function_exists('getBasePath')) {
    function getBasePath(): string {
        global $basePath;
        return (string) $basePath;
    }
}