<?php

namespace App\Models;

use App\Core\Model;

class RaporFisik extends Model
{
    protected string $table = 'rapor_fisik';
    protected string $primaryKey = 'id_fisik';

    public function getByClass(int $idTp, int $idSmt, int $idKelas): array
    {
        $stmt = $this->db->prepare(
            "SELECT rf.*, s.nama AS nama_siswa, s.nis, s.nisn
             FROM kelas_siswa ks
             JOIN master_siswa s ON ks.id_siswa = s.id_siswa
             LEFT JOIN `{$this->table}` rf ON rf.id_siswa = s.id_siswa 
                   AND rf.id_tp = ? AND rf.id_smt = ? AND rf.id_kelas = ?
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
            'tinggi' => !empty($data['tinggi']) ? (int)$data['tinggi'] : null,
            'berat' => !empty($data['berat']) ? (int)$data['berat'] : null,
            'pendengaran' => !empty($data['pendengaran']) ? trim($data['pendengaran']) : 'Baik',
            'penglihatan' => !empty($data['penglihatan']) ? trim($data['penglihatan']) : 'Baik',
            'gigi' => !empty($data['gigi']) ? trim($data['gigi']) : 'Baik',
            'lainnya' => !empty($data['lainnya']) ? trim($data['lainnya']) : null
        ];

        if ($existing) {
            return $this->update((int)$existing['id_fisik'], $payload);
        } else {
            $payload['id_tp'] = (int)$data['id_tp'];
            $payload['id_smt'] = (int)$data['id_smt'];
            $payload['id_kelas'] = (int)$data['id_kelas'];
            $payload['id_siswa'] = (int)$data['id_siswa'];
            return (bool)$this->insert($payload);
        }
    }
}

