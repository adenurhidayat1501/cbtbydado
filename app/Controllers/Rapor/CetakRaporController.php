<?php

namespace App\Controllers\Rapor;

use App\Core\Controller;
use App\Models\SettingSekolah;
use App\Models\MasterTp;
use App\Models\MasterSmt;
use App\Models\MasterKelas;
use App\Models\MasterSiswa;
use App\Models\MasterGuru;
use App\Models\MasterMapel;
use App\Models\RaporAdminSetting;
use App\Models\RaporNilaiHarian;
use App\Models\RaporNilaiPts;
use App\Models\RaporNilaiAkhir;
use App\Models\RaporNilaiSikap;
use App\Models\RaporNilaiEkstra;
use App\Models\RaporCatatanWali;
use App\Models\RaporFisik;
use App\Models\RaporPrestasi;
use App\Models\RaporNaik;
use App\Services\RaporCalculatorService;

class CetakRaporController extends Controller
{
    protected SettingSekolah $sekolahModel;
    protected MasterTp $tpModel;
    protected MasterSmt $smtModel;
    protected MasterKelas $kelasModel;
    protected MasterSiswa $siswaModel;
    protected MasterGuru $guruModel;
    protected MasterMapel $mapelModel;
    protected RaporAdminSetting $settingModel;
    protected RaporNilaiHarian $harianModel;
    protected RaporNilaiPts $ptsModel;
    protected RaporNilaiAkhir $akhirModel;
    protected RaporNilaiSikap $sikapModel;
    protected RaporNilaiEkstra $ekstraModel;
    protected RaporCatatanWali $catatanModel;
    protected RaporFisik $fisikModel;
    protected RaporPrestasi $prestasiModel;
    protected RaporNaik $naikModel;
    protected RaporCalculatorService $calcService;

