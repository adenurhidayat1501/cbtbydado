<?php

namespace App\Core;

use App\Config\App;

class Session
{
    private static bool $started = false;

    public static function start(): void
    {
        if (self::$started || session_status() === PHP_SESSION_ACTIVE) {
            self::$started = true;
            return;
        }

        $lifetime = App::get('session_lifetime', 7200);
        $cookieName = App::get('session_cookie_name', 'dadocbt_session');

        if (!headers_sent()) {
            ini_set('session.gc_maxlifetime', (string)$lifetime);
            ini_set('session.cookie_httponly', '1');
            ini_set('session.use_only_cookies', '1');

            session_name($cookieName);
            session_set_cookie_params([
                'lifetime' => $lifetime,
                'path' => '/',
                'domain' => '',
                'secure' => isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] === 'on',
                'httponly' => true,
                'samesite' => 'Lax'
            ]);
        }

        if (session_status() !== PHP_SESSION_ACTIVE) {
            @session_start();
        }
        self::$started = true;

        // Manage flash data lifecycle
        if (isset($_SESSION['_flash_next'])) {
            $_SESSION['_flash'] = $_SESSION['_flash_next'];
            unset($_SESSION['_flash_next']);
        } else {
            $_SESSION['_flash'] = $_SESSION['_flash'] ?? [];
        }
    }

    public static function set(string $key, $value): void
    {
        self::start();
        $_SESSION[$key] = $value;
    }

    public static function get(string $key, $default = null)
    {
        self::start();
        return $_SESSION[$key] ?? $default;
    }

    public static function has(string $key): bool
    {
        self::start();
        return isset($_SESSION[$key]);
    }

    public static function remove(string $key): void
    {
        self::start();
        unset($_SESSION[$key]);
    }

    public static function destroy(): void
    {
        self::start();
        $_SESSION = [];
        if (!headers_sent() && ini_get('session.use_cookies')) {
            $params = session_get_cookie_params();
            setcookie(
                session_name(),
                '',
                time() - 42000,
                $params['path'],
                $params['domain'],
                $params['secure'],
                $params['httponly']
            );
        }
        if (session_status() === PHP_SESSION_ACTIVE) {
            @session_destroy();
        }
        self::$started = false;
    }

    public static function regenerate(bool $deleteOld = true): void
    {
        self::start();
        if (session_status() === PHP_SESSION_ACTIVE && !headers_sent()) {
            @session_regenerate_id($deleteOld);
        }
    }

    public static function setFlash(string $key, string $message): void
    {
        self::start();
        $_SESSION['_flash_next'][$key] = $message;
    }

    public static function getFlash(string $key, ?string $default = null): ?string
    {
        self::start();
        $msg = $_SESSION['_flash'][$key] ?? $default;
        unset($_SESSION['_flash'][$key]);
        return $msg;
    }

    public static function hasFlash(string $key): bool
    {
        self::start();
        return isset($_SESSION['_flash'][$key]);
    }
}
