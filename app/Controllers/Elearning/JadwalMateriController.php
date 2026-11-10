<?php

namespace App\Controllers\Elearning;

use App\Core\Controller;
use App\Models\KelasJadwalMateri;
use App\Models\KelasMateri;
use App\Models\MasterKelas;
use App\Models\MasterMapel;
use App\Models\MasterGuru;

class JadwalMateriController extends Controller
{
    private KelasJadwalMateri $kjmModel;
    private KelasMateri $materiModel;
    private MasterKelas $kelasModel;
    private MasterMapel $mapelModel;
    private MasterGuru $guruModel;

    public function __construct(?\App\Core\Request $request = null, ?\App\Core\Response $response = null)
    {
        parent::__construct($request, $response);
        $this->kjmModel = new KelasJadwalMateri();
        $this->materiModel = new KelasMateri();
        $this->kelasModel = new MasterKelas();
        $this->mapelModel = new MasterMapel();
        $this->guruModel = new MasterGuru();
    }

    /**
     * Display list of scheduled materials and assignments.
     */
    public function index(): void
    {
        $tp = active_tp();
        $smt = active_smt();
        $idTp = (int)($tp['id_tp'] ?? 1);
        $idSmt = (int)($smt['id_smt'] ?? 1);

        $role = auth_role();
        $currentGuru = auth_guru();
        $filterGuruId = null;

        if ($role === 'guru') {
            if (!$currentGuru) {
                set_flash('error', 'Profil guru tidak ditemukan.');
                $this->redirect('/dashboard');
                return;
            }
            $filterGuruId = (int)$currentGuru['id_guru'];
        }

        $idKelas = (int)$this->request->query('kelas_id', 0) ?: null;
        $idMapel = (int)$this->request->query('mapel_id', 0) ?: null;
        $date = trim((string)$this->request->query('date', '')) ?: null;
        $jenisFilter = $this->request->query('jenis');
        $jenis = ($jenisFilter !== null && $jenisFilter !== '') ? (int)$jenisFilter : null;

        $schedules = $this->kjmModel->getSchedules($idTp, $idSmt, $idKelas, $idMapel, $date, $jenis, $filterGuruId);

        $kelasList = $this->kelasModel->getByPeriod($idTp, $idSmt);
        if (empty($kelasList)) {
            $kelasList = $this->kelasModel->all('level_id ASC, nama_kelas ASC');
        }

        $mapelList = $this->mapelModel->where(['status' => 1], 'nama_mapel ASC');

        // Available materials/tasks for scheduling
        $availableMateri = $this->materiModel->getAllByFilter($idTp, $idSmt, $filterGuruId, null, null, null);

        $this->render('elearning/jadwal_materi/index', [
            'title' => 'Jadwal Tayang Materi / Tugas — DADO CBT',
            'tp' => $tp,
            'smt' => $smt,
            'schedules' => $schedules,
            'kelasList' => $kelasList,
            'mapelList' => $mapelList,
            'availableMateri' => $availableMateri,
            'selectedKelasId' => $idKelas,
            'selectedMapelId' => $idMapel,
            'selectedDate' => $date,
            'selectedJenis' => $jenis,
            'role' => $role,
        ]);
    }

    /**
     * Schedule a material or assignment to one or more classes on a specific date.
     */
    public function store(): void
    {
        $tp = active_tp();
        $smt = active_smt();
        $idTp = (int)($tp['id_tp'] ?? 1);
        $idSmt = (int)($smt['id_smt'] ?? 1);

        $idMateri = (int)$this->request->input('id_materi', 0);
        $jadwalDate = trim((string)$this->request->input('jadwal_materi', ''));
        $targetKelas = $this->request->input('id_kelas', []);

        if (!is_array($targetKelas)) {
            $targetKelas = !empty($targetKelas) ? [(int)$targetKelas] : [];
        }

        if ($idMateri <= 0 || empty($jadwalDate) || empty($targetKelas)) {
            set_flash('error', 'Pilih materi/tugas, tanggal tayang, dan minimal 1 kelas tujuan.');
            $this->redirect('/elearning/jadwal-materi');
            return;
        }

        $materi = $this->materiModel->find($idMateri);
        if (!$materi) {
            set_flash('error', 'Materi/Tugas tidak ditemukan.');
            $this->redirect('/elearning/jadwal-materi');
            return;
        }

        $role = auth_role();
        $currentGuru = auth_guru();
        if ($role === 'guru') {
            if (!$currentGuru || (int)$materi['id_guru'] !== (int)$currentGuru['id_guru']) {
                set_flash('error', 'Akses Ditolak: Anda hanya boleh menjadwalkan materi/tugas milik sendiri.');
                $this->redirect('/elearning/jadwal-materi');
                return;
            }
        }

        $success = $this->kjmModel->scheduleMateri($idTp, $idSmt, $idMateri, $targetKelas, $jadwalDate);

        if ($success) {
            set_flash('success', 'Jadwal tayang materi/tugas berhasil disimpan.');
        } else {
            set_flash('error', 'Gagal menyimpan jadwal tayang.');
        }

        $this->redirect('/elearning/jadwal-materi');
    }

    /**
     * Delete a schedule entry.
     */
    public function delete(int $id): void
    {
        $schedule = $this->kjmModel->getDetail($id);
        if (!$schedule) {
            set_flash('error', 'Jadwal materi tidak ditemukan.');
            $this->redirect('/elearning/jadwal-materi');
            return;
        }

        $role = auth_role();
        $currentGuru = auth_guru();
        if ($role === 'guru') {
            if (!$currentGuru || (int)$schedule['id_guru'] !== (int)$currentGuru['id_guru']) {
                set_flash('error', 'Akses Ditolak: Anda tidak diizinkan menghapus jadwal ini.');
                $this->redirect('/elearning/jadwal-materi');
                return;
            }
        }

        $deleted = $this->kjmModel->delete($id);

        if ($deleted) {
            set_flash('success', 'Jadwal materi/tugas berhasil dihapus.');
        } else {
            set_flash('error', 'Gagal menghapus jadwal.');
        }

        $this->redirect('/elearning/jadwal-materi');
    }
}
