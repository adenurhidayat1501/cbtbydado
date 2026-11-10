<?php

namespace App\Models;

use App\Core\Model;

class CbtSesi extends Model
{
    protected string $table = 'cbt_sesi';
    protected string $primaryKey = 'id_sesi';

    /**
     * Get all exam sessions ordered by start time.
     */
    public function getAll(): array
    {
        return $this->all('waktu_mulai ASC, kode_sesi ASC');
    }

    /**
     * Get sessions with student count for an academic period.
     */
    public function getWithStudentCount(int $idTp, int $idSmt): array
    {
        $sql = "SELECT s.*, COUNT(ss.id_sesi_siswa) as total_siswa
                FROM cbt_sesi s
                LEFT JOIN cbt_sesi_siswa ss ON ss.sesi_id = s.id_sesi AND ss.tp_id = ? AND ss.smt_id = ?
                GROUP BY s.id_sesi
                ORDER BY s.waktu_mulai ASC";

        return $this->query($sql, [$idTp, $idSmt]);
    }
}
