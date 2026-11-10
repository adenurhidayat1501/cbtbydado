<?php

namespace App\Controllers\Elearning;

use App\Core\Controller;
use App\Models\KelasJadwalKbm;
use App\Models\KelasJadwalMapel;
use App\Models\MasterKelas;
use App\Models\MasterMapel;
use App\Models\MasterGuru;

class JadwalKbmController extends Controller
{
    private KelasJadwalKbm $kbmModel;
    private KelasJadwalMapel $jadwalModel;
    private MasterKelas $kelasModel;
    private MasterMapel $mapelModel;
    private MasterGuru $guruModel;

    public function __construct(?\App\Core\Request $request = null, ?\App\Core\Response $response = null)
    {
        parent::__construct($request, $response);
        $this->kbmModel = new KelasJadwalKbm();
        $this->jadwalModel = new KelasJadwalMapel();
        $this->kelasModel = new MasterKelas();
        $this->mapelModel = new MasterMapel();
        $this->guruModel = new MasterGuru();
    }

    /**
     * Display KBM Settings and Weekly Schedule Matrix per class.
     */
    public function index(): void
    {
        $tp = active_tp();
        $smt = active_smt();
        $idTp = (int)($tp['id_tp'] ?? 1);
        $idSmt = (int)($smt['id_smt'] ?? 1);

        $kelasList = $this->kelasModel->getByPeriod($idTp, $idSmt);
        if (empty($kelasList)) {
            $kelasList = $this->kelasModel->all('level_id ASC, nama_kelas ASC');
        }

        $selectedKelasId = (int)$this->request->query('kelas_id', 0);
        if ($selectedKelasId <= 0 && !empty($kelasList)) {
            $selectedKelasId = (int)$kelasList[0]['id_kelas'];
        }

        $setting = $this->kbmModel->getSetting($idTp, $idSmt, $selectedKelasId);
        $timeSlots = $this->kbmModel->getTimeSlots($setting);
        $matrix = $this->jadwalModel->getMatrix($idTp, $idSmt, $selectedKelasId);

        $mapelList = $this->mapelModel->where(['status' => 1], 'nama_mapel ASC');
        $guruList = $this->guruModel->where(['status' => 1], 'nama_guru ASC');

        $this->render('elearning/jadwal_kbm/index', [
            'title' => 'Jadwal Pelajaran KBM — DADO CBT',
            'tp' => $tp,
            'smt' => $smt,
            'kelasList' => $kelasList,
            'selectedKelasId' => $selectedKelasId,
            'setting' => $setting,
            'timeSlots' => $timeSlots,
            'matrix' => $matrix,
            'mapelList' => $mapelList,
            'guruList' => $guruList,
            'days' => [
                1 => 'Senin',
                2 => 'Selasa',
                3 => 'Rabu',
                4 => 'Kamis',
                5 => 'Jumat',
                6 => 'Sabtu',
            ],
        ]);
    }

