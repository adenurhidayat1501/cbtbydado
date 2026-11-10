<?php

namespace App\Services;

use App\Core\Database;
use PDO;
use Exception;

class DatabaseUtilityService
{
    protected PDO $db;
    protected string $dbName;

    public function __construct()
    {
        $this->db = Database::getInstance()->getPdo();
        $this->dbName = $_ENV['DB_NAME'] ?? 'garudacbt';
    }

    /**
     * Generate full SQL dump of the database
     */
    public function generateBackupSql(): string
    {
        $sql = "-- ========================================================\n";
        $sql .= "--   DADO CBT v1.5.3 DATABASE BACKUP                      \n";
        $sql .= "--   Generated: " . date('Y-m-d H:i:s') . "               \n";
        $sql .= "--   Database: " . $this->dbName . "                      \n";
        $sql .= "-- ========================================================\n\n";
        $sql .= "SET FOREIGN_KEY_CHECKS = 0;\n";
        $sql .= "SET SQL_MODE = 'NO_AUTO_VALUE_ON_ZERO';\n";
        $sql .= "SET time_zone = '+00:00';\n\n";

        // Ambil seluruh nama tabel
        $stmtTables = $this->db->query("SHOW TABLES");
        $tables = $stmtTables->fetchAll(PDO::FETCH_COLUMN);

        foreach ($tables as $table) {
            $sql .= "-- --------------------------------------------------------\n";
            $sql .= "-- Structure for table `{$table}`\n";
            $sql .= "-- --------------------------------------------------------\n";
            $sql .= "DROP TABLE IF EXISTS `{$table}`;\n";

            $stmtCreate = $this->db->query("SHOW CREATE TABLE `{$table}`");
            $rowCreate = $stmtCreate->fetch(PDO::FETCH_NUM);
            if ($rowCreate && !empty($rowCreate[1])) {
                $sql .= $rowCreate[1] . ";\n\n";
            }

            // Data dump
            $stmtData = $this->db->query("SELECT * FROM `{$table}`");
            $rows = $stmtData->fetchAll(PDO::FETCH_ASSOC);

            if (!empty($rows)) {
                $sql .= "-- Dumping data for table `{$table}`\n";
                $columns = array_keys($rows[0]);
                $escapedCols = array_map(fn($c) => "`{$c}`", $columns);
                $colList = implode(', ', $escapedCols);

                $batchSize = 100;
                $chunks = array_chunk($rows, $batchSize);

                foreach ($chunks as $chunk) {
                    $valSets = [];
                    foreach ($chunk as $row) {
                        $values = [];
                        foreach ($row as $val) {
                            if ($val === null) {
                                $values[] = 'NULL';
                            } else {
                                $values[] = $this->db->quote((string)$val);
                            }
                        }
                        $valSets[] = "(" . implode(', ', $values) . ")";
                    }
                    $sql .= "INSERT INTO `{$table}` ({$colList}) VALUES\n" . implode(",\n", $valSets) . ";\n";
                }
                $sql .= "\n";
            }
        }

        $sql .= "SET FOREIGN_KEY_CHECKS = 1;\n";
        $sql .= "-- Backup completed successfully.\n";

        return $sql;
    }

    /**
     * Restore database from raw SQL content or file
     */
    public function restoreFromSql(string $sqlContent): array
    {
        if (empty(trim($sqlContent))) {
            throw new Exception("File SQL backup kosong atau tidak valid.");
        }

        $this->db->exec("SET FOREIGN_KEY_CHECKS = 0");

        // Parse query SQL yang aman terhadap komentar dan delimiter
        $queries = $this->splitSqlQueries($sqlContent);
        $executedCount = 0;
        $errors = [];

        foreach ($queries as $query) {
            $q = trim($query);
            if (empty($q)) {
                continue;
            }

            try {
                $this->db->exec($q);
                $executedCount++;
            } catch (Exception $e) {
                $errors[] = [
                    'query' => substr($q, 0, 100) . '...',
                    'error' => $e->getMessage()
                ];
            }
        }

        $this->db->exec("SET FOREIGN_KEY_CHECKS = 1");

        return [
            'success' => empty($errors),
            'executed' => $executedCount,
            'errors' => $errors
        ];
    }

    /**
     * Memeriksa dan memperbarui struktur tabel yang belum ada sesuai skema standar
     */
    public function updateDatabaseSchema(?string $customSchemaFile = null): array
    {
        return $this->verifyAndMigrateSchema($customSchemaFile);
    }

    /**
     * Verifikasi dan migrasi skema tabel DADO CBT
     */
    public function verifyAndMigrateSchema(?string $customSchemaFile = null): array
    {
        if ($customSchemaFile !== null && file_exists($customSchemaFile)) {
            $schemaFile = $customSchemaFile;
        } else {
            $base = defined('ROOT_PATH') ? ROOT_PATH : (defined('BASE_PATH') ? BASE_PATH : dirname(__DIR__, 2));
            $schemaFile = $base . '/database/schema.sql';
        }

        if (!file_exists($schemaFile)) {
            throw new Exception("File referensi skema database ({$schemaFile}) tidak ditemukan.");
        }

        // Ambil daftar tabel yang saat ini ada
        $stmtExisting = $this->db->query("SHOW TABLES");
        $existingTables = $stmtExisting->fetchAll(PDO::FETCH_COLUMN);

        $schemaContent = file_get_contents($schemaFile);
        $queries = $this->splitSqlQueries($schemaContent);

        $createdTables = [];
        $skippedTables = [];
        $errors = [];

        $this->db->exec("SET FOREIGN_KEY_CHECKS = 0");

        foreach ($queries as $query) {
            $q = trim($query);
            if (empty($q)) {
                continue;
            }

            // Deteksi nama tabel pada statement CREATE TABLE
            if (preg_match('/CREATE\s+TABLE\s+(?:IF\s+NOT\s+EXISTS\s+)?`?([a-zA-Z0-9_]+)`?/i', $q, $matches)) {
                $tableName = $matches[1];
                if (!in_array($tableName, $existingTables)) {
                    try {
                        $this->db->exec($q);
                        $createdTables[] = $tableName;
                        $existingTables[] = $tableName;
                    } catch (Exception $e) {
                        $errors[] = "Gagal membuat tabel {$tableName}: " . $e->getMessage();
                    }
                } else {
                    $skippedTables[] = $tableName;
                }
            }
        }

        $this->db->exec("SET FOREIGN_KEY_CHECKS = 1");

        return [
            'total_existing' => count($existingTables),
            'created_count' => count($createdTables),
            'created_tables' => $createdTables,
            'skipped_count' => count($skippedTables),
            'errors' => $errors
        ];
    }

    /**
     * Helper pemecah query SQL per titik koma yang aman
     */
    protected function splitSqlQueries(string $sql): array
    {
        // Bersihkan komentar baris -- dan /* */
        $sql = preg_replace('/--[^\n]*\n/', "\n", $sql);
        $sql = preg_replace('/\/\*.*?\*\//s', '', $sql);

        $tokens = explode(";\n", $sql);
        $cleanQueries = [];

        foreach ($tokens as $token) {
            $t = trim($token);
            if (!empty($t)) {
                $cleanQueries[] = $t;
            }
        }

        return $cleanQueries;
    }
}
