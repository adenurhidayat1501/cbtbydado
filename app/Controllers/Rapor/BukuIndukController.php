<?php

namespace App\Controllers\Rapor;

use App\Core\Controller;
use App\Models\BukuInduk;
use App\Models\MasterSiswa;
use App\Models\MasterKelas;
use App\Models\MasterTp;
use App\Models\MasterSmt;
use App\Models\SettingSekolah;
use App\Models\MasterMapel;
use App\Models\RaporNilaiAkhir;
use App\Models\RaporCatatanWali;
use App\Models\RaporNaik;

class BukuIndukController extends Controller
{
    protected BukuInduk $bukuModel;
    protected MasterSiswa $siswaModel;
    protected MasterKelas $kelasModel;
    protected MasterTp $tpModel;
    protected MasterSmt $smtModel;
    protected SettingSekolah $sekolahModel;
    protected MasterMapel $mapelModel;
    protected RaporNilaiAkhir $akhirModel;

    public function __construct()
    {
        $this->bukuModel = new BukuInduk();
        $this->siswaModel = new MasterSiswa();
        $this->kelasModel = new MasterKelas();
        $this->tpModel = new MasterTp();
        $this->smtModel = new MasterSmt();
        $this->sekolahModel = new SettingSekolah();
        $this->mapelModel = new MasterMapel();
        $this->akhirModel = new RaporNilaiAkhir();
    }

    protected function getActiveTpSmt(): array
    {
        $activeTp = $this->tpModel->getActive();
        $activeSmt = $this->smtModel->getActive();
        return [
            'id_tp' => (int)($activeTp['id_tp'] ?? 1),
            'id_smt' => (int)($activeSmt['id_smt'] ?? 1)
        ];
    }

    /**
     * Daftar Buku Induk & Alumni
     */
    public function index(): void
    {
        $this->requireRole(['admin', 'guru']);
        ['id_tp' => $idTp, 'id_smt' => $idSmt] = $this->getActiveTpSmt();

        $kelasList = $this->kelasModel->all('level ASC, nama_kelas ASC');
        $selectedKelas = isset($_GET['id_kelas']) ? (int)$_GET['id_kelas'] : null;
        $selectedStatus = isset($_GET['status']) && $_GET['status'] !== '' ? (int)$_GET['status'] : null;

        $students = $this->bukuModel->listAllWithClass($idTp, $idSmt, $selectedKelas, $selectedStatus);

        $this->render('rapor/buku_induk/index', [
            'title' => 'Buku Induk Siswa & Alumni',
            'kelasList' => $kelasList,
            'selectedKelas' => $selectedKelas,
            'selectedStatus' => $selectedStatus,
            'students' => $students
        ]);
    }

    /**
     * Detail Lembar Buku Induk Siswa
     */
    public function detail(int $idSiswa): void
    {
        $this->requireRole(['admin', 'guru']);
        $buku = $this->bukuModel->getOrCreate($idSiswa);

        $arsip = [];
        if (!empty($buku['arsip_rapor_json'])) {
            $decoded = json_decode($buku['arsip_rapor_json'], true);
            if (is_array($decoded)) {
                $arsip = $decoded;
            }
        }

        $this->render('rapor/buku_induk/detail', [
            'title' => 'Detail Lembar Buku Induk — ' . ($buku['nama'] ?? 'Siswa'),
            'buku' => $buku,
            'arsip' => $arsip
        ]);
    }

    /**
     * Simpan Perubahan Biodata Buku Induk Siswa
     */
    public function save(int $idSiswa): void
    {
        $this->requireRole(['admin', 'guru']);

        $saved = $this->bukuModel->saveDetails($idSiswa, $_POST);
        if ($saved) {
            $_SESSION['flash_success'] = 'Biodata lembar buku induk siswa berhasil diperbarui.';
        } else {
            $_SESSION['flash_error'] = 'Gagal menyimpan data buku induk.';
        }

        $this->redirect("/rapor/buku-induk/detail/{$idSiswa}");
    }

    /**
     * Cetak Lembar Buku Induk Siswa
     */
    public function cetak(int $idSiswa): void
    {
        $this->requireRole(['admin', 'guru']);
        $sekolah = $this->sekolahModel->getSetting();
        $buku = $this->bukuModel->getOrCreate($idSiswa);

        $arsip = [];
        if (!empty($buku['arsip_rapor_json'])) {
            $decoded = json_decode($buku['arsip_rapor_json'], true);
            if (is_array($decoded)) {
                $arsip = $decoded;
            }
        }

        $this->render('rapor/buku_induk/cetak', [
            'sekolah' => $sekolah,
            'buku' => $buku,
            'arsip' => $arsip
        ]);
    }

