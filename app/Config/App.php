<?php

namespace App\Config;

class App
{
    public static function get(string $key, $default = null)
    {
        $config = [
            'name' => $_ENV['APP_NAME'] ?? 'DADO CBT',
            'version' => '1.5.3-FIXED',
            'env' => $_ENV['APP_ENV'] ?? 'development',
            'debug' => filter_var($_ENV['APP_DEBUG'] ?? true, FILTER_VALIDATE_BOOLEAN),
            'url' => rtrim($_ENV['APP_URL'] ?? 'http://localhost:8080', '/'),
            'timezone' => 'Asia/Jakarta',
            'session_lifetime' => (int)($_ENV['SESSION_LIFETIME'] ?? 7200),
            'session_cookie_name' => $_ENV['SESSION_COOKIE_NAME'] ?? 'dadocbt_session',
        ];

        return $config[$key] ?? $default;
    }
}

