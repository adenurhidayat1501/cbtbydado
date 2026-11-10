<?php

/**
 * DADO CBT v1.5.3 — WEB INSTALLER WIZARD
 * Panduan Instalasi Mandiri & Konfigurasi Basis Data
 */

session_start();

$baseDir = dirname(__DIR__, 2);
$lockFile = $baseDir . '/installed.lock';
$envFile = $baseDir . '/.env';

// Jika sudah terinstal, cegah akses ulang
if (file_exists($lockFile) && (!isset($_GET['force']) || $_GET['force'] !== '1')) {
    echo '<!DOCTYPE html><html lang="id"><head><meta charset="UTF-8"><title>Aplikasi Sudah Terpasang</title>';
    echo '<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet"></head>';
    echo '<body class="bg-light d-flex align-items-center justify-content-center" style="height:100vh;">';
    echo '<div class="card p-4 text-center shadow-sm" style="max-width:500px;">';
    echo '<h4 class="text-success fw-bold mb-3">DADO CBT Sudah Terpasang</h4>';
    echo '<p class="text-muted small">File <code>installed.lock</code> ditemukan. Untuk menginstal ulang, silakan hapus file tersebut terlebih dahulu.</p>';
    echo '<a href="/login" class="btn btn-primary mt-2">Masuk ke Halaman Login</a>';
    echo '</div></body></html>';
    exit;
}

$step = isset($_GET['step']) ? (int)$_GET['step'] : 1;
$errorMessage = '';
$successMessage = '';

// Helper untuk membaca koneksi DB dari session atau file .env
function getInstallerDbConfig(string $envFile): ?array
{
    if (!empty($_SESSION['installer_db'])) {
        return $_SESSION['installer_db'];
    }

    if (file_exists($envFile)) {
        $lines = file($envFile, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);
        $vars = [];
        foreach ($lines as $line) {
            $line = trim($line);
            if (!empty($line) && !str_starts_with($line, '#')) {
                $parts = explode('=', $line, 2);
                if (count($parts) === 2) {
                    $vars[trim($parts[0])] = trim($parts[1], "\"'");
                }
            }
        }
        if (!empty($vars['DB_NAME'])) {
            return [
                'host' => $vars['DB_HOST'] ?? '127.0.0.1',
                'port' => $vars['DB_PORT'] ?? '3306',
                'name' => $vars['DB_NAME'] ?? 'garudacbt',
                'user' => $vars['DB_USER'] ?? 'root',
                'pass' => $vars['DB_PASS'] ?? ''
            ];
        }
    }

    return null;
}

// Handle Step 2: Database Connection
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action']) && $_POST['action'] === 'save_db') {
    $dbHost = trim($_POST['db_host'] ?? '127.0.0.1');
    $dbPort = trim($_POST['db_port'] ?? '3306');
    $dbName = trim($_POST['db_name'] ?? 'garudacbt');
    $dbUser = trim($_POST['db_user'] ?? 'root');
    $dbPass = trim($_POST['db_pass'] ?? '');

    try {
        // Uji koneksi ke server database
        $dsn = "mysql:host={$dbHost};port={$dbPort};charset=utf8mb4";
        $pdo = new PDO($dsn, $dbUser, $dbPass, [
            PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION
        ]);

        // Buat database jika belum ada
        $pdo->exec("CREATE DATABASE IF NOT EXISTS `{$dbName}` CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci");
        $pdo->exec("USE `{$dbName}`");

        // Simpan sesi instalasi DB
        $_SESSION['installer_db'] = [
            'host' => $dbHost,
            'port' => $dbPort,
            'name' => $dbName,
            'user' => $dbUser,
            'pass' => $dbPass
        ];

        header('Location: ?step=3');
        exit;
    } catch (Exception $e) {
        $errorMessage = 'Gagal terhubung ke MySQL: ' . $e->getMessage();
    }
}

