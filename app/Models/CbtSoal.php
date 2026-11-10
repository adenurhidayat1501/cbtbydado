<?php

namespace App\Models;

use App\Core\Model;

class CbtSoal extends Model
{
    protected string $table = 'cbt_soal';
    protected string $primaryKey = 'id_soal';

    /**
     * Get all questions for a specific bank, ordered by nomor_soal.
     */
    public function getByBank(int $idBank, ?int $jenis = null): array
    {
        $sql = "SELECT s.* FROM cbt_soal s WHERE s.bank_id = ?";
        $params = [$idBank];

        if ($jenis !== null) {
            $sql .= " AND s.jenis = ?";
            $params[] = $jenis;
        }

        $sql .= " ORDER BY s.nomor_soal ASC, s.id_soal ASC";

        return $this->query($sql, $params);
    }

    /**
     * Get next question number for a bank.
     */
    public function getNextNomor(int $idBank): int
    {
        $sql = "SELECT MAX(nomor_soal) as max_nomor FROM cbt_soal WHERE bank_id = ?";
        $rows = $this->query($sql, [$idBank]);
        return ((int)($rows[0]['max_nomor'] ?? 0)) + 1;
    }

    /**
     * Save a new question or update an existing one.
     */
    public function saveSoal(int $idBank, array $data, ?int $idSoal = null): int|false
    {
        $bankModel = new CbtBankSoal();
        $bank = $bankModel->find($idBank);
        if (!$bank) {
            return false;
        }

        $jenis = (int)($data['jenis'] ?? 1);
        $nomorSoal = isset($data['nomor_soal']) && (int)$data['nomor_soal'] > 0 ? (int)$data['nomor_soal'] : $this->getNextNomor($idBank);

        $payload = [
            'bank_id' => $idBank,
            'mapel_id' => (int)($bank['bank_mapel_id'] ?? 0),
            'jenis' => $jenis,
            'nomor_soal' => $nomorSoal,
            'soal' => $data['soal'] ?? '',
            'opsi_a' => $data['opsi_a'] ?? null,
            'opsi_b' => $data['opsi_b'] ?? null,
            'opsi_c' => $data['opsi_c'] ?? null,
            'opsi_d' => $data['opsi_d'] ?? null,
            'opsi_e' => $data['opsi_e'] ?? null,
            'jawaban' => is_array($data['jawaban']) ? json_encode($data['jawaban'], JSON_UNESCAPED_UNICODE) : (string)($data['jawaban'] ?? ''),
            'tampilkan' => isset($data['tampilkan']) ? (int)$data['tampilkan'] : 1,
        ];

        if ($idSoal !== null && $idSoal > 0) {
            $this->update($idSoal, $payload);
            $savedId = $idSoal;
        } else {
            $savedId = (int)$this->insert($payload);
        }

        // Update counts on bank soal
        $bankModel->updateQuestionCounts($idBank);

        return $savedId;
    }

    /**
     * Delete question and sync bank soal counts.
     */
    public function deleteSoal(int $idSoal): bool
    {
        $soal = $this->find($idSoal);
        if (!$soal) {
            return false;
        }

        $bankId = (int)$soal['bank_id'];
        $deleted = $this->delete($idSoal);

        if ($deleted) {
            (new CbtBankSoal())->updateQuestionCounts($bankId);
        }

        return $deleted;
    }

    /**
     * Sequential re-ordering of question numbers within a bank.
     */
    public function reorderNomor(int $idBank): void
    {
        $questions = $this->getByBank($idBank);
        $no = 1;
        foreach ($questions as $q) {
            $this->update($q['id_soal'], ['nomor_soal' => $no++]);
        }
    }
}
