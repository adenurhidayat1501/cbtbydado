<?php

declare(strict_types=1);

namespace App\Services;

use App\Models\CbtBankSoal;
use App\Models\CbtJadwal;
use App\Models\CbtNilai;
use App\Models\CbtSoalSiswa;

class AutoGraderService
{
    private CbtBankSoal $bankModel;
    private CbtJadwal $jadwalModel;
    private CbtSoalSiswa $soalSiswaModel;
    private CbtNilai $nilaiModel;

    public function __construct()
    {
        $this->bankModel = new CbtBankSoal();
        $this->jadwalModel = new CbtJadwal();
        $this->soalSiswaModel = new CbtSoalSiswa();
        $this->nilaiModel = new CbtNilai();
    }

    /**
     * Calculate and store scores for a student on an exam schedule.
     */
    public function gradeExam(int $idJadwal, int $idSiswa): array
    {
        $jadwal = $this->jadwalModel->find($idJadwal);
        if (!$jadwal) {
            throw new \RuntimeException("Jadwal tidak ditemukan.");
        }

        $idTp = (int)$jadwal['id_tp'];
        $idSmt = (int)$jadwal['id_smt'];
        $idBank = (int)$jadwal['id_bank'];

        $bank = $this->bankModel->find($idBank);
        if (!$bank) {
            throw new \RuntimeException("Bank soal tidak ditemukan.");
        }

        $studentQuestions = $this->soalSiswaModel->getStudentQuestions($idJadwal, $idSiswa);

        // Group questions by type
        $grouped = [1 => [], 2 => [], 3 => [], 4 => [], 5 => []];
        foreach ($studentQuestions as $q) {
            $j = (int)$q['jenis_soal'];
            if (isset($grouped[$j])) {
                $grouped[$j][] = $q;
            }
        }

        // 1. Tipe 1: PG Tunggal
        $tampilPg = (int)($bank['tampil_pg'] ?? 0);
        $bobotPg = (float)($bank['bobot_pg'] ?? 0);
        if ($tampilPg <= 0) $tampilPg = count($grouped[1]);

        $pgBenar = 0;
        foreach ($grouped[1] as $q) {
            $ans = strtoupper(trim((string)$q['jawaban_siswa']));
            $key = strtoupper(trim((string)$q['jawaban_benar']));
            $point = 0.00;
            if (!empty($ans) && $ans === $key) {
                $pgBenar++;
                $point = 1.00;
            }
            $this->soalSiswaModel->update($q['id_soal_siswa'], ['point_soal' => $point]);
        }
        $pgNilai = $tampilPg > 0 ? ($pgBenar / $tampilPg) * $bobotPg : 0.00;

        // 2. Tipe 2: PG Kompleks
        $tampilPgk = (int)($bank['tampil_kompleks'] ?? 0);
        $bobotPgk = (float)($bank['bobot_kompleks'] ?? 0);
        if ($tampilPgk <= 0) $tampilPgk = count($grouped[2]);

        $sumSkorPgk = 0.00;
        foreach ($grouped[2] as $q) {
            $rawStudent = is_string($q['jawaban_siswa']) ? json_decode($q['jawaban_siswa'], true) : $q['jawaban_siswa'];
            $rawKey = is_string($q['jawaban_benar']) ? json_decode($q['jawaban_benar'], true) : $q['jawaban_benar'];

            $studentAnswers = is_array($rawStudent) ? array_map('strtoupper', $rawStudent) : [];
            $keyAnswers = is_array($rawKey) ? array_map('strtoupper', $rawKey) : [];

            $kCount = count($keyAnswers);
            $skorItem = 0.00;

            if ($kCount > 0 && !empty($studentAnswers)) {
                $mCount = 0; // matching correct choices
                $wCount = 0; // wrong choices
                foreach ($studentAnswers as $sa) {
                    if (in_array($sa, $keyAnswers, true)) {
                        $mCount++;
                    } else {
                        $wCount++;
                    }
                }
                $skorItem = max(0.00, ($mCount - $wCount) / $kCount);
            }

            $this->soalSiswaModel->update($q['id_soal_siswa'], ['point_soal' => round($skorItem, 2)]);
            $sumSkorPgk += $skorItem;
        }
        $kompleksNilai = $tampilPgk > 0 ? ($sumSkorPgk / $tampilPgk) * $bobotPgk : 0.00;

        // 3. Tipe 3: Menjodohkan
        $tampilJodoh = (int)($bank['tampil_jodohkan'] ?? 0);
        $bobotJodoh = (float)($bank['bobot_jodohkan'] ?? 0);
        if ($tampilJodoh <= 0) $tampilJodoh = count($grouped[3]);

        $sumSkorJodoh = 0.00;
        foreach ($grouped[3] as $q) {
            $rawStudent = is_string($q['jawaban_siswa']) ? json_decode($q['jawaban_siswa'], true) : $q['jawaban_siswa'];
            $rawKey = is_string($q['jawaban_benar']) ? json_decode($q['jawaban_benar'], true) : $q['jawaban_benar'];

            $keyLinks = (is_array($rawKey) && isset($rawKey['links']) && is_array($rawKey['links'])) ? $rawKey['links'] : [];
            $studentLinks = (is_array($rawStudent) && isset($rawStudent['links']) && is_array($rawStudent['links'])) ? $rawStudent['links'] : [];

            // Calculate total key lines
            $lKunci = 0;
            $normalizedKeyLines = [];
            foreach ($keyLinks as $rowIdx => $colIndices) {
                if (is_array($colIndices)) {
                    foreach ($colIndices as $colIdx) {
                        $normalizedKeyLines[] = "{$rowIdx}->{$colIdx}";
                        $lKunci++;
                    }
                }
            }

            // Calculate student lines & matches
            $lSiswa = 0;
            $lBenar = 0;
            foreach ($studentLinks as $rowIdx => $colIndices) {
                if (is_array($colIndices)) {
                    foreach ($colIndices as $colIdx) {
                        $lSiswa++;
                        if (in_array("{$rowIdx}->{$colIdx}", $normalizedKeyLines, true)) {
                            $lBenar++;
                        }
                    }
                }
            }

            $skorItem = 0.00;
            if ($lKunci > 0) {
                $excessLines = max(0, $lSiswa - $lKunci);
                $skorItem = max(0.00, ($lBenar - $excessLines) / $lKunci);
            }

            $this->soalSiswaModel->update($q['id_soal_siswa'], ['point_soal' => round($skorItem, 2)]);
            $sumSkorJodoh += $skorItem;
        }
        $jodohkanNilai = $tampilJodoh > 0 ? ($sumSkorJodoh / $tampilJodoh) * $bobotJodoh : 0.00;

        // 4. Tipe 4: Isian Singkat
        $tampilIsian = (int)($bank['tampil_isian'] ?? 0);
        $bobotIsian = (float)($bank['bobot_isian'] ?? 0);
        if ($tampilIsian <= 0) $tampilIsian = count($grouped[4]);

        $isianBenar = 0;
        foreach ($grouped[4] as $q) {
            $studentText = strtolower(trim(strip_tags((string)$q['jawaban_siswa'])));
            $keyText = strtolower(trim(strip_tags((string)$q['jawaban_benar'])));

            $point = 0.00;
            if ($studentText !== '' && $studentText === $keyText) {
                $isianBenar++;
                $point = 1.00;
            }
            $this->soalSiswaModel->update($q['id_soal_siswa'], ['point_soal' => $point]);
        }
        $isianNilai = $tampilIsian > 0 ? ($isianBenar / $tampilIsian) * $bobotIsian : 0.00;

        // 5. Tipe 5: Essai
        $tampilEsai = (int)($bank['tampil_esai'] ?? 0);
        $bobotEsai = (float)($bank['bobot_esai'] ?? 0);
        if ($tampilEsai <= 0) $tampilEsai = count($grouped[5]);

        $sumNilaiKoreksi = 0.00;
        $allDikoreksi = true;
        foreach ($grouped[5] as $q) {
            $koreksi = (float)($q['nilai_koreksi'] ?? 0.00);
            $sumNilaiKoreksi += $koreksi;
            if ((int)($q['nilai_otomatis'] ?? 0) === 0 && (float)$q['nilai_koreksi'] == 0.00) {
                $allDikoreksi = false;
            }
        }
        // Max score per essay question is 100 points
        $maxEssayPoints = max(1, $tampilEsai * 100);
        $essaiNilai = $tampilEsai > 0 ? ($sumNilaiKoreksi / $maxEssayPoints) * $bobotEsai : 0.00;

        // Total score calculation
        $totalNilai = $pgNilai + $kompleksNilai + $jodohkanNilai + $isianNilai + $essaiNilai;
        $totalNilai = round(min(100.00, max(0.00, $totalNilai)), 2);

        $resultPayload = [
            'pg_benar' => $pgBenar,
            'pg_nilai' => round($pgNilai, 2),
            'kompleks_nilai' => round($kompleksNilai, 2),
            'jodohkan_nilai' => round($jodohkanNilai, 2),
            'isian_nilai' => round($isianNilai, 2),
            'essai_nilai' => round($essaiNilai, 2),
            'total_nilai' => $totalNilai,
            'dikoreksi' => (empty($grouped[5]) || $allDikoreksi) ? 1 : 0,
        ];

        // Save into cbt_nilai
        $this->nilaiModel->saveScore($idTp, $idSmt, $idJadwal, $idSiswa, $resultPayload);

        return $resultPayload;
    }

