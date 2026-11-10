<?php

declare(strict_types=1);

namespace App\Controllers\Cbt;

use App\Core\Controller;
use App\Models\CbtJadwal;
use App\Models\CbtKopAbsensi;
use App\Models\CbtNomorPeserta;
use App\Models\CbtPengawas;
use App\Models\CbtRuang;
use App\Models\CbtSesi;
use App\Models\CbtSesiSiswa;
use App\Models\MasterKelas;
use App\Models\MasterSiswa;
use App\Models\SettingSekolah;

class CetakController extends Controller
{
    private CbtKopAbsensi $kopModel;
    private SettingSekolah $sekolahModel;
    private MasterKelas $kelasModel;
    private MasterSiswa $siswaModel;
    private CbtJadwal $jadwalModel;
    private CbtRuang $ruangModel;
    private CbtSesi $sesiModel;
    private CbtSesiSiswa $sesiSiswaModel;
    private CbtNomorPeserta $nomorModel;
    private CbtPengawas $pengawasModel;

    public function __construct(?\App\Core\Request $request = null, ?\App\Core\Response $response = null)
    {
        parent::__construct($request, $response);
        $this->kopModel = new CbtKopAbsensi();
        $this->sekolahModel = new SettingSekolah();
        $this->kelasModel = new MasterKelas();
        $this->siswaModel = new MasterSiswa();
        $this->jadwalModel = new CbtJadwal();
        $this->ruangModel = new CbtRuang();
        $this->sesiModel = new CbtSesi();
        $this->sesiSiswaModel = new CbtSesiSiswa();
        $this->nomorModel = new CbtNomorPeserta();
        $this->pengawasModel = new CbtPengawas();
    }

    /**
     * Index of examination print center.
     */
    public function index(): void
    {
        $tp = active_tp();
        $smt = active_smt();
        $idTp = (int)($tp['id_tp'] ?? 1);
        $idSmt = (int)($smt['id_smt'] ?? 1);

        $kelasList = $this->kelasModel->getByPeriod($idTp, $idSmt);
        $jadwalList = $this->jadwalModel->getAllByFilter($idTp, $idSmt);
        $ruangList = $this->ruangModel->getAll();
        $sesiList = $this->sesiModel->getAll();
        $kop = $this->kopModel->getActiveKop();

        $this->render('cbt/cetak/index', [
            'title' => 'Cetak Administrasi CBT — DADO CBT',
            'kelasList' => $kelasList,
            'jadwalList' => $jadwalList,
            'ruangList' => $ruangList,
            'sesiList' => $sesiList,
            'kop' => $kop,
            'tp' => $tp,
            'smt' => $smt,
        ]);
    }

    /**
     * Save Kop & Setting Cetak Administrasi.
     */
    public function saveKop(): void
    {
        $payload = [
            'header_1' => trim((string)$this->request->input('header_1', '')),
            'header_2' => trim((string)$this->request->input('header_2', '')),
            'header_3' => trim((string)$this->request->input('header_3', '')),
            'header_4' => trim((string)$this->request->input('header_4', '')),
            'proktor' => trim((string)$this->request->input('proktor', '')),
            'pengawas_1' => trim((string)$this->request->input('pengawas_1', '')),
            'pengawas_2' => trim((string)$this->request->input('pengawas_2', '')),
            'tanggal' => trim((string)$this->request->input('tanggal', date('d-m-Y'))),
            'kota' => trim((string)$this->request->input('kota', '')),
        ];

        $kop = $this->kopModel->getActiveKop();
        if ($kop) {
            $this->kopModel->update($kop['id_kop'], $payload);
        } else {
            $this->kopModel->insert($payload);
        }

        set_flash('success', 'Pengaturan Kop dan Penandatangan berhasil disimpan.');
        $this->redirect('/cbt/cetak');
    }

    /**
     * Cetak Kartu Peserta Ujian (Print Template).
     */
    public function kartuPeserta(): void
    {
        $tp = active_tp();
        $smt = active_smt();
        $idTp = (int)($tp['id_tp'] ?? 1);
        $idSmt = (int)($smt['id_smt'] ?? 1);

        $idKelas = (int)$this->request->query('kelas_id', 0);
        $students = $this->nomorModel->getStudentsWithNomor($idTp, $idSmt, $idKelas > 0 ? $idKelas : null);
        $sekolah = $this->sekolahModel->getActive();
        $kop = $this->kopModel->getActiveKop();

        // Attach room and session for each student
        foreach ($students as &$stu) {
            $sesiSiswa = $this->sesiSiswaModel->first([
                'siswa_id' => $stu['id_siswa'],
                'tp_id' => $idTp,
                'smt_id' => $idSmt,
            ]);
            $stu['nama_ruang'] = '-';
            $stu['nama_sesi'] = '-';
            if ($sesiSiswa) {
                $r = $this->ruangModel->find($sesiSiswa['ruang_id']);
                $s = $this->sesiModel->find($sesiSiswa['sesi_id']);
                $stu['nama_ruang'] = $r['nama_ruang'] ?? '-';
                $stu['nama_sesi'] = $s['nama_sesi'] ?? '-';
            }
        }
        unset($stu);

        $this->render('cbt/cetak/kartu_peserta', [
            'title' => 'Cetak Kartu Peserta Ujian',
            'students' => $students,
            'sekolah' => $sekolah,
            'kop' => $kop,
            'tp' => $tp,
            'smt' => $smt,
        ], false);
    }

