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
use App\Models\CbtBankSoal;
use App\Models\CbtSoal;
use App\Models\CbtJadwal;
use App\Models\CbtDurasiSiswa;
use App\Models\CbtSoalSiswa;
use App\Models\CbtNilai;
use App\Models\CbtToken;
use App\Models\MasterTp;
use App\Models\MasterSmt;
use App\Models\MasterSiswa;
use App\Models\MasterGuru;
use App\Models\MasterMapel;
use App\Models\MasterKelas;
use App\Models\KelasSiswa;
use App\Services\QuestionRandomizerService;
use App\Services\AutoGraderService;

echo "========================================================\n";
echo "   GARUDACBT v1.5.3 REBUILD — MILESTONE 5 TEST SUITE    \n";
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
// Setup Fixtures for Milestone 5
// ----------------------------------------------------
echo "--- 1. Setting Up Test Fixtures ---\n";

$tpModel = new MasterTp();
$activeTp = $tpModel->getActive();
$idTp = (int)($activeTp['id_tp'] ?? 1);

$smtModel = new MasterSmt();
$activeSmt = $smtModel->getActive();
$idSmt = (int)($activeSmt['id_smt'] ?? 1);

$siswaModel = new MasterSiswa();
$siswaList = $siswaModel->all('id_siswa ASC', 1);
if (empty($siswaList)) {
    $idSiswa = (int)$siswaModel->insert([
        'nama' => 'Ahmad Siswa Test',
        'nis' => '10001',
        'nisn' => '0010001',
        'jk' => 'L',
        'status' => 1
    ]);
} else {
    $idSiswa = (int)$siswaList[0]['id_siswa'];
}

$kelasModel = new MasterKelas();
$kelasList = $kelasModel->all('id_kelas ASC', 1);
$idKelas = !empty($kelasList) ? (int)$kelasList[0]['id_kelas'] : 1;

// Link student to class
$kelasSiswaModel = new KelasSiswa();
$existingKs = $kelasSiswaModel->first(['id_siswa' => $idSiswa, 'id_tp' => $idTp, 'id_smt' => $idSmt]);
if (!$existingKs) {
    $kelasSiswaModel->insert([
        'id_tp' => $idTp,
        'id_smt' => $idSmt,
        'id_kelas' => $idKelas,
        'id_siswa' => $idSiswa,
    ]);
}

$mapelModel = new MasterMapel();
$mapelList = $mapelModel->all('id_mapel ASC', 1);
$idMapel = !empty($mapelList) ? (int)$mapelList[0]['id_mapel'] : 1;

$guruModel = new MasterGuru();
$guruList = $guruModel->all('id_guru ASC', 1);
$idGuru = !empty($guruList) ? (int)$guruList[0]['id_guru'] : 1;

$jenisModel = new CbtJenis();
$jenisList = $jenisModel->all('id_jenis ASC', 1);
$idJenis = !empty($jenisList) ? (int)$jenisList[0]['id_jenis'] : 1;

// Create Test Bank Soal
$bankModel = new CbtBankSoal();
$idBank = (int)$bankModel->insert([
    'id_tp' => $idTp,
    'id_smt' => $idSmt,
    'bank_jenis_id' => $idJenis,
    'bank_kode' => 'BANK-M5-' . time(),
    'bank_level' => 7,
    'bank_kelas' => json_encode([$idKelas]),
    'bank_mapel_id' => $idMapel,
    'bank_guru_id' => $idGuru,
    'opsi' => 4,
    'tampil_pg' => 1,
    'bobot_pg' => 20,
    'tampil_kompleks' => 1,
    'bobot_kompleks' => 20,
    'tampil_jodohkan' => 1,
    'bobot_jodohkan' => 20,
    'tampil_isian' => 1,
    'bobot_isian' => 20,
    'tampil_esai' => 1,
    'bobot_esai' => 20,
    'status' => 1,
    'status_soal' => 1
]);
assertTest($idBank > 0, "Created Test Question Bank (ID: {$idBank})");

// Insert 5 AKM Question Items
$soalModel = new CbtSoal();

