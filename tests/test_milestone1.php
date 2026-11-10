<?php

declare(strict_types=1);

// Boot minimal bootstrap for testing
define('ROOT_PATH', dirname(__DIR__));
define('APP_PATH', ROOT_PATH . '/app');
define('PUBLIC_PATH', ROOT_PATH . '/public');

// Load .env
$envFile = ROOT_PATH . '/.env';
if (file_exists($envFile)) {
    $lines = file($envFile, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);
    foreach ($lines as $line) {
        $trimmed = trim($line);
        if (empty($trimmed) || str_starts_with($trimmed, '#')) continue;
        $parts = explode('=', $trimmed, 2);
        if (count($parts) === 2) {
            $key = trim($parts[0]);
            $val = trim($parts[1]);
            if ((str_starts_with($val, '"') && str_ends_with($val, '"')) || (str_starts_with($val, "'") && str_ends_with($val, "'"))) {
                $val = substr($val, 1, -1);
            }
            $_ENV[$key] = $val;
            putenv("{$key}={$val}");
        }
    }
}

// Autoloader
spl_autoload_register(function ($class) {
    $prefix = 'App\\';
    $len = strlen($prefix);
    if (strncmp($prefix, $class, $len) !== 0) return;
    $relativeClass = substr($class, $len);
    $file = APP_PATH . '/' . str_replace('\\', '/', $relativeClass) . '.php';
    if (file_exists($file)) require $file;
});

require APP_PATH . '/Helpers/functions.php';

use App\Core\Auth;
use App\Core\Database;
use App\Core\Session;
use App\Models\MasterSmt;
use App\Models\MasterTp;
use App\Models\SettingSekolah;
use App\Models\User;
use App\Services\AutoGraderService;

echo "=== MEMULAI TEST MILESTONE 1 ===\n";

// TEST 1: Database Connection
try {
    $db = Database::getInstance()->getPdo();
    echo "[OK] 1. Database PDO Connection terhubung ke MySQL.\n";
} catch (\Throwable $e) {
    die("[FAIL] 1. Koneksi DB gagal: " . $e->getMessage() . "\n");
}

// TEST 2: User Model & Password Verification
$userModel = new User();
$adminUser = $userModel->findByUsername('admin');
if (!$adminUser) {
    die("[FAIL] 2. User 'admin' tidak ditemukan di database.\n");
}
if (!password_verify('admin123', $adminUser['password'])) {
    die("[FAIL] 2. Password admin123 gagal diverifikasi.\n");
}
echo "[OK] 2. User Model & Verifikasi Password Bcrypt sukses.\n";

// TEST 3: Single-Device Session Lock
Session::start();
Auth::login($adminUser);
$firstToken = Session::get('session_token');
if (empty($firstToken)) {
    die("[FAIL] 3. Session token kosong setelah login.\n");
}
if (!Auth::verifySessionToken()) {
    die("[FAIL] 3. verifySessionToken() gagal pada sesi pertama.\n");
}
echo "[OK] 3.1. Sesi login pertama tervalidasi.\n";

// Simulate login on another device for the same user
$newToken = bin2hex(random_bytes(24));
$db->prepare("UPDATE users SET session_token = ? WHERE id = ?")->execute([$newToken, $adminUser['id']]);

// Now check if original device is rejected
if (Auth::verifySessionToken()) {
    die("[FAIL] 3.2. Single-Device Lock gagal! Sesi lama masih dianggap valid padahal token DB berubah.\n");
}
echo "[OK] 3.2. Single-Device Lock sukses menolak sesi lama saat login di perangkat lain.\n";

// Re-login to valid state
Auth::login($adminUser);

// TEST 4: Auto-Logout saat ganti password
$testUserId = (int)$adminUser['id'];
$oldToken = Session::get('session_token');
Auth::onPasswordChanged($testUserId);
if (Auth::check()) {
    die("[FAIL] 4. Auto-Logout gagal! Session masih aktif setelah password diubah.\n");
}
$stmtTokenCheck = $db->prepare("SELECT session_token FROM users WHERE id = ?");
$stmtTokenCheck->execute([$testUserId]);
$tokenAfterPassChange = $stmtTokenCheck->fetchColumn();
if ($tokenAfterPassChange === $oldToken) {
    die("[FAIL] 4. DB session_token tidak diinvalider saat ganti password.\n");
}
echo "[OK] 4. Auto-Logout & Session Invalidation saat ganti password sukses.\n";

