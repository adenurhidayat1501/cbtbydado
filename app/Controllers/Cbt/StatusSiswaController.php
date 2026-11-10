<?php

declare(strict_types=1);

namespace App\Controllers\Cbt;

use App\Core\Controller;
use App\Models\CbtDurasiSiswa;
use App\Models\CbtJadwal;
use App\Models\CbtNilai;
use App\Models\CbtPengawas;
use App\Models\CbtRuang;
use App\Models\CbtSesi;
use App\Models\CbtSoalSiswa;
use App\Models\MasterKelas;
use App\Models\MasterSiswa;
use App\Services\AutoGraderService;

class StatusSiswaController extends Controller
{
    private CbtJadwal $jadwalModel;
    private CbtDurasiSiswa $durasiModel;
    private CbtSoalSiswa $soalSiswaModel;
    private CbtNilai $nilaiModel;
    private CbtRuang $ruangModel;
    private CbtSesi $sesiModel;
    private MasterKelas $kelasModel;
    private MasterSiswa $siswaModel;
    private CbtPengawas $pengawasModel;
    private AutoGraderService $grader;

    public function __construct(?\App\Core\Request $request = null, ?\App\Core\Response $response = null)
    {
        parent::__construct($request, $response);
        $this->jadwalModel = new CbtJadwal();
        $this->durasiModel = new CbtDurasiSiswa();
        $this->soalSiswaModel = new CbtSoalSiswa();
        $this->nilaiModel = new CbtNilai();
        $this->ruangModel = new CbtRuang();
        $this->sesiModel = new CbtSesi();
        $this->kelasModel = new MasterKelas();
        $this->siswaModel = new MasterSiswa();
        $this->pengawasModel = new CbtPengawas();
        $this->grader = new AutoGraderService();
    }

    /**
     * Live Status Siswa table for Admin and Proctors.
     */
    public function index(): void
    {
        $tp = active_tp();
        $smt = active_smt();
        $idTp = (int)($tp['id_tp'] ?? 1);
        $idSmt = (int)($smt['id_smt'] ?? 1);

        $jadwalList = $this->jadwalModel->getAllByFilter($idTp, $idSmt);
        $kelasList = $this->kelasModel->getByPeriod($idTp, $idSmt);
        $ruangList = $this->ruangModel->getAll();
        $sesiList = $this->sesiModel->getAll();

        $selectedJadwal = (int)$this->request->query('jadwal_id', 0);
        if ($selectedJadwal <= 0 && !empty($jadwalList)) {
            $selectedJadwal = (int)$jadwalList[0]['id_jadwal'];
        }

        $selectedKelas = (int)$this->request->query('kelas_id', 0);
        $selectedRuang = (int)$this->request->query('ruang_id', 0);
        $selectedSesi = (int)$this->request->query('sesi_id', 0);
        $search = trim((string)$this->request->query('search', ''));

        $currentJadwal = $selectedJadwal > 0 ? $this->jadwalModel->getDetail($selectedJadwal) : null;
        $studentsStatus = [];

        if ($currentJadwal) {
            $sql = "SELECT s.id_siswa, s.nis, s.nisn, s.nama, s.jk, s.foto,
                           k.id_kelas, k.nama_kelas,
                           r.id_ruang, r.nama_ruang,
                           se.id_sesi, se.nama_sesi,
                           np.nomor_peserta,
                           cds.id_durasi, cds.status as status_ujian, cds.lama_ujian, cds.mulai, cds.selesai, cds.reset, cds.pelanggaran_tab,
                           cn.total_nilai
                    FROM master_siswa s
                    JOIN kelas_siswa ks ON ks.id_siswa = s.id_siswa AND ks.id_tp = ? AND ks.id_smt = ?
                    JOIN master_kelas k ON k.id_kelas = ks.id_kelas
                    LEFT JOIN cbt_sesi_siswa css ON css.siswa_id = s.id_siswa AND css.tp_id = ? AND css.smt_id = ?
                    LEFT JOIN cbt_ruang r ON r.id_ruang = css.ruang_id
                    LEFT JOIN cbt_sesi se ON se.id_sesi = css.sesi_id
                    LEFT JOIN cbt_nomor_peserta np ON np.id_siswa = s.id_siswa AND np.id_tp = ?
                    LEFT JOIN cbt_durasi_siswa cds ON cds.id_jadwal = ? AND cds.id_siswa = s.id_siswa
                    LEFT JOIN cbt_nilai cn ON cn.id_jadwal = ? AND cn.id_siswa = s.id_siswa
                    WHERE 1=1";

            $params = [$idTp, $idSmt, $idTp, $idSmt, $idTp, $selectedJadwal, $selectedJadwal];

            if ($selectedKelas > 0) {
                $sql .= " AND ks.id_kelas = ?";
                $params[] = $selectedKelas;
            }
            if ($selectedRuang > 0) {
                $sql .= " AND css.ruang_id = ?";
                $params[] = $selectedRuang;
            }
            if ($selectedSesi > 0) {
                $sql .= " AND css.sesi_id = ?";
                $params[] = $selectedSesi;
            }
            if (!empty($search)) {
                $sql .= " AND (s.nama LIKE ? OR s.nis LIKE ?)";
                $params[] = "%{$search}%";
                $params[] = "%{$search}%";
            }

            $sql .= " ORDER BY k.level_id ASC, k.nama_kelas ASC, s.nama ASC";

            $rawStudents = $this->siswaModel->query($sql, $params);
            $durasiTotalSeconds = (int)$currentJadwal['durasi_ujian'] * 60;

            foreach ($rawStudents as $st) {
                $stStatus = (int)($st['status_ujian'] ?? 0);
                $remainingSeconds = $durasiTotalSeconds;

                if ($stStatus === 1 && !empty($st['mulai'])) {
                    $elapsed = time() - strtotime($st['mulai']);
                    $remainingSeconds = max(0, $durasiTotalSeconds - $elapsed);
                } elseif ($stStatus === 2) {
                    $remainingSeconds = 0;
                }

                $st['remaining_seconds'] = $remainingSeconds;
                $studentsStatus[] = $st;
            }
        }

        $this->render('cbt/status/index', [
            'title' => 'Live Status Siswa CBT — DADO CBT',
            'jadwalList' => $jadwalList,
            'currentJadwal' => $currentJadwal,
            'kelasList' => $kelasList,
            'ruangList' => $ruangList,
            'sesiList' => $sesiList,
            'students' => $studentsStatus,
            'selectedJadwal' => $selectedJadwal,
            'selectedKelas' => $selectedKelas,
            'selectedRuang' => $selectedRuang,
            'selectedSesi' => $selectedSesi,
            'search' => $search,
        ]);
    }

