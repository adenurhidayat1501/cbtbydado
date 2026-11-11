<?php

/**
 * ==============================================================================
 * DADO CBT v1.5.3 (FIXED) — FRONT CONTROLLER & BOOTSTRAPPER
 * ==============================================================================
 */

declare(strict_types=1);

// Set default timezone
date_default_timezone_set('Asia/Jakarta');

// Define base paths
define('ROOT_PATH', dirname(__DIR__));
define('APP_PATH', ROOT_PATH . '/app');
define('PUBLIC_PATH', __DIR__);

// 1. Simple Environment (.env) Loader
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
            $val = trim($parts[1]);
            // Strip surrounding quotes
            if (str_starts_with($val, '"') && str_ends_with($val, '"')) {
                $val = substr($val, 1, -1);
            } elseif (str_starts_with($val, "'") && str_ends_with($val, "'")) {
                $val = substr($val, 1, -1);
            }
            $_ENV[$key] = $val;
            putenv("{$key}={$val}");
        }
    }
}

// 2. PSR-4 Autoloader for "App\"
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

// 3. Load Global Helper Functions
require APP_PATH . '/Helpers/functions.php';

// 4. Initialize Session
\App\Core\Session::start();

// 5. Create Request & Response
$request = new \App\Core\Request();
$response = new \App\Core\Response();

// 6. Router Definition
$router = new \App\Core\Router($request, $response);

// Middlewares
$authMiddleware = \App\Middlewares\AuthMiddleware::class;
$guestMiddleware = \App\Middlewares\GuestMiddleware::class;
$adminGuard = new \App\Middlewares\RoleMiddleware(['admin']);
$guruGuard = new \App\Middlewares\RoleMiddleware(['guru', 'admin']);
$siswaGuard = new \App\Middlewares\RoleMiddleware(['siswa']);

// --- GUEST ROUTES ---
$router->get('/', function ($req, $res) {
    if (\App\Core\Auth::check()) {
        $role = \App\Core\Auth::role();
        $target = \App\Config\Auth::getRoleRedirect($role ?: 'siswa');
        $res->redirect($target);
    } else {
        $res->redirect('/login');
    }
});

// Login Reguler (Admin & Guru)
$router->get('/login', [\App\Controllers\Auth\LoginController::class, 'showLogin'], [$guestMiddleware]);
$router->post('/login', [\App\Controllers\Auth\LoginController::class, 'login']);

// Login CBT Khusus Siswa
$router->get('/cbt/login', [\App\Controllers\Auth\CbtLoginController::class, 'showLogin'], [$guestMiddleware]);
$router->post('/cbt/login', [\App\Controllers\Auth\CbtLoginController::class, 'login']);

// --- AUTHENTICATED GENERAL ROUTES ---
$router->get('/logout', [\App\Controllers\Auth\LoginController::class, 'logout']);
$router->post('/user/change-password', [\App\Controllers\Auth\PasswordController::class, 'changePassword'], [$authMiddleware]);
$router->post('/settings/tp-smt/switch', [\App\Controllers\Settings\TpSmtSwitchController::class, 'switch'], [$authMiddleware]);

// --- DASHBOARD ROUTES PER ROLE ---
// Admin Dashboard & Token Release
$router->get('/dashboard', [\App\Controllers\DashboardController::class, 'index'], [$authMiddleware, $adminGuard]);
$router->post('/cbt/token/generate', [\App\Controllers\DashboardController::class, 'generateToken'], [$authMiddleware, $adminGuard]);

// Guru Dashboard
$router->get('/guru/dashboard', [\App\Controllers\DashboardController::class, 'guruDashboard'], [$authMiddleware, $guruGuard]);

// Siswa Dashboard
$router->get('/student/dashboard', [\App\Controllers\DashboardController::class, 'studentDashboard'], [$authMiddleware, $siswaGuard]);

// --- MILESTONE 2: DATA MASTER ROUTES (ADMIN) ---

// 1. Tahun Pelajaran & Semester
$router->get('/master/tp-smt', [\App\Controllers\Master\TpSmtController::class, 'index'], [$authMiddleware, $adminGuard]);
$router->post('/master/tp/store', [\App\Controllers\Master\TpSmtController::class, 'storeTp'], [$authMiddleware, $adminGuard]);
$router->get('/master/tp/activate/{id}', [\App\Controllers\Master\TpSmtController::class, 'activateTp'], [$authMiddleware, $adminGuard]);
$router->get('/master/tp/delete/{id}', [\App\Controllers\Master\TpSmtController::class, 'deleteTp'], [$authMiddleware, $adminGuard]);
$router->get('/master/smt/activate/{id}', [\App\Controllers\Master\TpSmtController::class, 'activateSmt'], [$authMiddleware, $adminGuard]);

