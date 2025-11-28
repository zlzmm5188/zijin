<?php
/**
 * Database configuration
 * Uses environment variables for production deployment
 * Leverages vlucas/phpdotenv for secure environment variable loading
 */

// Use phpdotenv if available (loaded via composer autoload in bootstrap.php)
// Environment variables should already be loaded by bootstrap.php

/**
 * Safely get environment variable with validation
 */
function safe_env($key, $default = null) {
    $value = $_ENV[$key] ?? getenv($key) ?: $default;
    // Sanitize value to prevent injection
    if ($value !== null && $value !== $default) {
        $value = preg_replace('/[^\w\-\.\:\/\@]/', '', $value);
    }
    return $value;
}

return [
    'host'     => safe_env('DB_HOST', 'localhost'),
    'port'     => safe_env('DB_PORT', '3306'),
    'database' => safe_env('DB_DATABASE', 'providence'),
    'username' => safe_env('DB_USERNAME', 'root'),
    'password' => $_ENV['DB_PASSWORD'] ?? getenv('DB_PASSWORD') ?: '',
    'charset'  => safe_env('DB_CHARSET', 'utf8mb4'),
    'prefix'   => safe_env('DB_PREFIX', ''),
];
