<?php

namespace App\Models;

use App\Core\Model;

class CbtPengawas extends Model
{
    protected string $table = 'cbt_pengawas';
    protected string $primaryKey = 'id_pengawas';

    /**
     * Get pengawas assignment matrix for a specific exam schedule.
     * Returns array keyed by [id_ruang][id_sesi].
     */
    public function getPengawasMatrix(int $idJadwal): array
    {
        $sql = "SELECT cp.*, r.nama_ruang, r.kode_ruang, s.nama_sesi, s.kode_sesi, s.waktu_mulai, s.waktu_akhir
                FROM cbt_pengawas cp
                JOIN cbt_ruang r ON r.id_ruang = cp.id_ruang
                JOIN cbt_sesi s ON s.id_sesi = cp.id_sesi
                WHERE cp.id_jadwal = ?
                ORDER BY r.kode_ruang ASC, s.waktu_mulai ASC";

        $rows = $this->query($sql, [$idJadwal]);
        $guruModel = new MasterGuru();
        $allGurus = $guruModel->where(['status' => 1]);
        $guruMap = [];
        foreach ($allGurus as $g) {
            $guruMap[$g['id_guru']] = $g['nama_guru'];
        }

        $matrix = [];
        foreach ($rows as $r) {
            $ruangId = (int)$r['id_ruang'];
            $sesiId = (int)$r['id_sesi'];
            $guruIds = is_string($r['id_guru']) ? (json_decode($r['id_guru'], true) ?: []) : (array)($r['id_guru'] ?? []);

            $guruNames = [];
            foreach ($guruIds as $gid) {
                if (isset($guruMap[$gid])) {
                    $guruNames[] = $guruMap[$gid];
                }
            }

            $r['guru_ids'] = $guruIds;
            $r['guru_names'] = $guruNames;
            $r['formatted_names'] = !empty($guruNames) ? implode(', ', $guruNames) : '-';

            $matrix[$ruangId][$sesiId] = $r;
        }

        return $matrix;
    }

    /**
     * Save or update proctor assignment for a schedule, room, and session.
     */
    public function savePengawas(int $idTp, int $idSmt, int $idJadwal, int $idRuang, int $idSesi, array $guruIds): bool
    {
        $existing = $this->first([
            'id_jadwal' => $idJadwal,
            'id_ruang' => $idRuang,
            'id_sesi' => $idSesi,
        ]);

        $guruIdsFiltered = array_values(array_filter(array_map('intval', $guruIds)));
        $guruJson = json_encode($guruIdsFiltered);

        if (empty($guruIdsFiltered)) {
            if ($existing) {
                return $this->delete($existing['id_pengawas']);
            }
            return true;
        }

        $payload = [
            'id_tp' => $idTp,
            'id_smt' => $idSmt,
            'id_jadwal' => $idJadwal,
            'id_ruang' => $idRuang,
            'id_sesi' => $idSesi,
            'id_guru' => $guruJson,
        ];

        if ($existing) {
            return $this->update($existing['id_pengawas'], $payload);
        } else {
            return (bool)$this->insert($payload);
        }
    }

    /**
     * Strictly check if a specific teacher is assigned as proctor/pengawas.
     * Prevents assuming all teachers are proctors.
     */
    public function isGuruPengawas(int $idGuru, ?int $idJadwal = null): bool
    {
        $sql = "SELECT COUNT(*) as count FROM cbt_pengawas WHERE JSON_CONTAINS(id_guru, ?)";
        $params = [json_encode((int)$idGuru)];

        if ($idJadwal !== null && $idJadwal > 0) {
            $sql .= " AND id_jadwal = ?";
            $params[] = $idJadwal;
        }

        $rows = $this->query($sql, $params);
        return ((int)($rows[0]['count'] ?? 0)) > 0;
    }

    /**
     * Alias for isGuruPengawas accepting period.
     */
    public function isGuruAssigned(int $idTp, int $idSmt, int $idGuru, ?int $idJadwal = null): bool
    {
        return $this->isGuruPengawas($idGuru, $idJadwal);
    }

    /**
     * Get list of exam schedules where this teacher is assigned as proctor.
     */
    public function getSchedulesForPengawas(int $idTp, int $idSmt, int $idGuru): array
    {
        $sql = "SELECT cp.*, 
                       j.id_jadwal, j.tgl_mulai, j.tgl_selesai, j.durasi_ujian, j.jam_ke, j.status as jadwal_status,
                       bs.bank_kode, m.nama_mapel, jn.nama_jenis,
                       r.nama_ruang, r.kode_ruang,
                       s.nama_sesi, s.kode_sesi, s.waktu_mulai, s.waktu_akhir
                FROM cbt_pengawas cp
                JOIN cbt_jadwal j ON j.id_jadwal = cp.id_jadwal
                JOIN cbt_bank_soal bs ON bs.id_bank = j.id_bank
                JOIN master_mapel m ON m.id_mapel = bs.bank_mapel_id
                JOIN cbt_jenis jn ON jn.id_jenis = j.id_jenis
                JOIN cbt_ruang r ON r.id_ruang = cp.id_ruang
                JOIN cbt_sesi s ON s.id_sesi = cp.id_sesi
                WHERE cp.id_tp = ? AND cp.id_smt = ? AND JSON_CONTAINS(cp.id_guru, ?)
                ORDER BY j.tgl_mulai DESC, s.waktu_mulai ASC";

        return $this->query($sql, [$idTp, $idSmt, json_encode((int)$idGuru)]);
    }

    /**
     * Safe helper to format proctors without returning undefined.
     * Can accept either a string (e.g. from subquery or JS), or schedule ID (int).
     */
    public function safeFormatProctors($input): string
    {
        if (is_int($input) || ctype_digit((string)$input)) {
            $idJadwal = (int)$input;
            $rows = $this->query("SELECT id_guru FROM cbt_pengawas WHERE id_jadwal = ?", [$idJadwal]);
            if (empty($rows)) {
                return '-';
            }
            $guruIds = [];
            foreach ($rows as $r) {
                $ids = is_string($r['id_guru']) ? json_decode($r['id_guru'], true) : $r['id_guru'];
                if (is_array($ids)) {
                    foreach ($ids as $gid) {
                        $gid = (int)$gid;
                        if ($gid > 0) {
                            $guruIds[$gid] = true;
                        }
                    }
                }
            }
            if (empty($guruIds)) {
                return '-';
            }
            $inClause = implode(',', array_keys($guruIds));
            $gurus = $this->query("SELECT nama_guru FROM master_guru WHERE id_guru IN ({$inClause}) ORDER BY nama_guru ASC");
            if (empty($gurus)) {
                return '-';
            }
            return implode(', ', array_column($gurus, 'nama_guru'));
        }

        if (empty($input) || $input === 'undefined' || $input === 'null' || trim((string)$input) === '') {
            return '-';
        }

        return (string)$input;
    }
}
