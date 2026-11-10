<?php
$title = $title ?? 'Ujian CBT Siswa — DADO CBT';
$siswa = $siswa ?? [];
$jadwal = $jadwal ?? [];
$session = $session ?? [];
$questions = $questions ?? [];
$remainingSeconds = $remainingSeconds ?? 3600;
$settingSekolah = $settingSekolah ?? [];

$idJadwal = (int)($jadwal['id_jadwal'] ?? 0);
$totalSoal = count($questions);
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title><?= htmlspecialchars($title) ?></title>

    <!-- Google Font: Source Sans Pro -->
    <link rel="stylesheet" href="https://fonts.googleapis.com/css?family=Source+Sans+Pro:300,400,400i,700&display=fallback">
    <!-- Font Awesome -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.4/css/all.min.css">
    <!-- Bootstrap 4 / AdminLTE 3 -->
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/admin-lte@3.2/dist/css/adminlte.min.css">
    <!-- KaTeX CSS for LaTeX equations -->
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/katex@0.16.8/dist/katex.min.css">
    
    <style>
        @font-face {
            font-family: 'Arabic Calibri';
            src: url('/assets/fonts/Arabic-Calibri.ttf') format('truetype');
            font-weight: normal;
            font-style: normal;
        }
        .arabic-text {
            font-family: 'Arabic Calibri', Calibri, Tahoma, sans-serif !important;
            direction: rtl;
            text-align: right;
            font-size: 1.4rem;
            line-height: 2.2;
        }
        body {
            background-color: #f4f6f9;
            user-select: none;
            -webkit-user-select: none;
        }
        .cbt-header {
            background: #ffffff;
            border-bottom: 2px solid #007bff;
            position: sticky;
            top: 0;
            z-index: 1040;
        }
        .timer-badge {
            font-size: 1.5rem;
            font-family: monospace;
            font-weight: bold;
            padding: 4px 14px;
            border-radius: 6px;
            letter-spacing: 2px;
        }
        .zoom-1 { font-size: 1rem; }
        .zoom-2 { font-size: 1.25rem; }
        .zoom-3 { font-size: 1.5rem; }
        
        .soal-container {
            min-height: 480px;
        }
        .option-box {
            border: 2px solid #e9ecef;
            border-radius: 8px;
            padding: 12px 16px;
            cursor: pointer;
            transition: all 0.2s ease;
            background: #ffffff;
            margin-bottom: 12px;
            display: flex;
            align-items: flex-start;
        }
        .option-box:hover {
            border-color: #b8daff;
            background-color: #f8fbff;
        }
        .option-box.active {
            border-color: #007bff;
            background-color: #e7f1ff;
            font-weight: 600;
        }
        .nav-btn-soal {
            width: 48px;
            height: 48px;
            font-weight: bold;
            font-size: 1.1rem;
            display: flex;
            align-items: center;
            justify-content: center;
            border-radius: 8px;
            margin: 4px;
            border: 2px solid #dee2e6;
            cursor: pointer;
            background: #ffffff;
        }
        .nav-btn-soal.answered {
            background-color: #28a745 !important;
            color: #ffffff !important;
            border-color: #218838 !important;
        }
        .nav-btn-soal.ragu {
            background-color: #ffc107 !important;
            color: #212529 !important;
            border-color: #e0a800 !important;
        }
        .nav-btn-soal.current {
            border: 3px solid #007bff !important;
            box-shadow: 0 0 8px rgba(0, 123, 255, 0.5);
        }
        .btn-zoom {
            width: 36px;
            height: 36px;
            padding: 0;
            display: inline-flex;
            align-items: center;
            justify-content: center;
            font-weight: bold;
        }
    </style>
</head>
<body class="hold-transition layout-top-nav">

