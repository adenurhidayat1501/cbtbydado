<?php

/**
 * Test Suite: Milestone 3 — Modul E-Learning & Presensi KBM
 * Run: php tests/test_milestone3.php
 */

spl_autoload_register(function ($class) {
    $prefix = 'App\\';
    $baseDir = __DIR__ . '/../app/';

    $len = strlen($prefix);
    if (strncmp($prefix, $class, $len) !== 0) {
        return;
    }

    $relativeClass = substr($class, $len);
    $file = $baseDir . str_replace('\\', '/', $relativeClass) . '.php';

    if (file_exists($file)) {
        require_once $file;
    }
});

require_once __DIR__ . '/../app/Helpers/functions.php';

use App\Core\Database;
use App\Models\KelasJadwalKbm;
use App\Models\KelasJadwalMapel;
use App\Models\KelasMateri;
use App\Models\KelasJadwalMateri;
use App\Models\LogMateri;
use App\Models\KelasMateriKomentar;

$passed = 0;
$failed = 0;

function assertTest(string $desc, bool $condition): void {
    global $passed, $failed;
    if ($condition) {
        echo "  [PASS] {$desc}\n";
        $passed++;
    } else {
        echo "  [FAIL] {$desc}\n";
        $failed++;
    }
}

echo "========================================================\n";
echo "   GARUDACBT REBUILD — MILESTONE 3 TEST SUITE\n";
echo "========================================================\n\n";

$db = Database::getInstance()->getPdo();
$tp = active_tp();
$smt = active_smt();
$idTp = (int)($tp['id_tp'] ?? 2);
$idSmt = (int)($smt['id_smt'] ?? 1);
$idKelas = 1; // Kelas 7A demo
$idGuru = 1;  // Guru demo
$idMapel = 1; // PAI

// -------------------------------------------------------------
echo "Test 1: KelasJadwalKbm — Pengaturan Waktu & Slot KBM\n";
$kbmModel = new KelasJadwalKbm();

$setting = $kbmModel->getSetting($idTp, $idSmt, $idKelas);
assertTest("1.1 Dapat membaca setting KBM kelas (default atau tersimpan)", is_array($setting) && isset($setting['kbm_jam_pel']));

$saveResult = $kbmModel->saveSetting($idTp, $idSmt, $idKelas, [
    'kbm_jam_pel' => 45,
    'kbm_jam_mulai' => '07:15:00',
    'kbm_jml_mapel_hari' => 6,
    'istirahat' => [
        ['ist' => 1, 'mulai' => '09:30', 'durasi' => 20],
        ['ist' => 2, 'mulai' => '11:45', 'durasi' => 35],
    ]
]);
assertTest("1.2 Berhasil menyimpan kustomisasi waktu KBM dan jam istirahat", $saveResult);

$newSetting = $kbmModel->getSetting($idTp, $idSmt, $idKelas);
assertTest("1.3 Durasi per jam tersimpan menjadi 45 menit", (int)$newSetting['kbm_jam_pel'] === 45);

$slots = $kbmModel->getTimeSlots($newSetting);
assertTest("1.4 Generator time slot menghasilkan slot jam pelajaran dan istirahat", count($slots) >= 6);

$jamKeTest = $kbmModel->getCurrentJamKe($newSetting, '07:30');
assertTest("1.5 Deteksi jam pelajaran aktif pada pukul 07:30 adalah Jam Ke-1", $jamKeTest === 1);

// -------------------------------------------------------------
echo "\nTest 2: KelasJadwalMapel — Matriks Jadwal Mingguan\n";
$mapelJadwalModel = new KelasJadwalMapel();

// Set cell: Senin (id_hari = 1), Jam Ke-1, Mapel PAI (1), Guru (1)
$cellSaved = $mapelJadwalModel->saveCell($idTp, $idSmt, $idKelas, 1, 1, $idMapel, $idGuru);
assertTest("2.1 Berhasil menyimpan sel jadwal: Senin Jam Ke-1 -> Mapel PAI & Guru 1", $cellSaved);

$matrix = $mapelJadwalModel->getMatrix($idTp, $idSmt, $idKelas);
assertTest("2.2 Matriks jadwal membaca sel Senin Jam Ke-1 dengan nama mapel valid", isset($matrix[1][1]) && !empty($matrix[1][1]['nama_mapel']));

$guruJadwal = $mapelJadwalModel->getJadwalForGuru($idTp, $idSmt, $idGuru);
assertTest("2.3 Jadwal mengajar guru membaca penugasan pada kelas terkait", count($guruJadwal) >= 1);

$foundJadwal = $mapelJadwalModel->findJadwalMapel($idTp, $idSmt, $idKelas, $idMapel, 1);
assertTest("2.4 Pencarian jadwal mapel hari Senin menemukan record jadwal aktif", $foundJadwal !== null && (int)$foundJadwal['jam_ke'] === 1);

// -------------------------------------------------------------
echo "\nTest 3: KelasMateri — CRUD Materi/Tugas & Isolasi Hak Akses Guru\n";
$materiModel = new KelasMateri();

