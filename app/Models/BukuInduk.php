<?php

namespace App\Models;

use App\Core\Model;

class BukuInduk extends Model
{
    protected string $table = 'buku_induk';
    protected string $primaryKey = 'id_buku';

    public function getBySiswa(int $idSiswa): ?array
    {
        $stmt = $this->db->prepare(
            "SELECT bi.*, s.nama, s.nis, s.nisn, s.tempat_lahir, s.tgl_lahir AS tanggal_lahir, 
                    s.jk AS jenis_kelamin, s.agama, s.foto, s.alamat, s.no_hp AS hp, s.nama_ayah, s.nama_ibu
             FROM master_siswa s
             LEFT JOIN `{$this->table}` bi ON bi.id_siswa = s.id_siswa
             WHERE s.id_siswa = ?
             LIMIT 1"
        );
        $stmt->execute([$idSiswa]);
        $row = $stmt->fetch();
        return $row ?: null;
    }

    public function getOrCreate(int $idSiswa): array
    {
        $existing = $this->getBySiswa($idSiswa);
        if ($existing && !empty($existing['id_buku'])) {
            return $existing;
        }

        // Initialize record if missing
        $stmt = $this->db->prepare("SELECT * FROM master_siswa WHERE id_siswa = ? LIMIT 1");
        $stmt->execute([$idSiswa]);
        $siswa = $stmt->fetch();

        $uid = uniqid('BI-', true);
        $this->insert([
            'id_siswa' => $idSiswa,
            'uid' => $uid,
            'bahasa' => 'Indonesia',
            'status_yatim' => 'Lengkap',
            'status' => 1
        ]);

        return $this->getBySiswa($idSiswa) ?? [];
    }

    public function saveDetails(int $idSiswa, array $data): bool
    {
        $existing = $this->getOrCreate($idSiswa);
        $idBuku = (int)$existing['id_buku'];

        $payload = [
            'rombel_awal' => !empty($data['rombel_awal']) ? trim($data['rombel_awal']) : null,
            'nama_panggilan' => !empty($data['nama_panggilan']) ? trim($data['nama_panggilan']) : null,
            'bahasa' => !empty($data['bahasa']) ? trim($data['bahasa']) : 'Indonesia',
            'jml_saudara_kandung' => isset($data['jml_saudara_kandung']) ? (int)$data['jml_saudara_kandung'] : 0,
            'jml_saudara_tiri' => isset($data['jml_saudara_tiri']) ? (int)$data['jml_saudara_tiri'] : 0,
            'jml_saudara_angkat' => isset($data['jml_saudara_angkat']) ? (int)$data['jml_saudara_angkat'] : 0,
            'status_yatim' => !empty($data['status_yatim']) ? trim($data['status_yatim']) : 'Lengkap',
            'gol_darah' => !empty($data['gol_darah']) ? trim($data['gol_darah']) : null,
            'penyakit' => !empty($data['penyakit']) ? trim($data['penyakit']) : null,
            'kelainan_fisik' => !empty($data['kelainan_fisik']) ? trim($data['kelainan_fisik']) : null,
            'tinggi' => !empty($data['tinggi']) ? (int)$data['tinggi'] : null,
            'berat' => !empty($data['berat']) ? (int)$data['berat'] : null,
            'tamat_dari' => !empty($data['tamat_dari']) ? trim($data['tamat_dari']) : null,
            'no_ijazah_sebelumnya' => !empty($data['no_ijazah_sebelumnya']) ? trim($data['no_ijazah_sebelumnya']) : null,
            'tgl_ijazah_sebelumnya' => !empty($data['tgl_ijazah_sebelumnya']) ? $data['tgl_ijazah_sebelumnya'] : null,
            'tahun_lulus' => !empty($data['tahun_lulus']) ? trim($data['tahun_lulus']) : null,
            'no_ijazah' => !empty($data['no_ijazah']) ? trim($data['no_ijazah']) : null,
            'status' => isset($data['status']) ? (int)$data['status'] : 1
        ];

        return $this->update($idBuku, $payload);
    }

    public function archiveSemesterGrade(int $idSiswa, int $idTp, int $idSmt, array $gradeSnapshot): bool
    {
        $existing = $this->getOrCreate($idSiswa);
        $idBuku = (int)$existing['id_buku'];

        $archive = [];
        if (!empty($existing['arsip_rapor_json'])) {
            $decoded = json_decode($existing['arsip_rapor_json'], true);
            if (is_array($decoded)) {
                $archive = $decoded;
            }
        }

        $key = "tp_{$idTp}_smt_{$idSmt}";
        $gradeSnapshot['archived_at'] = date('Y-m-d H:i:s');
        $archive[$key] = $gradeSnapshot;

        return $this->update($idBuku, [
            'arsip_rapor_json' => json_encode($archive)
        ]);
    }

    public function listAllWithClass(int $idTp, int $idSmt, ?int $idKelas = null, ?int $status = null): array
    {
        $sql = "SELECT s.id_siswa, s.nama, s.nis, s.nisn, s.jk AS jenis_kelamin, s.foto,
                       k.id_kelas, k.nama_kelas,
                       bi.id_buku, bi.tahun_lulus, bi.no_ijazah, COALESCE(bi.status, 1) AS status_buku
                FROM master_siswa s
                LEFT JOIN kelas_siswa ks ON ks.id_siswa = s.id_siswa AND ks.id_tp = ? AND ks.id_smt = ?
                LEFT JOIN master_kelas k ON ks.id_kelas = k.id_kelas
                LEFT JOIN `{$this->table}` bi ON bi.id_siswa = s.id_siswa
                WHERE 1=1";
        $params = [$idTp, $idSmt];

        if ($idKelas !== null && $idKelas > 0) {
            $sql .= " AND ks.id_kelas = ?";
            $params[] = $idKelas;
        }

        if ($status !== null) {
            $sql .= " AND COALESCE(bi.status, 1) = ?";
            $params[] = $status;
        }

        $sql .= " ORDER BY k.nama_kelas ASC, s.nama ASC";

        $stmt = $this->db->prepare($sql);
        $stmt->execute($params);
        return $stmt->fetchAll();
    }
}
