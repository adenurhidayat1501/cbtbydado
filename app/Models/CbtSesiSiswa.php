<?php

namespace App\Models;

use App\Core\Model;

class CbtSesiSiswa extends Model
{
    protected string $table = 'cbt_sesi_siswa';
    protected string $primaryKey = 'id_sesi_siswa';

    /**
     * Get list of students in a class with their assigned Room and Session in active period.
     */
    public function getStudentsWithRoomAndSession(int $idTp, int $idSmt, int $idKelas): array
    {
        $sql = "SELECT s.id_siswa, s.nis, s.nisn, s.nama, s.jk, s.foto,
                       k.nama_kelas,
                       np.nomor_peserta,
                       css.id_sesi_siswa, css.ruang_id, css.sesi_id,
                       r.nama_ruang, r.kode_ruang,
                       se.nama_sesi, se.kode_sesi, se.waktu_mulai, se.waktu_akhir
                FROM kelas_siswa ks
                JOIN master_siswa s ON s.id_siswa = ks.id_siswa
                JOIN master_kelas k ON k.id_kelas = ks.id_kelas
                LEFT JOIN cbt_sesi_siswa css ON css.siswa_id = s.id_siswa AND css.tp_id = ? AND css.smt_id = ?
                LEFT JOIN cbt_ruang r ON r.id_ruang = css.ruang_id
                LEFT JOIN cbt_sesi se ON se.id_sesi = css.sesi_id
                LEFT JOIN cbt_nomor_peserta np ON np.id_siswa = s.id_siswa AND np.id_tp = ?
                WHERE ks.id_tp = ? AND ks.id_smt = ? AND ks.id_kelas = ?
                ORDER BY s.nama ASC";

        return $this->query($sql, [$idTp, $idSmt, $idTp, $idTp, $idSmt, $idKelas]);
    }

    /**
     * Assign or update room and session for a single student in active period.
     */
    public function assignStudent(int $idTp, int $idSmt, int $idSiswa, int $idKelas, int $idRuang, int $idSesi): bool
    {
        $existing = $this->first([
            'siswa_id' => $idSiswa,
            'tp_id' => $idTp,
            'smt_id' => $idSmt,
        ]);

        $payload = [
            'siswa_id' => $idSiswa,
            'kelas_id' => $idKelas,
            'ruang_id' => $idRuang,
            'sesi_id' => $idSesi,
            'tp_id' => $idTp,
            'smt_id' => $idSmt,
        ];

        if ($existing) {
            return $this->update($existing['id_sesi_siswa'], $payload);
        } else {
            return (bool)$this->insert($payload);
        }
    }

    /**
     * Bulk assign room and session to multiple students in a class.
     */
    public function bulkAssign(int $idTp, int $idSmt, int $idKelas, int $idRuang, int $idSesi, array $siswaIds): int
    {
        $count = 0;
        foreach ($siswaIds as $idSiswa) {
            $idSiswa = (int)$idSiswa;
            if ($idSiswa > 0) {
                if ($this->assignStudent($idTp, $idSmt, $idSiswa, $idKelas, $idRuang, $idSesi)) {
                    $count++;
                }
            }
        }
        return $count;
    }
}