<div class="wrapper">
    <!-- CBT Header -->
    <header class="cbt-header py-2 px-3 shadow-sm">
        <div class="container-fluid">
            <div class="d-flex flex-wrap justify-content-between align-items-center">
                <!-- Left: Exam Info -->
                <div class="d-flex align-items-center my-1">
                    <img src="<?= !empty($settingSekolah['logo_kiri']) ? upload_url('sekolah/' . $settingSekolah['logo_kiri']) : asset('img/logo.png') ?>" 
                         alt="Logo" style="height: 44px;" class="mr-3">
                    <div>
                        <h5 class="mb-0 font-weight-bold text-dark">
                            <?= htmlspecialchars($jadwal['nama_mapel'] ?? 'Ujian CBT') ?>
                            <span class="badge badge-primary ml-1"><?= htmlspecialchars($jadwal['kode_jenis'] ?? 'CBT') ?></span>
                        </h5>
                        <div class="small text-muted font-weight-bold">
                            Bank: <?= htmlspecialchars($jadwal['bank_kode'] ?? '-') ?> | Peserta: <?= htmlspecialchars($siswa['nama'] ?? 'Siswa') ?> (<?= htmlspecialchars($siswa['nis'] ?? '-') ?>)
                        </div>
                    </div>
                </div>

                <!-- Center: Synchronized Timer -->
                <div class="text-center my-1">
                    <div class="small text-muted font-weight-bold mb-1"><i class="fas fa-stopwatch mr-1"></i>SISA WAKTU:</div>
                    <span id="examTimer" class="timer-badge bg-dark text-warning shadow-sm">00:00:00</span>
                </div>

                <!-- Right: Zoom Controls & Drawer Toggle -->
                <div class="d-flex align-items-center my-1">
                    <div class="btn-group mr-3 shadow-sm">
                        <button type="button" class="btn btn-outline-secondary btn-zoom" onclick="setZoomLevel(1)" title="Ukuran Normal">A</button>
                        <button type="button" class="btn btn-outline-secondary btn-zoom" onclick="setZoomLevel(2)" title="Perbesar Teks">A+</button>
                        <button type="button" class="btn btn-outline-secondary btn-zoom" onclick="setZoomLevel(3)" title="Paling Besar">A++</button>
                    </div>

                    <button type="button" class="btn btn-primary font-weight-bold shadow-sm" data-toggle="modal" data-target="#modalDaftarSoal">
                        <i class="fas fa-th mr-1"></i>Daftar Soal
                    </button>
                </div>
            </div>
        </div>
    </header>

    <!-- Main Content Area -->
    <main class="content my-3">
        <div class="container-fluid">

            <!-- Question Cards -->
            <div id="soalWrapper" class="zoom-1">
                <?php foreach ($questions as $idx => $q): 
                    $noAlias = (int)$q['no_soal_alias'];
                    $jenisQ = (int)$q['jenis_soal'];
                    $idSoalSiswa = (int)$q['id_soal_siswa'];
                    $jawabanSiswa = $q['jawaban_siswa'];
                    $isRagu = (int)$q['ragu'] === 1;

                    // Options mapping for PG Tunggal
                    $aliasMap = [
                        'A' => $q['opsi_alias_a'] ?? 'A',
                        'B' => $q['opsi_alias_b'] ?? 'B',
                        'C' => $q['opsi_alias_c'] ?? 'C',
                        'D' => $q['opsi_alias_d'] ?? 'D',
                        'E' => $q['opsi_alias_e'] ?? 'E',
                    ];
                ?>
                    <div class="card card-outline card-primary shadow-sm soal-card <?= $idx === 0 ? '' : 'd-none' ?>" 
                         id="cardSoal_<?= $noAlias ?>" data-index="<?= $idx ?>" data-no="<?= $noAlias ?>" data-id="<?= $idSoalSiswa ?>" data-jenis="<?= $jenisQ ?>">
                        
                        <!-- Card Header -->
                        <div class="card-header bg-white py-2">
                            <div class="d-flex justify-content-between align-items-center">
                                <div>
                                    <span class="badge badge-primary px-3 py-2 font-weight-bold" style="font-size: 1rem;">
                                        SOAL NO. <?= $noAlias ?>
                                    </span>
                                    <span class="badge badge-light border ml-2">
                                        <?php if ($jenisQ === 1): ?>Pilihan Ganda
                                        <?php elseif ($jenisQ === 2): ?>Pilihan Ganda Kompleks
                                        <?php elseif ($jenisQ === 3): ?>Menjodohkan
                                        <?php elseif ($jenisQ === 4): ?>Isian Singkat
                                        <?php elseif ($jenisQ === 5): ?>Uraian / Essai
                                        <?php endif; ?>
                                    </span>
                                </div>
                                <div class="d-flex align-items-center">
                                    <span class="text-muted small mr-3 auto-save-status text-success"><i class="fas fa-check-circle mr-1"></i>Tersimpan</span>
                                    <div class="custom-control custom-checkbox">
                                        <input type="checkbox" class="custom-control-input check-ragu" id="ragu_<?= $noAlias ?>" <?= $isRagu ? 'checked' : '' ?> onchange="toggleRagu(<?= $noAlias ?>)">
                                        <label class="custom-control-label font-weight-bold text-warning text-dark" for="ragu_<?= $noAlias ?>">Ragu-ragu</label>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- Card Body (Question & Options) -->
                        <div class="card-body soal-container p-4">
                            <!-- Question Text -->
                            <div class="question-text mb-4 p-3 bg-white rounded border">
                                <?= $q['soal'] ?>
                            </div>

                            <!-- Responses Area for 5 AKM Types -->
                            <div class="response-area">
                                <!-- TIPE 1: Pilihan Ganda Tunggal -->
                                <?php if ($jenisQ === 1): ?>
                                    <div class="options-list">
                                        <?php 
                                        $selectedOpsi = strtoupper(trim((string)$jawabanSiswa));
                                        foreach ($aliasMap as $displayLetter => $originalLetter): 
                                            if (empty($originalLetter)) continue;
                                            $colName = 'opsi_' . strtolower($originalLetter);
                                            $opsiContent = $q[$colName] ?? '';
                                            if (empty(trim((string)$opsiContent))) continue;
                                            $isSelected = ($selectedOpsi === $originalLetter);
                                        ?>
                                            <div class="option-box <?= $isSelected ? 'active' : '' ?>" onclick="selectPgOption(<?= $noAlias ?>, '<?= $originalLetter ?>', '<?= $displayLetter ?>', this)">
                                                <div class="option-badge mr-3">
                                                    <span class="badge badge-light border font-weight-bold px-2 py-1" style="font-size: 1.1rem;"><?= $displayLetter ?></span>
                                                </div>
                                                <div class="option-text flex-grow-1">
                                                    <?= $opsiContent ?>
                                                </div>
                                            </div>
                                        <?php endforeach; ?>
                                    </div>

                                <!-- TIPE 2: Pilihan Ganda Kompleks -->
                                <?php elseif ($jenisQ === 2): 
                                    $selectedPgk = is_string($jawabanSiswa) ? json_decode($jawabanSiswa, true) : (array)$jawabanSiswa;
                                    if (!is_array($selectedPgk)) $selectedPgk = [];
                                ?>
                                    <div class="alert alert-info py-2 small mb-3">
                                        <i class="fas fa-info-circle mr-1"></i>Pilih satu atau lebih jawaban yang menurut Anda benar dengan mencentang kotak.
                                    </div>
                                    <div class="options-list">
                                        <?php foreach (['A', 'B', 'C', 'D', 'E'] as $let): 
                                            $colName = 'opsi_' . strtolower($let);
                                            $opsiContent = $q[$colName] ?? '';
                                            if (empty(trim((string)$opsiContent))) continue;
                                            $isChecked = in_array($let, $selectedPgk);
                                        ?>
                                            <div class="custom-control custom-checkbox option-box <?= $isChecked ? 'active' : '' ?>" onclick="togglePgkOption(<?= $noAlias ?>, '<?= $let ?>', this, event)">
                                                <input type="checkbox" class="custom-control-input cb-pgk" id="pgk_<?= $noAlias ?>_<?= $let ?>" value="<?= $let ?>" <?= $isChecked ? 'checked' : '' ?>>
                                                <label class="custom-control-label font-weight-bold text-dark ml-2 d-block" for="pgk_<?= $noAlias ?>_<?= $let ?>">
                                                    <span class="badge badge-light border font-weight-bold mr-2"><?= $let ?></span>
                                                    <?= $opsiContent ?>
                                                </label>
                                            </div>
                                        <?php endforeach; ?>
                                    </div>

                                <!-- TIPE 3: Menjodohkan (Matching with Interactive Line Connector) -->
                                <?php elseif ($jenisQ === 3): 
                                    $kunciJodoh = is_string($q['jawaban_benar']) ? json_decode($q['jawaban_benar'], true) : $q['jawaban_benar'];
                                    $studentJodoh = is_string($jawabanSiswa) ? json_decode($jawabanSiswa, true) : $jawabanSiswa;
                                    $barisList = $kunciJodoh['baris'] ?? [];
                                    $kolomList = $kunciJodoh['kolom'] ?? [];
                                    $activeLinks = (is_array($studentJodoh) && isset($studentJodoh['links'])) ? $studentJodoh['links'] : [];
                                ?>
                                    <div class="alert alert-warning py-2 small mb-3">
                                        <i class="fas fa-bezier-curve mr-1"></i>
                                        Klik bulatan pada <strong>Premis Kiri</strong> lalu klik bulatan pada <strong>Respon Kanan</strong> untuk menghubungkan garis. Soal baru dianggap terjawab jika minimal ada 1 garis terhubung.
                                    </div>

                                    <div class="matching-container border rounded p-3 bg-light position-relative" id="matching_box_<?= $noAlias ?>">
                                        <div class="d-flex justify-content-between mb-2">
                                            <span class="small font-weight-bold text-muted">Hubungkan Pasangan yang Sesuai:</span>
                                            <button type="button" class="btn btn-xs btn-outline-secondary" onclick="resetStudentLines(<?= $noAlias ?>)">
                                                <i class="fas fa-undo mr-1"></i>Hapus Semua Garis
                                            </button>
                                        </div>

                                        <div id="matching_board_<?= $noAlias ?>" class="position-relative bg-white border rounded p-3" style="min-height: 240px;">
                                            <svg id="svg_lines_<?= $noAlias ?>" style="position:absolute; top:0; left:0; width:100%; height:100%; pointer-events:none; z-index:2;"></svg>
                                            <div class="d-flex justify-content-between position-relative" style="z-index:3;">
                                                <!-- Left Premis -->
                                                <div class="premis-nodes d-flex flex-column" style="gap: 16px;">
                                                    <?php foreach ($barisList as $bIdx => $bText): ?>
                                                        <div class="d-flex align-items-center">
                                                            <div class="p-2 bg-light border rounded mr-2" style="max-width: 280px;"><?= $bText ?></div>
                                                            <div class="node-circle bg-primary" data-side="left" data-index="<?= $bIdx ?>" 
                                                                 style="width: 16px; height: 16px; border-radius: 50%; cursor: pointer;" 
                                                                 onclick="clickStudentNode(<?= $noAlias ?>, 'left', <?= $bIdx ?>, this)"></div>
                                                        </div>
                                                    <?php endforeach; ?>
                                                </div>

                                                <!-- Right Respon -->
                                                <div class="respon-nodes d-flex flex-column" style="gap: 16px;">
                                                    <?php foreach ($kolomList as $kIdx => $kText): ?>
                                                        <div class="d-flex align-items-center justify-content-end">
                                                            <div class="node-circle bg-success mr-2" data-side="right" data-index="<?= $kIdx ?>" 
                                                                 style="width: 16px; height: 16px; border-radius: 50%; cursor: pointer;" 
                                                                 onclick="clickStudentNode(<?= $noAlias ?>, 'right', <?= $kIdx ?>, this)"></div>
                                                            <div class="p-2 bg-light border rounded" style="max-width: 280px;"><?= $kText ?></div>
                                                        </div>
                                                    <?php endforeach; ?>
                                                </div>
                                            </div>
                                        </div>
                                    </div>

                                <!-- TIPE 4: Isian Singkat -->
                                <?php elseif ($jenisQ === 4): ?>
                                    <div class="form-group">
                                        <label class="font-weight-bold">Tuliskan Jawaban Singkat Anda di bawah ini:</label>
                                        <input type="text" class="form-control form-control-lg font-weight-bold input-isian" 
                                               id="isian_<?= $noAlias ?>" value="<?= htmlspecialchars((string)$jawabanSiswa) ?>" 
                                               placeholder="Ketik jawaban di sini..." oninput="saveIsianAnswer(<?= $noAlias ?>)">
                                    </div>

                                <!-- TIPE 5: Uraian / Essai (With Rich Text Header Editor) -->
                                <?php elseif ($jenisQ === 5): ?>
                                    <div class="form-group">
                                        <label class="font-weight-bold d-block mb-2">Tuliskan Jawaban Uraian Anda secara Terstruktur:</label>
                                        
                                        <!-- Header Editor Toolbar -->
                                        <div class="btn-toolbar p-1 bg-light border rounded-top" role="toolbar">
                                            <div class="btn-group btn-group-sm mr-2">
                                                <button type="button" class="btn btn-outline-secondary" onclick="formatEssay('bold', <?= $noAlias ?>)"><i class="fas fa-bold"></i></button>
                                                <button type="button" class="btn btn-outline-secondary" onclick="formatEssay('italic', <?= $noAlias ?>)"><i class="fas fa-italic"></i></button>
                                                <button type="button" class="btn btn-outline-secondary" onclick="formatEssay('underline', <?= $noAlias ?>)"><i class="fas fa-underline"></i></button>
                                            </div>
                                            <div class="btn-group btn-group-sm mr-2">
                                                <button type="button" class="btn btn-outline-secondary" onclick="formatEssay('insertUnorderedList', <?= $noAlias ?>)"><i class="fas fa-list-ul"></i></button>
                                                <button type="button" class="btn btn-outline-secondary" onclick="formatEssay('insertOrderedList', <?= $noAlias ?>)"><i class="fas fa-list-ol"></i></button>
                                            </div>
                                            <div class="btn-group btn-group-sm">
                                                <button type="button" class="btn btn-outline-secondary" onclick="formatEssay('subscript', <?= $noAlias ?>)"><i class="fas fa-subscript"></i></button>
                                                <button type="button" class="btn btn-outline-secondary" onclick="formatEssay('superscript', <?= $noAlias ?>)"><i class="fas fa-superscript"></i></button>
                                            </div>
                                        </div>

                                        <!-- Editable Content Area -->
                                        <div class="form-control border rounded-bottom essay-editable p-3" 
                                             id="essay_<?= $noAlias ?>" contenteditable="true" style="min-height: 180px; max-height: 380px; overflow-y: auto;" 
                                             oninput="saveEssayAnswer(<?= $noAlias ?>)"><?= $jawabanSiswa ?></div>
                                    </div>
                                <?php endif; ?>
                            </div>
                        </div>

                        <!-- Card Footer Navigation -->
                        <div class="card-footer bg-light d-flex justify-content-between align-items-center py-3">
                            <button type="button" class="btn btn-secondary font-weight-bold px-4" <?= $idx === 0 ? 'disabled' : '' ?> onclick="navigateSoal(<?= $noAlias - 1 ?>)">
                                <i class="fas fa-chevron-left mr-1"></i>Sebelumnya
                            </button>

                            <button type="button" class="btn btn-warning text-dark font-weight-bold px-3" onclick="toggleRagu(<?= $noAlias ?>)">
                                <i class="fas fa-flag mr-1"></i>Ragu-ragu
                            </button>

                            <?php if ($idx === $totalSoal - 1): ?>
                                <button type="button" class="btn btn-success font-weight-bold px-4 shadow-sm" onclick="openFinishModal()">
                                    <i class="fas fa-check-circle mr-1"></i>SELESAI UJIAN
                                </button>
                            <?php else: ?>
                                <button type="button" class="btn btn-primary font-weight-bold px-4 shadow-sm" onclick="navigateSoal(<?= $noAlias + 1 ?>)">
                                    Berikutnya<i class="fas fa-chevron-right ml-1"></i>
                                </button>
                            <?php endif; ?>
                        </div>
                    </div>
                <?php endforeach; ?>
            </div>

        </div>
    </main>
