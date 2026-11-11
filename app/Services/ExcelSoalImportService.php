<?php

namespace App\Services;

class ExcelSoalImportService
{
    private WordImportParserService $tableParser;

    public function __construct()
    {
        $this->tableParser = new WordImportParserService();
    }

    /**
     * Parse questions from CSV or Excel (.xlsx) file.
     */
    public function parse(string $filePath): array
    {
        if (!file_exists($filePath)) {
            throw new \InvalidArgumentException("Berkas tidak ditemukan di path: {$filePath}");
        }

        // Check if file is OpenXML (.xlsx) by header magic bytes 'PK'
        $handle = fopen($filePath, 'rb');
        $magic = fread($handle, 4);
        fclose($handle);

        if (str_starts_with($magic, "PK\x03\x04")) {
            return $this->parseXlsx($filePath);
        }

        return $this->parseCsv($filePath);
    }

    /**
     * Native parser for Microsoft Excel OpenXML (.xlsx) files without external dependencies.
     */
    public function parseXlsx(string $filePath): array
    {
        $zip = new \ZipArchive();
        if ($zip->open($filePath) !== true) {
            return $this->parseCsv($filePath);
        }

        // 1. Read shared strings if available
        $sharedStrings = [];
        $stringsXml = $zip->getFromName('xl/sharedStrings.xml');
        if ($stringsXml) {
            $domStrings = new \DOMDocument();
            if (@$domStrings->loadXML($stringsXml, LIBXML_NOERROR | LIBXML_NOWARNING)) {
                $siNodes = $domStrings->getElementsByTagName('si');
                foreach ($siNodes as $si) {
                    $tNodes = $si->getElementsByTagName('t');
                    $tText = '';
                    foreach ($tNodes as $t) {
                        $tText .= $t->textContent;
                    }
                    $sharedStrings[] = $tText;
                }
            }
        }

        // 2. Read first worksheet (xl/worksheets/sheet1.xml)
        $sheetXml = $zip->getFromName('xl/worksheets/sheet1.xml');
        $zip->close();

        if (!$sheetXml) {
            throw new \RuntimeException("Tidak dapat menemukan sheet data di dalam berkas Excel (.xlsx).");
        }

        $domSheet = new \DOMDocument();
        if (!@$domSheet->loadXML($sheetXml, LIBXML_NOERROR | LIBXML_NOWARNING)) {
            throw new \RuntimeException("Gagal mem-parsing XML sheet berkas Excel.");
        }

        $rows = [];
        $rowNodes = $domSheet->getElementsByTagName('row');

        foreach ($rowNodes as $rowNode) {
            $cNodes = $rowNode->getElementsByTagName('c');
            $rowCells = [];

            foreach ($cNodes as $cNode) {
                $rAttr = $cNode->getAttribute('r'); // e.g. "A1", "C2"
                $tAttr = $cNode->getAttribute('t'); // "s" for shared string, "inlineStr", etc.

                $val = '';
                $vNode = $cNode->getElementsByTagName('v')->item(0);
                if ($vNode) {
                    $rawVal = $vNode->textContent;
                    if ($tAttr === 's' && isset($sharedStrings[(int)$rawVal])) {
                        $val = $sharedStrings[(int)$rawVal];
                    } else {
                        $val = $rawVal;
                    }
                } else {
                    $isNode = $cNode->getElementsByTagName('is')->item(0);
                    if ($isNode) {
                        $val = $isNode->textContent;
                    }
                }

                // Determine column index from cell reference e.g. "A"->0, "B"->1
                $colLetters = preg_replace('/[0-9]/', '', $rAttr);
                $colIndex = $this->letterToColumnIndex($colLetters);

                $rowCells[$colIndex] = trim($val);
            }

            if (!empty($rowCells)) {
                // Fill missing gaps in the row
                $maxIndex = max(array_keys($rowCells));
                $filledRow = [];
                for ($idx = 0; $idx <= $maxIndex; $idx++) {
                    $filledRow[$idx] = $rowCells[$idx] ?? '';
                }
                $rows[] = $filledRow;
            }
        }

        return $this->tableParser->parseTableRows($rows);
    }

    /**
     * Convert Excel column letters (A, B, ..., Z, AA) to 0-based integer index.
     */
    private function letterToColumnIndex(string $letters): int
    {
        $letters = strtoupper($letters);
        $len = strlen($letters);
        $index = 0;
        for ($i = 0; $i < $len; $i++) {
            $index = $index * 26 + (ord($letters[$i]) - 64);
        }
        return max(0, $index - 1);
    }

