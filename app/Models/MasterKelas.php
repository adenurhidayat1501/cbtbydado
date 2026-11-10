<?php

namespace App\Models;

use App\Core\Model;

class MasterKelas extends Model
{
    protected string $table = 'master_kelas';
    protected string $primaryKey = 'id_kelas';

    public function getByPeriod(int $idTp, int $idSmt): array
    {
        $sql = "SELECT k.*, g.nama_guru as wali_kelas, j.nama_jurusan,
                       (SELECT COUNT(*) FROM kelas_siswa ks WHERE ks.id_kelas = k.id_kelas AND ks.id_tp = k.id_tp AND ks.id_smt = k.id_smt) as total_siswa
                FROM master_kelas k
                LEFT JOIN master_guru g ON g.id_guru = k.guru_id
                LEFT JOIN master_jurusan j ON j.id_jurusan = k.jurusan_id
                WHERE k.id_tp = ? AND k.id_smt = ?
                ORDER BY k.level_id ASC, k.nama_kelas ASC";
        return $this->query($sql, [$idTp, $idSmt]);
    }

    public function getDetail(int $idKelas): ?array
    {
        $sql = "SELECT k.*, g.nama_guru as wali_kelas, g.nip as nip_wali, j.nama_jurusan
                FROM master_kelas k
                LEFT JOIN master_guru g ON g.id_guru = k.guru_id
                LEFT JOIN master_jurusan j ON j.id_jurusan = k.jurusan_id
                WHERE k.id_kelas = ?
                LIMIT 1";
        $res = $this->query($sql, [$idKelas]);
        return $res[0] ?? null;
    }
}

