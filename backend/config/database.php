<?php
/**
 * Database Configuration - MySQL
 * Uses Env loader for .env support
 */
require_once __DIR__ . '/../app/Core/Env.php';

\App\Core\Env::load();

return [
    'host'     => \App\Core\Env::get('DB_HOST', '127.0.0.1'),
    'port'     => \App\Core\Env::get('DB_PORT', 3306),
    'dbname'   => \App\Core\Env::get('DB_NAME', 'rentflow'),
    'username' => \App\Core\Env::get('DB_USER', 'root'),
    'password' => \App\Core\Env::get('DB_PASS', ''),
    'charset'  => 'utf8mb4',
];
