<?php

/**
 * ==============================================================================
 * DADO CBT v1.5.3 — CLI DATABASE MIGRATION RUNNER
 * ==============================================================================
 * Digunakan oleh Auto Update GitHub dan Auto Installer VPS untuk memastikan
 * seluruh 42 tabel, composite index, dan skema terbaru terpasang tanpa menghapus data.
 */

declare(strict_types=1);

define('ROOT_PATH', dirname(__DIR__));
define('APP_PATH', ROOT_PATH . '/app');
define('PUBLIC_PATH', ROOT_PATH . '/public');

// 1. Load Environment (.env)
$envFile = ROOT_PATH . '/.env';
if (file_exists($envFile)) {
    $lines = file($envFile, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);
    foreach ($lines as $line) {
        $trimmed = trim($line);
        if (empty($trimmed) || str_starts_with($trimmed, '#')) {
            continue;
        }
        $parts = explode('=', $trimmed, 2);
        if (count($parts) === 2) {
            $key = trim($parts[0]);
            $val = trim($parts[1], " \t\n\r\0\x0B\"'");
            $_ENV[$key] = $val;
            putenv("{$key}={$val}");
        }
    }
}

// 2. Autoloader
spl_autoload_register(function ($class) {
    $prefix = 'App\\';
    $len = strlen($prefix);
    if (strncmp($prefix, $class, $len) !== 0) {
        return;
    }
    $relativeClass = substr($class, $len);
    $file = APP_PATH . '/' . str_replace('\\', '/', $relativeClass) . '.php';
    if (file_exists($file)) {
        require $file;
    }
});

// 3. Helper Functions
require APP_PATH . '/Helpers/functions.php';

echo "========================================================\n";
echo "   DADO CBT v1.5.3 — DATABASE MIGRATION ENGINE\n";
echo "========================================================\n";
echo "Waktu       : " . date('Y-m-d H:i:s') . "\n";
echo "Database    : " . ($_ENV['DB_NAME'] ?? 'garudacbt') . "\n";
echo "Host        : " . ($_ENV['DB_HOST'] ?? '127.0.0.1') . ":" . ($_ENV['DB_PORT'] ?? '3306') . "\n";
echo "--------------------------------------------------------\n";

try {
    $schemaFile = ROOT_PATH . '/database/schema.sql';
    if (!file_exists($schemaFile)) {
        throw new Exception("File schema.sql tidak ditemukan di: {$schemaFile}");
    }

    $dbUtility = new \App\Services\DatabaseUtilityService();
    $result = $dbUtility->verifyAndMigrateSchema($schemaFile);

    echo "Tabel Terdaftar Awal : " . $result['total_existing'] . "\n";
    echo "Tabel Baru Dibuat    : " . $result['created_count'] . "\n";
    echo "Tabel Dilewati       : " . $result['skipped_count'] . "\n";

    if (!empty($result['created_tables'])) {
        echo "Tabel yang ditambahkan:\n";
        foreach ($result['created_tables'] as $tbl) {
            echo "  [+] {$tbl}\n";
        }
    }

    if (!empty($result['errors'])) {
        echo "\nPeringatan/Kesalahan Migrasi:\n";
        foreach ($result['errors'] as $err) {
            echo "  [!] {$err}\n";
        }
    }

    echo "--------------------------------------------------------\n";
    echo ">> Migrasi skema database berhasil diselesaikan.\n";
    echo "========================================================\n";
    exit(0);
} catch (\Throwable $e) {
    echo "\n[ERROR CRITICAL] Migrasi gagal: " . $e->getMessage() . "\n";
    echo "File: " . $e->getFile() . " Line: " . $e->getLine() . "\n";
    echo "========================================================\n";
    exit(1);
}

