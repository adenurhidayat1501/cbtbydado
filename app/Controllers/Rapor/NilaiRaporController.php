<?php

namespace App\Controllers\Rapor;

use App\Core\Controller;
use App\Models\RaporNilaiHarian;
use App\Models\RaporNilaiPts;
use App\Models\RaporNilaiAkhir;
use App\Models\RaporNilaiSikap;
use App\Models\RaporNilaiEkstra;
use App\Models\RaporCatatanWali;
use App\Models\RaporFisik;
use App\Models\RaporPrestasi;
use App\Models\RaporNaik;
use App\Models\MasterTp;
use App\Models\MasterSmt;
use App\Models\MasterKelas;
use App\Models\MasterMapel;
use App\Models\MasterEkstra;
use App\Services\RaporCalculatorService;

class NilaiRaporController extends Controller
{
    protected RaporNilaiHarian $harianModel;
    protected RaporNilaiPts $ptsModel;
    protected RaporNilaiAkhir $akhirModel;
    protected RaporNilaiSikap $sikapModel;
    protected RaporNilaiEkstra $ekstraNilaiModel;
    protected RaporCatatanWali $catatanModel;
    protected RaporFisik $fisikModel;
    protected RaporPrestasi $prestasiModel;
    protected RaporNaik $naikModel;

    protected MasterTp $tpModel;
    protected MasterSmt $smtModel;
    protected MasterKelas $kelasModel;
    protected MasterMapel $mapelModel;
    protected MasterEkstra $ekstraMasterModel;
    protected RaporCalculatorService $calcService;

