<?php

namespace App\Controllers\Master;

use App\Core\Controller;
use App\Models\MasterEkstra;

class EkstraController extends Controller
{
    private MasterEkstra $ekstraModel;

    public function __construct($request, $response)
    {
        parent::__construct($request, $response);
        $this->ekstraModel = new MasterEkstra();
    }

    public function index(): void
    {
        $list = $this->ekstraModel->all('nama_ekstra ASC');

        $this->render('master/ekstra/index', [
            'title' => 'Ekstrakurikuler — DADO CBT',
            'list' => $list,
        ]);
    }

    public function store(): void
    {
        $nama = trim((string)$this->request->input('nama_ekstra', ''));
        $kode = strtoupper(trim((string)$this->request->input('kode_ekstra', '')));

        if (empty($nama) || empty($kode)) {
            $this->setFlash('error', 'Nama dan Kode Ekstrakurikuler wajib diisi.');
            $this->redirect('/master/ekstra');
            return;
        }

        $this->ekstraModel->insert([
            'nama_ekstra' => $nama,
            'kode_ekstra' => $kode,
        ]);

        $this->setFlash('success', "Ekstrakurikuler {$nama} ({$kode}) berhasil ditambahkan.");
        $this->redirect('/master/ekstra');
    }

    public function update(string $id): void
    {
        $idEkstra = (int)$id;
        $nama = trim((string)$this->request->input('nama_ekstra', ''));
        $kode = strtoupper(trim((string)$this->request->input('kode_ekstra', '')));

        $this->ekstraModel->update($idEkstra, [
            'nama_ekstra' => $nama,
            'kode_ekstra' => $kode,
        ]);

        $this->setFlash('success', 'Ekstrakurikuler berhasil diperbarui.');
        $this->redirect('/master/ekstra');
    }

    public function delete(string $id): void
    {
        $idEkstra = (int)$id;
        $this->ekstraModel->delete($idEkstra);
        $this->setFlash('success', 'Ekstrakurikuler berhasil dihapus.');
        $this->redirect('/master/ekstra');
    }
}