    /**
     * Save KBM duration, start time, periods per day, and break intervals.
     */
    public function saveSettings(): void
    {
        $tp = active_tp();
        $smt = active_smt();
        $idTp = (int)($tp['id_tp'] ?? 1);
        $idSmt = (int)($smt['id_smt'] ?? 1);

        $idKelas = (int)$this->request->input('id_kelas', 0);
        if ($idKelas <= 0) {
            set_flash('error', 'Kelas belum dipilih.');
            $this->redirect('/elearning/jadwal-kbm');
            return;
        }

        $jamPel = (int)$this->request->input('kbm_jam_pel', 40);
        $jamMulai = trim((string)$this->request->input('kbm_jam_mulai', '07:00'));
        $jmlMapelHari = (int)$this->request->input('kbm_jml_mapel_hari', 6);

        // Process istirahat JSON
        $ist1Mulai = trim((string)$this->request->input('ist1_mulai', ''));
        $ist1Durasi = (int)$this->request->input('ist1_durasi', 0);
        $ist2Mulai = trim((string)$this->request->input('ist2_mulai', ''));
        $ist2Durasi = (int)$this->request->input('ist2_durasi', 0);

        $istirahat = [];
        if (!empty($ist1Mulai) && $ist1Durasi > 0) {
            $istirahat[] = ['ist' => 1, 'mulai' => $ist1Mulai, 'durasi' => $ist1Durasi];
        }
        if (!empty($ist2Mulai) && $ist2Durasi > 0) {
            $istirahat[] = ['ist' => 2, 'mulai' => $ist2Mulai, 'durasi' => $ist2Durasi];
        }

        $success = $this->kbmModel->saveSetting($idTp, $idSmt, $idKelas, [
            'kbm_jam_pel' => $jamPel,
            'kbm_jam_mulai' => $jamMulai,
            'kbm_jml_mapel_hari' => $jmlMapelHari,
            'istirahat' => $istirahat,
        ]);

        if ($success) {
            set_flash('success', 'Pengaturan KBM berhasil disimpan.');
        } else {
            set_flash('error', 'Gagal menyimpan pengaturan KBM.');
        }

        $this->redirect('/elearning/jadwal-kbm?kelas_id=' . $idKelas);
    }

    /**
     * AJAX endpoint to save or clear a schedule matrix cell.
     */
    public function saveCell(): void
    {
        $tp = active_tp();
        $smt = active_smt();
        $idTp = (int)($tp['id_tp'] ?? 1);
        $idSmt = (int)($smt['id_smt'] ?? 1);

        $idKelas = (int)$this->request->input('id_kelas', 0);
        $idHari = (int)$this->request->input('id_hari', 0);
        $jamKe = (int)$this->request->input('jam_ke', 0);
        $idMapel = (int)$this->request->input('id_mapel', 0);
        $idGuru = (int)$this->request->input('id_guru', 0);

        if ($idKelas <= 0 || $idHari < 1 || $idHari > 7 || $jamKe <= 0) {
            $this->response->json(['status' => 'error', 'message' => 'Parameter jadwal tidak valid.'], 400);
            return;
        }

        $saved = $this->jadwalModel->saveCell(
            $idTp,
            $idSmt,
            $idKelas,
            $idHari,
            $jamKe,
            $idMapel > 0 ? $idMapel : null,
            $idGuru > 0 ? $idGuru : null
        );

        if ($saved) {
            $this->response->json(['status' => 'success', 'message' => 'Jadwal berhasil diperbarui!']);
        } else {
            $this->response->json(['status' => 'error', 'message' => 'Gagal memperbarui jadwal.'], 500);
        }
    }

    /**
     * Dedicated view for a teacher to view their own teaching schedule.
     */
    public function guruJadwal(): void
    {
        $tp = active_tp();
        $smt = active_smt();
        $idTp = (int)($tp['id_tp'] ?? 1);
        $idSmt = (int)($smt['id_smt'] ?? 1);

        $guru = auth_guru();
        if (!$guru) {
            set_flash('error', 'Data profil guru tidak ditemukan.');
            $this->redirect('/dashboard');
            return;
        }

        $idGuru = (int)$guru['id_guru'];
        $schedules = $this->jadwalModel->getJadwalForGuru($idTp, $idSmt, $idGuru);

        // Group schedules by day (1-6)
        $grouped = [];
        for ($i = 1; $i <= 6; $i++) {
            $grouped[$i] = [];
        }
        foreach ($schedules as $s) {
            $grouped[(int)$s['id_hari']][] = $s;
        }

        $this->render('elearning/jadwal_kbm/guru', [
            'title' => 'Jadwal Mengajar Saya — DADO CBT',
            'tp' => $tp,
            'smt' => $smt,
            'guru' => $guru,
            'groupedSchedules' => $grouped,
            'days' => [
                1 => 'Senin',
                2 => 'Selasa',
                3 => 'Rabu',
                4 => 'Kamis',
                5 => 'Jumat',
                6 => 'Sabtu',
            ],
        ]);
    }
}
