<?php

namespace App\Controllers\Master;

use App\Core\Controller;
use App\Core\Database;
use App\Models\JabatanGuru;
use App\Models\MasterGuru;
use App\Models\MasterKelas;
use App\Models\MasterMapel;
use App\Models\SettingSekolah;
use App\Models\User;

class GuruController extends Controller
{
    private MasterGuru $guruModel;
    private JabatanGuru $jabatanModel;
    private MasterKelas $kelasModel;
    private MasterMapel $mapelModel;
    private User $userModel;
    private SettingSekolah $sekolahModel;

    public function __construct($request, $response)
    {
        parent::__construct($request, $response);
        $this->guruModel = new MasterGuru();
        $this->jabatanModel = new JabatanGuru();
        $this->kelasModel = new MasterKelas();
        $this->mapelModel = new MasterMapel();
        $this->userModel = new User();
        $this->sekolahModel = new SettingSekolah();
    }

    public function index(): void
    {
        $tp = active_tp();
        $smt = active_smt();
        $tpId = (int)($tp['id_tp'] ?? 0);
        $smtId = (int)($smt['id_smt'] ?? 0);

        $sql = "SELECT g.*, jg.id_jabatan, jg.id_kelas as wali_kelas_id, k.nama_kelas as wali_kelas_nama
                FROM master_guru g
                LEFT JOIN jabatan_guru jg ON jg.id_guru = g.id_guru AND jg.id_tp = ? AND jg.id_smt = ?
                LEFT JOIN master_kelas k ON k.id_kelas = jg.id_kelas
                ORDER BY g.status ASC, g.nama_guru ASC";

        $guruList = $this->guruModel->query($sql, [$tpId, $smtId]);
        $kelasList = $this->kelasModel->getByPeriod($tpId, $smtId);
        $mapelList = $this->mapelModel->all('nama_mapel ASC');

        $this->render('master/guru/index', [
            'title' => 'Data Guru & Pendidik — DADO CBT',
            'guruList' => $guruList,
            'kelasList' => $kelasList,
            'mapelList' => $mapelList,
            'tp' => $tp,
            'smt' => $smt,
        ]);
    }

    public function store(): void
    {
        $nip = trim((string)$this->request->input('nip', ''));
        $nama = trim((string)$this->request->input('nama_guru', ''));
        $kode = strtoupper(trim((string)$this->request->input('kode_guru', '')));
        $jk = $this->request->input('jk') === 'P' ? 'P' : 'L';
        $noHp = trim((string)$this->request->input('no_hp', ''));
        $alamat = trim((string)$this->request->input('alamat', ''));
        $idJabatan = (int)$this->request->input('id_jabatan', 3);
        $idKelas = (int)$this->request->input('id_kelas', 0) ?: null;

        if (empty($nama)) {
            $this->setFlash('error', 'Nama Guru wajib diisi.');
            $this->redirect('/master/guru');
            return;
        }

        // Determine username for login: NIP or Kode Guru or sanitized name
        $username = !empty($nip) ? $nip : (!empty($kode) ? $kode : strtolower(preg_replace('/[^a-zA-Z0-9]/', '', $nama)));

        // Handle profile photo upload
        $fotoPath = null;
        $fotoFile = $this->request->file('foto');
        if ($fotoFile && !empty($fotoFile['tmp_name']) && $fotoFile['error'] === UPLOAD_ERR_OK) {
            $uploadDir = ROOT_PATH . '/public/uploads/foto_guru';
            if (!is_dir($uploadDir)) {
                mkdir($uploadDir, 0777, true);
            }
            $ext = strtolower(pathinfo($fotoFile['name'], PATHINFO_EXTENSION));
            if (in_array($ext, ['jpg', 'jpeg', 'png', 'webp'], true)) {
                $filename = 'guru_' . time() . '_' . random_int(100, 999) . '.' . $ext;
                if (move_uploaded_file($fotoFile['tmp_name'], $uploadDir . '/' . $filename)) {
                    $fotoPath = 'foto_guru/' . $filename;
                }
            }
        }

        $db = Database::getInstance();
        $db->beginTransaction();

        try {
            // 1. Create user account
            $defaultPassword = password_hash('guru123', PASSWORD_BCRYPT);
            $userId = $this->userModel->insert([
                'username' => $username,
                'password' => $defaultPassword,
                'email' => null,
                'role' => 'guru',
                'active' => 1,
            ]);

            // 2. Create guru record
            $guruId = $this->guruModel->insert([
                'id_user' => $userId,
                'nip' => !empty($nip) ? $nip : null,
                'nama_guru' => $nama,
                'kode_guru' => !empty($kode) ? $kode : null,
                'jk' => $jk,
                'no_hp' => $noHp,
                'alamat' => $alamat,
                'foto' => $fotoPath,
                'id_jabatan' => $idJabatan,
                'status' => 1,
            ]);

            // 3. Set Jabatan in active period
            $tp = active_tp();
            $smt = active_smt();
            $this->jabatanModel->setJabatan(
                (int)$guruId,
                (int)($tp['id_tp'] ?? 0),
                (int)($smt['id_smt'] ?? 0),
                $idJabatan,
                $idKelas
            );

            $db->commit();
            $this->setFlash('success', "Data guru {$nama} berhasil ditambahkan. Akun login: {$username} (Password: guru123).");
        } catch (\Throwable $e) {
            $db->rollBack();
            $this->setFlash('error', 'Gagal menambahkan guru: ' . $e->getMessage());
        }

        $this->redirect('/master/guru');
    }

