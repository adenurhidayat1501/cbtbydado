<?php

namespace App\Models;

use App\Core\Model;

class RaporNilaiEkstra extends Model
{
    protected string $table = 'rapor_nilai_ekstra';
    protected string $primaryKey = 'id_nilai_ekstra';

    public function getByClassAndEkstra(int $idTp, int $idSmt, int $idKelas, int $idEkstra): array
    {
        $stmt = $this->db->prepare(
            "SELECT rne.*, s.nama AS nama_siswa, s.nis, s.nisn
             FROM kelas_siswa ks
             JOIN master_siswa s ON ks.id_siswa = s.id_siswa
             LEFT JOIN `{$this->table}` rne ON rne.id_siswa = s.id_siswa 
                   AND rne.id_tp = ? AND rne.id_smt = ? AND rne.id_ekstra = ?
             WHERE ks.id_tp = ? AND ks.id_smt = ? AND ks.id_kelas = ?
             ORDER BY s.nama ASC"
        );
        $stmt->execute([$idTp, $idSmt, $idEkstra, $idTp, $idSmt, $idKelas]);
        return $stmt->fetchAll();
    }

    public function getStudentEkstra(int $idTp, int $idSmt, int $idSiswa): array
    {
        $stmt = $this->db->prepare(
            "SELECT rne.*, me.nama_ekstra
             FROM `{$this->table}` rne
             JOIN master_ekstra me ON rne.id_ekstra = me.id_ekstra
             WHERE rne.id_tp = ? AND rne.id_smt = ? AND rne.id_siswa = ?
             ORDER BY me.nama_ekstra ASC"
        );
        $stmt->execute([$idTp, $idSmt, $idSiswa]);
        return $stmt->fetchAll();
    }

    public function getSingle(int $idTp, int $idSmt, int $idEkstra, int $idSiswa): ?array
    {
        $stmt = $this->db->prepare(
            "SELECT * FROM `{$this->table}`
             WHERE id_tp = ? AND id_smt = ? AND id_ekstra = ? AND id_siswa = ?
             LIMIT 1"
        );
        $stmt->execute([$idTp, $idSmt, $idEkstra, $idSiswa]);
        $row = $stmt->fetch();
        return $row ?: null;
    }

    public function saveEntry(array $data): bool
    {
        $existing = $this->getSingle(
            (int)$data['id_tp'],
            (int)$data['id_smt'],
            (int)$data['id_ekstra'],
            (int)$data['id_siswa']
        );

        $payload = [
            'id_kelas' => (int)$data['id_kelas'],
            'nilai' => $data['nilai'] ?? null,
            'predikat' => $data['predikat'] ?? null,
            'deskripsi' => $data['deskripsi'] ?? null
        ];

        if ($existing) {
            return $this->update((int)$existing['id_nilai_ekstra'], $payload);
        } else {
            $payload['id_tp'] = (int)$data['id_tp'];
            $payload['id_smt'] = (int)$data['id_smt'];
            $payload['id_ekstra'] = (int)$data['id_ekstra'];
            $payload['id_siswa'] = (int)$data['id_siswa'];
            return (bool)$this->insert($payload);
        }
    }
}

