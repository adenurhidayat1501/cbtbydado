<?php

namespace App\Services;

class WordImportParserService
{
    /**
     * Parse questions from a Word (.docx) file structure.
     * Supports the official GarudaCBT 5-Type AKM Table Template:
     *   - Tipe 1: Pilihan Ganda Tunggal (6 kolom: NO | SOAL | JENIS | OPSI | JAWABAN | KUNCI)
     *   - Tipe 2: Pilihan Ganda Kompleks (6 kolom: NO | SOAL | JENIS | OPSI | JAWABAN | KUNCI)
     *   - Tipe 3: Menjodohkan (9 kolom: NO | SOAL | JENIS | KODE BARIS | NAMA BARIS | KODE KOLOM | NAMA KOLOM | KODE BARIS | KODE KOLOM)
     *   - Tipe 4: Isian Singkat (4 kolom: NO | SOAL | JENIS | JAWABAN)
     *   - Tipe 5: Uraian / Essai (4 kolom: NO | SOAL | JENIS | JAWABAN)
     *
     * Automatically extracts embedded images into public/uploads/soal/ and embeds <img> tags.
     */
    public function parseDocx(string $filePath): array
    {
        if (!file_exists($filePath)) {
            throw new \InvalidArgumentException("Berkas dokumen Word tidak ditemukan di path: {$filePath}");
        }

        $mediaDir = __DIR__ . '/../../public/uploads/soal/';
        if (!is_dir($mediaDir)) {
            @mkdir($mediaDir, 0777, true);
        }

        $zip = new \ZipArchive();
        if ($zip->open($filePath) !== true) {
            throw new \RuntimeException("Gagal membuka berkas Word (.docx). Pastikan format berkas valid.");
        }

        // 1. Extract embedded images from word/media/
        $extractedImages = [];
        for ($i = 0; $i < $zip->numFiles; $i++) {
            $filename = $zip->getNameIndex($i);
            if (str_starts_with($filename, 'word/media/')) {
                $baseName = basename($filename);
                $ext = strtolower(pathinfo($baseName, PATHINFO_EXTENSION));
                $safeFileName = 'img_w_' . time() . '_' . mt_rand(100, 999) . '_' . preg_replace('/[^a-zA-Z0-9_-]/', '_', pathinfo($baseName, PATHINFO_FILENAME)) . '.' . $ext;
                $targetPath = $mediaDir . $safeFileName;

                $fileContent = $zip->getFromIndex($i);
                if ($fileContent && @file_put_contents($targetPath, $fileContent)) {
                    $extractedImages[$baseName] = 'uploads/soal/' . $safeFileName;
                }
            }
        }

        // 2. Parse word/_rels/document.xml.rels to map rId -> image path
        $relIdToImage = [];
        $relsXml = $zip->getFromName('word/_rels/document.xml.rels');
        if ($relsXml) {
            $domRels = new \DOMDocument();
            if (@$domRels->loadXML($relsXml, LIBXML_NOERROR | LIBXML_NOWARNING)) {
                $relNodes = $domRels->getElementsByTagName('Relationship');
                foreach ($relNodes as $relNode) {
                    $rId = $relNode->getAttribute('Id');
                    $target = $relNode->getAttribute('Target');
                    $baseTarget = basename($target);
                    if (isset($extractedImages[$baseTarget])) {
                        $relIdToImage[$rId] = $extractedImages[$baseTarget];
                    }
                }
            }
        }

        // 3. Parse word/document.xml
        $xmlContent = $zip->getFromName('word/document.xml');
        $zip->close();

        if (!$xmlContent) {
            throw new \RuntimeException("Berkas word/document.xml tidak ditemukan di dalam arsip docx.");
        }

        $dom = new \DOMDocument();
        if (!@$dom->loadXML($xmlContent, LIBXML_NOERROR | LIBXML_NOWARNING)) {
            throw new \RuntimeException("Gagal mem-parsing XML dokumen Word.");
        }

        $xpath = new \DOMXPath($dom);
        $xpath->registerNamespace('w', 'http://schemas.openxmlformats.org/wordprocessingml/2006/main');
        $xpath->registerNamespace('a', 'http://schemas.openxmlformats.org/drawingml/2006/main');
        $xpath->registerNamespace('r', 'http://schemas.openxmlformats.org/officeDocument/2006/relationships');
        $xpath->registerNamespace('v', 'urn:schemas-microsoft-com:vml');

        // 4. Extract 2D Table Matrix from all <w:tbl>
        $tableRows = [];
        $tblElements = $xpath->query('//w:tbl');

        if ($tblElements && $tblElements->length > 0) {
            foreach ($tblElements as $table) {
                $trElements = $xpath->query('.//w:tr', $table);
                foreach ($trElements as $row) {
                    $tcElements = $xpath->query('.//w:tc', $row);
                    $rowCells = [];

                    foreach ($tcElements as $cell) {
                        $cellHtmlParts = [];
                        $pElements = $xpath->query('.//w:p', $cell);

                        foreach ($pElements as $p) {
                            $paraParts = [];

                            // Check text nodes
                            $tElements = $xpath->query('.//w:t', $p);
                            foreach ($tElements as $t) {
                                $paraParts[] = htmlspecialchars($t->textContent, ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8');
                            }

                            // Check drawing images (<a:blip r:embed="..."/>)
                            $blipElements = $xpath->query('.//a:blip', $p);
                            foreach ($blipElements as $blip) {
                                $embedId = $blip->getAttribute('r:embed');
                                if (isset($relIdToImage[$embedId])) {
                                    $paraParts[] = '<img src="/' . htmlspecialchars($relIdToImage[$embedId], ENT_QUOTES) . '" class="img-fluid my-2 d-block" alt="Gambar Soal">';
                                }
                            }

                            // Check legacy VML images (<v:imagedata r:id="..."/>)
                            $vmlElements = $xpath->query('.//v:imagedata', $p);
                            foreach ($vmlElements as $vml) {
                                $vmlId = $vml->getAttribute('r:id');
                                if (isset($relIdToImage[$vmlId])) {
                                    $paraParts[] = '<img src="/' . htmlspecialchars($relIdToImage[$vmlId], ENT_QUOTES) . '" class="img-fluid my-2 d-block" alt="Gambar Soal">';
                                }
                            }

                            $paraText = implode('', $paraParts);
                            if (trim($paraText) !== '') {
                                $cellHtmlParts[] = $paraText;
                            }
                        }

                        $rowCells[] = implode('<br>', $cellHtmlParts);
                    }

                    if (!empty($rowCells)) {
                        $tableRows[] = $rowCells;
                    }
                }
            }
        }

        // If tables were found, parse them using the 5 AKM table protocol
        if (!empty($tableRows)) {
            $parsedQuestions = $this->parseTableRows($tableRows);
            if (!empty($parsedQuestions)) {
                return $parsedQuestions;
            }
        }

        // Fallback: Paragraph parser if document doesn't use standard tables
        return $this->parseFallbackParagraphs($xmlContent, $relIdToImage);
    }

    /**
     * Parses raw 2D array of table cells following official GarudaCBT 5-Type AKM format.
     */
    public function parseTableRows(array $rows): array
    {
        $questions = [];
        $currentSoal = null;
        $currentJenis = 1;

        foreach ($rows as $cells) {
            $colCount = count($cells);
            if ($colCount < 3) {
                continue;
            }

            // Normalization
            $c0 = trim($cells[0] ?? '');
            $c1 = trim($cells[1] ?? '');
            $c2 = trim($cells[2] ?? '');
            $c3 = trim($cells[3] ?? '');
            $c4 = trim($cells[4] ?? '');
            $c5 = trim($cells[5] ?? '');
            $c6 = trim($cells[6] ?? '');
            $c7 = trim($cells[7] ?? '');
            $c8 = trim($cells[8] ?? '');

            // Header Row Detection
            $hdrStr = strtoupper(strip_tags("{$c0}_{$c1}_{$c2}"));
            if (str_contains($hdrStr, 'NO_SOAL_JENIS') || str_contains($hdrStr, 'NO_SOAL') || $c0 === 'NO') {
                if (is_numeric(strip_tags($c2))) {
                    $currentJenis = (int)strip_tags($c2);
                }
                continue;
            }

            $hasNum = preg_match('/^(\d+)/', strip_tags($c0), $mNum);
            $jenisNum = is_numeric(strip_tags($c2)) ? (int)strip_tags($c2) : 0;

            // Section Divider Rows (only if NOT a question/option row)
            if (!$hasNum && $jenisNum === 0 && empty($c3) && empty($c4)) {
                $fullRowText = strip_tags(implode(' ', $cells));
                if (preg_match('/(?:SOAL\s+)?(PILIHAN\s+GANDA\s+KOMPLEKS|PG\s+KOMPLEKS|PGK)/i', $fullRowText)) {
                    $currentJenis = 2;
                    continue;
                } elseif (preg_match('/(?:SOAL\s+)?(MENJODOHKAN|MATCHING|JODOHKAN)/i', $fullRowText)) {
                    $currentJenis = 3;
                    continue;
                } elseif (preg_match('/(?:SOAL\s+)?(ISIAN\s+SINGKAT|ISIAN)/i', $fullRowText)) {
                    $currentJenis = 4;
                    continue;
                } elseif (preg_match('/(?:SOAL\s+)?(URAIAN|ESSAI|ESAI)/i', $fullRowText)) {
                    $currentJenis = 5;
                    continue;
                } elseif (preg_match('/(?:SOAL\s+)?(PILIHAN\s+GANDA)/i', $fullRowText)) {
                    $currentJenis = 1;
                    continue;
                }
            }

            $isNewQuestion = false;
            if ($hasNum) {
                $isNewQuestion = true;
            } elseif (!empty(strip_tags($c1)) && $jenisNum >= 1 && $jenisNum <= 5 && empty($c0)) {
                $isNewQuestion = true;
            }

            if ($isNewQuestion) {
                if ($currentSoal !== null) {
                    $this->finalizeQuestion($currentSoal, $questions);
                }

                $currentJenis = ($jenisNum >= 1 && $jenisNum <= 5) ? $jenisNum : $currentJenis;
                $nomor = $hasNum ? (int)$mNum[1] : (count($questions) + 1);

                $currentSoal = [
                    'nomor' => $nomor,
                    'jenis' => $currentJenis,
                    'soal' => $c1,
                    'opsi_a' => '',
                    'opsi_b' => '',
                    'opsi_c' => '',
                    'opsi_d' => '',
                    'opsi_e' => '',
                    'jawaban' => '',
                    '_kunci_pgk' => [],
                    '_baris' => [],
                    '_kolom' => [],
                    '_kunci_jodoh' => [],
                ];
            }

            if ($currentSoal === null) {
                continue;
            }

            // Append extra question content if continuation row
            if (!$isNewQuestion && !empty(strip_tags($c1)) && empty($c0) && empty($c2)) {
                $currentSoal['soal'] .= '<br>' . $c1;
            }

            $jenis = $currentSoal['jenis'];

            if ($jenis === 1) {
                // Tipe 1: PG Tunggal
                $opsiLabel = strtoupper(trim(strip_tags($c3)));
                $opsiText = $c4;
                $kunciCell = $c5;

                if (!empty($opsiLabel)) {
                    $letter = strtolower($opsiLabel[0]);
                    if (in_array($letter, ['a', 'b', 'c', 'd', 'e'])) {
                        $currentSoal['opsi_' . $letter] = $opsiText;
                        if ($this->isKunciMarked($kunciCell)) {
                            $currentSoal['jawaban'] = strtoupper($letter);
                        }
                    }
                }

                // If kunci is written directly as "A", "B", etc.
                if (preg_match('/^[A-E]$/i', trim(strip_tags($kunciCell)), $mKey)) {
                    $currentSoal['jawaban'] = strtoupper($mKey[0]);
                }

            } elseif ($jenis === 2) {
                // Tipe 2: PG Kompleks
                $opsiLabel = strtoupper(trim(strip_tags($c3)));
                $opsiText = $c4;
                $kunciCell = $c5;

                if (!empty($opsiLabel)) {
                    $letter = strtolower($opsiLabel[0]);
                    if (in_array($letter, ['a', 'b', 'c', 'd', 'e'])) {
                        $currentSoal['opsi_' . $letter] = $opsiText;
                        if ($this->isKunciMarked($kunciCell)) {
                            $currentSoal['_kunci_pgk'][] = strtoupper($letter);
                        }
                    }
                }

                // Support multiple letters in KUNCI column (e.g. "B, D, E" or "BDE")
                if (preg_match_all('/[A-Z]/', $kunciCell, $mKunci)) {
                    foreach ($mKunci[0] as $kLtr) {
                        if (!in_array($kLtr, ['V', 'X'])) {
                            $currentSoal['_kunci_pgk'][] = $kLtr;
                        }
                    }
                }

            } elseif ($jenis === 3) {
                // Tipe 3: Menjodohkan
                $bKode = trim(strip_tags($c3));
                $bNama = $c4;
                $kKode = strtoupper(trim(strip_tags($c5)));
                $kNama = $c6;
                $kbKunci = trim(strip_tags($c7));
                $kkKunci = trim(strip_tags($c8));

                $cleanBNama = trim(str_replace(["\xc2\xa0", '&nbsp;', "\xc2\xad"], ' ', strip_tags($bNama)));
                $cleanKNama = trim(str_replace(["\xc2\xa0", '&nbsp;', "\xc2\xad"], ' ', strip_tags($kNama)));

                if ($cleanBNama !== '') {
                    $code = !empty($bKode) ? $bKode : (string)(count($currentSoal['_baris']) + 1);
                    $currentSoal['_baris'][$code] = $bNama;
                }
                if ($cleanKNama !== '') {
                    $code = !empty($kKode) ? $kKode : chr(65 + count($currentSoal['_kolom']));
                    $currentSoal['_kolom'][$code] = $kNama;
                }
                if (!empty($kbKunci) && !empty($kkKunci)) {
                    $currentSoal['_kunci_jodoh'][$kbKunci] = $kkKunci;
                }

            } elseif ($jenis === 4 || $jenis === 5) {
                // Tipe 4: Isian Singkat & Tipe 5: Essai
                $jawabanText = !empty($c3) ? $c3 : (!empty($c4) ? $c4 : (!empty($c5) ? $c5 : ''));
                if (!empty($jawabanText) && empty($currentSoal['jawaban'])) {
                    $currentSoal['jawaban'] = trim($jawabanText);
                }
            }
        }

        if ($currentSoal !== null) {
            $this->finalizeQuestion($currentSoal, $questions);
        }

        return $questions;
    }

    /**
     * Finalizes and formats question before adding to the output array.
     */
    private function finalizeQuestion(array $soal, array &$questions): void
    {
        if (empty(trim(strip_tags((string)$soal['soal'])))) {
            return;
        }

        if ($soal['jenis'] === 1) {
            if (empty($soal['jawaban'])) {
                $soal['jawaban'] = 'A';
            }
        } elseif ($soal['jenis'] === 2) {
            $keys = array_values(array_unique($soal['_kunci_pgk']));
            $soal['jawaban'] = !empty($keys) ? $keys : ['A'];
        } elseif ($soal['jenis'] === 3) {
            $barisList = [];
            $barisMap = [];
            $bIdx = 0;
            foreach ($soal['_baris'] as $code => $text) {
                $barisList[] = $text;
                $barisMap[(string)$code] = $bIdx++;
            }

            $kolomList = [];
            $kolomMap = [];
            $kIdx = 0;
            foreach ($soal['_kolom'] as $code => $text) {
                $kolomList[] = $text;
                $kolomMap[(string)$code] = $kIdx++;
            }

            $links = [];
            foreach ($soal['_kunci_jodoh'] as $bCode => $kCodes) {
                $targetBIdx = $barisMap[(string)$bCode] ?? (is_numeric($bCode) ? (int)$bCode - 1 : null);
                if ($targetBIdx === null) {
                    continue;
                }

                $colParts = preg_split('/[,\s;]+/', (string)$kCodes);
                $targetKIndices = [];
                foreach ($colParts as $cp) {
                    $cp = strtoupper(trim($cp));
                    if ($cp === '') {
                        continue;
                    }
                    if (isset($kolomMap[$cp])) {
                        $targetKIndices[] = $kolomMap[$cp];
                    } elseif (ord($cp) >= 65 && ord($cp) <= 90) {
                        $targetKIndices[] = ord($cp) - 65;
                    } elseif (is_numeric($cp)) {
                        $targetKIndices[] = (int)$cp - 1;
                    }
                }

                if (!empty($targetKIndices)) {
                    $links[(string)$targetBIdx] = array_values(array_unique($targetKIndices));
                }
            }

            $soal['jawaban'] = [
                'type' => 'line',
                'mode' => '1',
                'baris' => $barisList,
                'kolom' => $kolomList,
                'links' => $links,
            ];
        }

        unset($soal['_kunci_pgk'], $soal['_baris'], $soal['_kolom'], $soal['_kunci_jodoh']);
        $questions[] = $soal;
    }

    /**
     * Checks if a cell contains a valid checkmark or true indicator.
     */
    private function isKunciMarked(string $str): bool
    {
        $s = strtoupper(trim(strip_tags($str)));
        return in_array($s, ['V', 'X', '1', 'TRUE', 'BENAR', 'YA', '✓', '✔', '&#10003;', '&#10004;']);
    }

    /**
     * Fallback paragraph-based parser for non-tabular Word documents.
     */
    private function parseFallbackParagraphs(string $xmlContent, array $relIdToImage): array
    {
        $questions = [];
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
                if (preg_match('/\[(?:JENIS|TIPE)\s*:\s*([1-5])\]/i', $trimmed, $jMatch)) {
                    $currentQuestion['jenis'] = (int)$jMatch[1];
                    continue;
                }

                if (preg_match('/^([A-E])[\.\)]\s*(.+)/i', $trimmed, $optMatch)) {
                    $optLetter = strtolower($optMatch[1]);
                    $currentQuestion['opsi_' . $optLetter] = trim($optMatch[2]);
                } elseif (preg_match('/^KUNCI\s*:\s*(.+)/i', $trimmed, $keyMatch)) {
                    $kunciRaw = trim($keyMatch[1]);
                    if (preg_match_all('/[A-E]/i', $kunciRaw, $letters)) {
                        if (count($letters[0]) > 1) {
                            $currentQuestion['jenis'] = 2;
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

        return $questions;
    }

    /**
     * Generates and downloads the official Microsoft Word (.docx) template.
     */
    public function generateTemplateDocx(): void
    {
        $filename = 'Template_Soal_AKM_GarudaCBT.docx';

        $tempFile = tempnam(sys_get_temp_dir(), 'docx_tpl_');
        $zip = new \ZipArchive();
        if ($zip->open($tempFile, \ZipArchive::CREATE | \ZipArchive::OVERWRITE) !== true) {
            throw new \RuntimeException("Gagal membuat berkas sementara docx.");
        }

        // 1. [Content_Types].xml
        $contentTypes = '<?xml version="1.0" encoding="UTF-8" standalone="yes"?>'
            . '<Types xmlns="http://schemas.openxmlformats.org/package/2006/content-types">'
            . '<Default Extension="rels" ContentType="application/vnd.openxmlformats-package.relationships+xml"/>'
            . '<Default Extension="xml" ContentType="application/xml"/>'
            . '<Override PartName="/word/document.xml" ContentType="application/vnd.openxmlformats-officedocument.wordprocessingml.document.main+xml"/>'
            . '</Types>';
        $zip->addFromString('[Content_Types].xml', $contentTypes);

        // 2. _rels/.rels
        $rels = '<?xml version="1.0" encoding="UTF-8" standalone="yes"?>'
            . '<Relationships xmlns="http://schemas.openxmlformats.org/package/2006/relationships">'
            . '<Relationship Id="rId1" Type="http://schemas.openxmlformats.org/officeDocument/2006/relationships/officeDocument" Target="word/document.xml"/>'
            . '</Relationships>';
        $zip->addFromString('_rels/.rels', $rels);

        // 3. word/_rels/document.xml.rels
        $docRels = '<?xml version="1.0" encoding="UTF-8" standalone="yes"?>'
            . '<Relationships xmlns="http://schemas.openxmlformats.org/package/2006/relationships">'
            . '</Relationships>';
        $zip->addFromString('word/_rels/document.xml.rels', $docRels);

        // 4. word/document.xml (Building the 5 AKM Tables)
        $docXml = $this->buildTemplateDocxXml();
        $zip->addFromString('word/document.xml', $docXml);

        $zip->close();

        header('Content-Type: application/vnd.openxmlformats-officedocument.wordprocessingml.document');
        header('Content-Disposition: attachment; filename="' . $filename . '"');
        header('Content-Length: ' . filesize($tempFile));
        header('Pragma: no-cache');
        header('Expires: 0');

        readfile($tempFile);
        @unlink($tempFile);
        exit;
    }

    /**
     * Builds standard OpenXML WordprocessingML content for the 5-Type AKM template.
     */
    private function buildTemplateDocxXml(): string
    {
        $borderXml = '<w:tblBorders>'
            . '<w:top w:val="single" w:sz="4" w:space="0" w:color="CCCCCC"/>'
            . '<w:left w:val="single" w:sz="4" w:space="0" w:color="CCCCCC"/>'
            . '<w:bottom w:val="single" w:sz="4" w:space="0" w:color="CCCCCC"/>'
            . '<w:right w:val="single" w:sz="4" w:space="0" w:color="CCCCCC"/>'
            . '<w:insideH w:val="single" w:sz="4" w:space="0" w:color="CCCCCC"/>'
            . '<w:insideV w:val="single" w:sz="4" w:space="0" w:color="CCCCCC"/>'
            . '</w:tblBorders>';

        $shdHeader = '<w:tcPr><w:shd w:val="clear" w:color="auto" w:fill="DCE6F1"/></w:tcPr>';

        $cell = function (string $text, bool $bold = false, string $customPr = '') {
            $boldTag = $bold ? '<w:b/>' : '';
            return '<w:tc>' . $customPr . '<w:p><w:r><w:rPr>' . $boldTag . '</w:rPr><w:t xml:space="preserve">' . htmlspecialchars($text, ENT_QUOTES) . '</w:t></w:r></w:p></w:tc>';
        };

        $p = function (string $text, bool $bold = false) {
            $boldTag = $bold ? '<w:b/>' : '';
            return '<w:p><w:r><w:rPr>' . $boldTag . '</w:rPr><w:t xml:space="preserve">' . htmlspecialchars($text, ENT_QUOTES) . '</w:t></w:r></w:p>';
        };

        $xml = '<?xml version="1.0" encoding="UTF-8" standalone="yes"?>'
            . '<w:document xmlns:w="http://schemas.openxmlformats.org/wordprocessingml/2006/main">'
            . '<w:body>';

        // Header and Petunjuk
        $xml .= $p('FORMAT IMPOR BANK SOAL STANDAR AKM — GARUDACBT / DADO CBT', true);
        $xml .= $p('Petunjuk Pengisian:', true);
        $xml .= $p('- Jangan merubah atau menghapus susunan kolom pada tabel.');
        $xml .= $p('- Kolom JENIS wajib diisi sesuai jenis soal: 1=PG, 2=PG Kompleks, 3=Menjodohkan, 4=Isian, 5=Essai.');
        $xml .= $p('- Beri tanda v pada kolom KUNCI untuk menandai jawaban yang benar.');
        $xml .= $p('- Gambar dapat disisipkan langsung ke dalam tabel Word.');
        $xml .= $p('');

        // ----------------------------------------------------
        // SECTION 1: Pilihan Ganda (PG 1)
        // ----------------------------------------------------
        $xml .= $p('I. Soal Pilihan Ganda (satu jawaban benar)', true);
        $xml .= $p('- Kolom jenis harus diisi angka 1');
        $xml .= $p('- KUNCI hanya diisi satu jawaban dengan tanda v');
        $xml .= '<w:tbl><w:tblPr><w:tblW w:w="0" w:type="auto"/>' . $borderXml . '</w:tblPr>';
        $xml .= '<w:tr>' . $cell('NO', true, $shdHeader) . $cell('SOAL', true, $shdHeader) . $cell('JENIS', true, $shdHeader) . $cell('OPSI', true, $shdHeader) . $cell('JAWABAN', true, $shdHeader) . $cell('KUNCI', true, $shdHeader) . '</w:tr>';
        // Sample PG 1
        $xml .= '<w:tr>' . $cell('1') . $cell('Hasil dari 15 x 4 : 2 adalah...') . $cell('1') . $cell('A') . $cell('20') . $cell('') . '</w:tr>';
        $xml .= '<w:tr>' . $cell('') . $cell('') . $cell('') . $cell('B') . $cell('30') . $cell('v') . '</w:tr>';
        $xml .= '<w:tr>' . $cell('') . $cell('') . $cell('') . $cell('C') . $cell('40') . $cell('') . '</w:tr>';
        $xml .= '<w:tr>' . $cell('') . $cell('') . $cell('') . $cell('D') . $cell('50') . $cell('') . '</w:tr>';
        $xml .= '<w:tr>' . $cell('') . $cell('') . $cell('') . $cell('E') . $cell('60') . $cell('') . '</w:tr>';
        $xml .= '</w:tbl>';
        $xml .= $p('');

        // ----------------------------------------------------
        // SECTION 2: Pilihan Ganda Kompleks (PG 2)
        // ----------------------------------------------------
        $xml .= $p('II. Soal Pilihan Ganda Kompleks (beberapa jawaban benar)', true);
        $xml .= $p('- Kolom jenis harus diisi angka 2');
        $xml .= $p('- KUNCI bisa diisi lebih dari satu tanda v');
        $xml .= '<w:tbl><w:tblPr><w:tblW w:w="0" w:type="auto"/>' . $borderXml . '</w:tblPr>';
        $xml .= '<w:tr>' . $cell('NO', true, $shdHeader) . $cell('SOAL', true, $shdHeader) . $cell('JENIS', true, $shdHeader) . $cell('OPSI', true, $shdHeader) . $cell('JAWABAN', true, $shdHeader) . $cell('KUNCI', true, $shdHeader) . '</w:tr>';
        // Sample PGK 1
        $xml .= '<w:tr>' . $cell('1') . $cell('Manakah diantara alat berikut ini yang merupakan peralatan dapur?') . $cell('2') . $cell('A') . $cell('Cangkul') . $cell('') . '</w:tr>';
        $xml .= '<w:tr>' . $cell('') . $cell('') . $cell('') . $cell('B') . $cell('Pisau') . $cell('v') . '</w:tr>';
        $xml .= '<w:tr>' . $cell('') . $cell('') . $cell('') . $cell('C') . $cell('Obeng') . $cell('') . '</w:tr>';
        $xml .= '<w:tr>' . $cell('') . $cell('') . $cell('') . $cell('D') . $cell('Spatula') . $cell('v') . '</w:tr>';
        $xml .= '<w:tr>' . $cell('') . $cell('') . $cell('') . $cell('E') . $cell('Panci') . $cell('v') . '</w:tr>';
        $xml .= '<w:tr>' . $cell('') . $cell('') . $cell('') . $cell('F') . $cell('Keranjang') . $cell('') . '</w:tr>';
        $xml .= '</w:tbl>';
        $xml .= $p('');

        // ----------------------------------------------------
        // SECTION 3: Menjodohkan
        // ----------------------------------------------------
        $xml .= $p('III. Soal Menjodohkan', true);
        $xml .= $p('- Kolom jenis harus diisi angka 3');
        $xml .= $p('- KUNCI diisi pasangan KODE BARIS dan KODE KOLOM (pisahkan dengan koma jika multi-respon)');
        $xml .= '<w:tbl><w:tblPr><w:tblW w:w="0" w:type="auto"/>' . $borderXml . '</w:tblPr>';
        $xml .= '<w:tr>'
            . $cell('NO', true, $shdHeader)
            . $cell('SOAL', true, $shdHeader)
            . $cell('JENIS', true, $shdHeader)
            . $cell('KODE BARIS', true, $shdHeader)
            . $cell('NAMA BARIS', true, $shdHeader)
            . $cell('KODE KOLOM', true, $shdHeader)
            . $cell('NAMA KOLOM', true, $shdHeader)
            . $cell('KODE BARIS', true, $shdHeader)
            . $cell('KODE KOLOM', true, $shdHeader)
            . '</w:tr>';
        // Sample Menjodohkan 1
        $xml .= '<w:tr>' . $cell('1') . $cell('Cocokanlah peralatan di bawah ini sesuai tempat penggunaannya!') . $cell('3') . $cell('1') . $cell('Peralatan Dapur') . $cell('A') . $cell('Cangkul') . $cell('1') . $cell('B,D,E') . '</w:tr>';
        $xml .= '<w:tr>' . $cell('') . $cell('') . $cell('') . $cell('2') . $cell('Peralatan Kebun') . $cell('B') . $cell('Pisau') . $cell('2') . $cell('A,F') . '</w:tr>';
        $xml .= '<w:tr>' . $cell('') . $cell('') . $cell('') . $cell('3') . $cell('Peralatan Tukang') . $cell('C') . $cell('Obeng') . $cell('3') . $cell('C,G,H') . '</w:tr>';
        $xml .= '<w:tr>' . $cell('') . $cell('') . $cell('') . $cell('') . $cell('') . $cell('D') . $cell('Spatula') . $cell('') . $cell('') . '</w:tr>';
        $xml .= '<w:tr>' . $cell('') . $cell('') . $cell('') . $cell('') . $cell('') . $cell('E') . $cell('Panci') . $cell('') . $cell('') . '</w:tr>';
        $xml .= '<w:tr>' . $cell('') . $cell('') . $cell('') . $cell('') . $cell('') . $cell('F') . $cell('Keranjang') . $cell('') . $cell('') . '</w:tr>';
        $xml .= '<w:tr>' . $cell('') . $cell('') . $cell('') . $cell('') . $cell('') . $cell('G') . $cell('Gergaji') . $cell('') . $cell('') . '</w:tr>';
        $xml .= '<w:tr>' . $cell('') . $cell('') . $cell('') . $cell('') . $cell('') . $cell('H') . $cell('Palu') . $cell('') . $cell('') . '</w:tr>';
        $xml .= '</w:tbl>';
        $xml .= $p('');

        // ----------------------------------------------------
        // SECTION 4: Isian Singkat
        // ----------------------------------------------------
        $xml .= $p('IV. Soal Isian Singkat', true);
        $xml .= $p('- Kolom jenis harus diisi angka 4');
        $xml .= '<w:tbl><w:tblPr><w:tblW w:w="0" w:type="auto"/>' . $borderXml . '</w:tblPr>';
        $xml .= '<w:tr>' . $cell('NO', true, $shdHeader) . $cell('SOAL', true, $shdHeader) . $cell('JENIS', true, $shdHeader) . $cell('JAWABAN', true, $shdHeader) . '</w:tr>';
        $xml .= '<w:tr>' . $cell('1') . $cell('Ibu kota negara Indonesia yang baru di Kalimantan Timur adalah...') . $cell('4') . $cell('Nusantara') . '</w:tr>';
        $xml .= '<w:tr>' . $cell('2') . $cell('Planet terbesar dalam tata surya kita adalah...') . $cell('4') . $cell('Yupiter') . '</w:tr>';
        $xml .= '</w:tbl>';
        $xml .= $p('');

        // ----------------------------------------------------
        // SECTION 5: Uraian / Essai
        // ----------------------------------------------------
        $xml .= $p('V. Soal Uraian / Essai', true);
        $xml .= $p('- Kolom jenis harus diisi angka 5');
        $xml .= '<w:tbl><w:tblPr><w:tblW w:w="0" w:type="auto"/>' . $borderXml . '</w:tblPr>';
        $xml .= '<w:tr>' . $cell('NO', true, $shdHeader) . $cell('SOAL', true, $shdHeader) . $cell('JENIS', true, $shdHeader) . $cell('JAWABAN', true, $shdHeader) . '</w:tr>';
        $xml .= '<w:tr>' . $cell('1') . $cell('Jelaskan perbedaan antara konduksi, konveksi, dan radiasi serta berikan contohnya!') . $cell('5') . $cell('Pedoman penskoran: Menyebutkan definisi konduksi, konveksi, radiasi secara tepat masing-masing bernilai maksimal 30 poin.') . '</w:tr>';
        $xml .= '</w:tbl>';

        $xml .= '</w:body></w:document>';
        return $xml;
    }
}
