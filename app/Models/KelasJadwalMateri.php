<?php

namespace App\Models;

use App\Core\Model;

class KelasJadwalMateri extends Model
{
    protected string $table = 'kelas_jadwal_materi';
    protected string $primaryKey = 'id_kjm';

    /**
     * Retrieve scheduled materials or assignments with detailed associations.
     */
    public function getSchedules(int $idTp, int $idSmt, ?int $idKelas = null, ?int $idMapel = null, ?string $date = null, ?int $jenis = null, ?int $idGuru = null): array
    {
        $sql = "SELECT kjm.*, km.judul_materi, km.kode_materi, km.link_video, km.file as files,
                       m.nama_mapel, m.kode as kode_mapel,
                       k.nama_kelas, k.level_id,
                       g.nama_guru, g.kode_guru,
                       (SELECT COUNT(*) FROM log_materi lm WHERE lm.id_kjm = kjm.id_kjm) as total_akses,
                       (SELECT COUNT(*) FROM log_materi lm WHERE lm.id_kjm = kjm.id_kjm AND lm.log_type = 3) as total_kumpul,
                       (SELECT COUNT(*) FROM kelas_siswa ks WHERE ks.id_kelas = kjm.id_kelas AND ks.id_tp = kjm.id_tp AND ks.id_smt = kjm.id_smt) as total_siswa_kelas
                FROM kelas_jadwal_materi kjm
                JOIN kelas_materi km ON km.id_materi = kjm.id_materi
                JOIN master_mapel m ON m.id_mapel = kjm.id_mapel
                JOIN master_kelas k ON k.id_kelas = kjm.id_kelas
                JOIN master_guru g ON g.id_guru = km.id_guru
                WHERE kjm.id_tp = ? AND kjm.id_smt = ?";

        $params = [$idTp, $idSmt];

        if ($idKelas !== null) {
            $sql .= " AND kjm.id_kelas = ?";
            $params[] = $idKelas;
        }

        if ($idMapel !== null) {
            $sql .= " AND kjm.id_mapel = ?";
            $params[] = $idMapel;
        }

        if ($date !== null) {
            $sql .= " AND kjm.jadwal_materi = ?";
            $params[] = $date;
        }

        if ($jenis !== null) {
            $sql .= " AND kjm.jenis = ?";
            $params[] = $jenis;
        }

        if ($idGuru !== null) {
            $sql .= " AND km.id_guru = ?";
            $params[] = $idGuru;
        }

        $sql .= " ORDER BY kjm.jadwal_materi DESC, k.nama_kelas ASC, m.nama_mapel ASC";

        return $this->query($sql, $params);
    }

    /**
     * Get detail of a single schedule record.
     */
    public function getDetail(int $idKjm): ?array
    {
        $sql = "SELECT kjm.*, km.judul_materi, km.kode_materi, km.isi_materi, km.link_video, km.file as files, km.id_guru,
                       m.nama_mapel, m.kode as kode_mapel,
                       k.nama_kelas, k.level_id,
                       g.nama_guru, g.kode_guru, g.foto as foto_guru
                FROM kelas_jadwal_materi kjm
                JOIN kelas_materi km ON km.id_materi = kjm.id_materi
                JOIN master_mapel m ON m.id_mapel = kjm.id_mapel
                JOIN master_kelas k ON k.id_kelas = kjm.id_kelas
                JOIN master_guru g ON g.id_guru = km.id_guru
                WHERE kjm.id_kjm = ?
                LIMIT 1";

        $rows = $this->query($sql, [$idKjm]);
        if (empty($rows)) {
            return null;
        }

        $row = $rows[0];
        if (is_string($row['files'])) {
            $row['file_list'] = json_decode($row['files'], true) ?: [];
        } else {
            $row['file_list'] = (array)($row['files'] ?? []);
        }

        return $row;
    }

    /**
     * Publish / schedule a material or assignment to multiple classes on a specific date.
     */
    public function scheduleMateri(int $idTp, int $idSmt, int $idMateri, array $targetKelas, string $jadwalDate): bool
    {
        $materiModel = new KelasMateri();
        $materi = $materiModel->find($idMateri);
        if (!$materi) {
            return false;
        }

        $idMapel = (int)$materi['id_mapel'];
        $jenis = (int)$materi['jenis'];

        foreach ($targetKelas as $idKelas) {
            $idKelas = (int)$idKelas;
            if ($idKelas <= 0) {
                continue;
            }

            // Check if already scheduled for this class on this date
            $existing = $this->first([
                'id_tp' => $idTp,
                'id_smt' => $idSmt,
                'id_kelas' => $idKelas,
                'id_materi' => $idMateri,
                'jadwal_materi' => $jadwalDate,
            ]);

            if (!$existing) {
                $this->insert([
                    'id_tp' => $idTp,
                    'id_smt' => $idSmt,
                    'id_kelas' => $idKelas,
                    'id_materi' => $idMateri,
                    'id_mapel' => $idMapel,
                    'jenis' => $jenis,
                    'jadwal_materi' => $jadwalDate,
                ]);
            }
        }

        return true;
    }

    /**
     * Get learning items visible to a specific student in their assigned class.
     */
    public function getStudentSchedule(int $idTp, int $idSmt, int $idKelas, ?int $idSiswa = null, ?int $jenis = null): array
    {
        $sql = "SELECT kjm.*, km.judul_materi, km.kode_materi, km.link_video, km.file as files,
                       m.nama_mapel, m.kode as kode_mapel,
                       g.nama_guru, g.kode_guru, g.foto as foto_guru,
                       lm.id_log, lm.log_type, lm.log_time, lm.finish_time, lm.nilai, lm.catatan, lm.file as submitted_file, lm.isi_tugas
                FROM kelas_jadwal_materi kjm
                JOIN kelas_materi km ON km.id_materi = kjm.id_materi
                JOIN master_mapel m ON m.id_mapel = kjm.id_mapel
                JOIN master_guru g ON g.id_guru = km.id_guru
                LEFT JOIN log_materi lm ON lm.id_kjm = kjm.id_kjm AND lm.id_siswa = ?
                WHERE kjm.id_tp = ? AND kjm.id_smt = ? AND kjm.id_kelas = ? AND km.status = 1";

        $params = [$idSiswa, $idTp, $idSmt, $idKelas];

        if ($jenis !== null) {
            $sql .= " AND kjm.jenis = ?";
            $params[] = $jenis;
        }

        $sql .= " ORDER BY kjm.jadwal_materi DESC, kjm.id_kjm DESC";

        $rows = $this->query($sql, $params);
        foreach ($rows as &$row) {
            if (is_string($row['files'])) {
                $row['file_list'] = json_decode($row['files'], true) ?: [];
            } else {
                $row['file_list'] = (array)($row['files'] ?? []);
            }
            if (is_string($row['submitted_file'])) {
                $row['submitted_file_list'] = json_decode($row['submitted_file'], true) ?: [];
            } else {
                $row['submitted_file_list'] = (array)($row['submitted_file'] ?? []);
            }
        }
        unset($row);

        return $rows;
    }
}

