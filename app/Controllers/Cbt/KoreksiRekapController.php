<?php

declare(strict_types=1);

namespace App\Controllers\Cbt;

use App\Core\Controller;
use App\Models\CbtBankSoal;
use App\Models\CbtJadwal;
use App\Models\CbtNilai;
use App\Models\CbtSoal;
use App\Models\CbtSoalSiswa;
use App\Models\MasterKelas;
use App\Models\MasterSiswa;
use App\Services\AutoGraderService;
use App\Services\ExcelExportService;

class KoreksiRekapController extends Controller
{
    private CbtJadwal $jadwalModel;
    private CbtBankSoal $bankModel;
    private CbtNilai $nilaiModel;
    private CbtSoal $soalModel;
    private CbtSoalSiswa $soalSiswaModel;
    private MasterKelas $kelasModel;
    private MasterSiswa $siswaModel;
    private AutoGraderService $grader;
    private ExcelExportService $excelExporter;

    public function __construct(?\App\Core\Request $request = null, ?\App\Core\Response $response = null)
    {
        parent::__construct($request, $response);
        $this->jadwalModel = new CbtJadwal();
        $this->bankModel = new CbtBankSoal();
        $this->nilaiModel = new CbtNilai();
        $this->soalModel = new CbtSoal();
        $this->soalSiswaModel = new CbtSoalSiswa();
        $this->kelasModel = new MasterKelas();
        $this->siswaModel = new MasterSiswa();
        $this->grader = new AutoGraderService();
        $this->excelExporter = new ExcelExportService();
    }

    /**
     * List schedules for manual correction of essay questions.
     */
    public function koreksiIndex(): void
    {
        $tp = active_tp();
        $smt = active_smt();
        $idTp = (int)($tp['id_tp'] ?? 1);
        $idSmt = (int)($smt['id_smt'] ?? 1);

        $schedules = $this->jadwalModel->getAllByFilter($idTp, $idSmt);

        $this->render('cbt/koreksi/index', [
            'title' => 'Koreksi Ujian CBT — DADO CBT',
            'schedules' => $schedules,
            'tp' => $tp,
            'smt' => $smt,
        ]);
    }

    /**
     * Student list under a specific schedule for manual correction.
     */
    public function koreksiPeserta(int $idJadwal): void
    {
        $jadwal = $this->jadwalModel->getDetail($idJadwal);
        if (!$jadwal) {
            set_flash('error', 'Jadwal ujian tidak ditemukan.');
            $this->redirect('/cbt/koreksi');
            return;
        }

        $students = $this->nilaiModel->getRecapBySchedule($idJadwal);

        $this->render('cbt/koreksi/peserta', [
            'title' => 'Daftar Siswa Koreksi: ' . ($jadwal['bank_kode'] ?? 'Ujian') . ' — DADO CBT',
            'jadwal' => $jadwal,
            'students' => $students,
        ]);
    }

    /**
     * Detail question-by-question correction for a student.
     */
    public function koreksiDetail(int $idJadwal, int $idSiswa): void
    {
        $jadwal = $this->jadwalModel->getDetail($idJadwal);
        $siswa = $this->siswaModel->find($idSiswa);
        if (!$jadwal || !$siswa) {
            set_flash('error', 'Data tidak ditemukan.');
            $this->redirect('/cbt/koreksi');
            return;
        }

        $questions = $this->soalSiswaModel->getStudentQuestions($idJadwal, $idSiswa);
        $nilai = $this->nilaiModel->getScore($idJadwal, $idSiswa);

        $this->render('cbt/koreksi/detail', [
            'title' => 'Koreksi Lembar Jawaban: ' . ($siswa['nama'] ?? 'Siswa') . ' — DADO CBT',
            'jadwal' => $jadwal,
            'siswa' => $siswa,
            'questions' => $questions,
            'nilai' => $nilai,
        ]);
    }

    /**
     * Save manual scores (nilai_koreksi) for essay and trigger auto score recalculation.
     */
    public function saveKoreksi(int $idJadwal, int $idSiswa): void
    {
        $koreksiPoints = $this->request->input('nilai_koreksi', []);

        if (is_array($koreksiPoints)) {
            foreach ($koreksiPoints as $idSoalSiswa => $point) {
                $idSoalSiswa = (int)$idSoalSiswa;
                $point = max(0.00, min(100.00, (float)$point));
                $this->soalSiswaModel->update($idSoalSiswa, [
                    'nilai_koreksi' => $point,
                    'nilai_otomatis' => 1, // Marked as manually corrected
                ]);
            }
        }

        // Recompute total score via AutoGraderService
        $this->grader->gradeExam($idJadwal, $idSiswa);

        set_flash('success', 'Nilai koreksi esai berhasil disimpan dan total skor telah diperbarui.');
        $this->redirect('/cbt/koreksi/detail/' . $idJadwal . '/' . $idSiswa);
    }