// 2. Jurusan (SMA/SMK)
$router->get('/master/jurusan', [\App\Controllers\Master\JurusanController::class, 'index'], [$authMiddleware, $adminGuard]);
$router->post('/master/jurusan/store', [\App\Controllers\Master\JurusanController::class, 'store'], [$authMiddleware, $adminGuard]);
$router->post('/master/jurusan/update/{id}', [\App\Controllers\Master\JurusanController::class, 'update'], [$authMiddleware, $adminGuard]);
$router->get('/master/jurusan/delete/{id}', [\App\Controllers\Master\JurusanController::class, 'delete'], [$authMiddleware, $adminGuard]);

// 3. Mata Pelajaran
$router->get('/master/mapel', [\App\Controllers\Master\MapelController::class, 'index'], [$authMiddleware, $adminGuard]);
$router->post('/master/mapel/store', [\App\Controllers\Master\MapelController::class, 'store'], [$authMiddleware, $adminGuard]);
$router->post('/master/mapel/update/{id}', [\App\Controllers\Master\MapelController::class, 'update'], [$authMiddleware, $adminGuard]);
$router->get('/master/mapel/delete/{id}', [\App\Controllers\Master\MapelController::class, 'delete'], [$authMiddleware, $adminGuard]);

// 4. Ekstrakurikuler
$router->get('/master/ekstra', [\App\Controllers\Master\EkstraController::class, 'index'], [$authMiddleware, $adminGuard]);
$router->post('/master/ekstra/store', [\App\Controllers\Master\EkstraController::class, 'store'], [$authMiddleware, $adminGuard]);
$router->post('/master/ekstra/update/{id}', [\App\Controllers\Master\EkstraController::class, 'update'], [$authMiddleware, $adminGuard]);
$router->get('/master/ekstra/delete/{id}', [\App\Controllers\Master\EkstraController::class, 'delete'], [$authMiddleware, $adminGuard]);

// 5. Data Siswa
$router->get('/master/siswa', [\App\Controllers\Master\SiswaController::class, 'index'], [$authMiddleware, $adminGuard]);
$router->post('/master/siswa/store', [\App\Controllers\Master\SiswaController::class, 'store'], [$authMiddleware, $adminGuard]);
$router->post('/master/siswa/update/{id}', [\App\Controllers\Master\SiswaController::class, 'update'], [$authMiddleware, $adminGuard]);
$router->get('/master/siswa/delete/{id}', [\App\Controllers\Master\SiswaController::class, 'delete'], [$authMiddleware, $adminGuard]);
$router->get('/master/siswa/template', [\App\Controllers\Master\SiswaController::class, 'downloadTemplate'], [$authMiddleware, $adminGuard]);
$router->post('/master/siswa/import', [\App\Controllers\Master\SiswaController::class, 'import'], [$authMiddleware, $adminGuard]);
$router->get('/master/siswa/foto-per-kelas', [\App\Controllers\Master\SiswaController::class, 'fotoPerKelas'], [$authMiddleware, $adminGuard]);
$router->post('/master/siswa/upload-foto', [\App\Controllers\Master\SiswaController::class, 'uploadFoto'], [$authMiddleware, $adminGuard]);

// 6. Kelas & Rombel
$router->get('/master/kelas', [\App\Controllers\Master\KelasController::class, 'index'], [$authMiddleware, $adminGuard]);
$router->post('/master/kelas/store', [\App\Controllers\Master\KelasController::class, 'store'], [$authMiddleware, $adminGuard]);
$router->post('/master/kelas/update/{id}', [\App\Controllers\Master\KelasController::class, 'update'], [$authMiddleware, $adminGuard]);
$router->get('/master/kelas/delete/{id}', [\App\Controllers\Master\KelasController::class, 'delete'], [$authMiddleware, $adminGuard]);
$router->get('/master/kelas/anggota/{id}', [\App\Controllers\Master\KelasController::class, 'anggota'], [$authMiddleware, $adminGuard]);
$router->post('/master/kelas/add-student', [\App\Controllers\Master\KelasController::class, 'addStudent'], [$authMiddleware, $adminGuard]);
$router->post('/master/kelas/remove-student', [\App\Controllers\Master\KelasController::class, 'removeStudent'], [$authMiddleware, $adminGuard]);
$router->post('/master/kelas/salin', [\App\Controllers\Master\KelasController::class, 'salinRombel'], [$authMiddleware, $adminGuard]);