    /**
     * Parse questions from a CSV file.
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

        // Auto-detect delimiter
        $firstLine = fgets($handle);
        rewind($handle);

        $delimiter = ',';
        if ($firstLine !== false) {
            $commaCount = substr_count($firstLine, ',');
            $semiCount = substr_count($firstLine, ';');
            $tabCount = substr_count($firstLine, "\t");
            if ($semiCount > $commaCount && $semiCount > $tabCount) {
                $delimiter = ';';
            } elseif ($tabCount > $commaCount && $tabCount > $semiCount) {
                $delimiter = "\t";
            }
        }

        // Remove BOM if present
        $bom = fread($handle, 3);
        if ($bom !== "\xEF\xBB\xBF") {
            rewind($handle);
        }

        $allRows = [];
        while (($row = fgetcsv($handle, 4096, $delimiter)) !== false) {
            if (!empty(array_filter($row, fn($c) => trim((string)$c) !== ''))) {
                $allRows[] = array_map('trim', $row);
            }
        }
        fclose($handle);

        return $this->tableParser->parseTableRows($allRows);
    }

    /**
     * Generate template CSV for importing 5 AKM Question Types.
     */
    public function downloadTemplate(): void
    {
        $filename = 'Template_Soal_AKM_GarudaCBT.csv';

        header('Content-Type: text/csv; charset=utf-8');
        header('Content-Disposition: attachment; filename="' . $filename . '"');
        header('Pragma: no-cache');
        header('Expires: 0');

        $output = fopen('php://output', 'w');
        fwrite($output, "\xEF\xBB\xBF"); // UTF-8 BOM

        // Header instructions
        fputcsv($output, ['FORMAT IMPOR BANK SOAL STANDAR AKM — GARUDACBT / DADO CBT']);
        fputcsv($output, ['Petunjuk: Kolom JENIS diisi angka 1=PG, 2=PG Kompleks, 3=Menjodohkan, 4=Isian, 5=Essai']);
        fputcsv($output, []);

        // 1. Pilihan Ganda (PG 1)
        fputcsv($output, ['I. Soal Pilihan Ganda (satu jawaban benar)']);
        fputcsv($output, ['NO', 'SOAL', 'JENIS', 'OPSI', 'JAWABAN', 'KUNCI']);
        fputcsv($output, [1, 'Hasil dari 15 x 4 : 2 adalah...', 1, 'A', '20', '']);
        fputcsv($output, ['', '', '', 'B', '30', 'v']);
        fputcsv($output, ['', '', '', 'C', '40', '']);
        fputcsv($output, ['', '', '', 'D', '50', '']);
        fputcsv($output, ['', '', '', 'E', '60', '']);
        fputcsv($output, []);

        // 2. Pilihan Ganda Kompleks (PG 2)
        fputcsv($output, ['II. Soal Pilihan Ganda Kompleks (beberapa jawaban benar)']);
        fputcsv($output, ['NO', 'SOAL', 'JENIS', 'OPSI', 'JAWABAN', 'KUNCI']);
        fputcsv($output, [1, 'Manakah diantara alat berikut ini yang merupakan peralatan dapur?', 2, 'A', 'Cangkul', '']);
        fputcsv($output, ['', '', '', 'B', 'Pisau', 'v']);
        fputcsv($output, ['', '', '', 'C', 'Obeng', '']);
        fputcsv($output, ['', '', '', 'D', 'Spatula', 'v']);
        fputcsv($output, ['', '', '', 'E', 'Panci', 'v']);
        fputcsv($output, ['', '', '', 'F', 'Keranjang', '']);
        fputcsv($output, []);

        // 3. Menjodohkan
        fputcsv($output, ['III. Soal Menjodohkan']);
        fputcsv($output, ['NO', 'SOAL', 'JENIS', 'KODE BARIS', 'NAMA BARIS', 'KODE KOLOM', 'NAMA KOLOM', 'KODE BARIS', 'KODE KOLOM']);
        fputcsv($output, [1, 'Cocokanlah peralatan di bawah ini sesuai tempat penggunaannya!', 3, '1', 'Peralatan Dapur', 'A', 'Cangkul', '1', 'B,D,E']);
        fputcsv($output, ['', '', '', '2', 'Peralatan Kebun', 'B', 'Pisau', '2', 'A,F']);
        fputcsv($output, ['', '', '', '3', 'Peralatan Tukang Bangunan', 'C', 'Obeng', '3', 'C,G,H']);
        fputcsv($output, ['', '', '', '', '', 'D', 'Spatula', '', '']);
        fputcsv($output, ['', '', '', '', '', 'E', 'Panci', '', '']);
        fputcsv($output, ['', '', '', '', '', 'F', 'Keranjang', '', '']);
        fputcsv($output, ['', '', '', '', '', 'G', 'Gergaji', '', '']);
        fputcsv($output, ['', '', '', '', '', 'H', 'Palu', '', '']);
        fputcsv($output, []);

        // 4. Isian Singkat
        fputcsv($output, ['IV. Soal Isian Singkat']);
        fputcsv($output, ['NO', 'SOAL', 'JENIS', 'JAWABAN']);
        fputcsv($output, [1, 'Ibu kota negara Indonesia yang baru di Kalimantan Timur adalah...', 4, 'Nusantara']);
        fputcsv($output, [2, 'Planet terbesar dalam tata surya kita adalah...', 4, 'Yupiter']);
        fputcsv($output, []);

        // 5. Uraian / Essai
        fputcsv($output, ['V. Soal Uraian / Essai']);
        fputcsv($output, ['NO', 'SOAL', 'JENIS', 'JAWABAN']);
        fputcsv($output, [1, 'Jelaskan perbedaan antara konduksi, konveksi, dan radiasi serta berikan contohnya!', 5, 'Pedoman penskoran: Definisi konduksi, konveksi, dan radiasi tepat masing-masing bernilai maksimal 30 poin.']);

        fclose($output);
        exit;
    }
}
