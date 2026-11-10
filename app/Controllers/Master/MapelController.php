<?php

namespace App\Controllers\Master;

use App\Core\Controller;
use App\Models\MasterMapel;
use App\Models\SettingSekolah;

class MapelController extends Controller
{
    private MasterMapel $mapelModel;
    private SettingSekolah $sekolahModel;

    public function __construct($request, $response)
    {
        parent::__construct($request, $response);
        $this->mapelModel = new MasterMapel();
        $this->sekolahModel = new SettingSekolah();
    }

    public function index(): void
    {
        $school = $this->sekolahModel->getActive();
        $jenjang = (int)($school['jenjang'] ?? 2);

        $filterKelompok = $this->request->input('kelompok');
        if (!empty($filterKelompok)) {
            $list = $this->mapelModel->where(['kelompok' => $filterKelompok], 'urutan_tampil ASC, nama_mapel ASC');
        } else {
            $list = $this->mapelModel->getAllOrdered();
        }

        $this->render('master/mapel/index', [
            'title' => 'Mata Pelajaran — DADO CBT',
            'list' => $list,
            'jenjang' => $jenjang,
            'filterKelompok' => $filterKelompok,
        ]);
    }

    public function store(): void
    {
        $nama = trim((string)$this->request->input('nama_mapel', ''));
        $kode = strtoupper(trim((string)$this->request->input('kode', '')));
        $kelompok = strtoupper(trim((string)$this->request->input('kelompok', 'A')));
        $bobot = (int)$this->request->input('bobot', 100);
        $urutan = (int)$this->request->input('urutan_tampil', 0);
        $jenjang = (int)$this->request->input('jenjang', 2);

        if (empty($nama) || empty($kode)) {
            $this->setFlash('error', 'Nama Mata Pelajaran dan Kode Mapel wajib diisi.');
            $this->redirect('/master/mapel');
            return;
        }

        $this->mapelModel->insert([
            'nama_mapel' => $nama,
            'kode' => $kode,
            'kelompok' => $kelompok,
            'bobot' => $bobot,
            'jenjang' => $jenjang,
            'status' => 1,
            'urutan_tampil' => $urutan,
        ]);

        $this->setFlash('success', "Mata Pelajaran {$nama} ({$kode}) berhasil ditambahkan.");
        $this->redirect('/master/mapel');
    }

    public function update(string $id): void
    {
        $idMapel = (int)$id;
        $nama = trim((string)$this->request->input('nama_mapel', ''));
        $kode = strtoupper(trim((string)$this->request->input('kode', '')));
        $kelompok = strtoupper(trim((string)$this->request->input('kelompok', 'A')));
        $bobot = (int)$this->request->input('bobot', 100);
        $urutan = (int)$this->request->input('urutan_tampil', 0);
        $status = (int)$this->request->input('status', 1);

        $this->mapelModel->update($idMapel, [
            'nama_mapel' => $nama,
            'kode' => $kode,
            'kelompok' => $kelompok,
            'bobot' => $bobot,
            'urutan_tampil' => $urutan,
            'status' => $status,
        ]);

        $this->setFlash('success', 'Mata Pelajaran berhasil diperbarui.');
        $this->redirect('/master/mapel');
    }

    public function delete(string $id): void
    {
        $idMapel = (int)$id;
        $this->mapelModel->delete($idMapel);
        $this->setFlash('success', 'Mata Pelajaran berhasil dihapus.');
        $this->redirect('/master/mapel');
    }
}