</div>

<!-- Modal Daftar Nomor Soal -->
<div class="modal fade" id="modalDaftarSoal" tabindex="-1" role="dialog" aria-labelledby="modalDaftarSoalLabel" aria-hidden="true">
    <div class="modal-dialog modal-lg" role="document">
        <div class="modal-content">
            <div class="modal-header bg-primary text-white py-2">
                <h5 class="modal-title font-weight-bold" id="modalDaftarSoalLabel">
                    <i class="fas fa-th mr-2"></i>Daftar Nomor Soal (<?= $totalSoal ?> Butir)
                </h5>
                <button type="button" class="close text-white" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <div class="modal-body p-3">
                <div class="d-flex flex-wrap justify-content-center">
                    <?php foreach ($questions as $q): 
                        $no = (int)$q['no_soal_alias'];
                        $isRagu = (int)$q['ragu'] === 1;
                        $hasAnswer = !empty($q['jawaban_siswa']);
                    ?>
                        <div class="nav-btn-soal <?= $hasAnswer ? 'answered' : '' ?> <?= $isRagu ? 'ragu' : '' ?> <?= $no === 1 ? 'current' : '' ?>" 
                             id="nav_badge_<?= $no ?>" onclick="jumpToSoal(<?= $no ?>)">
                            <?= $no ?>
                        </div>
                    <?php endforeach; ?>
                </div>

                <hr>
                <div class="d-flex justify-content-center text-muted small">
                    <span class="mr-3"><span class="badge badge-success px-2 py-1">&nbsp;</span> Terjawab</span>
                    <span class="mr-3"><span class="badge badge-warning text-dark px-2 py-1">&nbsp;</span> Ragu-ragu</span>
                    <span><span class="badge badge-light border px-2 py-1">&nbsp;</span> Belum Terjawab</span>
                </div>
            </div>
            <div class="modal-footer py-2">
                <button type="button" class="btn btn-secondary" data-dismiss="modal">Tutup</button>
            </div>
        </div>
    </div>
