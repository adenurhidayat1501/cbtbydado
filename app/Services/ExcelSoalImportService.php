<?php

namespace App\Services;

class ExcelSoalImportService
{
    /**
     * Parse questions from a CSV / Excel format.
     */
    public function parseCsv(string $filePath): array
    {
        if (!file_exists($filePath)) {
            throw new \InvalidArgumentException("Berkas CSV tidak ditemukan: {$filePath}");
        }

        $handle = fopen($filePath, 'r');
        if (!$handle) {
            throw new \RuntimeException("Gagal membuka berkas CSV.");
        }

        // Read header row
        $headers = fgetcsv($handle);
        if (!$headers) {
            fclose($handle);
            return [];
        }

        // Remove UTF-8 BOM if present
        if (isset($headers[0])) {
            $headers[0] = preg_replace('/^\xEF\xBB\xBF/', '', $headers[0]);
        }

        $questions = [];
        $noCounter = 1;

        while (($row = fgetcsv($handle)) !== false) {
            if (empty(array_filter($row))) {
                continue;
            }

            // Map columns: 0: Nomor, 1: Jenis, 2: Soal, 3: Opsi A, 4: Opsi B, 5: Opsi C, 6: Opsi D, 7: Opsi E, 8: Jawaban
            $nomor = !empty($row[0]) ? (int)$row[0] : $noCounter++;
            $jenis = !empty($row[1]) ? (int)$row[1] : 1;
            $soal = $row[2] ?? '';
            $opsiA = $row[3] ?? null;
            $opsiB = $row[4] ?? null;
            $opsiC = $row[5] ?? null;
            $opsiD = $row[6] ?? null;
            $opsiE = $row[7] ?? null;
            $jawabanRaw = trim((string)($row[8] ?? ''));

            if (empty($soal)) {
                continue;
            }

            $jawaban = $jawabanRaw;
            if ($jenis === 2) {
                // PG Kompleks: If comma separated e.g. "A, C" or JSON array
                if (str_starts_with($jawabanRaw, '[') && str_ends_with($jawabanRaw, ']')) {
                    $jawaban = json_decode($jawabanRaw, true) ?: [];
                } else {
                    preg_match_all('/[A-E]/i', $jawabanRaw, $matches);
                    $jawaban = !empty($matches[0]) ? array_map('strtoupper', $matches[0]) : [$jawabanRaw];
                }
            } elseif ($jenis === 3) {
                // Menjodohkan: Expects JSON or defaults
                if (str_starts_with($jawabanRaw, '{') && str_ends_with($jawabanRaw, '}')) {
                    $jawaban = json_decode($jawabanRaw, true) ?: [];
                }
            }

            $questions[] = [
                'nomor' => $nomor,
                'jenis' => $jenis,
                'soal' => $soal,
                'opsi_a' => $opsiA,
                'opsi_b' => $opsiB,
                'opsi_c' => $opsiC,
                'opsi_d' => $opsiD,
                'opsi_e' => $opsiE,
                'jawaban' => $jawaban,
            ];
        }

        fclose($handle);
        return $questions;
    }

    /**
     * Generate template CSV for importing questions.
     */
    public function downloadTemplate(): void
    {
        $filename = 'Template_Import_Soal_AKM_DADO CBT.csv';

        header('Content-Type: text/csv; charset=utf-8');
        header('Content-Disposition: attachment; filename="' . $filename . '"');
        header('Pragma: no-cache');
        header('Expires: 0');

        $output = fopen('php://output', 'w');
        fwrite($output, "\xEF\xBB\xBF"); // UTF-8 BOM

        $headers = [
            'Nomor',
            'Jenis (1=PG, 2=PGK, 3=Jodohkan, 4=Isian, 5=Esai)',
            'Teks Soal / Pertanyaan (Mendukung HTML & KaTeX)',
            'Opsi A',
            'Opsi B',
            'Opsi C',
            'Opsi D',
            'Opsi E',
            'Kunci Jawaban',
        ];
        fputcsv($output, $headers);

        // Examples
        fputcsv($output, [1, 1, 'Hasil dari 15 x 4 : 2 adalah...', '20', '30', '40', '50', '60', 'B']);
        fputcsv($output, [2, 2, 'Pilihlah dua hewan mamalia yang hidup di air!', 'Lumba-lumba', 'Ikan Hiu', 'Paus Biru', 'Ikan Pari', 'Kuda Laut', 'A, C']);
        fputcsv($output, [3, 4, 'Ibu kota negara Indonesia yang baru di Kalimantan Timur adalah...', '', '', '', '', '', 'Nusantara']);
        fputcsv($output, [4, 5, 'Jelaskan perbedaan antara konduksi, konveksi, dan radiasi!', '', '', '', '', '', 'Pedoman penilaian: Konduksi perpindahan panas tanpa zat perantara...']);

        fclose($output);
        exit;
    }
}
