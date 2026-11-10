<?php

namespace App\Controllers\Cbt;

use App\Core\Controller;
use App\Models\CbtJenis;
use App\Models\CbtRuang;
use App\Models\CbtSesi;
use App\Models\CbtSesiSiswa;
use App\Models\CbtNomorPeserta;
use App\Models\MasterKelas;

class MasterCbtController extends Controller
{
    private CbtJenis $jenisModel;
    private CbtRuang $ruangModel;
    private CbtSesi $sesiModel;
    private CbtSesiSiswa $sesiSiswaModel;
    private CbtNomorPeserta $nomorModel;
    private MasterKelas $kelasModel;

    public function __construct(?\App\Core\Request $request = null, ?\App\Core\Response $response = null)
    {
        parent::__construct($request, $response);
        $this->jenisModel = new CbtJenis();
        $this->ruangModel = new CbtRuang();
        $this->sesiModel = new CbtSesi();
        $this->sesiSiswaModel = new CbtSesiSiswa();
        $this->nomorModel = new CbtNomorPeserta();
        $this->kelasModel = new MasterKelas();
    }

    /**
     * Display Master CBT: Jenis Ujian, Ruang, and Sesi tabs.
     */
    public function index(): void
    {
        $tp = active_tp();
        $smt = active_smt();
        $idTp = (int)($tp['id_tp'] ?? 1);
        $idSmt = (int)($smt['id_smt'] ?? 1);

        $jenisList = $this->jenisModel->getAll();
        $ruangList = $this->ruangModel->getWithStudentCount($idTp, $idSmt);
        $sesiList = $this->sesiModel->getWithStudentCount($idTp, $idSmt);

        $activeTab = trim((string)$this->request->query('tab', 'jenis'));

        $this->render('cbt/master/index', [
            'title' => 'Master CBT & Ruang — DADO CBT',
            'tp' => $tp,
            'smt' => $smt,
            'activeTab' => $activeTab,
            'jenisList' => $jenisList,
            'ruangList' => $ruangList,
            'sesiList' => $sesiList,
        ]);
    }

    // --- JENIS UJIAN ACTIONS ---
    public function storeJenis(): void
    {
        $nama = trim((string)$this->request->input('nama_jenis', ''));
        $kode = strtoupper(trim((string)$this->request->input('kode_jenis', '')));

        if (empty($nama) || empty($kode)) {
            set_flash('error', 'Nama dan Kode jenis ujian wajib diisi.');
            $this->redirect('/cbt/master?tab=jenis');
            return;
        }

        $this->jenisModel->insert([
            'nama_jenis' => $nama,
            'kode_jenis' => $kode,
        ]);

        set_flash('success', "Jenis ujian '{$nama}' berhasil ditambahkan.");
        $this->redirect('/cbt/master?tab=jenis');
    }

    public function updateJenis(int $id): void
    {
        $nama = trim((string)$this->request->input('nama_jenis', ''));
        $kode = strtoupper(trim((string)$this->request->input('kode_jenis', '')));

        if (empty($nama) || empty($kode)) {
            set_flash('error', 'Nama dan Kode jenis ujian wajib diisi.');
            $this->redirect('/cbt/master?tab=jenis');
            return;
        }

        $this->jenisModel->update($id, [
            'nama_jenis' => $nama,
            'kode_jenis' => $kode,
        ]);

        set_flash('success', "Jenis ujian berhasil diperbarui.");
        $this->redirect('/cbt/master?tab=jenis');
    }

    public function deleteJenis(int $id): void
    {
        $this->jenisModel->delete($id);
        set_flash('success', 'Jenis ujian berhasil dihapus.');
        $this->redirect('/cbt/master?tab=jenis');
    }

    // --- RUANG UJIAN ACTIONS ---
    public function storeRuang(): void
    {
        $nama = trim((string)$this->request->input('nama_ruang', ''));
        $kode = strtoupper(trim((string)$this->request->input('kode_ruang', '')));

        if (empty($nama) || empty($kode)) {
            set_flash('error', 'Nama dan Kode ruang ujian wajib diisi.');
            $this->redirect('/cbt/master?tab=ruang');
            return;
        }

        $this->ruangModel->insert([
            'nama_ruang' => $nama,
            'kode_ruang' => $kode,
        ]);

        set_flash('success', "Ruang ujian '{$nama}' berhasil ditambahkan.");
        $this->redirect('/cbt/master?tab=ruang');
    }

