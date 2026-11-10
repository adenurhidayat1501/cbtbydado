<?php

namespace App\Controllers\Master;

use App\Config\Jenjang;
use App\Core\Controller;
use App\Core\Database;
use App\Models\KelasSiswa;
use App\Models\MasterGuru;
use App\Models\MasterJurusan;
use App\Models\MasterKelas;
use App\Models\MasterSiswa;
use App\Models\MasterTp;
use App\Models\SettingSekolah;

class KelasController extends Controller
{
    private MasterKelas $kelasModel;
    private KelasSiswa $kelasSiswaModel;
    private MasterGuru $guruModel;
    private MasterJurusan $jurusanModel;
    private MasterSiswa $siswaModel;
    private MasterTp $tpModel;
    private SettingSekolah $sekolahModel;

    public function __construct($request, $response)
    {
        parent::__construct($request, $response);
        $this->kelasModel = new MasterKelas();
        $this->kelasSiswaModel = new KelasSiswa();
        $this->guruModel = new MasterGuru();
        $this->jurusanModel = new MasterJurusan();
        $this->siswaModel = new MasterSiswa();
        $this->tpModel = new MasterTp();
        $this->sekolahModel = new SettingSekolah();
    }

    public function index(): void
    {
        $tp = active_tp();
        $smt = active_smt();
        $tpId = (int)($tp['id_tp'] ?? 0);
        $smtId = (int)($smt['id_smt'] ?? 0);

        $school = $this->sekolahModel->getActive();
        $jenjang = (int)($school['jenjang'] ?? 2);

        $kelasList = $this->kelasModel->getByPeriod($tpId, $smtId);
        $guruList = $this->guruModel->where(['status' => 1], 'nama_guru ASC');
        $jurusanList = Jenjang::hasJurusan($jenjang) ? $this->jurusanModel->getActive() : [];
        $levels = Jenjang::getLevels($jenjang);
        $allTp = $this->tpModel->all('tahun DESC');

        $this->render('master/kelas/index', [
            'title' => 'Kelas & Rombel — DADO CBT',
            'kelasList' => $kelasList,
            'guruList' => $guruList,
            'jurusanList' => $jurusanList,
            'levels' => $levels,
            'jenjang' => $jenjang,
            'allTp' => $allTp,
            'tp' => $tp,
            'smt' => $smt,
        ]);
    }

    public function store(): void
    {
        $tp = active_tp();
        $smt = active_smt();
        $tpId = (int)($tp['id_tp'] ?? 0);
        $smtId = (int)($smt['id_smt'] ?? 0);

        $nama = trim((string)$this->request->input('nama_kelas', ''));
        $kode = strtoupper(trim((string)$this->request->input('kode_kelas', '')));
        $levelId = (int)$this->request->input('level_id', 7);
        $jurusanId = (int)$this->request->input('jurusan_id', 0) ?: null;
        $guruId = (int)$this->request->input('guru_id', 0) ?: null;

        if (empty($nama) || empty($kode)) {
            $this->setFlash('error', 'Nama Kelas dan Kode Rombel wajib diisi.');
            $this->redirect('/master/kelas');
            return;
        }

        $this->kelasModel->insert([
            'id_tp' => $tpId,
            'id_smt' => $smtId,
            'nama_kelas' => $nama,
            'kode_kelas' => $kode,
            'level_id' => $levelId,
            'jurusan_id' => $jurusanId,
            'guru_id' => $guruId,
            'siswa_id' => json_encode([]),
        ]);

        $this->setFlash('success', "Rombel Kelas {$nama} berhasil ditambahkan.");
        $this->redirect('/master/kelas');
    }

    public function update(string $id): void
    {
        $idKelas = (int)$id;
        $nama = trim((string)$this->request->input('nama_kelas', ''));
        $kode = strtoupper(trim((string)$this->request->input('kode_kelas', '')));
        $levelId = (int)$this->request->input('level_id', 7);
        $jurusanId = (int)$this->request->input('jurusan_id', 0) ?: null;
        $guruId = (int)$this->request->input('guru_id', 0) ?: null;

        $this->kelasModel->update($idKelas, [
            'nama_kelas' => $nama,
            'kode_kelas' => $kode,
            'level_id' => $levelId,
            'jurusan_id' => $jurusanId,
            'guru_id' => $guruId,
        ]);

        $this->setFlash('success', "Data rombel {$nama} berhasil diperbarui.");
        $this->redirect('/master/kelas');
    }

    public function delete(string $id): void
    {
        $idKelas = (int)$id;
        $this->kelasModel->delete($idKelas);
        $this->setFlash('success', 'Rombel kelas berhasil dihapus.');
        $this->redirect('/master/kelas');
    }

