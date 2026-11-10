<?php

namespace App\Controllers\Rapor;

use App\Core\Controller;
use App\Models\RaporAdminSetting;
use App\Models\RaporKkm;
use App\Models\RaporKikd;
use App\Models\MasterTp;
use App\Models\MasterSmt;
use App\Models\MasterKelas;
use App\Models\MasterMapel;

class RaporSettingController extends Controller
{
    protected RaporAdminSetting $settingModel;
    protected RaporKkm $kkmModel;
    protected RaporKikd $kikdModel;
    protected MasterTp $tpModel;
    protected MasterSmt $smtModel;
    protected MasterKelas $kelasModel;
    protected MasterMapel $mapelModel;

    public function __construct()
    {
        $this->settingModel = new RaporAdminSetting();
        $this->kkmModel = new RaporKkm();
        $this->kikdModel = new RaporKikd();
        $this->tpModel = new MasterTp();
        $this->smtModel = new MasterSmt();
        $this->kelasModel = new MasterKelas();
        $this->mapelModel = new MasterMapel();
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
     * Halaman Pengaturan Rapor Umum & KKM Tunggal
     */
    public function index(): void
    {
        $this->requireRole('admin');
        ['id_tp' => $idTp, 'id_smt' => $idSmt] = $this->getActiveTpSmt();

        $setting = $this->settingModel->getSetting($idTp, $idSmt);
        $activeTp = $this->tpModel->getActive();
        $activeSmt = $this->smtModel->getActive();

        $this->render('rapor/setting/index', [
            'title' => 'Pengaturan E-Rapor & KKM',
            'setting' => $setting,
            'activeTp' => $activeTp,
            'activeSmt' => $activeSmt
        ]);
    }

    /**
     * Simpan Pengaturan Rapor Umum
     */
    public function save(): void
    {
        $this->requireRole('admin');
        ['id_tp' => $idTp, 'id_smt' => $idSmt] = $this->getActiveTpSmt();

        $saved = $this->settingModel->saveSetting($idTp, $idSmt, $_POST);
        if ($saved) {
            $_SESSION['flash_success'] = 'Pengaturan rapor berhasil disimpan.';
        } else {
            $_SESSION['flash_error'] = 'Gagal menyimpan pengaturan rapor.';
        }

        $this->redirect('/rapor/setting');
    }

    /**
     * Halaman KKM per Mapel & Beban Jam
     */
    public function kkm(): void
    {
        $this->requireRole(['admin', 'guru']);
        ['id_tp' => $idTp, 'id_smt' => $idSmt] = $this->getActiveTpSmt();

        $kelasList = $this->kelasModel->all('level ASC, nama_kelas ASC');
        $selectedKelas = isset($_GET['id_kelas']) ? (int)$_GET['id_kelas'] : ($kelasList[0]['id_kelas'] ?? 0);

        $mapelList = $this->mapelModel->all('urutan_tampil ASC, nama_mapel ASC');
        $kkmList = [];

        if ($selectedKelas > 0) {
            $existingKkm = $this->kkmModel->getByClass($idTp, $idSmt, $selectedKelas);
            $kkmMap = [];
            foreach ($existingKkm as $k) {
                $kkmMap[(int)$k['id_mapel']] = $k;
            }

            foreach ($mapelList as $m) {
                $idMapel = (int)$m['id_mapel'];
                $kkmList[] = [
                    'id_mapel' => $idMapel,
                    'nama_mapel' => $m['nama_mapel'],
                    'kode_mapel' => $m['kode'] ?? ($m['kode_mapel'] ?? ''),
                    'kelompok' => $m['kelompok'],
                    'kkm' => $kkmMap[$idMapel]['kkm'] ?? 75,
                    'bobot_ph' => $kkmMap[$idMapel]['bobot_ph'] ?? 50,
                    'bobot_pts' => $kkmMap[$idMapel]['bobot_pts'] ?? 25,
                    'bobot_pas' => $kkmMap[$idMapel]['bobot_pas'] ?? 25,
                    'beban_jam' => $kkmMap[$idMapel]['beban_jam'] ?? 2
                ];
            }
        }

        $this->render('rapor/setting/kkm', [
            'title' => 'KKM & Bobot per Mapel',
            'kelasList' => $kelasList,
            'selectedKelas' => $selectedKelas,
            'kkmList' => $kkmList
        ]);
    }

    /**
     * Simpan KKM per Mapel
     */
    public function kkmSave(): void
    {
        $this->requireRole(['admin', 'guru']);
        ['id_tp' => $idTp, 'id_smt' => $idSmt] = $this->getActiveTpSmt();

        $idKelas = (int)($_POST['id_kelas'] ?? 0);
        $kkmData = $_POST['kkm'] ?? [];

        if ($idKelas > 0 && is_array($kkmData)) {
            foreach ($kkmData as $idMapel => $row) {
                $this->kkmModel->saveKkm([
                    'id_tp' => $idTp,
                    'id_smt' => $idSmt,
                    'id_kelas' => $idKelas,
                    'id_mapel' => (int)$idMapel,
                    'kkm' => (int)($row['kkm'] ?? 75),
                    'bobot_ph' => (int)($row['bobot_ph'] ?? 50),
                    'bobot_pts' => (int)($row['bobot_pts'] ?? 25),
                    'bobot_pas' => (int)($row['bobot_pas'] ?? 25),
                    'beban_jam' => (int)($row['beban_jam'] ?? 2)
                ]);
            }
            $_SESSION['flash_success'] = 'Data KKM mapel kelas berhasil diperbarui.';
        } else {
            $_SESSION['flash_error'] = 'Data KKM tidak valid.';
        }

        $this->redirect("/rapor/kkm?id_kelas={$idKelas}");
    }

    /**
     * Halaman Pengaturan KI-3 dan KI-4 (KIKD)
     */
    public function kikd(): void
    {
        $this->requireRole(['admin', 'guru']);
        ['id_tp' => $idTp, 'id_smt' => $idSmt] = $this->getActiveTpSmt();

        $mapelList = $this->mapelModel->all('urutan_tampil ASC, nama_mapel ASC');
        $selectedMapel = isset($_GET['id_mapel']) ? (int)$_GET['id_mapel'] : ($mapelList[0]['id_mapel'] ?? 0);
        $selectedAspek = isset($_GET['aspek']) ? (int)$_GET['aspek'] : 1; // 1=KI-3, 2=KI-4

        $kikdList = [];
        if ($selectedMapel > 0) {
            $kikdList = $this->kikdModel->getByMapel($idTp, $idSmt, $selectedMapel, $selectedAspek);
        }

        $this->render('rapor/setting/kikd', [
            'title' => 'Kompetensi Inti & Dasar (KI/KD)',
            'mapelList' => $mapelList,
            'selectedMapel' => $selectedMapel,
            'selectedAspek' => $selectedAspek,
            'kikdList' => $kikdList
        ]);
    }

    /**
     * Simpan / Tambah Butir KI/KD
     */
    public function kikdSave(): void
    {
        $this->requireRole(['admin', 'guru']);
        ['id_tp' => $idTp, 'id_smt' => $idSmt] = $this->getActiveTpSmt();

        $idMapel = (int)($_POST['id_mapel_kelas'] ?? 0);
        $aspek = (int)($_POST['aspek'] ?? 1);

        $saved = $this->kikdModel->saveKikd([
            'id_kikd' => !empty($_POST['id_kikd']) ? (int)$_POST['id_kikd'] : null,
            'id_tp' => $idTp,
            'id_smt' => $idSmt,
            'id_mapel_kelas' => $idMapel,
            'aspek' => $aspek,
            'nomor_kikd' => $_POST['nomor_kikd'] ?? '',
            'isi_kikd' => $_POST['isi_kikd'] ?? ''
        ]);

        if ($saved) {
            $_SESSION['flash_success'] = 'Butir KI/KD berhasil disimpan.';
        } else {
            $_SESSION['flash_error'] = 'Gagal menyimpan butir KI/KD.';
        }

        $this->redirect("/rapor/kikd?id_mapel={$idMapel}&aspek={$aspek}");
    }

    /**
     * Hapus Butir KI/KD
     */
    public function kikdDelete(int $id): void
    {
        $this->requireRole(['admin', 'guru']);
        $item = $this->kikdModel->find($id);

        if ($item) {
            $idMapel = (int)$item['id_mapel_kelas'];
            $aspek = (int)$item['aspek'];
            $this->kikdModel->delete($id);
            $_SESSION['flash_success'] = 'Butir KI/KD berhasil dihapus.';
            $this->redirect("/rapor/kikd?id_mapel={$idMapel}&aspek={$aspek}");
            return;
        }

        $this->redirect('/rapor/kikd');
    }
}