</div>

<!-- Modal Konfirmasi Selesai Ujian -->
<div class="modal fade" id="modalSelesaiUjian" tabindex="-1" role="dialog" aria-hidden="true">
    <div class="modal-dialog" role="document">
        <form action="/student/cbt/finish/<?= $idJadwal ?>" method="POST">
            <div class="modal-content">
                <div class="modal-header bg-success text-white py-2">
                    <h5 class="modal-title font-weight-bold">
                        <i class="fas fa-check-circle mr-2"></i>Konfirmasi Akhiri Ujian
                    </h5>
                    <button type="button" class="close text-white" data-dismiss="modal" aria-label="Close">
                        <span aria-hidden="true">&times;</span>
                    </button>
                </div>
                <div class="modal-body py-4 text-center">
                    <i class="fas fa-question-circle fa-4x text-warning mb-3"></i>
                    <h5 class="font-weight-bold text-dark">Apakah Anda yakin ingin mengakhiri ujian ini?</h5>
                    <p class="text-muted small">Pastikan Anda telah memeriksa kembali seluruh jawaban. Setelah menekan tombol <strong>Selesai</strong>, Anda tidak dapat mengubah jawaban lagi.</p>
                </div>
                <div class="modal-footer py-2 justify-content-between">
                    <button type="button" class="btn btn-secondary font-weight-bold" data-dismiss="modal">Lanjutkan Mengerjakan</button>
                    <button type="submit" class="btn btn-success font-weight-bold px-4">
                        <i class="fas fa-check mr-1"></i>Ya, Selesai Ujian
                    </button>
                </div>
            </div>
        </form>
    </div>
