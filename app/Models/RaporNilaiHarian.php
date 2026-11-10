<?php

namespace App\Models;

use App\Core\Model;

class RaporNilaiHarian extends Model
{
    protected string $table = 'rapor_nilai_harian';
    protected string $primaryKey = 'id_nilai_harian';

    public function getByClassMapel(int $idTp, int $idSmt, int $idKelas, int $idMapel): array
    {
        $stmt = $this->db->prepare(
            "SELECT rnh.*, s.nama AS nama_siswa, s.nis, s.nisn, s.jk AS jenis_kelamin
             FROM kelas_siswa ks
             JOIN master_siswa s ON ks.id_siswa = s.id_siswa
             LEFT JOIN `{$this->table}` rnh ON rnh.id_siswa = s.id_siswa 
                   AND rnh.id_tp = ? AND rnh.id_smt = ? AND rnh.id_kelas = ? AND rnh.id_mapel = ?
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

        // Calculate averages if p1..p8 provided
        $pScores = [];
        for ($i = 1; $i <= 8; $i++) {
            $key = "p{$i}";
            if (isset($data[$key]) && is_numeric($data[$key])) {
                $pScores[] = (float)$data[$key];
            }
        }
        $pRata = !empty($pScores) ? array_sum($pScores) / count($pScores) : ($data['p_rata_rata'] ?? null);

        $kScores = [];
        for ($i = 1; $i <= 8; $i++) {
            $key = "k{$i}";
            if (isset($data[$key]) && is_numeric($data[$key])) {
                $kScores[] = (float)$data[$key];
            }
        }
        $kRata = !empty($kScores) ? array_sum($kScores) / count($kScores) : ($data['k_rata_rata'] ?? null);

        $payload = [
            'p1' => isset($data['p1']) && is_numeric($data['p1']) ? (float)$data['p1'] : null,
            'p2' => isset($data['p2']) && is_numeric($data['p2']) ? (float)$data['p2'] : null,
            'p3' => isset($data['p3']) && is_numeric($data['p3']) ? (float)$data['p3'] : null,
            'p4' => isset($data['p4']) && is_numeric($data['p4']) ? (float)$data['p4'] : null,
            'p5' => isset($data['p5']) && is_numeric($data['p5']) ? (float)$data['p5'] : null,
            'p6' => isset($data['p6']) && is_numeric($data['p6']) ? (float)$data['p6'] : null,
            'p7' => isset($data['p7']) && is_numeric($data['p7']) ? (float)$data['p7'] : null,
            'p8' => isset($data['p8']) && is_numeric($data['p8']) ? (float)$data['p8'] : null,
            'p_rata_rata' => $pRata !== null ? round((float)$pRata, 2) : null,
            'p_predikat' => $data['p_predikat'] ?? null,
            'p_deskripsi' => $data['p_deskripsi'] ?? null,
            'k1' => isset($data['k1']) && is_numeric($data['k1']) ? (float)$data['k1'] : null,
            'k2' => isset($data['k2']) && is_numeric($data['k2']) ? (float)$data['k2'] : null,
            'k3' => isset($data['k3']) && is_numeric($data['k3']) ? (float)$data['k3'] : null,
            'k4' => isset($data['k4']) && is_numeric($data['k4']) ? (float)$data['k4'] : null,
            'k5' => isset($data['k5']) && is_numeric($data['k5']) ? (float)$data['k5'] : null,
            'k6' => isset($data['k6']) && is_numeric($data['k6']) ? (float)$data['k6'] : null,
            'k7' => isset($data['k7']) && is_numeric($data['k7']) ? (float)$data['k7'] : null,
            'k8' => isset($data['k8']) && is_numeric($data['k8']) ? (float)$data['k8'] : null,
            'k_rata_rata' => $kRata !== null ? round((float)$kRata, 2) : null,
            'k_predikat' => $data['k_predikat'] ?? null,
            'k_deskripsi' => $data['k_deskripsi'] ?? null
        ];

        if ($existing) {
            return $this->update((int)$existing['id_nilai_harian'], $payload);
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
