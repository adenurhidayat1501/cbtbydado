<?php

namespace App\Models;

use App\Core\Model;

class KelasSiswa extends Model
{
    protected string $table = 'kelas_siswa';
    protected string $primaryKey = 'id_kelas_siswa';

    public function getStudentsInClass(int $idKelas, int $idTp, int $idSmt): array
    {
        $sql = "SELECT ks.*, s.nis, s.nisn, s.nama, s.jk, s.foto, s.status, s.no_hp
                FROM kelas_siswa ks
                JOIN master_siswa s ON s.id_siswa = ks.id_siswa
                WHERE ks.id_kelas = ? AND ks.id_tp = ? AND ks.id_smt = ?
                ORDER BY s.nama ASC";
        return $this->query($sql, [$idKelas, $idTp, $idSmt]);
    }

    public function getAvailableStudents(int $idTp, int $idSmt, ?string $search = null): array
    {
        $sql = "SELECT s.*
                FROM master_siswa s
                WHERE s.status = 1
                AND s.id_siswa NOT IN (
                    SELECT ks.id_siswa FROM kelas_siswa ks WHERE ks.id_tp = ? AND ks.id_smt = ?
                )";
        $params = [$idTp, $idSmt];

        if (!empty($search)) {
            $sql .= " AND (s.nis LIKE ? OR s.nama LIKE ? OR s.nisn LIKE ?)";
            $searchTerm = "%{$search}%";
            $params[] = $searchTerm;
            $params[] = $searchTerm;
            $params[] = $searchTerm;
        }

        $sql .= " ORDER BY s.nama ASC";
        return $this->query($sql, $params);
    }

    public function addStudentToClass(int $idTp, int $idSmt, int $idKelas, int $idSiswa): bool
    {
        // Remove from existing class in same period if any
        $this->execute(
            "DELETE FROM kelas_siswa WHERE id_tp = ? AND id_smt = ? AND id_siswa = ?",
            [$idTp, $idSmt, $idSiswa]
        );

        return (bool)$this->insert([
            'id_tp' => $idTp,
            'id_smt' => $idSmt,
            'id_kelas' => $idKelas,
            'id_siswa' => $idSiswa,
        ]);
    }

    public function removeStudentFromClass(int $idTp, int $idSmt, int $idKelas, int $idSiswa): bool
    {
        return (bool)$this->execute(
            "DELETE FROM kelas_siswa WHERE id_tp = ? AND id_smt = ? AND id_kelas = ? AND id_siswa = ?",
            [$idTp, $idSmt, $idKelas, $idSiswa]
        );
    }
}

