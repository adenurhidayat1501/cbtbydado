<?php

namespace App\Controllers\Elearning;

use App\Core\Controller;
use App\Models\KelasJadwalMateri;
use App\Models\LogMateri;
use App\Models\MasterKelas;
use App\Models\MasterMapel;
use App\Services\ExcelExportService;

class PresensiNilaiController extends Controller
{
    private KelasJadwalMateri $kjmModel;
    private LogMateri $logModel;
    private MasterKelas $kelasModel;
    private MasterMapel $mapelModel;
    private ExcelExportService $excelService;

    public function __construct(?\App\Core\Request $request = null, ?\App\Core\Response $response = null)
    {
        parent::__construct($request, $response);
        $this->kjmModel = new KelasJadwalMateri();
        $this->logModel = new LogMateri();
        $this->kelasModel = new MasterKelas();
        $this->mapelModel = new MasterMapel();
        $this->excelService = new ExcelExportService();
    }

    /**
     * Overview of assignments and attendance sessions.
     */
    public function index(): void
    {
        $tp = active_tp();
        $smt = active_smt();
        $idTp = (int)($tp['id_tp'] ?? 1);
        $idSmt = (int)($smt['id_smt'] ?? 1);

        $role = auth_role();
        $currentGuru = auth_guru();
        $filterGuruId = ($role === 'guru' && $currentGuru) ? (int)$currentGuru['id_guru'] : null;

        $idKelas = (int)$this->request->query('kelas_id', 0) ?: null;
        $idMapel = (int)$this->request->query('mapel_id', 0) ?: null;

        // Fetch tasks
        $tasks = $this->kjmModel->getSchedules($idTp, $idSmt, $idKelas, $idMapel, null, 2, $filterGuruId);

        $kelasList = $this->kelasModel->getByPeriod($idTp, $idSmt);
        if (empty($kelasList)) {
            $kelasList = $this->kelasModel->all('level_id ASC, nama_kelas ASC');
        }

        $mapelList = $this->mapelModel->where(['status' => 1], 'nama_mapel ASC');

        $this->render('elearning/nilai/index', [
            'title' => 'Presensi & Penilaian E-Learning — DADO CBT',
            'tp' => $tp,
            'smt' => $smt,
            'tasks' => $tasks,
            'kelasList' => $kelasList,
            'mapelList' => $mapelList,
            'selectedKelasId' => $idKelas,
            'selectedMapelId' => $idMapel,
            'role' => $role,
        ]);
    }

    /**
     * Detail student submissions for an assignment.
     */
    public function tugasSubmissions(int $idKjm): void
    {
        $schedule = $this->kjmModel->getDetail($idKjm);
        if (!$schedule) {
            set_flash('error', 'Jadwal tugas tidak ditemukan.');
            $this->redirect('/elearning/presensi-nilai');
            return;
        }

        $role = auth_role();
        $currentGuru = auth_guru();
        if ($role === 'guru') {
            if (!$currentGuru || (int)$schedule['id_guru'] !== (int)$currentGuru['id_guru']) {
                set_flash('error', 'Akses Ditolak: Anda tidak mengampu tugas ini.');
                $this->redirect('/elearning/presensi-nilai');
                return;
            }
        }

        $submissions = $this->logModel->getSubmissionsForSchedule($idKjm);

        $this->render('elearning/nilai/submissions', [
            'title' => 'Koreksi Tugas Siswa — DADO CBT',
            'schedule' => $schedule,
            'submissions' => $submissions,
            'role' => $role,
        ]);
    }

    /**
     * Input or update grade and feedback for a student assignment.
     */
    public function gradeSubmission(): void
    {
        $idLog = (int)$this->request->input('id_log', 0);
        $idSiswa = (int)$this->request->input('id_siswa', 0);
        $idKjm = (int)$this->request->input('id_kjm', 0);
        $nilai = (float)$this->request->input('nilai', 0);
        $catatan = trim((string)$this->request->input('catatan', ''));

        if ($idLog <= 0 && ($idSiswa > 0 && $idKjm > 0)) {
            // Create log record if not existed yet
            $this->logModel->insert([
                'id_siswa' => $idSiswa,
                'id_kjm' => $idKjm,
                'log_type' => 3,
                'log_time' => date('Y-m-d H:i:s'),
                'finish_time' => date('Y-m-d H:i:s'),
                'nilai' => max(0, min(100, $nilai)),
                'catatan' => !empty($catatan) ? $catatan : null,
            ]);

            if ($this->request->isAjax()) {
                $this->response->json(['status' => 'success', 'message' => 'Nilai berhasil disimpan.']);
                return;
            }
            set_flash('success', 'Nilai tugas berhasil disimpan.');
            $this->redirect('/elearning/presensi-nilai/submissions/' . $idKjm);
            return;
        }

        if ($idLog <= 0) {
            if ($this->request->isAjax()) {
                $this->response->json(['status' => 'error', 'message' => 'ID data tidak valid.'], 400);
                return;
            }
            set_flash('error', 'Data submission tidak valid.');
            $this->redirect('/elearning/presensi-nilai');
            return;
        }

        $graded = $this->logModel->gradeTugas($idLog, $nilai, $catatan);

        if ($this->request->isAjax()) {
            if ($graded) {
                $this->response->json(['status' => 'success', 'message' => 'Nilai berhasil diperbarui.']);
            } else {
                $this->response->json(['status' => 'error', 'message' => 'Gagal memperbarui nilai.'], 500);
            }
            return;
        }

        if ($graded) {
            set_flash('success', 'Nilai dan catatan revisi berhasil disimpan.');
        } else {
            set_flash('error', 'Gagal menyimpan nilai.');
        }

        $this->redirect('/elearning/presensi-nilai/submissions/' . $idKjm);
    }

