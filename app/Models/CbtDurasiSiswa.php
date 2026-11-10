<?php

declare(strict_types=1);

namespace App\Models;

use App\Core\Model;

class CbtDurasiSiswa extends Model
{
    protected string $table = 'cbt_durasi_siswa';
    protected string $primaryKey = 'id_durasi';

    /**
     * Get exam session status for a student on a specific schedule.
     */
    public function getSession(int $idJadwal, int $idSiswa): ?array
    {
        return $this->first([
            'id_jadwal' => $idJadwal,
            'id_siswa' => $idSiswa,
        ]);
    }

    /**
     * Initialize or resume exam session for a student.
     */
    public function startExam(int $idJadwal, int $idSiswa, int $durasiMenit): array
    {
        $existing = $this->getSession($idJadwal, $idSiswa);

        if (!$existing) {
            $now = date('Y-m-d H:i:s');
            $idDurasi = (int)$this->insert([
                'id_jadwal' => $idJadwal,
                'id_siswa' => $idSiswa,
                'status' => 1, // 1 = Sedang Mengerjakan
                'lama_ujian' => 0,
                'mulai' => $now,
                'selesai' => null,
                'reset' => 0,
                'pelanggaran_tab' => 0,
            ]);

            return [
                'id_durasi' => $idDurasi,
                'id_jadwal' => $idJadwal,
                'id_siswa' => $idSiswa,
                'status' => 1,
                'lama_ujian' => 0,
                'mulai' => $now,
                'selesai' => null,
                'reset' => 0,
                'pelanggaran_tab' => 0,
            ];
        }

        // If reset was requested by proctor (reset=2 allow login)
        if ((int)$existing['reset'] === 2) {
            $this->update($existing['id_durasi'], [
                'reset' => 0,
                'status' => 1,
            ]);
            $existing['reset'] = 0;
            $existing['status'] = 1;
        }

        return $existing;
    }

    /**
     * Record tab violation (Anti-Cheat).
     */
    public function recordViolation(int $idJadwal, int $idSiswa): int
    {
        $session = $this->getSession($idJadwal, $idSiswa);
        if (!$session) {
            return 0;
        }

        $newCount = (int)$session['pelanggaran_tab'] + 1;
        $this->update($session['id_durasi'], [
            'pelanggaran_tab' => $newCount,
        ]);

        return $newCount;
    }

    /**
     * Finish exam session.
     */
    public function finishExam(int $idJadwal, int $idSiswa): bool
    {
        $session = $this->getSession($idJadwal, $idSiswa);
        if (!$session) {
            return false;
        }

        $now = date('Y-m-d H:i:s');
        $startTime = strtotime($session['mulai'] ?? $now);
        $elapsedMinutes = max(1, (int)round((time() - $startTime) / 60));

        return $this->update($session['id_durasi'], [
            'status' => 2, // 2 = Selesai
            'selesai' => $now,
            'lama_ujian' => $elapsedMinutes,
        ]);
    }

    /**
     * Reset login permission for a student.
     */
    public function resetLogin(int $idJadwal, int $idSiswa): bool
    {
        $session = $this->getSession($idJadwal, $idSiswa);
        if (!$session) {
            return false;
        }

        return $this->update($session['id_durasi'], [
            'reset' => 2, // 2 = Izin Login Kembali
        ]);
    }

    /**
     * Reset exam timer or add minutes.
     */
    public function resetTimer(int $idJadwal, int $idSiswa, ?int $extraMinutes = null): bool
    {
        $session = $this->getSession($idJadwal, $idSiswa);
        if (!$session) {
            return false;
        }

        if ($extraMinutes !== null && $extraMinutes > 0) {
            // Extend start time by pushing it forward
            $newStart = date('Y-m-d H:i:s', strtotime($session['mulai'] ?? date('Y-m-d H:i:s')) + ($extraMinutes * 60));
            return $this->update($session['id_durasi'], [
                'mulai' => $newStart,
                'reset' => 1,
            ]);
        }

        // Full timer restart
        return $this->update($session['id_durasi'], [
            'mulai' => date('Y-m-d H:i:s'),
            'reset' => 1,
            'status' => 1,
        ]);
    }

    /**
     * Delete session for full exam re-take.
     */
    public function deleteSession(int $idJadwal, int $idSiswa): bool
    {
        $sql = "DELETE FROM cbt_durasi_siswa WHERE id_jadwal = ? AND id_siswa = ?";
        return $this->query($sql, [$idJadwal, $idSiswa]) !== false;
    }
}

