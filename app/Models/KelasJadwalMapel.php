<?php

namespace App\Models;

use App\Core\Model;

class KelasJadwalMapel extends Model
{
    protected string $table = 'kelas_jadwal_mapel';
    protected string $primaryKey = 'id_jadwal';

    /**
     * Retrieve weekly schedule matrix for a specific class in an academic period.
     * Returns associative array keyed by [id_hari][jam_ke].
     */
    public function getMatrix(int $idTp, int $idSmt, int $idKelas): array
    {
        $sql = "SELECT jm.*, m.nama_mapel, m.kode as kode_mapel, g.nama_guru, g.kode_guru
                FROM kelas_jadwal_mapel jm
                JOIN master_mapel m ON m.id_mapel = jm.id_mapel
                LEFT JOIN master_guru g ON g.id_guru = jm.id_guru
                WHERE jm.id_tp = ? AND jm.id_smt = ? AND jm.id_kelas = ?
                ORDER BY jm.id_hari ASC, jm.jam_ke ASC";

        $rows = $this->query($sql, [$idTp, $idSmt, $idKelas]);
        $matrix = [];

        foreach ($rows as $row) {
            $h = (int)$row['id_hari'];
            $j = (int)$row['jam_ke'];
            $matrix[$h][$j] = $row;
        }

        return $matrix;
    }

    /**
     * Save, update, or remove a single cell in the weekly schedule matrix.
     */
    public function saveCell(int $idTp, int $idSmt, int $idKelas, int $idHari, int $jamKe, ?int $idMapel, ?int $idGuru = null): bool
    {
        $existing = $this->first([
            'id_tp' => $idTp,
            'id_smt' => $idSmt,
            'id_kelas' => $idKelas,
            'id_hari' => $idHari,
            'jam_ke' => $jamKe,
        ]);

        if (empty($idMapel)) {
            // Clear cell if mapel is empty
            if ($existing) {
                return $this->delete($existing['id_jadwal']);
            }
            return true;
        }

        $payload = [
            'id_tp' => $idTp,
            'id_smt' => $idSmt,
            'id_kelas' => $idKelas,
            'id_hari' => $idHari,
            'jam_ke' => $jamKe,
            'id_mapel' => $idMapel,
            'id_guru' => $idGuru ?: null,
        ];

        if ($existing) {
            return $this->update($existing['id_jadwal'], $payload);
        } else {
            return (bool)$this->insert($payload);
        }
    }

    /**
     * Get all teaching schedule entries for a specific teacher.
     */
    public function getJadwalForGuru(int $idTp, int $idSmt, int $idGuru): array
    {
        $sql = "SELECT jm.*, k.nama_kelas, m.nama_mapel, m.kode as kode_mapel
                FROM kelas_jadwal_mapel jm
                JOIN master_kelas k ON k.id_kelas = jm.id_kelas
                JOIN master_mapel m ON m.id_mapel = jm.id_mapel
                WHERE jm.id_tp = ? AND jm.id_smt = ? AND jm.id_guru = ?
                ORDER BY jm.id_hari ASC, jm.jam_ke ASC";

        return $this->query($sql, [$idTp, $idSmt, $idGuru]);
    }

    /**
     * Get schedule for a class on a specific day (e.g. today).
     */
    public function getJadwalHariIni(int $idTp, int $idSmt, int $idKelas, int $idHari): array
    {
        $sql = "SELECT jm.*, m.nama_mapel, m.kode as kode_mapel, g.nama_guru
                FROM kelas_jadwal_mapel jm
                JOIN master_mapel m ON m.id_mapel = jm.id_mapel
                LEFT JOIN master_guru g ON g.id_guru = jm.id_guru
                WHERE jm.id_tp = ? AND jm.id_smt = ? AND jm.id_kelas = ? AND jm.id_hari = ?
                ORDER BY jm.jam_ke ASC";

        return $this->query($sql, [$idTp, $idSmt, $idKelas, $idHari]);
    }

    /**
     * Find if a subject is scheduled on a given day for a class.
     */
    public function findJadwalMapel(int $idTp, int $idSmt, int $idKelas, int $idMapel, int $idHari): ?array
    {
        $sql = "SELECT jm.*, m.nama_mapel, g.nama_guru
                FROM kelas_jadwal_mapel jm
                JOIN master_mapel m ON m.id_mapel = jm.id_mapel
                LEFT JOIN master_guru g ON g.id_guru = jm.id_guru
                WHERE jm.id_tp = ? AND jm.id_smt = ? AND jm.id_kelas = ? AND jm.id_mapel = ? AND jm.id_hari = ?
                ORDER BY jm.jam_ke ASC
                LIMIT 1";

        $rows = $this->query($sql, [$idTp, $idSmt, $idKelas, $idMapel, $idHari]);
        return $rows[0] ?? null;
    }
}

