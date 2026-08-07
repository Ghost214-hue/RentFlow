<?php
/**
 * Database Configuration - MySQL
 * Uses Env loader for .env support
 */
require_once __DIR__ . '/../app/Core/Env.php';

// Load environment variables - prefer .env for localhost, .env.production for production
if (!\App\Core\Env::get('DB_HOST')) {
    $isLocalhost = in_array($_SERVER['HTTP_HOST'] ?? '', ['localhost', '127.0.0.1'], true) ||
                   str_starts_with($_SERVER['HTTP_HOST'] ?? '', 'localhost:');
    if ($isLocalhost) {
        \App\Core\Env::load(dirname(__DIR__) . '/../.env');
    } else {
        $productionEnv = dirname(__DIR__) . '/.env.production';
        if (file_exists($productionEnv)) {
            \App\Core\Env::load($productionEnv);
        } else {
            \App\Core\Env::load();
        }
    }
}

// Parse host and port from DB_HOST if it contains a port (e.g. localhost:3306)
$hostEnv = \App\Core\Env::get('DB_HOST', '127.0.0.1');
$portEnv = \App\Core\Env::get('DB_PORT', 3306);
if (strpos($hostEnv, ':') !== false) {
    [$hostEnv, $portEnv] = explode(':', $hostEnv, 2);
    $portEnv = (int) $portEnv;
}

// With mysqli, "localhost" prefers a Unix socket. Cron often runs without the
// same socket path as Apache/PHP-FPM, so localhost:3306 should mean TCP.
if ($hostEnv === 'localhost' && (int) $portEnv > 0) {
    $hostEnv = '127.0.0.1';
}

return [
    'host'     => $hostEnv,
    'port'     => $portEnv,
    'dbname'   => \App\Core\Env::get('DB_NAME', 'rentaflow'),
    'username' => \App\Core\Env::get('DB_USER', 'root'),
    'password' => \App\Core\Env::get('DB_PASS', ''),
    'charset'  => 'utf8mb4',
];