// 3.1 Buat Materi Baru (jenis = 1)
$idMateri = $materiModel->createMateri([
    'id_tp' => $idTp,
    'id_smt' => $idSmt,
    'jenis' => 1,
    'kode_materi' => 'MAT-TEST-' . time(),
    'id_guru' => $idGuru,
    'id_mapel' => $idMapel,
    'id_kelas' => [$idKelas],
    'judul_materi' => 'Bab 1 - Pengenalan Tajwid & Makharijul Huruf',
    'isi_materi' => '<p>Materi tajwid hukum bacaan nun sukun dan tanwin: \( \int x dx \) dan teks Arab: <span class="arabic-text">بِسْمِ اللَّهِ الرَّحْمَٰنِ الرَّحِيمِ</span></p>',
    'file' => [
        ['orig_name' => 'tajwid.pdf', 'filename' => 'tajwid_123.pdf', 'path' => 'uploads/materi/tajwid_123.pdf', 'size' => 102400]
    ],
    'link_video' => 'https://www.youtube.com/watch?v=dQw4w9WgXcQ',
    'status' => 1,
]);
assertTest("3.1 Pembuatan materi belajar baru mengembalikan ID materi valid", $idMateri > 0);

// 3.2 Isolasi Hak Akses Guru
$isOwnerAuthorized = $materiModel->isGuruAuthorized($idMateri, $idGuru);
$isOtherTeacherAuthorized = $materiModel->isGuruAuthorized($idMateri, 9999);
assertTest("3.2 Guru pemilik materi terotorisasi (isGuruAuthorized = true)", $isOwnerAuthorized === true);
assertTest("3.3 Guru lain tidak terotorisasi mengakses materi (isGuruAuthorized = false)", $isOtherTeacherAuthorized === false);

// 3.3 Buat Tugas Siswa Baru (jenis = 2)
$idTugas = $materiModel->createMateri([
    'id_tp' => $idTp,
    'id_smt' => $idSmt,
    'jenis' => 2,
    'kode_materi' => 'TGS-TEST-' . time(),
    'id_guru' => $idGuru,
    'id_mapel' => $idMapel,
    'id_kelas' => [$idKelas],
    'judul_materi' => 'Tugas 1 - Menuliskan Contoh Hukum Idgham Bighunnah',
    'isi_materi' => '<p>Carilah 3 contoh hukum bacaan Idgham Bighunnah dari Al-Quran Surah Al-Baqarah!</p>',
    'file' => [],
    'link_video' => null,
    'status' => 1,
]);
assertTest("3.4 Pembuatan tugas siswa baru mengembalikan ID tugas valid", $idTugas > 0);

// 3.5 Edit materi tanpa merusak berkas lampiran lama
$updateResult = $materiModel->updateMateri($idMateri, [
    'judul_materi' => 'Bab 1 - Pengenalan Tajwid (Revisi Lengkap)',
    'file' => [
        ['orig_name' => 'tajwid.pdf', 'filename' => 'tajwid_123.pdf', 'path' => 'uploads/materi/tajwid_123.pdf', 'size' => 102400],
        ['orig_name' => 'latihan.docx', 'filename' => 'latihan_456.docx', 'path' => 'uploads/materi/latihan_456.docx', 'size' => 51200]
    ]
]);
assertTest("3.5 Berhasil memperbarui materi dan melampirkan berkas tambahan tanpa merusak lampiran lama", $updateResult);

$detailMateri = $materiModel->getDetail($idMateri);
assertTest("3.6 Detail materi membaca 2 lampiran tersimpan", count($detailMateri['file_list']) === 2);

// -------------------------------------------------------------
echo "\nTest 4: KelasJadwalMateri — Penjadwalan Materi & Tugas ke Rombel\n";
$kjmModel = new KelasJadwalMateri();
$today = date('Y-m-d');

// Schedule materi & tugas for today
$schedMateri = $kjmModel->scheduleMateri($idTp, $idSmt, $idMateri, [$idKelas], $today);
$schedTugas = $kjmModel->scheduleMateri($idTp, $idSmt, $idTugas, [$idKelas], $today);
assertTest("4.1 Penjadwalan materi ke Kelas 7A untuk hari ini berhasil", $schedMateri);
assertTest("4.2 Penjadwalan tugas ke Kelas 7A untuk hari ini berhasil", $schedTugas);

$schedules = $kjmModel->getSchedules($idTp, $idSmt, $idKelas);
assertTest("4.3 Daftar jadwal materi/tugas membaca sesi yang telah dijadwalkan", count($schedules) >= 2);

$studentMateriList = $kjmModel->getStudentSchedule($idTp, $idSmt, $idKelas, 1, 1);
assertTest("4.4 Jadwal belajar siswa untuk materi mengembalikan item dengan status", count($studentMateriList) >= 1);