// 7. Data Guru
$router->get('/master/guru', [\App\Controllers\Master\GuruController::class, 'index'], [$authMiddleware, $adminGuard]);
$router->post('/master/guru/store', [\App\Controllers\Master\GuruController::class, 'store'], [$authMiddleware, $adminGuard]);
$router->post('/master/guru/update/{id}', [\App\Controllers\Master\GuruController::class, 'update'], [$authMiddleware, $adminGuard]);
$router->get('/master/guru/delete/{id}', [\App\Controllers\Master\GuruController::class, 'delete'], [$authMiddleware, $adminGuard]);
$router->get('/master/guru/template', [\App\Controllers\Master\GuruController::class, 'downloadTemplate'], [$authMiddleware, $adminGuard]);
$router->post('/master/guru/import', [\App\Controllers\Master\GuruController::class, 'import'], [$authMiddleware, $adminGuard]);
$router->get('/master/guru/struktur', [\App\Controllers\Master\GuruController::class, 'strukturOrganisasi'], [$authMiddleware, $adminGuard]);

// 8. Pengaturan Sistem: Profil Sekolah, Manajemen Pengguna, Running Text
$router->get('/settings/profile', [\App\Controllers\Settings\ProfileController::class, 'index'], [$authMiddleware, $adminGuard]);
$router->post('/settings/profile/update', [\App\Controllers\Settings\ProfileController::class, 'update'], [$authMiddleware, $adminGuard]);

$router->get('/settings/users', [\App\Controllers\Settings\UserController::class, 'index'], [$authMiddleware, $adminGuard]);
$router->post('/settings/users/store-admin', [\App\Controllers\Settings\UserController::class, 'storeAdmin'], [$authMiddleware, $adminGuard]);
$router->get('/settings/users/reset/{id}', [\App\Controllers\Settings\UserController::class, 'resetPassword'], [$authMiddleware, $adminGuard]);
$router->get('/settings/users/toggle/{id}', [\App\Controllers\Settings\UserController::class, 'toggleStatus'], [$authMiddleware, $adminGuard]);
$router->get('/settings/users/delete/{id}', [\App\Controllers\Settings\UserController::class, 'delete'], [$authMiddleware, $adminGuard]);

$router->get('/settings/announcements', [\App\Controllers\Settings\AnnouncementController::class, 'index'], [$authMiddleware, $adminGuard]);
$router->post('/settings/announcements/store', [\App\Controllers\Settings\AnnouncementController::class, 'store'], [$authMiddleware, $adminGuard]);
$router->post('/settings/announcements/update/{id}', [\App\Controllers\Settings\AnnouncementController::class, 'update'], [$authMiddleware, $adminGuard]);
$router->get('/settings/announcements/toggle/{id}', [\App\Controllers\Settings\AnnouncementController::class, 'toggle'], [$authMiddleware, $adminGuard]);
$router->get('/settings/announcements/delete/{id}', [\App\Controllers\Settings\AnnouncementController::class, 'delete'], [$authMiddleware, $adminGuard]);

// --- MILESTONE 3: E-LEARNING & PRESENSI KBM (GURU & ADMIN) ---

// 1. Jadwal Pelajaran KBM
$router->get('/elearning/jadwal-kbm', [\App\Controllers\Elearning\JadwalKbmController::class, 'index'], [$authMiddleware, $guruGuard]);
$router->post('/elearning/jadwal-kbm/settings', [\App\Controllers\Elearning\JadwalKbmController::class, 'saveSettings'], [$authMiddleware, $guruGuard]);
$router->post('/elearning/jadwal-kbm/cell', [\App\Controllers\Elearning\JadwalKbmController::class, 'saveCell'], [$authMiddleware, $guruGuard]);
$router->get('/guru/jadwal', [\App\Controllers\Elearning\JadwalKbmController::class, 'guruJadwal'], [$authMiddleware, $guruGuard]);

// 2. Materi & Tugas Belajar
$router->get('/elearning/materi-tugas', [\App\Controllers\Elearning\MateriController::class, 'index'], [$authMiddleware, $guruGuard]);
$router->get('/guru/materi', [\App\Controllers\Elearning\MateriController::class, 'index'], [$authMiddleware, $guruGuard]);
$router->get('/elearning/materi-tugas/create', [\App\Controllers\Elearning\MateriController::class, 'create'], [$authMiddleware, $guruGuard]);
$router->post('/elearning/materi-tugas/store', [\App\Controllers\Elearning\MateriController::class, 'store'], [$authMiddleware, $guruGuard]);
$router->get('/elearning/materi-tugas/edit/{id}', [\App\Controllers\Elearning\MateriController::class, 'edit'], [$authMiddleware, $guruGuard]);
$router->post('/elearning/materi-tugas/update/{id}', [\App\Controllers\Elearning\MateriController::class, 'update'], [$authMiddleware, $guruGuard]);
$router->get('/elearning/materi-tugas/delete/{id}', [\App\Controllers\Elearning\MateriController::class, 'delete'], [$authMiddleware, $guruGuard]);
$router->get('/elearning/materi-tugas/detail/{id}', [\App\Controllers\Elearning\MateriController::class, 'detail'], [$authMiddleware, $guruGuard]);
$router->post('/elearning/materi-tugas/komentar/{id}', [\App\Controllers\Elearning\MateriController::class, 'postKomentar'], [$authMiddleware, $guruGuard]);

