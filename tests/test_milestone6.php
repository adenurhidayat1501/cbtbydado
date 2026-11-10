<?php

/**
 * GARUDACBT v1.5.3 REBUILD — TEST SUITE FOR MILESTONE 6
 * Modul E-Rapor, DKN, Buku Induk, & Utilitas Database / Web Installer
 */

define('BASE_PATH', dirname(__DIR__));
define('APP_PATH', BASE_PATH . '/app');

require_once APP_PATH . '/Helpers/functions.php';

// PSR-4 Autoloader
spl_autoload_register(function ($class) {
    $prefix = 'App\\';
    $len = strlen($prefix);
    if (strncmp($prefix, $class, $len) !== 0) {
        return;
    }
    $relativeClass = substr($class, $len);
    $file = APP_PATH . '/' . str_replace('\\', '/', $relativeClass) . '.php';
    if (file_exists($file)) {
        require_once $file;
    }
});

use App\Models\MasterTp;
use App\Models\MasterSmt;
use App\Models\MasterKelas;
use App\Models\MasterSiswa;
use App\Models\MasterMapel;
use App\Models\MasterEkstra;
use App\Models\KelasSiswa;
use App\Models\RaporAdminSetting;
use App\Models\RaporKkm;
use App\Models\RaporKikd;
use App\Models\RaporNilaiHarian;
use App\Models\RaporNilaiPts;
use App\Models\RaporNilaiAkhir;
use App\Models\RaporNilaiSikap;
use App\Models\RaporNilaiEkstra;
use App\Models\RaporCatatanWali;
use App\Models\RaporFisik;
use App\Models\RaporPrestasi;
use App\Models\RaporNaik;
use App\Models\BukuInduk;
use App\Services\RaporCalculatorService;
use App\Services\DatabaseUtilityService;

echo "========================================================\n";
echo "   GARUDACBT v1.5.3 REBUILD — MILESTONE 6 TEST SUITE    \n";
echo "========================================================\n\n";

$testsPassed = 0;
$testsFailed = 0;

function assertTest(bool $condition, string $message): void {
    global $testsPassed, $testsFailed;
    if ($condition) {
        echo "  [PASS] {$message}\n";
        $testsPassed++;
    } else {
        echo "  [FAIL] {$message}\n";
        $testsFailed++;
    }
}

// ----------------------------------------------------
// Setup Fixtures for Milestone 6
// ----------------------------------------------------
echo "--- 1. Setting Up Test Fixtures ---\n";

$tpModel = new MasterTp();
$activeTp = $tpModel->getActive();
$idTp = (int)($activeTp['id_tp'] ?? 1);

$smtModel = new MasterSmt();
$activeSmt = $smtModel->getActive();
$idSmt = (int)($activeSmt['id_smt'] ?? 1);

$kelasModel = new MasterKelas();
$kelasList = $kelasModel->all('id_kelas ASC', 1);
$idKelas = !empty($kelasList) ? (int)$kelasList[0]['id_kelas'] : 1;

$siswaModel = new MasterSiswa();
$siswaList = $siswaModel->all('id_siswa ASC', 1);
$idSiswa = !empty($siswaList) ? (int)$siswaList[0]['id_siswa'] : 1;

$mapelModel = new MasterMapel();
$mapelList = $mapelModel->all('id_mapel ASC', 1);
$idMapel = !empty($mapelList) ? (int)$mapelList[0]['id_mapel'] : 1;

$ekstraModel = new MasterEkstra();
$ekstraList = $ekstraModel->all('id_ekstra ASC', 1);
$idEkstra = !empty($ekstraList) ? (int)$ekstraList[0]['id_ekstra'] : 1;

// Ensure student is in class
$kelasSiswaModel = new KelasSiswa();
$kelasSiswaModel->addStudentToClass($idTp, $idSmt, $idKelas, $idSiswa);