    /**
     * Cetak Daftar Kehadiran Siswa.
     */
    public function daftarHadir(): void
    {
        $tp = active_tp();
        $smt = active_smt();
        $idTp = (int)($tp['id_tp'] ?? 1);
        $idSmt = (int)($smt['id_smt'] ?? 1);

        $idJadwal = (int)$this->request->query('jadwal_id', 0);
        $idRuang = (int)$this->request->query('ruang_id', 0);
        $idSesi = (int)$this->request->query('sesi_id', 0);

        $jadwal = $this->jadwalModel->getDetail($idJadwal);
        $ruang = $this->ruangModel->find($idRuang);
        $sesi = $this->sesiModel->find($idSesi);
        $sekolah = $this->sekolahModel->getActive();
        $kop = $this->kopModel->getActiveKop();

        // Get students assigned to this room and session in this period
        $sql = "SELECT s.id_siswa, s.nis, s.nisn, s.nama, s.jk,
                       k.nama_kelas,
                       np.nomor_peserta
                FROM cbt_sesi_siswa css
                JOIN master_siswa s ON s.id_siswa = css.siswa_id
                JOIN master_kelas k ON k.id_kelas = css.kelas_id
                LEFT JOIN cbt_nomor_peserta np ON np.id_siswa = s.id_siswa AND np.id_tp = css.tp_id
                WHERE css.tp_id = ? AND css.smt_id = ? AND css.ruang_id = ? AND css.sesi_id = ?
                ORDER BY s.nama ASC";

        $students = $this->siswaModel->query($sql, [$idTp, $idSmt, $idRuang, $idSesi]);

        $this->render('cbt/cetak/daftar_hadir', [
            'title' => 'Cetak Daftar Kehadiran Ujian',
            'students' => $students,
            'jadwal' => $jadwal,
            'ruang' => $ruang,
            'sesi' => $sesi,
            'sekolah' => $sekolah,
            'kop' => $kop,
            'tp' => $tp,
            'smt' => $smt,
        ], false);
    }

    /**
     * Cetak Berita Acara Pelaksanaan Ujian.
     */
    public function beritaAcara(): void
    {
        $tp = active_tp();
        $smt = active_smt();
        $idTp = (int)($tp['id_tp'] ?? 1);
        $idSmt = (int)($smt['id_smt'] ?? 1);

        $idJadwal = (int)$this->request->query('jadwal_id', 0);
        $idRuang = (int)$this->request->query('ruang_id', 0);
        $idSesi = (int)$this->request->query('sesi_id', 0);

        $jadwal = $this->jadwalModel->getDetail($idJadwal);
        $ruang = $this->ruangModel->find($idRuang);
        $sesi = $this->sesiModel->find($idSesi);
        $sekolah = $this->sekolahModel->getActive();
        $kop = $this->kopModel->getActiveKop();

        // Calculate student counts
        $sql = "SELECT COUNT(*) as total FROM cbt_sesi_siswa WHERE tp_id = ? AND smt_id = ? AND ruang_id = ? AND sesi_id = ?";
        $totalRow = $this->siswaModel->query($sql, [$idTp, $idSmt, $idRuang, $idSesi]);
        $totalTerdaftar = (int)($totalRow[0]['total'] ?? 0);

        // Get present count from cbt_durasi_siswa
        $sqlHadir = "SELECT COUNT(*) as hadir FROM cbt_durasi_siswa cds
                     JOIN cbt_sesi_siswa css ON css.siswa_id = cds.id_siswa AND css.tp_id = ? AND css.smt_id = ?
                     WHERE cds.id_jadwal = ? AND css.ruang_id = ? AND css.sesi_id = ? AND cds.status >= 1";
        $hadirRow = $this->siswaModel->query($sqlHadir, [$idTp, $idSmt, $idJadwal, $idRuang, $idSesi]);
        $totalHadir = (int)($hadirRow[0]['hadir'] ?? $totalTerdaftar);
        $totalTidakHadir = max(0, $totalTerdaftar - $totalHadir);

        $this->render('cbt/cetak/berita_acara', [
            'title' => 'Cetak Berita Acara Ujian',
            'jadwal' => $jadwal,
            'ruang' => $ruang,
            'sesi' => $sesi,
            'sekolah' => $sekolah,
            'kop' => $kop,
            'totalTerdaftar' => $totalTerdaftar,
            'totalHadir' => $totalHadir,
            'totalTidakHadir' => $totalTidakHadir,
            'tp' => $tp,
            'smt' => $smt,
        ], false);
    }
}