// 3. Penjadwalan Materi / Tugas ke Kelas
$router->get('/elearning/jadwal-materi', [\App\Controllers\Elearning\JadwalMateriController::class, 'index'], [$authMiddleware, $guruGuard]);
$router->post('/elearning/jadwal-materi/store', [\App\Controllers\Elearning\JadwalMateriController::class, 'store'], [$authMiddleware, $guruGuard]);
$router->get('/elearning/jadwal-materi/delete/{id}', [\App\Controllers\Elearning\JadwalMateriController::class, 'delete'], [$authMiddleware, $guruGuard]);

// 4. Presensi KBM & Penilaian E-Learning
$router->get('/elearning/presensi-nilai', [\App\Controllers\Elearning\PresensiNilaiController::class, 'index'], [$authMiddleware, $guruGuard]);
$router->get('/elearning/presensi-nilai/submissions/{id}', [\App\Controllers\Elearning\PresensiNilaiController::class, 'tugasSubmissions'], [$authMiddleware, $guruGuard]);
$router->post('/elearning/presensi-nilai/grade', [\App\Controllers\Elearning\PresensiNilaiController::class, 'gradeSubmission'], [$authMiddleware, $guruGuard]);
$router->get('/elearning/presensi-nilai/harian', [\App\Controllers\Elearning\PresensiNilaiController::class, 'rekapHarian'], [$authMiddleware, $guruGuard]);
$router->get('/elearning/presensi-nilai/bulanan', [\App\Controllers\Elearning\PresensiNilaiController::class, 'rekapBulanan'], [$authMiddleware, $guruGuard]);
$router->get('/elearning/presensi-nilai/export-nilai', [\App\Controllers\Elearning\PresensiNilaiController::class, 'exportNilai'], [$authMiddleware, $guruGuard]);
$router->get('/elearning/presensi-nilai/export-harian', [\App\Controllers\Elearning\PresensiNilaiController::class, 'exportKehadiranHarian'], [$authMiddleware, $guruGuard]);
$router->get('/elearning/presensi-nilai/export-bulanan', [\App\Controllers\Elearning\PresensiNilaiController::class, 'exportKehadiranBulanan'], [$authMiddleware, $guruGuard]);

// 5. Ruang Belajar Siswa (Student E-Learning)
$router->get('/student/elearning', [\App\Controllers\Student\ElearningSiswaController::class, 'index'], [$authMiddleware, $siswaGuard]);
$router->get('/student/elearning/baca/{id}', [\App\Controllers\Student\ElearningSiswaController::class, 'bacaMateri'], [$authMiddleware, $siswaGuard]);
$router->get('/student/elearning/tugas/{id}', [\App\Controllers\Student\ElearningSiswaController::class, 'detailTugas'], [$authMiddleware, $siswaGuard]);
$router->post('/student/elearning/kumpul/{id}', [\App\Controllers\Student\ElearningSiswaController::class, 'kumpulTugas'], [$authMiddleware, $siswaGuard]);
$router->post('/student/elearning/komentar/{id}', [\App\Controllers\Student\ElearningSiswaController::class, 'postKomentar'], [$authMiddleware, $siswaGuard]);

// ==========================================
// MILESTONE 4: CBT ENGINE & ASSESSMENTS
// ==========================================