</div>

<!-- Scripts -->
<script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@4.6.2/dist/js/bootstrap.bundle.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/katex@0.16.8/dist/katex.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/katex@0.16.8/dist/contrib/auto-render.min.js"></script>

<script>
// State variables
var totalQuestions = <?= $totalSoal ?>;
var currentSoalNo = 1;
var remainingTime = <?= $remainingSeconds ?>;
var timerInterval = null;
var studentMatchingLinks = {};
var selectedLeftDot = {};
var violationCounter = <?= (int)($session['pelanggaran_tab'] ?? 0) ?>;

// Initialize Line matching links from database
<?php foreach ($questions as $q): 
    if ((int)$q['jenis_soal'] === 3):
        $stJ = is_string($q['jawaban_siswa']) ? json_decode($q['jawaban_siswa'], true) : $q['jawaban_siswa'];
        $linksInit = (is_array($stJ) && isset($stJ['links'])) ? $stJ['links'] : [];
?>
    studentMatchingLinks[<?= (int)$q['no_soal_alias'] ?>] = <?= json_encode($linksInit) ?>;
<?php endif; endforeach; ?>

document.addEventListener('DOMContentLoaded', function() {
    // 1. Initialize KaTeX math rendering
    if (typeof renderMathInElement !== 'undefined') {
        renderMathInElement(document.body, {
            delimiters: [
                {left: '$$', right: '$$', display: true},
                {left: '$', right: '$', display: false},
                {left: '\\(', right: '\\)', display: false},
                {left: '\\[', right: '\\]', display: true}
            ],
            throwOnError: false
        });
    }

    // 2. Start Synchronized Server Timer
    startExamTimer();

    // 3. Initialize Matching Lines for question 1 if applicable
    redrawStudentLines(1);

    // 4. Register Anti-Cheat Listeners
    initAntiCheat();
});