    /**
     * Halaman Kelola Anggota Siswa dalam Rombel
     */
    public function anggota(string $id): void
    {
        $idKelas = (int)$id;
        $kelas = $this->kelasModel->getDetail($idKelas);

        if (!$kelas) {
            $this->setFlash('error', 'Data kelas tidak ditemukan.');
            $this->redirect('/master/kelas');
            return;
        }

        $tp = active_tp();
        $smt = active_smt();
        $tpId = (int)($tp['id_tp'] ?? 0);
        $smtId = (int)($smt['id_smt'] ?? 0);

        $studentsInClass = $this->kelasSiswaModel->getStudentsInClass($idKelas, $tpId, $smtId);
        $search = $this->request->input('q');
        $availableStudents = $this->kelasSiswaModel->getAvailableStudents($tpId, $smtId, $search);

        $this->render('master/kelas/anggota', [
            'title' => "Anggota Rombel: {$kelas['nama_kelas']} — DADO CBT",
            'kelas' => $kelas,
            'studentsInClass' => $studentsInClass,
            'availableStudents' => $availableStudents,
            'search' => $search,
            'tp' => $tp,
            'smt' => $smt,
        ]);
    }

    /**
     * Tambah siswa ke rombel
     */
    public function addStudent(): void
    {
        $idKelas = (int)$this->request->input('id_kelas');
        $idSiswa = (int)$this->request->input('id_siswa');

        $tp = active_tp();
        $smt = active_smt();
        $tpId = (int)($tp['id_tp'] ?? 0);
        $smtId = (int)($smt['id_smt'] ?? 0);

        if ($idKelas && $idSiswa) {
            $this->kelasSiswaModel->addStudentToClass($tpId, $smtId, $idKelas, $idSiswa);
            $this->setFlash('success', 'Siswa berhasil dimasukkan ke dalam rombel kelas.');
        }

        $this->redirect('/master/kelas/anggota/' . $idKelas);
    }

    /**
     * Hapus siswa dari rombel
     */
    public function removeStudent(): void
    {
        $idKelas = (int)$this->request->input('id_kelas');
        $idSiswa = (int)$this->request->input('id_siswa');

        $tp = active_tp();
        $smt = active_smt();
        $tpId = (int)($tp['id_tp'] ?? 0);
        $smtId = (int)($smt['id_smt'] ?? 0);

        if ($idKelas && $idSiswa) {
            $this->kelasSiswaModel->removeStudentFromClass($tpId, $smtId, $idKelas, $idSiswa);
            $this->setFlash('success', 'Siswa berhasil dikeluarkan dari rombel kelas.');
        }

        $this->redirect('/master/kelas/anggota/' . $idKelas);
    }

    /**
     * Fitur Salin Rombel Kelas Lintas TP
     */
    public function salinRombel(): void
    {
        $fromTpId = (int)$this->request->input('from_tp_id');
        $fromSmtId = (int)$this->request->input('from_smt_id', 1);

        $tp = active_tp();
        $smt = active_smt();
        $targetTpId = (int)($tp['id_tp'] ?? 0);
        $targetSmtId = (int)($smt['id_smt'] ?? 0);

        if ($fromTpId === $targetTpId && $fromSmtId === $targetSmtId) {
            $this->setFlash('error', 'Periode asal dan periode tujuan tidak boleh sama.');
            $this->redirect('/master/kelas');
            return;
        }

        $sourceKelas = $this->kelasModel->where(['id_tp' => $fromTpId, 'id_smt' => $fromSmtId]);
        if (empty($sourceKelas)) {
            $this->setFlash('error', 'Tidak ada data rombel kelas pada periode asal yang dipilih.');
            $this->redirect('/master/kelas');
            return;
        }

        $copied = 0;
        foreach ($sourceKelas as $sk) {
            // Check if already exists in target
            $exist = $this->kelasModel->first([
                'id_tp' => $targetTpId,
                'id_smt' => $targetSmtId,
                'kode_kelas' => $sk['kode_kelas'],
            ]);

            if (!$exist) {
                $this->kelasModel->insert([
                    'id_tp' => $targetTpId,
                    'id_smt' => $targetSmtId,
                    'nama_kelas' => $sk['nama_kelas'],
                    'kode_kelas' => $sk['kode_kelas'],
                    'level_id' => $sk['level_id'],
                    'jurusan_id' => $sk['jurusan_id'],
                    'guru_id' => $sk['guru_id'],
                    'siswa_id' => json_encode([]),
                ]);
                $copied++;
            }
        }

        $this->setFlash('success', "Berhasil menyalin {$copied} rombel kelas ke periode aktif saat ini.");
        $this->redirect('/master/kelas');
    }
}