assertTest($idTp > 0 && $idSmt > 0, "Active TP (ID: {$idTp}) and SMT (ID: {$idSmt}) verified");
assertTest($idKelas > 0 && $idSiswa > 0 && $idMapel > 0, "Master Class, Student, and Subject verified");

// ----------------------------------------------------
// 2. Testing Setting Rapor, KKM, & KIKD
// ----------------------------------------------------
echo "\n--- 2. Testing Setting Rapor, KKM, & KIKD ---\n";

$settingModel = new RaporAdminSetting();
$saveSetting = $settingModel->saveSetting($idTp, $idSmt, [
    'kkm_tunggal' => 1,
    'kkm' => 75,
    'bobot_ph' => 50,
    'bobot_pts' => 25,
    'bobot_pas' => 25,
    'nip_kepsek' => 1,
    'nip_walikelas' => 1
]);
assertTest($saveSetting, "Save Rapor Admin Setting (KKM Tunggal = 75, Bobot 50:25:25)");

$settingRow = $settingModel->getSetting($idTp, $idSmt);
assertTest((int)$settingRow['kkm'] === 75 && (int)$settingRow['bobot_ph'] === 50, "Retrieve Rapor Admin Setting matches saved values");

$kkmModel = new RaporKkm();
$saveKkm = $kkmModel->saveKkm([
    'id_tp' => $idTp,
    'id_smt' => $idSmt,
    'id_kelas' => $idKelas,
    'id_mapel' => $idMapel,
    'kkm' => 78,
    'bobot_ph' => 50,
    'bobot_pts' => 25,
    'bobot_pas' => 25,
    'beban_jam' => 3
]);
assertTest($saveKkm, "Save Rapor KKM per Mapel (KKM: 78, Beban Jam: 3)");

$kkmRow = $kkmModel->getByClassAndMapel($idTp, $idSmt, $idKelas, $idMapel);
assertTest($kkmRow && (int)$kkmRow['kkm'] === 78, "Retrieve Rapor KKM per Mapel matches 78");

$kikdModel = new RaporKikd();
$saveKikd = $kikdModel->saveKikd([
    'id_tp' => $idTp,
    'id_smt' => $idSmt,
    'id_mapel_kelas' => $idMapel,
    'aspek' => 1,
    'nomor_kikd' => '3.1',
    'isi_kikd' => 'Memahami konsep dasar teknologi jaringan komputer'
]);
assertTest($saveKikd, "Save KIKD KI-3 (Nomor 3.1)");

$kikdRows = $kikdModel->getByMapel($idTp, $idSmt, $idMapel, 1);
assertTest(count($kikdRows) > 0 && $kikdRows[0]['nomor_kikd'] === '3.1', "Retrieve KIKD by Mapel verified");

// ----------------------------------------------------
// 3. Testing Kumpulan Nilai Rapor Models
// ----------------------------------------------------
echo "\n--- 3. Testing Kumpulan Nilai Rapor Models ---\n";

// Nilai Harian (KI-3 Pengetahuan & KI-4 Keterampilan)
$harianModel = new RaporNilaiHarian();
$saveHarian = $harianModel->saveEntry([
    'id_tp' => $idTp,
    'id_smt' => $idSmt,
    'id_kelas' => $idKelas,
    'id_mapel' => $idMapel,
    'id_siswa' => $idSiswa,
    'p1' => 80.0,
    'p2' => 85.0,
    'p3' => 90.0,
    'p_predikat' => 'B',
    'k1' => 85.0,
    'k2' => 88.0,
    'k_predikat' => 'B'
]);
assertTest($saveHarian, "Save Rapor Nilai Harian (P1-P3, K1-K2)");

$harianRow = $harianModel->getSingle($idTp, $idSmt, $idKelas, $idMapel, $idSiswa);
assertTest($harianRow && (float)$harianRow['p_rata_rata'] === 85.00, "Nilai Harian P average = 85.00 (Expected: 85.00)");

