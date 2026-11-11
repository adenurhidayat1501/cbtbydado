<?php

namespace App\Config;

class Database
{
    public static function getConfig(): array
    {
        $dbName = $_ENV['DB_DATABASE'] ?? $_ENV['DB_NAME'] ?? 'dadocbt';
        $dbUser = $_ENV['DB_USERNAME'] ?? $_ENV['DB_USER'] ?? 'root';
        $dbPass = $_ENV['DB_PASSWORD'] ?? $_ENV['DB_PASS'] ?? '';

        $mysqlInitCommand = defined('\PDO::MYSQL_ATTR_INIT_COMMAND')
            ? \PDO::MYSQL_ATTR_INIT_COMMAND
            : 1002;

        return [
            'driver' => $_ENV['DB_CONNECTION'] ?? 'mysql',
            'host' => $_ENV['DB_HOST'] ?? '127.0.0.1',
            'port' => (int)($_ENV['DB_PORT'] ?? 3306),
            'database' => $dbName,
            'username' => $dbUser,
            'password' => $dbPass,
            'charset' => $_ENV['DB_CHARSET'] ?? 'utf8mb4',
            'collation' => $_ENV['DB_COLLATION'] ?? 'utf8mb4_unicode_ci',
            'options' => [
                \PDO::ATTR_ERRMODE => \PDO::ERRMODE_EXCEPTION,
                \PDO::ATTR_DEFAULT_FETCH_MODE => \PDO::FETCH_ASSOC,
                \PDO::ATTR_EMULATE_PREPARES => false,
                $mysqlInitCommand => "SET NAMES utf8mb4 COLLATE utf8mb4_unicode_ci",
            ],
        ];
    }
}