// 1. PG Tunggal
$idSoal1 = (int)$soalModel->saveSoal($idBank, [
    'jenis' => 1,
    'nomor_soal' => 1,
    'soal' => '<p>Berapakah hasil dari 5 + 7?</p>',
    'opsi_a' => '<p>10</p>',
    'opsi_b' => '<p>12</p>',
    'opsi_c' => '<p>14</p>',
    'opsi_d' => '<p>16</p>',
    'opsi_e' => '',
    'jawaban' => 'B',
    'tampilkan' => 1
]);

// 2. PG Kompleks
$idSoal2 = (int)$soalModel->saveSoal($idBank, [
    'jenis' => 2,
    'nomor_soal' => 2,
    'soal' => '<p>Pilih kota yang terletak di Pulau Jawa:</p>',
    'opsi_a' => '<p>Surabaya</p>',
    'opsi_b' => '<p>Medan</p>',
    'opsi_c' => '<p>Bandung</p>',
    'opsi_d' => '<p>Makassar</p>',
    'opsi_e' => '',
    'jawaban' => ['A', 'C'],
    'tampilkan' => 1
]);

// 3. Menjodohkan
$idSoal3 = (int)$soalModel->saveSoal($idBank, [
    'jenis' => 3,
    'nomor_soal' => 3,
    'soal' => '<p>Pasangkan mata uang dengan negaranya:</p>',
    'opsi_a' => '', 'opsi_b' => '', 'opsi_c' => '', 'opsi_d' => '', 'opsi_e' => '',
    'jawaban' => [
        'type' => 'line',
        'mode' => '1',
        'baris' => ['Rupiah', 'Yen'],
        'kolom' => ['Jepang', 'Indonesia'],
        'links' => ['0' => ['1'], '1' => ['0']]
    ],
    'tampilkan' => 1
]);

// 4. Isian Singkat
$idSoal4 = (int)$soalModel->saveSoal($idBank, [
    'jenis' => 4,
    'nomor_soal' => 4,
    'soal' => '<p>Ibukota negara Indonesia saat ini adalah...</p>',
    'opsi_a' => '', 'opsi_b' => '', 'opsi_c' => '', 'opsi_d' => '', 'opsi_e' => '',
    'jawaban' => 'Jakarta',
    'tampilkan' => 1
]);

// 5. Essai
$idSoal5 = (int)$soalModel->saveSoal($idBank, [
    'jenis' => 5,
    'nomor_soal' => 5,
    'soal' => '<p>Jelaskan secara singkat manfaat menjaga kebersihan lingkungan!</p>',
    'opsi_a' => '', 'opsi_b' => '', 'opsi_c' => '', 'opsi_d' => '', 'opsi_e' => '',
    'jawaban' => 'Pedoman penskoran: Menyebutkan kesehatan (50 poin), keindahan (50 poin).',
    'tampilkan' => 1
]);
assertTest($idSoal1 > 0 && $idSoal5 > 0, "Inserted 5 AKM Question items");

// Create Schedule
$jadwalModel = new CbtJadwal();
$idJadwal = (int)$jadwalModel->insert([
    'id_tp' => $idTp,
    'id_smt' => $idSmt,
    'id_bank' => $idBank,
    'id_jenis' => $idJenis,
    'tgl_mulai' => date('Y-m-d 07:00:00'),
    'tgl_selesai' => date('Y-m-d 23:59:59', strtotime('+2 days')),
    'durasi_ujian' => 60,
    'jam_ke' => 1,
    'jarak' => 15,
    'acak_soal' => 1,
    'acak_opsi' => 1,
    'hasil_tampil' => 1,
    'token' => 1,
    'status' => 1,
    'ulang' => 0,
    'reset_login' => 0
]);
assertTest($idJadwal > 0, "Created Active Exam Schedule (ID: {$idJadwal})");

// Generate Active Token
$tokenModel = new CbtToken();
$activeToken = $tokenModel->generateNewToken();
assertTest(!empty($activeToken), "Active CBT Token: {$activeToken}");

// ----------------------------------------------------
// 2. Testing QuestionRandomizerService (Fisher-Yates)
// ----------------------------------------------------
echo "\n--- 2. Testing QuestionRandomizerService ---\n";

