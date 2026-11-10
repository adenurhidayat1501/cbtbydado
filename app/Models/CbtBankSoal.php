<?php

namespace App\Models;

use App\Core\Model;

class CbtBankSoal extends Model
{
    protected string $table = 'cbt_bank_soal';
    protected string $primaryKey = 'id_bank';

    /**
     * Get list of question banks filtered by TP, SMT, and optional guru, mapel, level.
     */
    public function getAllByFilter(int $idTp, int $idSmt, ?int $idGuru = null, ?int $idMapel = null, ?int $level = null): array
    {
        $sql = "SELECT bs.*, 
                       m.nama_mapel, m.kode as kode_mapel,
                       g.nama_guru, g.kode_guru,
                       j.kode_jenis, j.nama_jenis,
                       (SELECT COUNT(*) FROM cbt_soal s WHERE s.bank_id = bs.id_bank) as total_butir_soal,
                       (SELECT COUNT(*) FROM cbt_jadwal cj WHERE cj.id_bank = bs.id_bank) as total_jadwal
                FROM cbt_bank_soal bs
                JOIN master_mapel m ON m.id_mapel = bs.bank_mapel_id
                JOIN master_guru g ON g.id_guru = bs.bank_guru_id
                LEFT JOIN cbt_jenis j ON j.id_jenis = bs.bank_jenis_id
                WHERE bs.id_tp = ? AND bs.id_smt = ?";

        $params = [$idTp, $idSmt];

        if ($idGuru !== null && $idGuru > 0) {
            $sql .= " AND bs.bank_guru_id = ?";
            $params[] = $idGuru;
        }

        if ($idMapel !== null && $idMapel > 0) {
            $sql .= " AND bs.bank_mapel_id = ?";
            $params[] = $idMapel;
        }

        if ($level !== null && $level > 0) {
            $sql .= " AND bs.bank_level = ?";
            $params[] = $level;
        }

        $sql .= " ORDER BY bs.created_at DESC";

        $rows = $this->query($sql, $params);
        foreach ($rows as &$r) {
            if (is_string($r['bank_kelas'])) {
                $r['kelas_ids'] = json_decode($r['bank_kelas'], true) ?: [];
            } else {
                $r['kelas_ids'] = (array)($r['bank_kelas'] ?? []);
            }
        }
        unset($r);

        return $rows;
    }

    /**
     * Get detail of a single question bank.
     */
    public function getDetail(int $idBank): ?array
    {
        $sql = "SELECT bs.*, 
                       m.nama_mapel, m.kode as kode_mapel,
                       g.nama_guru, g.kode_guru,
                       j.kode_jenis, j.nama_jenis
                FROM cbt_bank_soal bs
                JOIN master_mapel m ON m.id_mapel = bs.bank_mapel_id
                JOIN master_guru g ON g.id_guru = bs.bank_guru_id
                LEFT JOIN cbt_jenis j ON j.id_jenis = bs.bank_jenis_id
                WHERE bs.id_bank = ?
                LIMIT 1";

        $rows = $this->query($sql, [$idBank]);
        if (empty($rows)) {
            return null;
        }

        $row = $rows[0];
        if (is_string($row['bank_kelas'])) {
            $row['kelas_ids'] = json_decode($row['bank_kelas'], true) ?: [];
        } else {
            $row['kelas_ids'] = (array)($row['bank_kelas'] ?? []);
        }

        return $row;
    }

    /**
     * Check if bank soal is locked because an exam is currently active.
     */
    public function isLocked(int $idBank): bool
    {
        $sql = "SELECT COUNT(*) as active_students
                FROM cbt_durasi_siswa ds
                JOIN cbt_jadwal j ON j.id_jadwal = ds.id_jadwal
                WHERE j.id_bank = ? AND ds.status = 1";

        $rows = $this->query($sql, [$idBank]);
        return ((int)($rows[0]['active_students'] ?? 0)) > 0;
    }

    /**
     * Strictly verify teacher ownership for question bank.
     */
    public function isGuruAuthorized(int $idBank, int $idGuru): bool
    {
        $bank = $this->find($idBank);
        if (!$bank) {
            return false;
        }
        return (int)$bank['bank_guru_id'] === $idGuru;
    }

    /**
     * Recalculate and synchronize question counts by type.
     */
    public function updateQuestionCounts(int $idBank): void
    {
        $sql = "SELECT jenis, COUNT(*) as jml FROM cbt_soal WHERE bank_id = ? GROUP BY jenis";
        $rows = $this->query($sql, [$idBank]);

        $counts = [
            1 => 0, // PG
            2 => 0, // PGK
            3 => 0, // Jodohkan
            4 => 0, // Isian
            5 => 0, // Esai
        ];

        foreach ($rows as $r) {
            $t = (int)$r['jenis'];
            if (isset($counts[$t])) {
                $counts[$t] = (int)$r['jml'];
            }
        }

        $this->update($idBank, [
            'jml_soal' => $counts[1],
            'jml_kompleks' => $counts[2],
            'jml_jodohkan' => $counts[3],
            'jml_isian' => $counts[4],
            'jml_esai' => $counts[5],
        ]);
    }
}
