<?php

namespace App\Controllers\Master;

use App\Core\Controller;
use App\Core\Database;
use App\Models\KelasSiswa;
use App\Models\MasterKelas;
use App\Models\MasterSiswa;
use App\Models\User;

class SiswaController extends Controller
{
    private MasterSiswa $siswaModel;
    private MasterKelas $kelasModel;
    private KelasSiswa $kelasSiswaModel;
    private User $userModel;

    public function __construct($request, $response)
    {
        parent::__construct($request, $response);
        $this->siswaModel = new MasterSiswa();
        $this->kelasModel = new MasterKelas();
        $this->kelasSiswaModel = new KelasSiswa();
        $this->userModel = new User();
    }

    public function index(): void
    {
        $tp = active_tp();
        $smt = active_smt();
        $tpId = (int)($tp['id_tp'] ?? 0);
        $smtId = (int)($smt['id_smt'] ?? 0);

        $sql = "SELECT s.*, k.nama_kelas, u.active as user_active
                FROM master_siswa s
                LEFT JOIN users u ON u.id = s.id_user
                LEFT JOIN kelas_siswa ks ON ks.id_siswa = s.id_siswa AND ks.id_tp = ? AND ks.id_smt = ?
                LEFT JOIN master_kelas k ON k.id_kelas = ks.id_kelas
                ORDER BY s.status ASC, s.nama ASC";

        $students = $this->siswaModel->query($sql, [$tpId, $smtId]);
        $kelasList = $this->kelasModel->getByPeriod($tpId, $smtId);

        $this->render('master/siswa/index', [
            'title' => 'Data Siswa — DADO CBT',
            'students' => $students,
            'kelasList' => $kelasList,
            'tp' => $tp,
            'smt' => $smt,
        ]);
    }

    public function store(): void
    {
        $nis = trim((string)$this->request->input('nis', ''));
        $nama = trim((string)$this->request->input('nama', ''));
        $nisn = trim((string)$this->request->input('nisn', ''));
        $jk = $this->request->input('jk') === 'P' ? 'P' : 'L';
        $tempatLahir = trim((string)$this->request->input('tempat_lahir', ''));
        $tglLahir = $this->request->input('tgl_lahir') ?: null;
        $agama = trim((string)$this->request->input('agama', 'Islam'));
        $alamat = trim((string)$this->request->input('alamat', ''));
        $namaAyah = trim((string)$this->request->input('nama_ayah', ''));
        $namaIbu = trim((string)$this->request->input('nama_ibu', ''));
        $noHp = trim((string)$this->request->input('no_hp', ''));
        $tahunMasuk = trim((string)$this->request->input('tahun_masuk', date('Y')));
        $kelasAwal = trim((string)$this->request->input('kelas_awal', ''));
        $idKelas = (int)$this->request->input('id_kelas', 0);

        if (empty($nis) || empty($nama)) {
            $this->setFlash('error', 'NIS dan Nama Siswa wajib diisi.');
            $this->redirect('/master/siswa');
            return;
        }

        // Check duplicate NIS
        $exist = $this->siswaModel->findByNis($nis);
        if ($exist) {
            $this->setFlash('error', "Siswa dengan NIS {$nis} sudah terdaftar.");
            $this->redirect('/master/siswa');
            return;
        }

        $db = Database::getInstance();
        $db->beginTransaction();

        try {
            // 1. Create user account
            $defaultPassword = password_hash('siswa123', PASSWORD_BCRYPT);
            $userId = $this->userModel->insert([
                'username' => $nis,
                'password' => $defaultPassword,
                'email' => null,
                'role' => 'siswa',
                'active' => 1,
            ]);

            // 2. Create student record
            $siswaId = $this->siswaModel->insert([
                'id_user' => $userId,
                'nis' => $nis,
                'nisn' => !empty($nisn) ? $nisn : null,
                'nama' => $nama,
                'jk' => $jk,
                'tempat_lahir' => $tempatLahir,
                'tgl_lahir' => $tglLahir,
                'agama' => $agama,
                'alamat' => $alamat,
                'nama_ayah' => $namaAyah,
                'nama_ibu' => $namaIbu,
                'no_hp' => $noHp,
                'tahun_masuk' => $tahunMasuk,
                'kelas_awal' => $kelasAwal,
                'foto' => null,
                'status' => 1,
            ]);

            // 3. Assign class if selected
            if ($idKelas > 0) {
                $tp = active_tp();
                $smt = active_smt();
                $this->kelasSiswaModel->addStudentToClass(
                    (int)($tp['id_tp'] ?? 0),
                    (int)($smt['id_smt'] ?? 0),
                    $idKelas,
                    (int)$siswaId
                );
            }

            $db->commit();
            $this->setFlash('success', "Siswa {$nama} (NIS: {$nis}) berhasil ditambahkan. Akun login dibuat otomatis dengan password default: siswa123");
        } catch (\Throwable $e) {
            $db->rollBack();
            $this->setFlash('error', 'Gagal menyimpan siswa: ' . $e->getMessage());
        }

        $this->redirect('/master/siswa');
    }