$randomizer = new QuestionRandomizerService();
$generatedQuestions = $randomizer->initializeExamQuestions($idJadwal, $idSiswa);

assertTest(count($generatedQuestions) === 5, "Randomizer generated 5 student questions (Quota: 1 per type)");
assertTest((int)$generatedQuestions[0]['no_soal_alias'] === 1, "First alias number is 1");
assertTest((int)$generatedQuestions[4]['no_soal_alias'] === 5, "Last alias number is 5");

// Check PG Option alias mapping
$pgStudentRow = null;
foreach ($generatedQuestions as $gq) {
    if ((int)$gq['jenis_soal'] === 1) {
        $pgStudentRow = $gq;
        break;
    }
}
assertTest($pgStudentRow !== null && !empty($pgStudentRow['opsi_alias_a']), "PG Tunggal has options alias mapping (opsi_alias_a: {$pgStudentRow['opsi_alias_a']})");

// Re-requesting should return existing records without duplicate insert
$secondFetch = $randomizer->initializeExamQuestions($idJadwal, $idSiswa);
assertTest(count($secondFetch) === 5, "Re-requesting returns existing 5 questions (Idempotency verified)");

// ----------------------------------------------------
// 3. Testing CbtSoalSiswa & Atomic Save Answers
// ----------------------------------------------------
echo "\n--- 3. Testing CbtSoalSiswa & Atomic Answer Save ---\n";

$soalSiswaModel = new CbtSoalSiswa();
$durasiModel = new CbtDurasiSiswa();
$nilaiModel = new CbtNilai();

// Find question records for student
$questionsByAlias = [];
foreach ($generatedQuestions as $gq) {
    $questionsByAlias[(int)$gq['jenis_soal']] = $gq;
}

// 1. Answer PG Tunggal (Answer = 'B')
$idPg = (int)$questionsByAlias[1]['id_soal_siswa'];
$savePg = $soalSiswaModel->saveAnswer($idPg, 'B', 'B', 0);
assertTest($savePg, "Atomic save PG Tunggal answer");

// 2. Answer PG Kompleks (Answer = ["A", "C"])
$idPgk = (int)$questionsByAlias[2]['id_soal_siswa'];
$savePgk = $soalSiswaModel->saveAnswer($idPgk, ['A', 'C'], ['A', 'C'], 0);
assertTest($savePgk, "Atomic save PG Kompleks answer");

// 3. Answer Menjodohkan (Partial/Empty link test vs Valid link)
$idJodoh = (int)$questionsByAlias[3]['id_soal_siswa'];

// Empty link: should NOT be marked answered
$soalSiswaModel->saveAnswer($idJodoh, ['type' => 'line', 'links' => []], null, 0);
$navStatusEmpty = $soalSiswaModel->getNavStatus($idJadwal, $idSiswa);
assertTest($navStatusEmpty['answered'] === 2, "Menjodohkan with empty links is NOT counted as answered (PRD Validation Rule)");

// Valid link: connect 0->1 and 1->0
$soalSiswaModel->saveAnswer($idJodoh, [
    'type' => 'line',
    'links' => ['0' => ['1'], '1' => ['0']]
], null, 0);
$navStatusFilled = $soalSiswaModel->getNavStatus($idJadwal, $idSiswa);
assertTest($navStatusFilled['answered'] === 3, "Menjodohkan with valid connected lines IS counted as answered");

// 4. Answer Isian Singkat with trailing spaces and mixed casing ("  jaKaRTA  ")
$idIsian = (int)$questionsByAlias[4]['id_soal_siswa'];
$saveIsian = $soalSiswaModel->saveAnswer($idIsian, '  jaKaRTA  ', '  jaKaRTA  ', 0);
assertTest($saveIsian, "Atomic save Isian Singkat answer");

// 5. Answer Essai with HTML format & toggle ragu-ragu
$idEsai = (int)$questionsByAlias[5]['id_soal_siswa'];
$saveEsai = $soalSiswaModel->saveAnswer($idEsai, '<p><b>Lingkungan bersih</b> mencegah penyebaran penyakit dan memberi kenyamanan.</p>', null, 1);
assertTest($saveEsai, "Atomic save Essai answer with ragu-ragu flag");

