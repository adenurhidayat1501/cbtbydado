<?php

namespace App\Models;

use App\Core\Model;

class JabatanGuru extends Model
{
    protected string $table = 'jabatan_guru';
    protected string $primaryKey = 'id_jabatan_guru';

    public function getByPeriod(int $idTp, int $idSmt): array
    {
        $sql = "SELECT jg.*, g.nama_guru, g.nip, g.kode_guru, g.foto, k.nama_kelas
                FROM jabatan_guru jg
                JOIN master_guru g ON g.id_guru = jg.id_guru
                LEFT JOIN master_kelas k ON k.id_kelas = jg.id_kelas
                WHERE jg.id_tp = ? AND jg.id_smt = ?
                ORDER BY jg.id_jabatan ASC, g.nama_guru ASC";
        return $this->query($sql, [$idTp, $idSmt]);
    }

    public function setJabatan(int $idGuru, int $idTp, int $idSmt, int $idJabatan, ?int $idKelas = null, ?array $mapelKelas = null): bool
    {
        $existing = $this->first([
            'id_guru' => $idGuru,
            'id_tp' => $idTp,
            'id_smt' => $idSmt,
        ]);

        $payload = [
            'id_guru' => $idGuru,
            'id_tp' => $idTp,
            'id_smt' => $idSmt,
            'id_jabatan' => $idJabatan,
            'id_kelas' => $idKelas,
            'mapel_kelas' => !empty($mapelKelas) ? json_encode($mapelKelas, JSON_UNESCAPED_UNICODE) : null,
        ];

        if ($existing) {
            return $this->update($existing['id_jabatan_guru'], $payload);
        } else {
            return (bool)$this->insert($payload);
        }
    }
}