// Nilai PTS
$ptsModel = new RaporNilaiPts();
$savePts = $ptsModel->saveEntry([
    'id_tp' => $idTp,
    'id_smt' => $idSmt,
    'id_kelas' => $idKelas,
    'id_mapel' => $idMapel,
    'id_siswa' => $idSiswa,
    'nilai' => 82.50,
    'predikat' => 'B'
]);
assertTest($savePts, "Save Rapor Nilai PTS (82.50, Predikat B)");

// Nilai Akhir PAS & NA
$akhirModel = new RaporNilaiAkhir();
$saveAkhir = $akhirModel->saveEntry([
    'id_tp' => $idTp,
    'id_smt' => $idSmt,
    'id_kelas' => $idKelas,
    'id_mapel' => $idMapel,
    'id_siswa' => $idSiswa,
    'nilai' => 88.00,
    'akhir' => 85.13,
    'predikat' => 'B'
]);
assertTest($saveAkhir, "Save Rapor Nilai Akhir (PAS: 88.00, NA: 85.13, Predikat B)");

// Nilai Sikap
$sikapModel = new RaporNilaiSikap();
$saveSikap = $sikapModel->saveEntry([
    'id_tp' => $idTp,
    'id_smt' => $idSmt,
    'id_kelas' => $idKelas,
    'id_siswa' => $idSiswa,
    'jenis' => 1,
    'nilai' => ['predikat' => 'A'],
    'deskripsi' => 'Selalu rajin berdoa dan taat beribadah.'
]);
assertTest($saveSikap, "Save Nilai Sikap Spiritual");

// Nilai Ekstrakurikuler
$ekstraNilaiModel = new RaporNilaiEkstra();
$saveEkstra = $ekstraNilaiModel->saveEntry([
    'id_tp' => $idTp,
    'id_smt' => $idSmt,
    'id_ekstra' => $idEkstra,
    'id_kelas' => $idKelas,
    'id_siswa' => $idSiswa,
    'nilai' => 'A',
    'predikat' => 'Sangat Baik',
    'deskripsi' => 'Aktif dalam kepramukaan.'
]);
assertTest($saveEkstra, "Save Nilai Ekstrakurikuler");

// Catatan Wali & Presensi
$catatanModel = new RaporCatatanWali();
$saveCatatan = $catatanModel->saveEntry([
    'id_tp' => $idTp,
    'id_smt' => $idSmt,
    'id_kelas' => $idKelas,
    'id_siswa' => $idSiswa,
    'sakit' => 1,
    'izin' => 2,
    'alfa' => 0,
    'deskripsi' => 'Pertahankan prestasi belajar Anda.'
]);
assertTest($saveCatatan, "Save Catatan Wali Kelas & Presensi (S:1, I:2, A:0)");

// Fisik & Prestasi
$fisikModel = new RaporFisik();
$saveFisik = $fisikModel->saveEntry([
    'id_tp' => $idTp,
    'id_smt' => $idSmt,
    'id_kelas' => $idKelas,
    'id_siswa' => $idSiswa,
    'tinggi' => 165,
    'berat' => 55,
    'pendengaran' => 'Baik',
    'penglihatan' => 'Baik',
    'gigi' => 'Baik'
]);
assertTest($saveFisik, "Save Kondisi Fisik & Kesehatan Siswa");

$prestasiModel = new RaporPrestasi();
$savePrestasi = $prestasiModel->saveEntry([
    'id_tp' => $idTp,
    'id_smt' => $idSmt,
    'id_kelas' => $idKelas,
    'id_siswa' => $idSiswa,
    'ranking' => 1,
    'p1' => 'Juara 1 LKS Web Technologies',
    'p1_desk' => 'Tingkat Kabupaten'
]);
assertTest($savePrestasi, "Save Prestasi Siswa");

// Kenaikan Kelas / Kelulusan
$naikModel = new RaporNaik();
$saveNaik = $naikModel->saveEntry([
    'id_tp' => $idTp,
    'id_smt' => $idSmt,
    'id_siswa' => $idSiswa,
    'naik' => 1,
    'keterangan' => 'Naik ke kelas XI'
]);
assertTest($saveNaik, "Save Keputusan Kenaikan Kelas (Naik = 1)");

