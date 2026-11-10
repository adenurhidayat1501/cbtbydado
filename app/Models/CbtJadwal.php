<?php

namespace App\Models;

use App\Core\Model;

class CbtJadwal extends Model
{
    protected string $table = 'cbt_jadwal';
    protected string $primaryKey = 'id_jadwal';

    /**
     * Get all exam schedules with joins to bank, mapel, guru, and jenis.
     */
    public function getAllByFilter(int $idTp, int $idSmt, ?int $status = null, ?int $idJenis = null, ?int $idBank = null): array
    {
        $sql = "SELECT j.*, 
                       bs.bank_kode, bs.bank_level, bs.tampil_pg, bs.tampil_kompleks, bs.tampil_jodohkan, bs.tampil_isian, bs.tampil_esai,
                       m.nama_mapel, m.kode as kode_mapel,
                       g.nama_guru, g.kode_guru,
                       jn.nama_jenis, jn.kode_jenis,
                       (SELECT COUNT(*) FROM cbt_soal_siswa ss WHERE ss.id_jadwal = j.id_jadwal) as total_peserta_ujian,
                       (SELECT COUNT(*) FROM cbt_durasi_siswa ds WHERE ds.id_jadwal = j.id_jadwal AND ds.status = 1) as sedang_ujian
                FROM cbt_jadwal j
                JOIN cbt_bank_soal bs ON bs.id_bank = j.id_bank
                JOIN master_mapel m ON m.id_mapel = bs.bank_mapel_id
                JOIN master_guru g ON g.id_guru = bs.bank_guru_id
                JOIN cbt_jenis jn ON jn.id_jenis = j.id_jenis
                WHERE j.id_tp = ? AND j.id_smt = ?";

        $params = [$idTp, $idSmt];

        if ($status !== null) {
            $sql .= " AND j.status = ?";
            $params[] = $status;
        }

        if ($idJenis !== null && $idJenis > 0) {
            $sql .= " AND j.id_jenis = ?";
            $params[] = $idJenis;
        }

        if ($idBank !== null && $idBank > 0) {
            $sql .= " AND j.id_bank = ?";
            $params[] = $idBank;
        }

        $sql .= " ORDER BY j.tgl_mulai DESC, j.jam_ke ASC, j.id_jadwal DESC";

        $rows = $this->query($sql, $params);
        foreach ($rows as &$r) {
            if (is_string($r['pengawas'])) {
                $r['pengawas_list'] = json_decode($r['pengawas'], true) ?: [];
            } else {
                $r['pengawas_list'] = (array)($r['pengawas'] ?? []);
            }
        }
        unset($r);

        return $rows;
    }

    /**
     * Get detailed exam schedule with all relations.
     */
    public function getDetail(int $idJadwal): ?array
    {
        $sql = "SELECT j.*, 
                       bs.bank_kode, bs.bank_level, bs.opsi, bs.bank_mapel_id,
                       bs.tampil_pg, bs.bobot_pg,
                       bs.tampil_kompleks, bs.bobot_kompleks,
                       bs.tampil_jodohkan, bs.bobot_jodohkan,
                       bs.tampil_isian, bs.bobot_isian,
                       bs.tampil_esai, bs.bobot_esai,
                       m.nama_mapel, m.kode as kode_mapel,
                       g.nama_guru, g.kode_guru,
                       jn.nama_jenis, jn.kode_jenis,
                       (SELECT COUNT(*) FROM cbt_soal_siswa ss WHERE ss.id_jadwal = j.id_jadwal) as total_peserta_ujian,
                       (SELECT COUNT(*) FROM cbt_durasi_siswa ds WHERE ds.id_jadwal = j.id_jadwal AND ds.status = 1) as sedang_ujian
                FROM cbt_jadwal j
                JOIN cbt_bank_soal bs ON bs.id_bank = j.id_bank
                JOIN master_mapel m ON m.id_mapel = bs.bank_mapel_id
                JOIN master_guru g ON g.id_guru = bs.bank_guru_id
                JOIN cbt_jenis jn ON jn.id_jenis = j.id_jenis
                WHERE j.id_jadwal = ?
                LIMIT 1";

        $rows = $this->query($sql, [$idJadwal]);
        if (empty($rows)) {
            return null;
        }

        $row = $rows[0];
        if (is_string($row['pengawas'])) {
            $row['pengawas_list'] = json_decode($row['pengawas'], true) ?: [];
        } else {
            $row['pengawas_list'] = (array)($row['pengawas'] ?? []);
        }

        return $row;
    }

    /**
     * Check if exam is currently ongoing (at least one student is actively taking it).
     */
    public function isExamOngoing(int $idJadwal): bool
    {
        $sql = "SELECT COUNT(*) as active_count FROM cbt_durasi_siswa WHERE id_jadwal = ? AND status = 1";
        $rows = $this->query($sql, [$idJadwal]);
        return ((int)($rows[0]['active_count'] ?? 0)) > 0;
    }

    /**
     * Verify whether the schedule can be safely edited or deleted without corrupting live exam records.
     */
    public function canEditOrDelete(int $idJadwal): bool
    {
        return !$this->isExamOngoing($idJadwal);
    }
}