    /**
     * Daily attendance recap view.
     */
    public function rekapHarian(): void
    {
        $tp = active_tp();
        $smt = active_smt();
        $idTp = (int)($tp['id_tp'] ?? 1);
        $idSmt = (int)($smt['id_smt'] ?? 1);

        $kelasList = $this->kelasModel->getByPeriod($idTp, $idSmt);
        if (empty($kelasList)) {
            $kelasList = $this->kelasModel->all('level_id ASC, nama_kelas ASC');
        }

        $mapelList = $this->mapelModel->where(['status' => 1], 'nama_mapel ASC');

        $selectedKelasId = (int)$this->request->query('kelas_id', 0);
        if ($selectedKelasId <= 0 && !empty($kelasList)) {
            $selectedKelasId = (int)$kelasList[0]['id_kelas'];
        }

        $selectedMapelId = (int)$this->request->query('mapel_id', 0);
        if ($selectedMapelId <= 0 && !empty($mapelList)) {
            $selectedMapelId = (int)$mapelList[0]['id_mapel'];
        }

        $selectedDate = trim((string)$this->request->query('date', date('Y-m-d')));

        $attendanceData = [];
        if ($selectedKelasId > 0 && $selectedMapelId > 0) {
            $attendanceData = $this->logModel->getRekapHarian($idTp, $idSmt, $selectedKelasId, $selectedMapelId, $selectedDate);
        }

        $this->render('elearning/nilai/rekap_harian', [
            'title' => 'Rekap Presensi Harian — DADO CBT',
            'tp' => $tp,
            'smt' => $smt,
            'kelasList' => $kelasList,
            'mapelList' => $mapelList,
            'selectedKelasId' => $selectedKelasId,
            'selectedMapelId' => $selectedMapelId,
            'selectedDate' => $selectedDate,
            'attendanceData' => $attendanceData,
        ]);
    }

    /**
     * Monthly attendance recap view.
     */
    public function rekapBulanan(): void
    {
        $tp = active_tp();
        $smt = active_smt();
        $idTp = (int)($tp['id_tp'] ?? 1);
        $idSmt = (int)($smt['id_smt'] ?? 1);

        $kelasList = $this->kelasModel->getByPeriod($idTp, $idSmt);
        if (empty($kelasList)) {
            $kelasList = $this->kelasModel->all('level_id ASC, nama_kelas ASC');
        }

        $mapelList = $this->mapelModel->where(['status' => 1], 'nama_mapel ASC');

        $selectedKelasId = (int)$this->request->query('kelas_id', 0);
        if ($selectedKelasId <= 0 && !empty($kelasList)) {
            $selectedKelasId = (int)$kelasList[0]['id_kelas'];
        }

        $selectedMapelId = (int)$this->request->query('mapel_id', 0);
        if ($selectedMapelId <= 0 && !empty($mapelList)) {
            $selectedMapelId = (int)$mapelList[0]['id_mapel'];
        }

        $selectedBulan = (int)$this->request->query('bulan', (int)date('n'));
        $selectedTahun = (int)$this->request->query('tahun', (int)date('Y'));

        $rekapResult = ['meetings' => [], 'students_rekap' => []];
        if ($selectedKelasId > 0 && $selectedMapelId > 0) {
            $rekapResult = $this->logModel->getRekapBulanan($idTp, $idSmt, $selectedKelasId, $selectedMapelId, $selectedBulan, $selectedTahun);
        }

        $months = [
            1 => 'Januari', 2 => 'Februari', 3 => 'Maret', 4 => 'April',
            5 => 'Mei', 6 => 'Juni', 7 => 'Juli', 8 => 'Agustus',
            9 => 'September', 10 => 'Oktober', 11 => 'November', 12 => 'Desember',
        ];

        $this->render('elearning/nilai/rekap_bulanan', [
            'title' => 'Rekap Presensi Bulanan — DADO CBT',
            'tp' => $tp,
            'smt' => $smt,
            'kelasList' => $kelasList,
            'mapelList' => $mapelList,
            'selectedKelasId' => $selectedKelasId,
            'selectedMapelId' => $selectedMapelId,
            'selectedBulan' => $selectedBulan,
            'selectedTahun' => $selectedTahun,
            'months' => $months,
            'meetings' => $rekapResult['meetings'],
            'studentsRekap' => $rekapResult['students_rekap'],
        ]);
    }

