<?php

$files = [
    'AdminThrottleLogin',
    'AdminLoginRateLimiter',
    'AdminAuth',
    'AdminGuest',
    'AdminTwoFactor',
];

$base = 'c:/Users/Admin/Desktop/Rentalflow/RentFlow/laravel/backend/app/Http/Middleware/';

foreach ($files as $file) {
    $path = $base . $file . '.php';
    $content = file_get_contents($path);
    $cmd = 'php -l ' . escapeshellarg($path);
    $ok = shell_exec($cmd) ?: 'OK';
    echo "$file: " . trim($ok) . "\n";
}