$idKjmTugas = 0;
$idKjmMateri = 0;
foreach ($schedules as $s) {
    if ((int)$s['id_materi'] === $idTugas) {
        $idKjmTugas = (int)$s['id_kjm'];
    }
    if ((int)$s['id_materi'] === $idMateri) {
        $idKjmMateri = (int)$s['id_kjm'];
    }
}
assertTest("4.5 Mendapatkan ID jadwal tayang materi ($idKjmMateri) dan tugas ($idKjmTugas)", $idKjmTugas > 0 && $idKjmMateri > 0);

// -------------------------------------------------------------
echo "\nTest 5: LogMateri — Pencatatan Presensi Otomatis & Penilaian Tugas\n";
$logModel = new LogMateri();
$idSiswa = 1; // Siswa demo Rizky

// 5.1 Log Pembacaan Materi (Presensi Otomatis)
$logRead = $logModel->logRead($idSiswa, $idKjmMateri, 1);
assertTest("5.1 Presensi otomatis tercatat saat siswa membuka materi (Jam Ke-1, log_type=1)", $logRead);

$studentLog = $logModel->getStudentLog($idSiswa, $idKjmMateri);
assertTest("5.2 Verifikasi log materi: Siswa tercatat hadir membaca", $studentLog !== null && (int)$studentLog['log_type'] === 1);

// 5.2 Siswa Mengumpulkan Jawaban Tugas
$submitTugas = $logModel->submitTugas(
    $idSiswa,
    $idKjmTugas,
    'Berikut adalah 3 contoh Idgham Bighunnah dalam Surah Al-Baqarah: ...',
    [['orig_name' => 'jawaban_rizky.pdf', 'filename' => 'ans_rizky.pdf', 'path' => 'uploads/tugas/ans_rizky.pdf', 'size' => 20480]],
    1
);
assertTest("5.3 Siswa berhasil mengumpulkan jawaban teks dan lampiran file tugas (log_type=3)", $submitTugas);

$tugasLog = $logModel->getStudentLog($idSiswa, $idKjmTugas);
assertTest("5.4 Verifikasi status tugas siswa: log_type=3 (Kumpul Tugas)", (int)$tugasLog['log_type'] === 3);

// 5.3 Guru Menilai Tugas
$gradeResult = $logModel->gradeTugas((int)$tugasLog['id_log'], 92.50, 'Jawaban sangat baik dan tepat!');
assertTest("5.5 Guru berhasil memberikan nilai 92.50 dan catatan revisi", $gradeResult);

$gradedLog = $logModel->getStudentLog($idSiswa, $idKjmTugas);
assertTest("5.6 Nilai tersimpan dengan presisi desimal: 92.50", (float)$gradedLog['nilai'] === 92.50);

// 5.4 Rekap Kehadiran Harian
$rekapHarian = $logModel->getRekapHarian($idTp, $idSmt, $idKelas, $idMapel, $today);
assertTest("5.7 Rekap Kehadiran Harian mengembalikan status kehadiran siswa", count($rekapHarian) >= 1 && !empty($rekapHarian[0]['id_log']));

// 5.5 Rekap Kehadiran Bulanan
$bulan = (int)date('n');
$tahun = (int)date('Y');
$rekapBulanan = $logModel->getRekapBulanan($idTp, $idSmt, $idKelas, $idMapel, $bulan, $tahun);
assertTest("5.8 Rekap Kehadiran Bulanan menghitung akumulasi kehadiran dan persentase", count($rekapBulanan['students_rekap']) >= 1 && $rekapBulanan['students_rekap'][0]['persentase'] > 0);

// 5.6 Rekap Nilai Tugas E-Learning
$rekapNilai = $logModel->getRekapNilaiTugas($idTp, $idSmt, $idKelas, $idMapel);
assertTest("5.9 Rekap Nilai Tugas menghitung rata-rata nilai siswa secara akurat", count($rekapNilai['students_rekap']) >= 1 && (float)$rekapNilai['students_rekap'][0]['rata_rata'] === 92.50);

// -------------------------------------------------------------
echo "\nTest 6: KelasMateriKomentar — Diskusi & Ruang Tanya Jawab\n";
$komentarModel = new KelasMateriKomentar();

$addCommentSiswa = $komentarModel->addKomentar($idMateri, 3, 'Pak, apakah contoh hukum Idgham Mimi juga dipelajari di bab ini?');
assertTest("6.1 Siswa berhasil mengirimkan pertanyaan ke forum materi", $addCommentSiswa);

$addCommentGuru = $komentarModel->addKomentar($idMateri, 2, 'Betul Rizky, kita akan bahas tuntas pada pertemuan berikutnya.');
assertTest("6.2 Guru pengampu berhasil membalas diskusi materi", $addCommentGuru);

$comments = $komentarModel->getKomentar($idMateri);
assertTest("6.3 Komentar materi menampilkan thread diskusi dengan nama pengirim", count($comments) >= 2 && !empty($comments[0]['author_name']));

echo "\n========================================================\n";
echo "SUMMARY: {$passed} PASSED, {$failed} FAILED\n";
echo "========================================================\n";

if ($failed > 0) {
    exit(1);
}
exit(0);

