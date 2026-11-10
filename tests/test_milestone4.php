<?php

declare(strict_types=1);

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

use App\Models\CbtJenis;
use App\Models\CbtRuang;
use App\Models\CbtSesi;
use App\Models\CbtSesiSiswa;
use App\Models\CbtNomorPeserta;
use App\Models\CbtBankSoal;
use App\Models\CbtSoal;
use App\Models\CbtJadwal;
use App\Models\CbtPengawas;
use App\Models\CbtToken;
use App\Models\MasterTp;
use App\Models\MasterSmt;
use App\Models\MasterSiswa;
use App\Models\MasterGuru;
use App\Models\MasterMapel;
use App\Models\MasterKelas;
use App\Services\ExcelSoalImportService;

echo "========================================================\n";
echo "   GARUDACBT v1.5.3 REBUILD — MILESTONE 4 TEST SUITE    \n";
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
// 1. Master CBT (Jenis Ujian, Ruang, Sesi)
// ----------------------------------------------------
echo "--- 1. Testing Master CBT (Jenis, Ruang, Sesi) ---\n";

$jenisModel = new CbtJenis();
$idJenis = (int)$jenisModel->insert([
    'nama_jenis' => 'Penilaian Akhir Semester Test',
    'kode_jenis' => 'PASTEST'
]);
assertTest($idJenis > 0, "Insert CbtJenis (id: {$idJenis})");
$foundJenis = $jenisModel->find($idJenis);
assertTest($foundJenis && $foundJenis['kode_jenis'] === 'PASTEST', "Retrieve CbtJenis");

$ruangModel = new CbtRuang();
$idRuang = (int)$ruangModel->insert([
    'nama_ruang' => 'Laboratorium CBT 1',
    'kode_ruang' => 'LAB-1'
]);
assertTest($idRuang > 0, "Insert CbtRuang (id: {$idRuang})");
$foundRuang = $ruangModel->find($idRuang);
assertTest($foundRuang && $foundRuang['kode_ruang'] === 'LAB-1', "Retrieve CbtRuang");

$sesiModel = new CbtSesi();
$idSesi = (int)$sesiModel->insert([
    'nama_sesi' => 'Sesi Pagi 1',
    'kode_sesi' => 'S1',
    'waktu_mulai' => '07:30:00',
    'waktu_akhir' => '09:30:00'
]);
assertTest($idSesi > 0, "Insert CbtSesi (id: {$idSesi})");
$foundSesi = $sesiModel->find($idSesi);
assertTest($foundSesi && $foundSesi['kode_sesi'] === 'S1', "Retrieve CbtSesi");

// ----------------------------------------------------
// 2. Ruang-Sesi Siswa & Nomor Peserta Generator
// ----------------------------------------------------
echo "\n--- 2. Testing Ruang-Sesi Siswa & Nomor Peserta ---\n";

$tpModel = new MasterTp();
$activeTp = $tpModel->getActive();
$idTp = (int)($activeTp['id_tp'] ?? 1);

$smtModel = new MasterSmt();
$activeSmt = $smtModel->getActive();
$idSmt = (int)($activeSmt['id_smt'] ?? 1);

$siswaModel = new MasterSiswa();
$siswaList = $siswaModel->all('id_siswa ASC', 2);
if (count($siswaList) < 1) {
    // Create dummy siswa for testing
    $idSiswaTest = $siswaModel->insert([
        'nama' => 'Siswa CBT Test',
        'nis' => '999901',
        'nisn' => '00999901',
        'jenis_kelamin' => 'L',
        'username' => 'cbt_tester_1',
        'password' => password_hash('password123', PASSWORD_BCRYPT),
        'status' => 1
    ]);
    $idSiswa1 = $idSiswaTest;
} else {
    $idSiswa1 = (int)$siswaList[0]['id_siswa'];
}

$sesiSiswaModel = new CbtSesiSiswa();
$assignOk = $sesiSiswaModel->assignStudent($idTp, $idSmt, $idSiswa1, 1, $idRuang, $idSesi);
assertTest($assignOk, "Assign Siswa to Ruang and Sesi");

