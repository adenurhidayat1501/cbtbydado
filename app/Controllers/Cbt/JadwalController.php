<?php

namespace App\Controllers\Cbt;

use App\Core\Controller;
use App\Models\CbtJadwal;
use App\Models\CbtBankSoal;
use App\Models\CbtJenis;
use App\Models\CbtRuang;
use App\Models\CbtSesi;
use App\Models\CbtPengawas;
use App\Models\MasterGuru;

class JadwalController extends Controller
{
    private CbtJadwal $jadwalModel;
    private CbtBankSoal $bankModel;
    private CbtJenis $jenisModel;
    private CbtRuang $ruangModel;
    private CbtSesi $sesiModel;
    private CbtPengawas $pengawasModel;
    private MasterGuru $guruModel;

    public function __construct(?\App\Core\Request $request = null, ?\App\Core\Response $response = null)
    {
        parent::__construct($request, $response);
        $this->jadwalModel = new CbtJadwal();
        $this->bankModel = new CbtBankSoal();
        $this->jenisModel = new CbtJenis();
        $this->ruangModel = new CbtRuang();
        $this->sesiModel = new CbtSesi();
        $this->pengawasModel = new CbtPengawas();
        $this->guruModel = new MasterGuru();
    }

    /**
     * Display list of exam schedules and time allocation (Jam Ke-1, Ke-2, dst.).
     */
    public function index(): void
    {
        $tp = active_tp();
        $smt = active_smt();
        $idTp = (int)($tp['id_tp'] ?? 1);
        $idSmt = (int)($smt['id_smt'] ?? 1);

        $schedules = $this->jadwalModel->getAllByFilter($idTp, $idSmt);
        $jenisList = $this->jenisModel->getAll();

        $this->render('cbt/jadwal/index', [
            'title' => 'Jadwal Ujian CBT — DADO CBT',
            'tp' => $tp,
            'smt' => $smt,
            'schedules' => $schedules,
            'jenisList' => $jenisList,
            'role' => auth_role(),
        ]);
    }

    /**
     * Show form to create new exam schedule.
     */
    public function create(): void
    {
        $tp = active_tp();
        $smt = active_smt();
        $idTp = (int)($tp['id_tp'] ?? 1);
        $idSmt = (int)($smt['id_smt'] ?? 1);

        $bankList = $this->bankModel->getAllByFilter($idTp, $idSmt);
        $jenisList = $this->jenisModel->getAll();

        $this->render('cbt/jadwal/create', [
            'title' => 'Tambah Jadwal Ujian — DADO CBT',
            'tp' => $tp,
            'smt' => $smt,
            'bankList' => $bankList,
            'jenisList' => $jenisList,
        ]);
    }

    /**
     * Store new exam schedule.
     */
    public function store(): void
    {
        $tp = active_tp();
        $smt = active_smt();
        $idTp = (int)($tp['id_tp'] ?? 1);
        $idSmt = (int)($smt['id_smt'] ?? 1);

        $idBank = (int)$this->request->input('id_bank', 0);
        $idJenis = (int)$this->request->input('id_jenis', 0);
        $tglMulai = trim((string)$this->request->input('tgl_mulai', ''));
        $tglSelesai = trim((string)$this->request->input('tgl_selesai', ''));
        $durasi = (int)$this->request->input('durasi_ujian', 90);
        $jamKe = (int)$this->request->input('jam_ke', 1);
        $jarak = (int)$this->request->input('jarak', 0);

        if ($idBank <= 0 || $idJenis <= 0 || empty($tglMulai) || empty($tglSelesai) || $durasi <= 0) {
            set_flash('error', 'Semua bidang bertanda bintang wajib diisi.');
            $this->redirect('/cbt/jadwal/create');
            return;
        }

        $payload = [
            'id_tp' => $idTp,
            'id_smt' => $idSmt,
            'id_bank' => $idBank,
            'id_jenis' => $idJenis,
            'tgl_mulai' => $tglMulai,
            'tgl_selesai' => $tglSelesai,
            'durasi_ujian' => $durasi,
            'jam_ke' => $jamKe,
            'jarak' => $jarak,
            'acak_soal' => (int)$this->request->input('acak_soal', 1),
            'acak_opsi' => (int)$this->request->input('acak_opsi', 1),
            'hasil_tampil' => (int)$this->request->input('hasil_tampil', 0),
            'token' => (int)$this->request->input('token', 1),
            'status' => (int)$this->request->input('status', 1),
            'ulang' => (int)$this->request->input('ulang', 0),
            'reset_login' => (int)$this->request->input('reset_login', 0),
        ];

        $insertedId = $this->jadwalModel->insert($payload);

        if ($insertedId) {
            set_flash('success', 'Jadwal ujian berhasil ditambahkan.');
            $this->redirect('/cbt/jadwal');
        } else {
            set_flash('error', 'Gagal menyimpan jadwal ujian.');
            $this->redirect('/cbt/jadwal/create');
        }
    }

    /**
     * Show form to edit exam schedule.
     */
    public function edit(int $id): void
    {
        $jadwal = $this->jadwalModel->getDetail($id);
        if (!$jadwal) {
            set_flash('error', 'Jadwal ujian tidak ditemukan.');
            $this->redirect('/cbt/jadwal');
            return;
        }

        $tp = active_tp();
        $smt = active_smt();
        $idTp = (int)($tp['id_tp'] ?? 1);
        $idSmt = (int)($smt['id_smt'] ?? 1);

        $bankList = $this->bankModel->getAllByFilter($idTp, $idSmt);
        $jenisList = $this->jenisModel->getAll();
        $isOngoing = $this->jadwalModel->isExamOngoing($id);

        $this->render('cbt/jadwal/edit', [
            'title' => 'Edit Jadwal Ujian — DADO CBT',
            'jadwal' => $jadwal,
            'bankList' => $bankList,
            'jenisList' => $jenisList,
            'isOngoing' => $isOngoing,
        ]);
    }

