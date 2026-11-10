<?php

namespace App\Services;

class WordImportParserService
{
    /**
     * Parse questions from a Word (.docx) file structure.
     * Extracts text, tables, options, and embedded images into public/uploads/soal/.
     */
    public function parseDocx(string $filePath): array
    {
        if (!file_exists($filePath)) {
            throw new \InvalidArgumentException("File Word tidak ditemukan di path: {$filePath}");
        }

        $questions = [];
        $extractedImages = [];

        $mediaDir = __DIR__ . '/../../public/uploads/soal/';
        if (!is_dir($mediaDir)) {
            @mkdir($mediaDir, 0777, true);
        }

        $zip = new \ZipArchive();
        if ($zip->open($filePath) === true) {
            // 1. Extract embedded media/images from word/media/
            for ($i = 0; $i < $zip->numFiles; $i++) {
                $filename = $zip->getNameIndex($i);
                if (str_starts_with($filename, 'word/media/')) {
                    $baseName = basename($filename);
                    $ext = strtolower(pathinfo($baseName, PATHINFO_EXTENSION));
                    $safeFileName = 'img_w_' . time() . '_' . mt_rand(100, 999) . '_' . preg_replace('/[^a-zA-Z0-9_-]/', '_', pathinfo($baseName, PATHINFO_FILENAME)) . '.' . $ext;
                    $targetPath = $mediaDir . $safeFileName;

                    $fileContent = $zip->getFromIndex($i);
                    if ($fileContent && file_put_contents($targetPath, $fileContent)) {
                        $extractedImages[$baseName] = 'uploads/soal/' . $safeFileName;
                    }
                }
            }

            // 2. Parse main XML content
            $xmlContent = $zip->getFromName('word/document.xml');
            $zip->close();

            if ($xmlContent) {
                // Convert paragraphs and table rows into line breaks
                $cleanXml = str_replace(['</w:p>', '</w:tr>'], ["\n", "\n"], $xmlContent);
                $cleanText = strip_tags($cleanXml);
                $lines = explode("\n", $cleanText);

                $currentQuestion = null;
                $soalBuffer = [];

                foreach ($lines as $line) {
                    $trimmed = trim($line);
                    if (empty($trimmed)) {
                        continue;
                    }

                    // Check for question numbering e.g. "1.", "1)", "SOAL 1:", "#1"
                    if (preg_match('/^(?:SOAL\s*)?(\d+)[\.\)]\s*(.*)/i', $trimmed, $m)) {
                        if ($currentQuestion !== null) {
                            $currentQuestion['soal'] = implode('<br>', $soalBuffer);
                            $questions[] = $currentQuestion;
                            $soalBuffer = [];
                        }

                        $firstLine = trim($m[2] ?? '');
                        $detectedJenis = 1;

                        if (preg_match('/\[(?:JENIS|TIPE)\s*:\s*([1-5])\]/i', $firstLine, $jMatch)) {
                            $detectedJenis = (int)$jMatch[1];
                            $firstLine = trim(str_replace($jMatch[0], '', $firstLine));
                        }

                        $currentQuestion = [
                            'nomor' => (int)$m[1],
                            'jenis' => $detectedJenis,
                            'soal' => $firstLine,
                            'opsi_a' => '',
                            'opsi_b' => '',
                            'opsi_c' => '',
                            'opsi_d' => '',
                            'opsi_e' => '',
                            'jawaban' => '',
                        ];
                        if (!empty($firstLine)) {
                            $soalBuffer[] = $firstLine;
                        }
                    } elseif ($currentQuestion !== null) {
                        // Check for type tag in follow-up lines
                        if (preg_match('/\[(?:JENIS|TIPE)\s*:\s*([1-5])\]/i', $trimmed, $jMatch)) {
                            $currentQuestion['jenis'] = (int)$jMatch[1];
                            continue;
                        }

                        // Check for options e.g. "A. ...", "B. ...", "A) ..."
                        if (preg_match('/^([A-E])[\.\)]\s*(.+)/i', $trimmed, $optMatch)) {
                            $optLetter = strtolower($optMatch[1]);
                            $currentQuestion['opsi_' . $optLetter] = trim($optMatch[2]);
                        } elseif (preg_match('/^KUNCI\s*:\s*(.+)/i', $trimmed, $keyMatch)) {
                            $kunciRaw = trim($keyMatch[1]);
                            // If PGK with multiple letters like A, C or A,B
                            if (preg_match_all('/[A-E]/i', $kunciRaw, $letters)) {
                                if (count($letters[0]) > 1) {
                                    $currentQuestion['jenis'] = 2; // PGK
                                    $currentQuestion['jawaban'] = array_map('strtoupper', $letters[0]);
                                } else {
                                    $currentQuestion['jawaban'] = strtoupper($letters[0][0]);
                                }
                            } else {
                                $currentQuestion['jawaban'] = $kunciRaw;
                            }
                        } else {
                            $soalBuffer[] = $trimmed;
                        }
                    }
                }

                if ($currentQuestion !== null) {
                    $currentQuestion['soal'] = implode('<br>', $soalBuffer);
                    $questions[] = $currentQuestion;
                }
            }
        }

        return $questions;
    }
}
