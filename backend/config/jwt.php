<?php
/**
 * JWT Configuration
 * Uses Env loader for .env support
 */
require_once __DIR__ . '/../app/Core/Env.php';

\App\Core\Env::load();

return [
    'secret_key'      => \App\Core\Env::get('JWT_SECRET', 'rf_jwt_secret_8f2a9c1d3e5b7'),
    'algorithm'       => 'HS256',
    'expiry_seconds'  => 86400 * 7, // 7 days
    'issuer'          => 'rentflow.app',
];