// 1. Master CBT (Jenis, Ruang, Sesi, Ruang-Sesi Siswa, Nomor Peserta)
$router->get('/cbt/master', [\App\Controllers\Cbt\MasterCbtController::class, 'index'], [$authMiddleware, $adminGuard]);
$router->post('/cbt/master/jenis/store', [\App\Controllers\Cbt\MasterCbtController::class, 'storeJenis'], [$authMiddleware, $adminGuard]);
$router->get('/cbt/master/jenis/delete/{id}', [\App\Controllers\Cbt\MasterCbtController::class, 'deleteJenis'], [$authMiddleware, $adminGuard]);
$router->post('/cbt/master/ruang/store', [\App\Controllers\Cbt\MasterCbtController::class, 'storeRuang'], [$authMiddleware, $adminGuard]);
$router->get('/cbt/master/ruang/delete/{id}', [\App\Controllers\Cbt\MasterCbtController::class, 'deleteRuang'], [$authMiddleware, $adminGuard]);
$router->post('/cbt/master/sesi/store', [\App\Controllers\Cbt\MasterCbtController::class, 'storeSesi'], [$authMiddleware, $adminGuard]);
$router->get('/cbt/master/sesi/delete/{id}', [\App\Controllers\Cbt\MasterCbtController::class, 'deleteSesi'], [$authMiddleware, $adminGuard]);
$router->get('/cbt/master/ruang-sesi-siswa', [\App\Controllers\Cbt\MasterCbtController::class, 'sesiSiswa'], [$authMiddleware, $adminGuard]);
$router->post('/cbt/master/ruang-sesi-siswa/save', [\App\Controllers\Cbt\MasterCbtController::class, 'saveSesiSiswa'], [$authMiddleware, $adminGuard]);
$router->get('/cbt/master/nomor-peserta', [\App\Controllers\Cbt\MasterCbtController::class, 'nomorPeserta'], [$authMiddleware, $adminGuard]);
$router->post('/cbt/master/nomor-peserta/generate', [\App\Controllers\Cbt\MasterCbtController::class, 'generateNomorPeserta'], [$authMiddleware, $adminGuard]);
$router->post('/cbt/master/nomor-peserta/save', [\App\Controllers\Cbt\MasterCbtController::class, 'saveNomorPeserta'], [$authMiddleware, $adminGuard]);

// 2. Bank Soal & Editor Butir Soal 5 Tipe AKM
$router->get('/cbt/bank-soal', [\App\Controllers\Cbt\BankSoalController::class, 'index'], [$authMiddleware, $guruGuard]);
$router->get('/guru/bank-soal', [\App\Controllers\Cbt\BankSoalController::class, 'index'], [$authMiddleware, $guruGuard]);
$router->get('/cbt/bank-soal/create', [\App\Controllers\Cbt\BankSoalController::class, 'create'], [$authMiddleware, $guruGuard]);
$router->post('/cbt/bank-soal/store', [\App\Controllers\Cbt\BankSoalController::class, 'store'], [$authMiddleware, $guruGuard]);
$router->get('/cbt/bank-soal/edit/{id}', [\App\Controllers\Cbt\BankSoalController::class, 'edit'], [$authMiddleware, $guruGuard]);
$router->post('/cbt/bank-soal/update/{id}', [\App\Controllers\Cbt\BankSoalController::class, 'update'], [$authMiddleware, $guruGuard]);
$router->get('/cbt/bank-soal/delete/{id}', [\App\Controllers\Cbt\BankSoalController::class, 'delete'], [$authMiddleware, $guruGuard]);
$router->get('/cbt/bank-soal/detail/{id}', [\App\Controllers\Cbt\BankSoalController::class, 'detail'], [$authMiddleware, $guruGuard]);
$router->get('/cbt/bank-soal/soal/{id}', [\App\Controllers\Cbt\BankSoalController::class, 'editorSoal'], [$authMiddleware, $guruGuard]);
$router->post('/cbt/bank-soal/soal/{id}/store', [\App\Controllers\Cbt\BankSoalController::class, 'storeSoal'], [$authMiddleware, $guruGuard]);
$router->get('/cbt/bank-soal/soal/{id}/delete/{id_soal}', [\App\Controllers\Cbt\BankSoalController::class, 'deleteSoal'], [$authMiddleware, $guruGuard]);
$router->post('/cbt/bank-soal/upload-image', [\App\Controllers\Cbt\BankSoalController::class, 'uploadImage'], [$authMiddleware, $guruGuard]);
$router->post('/cbt/bank-soal/import-word/{id}', [\App\Controllers\Cbt\BankSoalController::class, 'importWord'], [$authMiddleware, $guruGuard]);
$router->post('/cbt/bank-soal/import-excel/{id}', [\App\Controllers\Cbt\BankSoalController::class, 'importExcel'], [$authMiddleware, $guruGuard]);
$router->get('/cbt/bank-soal/template-excel', [\App\Controllers\Cbt\BankSoalController::class, 'downloadTemplateExcel'], [$authMiddleware, $guruGuard]);
$router->get('/cbt/bank-soal/template-word', [\App\Controllers\Cbt\BankSoalController::class, 'downloadTemplateWord'], [$authMiddleware, $guruGuard]);