// ==========================================
// TIMER SINKRON WAKTU SERVER
// ==========================================
function startExamTimer() {
    var timerEl = document.getElementById('examTimer');
    function updateClock() {
        if (remainingTime <= 0) {
            clearInterval(timerInterval);
            timerEl.innerText = "00:00:00";
            alert("Waktu ujian telah habis! Sistem akan mengumpulkan jawaban Anda secara otomatis.");
            window.location.href = "/student/cbt/finish/<?= $idJadwal ?>";
            return;
        }

        var hours = Math.floor(remainingTime / 3600);
        var minutes = Math.floor((remainingTime % 3600) / 60);
        var seconds = remainingTime % 60;

        timerEl.innerText = 
            String(hours).padStart(2, '0') + ':' + 
            String(minutes).padStart(2, '0') + ':' + 
            String(seconds).padStart(2, '0');

        remainingTime--;
    }
    updateClock();
    timerInterval = setInterval(updateClock, 1000);
}

// ==========================================
// NAVIGATION & ZOOM CONTROLS
// ==========================================
function navigateSoal(targetNo) {
    if (targetNo < 1 || targetNo > totalQuestions) return;
    document.querySelectorAll('.soal-card').forEach(function(card) {
        card.classList.add('d-none');
    });

    var targetCard = document.getElementById('cardSoal_' + targetNo);
    if (targetCard) {
        targetCard.classList.remove('d-none');
        currentSoalNo = targetNo;
        
        // Update nav drawer active outline
        document.querySelectorAll('.nav-btn-soal').forEach(function(btn) {
            btn.classList.remove('current');
        });
        var navBtn = document.getElementById('nav_badge_' + targetNo);
        if (navBtn) navBtn.classList.add('current');

        // Redraw lines if Tipe 3 Menjodohkan
        redrawStudentLines(targetNo);
    }
}

function jumpToSoal(targetNo) {
    $('#modalDaftarSoal').modal('hide');
    navigateSoal(targetNo);
}

function setZoomLevel(level) {
    var wrapper = document.getElementById('soalWrapper');
    wrapper.classList.remove('zoom-1', 'zoom-2', 'zoom-3');
    wrapper.classList.add('zoom-' + level);
}

// ==========================================
// AUTO-SAVE ANSWER (SINGLE-ROW ATOMIC AJAX)
// ==========================================
function postSaveAnswer(noAlias, jawabanVal, jawabanAliasVal, raguVal) {
    var card = document.getElementById('cardSoal_' + noAlias);
    var idSoalSiswa = card.getAttribute('data-id');
    var statusEl = card.querySelector('.auto-save-status');

    if (statusEl) {
        statusEl.innerHTML = '<i class="fas fa-spinner fa-spin mr-1"></i>Menyimpan...';
        statusEl.className = 'text-muted small mr-3 auto-save-status';
    }

    $.ajax({
        url: '/student/cbt/save-answer',
        type: 'POST',
        data: {
            id_soal_siswa: idSoalSiswa,
            jawaban: jawabanVal,
            jawaban_alias: jawabanAliasVal,
            ragu: raguVal ? 1 : 0
        },
        success: function(resp) {
            if (statusEl) {
                statusEl.innerHTML = '<i class="fas fa-check-circle mr-1"></i>Tersimpan';
                statusEl.className = 'text-success small mr-3 auto-save-status';
            }
            updateNavBadgeStatus(noAlias);
        },
        error: function(err) {
            if (statusEl) {
                statusEl.innerHTML = '<i class="fas fa-exclamation-circle mr-1"></i>Gagal Simpan';
                statusEl.className = 'text-danger small mr-3 auto-save-status';
            }
        }
    });
}