$savedSesiSiswa = $sesiSiswaModel->first(['siswa_id' => $idSiswa1, 'tp_id' => $idTp, 'smt_id' => $idSmt]);
assertTest($savedSesiSiswa && (int)$savedSesiSiswa['ruang_id'] === $idRuang, "Verify Siswa Ruang assignment");

// Nomor Peserta
$nomorModel = new CbtNomorPeserta();
$nomorPesertaStr = '26-03-001-0001';
$saveNomorOk = $nomorModel->saveNomor($idTp, $idSiswa1, $nomorPesertaStr);
assertTest($saveNomorOk, "Save Nomor Peserta for student: {$nomorPesertaStr}");

$retrieved = $nomorModel->first(['id_siswa' => $idSiswa1, 'id_tp' => $idTp]);
assertTest($retrieved && $retrieved['nomor_peserta'] === $nomorPesertaStr, "Retrieve saved Nomor Peserta");

$genCount = $nomorModel->generateNomor($idTp, $idSmt);
assertTest($genCount >= 1, "Auto-generate Nomor Peserta (generated {$genCount} numbers)");

// ----------------------------------------------------
// 3. Bank Soal (5 Tipe AKM Settings & Quota)
// ----------------------------------------------------
echo "\n--- 3. Testing Bank Soal & AKM Configuration ---\n";

$guruModel = new MasterGuru();
$guruList = $guruModel->all('id_guru ASC', 1);
$idGuru = !empty($guruList) ? (int)$guruList[0]['id_guru'] : 1;

$mapelModel = new MasterMapel();
$mapelList = $mapelModel->all('id_mapel ASC', 1);
$idMapel = !empty($mapelList) ? (int)$mapelList[0]['id_mapel'] : 1;

$bankModel = new CbtBankSoal();
$idBank = (int)$bankModel->insert([
    'id_tp' => $idTp,
    'id_smt' => $idSmt,
    'bank_jenis_id' => $idJenis,
    'bank_kode' => 'BANK-TEST-' . time(),
    'bank_level' => 7,
    'bank_kelas' => json_encode([1, 2]),
    'bank_mapel_id' => $idMapel,
    'bank_guru_id' => $idGuru,
    'opsi' => 4,
    'tampil_pg' => 10,
    'bobot_pg' => 40,
    'tampil_kompleks' => 5,
    'bobot_kompleks' => 20,
    'tampil_jodohkan' => 5,
    'bobot_jodohkan' => 20,
    'tampil_isian' => 5,
    'bobot_isian' => 10,
    'tampil_esai' => 2,
    'bobot_esai' => 10,
    'status' => 1,
    'status_soal' => 0
]);
assertTest($idBank > 0, "Insert CbtBankSoal (id: {$idBank})");
$foundBank = $bankModel->getDetail($idBank);
assertTest($foundBank && (int)$foundBank['opsi'] === 4 && (int)$foundBank['bobot_pg'] === 40, "Verify Bank Soal settings and AKM weights");

// ----------------------------------------------------
// 4. Editor Butir Soal 5 Tipe AKM
// ----------------------------------------------------
echo "\n--- 4. Testing Butir Soal 5 Tipe AKM ---\n";

$soalModel = new CbtSoal();

// Tipe 1: PG Tunggal
$idSoal1 = $soalModel->saveSoal($idBank, [
    'jenis' => 1,
    'nomor_soal' => 1,
    'soal' => '<p>Hasil dari $2^3 + \\sqrt{16}$ adalah...</p>',
    'opsi_a' => '<p>10</p>',
    'opsi_b' => '<p>12</p>',
    'opsi_c' => '<p>14</p>',
    'opsi_d' => '<p>16</p>',
    'opsi_e' => '',
    'jawaban' => 'B',
    'tampilkan' => 1
]);
assertTest($idSoal1 > 0, "Tipe 1 (PG Tunggal) saved with KaTeX equation");