// 3. Jadwal Ujian & Alokasi Waktu
$router->get('/cbt/jadwal', [\App\Controllers\Cbt\JadwalController::class, 'index'], [$authMiddleware, $guruGuard]);
$router->get('/cbt/jadwal/create', [\App\Controllers\Cbt\JadwalController::class, 'create'], [$authMiddleware, $adminGuard]);
$router->post('/cbt/jadwal/store', [\App\Controllers\Cbt\JadwalController::class, 'store'], [$authMiddleware, $adminGuard]);
$router->get('/cbt/jadwal/edit/{id}', [\App\Controllers\Cbt\JadwalController::class, 'edit'], [$authMiddleware, $adminGuard]);
$router->post('/cbt/jadwal/update/{id}', [\App\Controllers\Cbt\JadwalController::class, 'update'], [$authMiddleware, $adminGuard]);
$router->get('/cbt/jadwal/delete/{id}', [\App\Controllers\Cbt\JadwalController::class, 'delete'], [$authMiddleware, $adminGuard]);

// 4. Pengawas Ujian & Monitoring Proktor
$router->get('/cbt/jadwal/pengawas/{id}', [\App\Controllers\Cbt\JadwalController::class, 'pengawas'], [$authMiddleware, $adminGuard]);
$router->post('/cbt/jadwal/pengawas/{id}/save', [\App\Controllers\Cbt\JadwalController::class, 'savePengawas'], [$authMiddleware, $adminGuard]);
$router->get('/guru/pengawas', [\App\Controllers\Cbt\JadwalController::class, 'guruPengawas'], [$authMiddleware, $guruGuard]);

// 5. Generator Token Ujian CBT
$router->get('/cbt/token', [\App\Controllers\Cbt\TokenController::class, 'index'], [$authMiddleware, $guruGuard]);
$router->any('/cbt/token/generate', [\App\Controllers\Cbt\TokenController::class, 'generate'], [$authMiddleware, $guruGuard]);
$router->post('/cbt/token/setting', [\App\Controllers\Cbt\TokenController::class, 'updateSetting'], [$authMiddleware, $adminGuard]);

// ==========================================
// MILESTONE 5: EXAM ENGINE, MONITORING & REKAP
// ==========================================

// 1. Antarmuka Ujian Siswa (Exam Engine)
$router->get('/student/cbt', [\App\Controllers\Student\ExamEngineController::class, 'index'], [$authMiddleware, $siswaGuard]);
$router->get('/student/cbt/konfirmasi/{id}', [\App\Controllers\Student\ExamEngineController::class, 'konfirmasi'], [$authMiddleware, $siswaGuard]);
$router->post('/student/cbt/start/{id}', [\App\Controllers\Student\ExamEngineController::class, 'start'], [$authMiddleware, $siswaGuard]);
$router->get('/student/cbt/ujian/{id}', [\App\Controllers\Student\ExamEngineController::class, 'ujian'], [$authMiddleware, $siswaGuard]);
$router->post('/student/cbt/save-answer', [\App\Controllers\Student\ExamEngineController::class, 'saveAnswer'], [$authMiddleware, $siswaGuard]);
$router->post('/student/cbt/log-violation', [\App\Controllers\Student\ExamEngineController::class, 'logViolation'], [$authMiddleware, $siswaGuard]);
$router->any('/student/cbt/finish/{id}', [\App\Controllers\Student\ExamEngineController::class, 'finish'], [$authMiddleware, $siswaGuard]);
$router->get('/student/cbt/selesai/{id}', [\App\Controllers\Student\ExamEngineController::class, 'selesai'], [$authMiddleware, $siswaGuard]);

// 2. Cetak Administrasi CBT (Kartu, Absensi, Berita Acara)
$router->get('/cbt/cetak', [\App\Controllers\Cbt\CetakController::class, 'index'], [$authMiddleware, $guruGuard]);
$router->post('/cbt/cetak/save-kop', [\App\Controllers\Cbt\CetakController::class, 'saveKop'], [$authMiddleware, $adminGuard]);
$router->get('/cbt/cetak/kartu-peserta', [\App\Controllers\Cbt\CetakController::class, 'kartuPeserta'], [$authMiddleware, $guruGuard]);
$router->get('/cbt/cetak/daftar-hadir', [\App\Controllers\Cbt\CetakController::class, 'daftarHadir'], [$authMiddleware, $guruGuard]);
$router->get('/cbt/cetak/berita-acara', [\App\Controllers\Cbt\CetakController::class, 'beritaAcara'], [$authMiddleware, $guruGuard]);

// 3. Live Status Siswa / Monitoring
$router->get('/cbt/status', [\App\Controllers\Cbt\StatusSiswaController::class, 'index'], [$authMiddleware, $guruGuard]);
$router->post('/cbt/status/reset-login', [\App\Controllers\Cbt\StatusSiswaController::class, 'resetLogin'], [$authMiddleware, $guruGuard]);
$router->post('/cbt/status/reset-waktu', [\App\Controllers\Cbt\StatusSiswaController::class, 'resetWaktu'], [$authMiddleware, $guruGuard]);
$router->post('/cbt/status/paksa-selesai', [\App\Controllers\Cbt\StatusSiswaController::class, 'paksaSelesai'], [$authMiddleware, $guruGuard]);
$router->post('/cbt/status/ulang-ujian', [\App\Controllers\Cbt\StatusSiswaController::class, 'ulangUjian'], [$authMiddleware, $guruGuard]);