    public function update(string $id): void
    {
        $idGuru = (int)$id;
        $guru = $this->guruModel->find($idGuru);

        if (!$guru) {
            $this->setFlash('error', 'Data guru tidak ditemukan.');
            $this->redirect('/master/guru');
            return;
        }

        $nip = trim((string)$this->request->input('nip', ''));
        $nama = trim((string)$this->request->input('nama_guru', ''));
        $kode = strtoupper(trim((string)$this->request->input('kode_guru', '')));
        $jk = $this->request->input('jk') === 'P' ? 'P' : 'L';
        $noHp = trim((string)$this->request->input('no_hp', ''));
        $alamat = trim((string)$this->request->input('alamat', ''));
        $idJabatan = (int)$this->request->input('id_jabatan', 3);
        $idKelas = (int)$this->request->input('id_kelas', 0) ?: null;
        $status = (int)$this->request->input('status', 1);

        $fotoPath = $guru['foto'];
        $fotoFile = $this->request->file('foto');
        if ($fotoFile && !empty($fotoFile['tmp_name']) && $fotoFile['error'] === UPLOAD_ERR_OK) {
            $uploadDir = ROOT_PATH . '/public/uploads/foto_guru';
            if (!is_dir($uploadDir)) {
                mkdir($uploadDir, 0777, true);
            }
            $ext = strtolower(pathinfo($fotoFile['name'], PATHINFO_EXTENSION));
            if (in_array($ext, ['jpg', 'jpeg', 'png', 'webp'], true)) {
                $filename = 'guru_' . time() . '_' . random_int(100, 999) . '.' . $ext;
                if (move_uploaded_file($fotoFile['tmp_name'], $uploadDir . '/' . $filename)) {
                    if (!empty($fotoPath) && file_exists(ROOT_PATH . '/public/uploads/' . $fotoPath)) {
                        @unlink(ROOT_PATH . '/public/uploads/' . $fotoPath);
                    }
                    $fotoPath = 'foto_guru/' . $filename;
                }
            }
        }

        $this->guruModel->update($idGuru, [
            'nip' => !empty($nip) ? $nip : null,
            'nama_guru' => $nama,
            'kode_guru' => !empty($kode) ? $kode : null,
            'jk' => $jk,
            'no_hp' => $noHp,
            'alamat' => $alamat,
            'foto' => $fotoPath,
            'id_jabatan' => $idJabatan,
            'status' => $status,
        ]);

        $tp = active_tp();
        $smt = active_smt();
        $this->jabatanModel->setJabatan(
            $idGuru,
            (int)($tp['id_tp'] ?? 0),
            (int)($smt['id_smt'] ?? 0),
            $idJabatan,
            $idKelas
        );

        $this->setFlash('success', "Data guru {$nama} berhasil diperbarui.");
        $this->redirect('/master/guru');
    }

    public function delete(string $id): void
    {
        $idGuru = (int)$id;
        $guru = $this->guruModel->find($idGuru);

        if ($guru) {
            if (!empty($guru['id_user'])) {
                $this->userModel->delete($guru['id_user']);
            }
            if (!empty($guru['foto']) && file_exists(ROOT_PATH . '/public/uploads/' . $guru['foto'])) {
                @unlink(ROOT_PATH . '/public/uploads/' . $guru['foto']);
            }
            $this->guruModel->delete($idGuru);
            $this->setFlash('success', "Guru {$guru['nama_guru']} berhasil dihapus.");
        }

        $this->redirect('/master/guru');
    }

