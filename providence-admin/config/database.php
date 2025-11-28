<?php
/**
 * Database configuration
 * Uses environment variables for production deployment
 */

/**
 * Get environment variable with proper null handling
 */
function get_env_value($key, $default = null) {
    // Check $_ENV first
    if (isset($_ENV[$key])) {
        return $_ENV[$key];
    }
    // Check getenv (returns false if not set)
    $value = getenv($key);
    if ($value !== false) {
        return $value;
    }
    return $default;
}

return [
    'host'     => get_env_value('DB_HOST', 'localhost'),
    'port'     => get_env_value('DB_PORT', '3306'),
    'database' => get_env_value('DB_DATABASE', 'providence'),
    'username' => get_env_value('DB_USERNAME', 'root'),
    'password' => get_env_value('DB_PASSWORD', ''),
    'charset'  => get_env_value('DB_CHARSET', 'utf8mb4'),
    'prefix'   => get_env_value('DB_PREFIX', ''),
];
