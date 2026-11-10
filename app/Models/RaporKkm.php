<?php

namespace App\Models;

use App\Core\Model;

class RaporKkm extends Model
{
    protected string $table = 'rapor_kkm';
    protected string $primaryKey = 'id_kkm';

    public function getByClassAndMapel(int $idTp, int $idSmt, int $idKelas, int $idMapel, int $jenis = 1): ?array
    {
        $stmt = $this->db->prepare(
            "SELECT * FROM `{$this->table}` 
             WHERE id_tp = ? AND id_smt = ? AND id_kelas = ? AND id_mapel = ? AND jenis = ? 
             LIMIT 1"
        );
        $stmt->execute([$idTp, $idSmt, $idKelas, $idMapel, $jenis]);
        $row = $stmt->fetch();
        return $row ?: null;
    }

    public function getByClass(int $idTp, int $idSmt, int $idKelas, int $jenis = 1): array
    {
        $stmt = $this->db->prepare(
            "SELECT rk.*, m.nama_mapel, m.kode AS kode_mapel 
             FROM `{$this->table}` rk
             JOIN master_mapel m ON rk.id_mapel = m.id_mapel
             WHERE rk.id_tp = ? AND rk.id_smt = ? AND rk.id_kelas = ? AND rk.jenis = ?
             ORDER BY m.urutan_tampil ASC, m.nama_mapel ASC"
        );
        $stmt->execute([$idTp, $idSmt, $idKelas, $jenis]);
        return $stmt->fetchAll();
    }

    public function saveKkm(array $data): bool
    {
        $existing = $this->getByClassAndMapel(
            (int)$data['id_tp'],
            (int)$data['id_smt'],
            (int)$data['id_kelas'],
            (int)$data['id_mapel'],
            (int)($data['jenis'] ?? 1)
        );

        $payload = [
            'kkm' => (int)($data['kkm'] ?? 75),
            'bobot_ph' => (int)($data['bobot_ph'] ?? 50),
            'bobot_pts' => (int)($data['bobot_pts'] ?? 25),
            'bobot_pas' => (int)($data['bobot_pas'] ?? 25),
            'beban_jam' => (int)($data['beban_jam'] ?? 2)
        ];

        if ($existing) {
            return $this->update((int)$existing['id_kkm'], $payload);
        } else {
            $payload['id_tp'] = (int)$data['id_tp'];
            $payload['id_smt'] = (int)$data['id_smt'];
            $payload['id_kelas'] = (int)$data['id_kelas'];
            $payload['id_mapel'] = (int)$data['id_mapel'];
            $payload['jenis'] = (int)($data['jenis'] ?? 1);
            return (bool)$this->insert($payload);
        }
    }
}