// ----------------------------------------------------
// 4. Testing Buku Induk & Alumni
// ----------------------------------------------------
echo "\n--- 4. Testing Buku Induk & Alumni ---\n";

$bukuModel = new BukuInduk();
$buku = $bukuModel->getOrCreate($idSiswa);
assertTest(!empty($buku['id_buku']), "Get or create Buku Induk record (ID: {$buku['id_buku']})");

$saveBuku = $bukuModel->saveDetails($idSiswa, [
    'nama_panggilan' => 'Ahmad',
    'bahasa' => 'Indonesia / Jawa',
    'status_yatim' => 'Lengkap',
    'gol_darah' => 'O',
    'tamat_dari' => 'SMP Negeri 1',
    'status' => 1
]);
assertTest($saveBuku, "Save Buku Induk biodata details");

// Archive semester grade snapshot
$archiveGrade = $bukuModel->archiveSemesterGrade($idSiswa, $idTp, $idSmt, [
    'tahun_pelajaran' => '2024/2025',
    'semester' => 'Ganjil',
    'rata_rata' => 85.50,
    'ranking' => 1
]);
assertTest($archiveGrade, "Archive semester grade snapshot to Buku Induk (arsip_rapor_json)");

$bukuAfterArchive = $bukuModel->getBySiswa($idSiswa);
$decodedArsip = json_decode($bukuAfterArchive['arsip_rapor_json'], true);
assertTest(isset($decodedArsip["tp_{$idTp}_smt_{$idSmt}"]), "Arsip JSON contains active semester snapshot");

// ----------------------------------------------------
// 5. Testing RaporCalculatorService & DatabaseUtilityService
// ----------------------------------------------------
echo "\n--- 5. Testing Rapor & Database Services ---\n";

$calcService = new RaporCalculatorService();

// Test Predikat
// KKM = 75, Interval = (100 - 75) / 3 = 8.33. A >= 91.67, B >= 83.33, C >= 75.0, D < 75.0
$predA = $calcService->calculatePredicate(95.0, 75.0);
$predB = $calcService->calculatePredicate(85.0, 75.0);
$predC = $calcService->calculatePredicate(76.0, 75.0);
$predD = $calcService->calculatePredicate(60.0, 75.0);
assertTest($predA === 'A' && $predB === 'B' && $predC === 'C' && $predD === 'D', "Calculate Predicates with KKM 75: A, B, C, D match interval formula");

// Test Nilai Akhir
// Bobot 50:25:25, PH=80, PTS=85, PAS=90 -> (50*80 + 25*85 + 25*90) / 100 = (4000 + 2125 + 2250) / 100 = 83.75
$naTest = $calcService->calculateFinalScore(80.0, 85.0, 90.0, ['bobot_ph' => 50, 'bobot_pts' => 25, 'bobot_pas' => 25]);
assertTest($naTest === 83.75, "Calculate Final Score NA (Expected: 83.75, Actual: {$naTest})");

// Test DKN Class Recap
$dknRecap = $calcService->calculateClassRecap($idTp, $idSmt, $idKelas);
assertTest(!empty($dknRecap['mapel_list']), "DKN Class Recap returns subject list");
assertTest(!empty($dknRecap['students_data']), "DKN Class Recap returns student data with rankings");

// Test DatabaseUtilityService
$dbUtil = new DatabaseUtilityService();
$sqlDump = $dbUtil->generateBackupSql();
assertTest(!empty($sqlDump) && str_contains($sqlDump, 'SET FOREIGN_KEY_CHECKS = 0;'), "Generate Database Backup SQL contains foreign key controls and DDL");

$schemaCheck = $dbUtil->updateDatabaseSchema();
assertTest($schemaCheck['total_existing'] >= 42, "Database Schema Verifier confirms at least 42 tables present (Found: {$schemaCheck['total_existing']})");