$navFinal = $soalSiswaModel->getNavStatus($idJadwal, $idSiswa);
assertTest($navFinal['answered'] === 5 && $navFinal['ragu'] === 1, "Final nav status: 5/5 answered, 1 ragu-ragu");

// ----------------------------------------------------
// 4. Testing CbtDurasiSiswa & Anti-Cheat Violation
// ----------------------------------------------------
echo "\n--- 4. Testing CbtDurasiSiswa & Anti-Cheat ---\n";

$durasiModel = new CbtDurasiSiswa();
$session = $durasiModel->startExam($idJadwal, $idSiswa, 60);
assertTest($session && (int)$session['status'] === 1, "Start exam session (status=1 Sedang Ujian)");

// Record tab violation
$vCount = $durasiModel->recordViolation($idJadwal, $idSiswa);
assertTest($vCount === 1, "Recorded tab blur violation (counter = {$vCount})");

// Reset login permission
$resetOk = $durasiModel->resetLogin($idJadwal, $idSiswa);
$sessAfterReset = $durasiModel->getSession($idJadwal, $idSiswa);
assertTest($resetOk && (int)$sessAfterReset['reset'] === 2, "Reset login permission (reset=2)");

// Re-start clears reset flag
$resumedSession = $durasiModel->startExam($idJadwal, $idSiswa, 60);
assertTest((int)$resumedSession['reset'] === 0, "Resuming exam clears reset flag back to 0");

// ----------------------------------------------------
// 5. Testing AutoGraderService & Katrol Formula
// ----------------------------------------------------
echo "\n--- 5. Testing AutoGraderService & Katrol Nilai ---\n";

$grader = new AutoGraderService();

// Simulate manual essay score input for testing
$soalSiswaModel->update($idEsai, [
    'nilai_koreksi' => 90.00,
    'nilai_otomatis' => 1
]);

$gradeResult = $grader->gradeExam($idJadwal, $idSiswa);

assertTest((float)$gradeResult['pg_nilai'] === 20.00, "PG Tunggal full score (20.00)");
assertTest((float)$gradeResult['kompleks_nilai'] === 20.00, "PG Kompleks full score (20.00)");
assertTest((float)$gradeResult['jodohkan_nilai'] === 20.00, "Menjodohkan full score (20.00)");
assertTest((float)$gradeResult['isian_nilai'] === 20.00, "Isian Singkat normalized case-insensitive match (20.00)");
assertTest((float)$gradeResult['essai_nilai'] === 18.00, "Essai score based on 90 points (18.00)");

$expectedTotal = 20 + 20 + 20 + 20 + 18; // 98.00
assertTest((float)$gradeResult['total_nilai'] === 98.00, "Total Score = {$gradeResult['total_nilai']} (Expected: {$expectedTotal})");

// Test Katrol / Konversi Nilai Formula (PRD 4.4)
// Formula: N_konversi = T_min + ((N_asli - N_min_asli) / (N_max_asli - N_min_asli)) * (T_max - T_min)
$converted = $grader->convertScore(50.0, 20.0, 80.0, 70.0, 95.0);
// ratio = (50 - 20) / (80 - 20) = 30/60 = 0.5. Converted = 70 + 0.5 * 25 = 82.50
assertTest($converted === 82.50, "Formula Katrol Nilai: convert 50.0 [20-80] to [70-95] = {$converted} (Expected: 82.50)");

// Finish Exam
$finishOk = $durasiModel->finishExam($idJadwal, $idSiswa);
$sessFinished = $durasiModel->getSession($idJadwal, $idSiswa);
assertTest($finishOk && (int)$sessFinished['status'] === 2, "Exam finished (status=2)");

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

$adminCookie = BASE_PATH . '/tests/admin_cookie_m5.txt';
$siswaCookie = BASE_PATH . '/tests/siswa_cookie_m5.txt';

