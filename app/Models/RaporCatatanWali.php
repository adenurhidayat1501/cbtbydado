<?php

namespace App\Models;

use App\Core\Model;

class RaporCatatanWali extends Model
{
    protected string $table = 'rapor_catatan_wali';
    protected string $primaryKey = 'id_catatan';

    public function getByClass(int $idTp, int $idSmt, int $idKelas): array
    {
        $stmt = $this->db->prepare(
            "SELECT rcw.*, s.nama AS nama_siswa, s.nis, s.nisn
             FROM kelas_siswa ks
             JOIN master_siswa s ON ks.id_siswa = s.id_siswa
             LEFT JOIN `{$this->table}` rcw ON rcw.id_siswa = s.id_siswa 
                   AND rcw.id_tp = ? AND rcw.id_smt = ? AND rcw.id_kelas = ?
             WHERE ks.id_tp = ? AND ks.id_smt = ? AND ks.id_kelas = ?
             ORDER BY s.nama ASC"
        );
        $stmt->execute([$idTp, $idSmt, $idKelas, $idTp, $idSmt, $idKelas]);
        return $stmt->fetchAll();
    }

    public function getSingle(int $idTp, int $idSmt, int $idKelas, int $idSiswa): ?array
    {
        $stmt = $this->db->prepare(
            "SELECT * FROM `{$this->table}`
             WHERE id_tp = ? AND id_smt = ? AND id_kelas = ? AND id_siswa = ?
             LIMIT 1"
        );
        $stmt->execute([$idTp, $idSmt, $idKelas, $idSiswa]);
        $row = $stmt->fetch();
        return $row ?: null;
    }

    public function saveEntry(array $data): bool
    {
        $existing = $this->getSingle(
            (int)$data['id_tp'],
            (int)$data['id_smt'],
            (int)$data['id_kelas'],
            (int)$data['id_siswa']
        );

        $absensi = [
            'sakit' => (int)($data['sakit'] ?? 0),
            'izin' => (int)($data['izin'] ?? 0),
            'alfa' => (int)($data['alfa'] ?? 0)
        ];

        $payload = [
            'nilai' => json_encode($absensi),
            'deskripsi' => $data['deskripsi'] ?? null
        ];

        if ($existing) {
            return $this->update((int)$existing['id_catatan'], $payload);
        } else {
            $payload['id_tp'] = (int)$data['id_tp'];
            $payload['id_smt'] = (int)$data['id_smt'];
            $payload['id_kelas'] = (int)$data['id_kelas'];
            $payload['id_siswa'] = (int)$data['id_siswa'];
            return (bool)$this->insert($payload);
        }
    }
}