function updateNavBadgeStatus(noAlias) {
    var navBadge = document.getElementById('nav_badge_' + noAlias);
    var card = document.getElementById('cardSoal_' + noAlias);
    if (!navBadge || !card) return;

    var jenis = parseInt(card.getAttribute('data-jenis'));
    var isRagu = card.querySelector('.check-ragu').checked;
    var isAnswered = false;

    if (jenis === 1) {
        isAnswered = card.querySelectorAll('.option-box.active').length > 0;
    } else if (jenis === 2) {
        isAnswered = card.querySelectorAll('.cb-pgk:checked').length > 0;
    } else if (jenis === 3) {
        // Strict PRD rule: only answered if links has at least 1 non-empty connection
        var links = studentMatchingLinks[noAlias] || {};
        for (var k in links) {
            if (Array.isArray(links[k]) && links[k].length > 0) {
                isAnswered = true;
                break;
            }
        }
    } else if (jenis === 4) {
        var isianVal = card.querySelector('.input-isian').value;
        isAnswered = isianVal.trim().length > 0;
    } else if (jenis === 5) {
        var essayText = card.querySelector('.essay-editable').innerText;
        isAnswered = essayText.trim().length > 0;
    }

    if (isAnswered) navBadge.classList.add('answered');
    else navBadge.classList.remove('answered');

    if (isRagu) navBadge.classList.add('ragu');
    else navBadge.classList.remove('ragu');
}

function toggleRagu(noAlias) {
    var card = document.getElementById('cardSoal_' + noAlias);
    var raguCb = card.querySelector('.check-ragu');
    var isRagu = raguCb.checked;
    // Trigger atomic save
    saveCurrentSoal(noAlias, isRagu);
}

function saveCurrentSoal(noAlias, isRagu) {
    var card = document.getElementById('cardSoal_' + noAlias);
    var jenis = parseInt(card.getAttribute('data-jenis'));

    if (jenis === 1) {
        var activeBox = card.querySelector('.option-box.active');
        var ans = activeBox ? activeBox.getAttribute('data-original') : '';
        var alias = activeBox ? activeBox.getAttribute('data-display') : '';
        postSaveAnswer(noAlias, ans, alias, isRagu);
    } else if (jenis === 2) {
        var checked = [];
        card.querySelectorAll('.cb-pgk:checked').forEach(function(cb) { checked.push(cb.value); });
        postSaveAnswer(noAlias, checked, checked, isRagu);
    } else if (jenis === 3) {
        var payload = { type: 'line', mode: '1', links: studentMatchingLinks[noAlias] || {} };
        postSaveAnswer(noAlias, payload, payload, isRagu);
    } else if (jenis === 4) {
        var val = card.querySelector('.input-isian').value;
        postSaveAnswer(noAlias, val, val, isRagu);
    } else if (jenis === 5) {
        var valHtml = card.querySelector('.essay-editable').innerHTML;
        postSaveAnswer(noAlias, valHtml, valHtml, isRagu);
    }
}

// ==========================================
// TIPE 1: PG TUNGGAL HANDLER
// ==========================================
function selectPgOption(noAlias, originalLetter, displayLetter, boxEl) {
    var card = document.getElementById('cardSoal_' + noAlias);
    card.querySelectorAll('.option-box').forEach(function(b) { b.classList.remove('active'); });
    boxEl.classList.add('active');
    boxEl.setAttribute('data-original', originalLetter);
    boxEl.setAttribute('data-display', displayLetter);

    var isRagu = card.querySelector('.check-ragu').checked;
    postSaveAnswer(noAlias, originalLetter, displayLetter, isRagu);
}

// ==========================================
// TIPE 2: PG KOMPLEKS HANDLER
// ==========================================
function togglePgkOption(noAlias, letter, boxEl, event) {
    var cb = boxEl.querySelector('.cb-pgk');
    if (event.target !== cb) {
        cb.checked = !cb.checked;
    }
    if (cb.checked) boxEl.classList.add('active');
    else boxEl.classList.remove('active');

    var card = document.getElementById('cardSoal_' + noAlias);
    var checked = [];
    card.querySelectorAll('.cb-pgk:checked').forEach(function(c) { checked.push(c.value); });

    var isRagu = card.querySelector('.check-ragu').checked;
    postSaveAnswer(noAlias, checked, checked, isRagu);
}

// ==========================================
// TIPE 3: MENJODOHKAN (LINE CONNECTOR)
// ==========================================
function clickStudentNode(noAlias, side, index, el) {
    if (side === 'left') {
        selectedLeftDot[noAlias] = index;
        var box = document.getElementById('matching_box_' + noAlias);
        box.querySelectorAll('.node-circle[data-side="left"]').forEach(function(d) { d.style.boxShadow = 'none'; });
        el.style.boxShadow = '0 0 0 4px #ffc107';
    } else if (side === 'right') {
        if (selectedLeftDot[noAlias] === undefined || selectedLeftDot[noAlias] === null) {
            alert('Klik bulatan premis kiri terlebih dahulu.');
            return;
        }

        var lKey = String(selectedLeftDot[noAlias]);
        var rKey = String(index);

        if (!studentMatchingLinks[noAlias]) studentMatchingLinks[noAlias] = {};
        if (!studentMatchingLinks[noAlias][lKey]) studentMatchingLinks[noAlias][lKey] = [];

        var foundIdx = studentMatchingLinks[noAlias][lKey].indexOf(rKey);
        if (foundIdx !== -1) {
            studentMatchingLinks[noAlias][lKey].splice(foundIdx, 1);
            if (studentMatchingLinks[noAlias][lKey].length === 0) {
                delete studentMatchingLinks[noAlias][lKey];
            }
        } else {
            studentMatchingLinks[noAlias][lKey].push(rKey);
        }

        selectedLeftDot[noAlias] = null;
        var box = document.getElementById('matching_box_' + noAlias);
        box.querySelectorAll('.node-circle[data-side="left"]').forEach(function(d) { d.style.boxShadow = 'none'; });

        redrawStudentLines(noAlias);

        var card = document.getElementById('cardSoal_' + noAlias);
        var isRagu = card.querySelector('.check-ragu').checked;
        var payload = { type: 'line', mode: '1', links: studentMatchingLinks[noAlias] };
        postSaveAnswer(noAlias, payload, payload, isRagu);
    }
}

