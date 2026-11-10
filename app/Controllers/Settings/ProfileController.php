<?php

namespace App\Controllers\Settings;

use App\Core\Controller;
use App\Models\SettingSekolah;

class ProfileController extends Controller
{
    private SettingSekolah $sekolahModel;

    public function __construct($request, $response)
    {
        parent::__construct($request, $response);
        $this->sekolahModel = new SettingSekolah();
    }

    public function index(): void
    {
        $school = $this->sekolahModel->getActive();

        $this->render('settings/profile', [
            'title' => 'Profil Sekolah — DADO CBT',
            'school' => $school,
        ]);
    }

    public function update(): void
    {
        $school = $this->sekolahModel->getActive();
        $idSetting = (int)($school['id_setting'] ?? 1);

        $namaSekolah = trim((string)$this->request->input('nama_sekolah', ''));
        $npsn = trim((string)$this->request->input('npsn', ''));
        $jenjang = (int)$this->request->input('jenjang', 2);
        $satuanPendidikan = trim((string)$this->request->input('satuan_pendidikan', 'SMP'));
        $alamat = trim((string)$this->request->input('alamat', ''));
        $desa = trim((string)$this->request->input('desa', ''));
        $kecamatan = trim((string)$this->request->input('kecamatan', ''));
        $kota = trim((string)$this->request->input('kota', ''));
        $provinsi = trim((string)$this->request->input('provinsi', ''));
        $kodePos = trim((string)$this->request->input('kode_pos', ''));
        $telp = trim((string)$this->request->input('telp', ''));
        $email = trim((string)$this->request->input('email', ''));
        $web = trim((string)$this->request->input('web', ''));
        $kepsek = trim((string)$this->request->input('kepsek', ''));
        $nipKepsek = trim((string)$this->request->input('nip_kepsek', ''));

        if (empty($namaSekolah)) {
            $this->setFlash('error', 'Nama Sekolah tidak boleh kosong.');
            $this->redirect('/settings/profile');
            return;
        }

        $uploadDir = ROOT_PATH . '/public/uploads/sekolah';
        if (!is_dir($uploadDir)) {
            mkdir($uploadDir, 0777, true);
        }

        $logoKiri = $school['logo_kiri'] ?? null;
        $logoKanan = $school['logo_kanan'] ?? null;
        $tandaTangan = $school['tanda_tangan'] ?? null;

        // Upload Logo Kiri
        $fileLogoKiri = $this->request->file('logo_kiri');
        if ($fileLogoKiri && !empty($fileLogoKiri['tmp_name']) && $fileLogoKiri['error'] === UPLOAD_ERR_OK) {
            $ext = strtolower(pathinfo($fileLogoKiri['name'], PATHINFO_EXTENSION));
            if (in_array($ext, ['png', 'jpg', 'jpeg', 'webp'], true)) {
                $filename = 'logo_kiri_' . time() . '.' . $ext;
                if (move_uploaded_file($fileLogoKiri['tmp_name'], $uploadDir . '/' . $filename)) {
                    $logoKiri = 'sekolah/' . $filename;
                }
            }
        }

        // Upload Logo Kanan
        $fileLogoKanan = $this->request->file('logo_kanan');
        if ($fileLogoKanan && !empty($fileLogoKanan['tmp_name']) && $fileLogoKanan['error'] === UPLOAD_ERR_OK) {
            $ext = strtolower(pathinfo($fileLogoKanan['name'], PATHINFO_EXTENSION));
            if (in_array($ext, ['png', 'jpg', 'jpeg', 'webp'], true)) {
                $filename = 'logo_kanan_' . time() . '.' . $ext;
                if (move_uploaded_file($fileLogoKanan['tmp_name'], $uploadDir . '/' . $filename)) {
                    $logoKanan = 'sekolah/' . $filename;
                }
            }
        }

        // Upload Tanda Tangan Kepsek
        $fileTtd = $this->request->file('tanda_tangan');
        if ($fileTtd && !empty($fileTtd['tmp_name']) && $fileTtd['error'] === UPLOAD_ERR_OK) {
            $ext = strtolower(pathinfo($fileTtd['name'], PATHINFO_EXTENSION));
            if (in_array($ext, ['png', 'jpg', 'jpeg', 'webp'], true)) {
                $filename = 'ttd_kepsek_' . time() . '.' . $ext;
                if (move_uploaded_file($fileTtd['tmp_name'], $uploadDir . '/' . $filename)) {
                    $tandaTangan = 'sekolah/' . $filename;
                }
            }
        }

        $this->sekolahModel->update($idSetting, [
            'nama_sekolah' => $namaSekolah,
            'npsn' => $npsn,
            'jenjang' => $jenjang,
            'satuan_pendidikan' => $satuanPendidikan,
            'alamat' => $alamat,
            'desa' => $desa,
            'kecamatan' => $kecamatan,
            'kota' => $kota,
            'provinsi' => $provinsi,
            'kode_pos' => $kodePos,
            'telp' => $telp,
            'email' => $email,
            'web' => $web,
            'kepsek' => $kepsek,
            'nip_kepsek' => $nipKepsek,
            'logo_kiri' => $logoKiri,
            'logo_kanan' => $logoKanan,
            'tanda_tangan' => $tandaTangan,
        ]);

        $this->setFlash('success', 'Profil Sekolah dan identitas lembaga berhasil disimpan.');
        $this->redirect('/settings/profile');
    }
}

