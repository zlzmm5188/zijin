<?php
/**
 * Database configuration
 * Uses environment variables for production deployment
 */

// Load environment variables from .env file if it exists
if (file_exists(__DIR__ . '/../../.env')) {
    $envFile = file(__DIR__ . '/../../.env', FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);
    foreach ($envFile as $line) {
        if (strpos(trim($line), '#') === 0) continue;
        if (strpos($line, '=') !== false) {
            list($name, $value) = explode('=', $line, 2);
            $name = trim($name);
            $value = trim($value);
            if (!empty($name) && !isset($_ENV[$name])) {
                $_ENV[$name] = $value;
                putenv("$name=$value");
            }
        }
    }
}

return [
    'host'     => $_ENV['DB_HOST'] ?? getenv('DB_HOST') ?: 'localhost',
    'port'     => $_ENV['DB_PORT'] ?? getenv('DB_PORT') ?: '3306',
    'database' => $_ENV['DB_DATABASE'] ?? getenv('DB_DATABASE') ?: 'providence',
    'username' => $_ENV['DB_USERNAME'] ?? getenv('DB_USERNAME') ?: 'root',
    'password' => $_ENV['DB_PASSWORD'] ?? getenv('DB_PASSWORD') ?: '',
    'charset'  => $_ENV['DB_CHARSET'] ?? getenv('DB_CHARSET') ?: 'utf8mb4',
    'prefix'   => $_ENV['DB_PREFIX'] ?? getenv('DB_PREFIX') ?: '',
];
