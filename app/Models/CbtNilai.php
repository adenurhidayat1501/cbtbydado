<?php

declare(strict_types=1);

namespace App\Models;

use App\Core\Model;

class CbtNilai extends Model
{
    protected string $table = 'cbt_nilai';
    protected string $primaryKey = 'id_nilai';

    /**
     * Get student score for a specific exam schedule.
     */
    public function getScore(int $idJadwal, int $idSiswa): ?array
    {
        return $this->first([
            'id_jadwal' => $idJadwal,
            'id_siswa' => $idSiswa,
        ]);
    }

    /**
     * Save or update exam score.
     */
    public function saveScore(int $idTp, int $idSmt, int $idJadwal, int $idSiswa, array $scores): bool
    {
        $existing = $this->getScore($idJadwal, $idSiswa);

        $payload = [
            'id_tp' => $idTp,
            'id_smt' => $idSmt,
            'id_jadwal' => $idJadwal,
            'id_siswa' => $idSiswa,
            'pg_benar' => (int)($scores['pg_benar'] ?? 0),
            'pg_nilai' => (float)($scores['pg_nilai'] ?? 0),
            'kompleks_nilai' => (float)($scores['kompleks_nilai'] ?? 0),
            'jodohkan_nilai' => (float)($scores['jodohkan_nilai'] ?? 0),
            'isian_nilai' => (float)($scores['isian_nilai'] ?? 0),
            'essai_nilai' => (float)($scores['essai_nilai'] ?? 0),
            'total_nilai' => (float)($scores['total_nilai'] ?? 0),
            'dikoreksi' => (int)($scores['dikoreksi'] ?? 0),
        ];

        if ($existing) {
            return $this->update($existing['id_nilai'], $payload);
        } else {
            return (bool)$this->insert($payload);
        }
    }

    /**
     * Get recap of scores for an exam schedule, with optional class filter.
     */
    public function getRecapBySchedule(int $idJadwal, ?int $idKelas = null, ?string $search = null): array
    {
        $sql = "SELECT cn.*,
                       s.nama as nama_siswa, s.nis, s.nisn, s.jk,
                       k.nama_kelas,
                       cds.mulai, cds.selesai, cds.lama_ujian, cds.pelanggaran_tab, cds.status as status_ujian
                FROM cbt_nilai cn
                JOIN master_siswa s ON s.id_siswa = cn.id_siswa
                LEFT JOIN kelas_siswa ks ON ks.id_siswa = s.id_siswa AND ks.id_tp = cn.id_tp AND ks.id_smt = cn.id_smt
                LEFT JOIN master_kelas k ON k.id_kelas = ks.id_kelas
                LEFT JOIN cbt_durasi_siswa cds ON cds.id_jadwal = cn.id_jadwal AND cds.id_siswa = cn.id_siswa
                WHERE cn.id_jadwal = ?";

        $params = [$idJadwal];

        if ($idKelas !== null && $idKelas > 0) {
            $sql .= " AND ks.id_kelas = ?";
            $params[] = $idKelas;
        }

        if (!empty($search)) {
            $sql .= " AND (s.nama LIKE ? OR s.nis LIKE ?)";
            $params[] = "%{$search}%";
            $params[] = "%{$search}%";
        }

        $sql .= " ORDER BY k.level_id ASC, k.nama_kelas ASC, s.nama ASC";

        return $this->query($sql, $params);
    }

    /**
     * Delete score for student (for exam re-take).
     */
    public function deleteScore(int $idJadwal, int $idSiswa): bool
    {
        $sql = "DELETE FROM cbt_nilai WHERE id_jadwal = ? AND id_siswa = ?";
        return $this->query($sql, [$idJadwal, $idSiswa]) !== false;
    }
}