    public function update(string $id): void
    {
        $idSiswa = (int)$id;
        $siswa = $this->siswaModel->find($idSiswa);

        if (!$siswa) {
            $this->setFlash('error', 'Data siswa tidak ditemukan.');
            $this->redirect('/master/siswa');
            return;
        }

        $nis = trim((string)$this->request->input('nis', ''));
        $nama = trim((string)$this->request->input('nama', ''));
        $nisn = trim((string)$this->request->input('nisn', ''));
        $jk = $this->request->input('jk') === 'P' ? 'P' : 'L';
        $tempatLahir = trim((string)$this->request->input('tempat_lahir', ''));
        $tglLahir = $this->request->input('tgl_lahir') ?: null;
        $agama = trim((string)$this->request->input('agama', 'Islam'));
        $alamat = trim((string)$this->request->input('alamat', ''));
        $namaAyah = trim((string)$this->request->input('nama_ayah', ''));
        $namaIbu = trim((string)$this->request->input('nama_ibu', ''));
        $noHp = trim((string)$this->request->input('no_hp', ''));
        $status = (int)$this->request->input('status', 1);
        $idKelas = (int)$this->request->input('id_kelas', 0);

        $this->siswaModel->update($idSiswa, [
            'nis' => $nis,
            'nisn' => !empty($nisn) ? $nisn : null,
            'nama' => $nama,
            'jk' => $jk,
            'tempat_lahir' => $tempatLahir,
            'tgl_lahir' => $tglLahir,
            'agama' => $agama,
            'alamat' => $alamat,
            'nama_ayah' => $namaAyah,
            'nama_ibu' => $namaIbu,
            'no_hp' => $noHp,
            'status' => $status,
        ]);

        // Update class assignment
        $tp = active_tp();
        $smt = active_smt();
        $tpId = (int)($tp['id_tp'] ?? 0);
        $smtId = (int)($smt['id_smt'] ?? 0);

        if ($idKelas > 0) {
            $this->kelasSiswaModel->addStudentToClass($tpId, $smtId, $idKelas, $idSiswa);
        } else {
            $this->kelasSiswaModel->execute(
                "DELETE FROM kelas_siswa WHERE id_tp = ? AND id_smt = ? AND id_siswa = ?",
                [$tpId, $smtId, $idSiswa]
            );
        }

        $this->setFlash('success', "Data siswa {$nama} berhasil diperbarui.");
        $this->redirect('/master/siswa');
    }

    public function delete(string $id): void
    {
        $idSiswa = (int)$id;
        $siswa = $this->siswaModel->find($idSiswa);

        if ($siswa) {
            if (!empty($siswa['id_user'])) {
                $this->userModel->delete($siswa['id_user']);
            }
            $this->siswaModel->delete($idSiswa);
            $this->setFlash('success', "Data siswa {$siswa['nama']} dan akun user terkait berhasil dihapus.");
        }

        $this->redirect('/master/siswa');
    }