// ----------------------------------------------------
// 6. Testing HTTP Routes via cURL
// ----------------------------------------------------
echo "\n--- 6. Testing HTTP Routes via cURL ---\n";

function curlReq(string $url, string $method = 'GET', array $data = [], ?string $cookieFile = null): array {
    $ch = curl_init();
    curl_setopt($ch, CURLOPT_URL, $url);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_FOLLOWLOCATION, false);
    curl_setopt($ch, CURLOPT_CUSTOMREQUEST, $method);
    if (!empty($data)) {
        curl_setopt($ch, CURLOPT_POSTFIELDS, http_build_query($data));
    }
    if ($cookieFile) {
        curl_setopt($ch, CURLOPT_COOKIEJAR, $cookieFile);
        curl_setopt($ch, CURLOPT_COOKIEFILE, $cookieFile);
    }
    $body = curl_exec($ch);
    $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    curl_close($ch);
    return ['code' => $httpCode, 'body' => $body];
}

$adminCookie = BASE_PATH . '/tests/admin_cookie_m6.txt';

// Admin Login
$loginRes = curlReq('http://127.0.0.1:8080/login', 'POST', [
    'username' => 'admin',
    'password' => 'admin123'
], $adminCookie);
assertTest($loginRes['code'] === 302 || $loginRes['code'] === 200, "Admin login HTTP {$loginRes['code']}");

// Milestone 6 Admin Endpoints
$routes = [
    '/rapor/setting' => 'Pengaturan Rapor & KKM',
    '/rapor/kkm?id_kelas=' . $idKelas => 'KKM per Mapel',
    '/rapor/kikd?id_mapel=' . $idMapel => 'Capaian KI/KD',
    '/rapor/input-nilai' => 'Hub Kumpulan Nilai Rapor',
    '/rapor/input-nilai/mapel?id_kelas=' . $idKelas . '&id_mapel=' . $idMapel => 'Input Nilai Mapel',
    '/rapor/input-nilai/sikap?id_kelas=' . $idKelas => 'Input Nilai Sikap',
    '/rapor/input-nilai/ekstra?id_kelas=' . $idKelas => 'Input Nilai Ekstra',
    '/rapor/input-nilai/catatan?id_kelas=' . $idKelas => 'Catatan Wali & Presensi',
    '/rapor/input-nilai/fisik?id_kelas=' . $idKelas => 'Input Kondisi Fisik',
    '/rapor/input-nilai/prestasi?id_kelas=' . $idKelas => 'Input Prestasi',
    '/rapor/input-nilai/naik?id_kelas=' . $idKelas => 'Input Kenaikan Kelas',
    '/rapor/cetak' => 'Cetak Rapor & DKN Index',
    '/rapor/cetak/pts/' . $idKelas . '/' . $idSiswa => 'Cetak Rapor PTS',
    '/rapor/cetak/pas/' . $idKelas . '/' . $idSiswa => 'Cetak Rapor Akhir PAS/PAT',
    '/rapor/cetak/dkn/' . $idKelas => 'Cetak DKN Kelas',
    '/rapor/buku-induk' => 'Buku Induk Index',
    '/rapor/buku-induk/detail/' . $idSiswa => 'Buku Induk Detail Siswa',
    '/rapor/buku-induk/cetak/' . $idSiswa => 'Cetak Lembar Buku Induk',
    '/settings/database' => 'Utilitas & Pemeliharaan Database',
    '/installer/index.php?force=1' => 'Web Installer Wizard'
];

foreach ($routes as $route => $label) {
    $res = curlReq('http://127.0.0.1:8080' . $route, 'GET', [], $adminCookie);
    assertTest($res['code'] === 200, "HTTP: {$label} ({$route}) -> HTTP {$res['code']}");
}

// Cleanup cookies
@unlink($adminCookie);

echo "\n========================================================\n";
echo "   TEST RESULTS: {$testsPassed} PASSED, {$testsFailed} FAILED\n";
echo "========================================================\n";

if ($testsFailed > 0) {
    exit(1);
}
