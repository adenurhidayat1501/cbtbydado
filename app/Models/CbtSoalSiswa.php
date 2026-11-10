<?php

declare(strict_types=1);

namespace App\Models;

use App\Core\Model;

class CbtSoalSiswa extends Model
{
    protected string $table = 'cbt_soal_siswa';
    protected string $primaryKey = 'id_soal_siswa';

    /**
     * Get student questions for an exam, joined with original question data.
     * Ordered by student's alias question number.
     */
    public function getStudentQuestions(int $idJadwal, int $idSiswa): array
    {
        $sql = "SELECT css.*, 
                       s.soal, s.opsi_a, s.opsi_b, s.opsi_c, s.opsi_d, s.opsi_e
                FROM cbt_soal_siswa css
                JOIN cbt_soal s ON s.id_soal = css.id_soal
                WHERE css.id_jadwal = ? AND css.id_siswa = ?
                ORDER BY css.no_soal_alias ASC";

        return $this->query($sql, [$idJadwal, $idSiswa]);
    }

    /**
     * Get answered count and question navigation status summary.
     */
    public function getNavStatus(int $idJadwal, int $idSiswa): array
    {
        $questions = $this->getStudentQuestions($idJadwal, $idSiswa);
        $total = count($questions);
        $answered = 0;
        $ragu = 0;

        foreach ($questions as $q) {
            $isAnswered = false;
            $jenis = (int)$q['jenis_soal'];
            $ans = $q['jawaban_siswa'];

            if ($jenis === 1) {
                // PG: answered if not empty string
                $isAnswered = !empty($ans);
            } elseif ($jenis === 2) {
                // PGK: answered if JSON array has elements
                $arr = is_string($ans) ? json_decode($ans, true) : $ans;
                $isAnswered = is_array($arr) && count($arr) > 0;
            } elseif ($jenis === 3) {
                // Menjodohkan: answered ONLY IF links has at least 1 non-empty connection
                $jodoh = is_string($ans) ? json_decode($ans, true) : $ans;
                if (is_array($jodoh) && isset($jodoh['links']) && is_array($jodoh['links'])) {
                    foreach ($jodoh['links'] as $targets) {
                        if (is_array($targets) && count($targets) > 0) {
                            $isAnswered = true;
                            break;
                        }
                    }
                }
            } elseif ($jenis === 4 || $jenis === 5) {
                // Isian or Essai: answered if non-empty trimmed text
                $isAnswered = !empty(trim((string)$ans));
            }

            if ($isAnswered) {
                $answered++;
            }
            if ((int)$q['ragu'] === 1) {
                $ragu++;
            }
        }

        return [
            'total' => $total,
            'answered' => $answered,
            'unanswered' => $total - $answered,
            'ragu' => $ragu,
        ];
    }

    /**
     * Atomic single-row update of student answer.
     * Highly optimized for 1,000 concurrent students via PK update.
     */
    public function saveAnswer(int $idSoalSiswa, $jawabanSiswa, $jawabanAlias = null, int $ragu = 0): bool
    {
        $sql = "UPDATE cbt_soal_siswa 
                SET jawaban_siswa = ?, jawaban_alias = ?, ragu = ?
                WHERE id_soal_siswa = ?";

        $jsonAns = is_array($jawabanSiswa) ? json_encode($jawabanSiswa, JSON_UNESCAPED_UNICODE) : (string)$jawabanSiswa;
        $jsonAlias = is_array($jawabanAlias) ? json_encode($jawabanAlias, JSON_UNESCAPED_UNICODE) : (string)$jawabanAlias;

        return $this->query($sql, [$jsonAns, $jsonAlias, $ragu, $idSoalSiswa]) !== false;
    }

    /**
     * Bulk insert student questions during randomization.
     */
    public function bulkInsert(array $rows): bool
    {
        if (empty($rows)) {
            return false;
        }

        $fields = [
            'id_bank', 'id_jadwal', 'id_soal', 'id_siswa', 'jenis_soal', 'no_soal_alias',
            'opsi_alias_a', 'opsi_alias_b', 'opsi_alias_c', 'opsi_alias_d', 'opsi_alias_e',
            'jawaban_alias', 'jawaban_siswa', 'jawaban_benar', 'point_soal', 'nilai_koreksi',
            'nilai_otomatis', 'ragu'
        ];

        $colList = '`' . implode('`, `', $fields) . '`';
        $placeholders = [];
        $values = [];

        foreach ($rows as $row) {
            $rowPlaceholders = [];
            foreach ($fields as $field) {
                $rowPlaceholders[] = '?';
                $val = $row[$field] ?? null;
                if (is_array($val)) {
                    $val = json_encode($val, JSON_UNESCAPED_UNICODE);
                }
                $values[] = $val;
            }
            $placeholders[] = '(' . implode(', ', $rowPlaceholders) . ')';
        }

        $sql = "INSERT INTO cbt_soal_siswa ({$colList}) VALUES " . implode(', ', $placeholders);
        return $this->query($sql, $values) !== false;
    }

    /**
     * Delete all questions for a student on a schedule (for exam re-take).
     */
    public function deleteStudentQuestions(int $idJadwal, int $idSiswa): bool
    {
        $sql = "DELETE FROM cbt_soal_siswa WHERE id_jadwal = ? AND id_siswa = ?";
        return $this->query($sql, [$idJadwal, $idSiswa]) !== false;
    }
}