    public function __construct()
    {
        $this->harianModel = new RaporNilaiHarian();
        $this->ptsModel = new RaporNilaiPts();
        $this->akhirModel = new RaporNilaiAkhir();
        $this->sikapModel = new RaporNilaiSikap();
        $this->ekstraNilaiModel = new RaporNilaiEkstra();
        $this->catatanModel = new RaporCatatanWali();
        $this->fisikModel = new RaporFisik();
        $this->prestasiModel = new RaporPrestasi();
        $this->naikModel = new RaporNaik();

        $this->tpModel = new MasterTp();
        $this->smtModel = new MasterSmt();
        $this->kelasModel = new MasterKelas();
        $this->mapelModel = new MasterMapel();
        $this->ekstraMasterModel = new MasterEkstra();
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
     * Dashboard Kumpulan Nilai Rapor
     */
    public function index(): void
    {
        $this->requireRole(['admin', 'guru']);
        ['id_tp' => $idTp, 'id_smt' => $idSmt] = $this->getActiveTpSmt();

        $kelasList = $this->kelasModel->all('level ASC, nama_kelas ASC');
        $mapelList = $this->mapelModel->all('urutan_tampil ASC, nama_mapel ASC');
        $ekstraList = $this->ekstraMasterModel->all('nama_ekstra ASC');

        $this->render('rapor/input/index', [
            'title' => 'Kumpulan Nilai Rapor',
            'kelasList' => $kelasList,
            'mapelList' => $mapelList,
            'ekstraList' => $ekstraList
        ]);
    }

    /**
     * Input Nilai Mapel (Pengetahuan, Keterampilan, PTS, PAS)
     */
    public function mapel(): void
    {
        $this->requireRole(['admin', 'guru']);
        ['id_tp' => $idTp, 'id_smt' => $idSmt] = $this->getActiveTpSmt();

        $kelasList = $this->kelasModel->all('level ASC, nama_kelas ASC');
        $mapelList = $this->mapelModel->all('urutan_tampil ASC, nama_mapel ASC');

        $selectedKelas = isset($_GET['id_kelas']) ? (int)$_GET['id_kelas'] : ($kelasList[0]['id_kelas'] ?? 0);
        $selectedMapel = isset($_GET['id_mapel']) ? (int)$_GET['id_mapel'] : ($mapelList[0]['id_mapel'] ?? 0);

        $students = [];
        $kkmSetting = ['kkm' => 75, 'bobot_ph' => 50, 'bobot_pts' => 25, 'bobot_pas' => 25];

        if ($selectedKelas > 0 && $selectedMapel > 0) {
            $kkmSetting = $this->calcService->getKkmValue($idTp, $idSmt, $selectedKelas, $selectedMapel);
            $harianRows = $this->harianModel->getByClassMapel($idTp, $idSmt, $selectedKelas, $selectedMapel);
            $ptsRows = $this->ptsModel->getByClassMapel($idTp, $idSmt, $selectedKelas, $selectedMapel);
            $akhirRows = $this->akhirModel->getByClassMapel($idTp, $idSmt, $selectedKelas, $selectedMapel);

            $ptsMap = [];
            foreach ($ptsRows as $pr) {
                $ptsMap[(int)$pr['id_siswa']] = $pr;
            }

            $akhirMap = [];
            foreach ($akhirRows as $ar) {
                $akhirMap[(int)$ar['id_siswa']] = $ar;
            }

            foreach ($harianRows as $hr) {
                $idSiswa = (int)$hr['id_siswa'];
                $pts = $ptsMap[$idSiswa]['nilai'] ?? null;
                $pas = $akhirMap[$idSiswa]['nilai'] ?? null;
                $na = $akhirMap[$idSiswa]['akhir'] ?? null;
                $pred = $akhirMap[$idSiswa]['predikat'] ?? null;

                $students[] = [
                    'id_siswa' => $idSiswa,
                    'nama_siswa' => $hr['nama_siswa'],
                    'nis' => $hr['nis'],
                    'p_rata_rata' => $hr['p_rata_rata'],
                    'p_predikat' => $hr['p_predikat'],
                    'k_rata_rata' => $hr['k_rata_rata'],
                    'k_predikat' => $hr['k_predikat'],
                    'pts' => $pts,
                    'pas' => $pas,
                    'akhir' => $na,
                    'predikat' => $pred
                ];
            }
        }

        $this->render('rapor/input/mapel', [
            'title' => 'Input Nilai Mata Pelajaran',
            'kelasList' => $kelasList,
            'mapelList' => $mapelList,
            'selectedKelas' => $selectedKelas,
            'selectedMapel' => $selectedMapel,
            'kkmSetting' => $kkmSetting,
            'students' => $students
        ]);
    }

    /**
     * Simpan Nilai Mapel Massal
     */
    public function mapelSave(): void
    {
        $this->requireRole(['admin', 'guru']);
        ['id_tp' => $idTp, 'id_smt' => $idSmt] = $this->getActiveTpSmt();

        $idKelas = (int)($_POST['id_kelas'] ?? 0);
        $idMapel = (int)($_POST['id_mapel'] ?? 0);
        $data = $_POST['nilai'] ?? [];

        if ($idKelas > 0 && $idMapel > 0 && is_array($data)) {
            $kkmSetting = $this->calcService->getKkmValue($idTp, $idSmt, $idKelas, $idMapel);
            $kkmVal = (float)$kkmSetting['kkm'];

            foreach ($data as $idSiswa => $row) {
                $idSiswa = (int)$idSiswa;

                $ph = isset($row['ph']) && is_numeric($row['ph']) ? (float)$row['ph'] : 0.0;
                $keterampilan = isset($row['k']) && is_numeric($row['k']) ? (float)$row['k'] : $ph;
                $pts = isset($row['pts']) && is_numeric($row['pts']) ? (float)$row['pts'] : null;
                $pas = isset($row['pas']) && is_numeric($row['pas']) ? (float)$row['pas'] : null;

                $phPred = $this->calcService->calculatePredicate($ph, $kkmVal);
                $kPred = $this->calcService->calculatePredicate($keterampilan, $kkmVal);
                $ptsPred = $pts !== null ? $this->calcService->calculatePredicate($pts, $kkmVal) : null;

                // Hitung Nilai Akhir NA
                $na = $this->calcService->calculateFinalScore($ph, $pts, $pas, $kkmSetting);
                $naPred = $this->calcService->calculatePredicate($na, $kkmVal);

                // Simpan Harian
                $this->harianModel->saveEntry([
                    'id_tp' => $idTp,
                    'id_smt' => $idSmt,
                    'id_kelas' => $idKelas,
                    'id_mapel' => $idMapel,
                    'id_siswa' => $idSiswa,
                    'p_rata_rata' => $ph,
                    'p_predikat' => $phPred,
                    'k_rata_rata' => $keterampilan,
                    'k_predikat' => $kPred
                ]);

                // Simpan PTS
                if ($pts !== null) {
                    $this->ptsModel->saveEntry([
                        'id_tp' => $idTp,
                        'id_smt' => $idSmt,
                        'id_kelas' => $idKelas,
                        'id_mapel' => $idMapel,
                        'id_siswa' => $idSiswa,
                        'nilai' => $pts,
                        'predikat' => $ptsPred
                    ]);
                }

                // Simpan PAS & Nilai Akhir
                $this->akhirModel->saveEntry([
                    'id_tp' => $idTp,
                    'id_smt' => $idSmt,
                    'id_kelas' => $idKelas,
                    'id_mapel' => $idMapel,
                    'id_siswa' => $idSiswa,
                    'nilai' => $pas,
                    'akhir' => $na,
                    'predikat' => $naPred
                ]);
            }

            $_SESSION['flash_success'] = 'Nilai rapor mata pelajaran berhasil disimpan.';
        } else {
            $_SESSION['flash_error'] = 'Parameter data nilai tidak valid.';
        }

        $this->redirect("/rapor/input-nilai/mapel?id_kelas={$idKelas}&id_mapel={$idMapel}");
    }

    /**
     * Sinkronisasi Otomatis dari E-Learning dan CBT
     */
    public function sync(): void
    {
        $this->requireRole(['admin', 'guru']);
        ['id_tp' => $idTp, 'id_smt' => $idSmt] = $this->getActiveTpSmt();

        $idKelas = (int)($_POST['id_kelas'] ?? 0);
        $idMapel = (int)($_POST['id_mapel'] ?? 0);

        if ($idKelas <= 0 || $idMapel <= 0) {
            $this->json(['success' => false, 'message' => 'Kelas dan Mapel wajib dipilih.'], 400);
            return;
        }

        $elearningCount = $this->calcService->syncElearningGrades($idTp, $idSmt, $idKelas, $idMapel);
        $cbtResult = $this->calcService->syncCbtGrades($idTp, $idSmt, $idKelas, $idMapel);

        $msg = "Sinkronisasi berhasil! {$elearningCount} nilai tugas E-Learning, {$cbtResult['pts']} nilai PTS, dan {$cbtResult['pas']} nilai PAS berhasil ditarik.";

        if (!empty($_SERVER['HTTP_X_REQUESTED_WITH']) && strtolower($_SERVER['HTTP_X_REQUESTED_WITH']) === 'xmlhttprequest') {
            $this->json(['success' => true, 'message' => $msg]);
            return;
        }

        $_SESSION['flash_success'] = $msg;
        $this->redirect("/rapor/input-nilai/mapel?id_kelas={$idKelas}&id_mapel={$idMapel}");
    }

    /**
     * Import Nilai via Format CSV / Excel Tab-Delimited
     */
    public function importExcel(): void
    {
        $this->requireRole(['admin', 'guru']);
        ['id_tp' => $idTp, 'id_smt' => $idSmt] = $this->getActiveTpSmt();

        $idKelas = (int)($_POST['id_kelas'] ?? 0);
        $idMapel = (int)($_POST['id_mapel'] ?? 0);

        if ($idKelas <= 0 || $idMapel <= 0 || empty($_FILES['excel_file']['tmp_name'])) {
            $_SESSION['flash_error'] = 'Pilih kelas, mata pelajaran, dan upload file nilai CSV/Excel.';
            $this->redirect("/rapor/input-nilai/mapel?id_kelas={$idKelas}&id_mapel={$idMapel}");
            return;
        }

        $filePath = $_FILES['excel_file']['tmp_name'];
        $handle = fopen($filePath, 'r');
        if (!$handle) {
            $_SESSION['flash_error'] = 'Gagal membaca file upload.';
            $this->redirect("/rapor/input-nilai/mapel?id_kelas={$idKelas}&id_mapel={$idMapel}");
            return;
        }

        $kkmSetting = $this->calcService->getKkmValue($idTp, $idSmt, $idKelas, $idMapel);
        $kkmVal = (float)$kkmSetting['kkm'];
        $importedCount = 0;

        $header = fgetcsv($handle, 1000, ',');
        if (!$header || count($header) < 2) {
            // Coba delimiter titik koma
            rewind($handle);
            $header = fgetcsv($handle, 1000, ';');
        }

        // Cari index kolom
        // Format diharapkan: NIS, NAMA, PH/PENGETAHUAN, KETERAMPILAN, PTS, PAS
        while (($row = fgetcsv($handle, 1000, ',')) !== false || ($row = fgetcsv($handle, 1000, ';')) !== false) {
            if (empty($row) || count($row) < 3) {
                continue;
            }

            $nis = trim($row[0] ?? '');
            if (empty($nis) || !is_numeric($nis)) {
                continue;
            }

            // Cari ID Siswa berdasarkan NIS
            $stmtSiswa = $this->tpModel->query("SELECT id_siswa FROM master_siswa WHERE nis = ? LIMIT 1", [$nis]);
            $s = $stmtSiswa->fetch();
            if (!$s) {
                continue;
            }
            $idSiswa = (int)$s['id_siswa'];

            $ph = isset($row[2]) && is_numeric(trim($row[2])) ? (float)trim($row[2]) : 75.0;
            $keterampilan = isset($row[3]) && is_numeric(trim($row[3])) ? (float)trim($row[3]) : $ph;
            $pts = isset($row[4]) && is_numeric(trim($row[4])) ? (float)trim($row[4]) : null;
            $pas = isset($row[5]) && is_numeric(trim($row[5])) ? (float)trim($row[5]) : null;

            $phPred = $this->calcService->calculatePredicate($ph, $kkmVal);
            $kPred = $this->calcService->calculatePredicate($keterampilan, $kkmVal);
            $ptsPred = $pts !== null ? $this->calcService->calculatePredicate($pts, $kkmVal) : null;

            $na = $this->calcService->calculateFinalScore($ph, $pts, $pas, $kkmSetting);
            $naPred = $this->calcService->calculatePredicate($na, $kkmVal);

            $this->harianModel->saveEntry([
                'id_tp' => $idTp,
                'id_smt' => $idSmt,
                'id_kelas' => $idKelas,
                'id_mapel' => $idMapel,
                'id_siswa' => $idSiswa,
                'p_rata_rata' => $ph,
                'p_predikat' => $phPred,
                'k_rata_rata' => $keterampilan,
                'k_predikat' => $kPred
            ]);

            if ($pts !== null) {
                $this->ptsModel->saveEntry([
                    'id_tp' => $idTp,
                    'id_smt' => $idSmt,
                    'id_kelas' => $idKelas,
                    'id_mapel' => $idMapel,
                    'id_siswa' => $idSiswa,
                    'nilai' => $pts,
                    'predikat' => $ptsPred
                ]);
            }

            $this->akhirModel->saveEntry([
                'id_tp' => $idTp,
                'id_smt' => $idSmt,
                'id_kelas' => $idKelas,
                'id_mapel' => $idMapel,
                'id_siswa' => $idSiswa,
                'nilai' => $pas,
                'akhir' => $na,
                'predikat' => $naPred
            ]);

            $importedCount++;
        }

        fclose($handle);
        $_SESSION['flash_success'] = "Berhasil mengimpor {$importedCount} data nilai siswa.";
        $this->redirect("/rapor/input-nilai/mapel?id_kelas={$idKelas}&id_mapel={$idMapel}");
    }

    /**
     * Input Nilai Sikap Spiritual (jenis=1) & Sikap Sosial (jenis=2)
     */
    public function sikap(): void
    {
        $this->requireRole(['admin', 'guru']);
        ['id_tp' => $idTp, 'id_smt' => $idSmt] = $this->getActiveTpSmt();

        $kelasList = $this->kelasModel->all('level ASC, nama_kelas ASC');
        $selectedKelas = isset($_GET['id_kelas']) ? (int)$_GET['id_kelas'] : ($kelasList[0]['id_kelas'] ?? 0);
        $selectedJenis = isset($_GET['jenis']) ? (int)$_GET['jenis'] : 1; // 1=Spiritual, 2=Sosial

        $students = [];
        if ($selectedKelas > 0) {
            $students = $this->sikapModel->getByClassAndType($idTp, $idSmt, $selectedKelas, $selectedJenis);
        }

        $this->render('rapor/input/sikap', [
            'title' => 'Input Nilai Sikap Spiritual & Sosial',
            'kelasList' => $kelasList,
            'selectedKelas' => $selectedKelas,
            'selectedJenis' => $selectedJenis,
            'students' => $students
        ]);
    }

    /**
     * Simpan Nilai Sikap
     */
    public function sikapSave(): void
    {
        $this->requireRole(['admin', 'guru']);
        ['id_tp' => $idTp, 'id_smt' => $idSmt] = $this->getActiveTpSmt();

        $idKelas = (int)($_POST['id_kelas'] ?? 0);
        $jenis = (int)($_POST['jenis'] ?? 1);
        $data = $_POST['sikap'] ?? [];

        if ($idKelas > 0 && is_array($data)) {
            foreach ($data as $idSiswa => $row) {
                $this->sikapModel->saveEntry([
                    'id_tp' => $idTp,
                    'id_smt' => $idSmt,
                    'id_kelas' => $idKelas,
                    'id_siswa' => (int)$idSiswa,
                    'jenis' => $jenis,
                    'nilai' => $row['nilai'] ?? ['predikat' => 'B'],
                    'deskripsi' => $row['deskripsi'] ?? 'Menunjukkan sikap yang baik.'
                ]);
            }
            $_SESSION['flash_success'] = 'Nilai sikap berhasil disimpan.';
        }

        $this->redirect("/rapor/input-nilai/sikap?id_kelas={$idKelas}&jenis={$jenis}");
    }

    /**
     * Input Nilai Ekstrakurikuler
     */
    public function ekstra(): void
    {
        $this->requireRole(['admin', 'guru']);
        ['id_tp' => $idTp, 'id_smt' => $idSmt] = $this->getActiveTpSmt();

        $kelasList = $this->kelasModel->all('level ASC, nama_kelas ASC');
        $ekstraList = $this->ekstraMasterModel->all('nama_ekstra ASC');

        $selectedKelas = isset($_GET['id_kelas']) ? (int)$_GET['id_kelas'] : ($kelasList[0]['id_kelas'] ?? 0);
        $selectedEkstra = isset($_GET['id_ekstra']) ? (int)$_GET['id_ekstra'] : ($ekstraList[0]['id_ekstra'] ?? 0);

        $students = [];
        if ($selectedKelas > 0 && $selectedEkstra > 0) {
            $students = $this->ekstraNilaiModel->getByClassAndEkstra($idTp, $idSmt, $selectedKelas, $selectedEkstra);
        }

        $this->render('rapor/input/ekstra', [
            'title' => 'Input Nilai Ekstrakurikuler',
            'kelasList' => $kelasList,
            'ekstraList' => $ekstraList,
            'selectedKelas' => $selectedKelas,
            'selectedEkstra' => $selectedEkstra,
            'students' => $students
        ]);
    }

    /**
     * Simpan Nilai Ekstrakurikuler
     */
    public function ekstraSave(): void
    {
        $this->requireRole(['admin', 'guru']);
        ['id_tp' => $idTp, 'id_smt' => $idSmt] = $this->getActiveTpSmt();

        $idKelas = (int)($_POST['id_kelas'] ?? 0);
        $idEkstra = (int)($_POST['id_ekstra'] ?? 0);
        $data = $_POST['ekstra'] ?? [];

        if ($idKelas > 0 && $idEkstra > 0 && is_array($data)) {
            foreach ($data as $idSiswa => $row) {
                if (!empty($row['nilai'])) {
                    $this->ekstraNilaiModel->saveEntry([
                        'id_tp' => $idTp,
                        'id_smt' => $idSmt,
                        'id_ekstra' => $idEkstra,
                        'id_kelas' => $idKelas,
                        'id_siswa' => (int)$idSiswa,
                        'nilai' => $row['nilai'],
                        'predikat' => $row['predikat'] ?? $row['nilai'],
                        'deskripsi' => $row['deskripsi'] ?? 'Aktif mengikuti kegiatan ekstrakurikuler dengan baik.'
                    ]);
                }
            }
            $_SESSION['flash_success'] = 'Nilai ekstrakurikuler berhasil disimpan.';
        }

        $this->redirect("/rapor/input-nilai/ekstra?id_kelas={$idKelas}&id_ekstra={$idEkstra}");
    }

    /**
     * Input Catatan Wali Kelas & Presensi (Sakit, Izin, Alfa)
     */
    public function catatan(): void
    {
        $this->requireRole(['admin', 'guru']);
        ['id_tp' => $idTp, 'id_smt' => $idSmt] = $this->getActiveTpSmt();

        $kelasList = $this->kelasModel->all('level ASC, nama_kelas ASC');
        $selectedKelas = isset($_GET['id_kelas']) ? (int)$_GET['id_kelas'] : ($kelasList[0]['id_kelas'] ?? 0);

        $students = [];
        if ($selectedKelas > 0) {
            $students = $this->catatanModel->getByClass($idTp, $idSmt, $selectedKelas);
        }

        $this->render('rapor/input/catatan', [
            'title' => 'Catatan Wali Kelas & Presensi',
            'kelasList' => $kelasList,
            'selectedKelas' => $selectedKelas,
            'students' => $students
        ]);
    }

    /**
     * Simpan Catatan Wali Kelas & Presensi
     */
    public function catatanSave(): void
    {
        $this->requireRole(['admin', 'guru']);
        ['id_tp' => $idTp, 'id_smt' => $idSmt] = $this->getActiveTpSmt();

        $idKelas = (int)($_POST['id_kelas'] ?? 0);
        $data = $_POST['catatan'] ?? [];

        if ($idKelas > 0 && is_array($data)) {
            foreach ($data as $idSiswa => $row) {
                $this->catatanModel->saveEntry([
                    'id_tp' => $idTp,
                    'id_smt' => $idSmt,
                    'id_kelas' => $idKelas,
                    'id_siswa' => (int)$idSiswa,
                    'sakit' => (int)($row['sakit'] ?? 0),
                    'izin' => (int)($row['izin'] ?? 0),
                    'alfa' => (int)($row['alfa'] ?? 0),
                    'deskripsi' => $row['deskripsi'] ?? ''
                ]);
            }
            $_SESSION['flash_success'] = 'Catatan wali kelas dan presensi berhasil disimpan.';
        }

        $this->redirect("/rapor/input-nilai/catatan?id_kelas={$idKelas}");
    }

    /**
     * Input Kesehatan & Fisik Siswa
     */
    public function fisik(): void
    {
        $this->requireRole(['admin', 'guru']);
        ['id_tp' => $idTp, 'id_smt' => $idSmt] = $this->getActiveTpSmt();

        $kelasList = $this->kelasModel->all('level ASC, nama_kelas ASC');
        $selectedKelas = isset($_GET['id_kelas']) ? (int)$_GET['id_kelas'] : ($kelasList[0]['id_kelas'] ?? 0);

        $students = [];
        if ($selectedKelas > 0) {
            $students = $this->fisikModel->getByClass($idTp, $idSmt, $selectedKelas);
        }

        $this->render('rapor/input/fisik', [
            'title' => 'Kondisi Kesehatan & Fisik Siswa',
            'kelasList' => $kelasList,
            'selectedKelas' => $selectedKelas,
            'students' => $students
        ]);
    }

    /**
     * Simpan Kesehatan & Fisik Siswa
     */
    public function fisikSave(): void
    {
        $this->requireRole(['admin', 'guru']);
        ['id_tp' => $idTp, 'id_smt' => $idSmt] = $this->getActiveTpSmt();

        $idKelas = (int)($_POST['id_kelas'] ?? 0);
        $data = $_POST['fisik'] ?? [];

        if ($idKelas > 0 && is_array($data)) {
            foreach ($data as $idSiswa => $row) {
                $this->fisikModel->saveEntry([
                    'id_tp' => $idTp,
                    'id_smt' => $idSmt,
                    'id_kelas' => $idKelas,
                    'id_siswa' => (int)$idSiswa,
                    'tinggi' => $row['tinggi'] ?? null,
                    'berat' => $row['berat'] ?? null,
                    'pendengaran' => $row['pendengaran'] ?? 'Baik',
                    'penglihatan' => $row['penglihatan'] ?? 'Baik',
                    'gigi' => $row['gigi'] ?? 'Baik',
                    'lainnya' => $row['lainnya'] ?? ''
                ]);
            }
            $_SESSION['flash_success'] = 'Data kesehatan dan fisik siswa berhasil disimpan.';
        }

        $this->redirect("/rapor/input-nilai/fisik?id_kelas={$idKelas}");
    }

    /**
     * Input Prestasi & Ranking Siswa
     */
    public function prestasi(): void
    {
        $this->requireRole(['admin', 'guru']);
        ['id_tp' => $idTp, 'id_smt' => $idSmt] = $this->getActiveTpSmt();

        $kelasList = $this->kelasModel->all('level ASC, nama_kelas ASC');
        $selectedKelas = isset($_GET['id_kelas']) ? (int)$_GET['id_kelas'] : ($kelasList[0]['id_kelas'] ?? 0);

        $students = [];
        if ($selectedKelas > 0) {
            $students = $this->prestasiModel->getByClass($idTp, $idSmt, $selectedKelas);
        }

        $this->render('rapor/input/prestasi', [
            'title' => 'Prestasi & Penghargaan Siswa',
            'kelasList' => $kelasList,
            'selectedKelas' => $selectedKelas,
            'students' => $students
        ]);
    }

    /**
     * Simpan Prestasi Siswa
     */
    public function prestasiSave(): void
    {
        $this->requireRole(['admin', 'guru']);
        ['id_tp' => $idTp, 'id_smt' => $idSmt] = $this->getActiveTpSmt();

        $idKelas = (int)($_POST['id_kelas'] ?? 0);
        $data = $_POST['prestasi'] ?? [];

        if ($idKelas > 0 && is_array($data)) {
            foreach ($data as $idSiswa => $row) {
                $this->prestasiModel->saveEntry([
                    'id_tp' => $idTp,
                    'id_smt' => $idSmt,
                    'id_kelas' => $idKelas,
                    'id_siswa' => (int)$idSiswa,
                    'ranking' => $row['ranking'] ?? null,
                    'deskripsi_ranking' => $row['deskripsi_ranking'] ?? null,
                    'p1' => $row['p1'] ?? null,
                    'p1_desk' => $row['p1_desk'] ?? null,
                    'p2' => $row['p2'] ?? null,
                    'p2_desk' => $row['p2_desk'] ?? null,
                    'p3' => $row['p3'] ?? null,
                    'p3_desk' => $row['p3_desk'] ?? null
                ]);
            }
            $_SESSION['flash_success'] = 'Data prestasi siswa berhasil disimpan.';
        }

        $this->redirect("/rapor/input-nilai/prestasi?id_kelas={$idKelas}");
    }

    /**
     * Input Keputusan Kenaikan Kelas / Kelulusan
     */
    public function naik(): void
    {
        $this->requireRole(['admin', 'guru']);
        ['id_tp' => $idTp, 'id_smt' => $idSmt] = $this->getActiveTpSmt();

        $kelasList = $this->kelasModel->all('level ASC, nama_kelas ASC');
        $selectedKelas = isset($_GET['id_kelas']) ? (int)$_GET['id_kelas'] : ($kelasList[0]['id_kelas'] ?? 0);

        $students = [];
        if ($selectedKelas > 0) {
            $students = $this->naikModel->getByClass($idTp, $idSmt, $selectedKelas);
        }

        $this->render('rapor/input/naik', [
            'title' => 'Keputusan Kenaikan Kelas & Kelulusan',
            'kelasList' => $kelasList,
            'selectedKelas' => $selectedKelas,
            'students' => $students
        ]);
    }

    /**
     * Simpan Keputusan Kenaikan Kelas
     */
    public function naikSave(): void
    {
        $this->requireRole(['admin', 'guru']);
        ['id_tp' => $idTp, 'id_smt' => $idSmt] = $this->getActiveTpSmt();

        $idKelas = (int)($_POST['id_kelas'] ?? 0);
        $data = $_POST['naik'] ?? [];

        if ($idKelas > 0 && is_array($data)) {
            foreach ($data as $idSiswa => $row) {
                $this->naikModel->saveEntry([
                    'id_tp' => $idTp,
                    'id_smt' => $idSmt,
                    'id_siswa' => (int)$idSiswa,
                    'naik' => isset($row['status']) ? (int)$row['status'] : 1,
                    'keterangan' => $row['keterangan'] ?? ''
                ]);
            }
            $_SESSION['flash_success'] = 'Keputusan kenaikan kelas / kelulusan berhasil disimpan.';
        }

        $this->redirect("/rapor/input-nilai/naik?id_kelas={$idKelas}");
    }
}
