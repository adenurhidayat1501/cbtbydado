<?php

namespace App\Models;

use App\Core\Model;

class LogMateri extends Model
{
    protected string $table = 'log_materi';
    protected string $primaryKey = 'id_log';

    /**
     * Get student log for a specific scheduled material/task.
     */
    public function getStudentLog(int $idSiswa, int $idKjm): ?array
    {
        $sql = "SELECT lm.*, s.nama as nama_siswa, s.nis, s.nisn, s.foto as foto_siswa
                FROM log_materi lm
                JOIN master_siswa s ON s.id_siswa = lm.id_siswa
                WHERE lm.id_siswa = ? AND lm.id_kjm = ?
                LIMIT 1";

        $rows = $this->query($sql, [$idSiswa, $idKjm]);
        if (empty($rows)) {
            return null;
        }

        $row = $rows[0];
        if (is_string($row['file'])) {
            $row['file_list'] = json_decode($row['file'], true) ?: [];
        } else {
            $row['file_list'] = (array)($row['file'] ?? []);
        }

        return $row;
    }

    /**
     * Record student attendance / reading event atomically into log_materi.
     */
    public function logRead(int $idSiswa, int $idKjm, ?int $jamKe = null): bool
    {
        $existing = $this->first([
            'id_siswa' => $idSiswa,
            'id_kjm' => $idKjm,
        ]);

        if ($existing) {
            // Already logged reading or submitted; update jam_ke if not recorded yet
            if (empty($existing['jam_ke']) && $jamKe !== null) {
                return $this->update($existing['id_log'], ['jam_ke' => $jamKe]);
            }
            return true;
        }

        $payload = [
            'id_siswa' => $idSiswa,
            'id_kjm' => $idKjm,
            'jam_ke' => $jamKe,
            'log_type' => 1, // 1 = Baca / Hadir
            'log_time' => date('Y-m-d H:i:s'),
        ];

        return (bool)$this->insert($payload);
    }

    /**
     * Student submits assignment answer (text and/or attachments).
     */
    public function submitTugas(int $idSiswa, int $idKjm, ?string $isiTugas, ?array $files = null, ?int $jamKe = null): bool
    {
        $existing = $this->first([
            'id_siswa' => $idSiswa,
            'id_kjm' => $idKjm,
        ]);

        $fileJson = !empty($files) ? json_encode($files, JSON_UNESCAPED_UNICODE) : null;
        $now = date('Y-m-d H:i:s');

        if ($existing) {
            // If already submitted and new files are empty, keep existing files
            if ($fileJson === null && !empty($existing['file'])) {
                $fileJson = $existing['file'];
            }

            $payload = [
                'log_type' => 3, // 3 = Kumpul Tugas
                'finish_time' => $now,
                'isi_tugas' => $isiTugas,
                'file' => $fileJson,
            ];
            if ($jamKe !== null && empty($existing['jam_ke'])) {
                $payload['jam_ke'] = $jamKe;
            }

            return $this->update($existing['id_log'], $payload);
        } else {
            $payload = [
                'id_siswa' => $idSiswa,
                'id_kjm' => $idKjm,
                'jam_ke' => $jamKe,
                'log_type' => 3,
                'log_time' => $now,
                'finish_time' => $now,
                'isi_tugas' => $isiTugas,
                'file' => $fileJson,
            ];

            return (bool)$this->insert($payload);
        }
    }

    /**
     * Teacher evaluates student assignment and provides grade (0-100) and feedback notes.
     */
    public function gradeTugas(int $idLog, float $nilai, ?string $catatan = null): bool
    {
        $nilai = max(0.0, min(100.0, $nilai));

        return $this->update($idLog, [
            'nilai' => $nilai,
            'catatan' => $catatan !== null ? trim($catatan) : null,
        ]);
    }