// Tipe 2: PG Kompleks
$idSoal2 = $soalModel->saveSoal($idBank, [
    'jenis' => 2,
    'nomor_soal' => 2,
    'soal' => '<p>Pernyataan berikut yang benar mengenai bilangan prima adalah...</p>',
    'opsi_a' => '<p>2 adalah bilangan prima genap</p>',
    'opsi_b' => '<p>9 adalah bilangan prima</p>',
    'opsi_c' => '<p>13 adalah bilangan prima</p>',
    'opsi_d' => '<p>15 adalah bilangan prima</p>',
    'opsi_e' => '',
    'jawaban' => ['A', 'C'],
    'tampilkan' => 1
]);
assertTest($idSoal2 > 0, "Tipe 2 (PG Kompleks) saved with multi-keys JSON");
$q2 = $soalModel->find($idSoal2);
$q2Keys = is_string($q2['jawaban']) ? json_decode($q2['jawaban'], true) : $q2['jawaban'];
assertTest(is_array($q2Keys) && in_array('A', $q2Keys) && in_array('C', $q2Keys), "Verify PG Kompleks JSON decoding");

// Tipe 3: Menjodohkan (Line Connector Structure)
$idSoal3 = $soalModel->saveSoal($idBank, [
    'jenis' => 3,
    'nomor_soal' => 3,
    'soal' => '<p>Jodohkanlah ibukota dengan negaranya yang tepat!</p>',
    'opsi_a' => '',
    'opsi_b' => '',
    'opsi_c' => '',
    'opsi_d' => '',
    'opsi_e' => '',
    'jawaban' => [
        'type' => 'line',
        'mode' => '1',
        'baris' => ['Indonesia', 'Jepang', 'Inggris'],
        'kolom' => ['Tokyo', 'London', 'Jakarta'],
        'links' => ['0' => ['2'], '1' => ['0'], '2' => ['1']]
    ],
    'tampilkan' => 1
]);
assertTest($idSoal3 > 0, "Tipe 3 (Menjodohkan) saved with Line Connector links JSON");
$q3 = $soalModel->find($idSoal3);
$q3Jodoh = is_string($q3['jawaban']) ? json_decode($q3['jawaban'], true) : $q3['jawaban'];
assertTest(isset($q3Jodoh['links']['0']) && $q3Jodoh['links']['0'][0] === '2', "Verify Line Connector mapping Indonesia -> Jakarta");

// Tipe 4: Isian Singkat
$idSoal4 = $soalModel->saveSoal($idBank, [
    'jenis' => 4,
    'nomor_soal' => 4,
    'soal' => '<p>Presiden pertama Republik Indonesia adalah...</p>',
    'opsi_a' => '',
    'opsi_b' => '',
    'opsi_c' => '',
    'opsi_d' => '',
    'opsi_e' => '',
    'jawaban' => 'Ir. Soekarno',
    'tampilkan' => 1
]);
assertTest($idSoal4 > 0, "Tipe 4 (Isian Singkat) saved");

// Tipe 5: Uraian / Essai
$idSoal5 = $soalModel->saveSoal($idBank, [
    'jenis' => 5,
    'nomor_soal' => 5,
    'soal' => '<p>Jelaskan proses terjadinya fotosintesis pada tumbuhan hijau!</p>',
    'opsi_a' => '',
    'opsi_b' => '',
    'opsi_c' => '',
    'opsi_d' => '',
    'opsi_e' => '',
    'jawaban' => 'Rubrik: Menyebutkan klorofil (skor 20), cahaya matahari (skor 20), reaksi CO2 dan H2O (skor 40), menghasilkan glukosa dan O2 (skor 20).',
    'tampilkan' => 1
]);
assertTest($idSoal5 > 0, "Tipe 5 (Uraian / Essai) saved with scoring rubric");

// Check question bank question counters
$allBankQuestions = $soalModel->getByBank($idBank);
assertTest(count($allBankQuestions) === 5, "Bank Soal total questions count is 5 (1 PG, 1 PGK, 1 Jodoh, 1 Isian, 1 Esai)");

// ----------------------------------------------------
// 5. Excel & CSV Soal Import Parser Service
// ----------------------------------------------------
echo "\n--- 5. Testing Excel/CSV Import Parser Service ---\n";

$excelService = new ExcelSoalImportService();
$csvMockPath = BASE_PATH . '/tests/mock_soal.csv';
$csvContent = "nomor,jenis,soal,opsi_a,opsi_b,opsi_c,opsi_d,opsi_e,jawaban\n" .
              "1,1,\"Siapakah bapak proklamator Indonesia?\",\"Soekarno-Hatta\",\"Sudirman\",\"Diponegoro\",\"Kartini\",\"\",\"A\"\n" .
              "2,2,\"Pilih hewan mamalia:\",\"Kucing\",\"Ayam\",\"Paus\",\"Elang\",\"\",\"A,C\"\n";