    /**
     * Reset Login: allows student to login again on another device.
     */
    public function resetLogin(): void
    {
        $idJadwal = (int)$this->request->input('id_jadwal', 0);
        $idSiswa = (int)$this->request->input('id_siswa', 0);

        if ($idJadwal <= 0 || $idSiswa <= 0) {
            $this->respondAction(['status' => 'error', 'message' => 'Parameter tidak valid.']);
            return;
        }

        $ok = $this->durasiModel->resetLogin($idJadwal, $idSiswa);

        if ($ok) {
            $this->respondAction(['status' => 'success', 'message' => 'Login siswa berhasil di-reset. Siswa dapat login kembali.']);
        } else {
            $this->respondAction(['status' => 'error', 'message' => 'Gagal mereset login siswa.']);
        }
    }

    /**
     * Reset Waktu: restart timer or add extra minutes.
     */
    public function resetWaktu(): void
    {
        $idJadwal = (int)$this->request->input('id_jadwal', 0);
        $idSiswa = (int)$this->request->input('id_siswa', 0);
        $extraMinutes = (int)$this->request->input('extra_minutes', 0);

        if ($idJadwal <= 0 || $idSiswa <= 0) {
            $this->respondAction(['status' => 'error', 'message' => 'Parameter tidak valid.']);
            return;
        }

        $ok = $this->durasiModel->resetTimer($idJadwal, $idSiswa, $extraMinutes > 0 ? $extraMinutes : null);

        if ($ok) {
            $msg = $extraMinutes > 0 ? "Waktu ujian siswa berhasil ditambah {$extraMinutes} menit." : "Waktu ujian siswa berhasil di-reset penuh.";
            $this->respondAction(['status' => 'success', 'message' => $msg]);
        } else {
            $this->respondAction(['status' => 'error', 'message' => 'Gagal mereset waktu ujian siswa.']);
        }
    }

    /**
     * Force Finish Exam: immediately ends student exam and computes grade.
     */
    public function paksaSelesai(): void
    {
        $idJadwal = (int)$this->request->input('id_jadwal', 0);
        $idSiswa = (int)$this->request->input('id_siswa', 0);

        if ($idJadwal <= 0 || $idSiswa <= 0) {
            $this->respondAction(['status' => 'error', 'message' => 'Parameter tidak valid.']);
            return;
        }

        $this->durasiModel->finishExam($idJadwal, $idSiswa);
        $this->grader->gradeExam($idJadwal, $idSiswa);

        $this->respondAction(['status' => 'success', 'message' => 'Sesi ujian siswa berhasil dipaksa selesai dan nilai telah dihitung.']);
    }

    /**
     * Ulang Ujian: clears cbt_soal_siswa, cbt_durasi_siswa, and cbt_nilai.
     * Allows student to start fresh without leftover old scores.
     */
    public function ulangUjian(): void
    {
        $idJadwal = (int)$this->request->input('id_jadwal', 0);
        $idSiswa = (int)$this->request->input('id_siswa', 0);

        if ($idJadwal <= 0 || $idSiswa <= 0) {
            $this->respondAction(['status' => 'error', 'message' => 'Parameter tidak valid.']);
            return;
        }

        $this->soalSiswaModel->deleteStudentQuestions($idJadwal, $idSiswa);
        $this->durasiModel->deleteSession($idJadwal, $idSiswa);
        $this->nilaiModel->deleteScore($idJadwal, $idSiswa);

        $this->respondAction(['status' => 'success', 'message' => 'Data sesi, soal acak, dan nilai ujian siswa berhasil dibersihkan. Siswa dapat mengulang ujian dari awal.']);
    }

    private function respondAction(array $data): void
    {
        if ($this->request->isAjax()) {
            $this->response->json($data);
            return;
        }

        if ($data['status'] === 'success') {
            set_flash('success', $data['message']);
        } else {
            set_flash('error', $data['message']);
        }

        $redirect = $this->request->input('redirect_url', '/cbt/status');
        $this->redirect((string)$redirect);
    }
}

