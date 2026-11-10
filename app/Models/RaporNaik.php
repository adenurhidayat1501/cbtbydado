<?php

namespace App\Models;

use App\Core\Model;

class RaporNaik extends Model
{
    protected string $table = 'rapor_naik';
    protected string $primaryKey = 'id_naik';

    public function getByClass(int $idTp, int $idSmt, int $idKelas): array
    {
        $stmt = $this->db->prepare(
            "SELECT rn.*, s.nama AS nama_siswa, s.nis, s.nisn
             FROM kelas_siswa ks
             JOIN master_siswa s ON ks.id_siswa = s.id_siswa
             LEFT JOIN `{$this->table}` rn ON rn.id_siswa = s.id_siswa 
                   AND rn.id_tp = ? AND rn.id_smt = ?
             WHERE ks.id_tp = ? AND ks.id_smt = ? AND ks.id_kelas = ?
             ORDER BY s.nama ASC"
        );
        $stmt->execute([$idTp, $idSmt, $idTp, $idSmt, $idKelas]);
        return $stmt->fetchAll();
    }

    public function getSingle(int $idTp, int $idSmt, int $idSiswa): ?array
    {
        $stmt = $this->db->prepare(
            "SELECT * FROM `{$this->table}`
             WHERE id_tp = ? AND id_smt = ? AND id_siswa = ?
             LIMIT 1"
        );
        $stmt->execute([$idTp, $idSmt, $idSiswa]);
        $row = $stmt->fetch();
        return $row ?: null;
    }

    public function saveEntry(array $data): bool
    {
        $existing = $this->getSingle(
            (int)$data['id_tp'],
            (int)$data['id_smt'],
            (int)$data['id_siswa']
        );

        $payload = [
            'naik' => isset($data['naik']) ? (int)$data['naik'] : 1,
            'keterangan' => !empty($data['keterangan']) ? trim($data['keterangan']) : null
        ];

        if ($existing) {
            return $this->update((int)$existing['id_naik'], $payload);
        } else {
            $payload['id_tp'] = (int)$data['id_tp'];
            $payload['id_smt'] = (int)$data['id_smt'];
            $payload['id_siswa'] = (int)$data['id_siswa'];
            return (bool)$this->insert($payload);
        }
    }
}