file_put_contents($csvMockPath, $csvContent);

$parsedQuestions = $excelService->parseCsv($csvMockPath);
assertTest(count($parsedQuestions) === 2, "ExcelSoalImportService parsed 2 rows from CSV");
assertTest((int)$parsedQuestions[0]['jenis'] === 1 && $parsedQuestions[0]['jawaban'] === 'A', "CSV row 1 parsed as PG Tunggal");
assertTest((int)$parsedQuestions[1]['jenis'] === 2 && is_array($parsedQuestions[1]['jawaban']), "CSV row 2 parsed as PG Kompleks array");
unlink($csvMockPath);

// ----------------------------------------------------
// 6. Jadwal Ujian & Alokasi Waktu
// ----------------------------------------------------
echo "\n--- 6. Testing Jadwal Ujian & Alokasi Waktu ---\n";

$jadwalModel = new CbtJadwal();
$idJadwal = (int)$jadwalModel->insert([
    'id_tp' => $idTp,
    'id_smt' => $idSmt,
    'id_bank' => $idBank,
    'id_jenis' => $idJenis,
    'tgl_mulai' => date('Y-m-d 08:00:00'),
    'tgl_selesai' => date('Y-m-d 10:00:00', strtotime('+3 days')),
    'durasi_ujian' => 90,
    'jam_ke' => 1,
    'jarak' => 15,
    'acak_soal' => 1,
    'acak_opsi' => 1,
    'hasil_tampil' => 0,
    'token' => 1,
    'status' => 1,
    'ulang' => 0,
    'reset_login' => 0
]);
assertTest($idJadwal > 0, "Insert CbtJadwal with jam_ke=1 (id: {$idJadwal})");
$foundJadwal = $jadwalModel->getDetail($idJadwal);
assertTest($foundJadwal && (int)$foundJadwal['durasi_ujian'] === 90 && (int)$foundJadwal['jam_ke'] === 1, "Retrieve CbtJadwal detail");

// ----------------------------------------------------
// 7. Pengawas Ujian & Safe Format Proctors
// ----------------------------------------------------
echo "\n--- 7. Testing Pengawas Ujian & safeFormatProctors() ---\n";

$pengawasModel = new CbtPengawas();

// Before assignment, test safeFormatProctors to ensure NO 'undefined' bug
$initialProctorSummary = $pengawasModel->safeFormatProctors($idJadwal);
assertTest($initialProctorSummary === '-', "Proctors unassigned returns '-' instead of 'undefined'");

// Assign Guru to Ruang & Sesi
$saveProctorOk = $pengawasModel->savePengawas($idTp, $idSmt, $idJadwal, $idRuang, $idSesi, [$idGuru]);
assertTest($saveProctorOk, "Assign Guru as proctor in cbt_pengawas");

$updatedProctorSummary = $pengawasModel->safeFormatProctors($idJadwal);
assertTest($updatedProctorSummary !== '-' && !empty($updatedProctorSummary), "Proctor assigned returns guru names: {$updatedProctorSummary}");

$isAssigned = $pengawasModel->isGuruAssigned($idTp, $idSmt, $idGuru);
assertTest($isAssigned === true, "isGuruAssigned() confirms teacher is authorized proctor");

$matrix = $pengawasModel->getPengawasMatrix($idJadwal);
assertTest(isset($matrix[$idRuang][$idSesi]), "Proctor matrix contains [id_ruang][id_sesi] key");

// ----------------------------------------------------
// 8. Token Ujian CBT
// ----------------------------------------------------
echo "\n--- 8. Testing Token Ujian CBT ---\n";

$tokenModel = new CbtToken();
$newToken = $tokenModel->generateNewToken();
assertTest(strlen($newToken) === 6 && ctype_alnum($newToken), "Generated 6-character CBT token: {$newToken}");

$activeTokenData = $tokenModel->getActiveToken();
assertTest($activeTokenData && $activeTokenData['token'] === $newToken, "Verify active CBT token in database");

// ----------------------------------------------------
// 9. HTTP Routes Verification
// ----------------------------------------------------
echo "\n--- 9. Testing HTTP Routes via cURL ---\n";