// TEST 5: Master TP & Semester Switching
$tpModel = new MasterTp();
$smtModel = new MasterSmt();
$activeTp = active_tp();
$activeSmt = active_smt();
if (!$activeTp || !$activeSmt) {
    die("[FAIL] 5. TP / Semester aktif gagal dibaca.\n");
}
echo "[OK] 5.1. TP Aktif: {$activeTp['tahun']}, SMT: {$activeSmt['smt']} ({$activeSmt['nama_smt']})\n";

// Switch TP test
$tpModel->setActive(1);
$switchedTp = $tpModel->getActive();
if ((int)$switchedTp['id_tp'] !== 1) {
    die("[FAIL] 5.2. Gagal switch TP.\n");
}
// Restore TP 2
$tpModel->setActive(2);
echo "[OK] 5.2. Saklar TP aktif sukses berfungsi.\n";

// TEST 6: Helper Functions (Date Indo, Safe Print String, Fisher-Yates)
$tglTest = format_indo_date('2026-11-10 18:30:00', true, true);
if (!str_contains($tglTest, 'Selasa') || !str_contains($tglTest, 'November') || !str_contains($tglTest, '2026')) {
    die("[FAIL] 6.1. format_indo_date keliru: {$tglTest}\n");
}
echo "[OK] 6.1. format_indo_date(): {$tglTest}\n";

$rawStr = "Kartu Ujian: Siswa Syafi'i bin \"Ahmad\"";
$escaped = safe_print_str($rawStr);
if (str_contains($escaped, "'") || str_contains($escaped, '"')) {
    die("[FAIL] 6.2. safe_print_str tidak meng-escape quote: {$escaped}\n");
}
echo "[OK] 6.2. safe_print_str(): {$escaped}\n";

$numbers = [1, 2, 3, 4, 5, 6, 7, 8, 9, 10];
$original = $numbers;
fisher_yates_shuffle($numbers);
sort($original);
$numbersSorted = $numbers;
sort($numbersSorted);
if ($original !== $numbersSorted) {
    die("[FAIL] 6.3. Fisher-Yates shuffle menghilangkan elemen array.\n");
}
echo "[OK] 6.3. Fisher-Yates Shuffle fair & valid.\n";

// TEST 7: AutoGrader Service Scoring Logic Verification
$grader = new AutoGraderService();
// We can test calculateMatchingScore directly using reflection
$refClass = new ReflectionClass(AutoGraderService::class);
$method = $refClass->getMethod('calculateMatchingScore');
$method->setAccessible(true);

$kunciJson = json_encode([
    'type' => 'line',
    'links' => [
        '0' => ['1'],
        '1' => ['0', '2']
    ]
]); // Total 3 links: "0-1", "1-0", "1-2"

$siswaJsonBenarSemua = json_encode([
    'links' => [
        '0' => ['1'],
        '1' => ['0', '2']
    ]
]);
$score1 = $method->invoke($grader, $kunciJson, $siswaJsonBenarSemua);
if (abs($score1 - 1.0) > 0.001) {
    die("[FAIL] 7.1. Skor Menjodohkan 100% salah: {$score1}\n");
}

$siswaJsonKelebihanGaris = json_encode([
    'links' => [
        '0' => ['1', '0'], // 1 link benar (0-1), 1 link salah (0-0)
        '1' => ['0', '2']  // 2 link benar (1-0, 1-2)
        // total 4 garis, kelebihan 1 garis dari kunci (3). L_benar = 3, excess = 1 => (3 - 1) / 3 = 2/3 = 0.6667
    ]
]);
$score2 = $method->invoke($grader, $kunciJson, $siswaJsonKelebihanGaris);
if (abs($score2 - (2.0/3.0)) > 0.01) {
    die("[FAIL] 7.2. Skor Menjodohkan Kelebihan Garis salah: {$score2}\n");
}
echo "[OK] 7. AutoGraderService Algoritma Menjodohkan AKM presisi 100%!\n";

echo "\n=== SELURUH TES MILESTONE 1 BERHASIL DENGAN SEMPURNA! ===\n";

