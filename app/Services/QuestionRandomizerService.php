<?php

declare(strict_types=1);

namespace App\Services;

use App\Models\CbtBankSoal;
use App\Models\CbtJadwal;
use App\Models\CbtSoal;
use App\Models\CbtSoalSiswa;

class QuestionRandomizerService
{
    private CbtBankSoal $bankModel;
    private CbtJadwal $jadwalModel;
    private CbtSoal $soalModel;
    private CbtSoalSiswa $soalSiswaModel;

    public function __construct()
    {
        $this->bankModel = new CbtBankSoal();
        $this->jadwalModel = new CbtJadwal();
        $this->soalModel = new CbtSoal();
        $this->soalSiswaModel = new CbtSoalSiswa();
    }

    /**
     * Initialize questions for a student on an exam schedule.
     * If already initialized, returns existing randomized questions.
     */
    public function initializeExamQuestions(int $idJadwal, int $idSiswa): array
    {
        // 1. Check if student already has generated questions
        $existing = $this->soalSiswaModel->getStudentQuestions($idJadwal, $idSiswa);
        if (!empty($existing)) {
            return $existing;
        }

        // 2. Fetch Schedule and Bank Soal details
        $jadwal = $this->jadwalModel->find($idJadwal);
        if (!$jadwal) {
            throw new \RuntimeException("Jadwal ujian tidak ditemukan (ID: {$idJadwal})");
        }

        $idBank = (int)$jadwal['id_bank'];
        $bank = $this->bankModel->find($idBank);
        if (!$bank) {
            throw new \RuntimeException("Bank soal tidak ditemukan (ID: {$idBank})");
        }

        $acakSoal = (int)($jadwal['acak_soal'] ?? 1);
        $acakOpsi = (int)($jadwal['acak_opsi'] ?? 1);
        $opsiCount = max(3, min(5, (int)($bank['opsi'] ?? 4)));

        // 3. Fetch question pool for each AKM type based on quotas
        $quotas = [
            1 => (int)($bank['tampil_pg'] ?? 0),
            2 => (int)($bank['tampil_kompleks'] ?? 0),
            3 => (int)($bank['tampil_jodohkan'] ?? 0),
            4 => (int)($bank['tampil_isian'] ?? 0),
            5 => (int)($bank['tampil_esai'] ?? 0),
        ];

        $selectedQuestions = [];

        for ($jenis = 1; $jenis <= 5; $jenis++) {
            $pool = $this->soalModel->where([
                'bank_id' => $idBank,
                'jenis' => $jenis,
                'tampilkan' => 1,
            ], 'nomor_soal ASC');

            if ($acakSoal === 1) {
                $pool = $this->fisherYatesShuffle($pool);
            }

            $quota = $quotas[$jenis];
            if ($quota > 0 && count($pool) > $quota) {
                $pool = array_slice($pool, 0, $quota);
            }

            foreach ($pool as $q) {
                $selectedQuestions[] = $q;
            }
        }

        // If overall shuffle across question types is desired, or keep grouped by type
        // By standard AKM, questions can be sequenced 1..N
        if (empty($selectedQuestions)) {
            // Fallback: take all active questions from bank if quotas are not set
            $selectedQuestions = $this->soalModel->where([
                'bank_id' => $idBank,
                'tampilkan' => 1,
            ], 'jenis ASC, nomor_soal ASC');

            if ($acakSoal === 1) {
                $selectedQuestions = $this->fisherYatesShuffle($selectedQuestions);
            }
        }

        // 4. Generate alias sequence and options alias
        $letters = ['A', 'B', 'C', 'D', 'E'];
        $activeLetters = array_slice($letters, 0, $opsiCount);

        $bulkRows = [];
        $aliasCounter = 1;

        foreach ($selectedQuestions as $q) {
            $jenisQ = (int)$q['jenis'];
            $idSoal = (int)$q['id_soal'];
            $kunciAsli = $q['jawaban'];

            $aliasRow = [
                'id_bank' => $idBank,
                'id_jadwal' => $idJadwal,
                'id_soal' => $idSoal,
                'id_siswa' => $idSiswa,
                'jenis_soal' => $jenisQ,
                'no_soal_alias' => $aliasCounter++,
                'opsi_alias_a' => null,
                'opsi_alias_b' => null,
                'opsi_alias_c' => null,
                'opsi_alias_d' => null,
                'opsi_alias_e' => null,
                'jawaban_alias' => null,
                'jawaban_siswa' => null,
                'jawaban_benar' => $kunciAsli,
                'point_soal' => 0.00,
                'nilai_koreksi' => 0.00,
                'nilai_otomatis' => 0,
                'ragu' => 0,
            ];

            // Shuffle options for Tipe 1 (PG Tunggal) if acak_opsi enabled
            if ($jenisQ === 1) {
                if ($acakOpsi === 1) {
                    $shuffledLetters = $this->fisherYatesShuffle($activeLetters);
                    $aliasRow['opsi_alias_a'] = $shuffledLetters[0] ?? 'A';
                    $aliasRow['opsi_alias_b'] = $shuffledLetters[1] ?? 'B';
                    $aliasRow['opsi_alias_c'] = $shuffledLetters[2] ?? 'C';
                    $aliasRow['opsi_alias_d'] = $shuffledLetters[3] ?? ($opsiCount >= 4 ? 'D' : null);
                    $aliasRow['opsi_alias_e'] = $shuffledLetters[4] ?? ($opsiCount >= 5 ? 'E' : null);
                } else {
                    $aliasRow['opsi_alias_a'] = 'A';
                    $aliasRow['opsi_alias_b'] = 'B';
                    $aliasRow['opsi_alias_c'] = 'C';
                    $aliasRow['opsi_alias_d'] = $opsiCount >= 4 ? 'D' : null;
                    $aliasRow['opsi_alias_e'] = $opsiCount >= 5 ? 'E' : null;
                }
            }

            $bulkRows[] = $aliasRow;
        }

        // 5. Bulk insert in a single query
        $this->soalSiswaModel->bulkInsert($bulkRows);

        return $this->soalSiswaModel->getStudentQuestions($idJadwal, $idSiswa);
    }

    /**
     * Fisher-Yates shuffle algorithm.
     */
    public function fisherYatesShuffle(array $items): array
    {
        $arr = array_values($items);
        $count = count($arr);
        for ($i = $count - 1; $i > 0; $i--) {
            $j = random_int(0, $i);
            $tmp = $arr[$i];
            $arr[$i] = $arr[$j];
            $arr[$j] = $tmp;
        }
        return $arr;
    }
}