    public function __construct()
    {
        $this->sekolahModel = new SettingSekolah();
        $this->tpModel = new MasterTp();
        $this->smtModel = new MasterSmt();
        $this->kelasModel = new MasterKelas();
        $this->siswaModel = new MasterSiswa();
        $this->guruModel = new MasterGuru();
        $this->mapelModel = new MasterMapel();
        $this->settingModel = new RaporAdminSetting();
        $this->harianModel = new RaporNilaiHarian();
        $this->ptsModel = new RaporNilaiPts();
        $this->akhirModel = new RaporNilaiAkhir();
        $this->sikapModel = new RaporNilaiSikap();
        $this->ekstraModel = new RaporNilaiEkstra();
        $this->catatanModel = new RaporCatatanWali();
        $this->fisikModel = new RaporFisik();
        $this->prestasiModel = new RaporPrestasi();
        $this->naikModel = new RaporNaik();
        $this->calcService = new RaporCalculatorService();
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
     * Halaman Utama Menu Cetak Rapor & DKN
     */
    public function index(): void
    {
        $this->requireRole(['admin', 'guru']);
        ['id_tp' => $idTp, 'id_smt' => $idSmt] = $this->getActiveTpSmt();

        $kelasList = $this->kelasModel->all('level ASC, nama_kelas ASC');
        $selectedKelas = isset($_GET['id_kelas']) ? (int)$_GET['id_kelas'] : ($kelasList[0]['id_kelas'] ?? 0);

        $students = [];
        if ($selectedKelas > 0) {
            $stmt = $this->tpModel->query(
                "SELECT s.id_siswa, s.nama, s.nis, s.nisn, s.jk AS jenis_kelamin, s.foto
                 FROM kelas_siswa ks
                 JOIN master_siswa s ON ks.id_siswa = s.id_siswa
                 WHERE ks.id_tp = ? AND ks.id_smt = ? AND ks.id_kelas = ?
                 ORDER BY s.nama ASC",
                [$idTp, $idSmt, $selectedKelas]
            );
            $students = $stmt->fetchAll();
        }

        $this->render('rapor/cetak/index', [
            'title' => 'Cetak Rapor & DKN',
            'kelasList' => $kelasList,
            'selectedKelas' => $selectedKelas,
            'students' => $students
        ]);
    }

    /**
     * Cetak Rapor Tengah Semester (PTS)
     */
    public function cetakPts(int $idKelas, int $idSiswa): void
    {
        $this->requireRole(['admin', 'guru']);
        ['id_tp' => $idTp, 'id_smt' => $idSmt] = $this->getActiveTpSmt();

        $sekolah = $this->sekolahModel->getSetting();
        $activeTp = $this->tpModel->getActive();
        $activeSmt = $this->smtModel->getActive();
        $kelas = $this->kelasModel->find($idKelas);
        $siswa = $this->siswaModel->find($idSiswa);
        $raporSetting = $this->settingModel->getSetting($idTp, $idSmt);

        if (!$kelas || !$siswa) {
            $_SESSION['flash_error'] = 'Data kelas atau siswa tidak ditemukan.';
            $this->redirect('/rapor/cetak');
            return;
        }

        // Wali Kelas
        $waliKelas = null;
        if (!empty($kelas['id_guru_wali'])) {
            $waliKelas = $this->guruModel->find($kelas['id_guru_wali']);
        }

        // Ambil Nilai Mapel
        $mapelList = $this->mapelModel->all('urutan_tampil ASC, nama_mapel ASC');
        $grades = [];

        foreach ($mapelList as $m) {
            $idMapel = (int)$m['id_mapel'];
            $kkmData = $this->calcService->getKkmValue($idTp, $idSmt, $idKelas, $idMapel);
            $harian = $this->harianModel->getSingle($idTp, $idSmt, $idKelas, $idMapel, $idSiswa);
            $pts = $this->ptsModel->getSingle($idTp, $idSmt, $idKelas, $idMapel, $idSiswa);

            $grades[] = [
                'mapel' => $m,
                'kkm' => (float)$kkmData['kkm'],
                'ph' => $harian['p_rata_rata'] ?? null,
                'ph_pred' => $harian['p_predikat'] ?? null,
                'k' => $harian['k_rata_rata'] ?? null,
                'k_pred' => $harian['k_predikat'] ?? null,
                'pts' => $pts['nilai'] ?? null,
                'pts_pred' => $pts['predikat'] ?? null
            ];
        }

        // Kehadiran & Catatan
        $cw = $this->catatanModel->getSingle($idTp, $idSmt, $idKelas, $idSiswa);
        $absensi = ['sakit' => 0, 'izin' => 0, 'alfa' => 0];
        if ($cw && !empty($cw['nilai'])) {
            $decoded = json_decode($cw['nilai'], true);
            if (is_array($decoded)) {
                $absensi = array_merge($absensi, $decoded);
            }
        }

        $this->render('rapor/cetak/rapor_pts', [
            'sekolah' => $sekolah,
            'activeTp' => $activeTp,
            'activeSmt' => $activeSmt,
            'kelas' => $kelas,
            'siswa' => $siswa,
            'waliKelas' => $waliKelas,
            'raporSetting' => $raporSetting,
            'grades' => $grades,
            'absensi' => $absensi,
            'catatan' => $cw['deskripsi'] ?? ''
        ]);
    }

    /**
     * Cetak Rapor Akhir Semester (PAS / PAT Lengkap Cover & Lembar Capaian)
     */
    public function cetakPas(int $idKelas, int $idSiswa): void
    {
        $this->requireRole(['admin', 'guru']);
        ['id_tp' => $idTp, 'id_smt' => $idSmt] = $this->getActiveTpSmt();

        $sekolah = $this->sekolahModel->getSetting();
        $activeTp = $this->tpModel->getActive();
        $activeSmt = $this->smtModel->getActive();
        $kelas = $this->kelasModel->find($idKelas);
        $siswa = $this->siswaModel->find($idSiswa);
        $raporSetting = $this->settingModel->getSetting($idTp, $idSmt);

        if (!$kelas || !$siswa) {
            $_SESSION['flash_error'] = 'Data kelas atau siswa tidak ditemukan.';
            $this->redirect('/rapor/cetak');
            return;
        }

        // Wali Kelas & Kepala Sekolah
        $waliKelas = null;
        if (!empty($kelas['id_guru_wali'])) {
            $waliKelas = $this->guruModel->find($kelas['id_guru_wali']);
        }

        // Ambil Nilai Akademik per Kelompok Mapel
        $mapelList = $this->mapelModel->all('urutan_tampil ASC, nama_mapel ASC');
        $groupedGrades = [
            'A' => [],
            'B' => [],
            'C' => [],
            'Mulok' => []
        ];

        foreach ($mapelList as $m) {
            $idMapel = (int)$m['id_mapel'];
            $kkmData = $this->calcService->getKkmValue($idTp, $idSmt, $idKelas, $idMapel);
            $harian = $this->harianModel->getSingle($idTp, $idSmt, $idKelas, $idMapel, $idSiswa);
            $akhir = $this->akhirModel->getSingle($idTp, $idSmt, $idKelas, $idMapel, $idSiswa);

            $kelompok = strtoupper(trim($m['kelompok'] ?? 'A'));
            if (!isset($groupedGrades[$kelompok])) {
                $groupedGrades[$kelompok] = [];
            }

            $groupedGrades[$kelompok][] = [
                'mapel' => $m,
                'kkm' => (float)$kkmData['kkm'],
                'p_nilai' => $harian['p_rata_rata'] ?? ($akhir['akhir'] ?? null),
                'p_predikat' => $harian['p_predikat'] ?? ($akhir['predikat'] ?? null),
                'p_deskripsi' => $harian['p_deskripsi'] ?? 'Memahami materi pembelajaran dengan baik.',
                'k_nilai' => $harian['k_rata_rata'] ?? ($akhir['akhir'] ?? null),
                'k_predikat' => $harian['k_predikat'] ?? ($akhir['predikat'] ?? null),
                'k_deskripsi' => $harian['k_deskripsi'] ?? 'Mampu mempraktikkan keterampilan materi dengan baik.',
                'akhir' => $akhir['akhir'] ?? null,
                'predikat' => $akhir['predikat'] ?? null
            ];
        }

        // Nilai Sikap
        $sikapSpiritual = $this->sikapModel->getSingle($idTp, $idSmt, $idKelas, $idSiswa, 1);
        $sikapSosial = $this->sikapModel->getSingle($idTp, $idSmt, $idKelas, $idSiswa, 2);

        // Ekstrakurikuler
        $ekstraList = $this->ekstraModel->getStudentEkstra($idTp, $idSmt, $idSiswa);

        // Prestasi
        $prestasi = $this->prestasiModel->getSingle($idTp, $idSmt, $idKelas, $idSiswa);

        // Fisik / Kesehatan
        $fisik = $this->fisikModel->getSingle($idTp, $idSmt, $idKelas, $idSiswa);

        // Catatan & Presensi
        $cw = $this->catatanModel->getSingle($idTp, $idSmt, $idKelas, $idSiswa);
        $absensi = ['sakit' => 0, 'izin' => 0, 'alfa' => 0];
        if ($cw && !empty($cw['nilai'])) {
            $decoded = json_decode($cw['nilai'], true);
            if (is_array($decoded)) {
                $absensi = array_merge($absensi, $decoded);
            }
        }

        // Kenaikan / Kelulusan
        $naik = $this->naikModel->getSingle($idTp, $idSmt, $idSiswa);

        $this->render('rapor/cetak/rapor_pas', [
            'sekolah' => $sekolah,
            'activeTp' => $activeTp,
            'activeSmt' => $activeSmt,
            'kelas' => $kelas,
            'siswa' => $siswa,
            'waliKelas' => $waliKelas,
            'raporSetting' => $raporSetting,
            'groupedGrades' => $groupedGrades,
            'sikapSpiritual' => $sikapSpiritual,
            'sikapSosial' => $sikapSosial,
            'ekstraList' => $ekstraList,
            'prestasi' => $prestasi,
            'fisik' => $fisik,
            'absensi' => $absensi,
            'catatan' => $cw['deskripsi'] ?? '',
            'naik' => $naik
        ]);
    }

    /**
     * Cetak DKN (Daftar Kumpulan Nilai)
     */
    public function cetakDkn(int $idKelas): void
    {
        $this->requireRole(['admin', 'guru']);
        ['id_tp' => $idTp, 'id_smt' => $idSmt] = $this->getActiveTpSmt();

        $sekolah = $this->sekolahModel->getSetting();
        $activeTp = $this->tpModel->getActive();
        $activeSmt = $this->smtModel->getActive();
        $kelas = $this->kelasModel->find($idKelas);
        $raporSetting = $this->settingModel->getSetting($idTp, $idSmt);

        if (!$kelas) {
            $_SESSION['flash_error'] = 'Kelas tidak ditemukan.';
            $this->redirect('/rapor/cetak');
            return;
        }

        $waliKelas = null;
        if (!empty($kelas['id_guru_wali'])) {
            $waliKelas = $this->guruModel->find($kelas['id_guru_wali']);
        }

        $recap = $this->calcService->calculateClassRecap($idTp, $idSmt, $idKelas);

        $this->render('rapor/cetak/dkn', [
            'sekolah' => $sekolah,
            'activeTp' => $activeTp,
            'activeSmt' => $activeSmt,
            'kelas' => $kelas,
            'waliKelas' => $waliKelas,
            'raporSetting' => $raporSetting,
            'mapelList' => $recap['mapel_list'],
            'studentsData' => $recap['students_data']
        ]);
    }

    /**
     * Export Excel DKN Kelas
     */
    public function exportDknExcel(int $idKelas): void
    {
        $this->requireRole(['admin', 'guru']);
        ['id_tp' => $idTp, 'id_smt' => $idSmt] = $this->getActiveTpSmt();

        $kelas = $this->kelasModel->find($idKelas);
        if (!$kelas) {
            $this->redirect('/rapor/cetak');
            return;
        }

        $recap = $this->calcService->calculateClassRecap($idTp, $idSmt, $idKelas);
        $mapelList = $recap['mapel_list'];
        $studentsData = $recap['students_data'];

        $filename = "DKN_Kelas_" . preg_replace('/[^a-zA-Z0-9_-]/', '_', $kelas['nama_kelas']) . "_" . date('Ymd_His') . ".csv";

        header('Content-Type: text/csv; charset=UTF-8');
        header("Content-Disposition: attachment; filename=\"{$filename}\"");
        header('Pragma: no-cache');
        header('Expires: 0');

        $out = fopen('php://output', 'w');
        // UTF-8 BOM
        fputs($out, "\xEF\xBB\xBF");

        // Header Baris
        $headers = ['No', 'NIS', 'Nama Siswa', 'L/P'];
        foreach ($mapelList as $m) {
            $headers[] = $m['kode_mapel'] ?? $m['nama_mapel'];
        }
        $headers[] = 'Total Nilai';
        $headers[] = 'Rata-Rata';
        $headers[] = 'Ranking';
        $headers[] = 'Sakit';
        $headers[] = 'Izin';
        $headers[] = 'Alfa';
        $headers[] = 'Status Kenaikan';

        fputcsv($out, $headers);

        $no = 1;
        foreach ($studentsData as $row) {
            $s = $row['siswa'];
            $line = [
                $no++,
                " " . ($s['nis'] ?? ''), // prefix space prevents Excel scientific notation
                $s['nama'],
                $s['jenis_kelamin'] ?? '-'
            ];

            foreach ($mapelList as $m) {
                $idMapel = (int)$m['id_mapel'];
                $sc = $row['scores'][$idMapel]['akhir'] ?? '-';
                $line[] = $sc;
            }

            $line[] = number_format($row['total_nilai'], 1);
            $line[] = number_format($row['rata_rata'], 2);
            $line[] = $row['ranking'];
            $line[] = $row['absensi']['sakit'] ?? 0;
            $line[] = $row['absensi']['izin'] ?? 0;
            $line[] = $row['absensi']['alfa'] ?? 0;
            $line[] = ($row['status_naik'] === 1 ? 'Naik / Lulus' : 'Tinggal / Tidak Lulus');

            fputcsv($out, $line);
        }

        fclose($out);
        exit;
    }
}
