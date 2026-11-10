<?php

declare(strict_types=1);

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

use App\Config\Jenjang;
use App\Core\Database;
use App\Models\CbtToken;
use App\Models\JabatanGuru;
use App\Models\KelasSiswa;
use App\Models\MasterEkstra;
use App\Models\MasterGuru;
use App\Models\MasterJurusan;
use App\Models\MasterKelas;
use App\Models\MasterMapel;
use App\Models\MasterSiswa;
use App\Models\MasterSmt;
use App\Models\MasterTp;
use App\Models\RunningText;
use App\Models\SettingSekolah;
use App\Models\User;

echo "=== MEMULAI TEST MILESTONE 2 ===\n";

$db = Database::getInstance()->getPdo();

// 1. TEST ITEM 2.1: Beranda & Token CBT
$tokenModel = new CbtToken();
$newToken = $tokenModel->generateNewToken(6);
if (strlen($newToken) !== 6) {
    die("[FAIL] 2.1. Panjang token CBT tidak 6 karakter: {$newToken}\n");
}
$activeTok = $tokenModel->getActiveToken();
if ($activeTok['token'] !== $newToken) {
    die("[FAIL] 2.1. Token di database tidak sinkron dengan token yang digenerate.\n");
}
echo "[OK] 2.1. Generator Token CBT aktif: {$newToken}\n";

// 2. TEST ITEM 2.2: TP, Semester, Jurusan, Mapel, Ekstra
$tpModel = new MasterTp();
$smtModel = new MasterSmt();
$activeTp = $tpModel->getActive();
$activeSmt = $smtModel->getActive();
if (!$activeTp || !$activeSmt) {
    die("[FAIL] 2.2.1. TP atau SMT aktif tidak terbaca.\n");
}
echo "[OK] 2.2.1. TP & SMT Aktif: {$activeTp['tahun']} Smt {$activeSmt['smt']}\n";

// Test multi-jenjang rules for Jurusan
if (Jenjang::hasJurusan(Jenjang::SD) !== false || Jenjang::hasJurusan(Jenjang::SMP) !== false) {
    die("[FAIL] 2.2.2. Jurusan tidak boleh aktif pada SD atau SMP!\n");
}
if (Jenjang::hasJurusan(Jenjang::SMA) !== true) {
    die("[FAIL] 2.2.2. Jurusan wajib aktif pada SMA/SMK!\n");
}
echo "[OK] 2.2.2. Multi-Jenjang Jurusan Rule (SD/SMP: Hidden, SMA: Visible) valid.\n";

// Test Mapel by Kelompok
$mapelModel = new MasterMapel();
$mapelA = $mapelModel->getByKelompok('A');
$mapelPai = $mapelModel->getByKelompok('PAI');
if (empty($mapelA) || empty($mapelPai)) {
    die("[FAIL] 2.2.3. Data Mapel Kelompok A atau PAI kosong.\n");
}
echo "[OK] 2.2.3. Pengelompokan Mapel Umum & PAI valid (Total Mapel A: " . count($mapelA) . ", PAI: " . count($mapelPai) . ")\n";

// Test Ekstrakurikuler
$ekstraModel = new MasterEkstra();
$ekstraList = $ekstraModel->all();
if (empty($ekstraList)) {
    die("[FAIL] 2.2.4. Data Ekstrakurikuler kosong.\n");
}
echo "[OK] 2.2.4. Data Ekstrakurikuler tersedia (" . count($ekstraList) . " kegiatan)\n";

// 3. TEST ITEM 2.3: Data Siswa & Auto User Creation
$userModel = new User();
$siswaModel = new MasterSiswa();
$testNis = '2026TEST' . random_int(100, 999);
$defaultPassword = password_hash('siswa123', PASSWORD_BCRYPT);

$userId = $userModel->insert([
    'username' => $testNis,
    'password' => $defaultPassword,
    'email' => null,
    'role' => 'siswa',
    'active' => 1,
]);

$siswaId = $siswaModel->insert([
    'id_user' => $userId,
    'nis' => $testNis,
    'nisn' => '0098765432',
    'nama' => 'Siswa Uji Coba Milestone 2',
    'jk' => 'L',
    'tempat_lahir' => 'Bandung',
    'tgl_lahir' => '2011-01-01',
    'agama' => 'Islam',
    'alamat' => 'Jl. Uji Coba No. 1',
    'nama_ayah' => 'Ayah Uji',
    'nama_ibu' => 'Ibu Uji',
    'no_hp' => '0812345678',
    'tahun_masuk' => '2026',
    'status' => 1,
]);

