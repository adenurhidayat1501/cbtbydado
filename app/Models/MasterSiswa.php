<?php

namespace App\Models;

use App\Core\Model;

class MasterSiswa extends Model
{
    protected string $table = 'master_siswa';
    protected string $primaryKey = 'id_siswa';

    public function findByUserId(int $userId): ?array
    {
        return $this->first(['id_user' => $userId]);
    }

    public function findByNis(string $nis): ?array
    {
        return $this->first(['nis' => $nis]);
    }

    public function findByNisn(string $nisn): ?array
    {
        return $this->first(['nisn' => $nisn]);
    }

    public function getStudentDetail(int $idSiswa, ?int $idTp = null, ?int $idSmt = null): ?array
    {
        $tpId = $idTp ?? (active_tp()['id_tp'] ?? 0);
        $smtId = $idSmt ?? (active_smt()['id_smt'] ?? 0);

        $sql = "SELECT s.*, 
                       k.id_kelas, k.nama_kelas, k.level_id,
                       ss.ruang_id, ss.sesi_id,
                       cr.nama_ruang, cr.kode_ruang,
                       cs.nama_sesi, cs.kode_sesi, cs.waktu_mulai, cs.waktu_akhir,
                       np.nomor_peserta
                FROM master_siswa s
                LEFT JOIN kelas_siswa ks ON ks.id_siswa = s.id_siswa AND ks.id_tp = ? AND ks.id_smt = ?
                LEFT JOIN master_kelas k ON k.id_kelas = ks.id_kelas
                LEFT JOIN cbt_sesi_siswa ss ON ss.siswa_id = s.id_siswa AND ss.tp_id = ? AND ss.smt_id = ?
                LEFT JOIN cbt_ruang cr ON cr.id_ruang = ss.ruang_id
                LEFT JOIN cbt_sesi cs ON cs.id_sesi = ss.sesi_id
                LEFT JOIN cbt_nomor_peserta np ON np.id_siswa = s.id_siswa AND np.id_tp = ?
                WHERE s.id_siswa = ?
                LIMIT 1";

        $rows = $this->query($sql, [$tpId, $smtId, $tpId, $smtId, $tpId, $idSiswa]);
        return $rows[0] ?? null;
    }
}