// Handle Step 3: Run Schema Migration & Seed
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action']) && $_POST['action'] === 'run_migration') {
    $dbConf = getInstallerDbConfig($envFile);
    if (!$dbConf) {
        header('Location: ?step=2');
        exit;
    }

    try {
        $dsn = "mysql:host={$dbConf['host']};port={$dbConf['port']};dbname={$dbConf['name']};charset=utf8mb4";
        $pdo = new PDO($dsn, $dbConf['user'], $dbConf['pass'], [
            PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION
        ]);

        // 1. Eksekusi Schema
        $schemaPath = $baseDir . '/database/schema.sql';
        if (!file_exists($schemaPath)) {
            throw new Exception("File schema.sql tidak ditemukan di {$schemaPath}");
        }

        $schemaSql = file_get_contents($schemaPath);
        $pdo->exec("SET FOREIGN_KEY_CHECKS = 0;");
        $pdo->exec($schemaSql);
        $pdo->exec("SET FOREIGN_KEY_CHECKS = 1;");

        // 2. Eksekusi Seeder Awal
        $seedPath = $baseDir . '/database/seed.sql';
        if (file_exists($seedPath)) {
            $seedSql = file_get_contents($seedPath);
            $pdo->exec("SET FOREIGN_KEY_CHECKS = 0;");
            $pdo->exec($seedSql);
            $pdo->exec("SET FOREIGN_KEY_CHECKS = 1;");
        }

        // 3. Update file .env
        $envContent = "APP_NAME=\"DADO CBT\"\n";
        $envContent .= "APP_ENV=production\n";
        $envContent .= "APP_DEBUG=false\n";
        $envContent .= "APP_URL=http://localhost:8080\n\n";
        $envContent .= "DB_HOST={$dbConf['host']}\n";
        $envContent .= "DB_PORT={$dbConf['port']}\n";
        $envContent .= "DB_NAME={$dbConf['name']}\n";
        $envContent .= "DB_USER={$dbConf['user']}\n";
        $envContent .= "DB_PASS=\"{$dbConf['pass']}\"\n\n";
        $envContent .= "SESSION_COOKIE_NAME=dadocbt_session\n";
        $envContent .= "SESSION_LIFETIME=7200\n";
        $envContent .= "TIMEZONE=Asia/Jakarta\n";

        file_put_contents($envFile, $envContent);

        header('Location: ?step=4');
        exit;
    } catch (Exception $e) {
        $errorMessage = 'Gagal migrasi database: ' . $e->getMessage();
    }
}