function curlRequest(string $url, string $method = 'GET', array $data = [], ?string $cookieFile = null, bool $follow = true): array {
    $ch = curl_init();
    curl_setopt($ch, CURLOPT_URL, $url);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_FOLLOWLOCATION, $follow);
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
    $finalUrl = curl_getinfo($ch, CURLINFO_EFFECTIVE_URL);
    curl_close($ch);
    return ['code' => $httpCode, 'body' => $body, 'url' => $finalUrl];
}

$adminCookie = BASE_PATH . '/tests/admin_cookie_m4.txt';
$guruCookie = BASE_PATH . '/tests/guru_cookie_m4.txt';

// Login as Admin (Expects 302 redirect to /dashboard)
$adminLogin = curlRequest('http://127.0.0.1:8080/login', 'POST', [
    'username' => 'admin',
    'password' => 'admin123'
], $adminCookie, false);
assertTest($adminLogin['code'] === 302 || $adminLogin['code'] === 200, "Admin login HTTP {$adminLogin['code']}");

// Test Admin Routes
$adminRoutes = [
    '/cbt/master' => 'Master CBT Index',
    '/cbt/master/ruang-sesi-siswa' => 'Atur Ruang & Sesi Siswa Matrix',
    '/cbt/master/nomor-peserta' => 'Nomor Peserta Ujian List',
    '/cbt/bank-soal' => 'Bank Soal Index',
    '/cbt/bank-soal/create' => 'Form Tambah Bank Soal',
    '/cbt/bank-soal/detail/' . $idBank => 'Detail Bank Soal (5 AKM items)',
    '/cbt/bank-soal/soal/' . $idBank . '?jenis=1' => 'Editor Soal Tipe 1 (PG Tunggal)',
    '/cbt/bank-soal/soal/' . $idBank . '?jenis=3' => 'Editor Soal Tipe 3 (Menjodohkan Canvas)',
    '/cbt/bank-soal/template-excel' => 'Download Template Excel Soal',
    '/cbt/jadwal' => 'Jadwal Ujian Index',
    '/cbt/jadwal/create' => 'Form Tambah Jadwal Ujian',
    '/cbt/jadwal/pengawas/' . $idJadwal => 'Matriks Atur Pengawas Ujian',
    '/cbt/token' => 'Token Ujian Management'
];

foreach ($adminRoutes as $route => $label) {
    $res = curlRequest('http://127.0.0.1:8080' . $route, 'GET', [], $adminCookie);
    assertTest($res['code'] === 200, "Admin: {$label} ({$route}) -> HTTP {$res['code']}");
}

// Login as Guru (Expects 302 redirect)
$guruLogin = curlRequest('http://127.0.0.1:8080/login', 'POST', [
    'username' => 'guru1',
    'password' => 'guru123'
], $guruCookie, false);
assertTest($guruLogin['code'] === 302 || $guruLogin['code'] === 200, "Guru login HTTP {$guruLogin['code']}");

$guruRoutes = [
    '/guru/bank-soal' => 'Bank Soal Guru',
    '/guru/pengawas' => 'Ruang Guru Pengawas'
];

foreach ($guruRoutes as $route => $label) {
    $res = curlRequest('http://127.0.0.1:8080' . $route, 'GET', [], $guruCookie);
    assertTest($res['code'] === 200, "Guru: {$label} ({$route}) -> HTTP {$res['code']}");
}

// Cleanup cookies
@unlink($adminCookie);
@unlink($guruCookie);

// Clean up temporary test data
$soalModel->deleteSoal($idSoal1);
$soalModel->deleteSoal($idSoal2);
$soalModel->deleteSoal($idSoal3);
$soalModel->deleteSoal($idSoal4);
$soalModel->deleteSoal($idSoal5);
$jadwalModel->delete($idJadwal);
$bankModel->delete($idBank);
$jenisModel->delete($idJenis);
$ruangModel->delete($idRuang);
$sesiModel->delete($idSesi);

echo "\n========================================================\n";
echo "   TEST RESULTS: {$testsPassed} PASSED, {$testsFailed} FAILED\n";
echo "========================================================\n";

if ($testsFailed > 0) {
    exit(1);
}
