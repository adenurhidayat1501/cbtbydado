<?php

namespace App\Controllers\Settings;

use App\Core\Controller;
use App\Core\Database;
use App\Services\DatabaseUtilityService;
use PDO;
use Exception;

class DatabaseController extends Controller
{
    protected DatabaseUtilityService $dbService;

    public function __construct()
    {
        $this->dbService = new DatabaseUtilityService();
    }

    /**
     * Halaman Backup, Restore, dan Update Database
     */
    public function index(): void
    {
        $this->requireRole('admin');
        $db = Database::getInstance()->getPdo();
        $dbName = $_ENV['DB_NAME'] ?? 'garudacbt';

        // Hitung total tabel dan estimasi ukuran database
        $stmtTables = $db->query("SHOW TABLES");
        $tables = $stmtTables->fetchAll(PDO::FETCH_COLUMN);

        $stmtSize = $db->prepare(
            "SELECT ROUND(SUM(data_length + index_length) / 1024 / 1024, 2) AS size_mb 
             FROM information_schema.TABLES 
             WHERE table_schema = ?"
        );
        $stmtSize->execute([$dbName]);
        $sizeRow = $stmtSize->fetch();
        $dbSizeMb = $sizeRow['size_mb'] ?? '0.00';

        // Deteksi status Git & Webhook
        $gitBranch = 'main';
        $gitCommit = '-';
        $gitAvailable = false;
        $repoPath = defined('ROOT_PATH') ? ROOT_PATH : dirname(__DIR__, 2);

        if (is_dir($repoPath . '/.git')) {
            $gitAvailable = true;
            @exec("cd " . escapeshellarg($repoPath) . " && git rev-parse --abbrev-ref HEAD 2>/dev/null", $branchOut);
            @exec("cd " . escapeshellarg($repoPath) . " && git log -1 --pretty=format:'%h - %s (%cr)' 2>/dev/null", $commitOut);
            if (!empty($branchOut[0])) {
                $gitBranch = trim($branchOut[0]);
            }
            if (!empty($commitOut[0])) {
                $gitCommit = trim($commitOut[0], "'\"");
            }
        }

        $webhookUrl = base_url('webhook.php');
        $webhookSecret = $_ENV['GITHUB_WEBHOOK_SECRET'] ?? 'DadoCbtSecretWebhookKey2026';

        $this->render('settings/database', [
            'title' => 'Utilitas & Pemeliharaan Database',
            'dbName' => $dbName,
            'tableCount' => count($tables),
            'dbSizeMb' => $dbSizeMb,
            'tables' => $tables,
            'gitAvailable' => $gitAvailable,
            'gitBranch' => $gitBranch,
            'gitCommit' => $gitCommit,
            'webhookUrl' => $webhookUrl,
            'webhookSecret' => $webhookSecret
        ]);
    }

    /**
     * Download Backup Database SQL
     */
    public function backup(): void
    {
        $this->requireRole('admin');

        try {
            $sqlContent = $this->dbService->generateBackupSql();
            $filename = "DADOCBT_Backup_" . date('Ymd_His') . ".sql";

            header('Content-Type: application/sql; charset=UTF-8');
            header("Content-Disposition: attachment; filename=\"{$filename}\"");
            header('Content-Length: ' . strlen($sqlContent));
            header('Pragma: no-cache');
            header('Expires: 0');

            echo $sqlContent;
            exit;
        } catch (Exception $e) {
            $_SESSION['flash_error'] = 'Gagal membuat backup database: ' . $e->getMessage();
            $this->redirect('/settings/database');
        }
    }

    /**
     * Restore Database dari File SQL
     */
    public function restore(): void
    {
        $this->requireRole('admin');

        if (empty($_FILES['sql_file']['tmp_name'])) {
            $_SESSION['flash_error'] = 'Silakan pilih file SQL cadangan untuk direstore.';
            $this->redirect('/settings/database');
            return;
        }

        $filePath = $_FILES['sql_file']['tmp_name'];
        $sqlContent = file_get_contents($filePath);

        try {
            $result = $this->dbService->restoreFromSql($sqlContent);
            if ($result['success']) {
                $_SESSION['flash_success'] = "Database berhasil dipulihkan! ({$result['executed']} perintah SQL berhasil dieksekusi).";
            } else {
                $errCount = count($result['errors']);
                $_SESSION['flash_warning'] = "Restore selesai dengan catatan ({$result['executed']} perintah berhasil, {$errCount} kesalahan dilewati).";
            }
        } catch (Exception $e) {
            $_SESSION['flash_error'] = 'Gagal merestore database: ' . $e->getMessage();
        }

        $this->redirect('/settings/database');
    }

    /**
     * Database Update (Sinkronisasi Skema 42 Tabel)
     */
    public function update(): void
    {
        $this->requireRole('admin');

        try {
            $result = $this->dbService->updateDatabaseSchema();
            if ($result['created_count'] > 0) {
                $tableList = implode(', ', $result['created_tables']);
                $_SESSION['flash_success'] = "Skema database berhasil diperbarui! Berhasil membuat {$result['created_count']} tabel baru: {$tableList}.";
            } else {
                $_SESSION['flash_success'] = "Struktur database sudah mutakhir. Seluruh {$result['total_existing']} tabel terverifikasi lengkap.";
            }
        } catch (Exception $e) {
            $_SESSION['flash_error'] = 'Gagal memperbarui skema database: ' . $e->getMessage();
        }

        $this->redirect('/settings/database');
    }

    /**
     * Trigger Auto-Update Aplikasi DADO CBT dari GitHub
     */
    public function appUpdate(): void
    {
        $this->requireRole('admin');

        $repoPath = defined('ROOT_PATH') ? ROOT_PATH : dirname(__DIR__, 2);
        $script = $repoPath . '/deploy/update.sh';

        if (file_exists($script) && strtoupper(substr(PHP_OS, 0, 3)) !== 'WIN') {
            $output = [];
            $returnVar = 0;
            @exec("sudo " . escapeshellarg($script) . " 2>&1", $output, $returnVar);
            $outputStr = implode("\n", $output);

            if ($returnVar === 0) {
                $_SESSION['flash_success'] = "Pembaruan GitHub berhasil diselesaikan!\nLog:\n" . substr($outputStr, -400);
            } else {
                $_SESSION['flash_error'] = "Pembaruan gagal (Kode {$returnVar}):\nLog:\n" . substr($outputStr, -400);
            }
        } else {
            // Lingkungan lokal / Windows / Fallback Skema
            try {
                $result = $this->dbService->updateDatabaseSchema();
                $_SESSION['flash_success'] = "Verifikasi basis data berhasil (42 tabel aktif). Pada lingkungan VPS Ubuntu, skrip deploy/update.sh akan menjalankan git pull otomatis.";
            } catch (Exception $e) {
                $_SESSION['flash_error'] = 'Gagal melakukan pembaruan: ' . $e->getMessage();
            }
        }

        $this->redirect('/settings/database');
    }
}