// 4. Koreksi, Analisis Soal & Rekap Nilai Ujian
$router->get('/cbt/koreksi', [\App\Controllers\Cbt\KoreksiRekapController::class, 'koreksiIndex'], [$authMiddleware, $guruGuard]);
$router->get('/cbt/koreksi/peserta/{id}', [\App\Controllers\Cbt\KoreksiRekapController::class, 'koreksiPeserta'], [$authMiddleware, $guruGuard]);
$router->get('/cbt/koreksi/detail/{id}/{id_siswa}', [\App\Controllers\Cbt\KoreksiRekapController::class, 'koreksiDetail'], [$authMiddleware, $guruGuard]);
$router->post('/cbt/koreksi/save/{id}/{id_siswa}', [\App\Controllers\Cbt\KoreksiRekapController::class, 'saveKoreksi'], [$authMiddleware, $guruGuard]);
$router->get('/cbt/analisis/{id}', [\App\Controllers\Cbt\KoreksiRekapController::class, 'analisisSoal'], [$authMiddleware, $guruGuard]);
$router->get('/cbt/rekap-nilai', [\App\Controllers\Cbt\KoreksiRekapController::class, 'rekapNilai'], [$authMiddleware, $guruGuard]);
$router->post('/cbt/rekap-nilai/katrol/{id}', [\App\Controllers\Cbt\KoreksiRekapController::class, 'katrolNilai'], [$authMiddleware, $guruGuard]);
$router->get('/cbt/rekap-nilai/export/{id}', [\App\Controllers\Cbt\KoreksiRekapController::class, 'exportExcel'], [$authMiddleware, $guruGuard]);

// ==========================================
// MILESTONE 6: E-RAPOR, DKN, BUKU INDUK & UTILITAS
// ==========================================

// 1. Pengaturan Rapor & KKM
$router->get('/rapor/setting', [\App\Controllers\Rapor\RaporSettingController::class, 'index'], [$authMiddleware, $adminGuard]);
$router->post('/rapor/setting/save', [\App\Controllers\Rapor\RaporSettingController::class, 'save'], [$authMiddleware, $adminGuard]);
$router->get('/rapor/kkm', [\App\Controllers\Rapor\RaporSettingController::class, 'kkm'], [$authMiddleware, $guruGuard]);
$router->post('/rapor/kkm/save', [\App\Controllers\Rapor\RaporSettingController::class, 'kkmSave'], [$authMiddleware, $guruGuard]);
$router->get('/rapor/kikd', [\App\Controllers\Rapor\RaporSettingController::class, 'kikd'], [$authMiddleware, $guruGuard]);
$router->post('/rapor/kikd/save', [\App\Controllers\Rapor\RaporSettingController::class, 'kikdSave'], [$authMiddleware, $guruGuard]);
$router->get('/rapor/kikd/delete/{id}', [\App\Controllers\Rapor\RaporSettingController::class, 'kikdDelete'], [$authMiddleware, $guruGuard]);