// Handle Step 4: Administrator & School Profile Setup
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action']) && $_POST['action'] === 'save_admin') {
    $dbConf = getInstallerDbConfig($envFile);
    $namaSekolah = trim($_POST['nama_sekolah'] ?? 'SMP NEGERI SATU ATAP 2 CIPATUJAH');
    $jenjang = (int)($_POST['jenjang'] ?? 2);
    $adminUser = trim($_POST['admin_username'] ?? 'admin');
    $adminPass = trim($_POST['admin_password'] ?? 'admin123');

    if (!$dbConf) {
        $errorMessage = 'Koneksi basis data belum terkonfigurasi. Silakan kembali ke Langkah 2.';
    } else {
        try {
            $dsn = "mysql:host={$dbConf['host']};port={$dbConf['port']};dbname={$dbConf['name']};charset=utf8mb4";
            $pdo = new PDO($dsn, $dbConf['user'], $dbConf['pass'], [
                PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION
            ]);

            // Update / Insert Setting Sekolah
            $checkSekolah = $pdo->query("SELECT id_setting FROM setting_sekolah ORDER BY id_setting ASC LIMIT 1")->fetch(PDO::FETCH_ASSOC);
            if ($checkSekolah) {
                $stmtSekolah = $pdo->prepare("UPDATE setting_sekolah SET nama_sekolah = ?, jenjang = ? WHERE id_setting = ?");
                $stmtSekolah->execute([$namaSekolah, $jenjang, $checkSekolah['id_setting']]);
            } else {
                $stmtSekolah = $pdo->prepare("INSERT INTO setting_sekolah (nama_sekolah, jenjang) VALUES (?, ?)");
                $stmtSekolah->execute([$namaSekolah, $jenjang]);
            }

            // Update / Insert Admin Account (Kolom Primary Key adalah 'id', bukan 'id_user')
            $hashed = password_hash($adminPass, PASSWORD_BCRYPT);
            $checkAdmin = $pdo->query("SELECT id FROM users WHERE role = 'admin' ORDER BY id ASC LIMIT 1")->fetch(PDO::FETCH_ASSOC);
            if ($checkAdmin) {
                $stmtAdmin = $pdo->prepare("UPDATE users SET username = ?, password = ? WHERE id = ?");
                $stmtAdmin->execute([$adminUser, $hashed, $checkAdmin['id']]);
            } else {
                $stmtAdmin = $pdo->prepare("INSERT INTO users (username, password, role, active) VALUES (?, ?, 'admin', 1)");
                $stmtAdmin->execute([$adminUser, $hashed]);
            }

            // Kunci file instalasi
            file_put_contents($lockFile, "Installed on " . date('Y-m-d H:i:s'));

            unset($_SESSION['installer_db']);
            header('Location: ?step=5');
            exit;
        } catch (Exception $e) {
            $errorMessage = 'Gagal menyimpan konfigurasi awal: ' . $e->getMessage();
        }
    }
}
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>DADO CBT — Panduan Instalasi Sistem</title>
    <link rel="icon" type="image/png" href="/assets/img/logo.png">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.0/font/bootstrap-icons.css">
    <style>
        body { background: #f4f6f9; min-height: 100vh; display: flex; align-items: center; justify-content: center; }
        .wizard-card { width: 100%; max-width: 720px; border-radius: 12px; box-shadow: 0 10px 25px rgba(0,0,0,0.07); }
        .step-pill { width: 36px; height: 36px; line-height: 36px; border-radius: 50%; text-align: center; font-weight: bold; }
    </style>
</head>
<body class="py-5">

<div class="wizard-card bg-white p-4 p-md-5">
    <div class="text-center mb-4">
        <img src="/assets/img/logo.png" alt="Logo" style="height: 70px; width: auto; object-fit: contain;" class="mb-2">
        <h3 class="fw-bold text-primary mb-1">DADO CBT v1.5.3</h3>
        <p class="text-muted small">Pemandu Instalasi & Konfigurasi Basis Data Otomatis</p>
    </div>

    <!-- Stepper Navigation -->
    <div class="d-flex justify-content-between align-items-center mb-4 position-relative">
        <div class="text-center">
            <div class="step-pill mx-auto <?= $step >= 1 ? 'bg-primary text-white' : 'bg-light text-muted' ?>">1</div>
            <div class="small mt-1 <?= $step === 1 ? 'fw-bold text-primary' : 'text-muted' ?>">Persyaratan</div>
        </div>
        <div class="text-center">
            <div class="step-pill mx-auto <?= $step >= 2 ? 'bg-primary text-white' : 'bg-light text-muted' ?>">2</div>
            <div class="small mt-1 <?= $step === 2 ? 'fw-bold text-primary' : 'text-muted' ?>">Database</div>
        </div>
        <div class="text-center">
            <div class="step-pill mx-auto <?= $step >= 3 ? 'bg-primary text-white' : 'bg-light text-muted' ?>">3</div>
            <div class="small mt-1 <?= $step === 3 ? 'fw-bold text-primary' : 'text-muted' ?>">Migrasi</div>
        </div>
        <div class="text-center">
            <div class="step-pill mx-auto <?= $step >= 4 ? 'bg-primary text-white' : 'bg-light text-muted' ?>">4</div>
            <div class="small mt-1 <?= $step === 4 ? 'fw-bold text-primary' : 'text-muted' ?>">Admin</div>
        </div>
        <div class="text-center">
            <div class="step-pill mx-auto <?= $step >= 5 ? 'bg-success text-white' : 'bg-light text-muted' ?>">5</div>
            <div class="small mt-1 <?= $step === 5 ? 'fw-bold text-success' : 'text-muted' ?>">Selesai</div>
        </div>
    </div>

    <?php if (!empty($errorMessage)): ?>
        <div class="alert alert-danger alert-dismissible fade show" role="alert">
            <i class="bi bi-exclamation-triangle me-2"></i><?= htmlspecialchars($errorMessage, ENT_QUOTES, 'UTF-8') ?>
        </div>
    <?php endif; ?>

    <!-- STEP 1: PERSYARATAN SISTEM -->
    <?php if ($step === 1): ?>
        <?php
            $phpOk = version_compare(PHP_VERSION, '8.1.0', '>=');
            $pdoOk = extension_loaded('pdo');
            $pdoMysqlOk = extension_loaded('pdo_mysql');
            $curlOk = extension_loaded('curl');
            $mbstringOk = extension_loaded('mbstring');
            $gdOk = extension_loaded('gd');
            $allRequirementsOk = $phpOk && $pdoOk && $pdoMysqlOk && $curlOk && $mbstringOk && $gdOk;
        ?>
        <h5 class="fw-bold mb-3">Langkah 1: Pemeriksaan Ekstensi & Lingkungan Server</h5>
        <div class="table-responsive mb-4">
            <table class="table table-bordered">
                <thead class="table-light">
                    <tr><th>Komponen / Ekstensi</th><th style="width: 140px;" class="text-center">Status</th></tr>
                </thead>
                <tbody>
                    <tr><td>Versi PHP (Minimal 8.1.0) — Terpasang: <?= PHP_VERSION ?></td><td class="text-center"><?= $phpOk ? '<span class="badge bg-success">Memenuhi</span>' : '<span class="badge bg-danger">Kurang</span>' ?></td></tr>
                    <tr><td>PDO Extension</td><td class="text-center"><?= $pdoOk ? '<span class="badge bg-success">Aktif</span>' : '<span class="badge bg-danger">Tidak Aktif</span>' ?></td></tr>
                    <tr><td>PDO MySQL Driver</td><td class="text-center"><?= $pdoMysqlOk ? '<span class="badge bg-success">Aktif</span>' : '<span class="badge bg-danger">Tidak Aktif</span>' ?></td></tr>
                    <tr><td>cURL Extension</td><td class="text-center"><?= $curlOk ? '<span class="badge bg-success">Aktif</span>' : '<span class="badge bg-danger">Tidak Aktif</span>' ?></td></tr>
                    <tr><td>Mbstring Extension</td><td class="text-center"><?= $mbstringOk ? '<span class="badge bg-success">Aktif</span>' : '<span class="badge bg-danger">Tidak Aktif</span>' ?></td></tr>
                    <tr><td>GD Extension (Manipulasi Gambar/Foto)</td><td class="text-center"><?= $gdOk ? '<span class="badge bg-success">Aktif</span>' : '<span class="badge bg-danger">Tidak Aktif</span>' ?></td></tr>
                </tbody>
            </table>
        </div>
        <div class="text-end">
            <?php if ($allRequirementsOk): ?>
                <a href="?step=2" class="btn btn-primary px-4">Lanjutkan ke Konfigurasi Database <i class="bi bi-arrow-right ms-1"></i></a>
            <?php else: ?>
                <div class="alert alert-warning text-center">Harap lengkapi ekstensi PHP yang belum aktif sebelum melanjutkan.</div>
            <?php endif; ?>
        </div>

    <!-- STEP 2: KONFIGURASI DATABASE -->
    <?php elseif ($step === 2): ?>
        <h5 class="fw-bold mb-3">Langkah 2: Konfigurasi Basis Data MySQL / MariaDB</h5>
        <form method="POST">
            <input type="hidden" name="action" value="save_db">
            <div class="mb-3">
                <label class="form-label fw-bold">Host Database</label>
                <input type="text" name="db_host" class="form-control" value="127.0.0.1" required>
            </div>
            <div class="row g-2 mb-3">
                <div class="col-md-6">
                    <label class="form-label fw-bold">Port Database</label>
                    <input type="number" name="db_port" class="form-control" value="3306" required>
                </div>
                <div class="col-md-6">
                    <label class="form-label fw-bold">Nama Database</label>
                    <input type="text" name="db_name" class="form-control" value="garudacbt" required>
                </div>
            </div>
            <div class="row g-2 mb-4">
                <div class="col-md-6">
                    <label class="form-label fw-bold">Username Database</label>
                    <input type="text" name="db_user" class="form-control" value="root" required>
                </div>
                <div class="col-md-6">
                    <label class="form-label fw-bold">Password Database</label>
                    <input type="password" name="db_pass" class="form-control" placeholder="Kosongkan jika tanpa password">
                </div>
            </div>
            <div class="d-flex justify-content-between">
                <a href="?step=1" class="btn btn-outline-secondary">Kembali</a>
                <button type="submit" class="btn btn-primary px-4">Uji Koneksi & Simpan <i class="bi bi-arrow-right ms-1"></i></button>
            </div>
        </form>

    <!-- STEP 3: MIGRASI SKEMA 42 TABEL & SEEDER -->
    <?php elseif ($step === 3): ?>
        <h5 class="fw-bold mb-3">Langkah 3: Migrasi Skema Basis Data</h5>
        <p class="text-muted small">
            Sistem siap mengimpor 42 tabel relational, primary key, composite indexes, dan seeder data awal (Tahun Pelajaran, Semester, Jenis Ujian CBT, dan Pengaturan Sekolah).
        </p>
        <div class="p-3 bg-light rounded border mb-4">
            <div class="d-flex align-items-center mb-2">
                <i class="bi bi-table text-primary fs-4 me-2"></i>
                <div><strong>42 Tabel Standar DADO CBT</strong> (Master, Elearning, CBT, E-Rapor)</div>
            </div>
            <div class="d-flex align-items-center">
                <i class="bi bi-check2-circle text-success fs-4 me-2"></i>
                <div><strong>Initial Seeds</strong> (Admin, 7 Jenis Ujian AKM, Semester Ganjil & Genap)</div>
            </div>
        </div>
        <form method="POST">
            <input type="hidden" name="action" value="run_migration">
            <div class="d-flex justify-content-between">
                <a href="?step=2" class="btn btn-outline-secondary">Kembali</a>
                <button type="submit" class="btn btn-primary px-4"><i class="bi bi-play-circle me-1"></i>Jalankan Migrasi Database</button>
            </div>
        </form>

    <!-- STEP 4: SETUP ADMIN & SEKOLAH -->
    <?php elseif ($step === 4): ?>
        <?php
            $currentSekolah = $_POST['nama_sekolah'] ?? 'SMP NEGERI SATU ATAP 2 CIPATUJAH';
            $currentJenjang = (int)($_POST['jenjang'] ?? 2);
            $currentAdminUser = $_POST['admin_username'] ?? 'admin';
        ?>
        <h5 class="fw-bold mb-3">Langkah 4: Akun Administrator & Profil Satuan Pendidikan</h5>
        <form method="POST">
            <input type="hidden" name="action" value="save_admin">
            <div class="mb-3">
                <label class="form-label fw-bold">Nama Satuan Pendidikan / Lembaga</label>
                <input type="text" name="nama_sekolah" class="form-control" value="<?= htmlspecialchars($currentSekolah, ENT_QUOTES, 'UTF-8') ?>" required>
            </div>
            <div class="mb-3">
                <label class="form-label fw-bold">Jenjang Pendidikan</label>
                <select name="jenjang" class="form-select" required>
                    <option value="1" <?= $currentJenjang === 1 ? 'selected' : '' ?>>SD / MI</option>
                    <option value="2" <?= $currentJenjang === 2 ? 'selected' : '' ?>>SMP / MTs</option>
                    <option value="3" <?= $currentJenjang === 3 ? 'selected' : '' ?>>SMA / SMK / MA</option>
                </select>
                <small class="text-muted">Fitur 'Jurusan' otomatis disembunyikan jika memilih SD atau SMP.</small>
            </div>
            <div class="row g-2 mb-4">
                <div class="col-md-6">
                    <label class="form-label fw-bold">Username Administrator</label>
                    <input type="text" name="admin_username" class="form-control" value="<?= htmlspecialchars($currentAdminUser, ENT_QUOTES, 'UTF-8') ?>" required>
                </div>
                <div class="col-md-6">
                    <label class="form-label fw-bold">Password Administrator</label>
                    <input type="password" name="admin_password" class="form-control" placeholder="Masukkan password admin baru" value="admin123" required>
                </div>
            </div>
            <div class="d-flex justify-content-between">
                <a href="?step=3" class="btn btn-outline-secondary">Kembali</a>
                <button type="submit" class="btn btn-primary px-4"><i class="bi bi-check-circle me-1"></i>Simpan & Selesaikan Instalasi</button>
            </div>
        </form>

    <!-- STEP 5: SELESAI -->
    <?php elseif ($step === 5): ?>
        <div class="text-center py-4">
            <div class="text-success mb-3"><i class="bi bi-check-circle-fill" style="font-size: 4rem;"></i></div>
            <h4 class="fw-bold text-success">Instalasi DADO CBT Berhasil!</h4>
            <p class="text-muted small mb-4">
                Seluruh 42 tabel database berhasil diinisialisasi dan file proteksi <code>installed.lock</code> telah dibuat. <br>
                Anda sekarang dapat masuk menggunakan akun Administrator yang telah dibuat.
            </p>
            <a href="/login" class="btn btn-primary px-5 py-2 fw-bold"><i class="bi bi-box-arrow-in-right me-1"></i>Buka Halaman Login</a>
        </div>
    <?php endif; ?>
</div>

</body>
</html>