function redrawStudentLines(noAlias) {
    var svg = document.getElementById('svg_lines_' + noAlias);
    var board = document.getElementById('matching_board_' + noAlias);
    if (!svg || !board) return;

    svg.innerHTML = '';
    var boardRect = board.getBoundingClientRect();
    var links = studentMatchingLinks[noAlias] || {};

    for (var leftKey in links) {
        var targets = links[leftKey];
        if (!Array.isArray(targets)) continue;

        var leftDot = board.querySelector('.node-circle[data-side="left"][data-index="' + leftKey + '"]');
        if (!leftDot) continue;
        var leftRect = leftDot.getBoundingClientRect();
        var x1 = leftRect.left - boardRect.left + (leftRect.width / 2);
        var y1 = leftRect.top - boardRect.top + (leftRect.height / 2);

        targets.forEach(function(rightKey) {
            var rightDot = board.querySelector('.node-circle[data-side="right"][data-index="' + rightKey + '"]');
            if (!rightDot) return;
            var rightRect = rightDot.getBoundingClientRect();
            var x2 = rightRect.left - boardRect.left + (rightRect.width / 2);
            var y2 = rightRect.top - boardRect.top + (rightRect.height / 2);

            var line = document.createElementNS('http://www.w3.org/2000/svg', 'line');
            line.setAttribute('x1', x1);
            line.setAttribute('y1', y1);
            line.setAttribute('x2', x2);
            line.setAttribute('y2', y2);
            line.setAttribute('stroke', '#ff9800');
            line.setAttribute('stroke-width', '3');
            line.setAttribute('stroke-linecap', 'round');
            svg.appendChild(line);
        });
    }

    updateNavBadgeStatus(noAlias);
}

function resetStudentLines(noAlias) {
    studentMatchingLinks[noAlias] = {};
    selectedLeftDot[noAlias] = null;
    redrawStudentLines(noAlias);

    var card = document.getElementById('cardSoal_' + noAlias);
    var isRagu = card.querySelector('.check-ragu').checked;
    var payload = { type: 'line', mode: '1', links: {} };
    postSaveAnswer(noAlias, payload, payload, isRagu);
}

// ==========================================
// TIPE 4 & 5: ISIAN & ESSAI HANDLERS
// ==========================================
var isianTimer = null;
function saveIsianAnswer(noAlias) {
    clearTimeout(isianTimer);
    isianTimer = setTimeout(function() {
        var card = document.getElementById('cardSoal_' + noAlias);
        var val = card.querySelector('.input-isian').value;
        var isRagu = card.querySelector('.check-ragu').checked;
        postSaveAnswer(noAlias, val, val, isRagu);
    }, 600);
}

var essayTimer = null;
function saveEssayAnswer(noAlias) {
    clearTimeout(essayTimer);
    essayTimer = setTimeout(function() {
        var card = document.getElementById('cardSoal_' + noAlias);
        var val = card.querySelector('.essay-editable').innerHTML;
        var isRagu = card.querySelector('.check-ragu').checked;
        postSaveAnswer(noAlias, val, val, isRagu);
    }, 800);
}

function formatEssay(command, noAlias) {
    document.execCommand(command, false, null);
    var card = document.getElementById('cardSoal_' + noAlias);
    card.querySelector('.essay-editable').focus();
    saveEssayAnswer(noAlias);
}

function openFinishModal() {
    $('#modalSelesaiUjian').modal('show');
}

// ==========================================
// ANTI-CHEAT DETECTION (BLUR & VISIBILITY)
// ==========================================
function initAntiCheat() {
    var hasWarned = false;

    function handleViolation() {
        violationCounter++;
        $.post('/student/cbt/log-violation', { id_jadwal: <?= $idJadwal ?> });

        alert("PERINGATAN SISTEM ANTI-CHEAT!\nAnda terdeteksi berpindah tab atau meninggalkan jendela ujian.\nPelanggaran ke-" + violationCounter + " telah dicatat oleh pengawas ruang.");
    }

    document.addEventListener('visibilitychange', function() {
        if (document.hidden) {
            handleViolation();
        }
    });

    window.addEventListener('blur', function() {
        // Debounce blur
        if (!hasWarned) {
            hasWarned = true;
            setTimeout(function() { hasWarned = false; }, 3000);
            handleViolation();
        }
    });
}
</script>
</body>
</html>