$fetchedStudent = $siswaModel->findByNis($testNis);
if (!$fetchedStudent || (int)$fetchedStudent['id_user'] !== (int)$userId) {
    die("[FAIL] 2.3. Data siswa baru tidak tersambung dengan akun users.\n");
}
echo "[OK] 2.3. Siswa baru berhasil disimpan dengan relasi akun login (NIS: {$testNis})\n";

// 4. TEST ITEM 2.4: Kelas / Rombel & Anggota
$kelasModel = new MasterKelas();
$kelasSiswaModel = new KelasSiswa();

$testKelasKode = 'TEST-' . random_int(10, 99);
$kelasId = $kelasModel->insert([
    'id_tp' => $activeTp['id_tp'],
    'id_smt' => $activeSmt['id_smt'],
    'nama_kelas' => 'Kelas Uji ' . $testKelasKode,
    'kode_kelas' => $testKelasKode,
    'level_id' => 7,
    'jurusan_id' => null,
    'guru_id' => 1,
    'siswa_id' => json_encode([]),
]);

// Tambah siswa ke rombel
$kelasSiswaModel->addStudentToClass(
    (int)$activeTp['id_tp'],
    (int)$activeSmt['id_smt'],
    (int)$kelasId,
    (int)$siswaId
);

$inClass = $kelasSiswaModel->getStudentsInClass((int)$kelasId, (int)$activeTp['id_tp'], (int)$activeSmt['id_smt']);
if (count($inClass) !== 1 || $inClass[0]['nis'] !== $testNis) {
    die("[FAIL] 2.4. Siswa gagal dimasukkan ke rombel kelas.\n");
}
echo "[OK] 2.4.1. Siswa berhasil dimasukkan ke rombel kelas {$testKelasKode}\n";

// Hapus siswa dari rombel
$kelasSiswaModel->removeStudentFromClass(
    (int)$activeTp['id_tp'],
    (int)$activeSmt['id_smt'],
    (int)$kelasId,
    (int)$siswaId
);
$inClassAfter = $kelasSiswaModel->getStudentsInClass((int)$kelasId, (int)$activeTp['id_tp'], (int)$activeSmt['id_smt']);
if (!empty($inClassAfter)) {
    die("[FAIL] 2.4. Siswa gagal dikeluarkan dari rombel kelas.\n");
}
echo "[OK] 2.4.2. Fitur hapus siswa dari rombel sukses.\n";

// Cleanup test kelas & test siswa
$kelasModel->delete($kelasId);
$siswaModel->delete($siswaId);
$userModel->delete($userId);

// 5. TEST ITEM 2.5: Data Guru & Jabatan
$guruModel = new MasterGuru();
$jabatanModel = new JabatanGuru();
$guruList = $guruModel->all();
if (empty($guruList)) {
    die("[FAIL] 2.5. Data Guru kosong.\n");
}
$firstGuru = $guruList[0];
$jabatanModel->setJabatan(
    (int)$firstGuru['id_guru'],
    (int)$activeTp['id_tp'],
    (int)$activeSmt['id_smt'],
    2, // Wali Kelas
    1  // id_kelas 1
);
$jabatanCheck = $jabatanModel->first([
    'id_guru' => $firstGuru['id_guru'],
    'id_tp' => $activeTp['id_tp'],
    'id_smt' => $activeSmt['id_smt'],
]);
if (!$jabatanCheck || (int)$jabatanCheck['id_jabatan'] !== 2) {
    die("[FAIL] 2.5. Penetapan jabatan guru gagal disimpan.\n");
}
echo "[OK] 2.5. Data Guru & Penetapan Jabatan Wali Kelas tersimpan valid.\n";

// 6. TEST ITEM 2.6: Setting Sekolah, Users & Pengumuman
$sekolahModel = new SettingSekolah();
$school = $sekolahModel->getActive();
if (empty($school['nama_sekolah'])) {
    die("[FAIL] 2.6. Profil Sekolah kosong.\n");
}
echo "[OK] 2.6.1. Profil Sekolah terbaca: {$school['nama_sekolah']}\n";

$textModel = new RunningText();
$textList = $textModel->getActiveList('semua');
if (empty($textList)) {
    die("[FAIL] 2.6.2. Running Text aktif kosong.\n");
}
echo "[OK] 2.6.2. Pengumuman Running Text aktif terbaca: {$textList[0]['text']}\n";

echo "\n=== SELURUH TES MILESTONE 2 (ITEM 2.1 - 2.6) BERHASIL 100%! ===\n";