    /**
     * Question item analysis (Analisis Butir Soal).
     */
    public function analisisSoal(int $idJadwal): void
    {
        $jadwal = $this->jadwalModel->getDetail($idJadwal);
        if (!$jadwal) {
            set_flash('error', 'Jadwal ujian tidak ditemukan.');
            $this->redirect('/cbt/jadwal');
            return;
        }

        $idBank = (int)$jadwal['id_bank'];
        $questions = $this->soalModel->getByBank($idBank);

        // Calculate statistics for each question
        $analysis = [];
        $totalPeserta = (int)($this->nilaiModel->query(
            "SELECT COUNT(DISTINCT id_siswa) as count FROM cbt_soal_siswa WHERE id_jadwal = ?",
            [$idJadwal]
        )[0]['count'] ?? 0);

        foreach ($questions as $q) {
            $idSoal = (int)$q['id_soal'];
            $stats = $this->soalSiswaModel->query(
                "SELECT 
                    COUNT(*) as total_responden,
                    SUM(CASE WHEN point_soal >= 1.00 THEN 1 ELSE 0 END) as total_benar,
                    AVG(point_soal) as avg_point
                 FROM cbt_soal_siswa 
                 WHERE id_jadwal = ? AND id_soal = ?",
                [$idJadwal, $idSoal]
            );

            $stat = $stats[0] ?? ['total_responden' => 0, 'total_benar' => 0, 'avg_point' => 0];
            $responden = (int)$stat['total_responden'];
            $benar = (int)$stat['total_benar'];
            $tingkatKetercapaian = $responden > 0 ? round(($benar / $responden) * 100, 1) : 0.0;

            $kategoriKesulitan = 'Sedang';
            if ($tingkatKetercapaian >= 75) {
                $kategoriKesulitan = 'Mudah';
            } elseif ($tingkatKetercapaian < 30) {
                $kategoriKesulitan = 'Sukar';
            }

            $analysis[] = [
                'soal' => $q,
                'total_responden' => $responden,
                'total_benar' => $benar,
                'avg_point' => round((float)$stat['avg_point'], 2),
                'ketercapaian' => $tingkatKetercapaian,
                'kategori' => $kategoriKesulitan,
            ];
        }

        $this->render('cbt/rekap/analisis', [
            'title' => 'Analisis Butir Soal: ' . ($jadwal['bank_kode'] ?? 'Ujian') . ' — DADO CBT',
            'jadwal' => $jadwal,
            'analysis' => $analysis,
            'totalPeserta' => $totalPeserta,
        ]);
    }

    /**
     * Rekap Nilai Ujian page with Search and Class Filter.
     */
    public function rekapNilai(): void
    {
        $tp = active_tp();
        $smt = active_smt();
        $idTp = (int)($tp['id_tp'] ?? 1);
        $idSmt = (int)($smt['id_smt'] ?? 1);

        $jadwalList = $this->jadwalModel->getAllByFilter($idTp, $idSmt);
        $kelasList = $this->kelasModel->getByPeriod($idTp, $idSmt);

        $selectedJadwal = (int)$this->request->query('jadwal_id', 0);
        if ($selectedJadwal <= 0 && !empty($jadwalList)) {
            $selectedJadwal = (int)$jadwalList[0]['id_jadwal'];
        }

        $selectedKelas = (int)$this->request->query('kelas_id', 0);
        $search = trim((string)$this->request->query('search', ''));

        $currentJadwal = $selectedJadwal > 0 ? $this->jadwalModel->getDetail($selectedJadwal) : null;
        $scores = [];

        if ($currentJadwal) {
            $scores = $this->nilaiModel->getRecapBySchedule($selectedJadwal, $selectedKelas > 0 ? $selectedKelas : null, $search);
        }

        $this->render('cbt/rekap/index', [
            'title' => 'Rekap Nilai Ujian CBT — DADO CBT',
            'jadwalList' => $jadwalList,
            'currentJadwal' => $currentJadwal,
            'kelasList' => $kelasList,
            'scores' => $scores,
            'selectedJadwal' => $selectedJadwal,
            'selectedKelas' => $selectedKelas,
            'search' => $search,
        ]);
    }

    /**
     * Katrol / Konversi Nilai Ujian (PRD 4.4 Formula).
     */
    public function katrolNilai(int $idJadwal): void
    {
        $tMin = (float)$this->request->input('t_min', 70.0);
        $tMax = (float)$this->request->input('t_max', 95.0);

        if ($tMin >= $tMax) {
            set_flash('error', 'Target nilai minimum harus lebih kecil daripada target nilai maksimum.');
            $this->redirect('/cbt/rekap-nilai?jadwal_id=' . $idJadwal);
            return;
        }

        $count = $this->grader->applyKatrol($idJadwal, $tMin, $tMax);

        set_flash('success', "Konversi rentang nilai berhasil diterapkan ke {$count} peserta ujian (Target [{$tMin} - {$tMax}]).");
        $this->redirect('/cbt/rekap-nilai?jadwal_id=' . $idJadwal);
    }

    /**
     * Export dynamic Excel / CSV of exam results.
     */
    public function exportExcel(int $idJadwal): void
    {
        $jadwal = $this->jadwalModel->getDetail($idJadwal);
        if (!$jadwal) {
            set_flash('error', 'Jadwal ujian tidak ditemukan.');
            $this->redirect('/cbt/rekap-nilai');
            return;
        }

        $scores = $this->nilaiModel->getRecapBySchedule($idJadwal);
        $this->excelExporter->exportExamRecap($jadwal, $scores);
    }
}