    /**
     * Sinkronisasi Arsip Rapor Semester Aktif ke Buku Induk Siswa
     */
    public function syncRapor(int $idSiswa): void
    {
        $this->requireRole(['admin', 'guru']);
        ['id_tp' => $idTp, 'id_smt' => $idSmt] = $this->getActiveTpSmt();

        $activeTp = $this->tpModel->getActive();
        $activeSmt = $this->smtModel->getActive();

        // Cari kelas siswa
        $stmtKelas = $this->tpModel->query(
            "SELECT ks.id_kelas, k.nama_kelas 
             FROM kelas_siswa ks
             JOIN master_kelas k ON ks.id_kelas = k.id_kelas
             WHERE ks.id_siswa = ? AND ks.id_tp = ? AND ks.id_smt = ? LIMIT 1",
            [$idSiswa, $idTp, $idSmt]
        );
        $kRow = $stmtKelas->fetch();
        $idKelas = (int)($kRow['id_kelas'] ?? 0);
        $namaKelas = $kRow['nama_kelas'] ?? '-';

        // Kumpulkan nilai seluruh mapel semester ini
        $mapelList = $this->mapelModel->all('urutan_tampil ASC, nama_mapel ASC');
        $grades = [];
        $totalNilai = 0.0;
        $count = 0;

        foreach ($mapelList as $m) {
            $idMapel = (int)$m['id_mapel'];
            $na = $this->akhirModel->getSingle($idTp, $idSmt, $idKelas, $idMapel, $idSiswa);
            $val = $na && $na['akhir'] !== null ? (float)$na['akhir'] : null;

            if ($val !== null) {
                $grades[] = [
                    'kode_mapel' => $m['kode'] ?? ($m['kode_mapel'] ?? ''),
                    'nama_mapel' => $m['nama_mapel'],
                    'nilai' => $val,
                    'predikat' => $na['predikat'] ?? '-'
                ];
                $totalNilai += $val;
                $count++;
            }
        }

        $rataRata = $count > 0 ? round($totalNilai / $count, 2) : 0.0;

        // Kehadiran
        $cwModel = new RaporCatatanWali();
        $cw = $cwModel->getSingle($idTp, $idSmt, $idKelas, $idSiswa);
        $absensi = ['sakit' => 0, 'izin' => 0, 'alfa' => 0];
        if ($cw && !empty($cw['nilai'])) {
            $decoded = json_decode($cw['nilai'], true);
            if (is_array($decoded)) {
                $absensi = array_merge($absensi, $decoded);
            }
        }

        // Kenaikan / Kelulusan
        $naikModel = new RaporNaik();
        $naik = $naikModel->getSingle($idTp, $idSmt, $idSiswa);

        $gradeSnapshot = [
            'tahun_pelajaran' => $activeTp['tahun'] ?? '-',
            'semester' => $activeSmt['nama_smt'] ?? '-',
            'kelas' => $namaKelas,
            'grades' => $grades,
            'total_nilai' => $totalNilai,
            'rata_rata' => $rataRata,
            'absensi' => $absensi,
            'status_naik' => $naik ? (int)$naik['naik'] : 1
        ];

        $archived = $this->bukuModel->archiveSemesterGrade($idSiswa, $idTp, $idSmt, $gradeSnapshot);
        if ($archived) {
            $_SESSION['flash_success'] = 'Nilai rapor semester berhasil diarsipkan ke Buku Induk Siswa.';
        } else {
            $_SESSION['flash_error'] = 'Gagal mengarsipkan nilai ke Buku Induk.';
        }

        $this->redirect("/rapor/buku-induk/detail/{$idSiswa}");
    }

    /**
     * Update Status Kelulusan / Alumni
     */
    public function updateStatus(): void
    {
        $this->requireRole('admin');
        $idSiswa = (int)($_POST['id_siswa'] ?? 0);
        $status = (int)($_POST['status'] ?? 1);
        $tahunLulus = $_POST['tahun_lulus'] ?? null;
        $noIjazah = $_POST['no_ijazah'] ?? null;

        if ($idSiswa > 0) {
            $this->bukuModel->getOrCreate($idSiswa);
            $this->bukuModel->saveDetails($idSiswa, [
                'status' => $status,
                'tahun_lulus' => $tahunLulus,
                'no_ijazah' => $noIjazah
            ]);
            $_SESSION['flash_success'] = 'Status kelulusan/alumni siswa berhasil diperbarui.';
        }

        $this->redirect('/rapor/buku-induk');
    }
}