    /**
     * Get all submissions for a scheduled assignment.
     */
    public function getSubmissionsForSchedule(int $idKjm): array
    {
        $sql = "SELECT s.id_siswa, s.nis, s.nisn, s.nama, s.foto,
                       lm.id_log, lm.log_type, lm.log_time, lm.finish_time, lm.isi_tugas, lm.file as submitted_file,
                       lm.nilai, lm.catatan
                FROM kelas_jadwal_materi kjm
                JOIN kelas_siswa ks ON ks.id_kelas = kjm.id_kelas AND ks.id_tp = kjm.id_tp AND ks.id_smt = kjm.id_smt
                JOIN master_siswa s ON s.id_siswa = ks.id_siswa
                LEFT JOIN log_materi lm ON lm.id_kjm = kjm.id_kjm AND lm.id_siswa = s.id_siswa
                WHERE kjm.id_kjm = ?
                ORDER BY s.nama ASC";

        $rows = $this->query($sql, [$idKjm]);
        foreach ($rows as &$row) {
            if (is_string($row['submitted_file'])) {
                $row['file_list'] = json_decode($row['submitted_file'], true) ?: [];
            } else {
                $row['file_list'] = (array)($row['submitted_file'] ?? []);
            }
        }
        unset($row);

        return $rows;
    }

    /**
     * Daily attendance recap for a specific class, subject, and date.
     */
    public function getRekapHarian(int $idTp, int $idSmt, int $idKelas, int $idMapel, string $date): array
    {
        $sql = "SELECT s.id_siswa, s.nis, s.nisn, s.nama, s.jk,
                       lm.id_log, lm.jam_ke, lm.log_type, lm.log_time, lm.finish_time,
                       kjm.id_kjm, km.judul_materi, km.jenis
                FROM kelas_siswa ks
                JOIN master_siswa s ON s.id_siswa = ks.id_siswa
                LEFT JOIN kelas_jadwal_materi kjm ON kjm.id_kelas = ks.id_kelas 
                     AND kjm.id_tp = ks.id_tp 
                     AND kjm.id_smt = ks.id_smt 
                     AND kjm.id_mapel = ? 
                     AND kjm.jadwal_materi = ?
                LEFT JOIN kelas_materi km ON km.id_materi = kjm.id_materi
                LEFT JOIN log_materi lm ON lm.id_kjm = kjm.id_kjm AND lm.id_siswa = s.id_siswa
                WHERE ks.id_tp = ? AND ks.id_smt = ? AND ks.id_kelas = ?
                ORDER BY s.nama ASC";

        return $this->query($sql, [$idMapel, $date, $idTp, $idSmt, $idKelas]);
    }

    /**
     * Monthly attendance recap for a specific class, subject, month, and year.
     */
    public function getRekapBulanan(int $idTp, int $idSmt, int $idKelas, int $idMapel, int $bulan, int $tahun): array
    {
        // 1. Get all scheduled meetings for this mapel in this month
        $sqlMeetings = "SELECT DISTINCT kjm.id_kjm, kjm.jadwal_materi, km.judul_materi, km.jenis
                        FROM kelas_jadwal_materi kjm
                        JOIN kelas_materi km ON km.id_materi = kjm.id_materi
                        WHERE kjm.id_tp = ? AND kjm.id_smt = ? AND kjm.id_kelas = ? AND kjm.id_mapel = ?
                          AND MONTH(kjm.jadwal_materi) = ? AND YEAR(kjm.jadwal_materi) = ?
                        ORDER BY kjm.jadwal_materi ASC";

        $meetings = $this->query($sqlMeetings, [$idTp, $idSmt, $idKelas, $idMapel, $bulan, $tahun]);

        // 2. Get all students in this class
        $sqlStudents = "SELECT s.id_siswa, s.nis, s.nisn, s.nama, s.jk
                        FROM kelas_siswa ks
                        JOIN master_siswa s ON s.id_siswa = ks.id_siswa
                        WHERE ks.id_tp = ? AND ks.id_smt = ? AND ks.id_kelas = ?
                        ORDER BY s.nama ASC";

        $students = $this->query($sqlStudents, [$idTp, $idSmt, $idKelas]);

        // 3. Get all logs for these meetings
        $attendanceMap = [];
        if (!empty($meetings)) {
            $kjmIds = array_column($meetings, 'id_kjm');
            $inClause = implode(',', array_map('intval', $kjmIds));
            $sqlLogs = "SELECT id_siswa, id_kjm, log_type, log_time FROM log_materi WHERE id_kjm IN ({$inClause})";
            $logs = $this->query($sqlLogs);
            foreach ($logs as $l) {
                $attendanceMap[$l['id_siswa']][$l['id_kjm']] = $l;
            }
        }

        $totalMeetings = count($meetings);

        // 4. Build student summary matrix
        $rekap = [];
        foreach ($students as $stu) {
            $hadirCount = 0;
            $meetingStatus = [];

            foreach ($meetings as $m) {
                $idKjm = $m['id_kjm'];
                $isHadir = isset($attendanceMap[$stu['id_siswa']][$idKjm]);
                if ($isHadir) {
                    $hadirCount++;
                }
                $meetingStatus[$idKjm] = [
                    'hadir' => $isHadir,
                    'log_time' => $attendanceMap[$stu['id_siswa']][$idKjm]['log_time'] ?? null,
                ];
            }

            $persen = $totalMeetings > 0 ? round(($hadirCount / $totalMeetings) * 100, 1) : 0;

            $rekap[] = [
                'siswa' => $stu,
                'meeting_status' => $meetingStatus,
                'total_hadir' => $hadirCount,
                'total_pertemuan' => $totalMeetings,
                'persentase' => $persen,
            ];
        }

        return [
            'meetings' => $meetings,
            'students_rekap' => $rekap,
        ];
    }

