<?php

namespace App\Controllers\Master;

use App\Config\Jenjang;
use App\Core\Controller;
use App\Models\MasterJurusan;
use App\Models\SettingSekolah;

class JurusanController extends Controller
{
    private MasterJurusan $jurusanModel;
    private SettingSekolah $sekolahModel;

    public function __construct($request, $response)
    {
        parent::__construct($request, $response);
        $this->jurusanModel = new MasterJurusan();
        $this->sekolahModel = new SettingSekolah();
    }

    private function checkJenjang(): bool
    {
        $school = $this->sekolahModel->getActive();
        $jenjang = (int)($school['jenjang'] ?? 2);
        if (!Jenjang::hasJurusan($jenjang)) {
            $this->setFlash('warning', 'Modul Jurusan dinonaktifkan karena jenjang sekolah saat ini adalah ' . Jenjang::getName($jenjang) . '.');
            $this->redirect('/dashboard');
            return false;
        }
        return true;
    }

    public function index(): void
    {
        if (!$this->checkJenjang()) {
            return;
        }

        $list = $this->jurusanModel->all('nama_jurusan ASC');

        $this->render('master/jurusan/index', [
            'title' => 'Jurusan & Program Keahlian — DADO CBT',
            'list' => $list,
        ]);
    }

    public function store(): void
    {
        if (!$this->checkJenjang()) {
            return;
        }

        $kode = strtoupper(trim((string)$this->request->input('kode_jurusan', '')));
        $nama = trim((string)$this->request->input('nama_jurusan', ''));

        if (empty($kode) || empty($nama)) {
            $this->setFlash('error', 'Kode dan Nama Jurusan wajib diisi.');
            $this->redirect('/master/jurusan');
            return;
        }

        $this->jurusanModel->insert([
            'kode_jurusan' => $kode,
            'nama_jurusan' => $nama,
            'status' => 1,
        ]);

        $this->setFlash('success', "Jurusan {$nama} ({$kode}) berhasil ditambahkan.");
        $this->redirect('/master/jurusan');
    }

    public function update(string $id): void
    {
        if (!$this->checkJenjang()) {
            return;
        }

        $idJurusan = (int)$id;
        $kode = strtoupper(trim((string)$this->request->input('kode_jurusan', '')));
        $nama = trim((string)$this->request->input('nama_jurusan', ''));
        $status = (int)$this->request->input('status', 1);

        $this->jurusanModel->update($idJurusan, [
            'kode_jurusan' => $kode,
            'nama_jurusan' => $nama,
            'status' => $status,
        ]);

        $this->setFlash('success', 'Data jurusan berhasil diperbarui.');
        $this->redirect('/master/jurusan');
    }

    public function delete(string $id): void
    {
        if (!$this->checkJenjang()) {
            return;
        }

        $idJurusan = (int)$id;
        $this->jurusanModel->delete($idJurusan);
        $this->setFlash('success', 'Jurusan berhasil dihapus.');
        $this->redirect('/master/jurusan');
    }
}
