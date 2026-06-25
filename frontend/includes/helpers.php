<?php
/**
 * Shared helper functions for all frontend pages
 * Provides dynamic base path for subdirectory deployment
 */

// Compute base path (e.g. /RentFlow)
$basePath = rtrim(str_replace('\\', '/', dirname($_SERVER['SCRIPT_NAME'])), '/');

// Helper to prefix a path with the base path
function baseUrl($path = '') {
    global $basePath;
    return $basePath . '/' . ltrim($path, '/');
}

// Helper to output base path in JavaScript
function jsBasePath() {
    global $basePath;
    return json_encode($basePath);
}