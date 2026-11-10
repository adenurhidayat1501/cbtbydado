<?php

namespace App\Models;

use App\Core\Model;

class CbtRuang extends Model
{
    protected string $table = 'cbt_ruang';
    protected string $primaryKey = 'id_ruang';

    /**
     * Get all exam rooms ordered by code.
     */
    public function getAll(): array
    {
        return $this->all('kode_ruang ASC, nama_ruang ASC');
    }

    /**
     * Get room with student count for an academic period.
     */
    public function getWithStudentCount(int $idTp, int $idSmt): array
    {
        $sql = "SELECT r.*, COUNT(ss.id_sesi_siswa) as total_siswa
                FROM cbt_ruang r
                LEFT JOIN cbt_sesi_siswa ss ON ss.ruang_id = r.id_ruang AND ss.tp_id = ? AND ss.smt_id = ?
                GROUP BY r.id_ruang
                ORDER BY r.kode_ruang ASC";

        return $this->query($sql, [$idTp, $idSmt]);
    }
}