    /**
     * Formula Katrol / Konversi Nilai Ujian (PRD 4.4).
     * N_konversi = T_min + ((N_asli - N_min_asli) / (N_max_asli - N_min_asli)) * (T_max - T_min)
     */
    public function convertScore(float $nAsli, float $nMinAsli, float $nMaxAsli, float $tMin, float $tMax): float
    {
        if ($nMaxAsli <= $nMinAsli) {
            return $tMin;
        }

        $ratio = ($nAsli - $nMinAsli) / ($nMaxAsli - $nMinAsli);
        $converted = $tMin + ($ratio * ($tMax - $tMin));

        return round(min(100.00, max(0.00, $converted)), 2);
    }

    /**
     * Apply conversion (katrol) to an entire schedule's score records.
     */
    public function applyKatrol(int $idJadwal, float $tMin, float $tMax): int
    {
        $scores = $this->nilaiModel->where(['id_jadwal' => $idJadwal]);
        if (empty($scores)) {
            return 0;
        }

        $allTotals = array_column($scores, 'total_nilai');
        $nMinAsli = (float)min($allTotals);
        $nMaxAsli = (float)max($allTotals);

        $updatedCount = 0;
        foreach ($scores as $s) {
            $newTotal = $this->convertScore((float)$s['total_nilai'], $nMinAsli, $nMaxAsli, $tMin, $tMax);
            $this->nilaiModel->update($s['id_nilai'], [
                'total_nilai' => $newTotal,
            ]);
            $updatedCount++;
        }

        return $updatedCount;
    }
}