    /**
     * Download template CSV Guru
     */
    public function downloadTemplate(): void
    {
        header('Content-Type: text/csv; charset=utf-8');
        header('Content-Disposition: attachment; filename="Template_Import_Guru.csv"');
        header('Pragma: no-cache');
        header('Expires: 0');

        $out = fopen('php://output', 'w');
        fwrite($out, "\xEF\xBB\xBF");

        fputcsv($out, ['NIP', 'Nama_Lengkap_Beserta_Gelar', 'Kode_Guru', 'Jenis_Kelamin(L/P)', 'No_HP', 'Alamat']);
        fputcsv($out, ['198001012005011002', 'Budi Santoso, S.Pd., M.Kom.', 'G01', 'L', '081234567890', 'Jl. Sukajadi No. 12 Bandung']);

        fclose($out);
        exit;
    }

    /**
     * Bulk Import Guru via CSV
     */
    public function import(): void
    {
        $file = $this->request->file('file_csv');
        if (!$file || empty($file['tmp_name']) || $file['error'] !== UPLOAD_ERR_OK) {
            $this->setFlash('error', 'Silakan pilih berkas CSV yang valid.');
            $this->redirect('/master/guru');
            return;
        }

        $handle = fopen($file['tmp_name'], 'r');
        if (!$handle) {
            $this->setFlash('error', 'Gagal membaca berkas unggahan.');
            $this->redirect('/master/guru');
            return;
        }

        $bom = fread($handle, 3);
        if ($bom !== "\xEF\xBB\xBF") {
            rewind($handle);
        }

        fgetcsv($handle); // skip header
        $imported = 0;
        $skipped = 0;
        $defaultPassword = password_hash('guru123', PASSWORD_BCRYPT);
        $db = Database::getInstance();

        while (($row = fgetcsv($handle, 1000, ',')) !== false) {
            if (empty($row) || empty($row[1])) {
                continue;
            }

            $nip = trim($row[0] ?? '');
            $nama = trim($row[1] ?? '');
            $kode = strtoupper(trim($row[2] ?? ''));
            $jk = strtoupper(trim($row[3] ?? 'L')) === 'P' ? 'P' : 'L';
            $noHp = trim($row[4] ?? '');
            $alamat = trim($row[5] ?? '');

            if (empty($nama)) {
                $skipped++;
                continue;
            }

            $username = !empty($nip) ? $nip : (!empty($kode) ? $kode : strtolower(preg_replace('/[^a-zA-Z0-9]/', '', $nama)));

            $db->beginTransaction();
            try {
                $userId = $this->userModel->insert([
                    'username' => $username,
                    'password' => $defaultPassword,
                    'email' => null,
                    'role' => 'guru',
                    'active' => 1,
                ]);

                $this->guruModel->insert([
                    'id_user' => $userId,
                    'nip' => !empty($nip) ? $nip : null,
                    'nama_guru' => $nama,
                    'kode_guru' => !empty($kode) ? $kode : null,
                    'jk' => $jk,
                    'no_hp' => $noHp,
                    'alamat' => $alamat,
                    'foto' => null,
                    'id_jabatan' => 3,
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
        $this->setFlash('success', "Import selesai. Berhasil menambahkan {$imported} guru. (Dilewati: {$skipped}).");
        $this->redirect('/master/guru');
    }

    /**
     * Visualisasi Struktur Organisasi
     */
    public function strukturOrganisasi(): void
    {
        $tp = active_tp();
        $smt = active_smt();
        $tpId = (int)($tp['id_tp'] ?? 0);
        $smtId = (int)($smt['id_smt'] ?? 0);

        $school = $this->sekolahModel->getActive();

        // 1. Kepsek
        $kepsek = $this->guruModel->first(['id_jabatan' => 1]);

        // 2. Wali Kelas
        $sqlWali = "SELECT k.nama_kelas, k.level_id, g.nama_guru, g.nip, g.foto
                    FROM master_kelas k
                    JOIN master_guru g ON g.id_guru = k.guru_id
                    WHERE k.id_tp = ? AND k.id_smt = ?
                    ORDER BY k.level_id ASC, k.nama_kelas ASC";
        $waliList = $this->guruModel->query($sqlWali, [$tpId, $smtId]);

        // 3. Guru Mapel
        $guruMapel = $this->guruModel->where(['status' => 1], 'nama_guru ASC');

        $this->render('master/guru/struktur', [
            'title' => 'Struktur Organisasi Sekolah — DADO CBT',
            'school' => $school,
            'kepsek' => $kepsek,
            'waliList' => $waliList,
            'guruMapel' => $guruMapel,
            'tp' => $tp,
            'smt' => $smt,
        ]);
    }
}

