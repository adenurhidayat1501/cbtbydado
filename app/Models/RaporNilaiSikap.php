<?php

namespace App\Models;

use App\Core\Model;

class RaporNilaiSikap extends Model
{
    protected string $table = 'rapor_nilai_sikap';
    protected string $primaryKey = 'id_nilai_sikap';

    public function getByClassAndType(int $idTp, int $idSmt, int $idKelas, int $jenis): array
    {
        $stmt = $this->db->prepare(
            "SELECT rns.*, s.nama AS nama_siswa, s.nis, s.nisn
             FROM kelas_siswa ks
             JOIN master_siswa s ON ks.id_siswa = s.id_siswa
             LEFT JOIN `{$this->table}` rns ON rns.id_siswa = s.id_siswa 
                   AND rns.id_tp = ? AND rns.id_smt = ? AND rns.id_kelas = ? AND rns.jenis = ?
             WHERE ks.id_tp = ? AND ks.id_smt = ? AND ks.id_kelas = ?
             ORDER BY s.nama ASC"
        );
        $stmt->execute([$idTp, $idSmt, $idKelas, $jenis, $idTp, $idSmt, $idKelas]);
        return $stmt->fetchAll();
    }

    public function getSingle(int $idTp, int $idSmt, int $idKelas, int $idSiswa, int $jenis): ?array
    {
        $stmt = $this->db->prepare(
            "SELECT * FROM `{$this->table}`
             WHERE id_tp = ? AND id_smt = ? AND id_kelas = ? AND id_siswa = ? AND jenis = ?
             LIMIT 1"
        );
        $stmt->execute([$idTp, $idSmt, $idKelas, $idSiswa, $jenis]);
        $row = $stmt->fetch();
        return $row ?: null;
    }

    public function saveEntry(array $data): bool
    {
        $existing = $this->getSingle(
            (int)$data['id_tp'],
            (int)$data['id_smt'],
            (int)$data['id_kelas'],
            (int)$data['id_siswa'],
            (int)$data['jenis']
        );

        $payload = [
            'nilai' => is_array($data['nilai']) ? json_encode($data['nilai']) : $data['nilai'],
            'deskripsi' => $data['deskripsi'] ?? null
        ];

        if ($existing) {
            return $this->update((int)$existing['id_nilai_sikap'], $payload);
        } else {
            $payload['id_tp'] = (int)$data['id_tp'];
            $payload['id_smt'] = (int)$data['id_smt'];
            $payload['id_kelas'] = (int)$data['id_kelas'];
            $payload['id_siswa'] = (int)$data['id_siswa'];
            $payload['jenis'] = (int)$data['jenis'];
            return (bool)$this->insert($payload);
        }
    }
}

