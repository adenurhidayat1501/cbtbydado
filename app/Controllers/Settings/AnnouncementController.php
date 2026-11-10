<?php

namespace App\Controllers\Settings;

use App\Core\Controller;
use App\Models\RunningText;

class AnnouncementController extends Controller
{
    private RunningText $textModel;

    public function __construct($request, $response)
    {
        parent::__construct($request, $response);
        $this->textModel = new RunningText();
    }

    public function index(): void
    {
        $list = $this->textModel->all('id_text DESC');

        $this->render('settings/announcements', [
            'title' => 'Pengumuman Running Text — DADO CBT',
            'list' => $list,
        ]);
    }

    public function store(): void
    {
        $text = trim((string)$this->request->input('text', ''));
        $tujuan = $this->request->input('tujuan', 'semua');
        $active = (int)$this->request->input('active', 1);

        if (empty($text)) {
            $this->setFlash('error', 'Isi teks pengumuman tidak boleh kosong.');
            $this->redirect('/settings/announcements');
            return;
        }

        $this->textModel->insert([
            'text' => $text,
            'tujuan' => in_array($tujuan, ['guru', 'siswa', 'semua'], true) ? $tujuan : 'semua',
            'active' => $active,
        ]);

        $this->setFlash('success', 'Pengumuman baru berhasil ditambahkan.');
        $this->redirect('/settings/announcements');
    }

    public function update(string $id): void
    {
        $idText = (int)$id;
        $text = trim((string)$this->request->input('text', ''));
        $tujuan = $this->request->input('tujuan', 'semua');
        $active = (int)$this->request->input('active', 1);

        $this->textModel->update($idText, [
            'text' => $text,
            'tujuan' => in_array($tujuan, ['guru', 'siswa', 'semua'], true) ? $tujuan : 'semua',
            'active' => $active,
        ]);

        $this->setFlash('success', 'Pengumuman berhasil diperbarui.');
        $this->redirect('/settings/announcements');
    }

    public function toggle(string $id): void
    {
        $idText = (int)$id;
        $row = $this->textModel->find($idText);
        if ($row) {
            $newActive = (int)$row['active'] === 1 ? 0 : 1;
            $this->textModel->update($idText, ['active' => $newActive]);
            $this->setFlash('success', 'Status tayang pengumuman berhasil diubah.');
        }

        $this->redirect('/settings/announcements');
    }

    public function delete(string $id): void
    {
        $idText = (int)$id;
        $this->textModel->delete($idText);
        $this->setFlash('success', 'Pengumuman berhasil dihapus.');
        $this->redirect('/settings/announcements');
    }
}

