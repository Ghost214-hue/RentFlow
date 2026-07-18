<?php
/**
 * Get the base path for the application
 * Works correctly whether app is in root or subdirectory
 */
function getBasePath(): string {
    $requestUri = parse_url($_SERVER['REQUEST_URI'] ?? '/', PHP_URL_PATH);
    // Remove the last path segment to get base
    // e.g. /RentalFlow/signin -> /RentalFlow
    //       /dashboard -> /
    $base = preg_replace('#/[^/]*$#', '', $requestUri);
    return rtrim($base, '/');
}