// 2. Kumpulan Nilai Rapor
$router->get('/rapor/input-nilai', [\App\Controllers\Rapor\NilaiRaporController::class, 'index'], [$authMiddleware, $guruGuard]);
$router->get('/rapor/input-nilai/mapel', [\App\Controllers\Rapor\NilaiRaporController::class, 'mapel'], [$authMiddleware, $guruGuard]);
$router->post('/rapor/input-nilai/mapel/save', [\App\Controllers\Rapor\NilaiRaporController::class, 'mapelSave'], [$authMiddleware, $guruGuard]);
$router->post('/rapor/input-nilai/sync', [\App\Controllers\Rapor\NilaiRaporController::class, 'sync'], [$authMiddleware, $guruGuard]);
$router->post('/rapor/input-nilai/import-excel', [\App\Controllers\Rapor\NilaiRaporController::class, 'importExcel'], [$authMiddleware, $guruGuard]);
$router->get('/rapor/input-nilai/sikap', [\App\Controllers\Rapor\NilaiRaporController::class, 'sikap'], [$authMiddleware, $guruGuard]);
$router->post('/rapor/input-nilai/sikap/save', [\App\Controllers\Rapor\NilaiRaporController::class, 'sikapSave'], [$authMiddleware, $guruGuard]);
$router->get('/rapor/input-nilai/ekstra', [\App\Controllers\Rapor\NilaiRaporController::class, 'ekstra'], [$authMiddleware, $guruGuard]);
$router->post('/rapor/input-nilai/ekstra/save', [\App\Controllers\Rapor\NilaiRaporController::class, 'ekstraSave'], [$authMiddleware, $guruGuard]);
$router->get('/rapor/input-nilai/catatan', [\App\Controllers\Rapor\NilaiRaporController::class, 'catatan'], [$authMiddleware, $guruGuard]);
$router->post('/rapor/input-nilai/catatan/save', [\App\Controllers\Rapor\NilaiRaporController::class, 'catatanSave'], [$authMiddleware, $guruGuard]);
$router->get('/rapor/input-nilai/fisik', [\App\Controllers\Rapor\NilaiRaporController::class, 'fisik'], [$authMiddleware, $guruGuard]);
$router->post('/rapor/input-nilai/fisik/save', [\App\Controllers\Rapor\NilaiRaporController::class, 'fisikSave'], [$authMiddleware, $guruGuard]);
$router->get('/rapor/input-nilai/prestasi', [\App\Controllers\Rapor\NilaiRaporController::class, 'prestasi'], [$authMiddleware, $guruGuard]);
$router->post('/rapor/input-nilai/prestasi/save', [\App\Controllers\Rapor\NilaiRaporController::class, 'prestasiSave'], [$authMiddleware, $guruGuard]);
$router->get('/rapor/input-nilai/naik', [\App\Controllers\Rapor\NilaiRaporController::class, 'naik'], [$authMiddleware, $guruGuard]);
$router->post('/rapor/input-nilai/naik/save', [\App\Controllers\Rapor\NilaiRaporController::class, 'naikSave'], [$authMiddleware, $guruGuard]);

// 3. Cetak Rapor & DKN
$router->get('/rapor/cetak', [\App\Controllers\Rapor\CetakRaporController::class, 'index'], [$authMiddleware, $guruGuard]);
$router->get('/rapor/cetak/pts/{idKelas}/{idSiswa}', [\App\Controllers\Rapor\CetakRaporController::class, 'cetakPts'], [$authMiddleware, $guruGuard]);
$router->get('/rapor/cetak/pas/{idKelas}/{idSiswa}', [\App\Controllers\Rapor\CetakRaporController::class, 'cetakPas'], [$authMiddleware, $guruGuard]);
$router->get('/rapor/cetak/dkn/{idKelas}', [\App\Controllers\Rapor\CetakRaporController::class, 'cetakDkn'], [$authMiddleware, $guruGuard]);
$router->get('/rapor/cetak/dkn-excel/{idKelas}', [\App\Controllers\Rapor\CetakRaporController::class, 'exportDknExcel'], [$authMiddleware, $guruGuard]);

// 4. Buku Induk & Alumni
$router->get('/rapor/buku-induk', [\App\Controllers\Rapor\BukuIndukController::class, 'index'], [$authMiddleware, $guruGuard]);
$router->get('/rapor/buku-induk/detail/{id}', [\App\Controllers\Rapor\BukuIndukController::class, 'detail'], [$authMiddleware, $guruGuard]);
$router->post('/rapor/buku-induk/save/{id}', [\App\Controllers\Rapor\BukuIndukController::class, 'save'], [$authMiddleware, $guruGuard]);
$router->get('/rapor/buku-induk/cetak/{id}', [\App\Controllers\Rapor\BukuIndukController::class, 'cetak'], [$authMiddleware, $guruGuard]);
$router->get('/rapor/buku-induk/sync-rapor/{id}', [\App\Controllers\Rapor\BukuIndukController::class, 'syncRapor'], [$authMiddleware, $guruGuard]);
$router->post('/rapor/buku-induk/alumni-status', [\App\Controllers\Rapor\BukuIndukController::class, 'updateStatus'], [$authMiddleware, $adminGuard]);

// 5. Utilitas & Pemeliharaan Database
$router->get('/settings/database', [\App\Controllers\Settings\DatabaseController::class, 'index'], [$authMiddleware, $adminGuard]);
$router->get('/settings/database/backup', [\App\Controllers\Settings\DatabaseController::class, 'backup'], [$authMiddleware, $adminGuard]);
$router->post('/settings/database/restore', [\App\Controllers\Settings\DatabaseController::class, 'restore'], [$authMiddleware, $adminGuard]);
$router->post('/settings/database/update', [\App\Controllers\Settings\DatabaseController::class, 'update'], [$authMiddleware, $adminGuard]);
$router->post('/settings/app/update', [\App\Controllers\Settings\DatabaseController::class, 'appUpdate'], [$authMiddleware, $adminGuard]);

// 7. Dispatch Request
$router->dispatch();