    /**
     * Download template CSV for Bulk Import
     */
    public function downloadTemplate(): void
    {
        header('Content-Type: text/csv; charset=utf-8');
        header('Content-Disposition: attachment; filename="Template_Import_Siswa.csv"');
        header('Pragma: no-cache');
        header('Expires: 0');

        $out = fopen('php://output', 'w');
        fwrite($out, "\xEF\xBB\xBF"); // UTF-8 BOM

        fputcsv($out, [
            'NIS', 'NISN', 'Nama_Lengkap', 'Jenis_Kelamin(L/P)', 'Tempat_Lahir',
            'Tanggal_Lahir(YYYY-MM-DD)', 'Agama', 'Alamat', 'Nama_Ayah', 'Nama_Ibu', 'No_HP', 'Tahun_Masuk'
        ]);

        // Sample row
        fputcsv($out, [
            '2026001', '0089123456', 'Ahmad Fadillah', 'L', 'Bandung',
            '2011-06-12', 'Islam', 'Jl. Merdeka No. 10 Bandung', 'Hendra', 'Nurhayati', '081234567890', '2026'
        ]);

        fclose($out);
        exit;
    }

    /**
     * Bulk Import Siswa via CSV
     */
    public function import(): void
    {
        $file = $this->request->file('file_csv');
        if (!$file || empty($file['tmp_name']) || $file['error'] !== UPLOAD_ERR_OK) {
            $this->setFlash('error', 'Silakan pilih berkas CSV / Excel yang valid.');
            $this->redirect('/master/siswa');
            return;
        }

        $handle = fopen($file['tmp_name'], 'r');
        if (!$handle) {
            $this->setFlash('error', 'Gagal membaca berkas unggahan.');
            $this->redirect('/master/siswa');
            return;
        }

        // Check BOM
        $bom = fread($handle, 3);
        if ($bom !== "\xEF\xBB\xBF") {
            rewind($handle);
        }

        $header = fgetcsv($handle);
        $imported = 0;
        $skipped = 0;

        $db = Database::getInstance();
        $defaultPassword = password_hash('siswa123', PASSWORD_BCRYPT);

        while (($row = fgetcsv($handle, 1000, ',')) !== false) {
            if (empty($row) || empty($row[0])) {
                continue;
            }

            $nis = trim($row[0]);
            $nisn = !empty($row[1]) ? trim($row[1]) : null;
            $nama = trim($row[2] ?? '');
            $jk = strtoupper(trim($row[3] ?? 'L')) === 'P' ? 'P' : 'L';
            $tempatLahir = trim($row[4] ?? '');
            $tglLahir = !empty($row[5]) ? trim($row[5]) : null;
            $agama = !empty($row[6]) ? trim($row[6]) : 'Islam';
            $alamat = trim($row[7] ?? '');
            $ayah = trim($row[8] ?? '');
            $ibu = trim($row[9] ?? '');
            $noHp = trim($row[10] ?? '');
            $tahunMasuk = !empty($row[11]) ? trim($row[11]) : date('Y');

            if (empty($nis) || empty($nama)) {
                $skipped++;
                continue;
            }

            // Check if exists
            $existing = $this->siswaModel->findByNis($nis);
            if ($existing) {
                $skipped++;
                continue;
            }

            $db->beginTransaction();
            try {
                $userId = $this->userModel->insert([
                    'username' => $nis,
                    'password' => $defaultPassword,
                    'email' => null,
                    'role' => 'siswa',
                    'active' => 1,
                ]);

                $this->siswaModel->insert([
                    'id_user' => $userId,
                    'nis' => $nis,
                    'nisn' => $nisn,
                    'nama' => $nama,
                    'jk' => $jk,
                    'tempat_lahir' => $tempatLahir,
                    'tgl_lahir' => $tglLahir,
                    'agama' => $agama,
                    'alamat' => $alamat,
                    'nama_ayah' => $ayah,
                    'nama_ibu' => $ibu,
                    'no_hp' => $noHp,
                    'tahun_masuk' => $tahunMasuk,
                    'status' => 1,
                ]);

                $db->commit();
                $imported++;
            } catch (\Throwable $e) {
                $db->rollBack();
                $skipped++;
            }
        }

        fclose($handle);
        $this->setFlash('success', "Proses import selesai. Berhasil menambahkan {$imported} siswa baru. (Dilewati/Duplikat: {$skipped}).");
        $this->redirect('/master/siswa');
    }