    public function updateRuang(int $id): void
    {
        $nama = trim((string)$this->request->input('nama_ruang', ''));
        $kode = strtoupper(trim((string)$this->request->input('kode_ruang', '')));

        if (empty($nama) || empty($kode)) {
            set_flash('error', 'Nama dan Kode ruang ujian wajib diisi.');
            $this->redirect('/cbt/master?tab=ruang');
            return;
        }

        $this->ruangModel->update($id, [
            'nama_ruang' => $nama,
            'kode_ruang' => $kode,
        ]);

        set_flash('success', "Ruang ujian berhasil diperbarui.");
        $this->redirect('/cbt/master?tab=ruang');
    }

    public function deleteRuang(int $id): void
    {
        $this->ruangModel->delete($id);
        set_flash('success', 'Ruang ujian berhasil dihapus.');
        $this->redirect('/cbt/master?tab=ruang');
    }

    // --- SESI UJIAN ACTIONS ---
    public function storeSesi(): void
    {
        $nama = trim((string)$this->request->input('nama_sesi', ''));
        $kode = strtoupper(trim((string)$this->request->input('kode_sesi', '')));
        $mulai = trim((string)$this->request->input('waktu_mulai', '07:30'));
        $akhir = trim((string)$this->request->input('waktu_akhir', '09:30'));

        if (empty($nama) || empty($kode)) {
            set_flash('error', 'Nama dan Kode sesi ujian wajib diisi.');
            $this->redirect('/cbt/master?tab=sesi');
            return;
        }

        if (strlen($mulai) === 5) $mulai .= ':00';
        if (strlen($akhir) === 5) $akhir .= ':00';

        $this->sesiModel->insert([
            'nama_sesi' => $nama,
            'kode_sesi' => $kode,
            'waktu_mulai' => $mulai,
            'waktu_akhir' => $akhir,
        ]);

        set_flash('success', "Sesi ujian '{$nama}' berhasil ditambahkan.");
        $this->redirect('/cbt/master?tab=sesi');
    }

    public function updateSesi(int $id): void
    {
        $nama = trim((string)$this->request->input('nama_sesi', ''));
        $kode = strtoupper(trim((string)$this->request->input('kode_sesi', '')));
        $mulai = trim((string)$this->request->input('waktu_mulai', '07:30'));
        $akhir = trim((string)$this->request->input('waktu_akhir', '09:30'));

        if (empty($nama) || empty($kode)) {
            set_flash('error', 'Nama dan Kode sesi ujian wajib diisi.');
            $this->redirect('/cbt/master?tab=sesi');
            return;
        }

        if (strlen($mulai) === 5) $mulai .= ':00';
        if (strlen($akhir) === 5) $akhir .= ':00';

        $this->sesiModel->update($id, [
            'nama_sesi' => $nama,
            'kode_sesi' => $kode,
            'waktu_mulai' => $mulai,
            'waktu_akhir' => $akhir,
        ]);

        set_flash('success', "Sesi ujian berhasil diperbarui.");
        $this->redirect('/cbt/master?tab=sesi');
    }

    public function deleteSesi(int $id): void
    {
        $this->sesiModel->delete($id);
        set_flash('success', 'Sesi ujian berhasil dihapus.');
        $this->redirect('/cbt/master?tab=sesi');
    }

    // --- MATRIKS ATUR RUANG & SESI SISWA PER KELAS ---
    public function ruangSesiSiswa(): void
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

        $ruangList = $this->ruangModel->getAll();
        $sesiList = $this->sesiModel->getAll();

        $students = [];
        if ($selectedKelasId > 0) {
            $students = $this->sesiSiswaModel->getStudentsWithRoomAndSession($idTp, $idSmt, $selectedKelasId);
        }

