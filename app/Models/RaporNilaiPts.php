<?php

namespace App\Models;

use App\Core\Model;

class RaporNilaiPts extends Model
{
    protected string $table = 'rapor_nilai_pts';
    protected string $primaryKey = 'id_nilai_pts';

    public function getByClassMapel(int $idTp, int $idSmt, int $idKelas, int $idMapel): array
    {
        $stmt = $this->db->prepare(
            "SELECT rnpts.*, s.nama AS nama_siswa, s.nis, s.nisn
             FROM kelas_siswa ks
             JOIN master_siswa s ON ks.id_siswa = s.id_siswa
             LEFT JOIN `{$this->table}` rnpts ON rnpts.id_siswa = s.id_siswa 
                   AND rnpts.id_tp = ? AND rnpts.id_smt = ? AND rnpts.id_kelas = ? AND rnpts.id_mapel = ?
             WHERE ks.id_tp = ? AND ks.id_smt = ? AND ks.id_kelas = ?
             ORDER BY s.nama ASC"
        );
        $stmt->execute([$idTp, $idSmt, $idKelas, $idMapel, $idTp, $idSmt, $idKelas]);
        return $stmt->fetchAll();
    }

    public function getSingle(int $idTp, int $idSmt, int $idKelas, int $idMapel, int $idSiswa): ?array
    {
        $stmt = $this->db->prepare(
            "SELECT * FROM `{$this->table}`
             WHERE id_tp = ? AND id_smt = ? AND id_kelas = ? AND id_mapel = ? AND id_siswa = ?
             LIMIT 1"
        );
        $stmt->execute([$idTp, $idSmt, $idKelas, $idMapel, $idSiswa]);
        $row = $stmt->fetch();
        return $row ?: null;
    }

    public function saveEntry(array $data): bool
    {
        $existing = $this->getSingle(
            (int)$data['id_tp'],
            (int)$data['id_smt'],
            (int)$data['id_kelas'],
            (int)$data['id_mapel'],
            (int)$data['id_siswa']
        );

        $payload = [
            'nilai' => isset($data['nilai']) && is_numeric($data['nilai']) ? (float)$data['nilai'] : null,
            'predikat' => $data['predikat'] ?? null
        ];

        if ($existing) {
            return $this->update((int)$existing['id_nilai_pts'], $payload);
        } else {
            $payload['id_tp'] = (int)$data['id_tp'];
            $payload['id_smt'] = (int)$data['id_smt'];
            $payload['id_kelas'] = (int)$data['id_kelas'];
            $payload['id_mapel'] = (int)$data['id_mapel'];
            $payload['id_siswa'] = (int)$data['id_siswa'];
            return (bool)$this->insert($payload);
        }
    }
}