    /**
     * Halaman Update Foto Siswa Per Kelas
     */
    public function fotoPerKelas(): void
    {
        $tp = active_tp();
        $smt = active_smt();
        $tpId = (int)($tp['id_tp'] ?? 0);
        $smtId = (int)($smt['id_smt'] ?? 0);

        $kelasList = $this->kelasModel->getByPeriod($tpId, $smtId);
        $selectedKelasId = (int)$this->request->input('kelas_id', ($kelasList[0]['id_kelas'] ?? 0));

        $students = [];
        if ($selectedKelasId > 0) {
            $students = $this->kelasSiswaModel->getStudentsInClass($selectedKelasId, $tpId, $smtId);
        }

        $this->render('master/siswa/foto_per_kelas', [
            'title' => 'Update Foto Siswa Per Kelas — DADO CBT',
            'kelasList' => $kelasList,
            'selectedKelasId' => $selectedKelasId,
            'students' => $students,
            'tp' => $tp,
            'smt' => $smt,
        ]);
    }

    /**
     * Upload single student photo via AJAX or form
     */
    public function uploadFoto(): void
    {
        $idSiswa = (int)$this->request->input('id_siswa');
        $file = $this->request->file('foto');

        if (!$idSiswa || !$file || empty($file['tmp_name']) || $file['error'] !== UPLOAD_ERR_OK) {
            $this->json(['success' => false, 'message' => 'Berkas foto tidak valid.'], 400);
            return;
        }

        $siswa = $this->siswaModel->find($idSiswa);
        if (!$siswa) {
            $this->json(['success' => false, 'message' => 'Data siswa tidak ditemukan.'], 404);
            return;
        }

        $allowedExt = ['jpg', 'jpeg', 'png', 'webp'];
        $ext = strtolower(pathinfo($file['name'], PATHINFO_EXTENSION));

        if (!in_array($ext, $allowedExt, true)) {
            $this->json(['success' => false, 'message' => 'Format berkas harus JPG, JPEG, PNG, atau WEBP.'], 400);
            return;
        }

        // Upload folder
        $uploadDir = ROOT_PATH . '/public/uploads/foto_siswa';
        if (!is_dir($uploadDir)) {
            mkdir($uploadDir, 0777, true);
        }

        $newFilename = 'foto_siswa_' . $siswa['nis'] . '_' . time() . '.' . $ext;
        $destination = $uploadDir . '/' . $newFilename;

        if (move_uploaded_file($file['tmp_name'], $destination)) {
            // Delete old photo if exists
            if (!empty($siswa['foto']) && file_exists(ROOT_PATH . '/public/uploads/' . $siswa['foto'])) {
                @unlink(ROOT_PATH . '/public/uploads/' . $siswa['foto']);
            }

            $relativePath = 'foto_siswa/' . $newFilename;
            $this->siswaModel->update($idSiswa, ['foto' => $relativePath]);

            $this->json([
                'success' => true,
                'message' => 'Foto siswa berhasil diperbarui.',
                'foto_url' => upload_url($relativePath),
            ]);
        } else {
            $this->json(['success' => false, 'message' => 'Gagal memindahkan berkas unggahan.'], 500);
        }
    }
}