        $this->render('cbt/master/sesi_siswa', [
            'title' => 'Atur Ruang & Sesi Siswa — DADO CBT',
            'tp' => $tp,
            'smt' => $smt,
            'kelasList' => $kelasList,
            'selectedKelasId' => $selectedKelasId,
            'ruangList' => $ruangList,
            'sesiList' => $sesiList,
            'students' => $students,
        ]);
    }

    public function saveSesiSiswa(): void
    {
        $tp = active_tp();
        $smt = active_smt();
        $idTp = (int)($tp['id_tp'] ?? 1);
        $idSmt = (int)($smt['id_smt'] ?? 1);

        $idKelas = (int)$this->request->input('id_kelas', 0);
        $idRuang = (int)$this->request->input('ruang_id', 0);
        $idSesi = (int)$this->request->input('sesi_id', 0);
        $idSiswa = (int)$this->request->input('id_siswa', 0);
        $bulkSiswaIds = $this->request->input('siswa_ids', []);

        if ($idKelas <= 0 || $idRuang <= 0 || $idSesi <= 0) {
            if ($this->request->isAjax()) {
                $this->response->json(['status' => 'error', 'message' => 'Pilih kelas, ruang, dan sesi terlebih dahulu.'], 400);
                return;
            }
            set_flash('error', 'Pilih kelas, ruang, dan sesi terlebih dahulu.');
            $this->redirect('/cbt/master/ruang-sesi-siswa?kelas_id=' . $idKelas);
            return;
        }

        if (!empty($bulkSiswaIds)) {
            $count = $this->sesiSiswaModel->bulkAssign($idTp, $idSmt, $idKelas, $idRuang, $idSesi, (array)$bulkSiswaIds);
            set_flash('success', "Berhasil memetakan {$count} siswa ke Ruang & Sesi yang dipilih.");
            $this->redirect('/cbt/master/ruang-sesi-siswa?kelas_id=' . $idKelas);
            return;
        }

        if ($idSiswa > 0) {
            $this->sesiSiswaModel->assignStudent($idTp, $idSmt, $idSiswa, $idKelas, $idRuang, $idSesi);
            if ($this->request->isAjax()) {
                $this->response->json(['status' => 'success', 'message' => 'Ruang dan sesi siswa berhasil disimpan.']);
                return;
            }
            set_flash('success', 'Ruang dan sesi siswa berhasil disimpan.');
            $this->redirect('/cbt/master/ruang-sesi-siswa?kelas_id=' . $idKelas);
            return;
        }

        $this->redirect('/cbt/master/ruang-sesi-siswa?kelas_id=' . $idKelas);
    }

    // --- NOMOR PESERTA UJIAN ---
    public function nomorPeserta(): void
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
        $students = $this->nomorModel->getStudentsWithNomor($idTp, $idSmt, $selectedKelasId ?: null);

        $this->render('cbt/master/nomor_peserta', [
            'title' => 'Nomor Peserta Ujian — DADO CBT',
            'tp' => $tp,
            'smt' => $smt,
            'kelasList' => $kelasList,
            'selectedKelasId' => $selectedKelasId,
            'students' => $students,
        ]);
    }

    public function generateNomorPeserta(): void
    {
        $tp = active_tp();
        $smt = active_smt();
        $idTp = (int)($tp['id_tp'] ?? 1);
        $idSmt = (int)($smt['id_smt'] ?? 1);

        $idKelas = (int)$this->request->input('id_kelas', 0);
        $prefix = trim((string)$this->request->input('prefix', ''));

        $count = $this->nomorModel->generateNomor($idTp, $idSmt, $idKelas ?: null, $prefix);

        set_flash('success', "Berhasil men-generate {$count} nomor peserta ujian.");
        $this->redirect('/cbt/master/nomor-peserta' . ($idKelas ? '?kelas_id=' . $idKelas : ''));
    }

    public function saveNomorPeserta(): void
    {
        $tp = active_tp();
        $idTp = (int)($tp['id_tp'] ?? 1);

        $idSiswa = (int)$this->request->input('id_siswa', 0);
        $nomor = trim((string)$this->request->input('nomor_peserta', ''));
        $idKelas = (int)$this->request->input('id_kelas', 0);

        if ($idSiswa <= 0 || empty($nomor)) {
            if ($this->request->isAjax()) {
                $this->response->json(['status' => 'error', 'message' => 'Nomor peserta tidak boleh kosong.'], 400);
                return;
            }
            set_flash('error', 'Nomor peserta tidak boleh kosong.');
            $this->redirect('/cbt/master/nomor-peserta');
            return;
        }

        $this->nomorModel->saveNomor($idTp, $idSiswa, $nomor);

        if ($this->request->isAjax()) {
            $this->response->json(['status' => 'success', 'message' => 'Nomor peserta tersimpan.']);
            return;
        }

        set_flash('success', 'Nomor peserta berhasil diperbarui.');
        $this->redirect('/cbt/master/nomor-peserta' . ($idKelas ? '?kelas_id=' . $idKelas : ''));
    }
}