    /**
     * Get assignment grades recap for a class (and optional mapel).
     */
    public function getRekapNilaiTugas(int $idTp, int $idSmt, int $idKelas, ?int $idMapel = null): array
    {
        // 1. Get all scheduled tasks (jenis = 2) for this class
        $sqlTasks = "SELECT kjm.id_kjm, kjm.jadwal_materi, km.id_materi, km.judul_materi, km.kode_materi, m.nama_mapel, m.kode as kode_mapel
                     FROM kelas_jadwal_materi kjm
                     JOIN kelas_materi km ON km.id_materi = kjm.id_materi
                     JOIN master_mapel m ON m.id_mapel = kjm.id_mapel
                     WHERE kjm.id_tp = ? AND kjm.id_smt = ? AND kjm.id_kelas = ? AND kjm.jenis = 2";

        $taskParams = [$idTp, $idSmt, $idKelas];
        if ($idMapel !== null) {
            $sqlTasks .= " AND kjm.id_mapel = ?";
            $taskParams[] = $idMapel;
        }
        $sqlTasks .= " ORDER BY kjm.jadwal_materi ASC, kjm.id_kjm ASC";

        $tasks = $this->query($sqlTasks, $taskParams);

        // 2. Get students in the class
        $sqlStudents = "SELECT s.id_siswa, s.nis, s.nisn, s.nama
                        FROM kelas_siswa ks
                        JOIN master_siswa s ON s.id_siswa = ks.id_siswa
                        WHERE ks.id_tp = ? AND ks.id_smt = ? AND ks.id_kelas = ?
                        ORDER BY s.nama ASC";

        $students = $this->query($sqlStudents, [$idTp, $idSmt, $idKelas]);

        // 3. Get all grades for these tasks
        $gradeMap = [];
        if (!empty($tasks)) {
            $kjmIds = array_column($tasks, 'id_kjm');
            $inClause = implode(',', array_map('intval', $kjmIds));
            $sqlGrades = "SELECT id_siswa, id_kjm, nilai, log_type FROM log_materi WHERE id_kjm IN ({$inClause})";
            $grades = $this->query($sqlGrades);
            foreach ($grades as $g) {
                $gradeMap[$g['id_siswa']][$g['id_kjm']] = $g;
            }
        }

        // 4. Calculate recap matrix per student
        $rekap = [];
        foreach ($students as $stu) {
            $taskScores = [];
            $totalNilai = 0;
            $countScored = 0;

            foreach ($tasks as $t) {
                $idKjm = $t['id_kjm'];
                $gradeRecord = $gradeMap[$stu['id_siswa']][$idKjm] ?? null;
                $score = $gradeRecord && $gradeRecord['nilai'] !== null ? (float)$gradeRecord['nilai'] : null;

                $taskScores[$idKjm] = [
                    'submitted' => ($gradeRecord && (int)$gradeRecord['log_type'] === 3),
                    'nilai' => $score,
                ];

                if ($score !== null) {
                    $totalNilai += $score;
                    $countScored++;
                }
            }

            $rataRata = $countScored > 0 ? round($totalNilai / $countScored, 2) : 0;

            $rekap[] = [
                'siswa' => $stu,
                'scores' => $taskScores,
                'count_scored' => $countScored,
                'rata_rata' => $rataRata,
            ];
        }

        return [
            'tasks' => $tasks,
            'students_rekap' => $rekap,
        ];
    }
}