// 1. Admin Login
$adminLogin = curlReq('http://127.0.0.1:8080/login', 'POST', [
    'username' => 'admin',
    'password' => 'admin123'
], $adminCookie);
assertTest($adminLogin['code'] === 302 || $adminLogin['code'] === 200, "Admin login HTTP {$adminLogin['code']}");

// 2. Admin Routes (Cetak, Status Siswa, Koreksi, Rekap)
$adminRoutes = [
    '/cbt/status?jadwal_id=' . $idJadwal => 'Live Status Siswa Monitoring',
    '/cbt/cetak' => 'Cetak Administrasi Index',
    '/cbt/cetak/kartu-peserta' => 'Cetak Kartu Peserta Ujian',
    '/cbt/cetak/daftar-hadir?jadwal_id=' . $idJadwal => 'Cetak Daftar Hadir Ujian',
    '/cbt/cetak/berita-acara?jadwal_id=' . $idJadwal => 'Cetak Berita Acara Ujian',
    '/cbt/koreksi' => 'Koreksi Ujian Index',
    '/cbt/koreksi/peserta/' . $idJadwal => 'Koreksi Daftar Peserta',
    '/cbt/koreksi/detail/' . $idJadwal . '/' . $idSiswa => 'Koreksi Detail Lembar Siswa',
    '/cbt/analisis/' . $idJadwal => 'Analisis Butir Soal',
    '/cbt/rekap-nilai?jadwal_id=' . $idJadwal => 'Rekap Nilai Ujian'
];

foreach ($adminRoutes as $route => $label) {
    $res = curlReq('http://127.0.0.1:8080' . $route, 'GET', [], $adminCookie);
    assertTest($res['code'] === 200, "Admin: {$label} ({$route}) -> HTTP {$res['code']}");
}

// 3. Siswa Login
$siswaLogin = curlReq('http://127.0.0.1:8080/cbt/login', 'POST', [
    'identifier' => 'siswa',
    'password' => 'siswa123'
], $siswaCookie);
assertTest($siswaLogin['code'] === 302 || $siswaLogin['code'] === 200, "Siswa CBT login HTTP {$siswaLogin['code']}");

// 4. Siswa Routes
$resPortal = curlReq('http://127.0.0.1:8080/student/cbt', 'GET', [], $siswaCookie);
assertTest($resPortal['code'] === 200, "Siswa: Portal Ujian Siswa (/student/cbt) -> HTTP {$resPortal['code']}");

// Delete session temporarily to test confirmation screen (Token Input)
$durasiModel->deleteSession($idJadwal, $idSiswa);
$resKonf = curlReq('http://127.0.0.1:8080/student/cbt/konfirmasi/' . $idJadwal, 'GET', [], $siswaCookie);
assertTest($resKonf['code'] === 200, "Siswa: Konfirmasi Ujian Siswa (/student/cbt/konfirmasi/{$idJadwal}) -> HTTP {$resKonf['code']}");

// Re-finish session to test completion screen
$durasiModel->finishExam($idJadwal, $idSiswa);
$resSelesai = curlReq('http://127.0.0.1:8080/student/cbt/selesai/' . $idJadwal, 'GET', [], $siswaCookie);
assertTest($resSelesai['code'] === 200, "Siswa: Rincian Selesai Ujian Siswa (/student/cbt/selesai/{$idJadwal}) -> HTTP {$resSelesai['code']}");

// Cleanup cookies
@unlink($adminCookie);
@unlink($siswaCookie);

// Clean up temporary test data
$soalSiswaModel->deleteStudentQuestions($idJadwal, $idSiswa);
$durasiModel->deleteSession($idJadwal, $idSiswa);
$nilaiModel->deleteScore($idJadwal, $idSiswa);
$soalModel->deleteSoal($idSoal1);
$soalModel->deleteSoal($idSoal2);
$soalModel->deleteSoal($idSoal3);
$soalModel->deleteSoal($idSoal4);
$soalModel->deleteSoal($idSoal5);
$jadwalModel->delete($idJadwal);
$bankModel->delete($idBank);

echo "\n========================================================\n";
echo "   TEST RESULTS: {$testsPassed} PASSED, {$testsFailed} FAILED\n";
echo "========================================================\n";

if ($testsFailed > 0) {
    exit(1);
}
