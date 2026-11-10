<?php

namespace App\Models;

use App\Core\Model;

class RaporKikd extends Model
{
    protected string $table = 'rapor_kikd';
    protected string $primaryKey = 'id_kikd';

    public function getByMapel(int $idTp, int $idSmt, int $idMapel, ?int $aspek = null): array
    {
        $sql = "SELECT * FROM `{$this->table}` WHERE id_tp = ? AND id_smt = ? AND id_mapel_kelas = ?";
        $params = [$idTp, $idSmt, $idMapel];

        if ($aspek !== null) {
            $sql .= " AND aspek = ?";
            $params[] = $aspek;
        }

        $sql .= " ORDER BY nomor_kikd ASC";

        $stmt = $this->db->prepare($sql);
        $stmt->execute($params);
        return $stmt->fetchAll();
    }

    public function saveKikd(array $data): bool
    {
        if (!empty($data['id_kikd'])) {
            return $this->update((int)$data['id_kikd'], [
                'aspek' => (int)$data['aspek'],
                'nomor_kikd' => trim($data['nomor_kikd']),
                'isi_kikd' => trim($data['isi_kikd'])
            ]);
        }

        return (bool)$this->insert([
            'id_tp' => (int)$data['id_tp'],
            'id_smt' => (int)$data['id_smt'],
            'id_mapel_kelas' => (int)$data['id_mapel_kelas'],
            'aspek' => (int)$data['aspek'],
            'nomor_kikd' => trim($data['nomor_kikd']),
            'isi_kikd' => trim($data['isi_kikd'])
        ]);
    }
}