    /**
     * Export Assignment Grades to Excel.
     */
    public function exportNilai(): void
    {
        $tp = active_tp();
        $smt = active_smt();
        $idTp = (int)($tp['id_tp'] ?? 1);
        $idSmt = (int)($smt['id_smt'] ?? 1);

        $idKelas = (int)$this->request->query('kelas_id', 0);
        $idMapel = (int)$this->request->query('mapel_id', 0) ?: null;

        if ($idKelas <= 0) {
            set_flash('error', 'Pilih kelas terlebih dahulu untuk export nilai.');
            $this->redirect('/elearning/presensi-nilai');
            return;
        }

        $kelas = $this->kelasModel->find($idKelas);
        $mapel = $idMapel ? $this->mapelModel->find($idMapel) : null;

        $rekap = $this->logModel->getRekapNilaiTugas($idTp, $idSmt, $idKelas, $idMapel);

        $meta = [
            'tp' => $tp,
            'smt' => $smt,
            'kelas' => $kelas,
            'mapel' => $mapel,
        ];

        $this->excelService->exportElearningRecap($meta, $rekap['tasks'], $rekap['students_rekap']);
    }

    /**
     * Export daily attendance to Excel/CSV.
     */
    public function exportKehadiranHarian(): void
    {
        $tp = active_tp();
        $smt = active_smt();
        $idTp = (int)($tp['id_tp'] ?? 1);
        $idSmt = (int)($smt['id_smt'] ?? 1);

        $idKelas = (int)$this->request->query('kelas_id', 0);
        $idMapel = (int)$this->request->query('mapel_id', 0);
        $date = trim((string)$this->request->query('date', date('Y-m-d')));

        if ($idKelas <= 0 || $idMapel <= 0) {
            set_flash('error', 'Pilih kelas dan mapel untuk export presensi.');
            $this->redirect('/elearning/presensi-nilai/harian');
            return;
        }

        $kelas = $this->kelasModel->find($idKelas);
        $mapel = $this->mapelModel->find($idMapel);
        $attendanceData = $this->logModel->getRekapHarian($idTp, $idSmt, $idKelas, $idMapel, $date);

        $meta = [
            'kelas' => $kelas,
            'mapel' => $mapel,
            'date' => $date,
        ];

        $this->excelService->exportKehadiranHarian($meta, $attendanceData);
    }

    /**
     * Export monthly attendance to Excel/CSV.
     */
    public function exportKehadiranBulanan(): void
    {
        $tp = active_tp();
        $smt = active_smt();
        $idTp = (int)($tp['id_tp'] ?? 1);
        $idSmt = (int)($smt['id_smt'] ?? 1);

        $idKelas = (int)$this->request->query('kelas_id', 0);
        $idMapel = (int)$this->request->query('mapel_id', 0);
        $bulan = (int)$this->request->query('bulan', (int)date('n'));
        $tahun = (int)$this->request->query('tahun', (int)date('Y'));

        if ($idKelas <= 0 || $idMapel <= 0) {
            set_flash('error', 'Pilih kelas dan mapel untuk export presensi bulanan.');
            $this->redirect('/elearning/presensi-nilai/bulanan');
            return;
        }

        $kelas = $this->kelasModel->find($idKelas);
        $mapel = $this->mapelModel->find($idMapel);

        $months = [
            1 => 'Januari', 2 => 'Februari', 3 => 'Maret', 4 => 'April',
            5 => 'Mei', 6 => 'Juni', 7 => 'Juli', 8 => 'Agustus',
            9 => 'September', 10 => 'Oktober', 11 => 'November', 12 => 'Desember',
        ];

        $rekap = $this->logModel->getRekapBulanan($idTp, $idSmt, $idKelas, $idMapel, $bulan, $tahun);

        $meta = [
            'kelas' => $kelas,
            'mapel' => $mapel,
            'bulan' => $bulan,
            'tahun' => $tahun,
            'nama_bulan' => $months[$bulan] ?? $bulan,
        ];

        $this->excelService->exportKehadiranBulanan($meta, $rekap['meetings'], $rekap['students_rekap']);
    }
}
