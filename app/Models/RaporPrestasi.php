<?php

namespace App\Models;

use App\Core\Model;

class RaporPrestasi extends Model
{
    protected string $table = 'rapor_prestasi';
    protected string $primaryKey = 'id_prestasi';

    public function getByClass(int $idTp, int $idSmt, int $idKelas): array
    {
        $stmt = $this->db->prepare(
            "SELECT rp.*, s.nama AS nama_siswa, s.nis, s.nisn
             FROM kelas_siswa ks
             JOIN master_siswa s ON ks.id_siswa = s.id_siswa
             LEFT JOIN `{$this->table}` rp ON rp.id_siswa = s.id_siswa 
                   AND rp.id_tp = ? AND rp.id_smt = ? AND rp.id_kelas = ?
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

        $payload = [
            'ranking' => !empty($data['ranking']) ? (int)$data['ranking'] : null,
            'deskripsi_ranking' => !empty($data['deskripsi_ranking']) ? trim($data['deskripsi_ranking']) : null,
            'p1' => !empty($data['p1']) ? trim($data['p1']) : null,
            'p1_desk' => !empty($data['p1_desk']) ? trim($data['p1_desk']) : null,
            'p2' => !empty($data['p2']) ? trim($data['p2']) : null,
            'p2_desk' => !empty($data['p2_desk']) ? trim($data['p2_desk']) : null,
            'p3' => !empty($data['p3']) ? trim($data['p3']) : null,
            'p3_desk' => !empty($data['p3_desk']) ? trim($data['p3_desk']) : null
        ];

        if ($existing) {
            return $this->update((int)$existing['id_prestasi'], $payload);
        } else {
            $payload['id_tp'] = (int)$data['id_tp'];
            $payload['id_smt'] = (int)$data['id_smt'];
            $payload['id_kelas'] = (int)$data['id_kelas'];
            $payload['id_siswa'] = (int)$data['id_siswa'];
            return (bool)$this->insert($payload);
        }
    }
}