    /**
     * Update exam schedule.
     */
    public function update(int $id): void
    {
        $jadwal = $this->jadwalModel->find($id);
        if (!$jadwal) {
            set_flash('error', 'Jadwal ujian tidak ditemukan.');
            $this->redirect('/cbt/jadwal');
            return;
        }

        $payload = [
            'id_bank' => (int)$this->request->input('id_bank', $jadwal['id_bank']),
            'id_jenis' => (int)$this->request->input('id_jenis', $jadwal['id_jenis']),
            'tgl_mulai' => trim((string)$this->request->input('tgl_mulai', $jadwal['tgl_mulai'])),
            'tgl_selesai' => trim((string)$this->request->input('tgl_selesai', $jadwal['tgl_selesai'])),
            'durasi_ujian' => (int)$this->request->input('durasi_ujian', $jadwal['durasi_ujian']),
            'jam_ke' => (int)$this->request->input('jam_ke', $jadwal['jam_ke']),
            'jarak' => (int)$this->request->input('jarak', $jadwal['jarak']),
            'acak_soal' => (int)$this->request->input('acak_soal', 1),
            'acak_opsi' => (int)$this->request->input('acak_opsi', 1),
            'hasil_tampil' => (int)$this->request->input('hasil_tampil', 0),
            'token' => (int)$this->request->input('token', 1),
            'status' => (int)$this->request->input('status', 1),
            'ulang' => (int)$this->request->input('ulang', 0),
            'reset_login' => (int)$this->request->input('reset_login', 0),
        ];

        $this->jadwalModel->update($id, $payload);
        set_flash('success', 'Jadwal ujian berhasil diperbarui.');
        $this->redirect('/cbt/jadwal');
    }

    /**
     * Delete exam schedule with ongoing protection.
     */
    public function delete(int $id): void
    {
        $jadwal = $this->jadwalModel->find($id);
        if (!$jadwal) {
            set_flash('error', 'Jadwal ujian tidak ditemukan.');
            $this->redirect('/cbt/jadwal');
            return;
        }

        if ($this->jadwalModel->isExamOngoing($id)) {
            set_flash('error', 'Gagal: Jadwal ujian sedang berlangsung dengan peserta aktif.');
            $this->redirect('/cbt/jadwal');
            return;
        }

        $this->jadwalModel->delete($id);
        set_flash('success', 'Jadwal ujian berhasil dihapus.');
        $this->redirect('/cbt/jadwal');
    }

    /**
     * Manage proctors mapping per Ruang & Sesi for an exam schedule.
     */
    public function pengawas(int $id): void
    {
        $jadwal = $this->jadwalModel->getDetail($id);
        if (!$jadwal) {
            set_flash('error', 'Jadwal ujian tidak ditemukan.');
            $this->redirect('/cbt/jadwal');
            return;
        }

        $ruangList = $this->ruangModel->getAll();
        $sesiList = $this->sesiModel->getAll();
        $guruList = $this->guruModel->where(['status' => 1], 'nama_guru ASC');

        $matrix = $this->pengawasModel->getPengawasMatrix($id);

        $this->render('cbt/jadwal/pengawas', [
            'title' => 'Atur Pengawas Ujian — DADO CBT',
            'jadwal' => $jadwal,
            'ruangList' => $ruangList,
            'sesiList' => $sesiList,
            'guruList' => $guruList,
            'matrix' => $matrix,
        ]);
    }

    /**
     * Save proctor assignments for a room and session.
     */
    public function savePengawas(int $id): void
    {
        $tp = active_tp();
        $smt = active_smt();
        $idTp = (int)($tp['id_tp'] ?? 1);
        $idSmt = (int)($smt['id_smt'] ?? 1);

        $idRuang = (int)$this->request->input('id_ruang', 0);
        $idSesi = (int)$this->request->input('id_sesi', 0);
        $guruIds = $this->request->input('guru_ids', []);

        if ($idRuang <= 0 || $idSesi <= 0) {
            set_flash('error', 'Ruang dan sesi wajib dipilih.');
            $this->redirect('/cbt/jadwal/pengawas/' . $id);
            return;
        }

        $saved = $this->pengawasModel->savePengawas($idTp, $idSmt, $id, $idRuang, $idSesi, (array)$guruIds);

        if ($saved) {
            set_flash('success', 'Pemetaan pengawas berhasil disimpan.');
        } else {
            set_flash('error', 'Gagal menyimpan pengawas.');
        }

        $this->redirect('/cbt/jadwal/pengawas/' . $id);
    }

    /**
     * Dedicated view for a logged-in teacher to access assigned monitoring room.
     */
    public function guruPengawas(): void
    {
        $guru = auth_guru();
        if (!$guru) {
            set_flash('error', 'Profil guru tidak ditemukan.');
            $this->redirect('/guru/dashboard');
            return;
        }

        $tp = active_tp();
        $smt = active_smt();
        $idTp = (int)($tp['id_tp'] ?? 1);
        $idSmt = (int)($smt['id_smt'] ?? 1);

        $idGuru = (int)$guru['id_guru'];
        $monitoringSchedules = $this->pengawasModel->getSchedulesForPengawas($idTp, $idSmt, $idGuru);

        $this->render('cbt/jadwal/guru_pengawas', [
            'title' => 'Ruang Pengawas Ujian — DADO CBT',
            'guru' => $guru,
            'tp' => $tp,
            'smt' => $smt,
            'monitoringSchedules' => $monitoringSchedules,
        ]);
    }
}
