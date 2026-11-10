<?php
$title = $title ?? 'Editor Soal AKM — DADO CBT';
$bank = $bank ?? [];
$editSoal = $editSoal ?? null;
$nextNomor = $nextNomor ?? 1;
$selectedJenis = (int)($selectedJenis ?? ($editSoal ? $editSoal['jenis'] : 1));
$idBank = (int)($bank['id_bank'] ?? 0);
$opsiCount = (int)($bank['opsi'] ?? 4); // 3, 4, or 5 options
$letters = ['A', 'B', 'C', 'D', 'E'];
$activeLetters = array_slice($letters, 0, $opsiCount);

// Parse answer for edit
$currentJawaban = $editSoal ? $editSoal['jawaban'] : '';
$pgkAnswers = [];
$jodohData = ['baris' => ['Premis 1', 'Premis 2'], 'kolom' => ['Respon A', 'Respon B'], 'links' => ['0' => ['0'], '1' => ['1']]];

if ($editSoal) {
    if ($selectedJenis === 2) {
        $parsed = is_string($currentJawaban) ? json_decode($currentJawaban, true) : $currentJawaban;
        $pgkAnswers = is_array($parsed) ? $parsed : [];
    } elseif ($selectedJenis === 3) {
        $parsed = is_string($currentJawaban) ? json_decode($currentJawaban, true) : $currentJawaban;
        if (is_array($parsed) && isset($parsed['baris'], $parsed['kolom'])) {
            $jodohData = $parsed;
        }
    }
}
?>

<div class="content-header">
    <div class="container-fluid">
        <div class="row mb-2">
            <div class="col-sm-6">
                <h1 class="m-0 text-dark font-weight-bold">
                    <i class="fas fa-edit text-primary mr-2"></i><?= $editSoal ? 'Edit Butir Soal' : 'Tambah Butir Soal' ?>
                </h1>
            </div>
            <div class="col-sm-6">
                <ol class="breadcrumb float-sm-right">
                    <li class="breadcrumb-item"><a href="/dashboard">Beranda</a></li>
                    <li class="breadcrumb-item"><a href="/cbt/bank-soal">Bank Soal</a></li>
                    <li class="breadcrumb-item"><a href="/cbt/bank-soal/detail/<?= $idBank ?>"><?= htmlspecialchars($bank['bank_kode'] ?? 'Detail') ?></a></li>
                    <li class="breadcrumb-item active"><?= $editSoal ? 'Edit Soal #' . ($editSoal['nomor_soal'] ?? '') : 'Tambah Soal' ?></li>
                </ol>
            </div>
        </div>
    </div>
</div>

<section class="content">
    <div class="container-fluid">

        <?php if ($flash = get_flash('error')): ?>
            <div class="alert alert-danger alert-dismissible fade show" role="alert">
                <i class="fas fa-exclamation-triangle mr-2"></i><?= htmlspecialchars($flash) ?>
                <button type="button" class="close" data-dismiss="alert" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
        <?php endif; ?>

        <!-- Selector Tipe Soal (5 AKM) -->
        <div class="card shadow-sm mb-4">
            <div class="card-body p-2">
                <div class="d-flex flex-wrap align-items-center justify-content-between">
                    <div class="font-weight-bold text-muted ml-2">Pilih Tipe Soal AKM:</div>
                    <div class="btn-group btn-group-toggle" data-toggle="buttons">
                        <a href="/cbt/bank-soal/soal/<?= $idBank ?><?= $editSoal ? '?soal_id=' . $editSoal['id_soal'] . '&' : '?' ?>jenis=1" class="btn btn-outline-primary <?= $selectedJenis === 1 ? 'active font-weight-bold' : '' ?>">
                            <i class="fas fa-check-circle mr-1"></i>1. PG Tunggal
                        </a>
                        <a href="/cbt/bank-soal/soal/<?= $idBank ?><?= $editSoal ? '?soal_id=' . $editSoal['id_soal'] . '&' : '?' ?>jenis=2" class="btn btn-outline-info <?= $selectedJenis === 2 ? 'active font-weight-bold' : '' ?>">
                            <i class="fas fa-check-double mr-1"></i>2. PG Kompleks
                        </a>
                        <a href="/cbt/bank-soal/soal/<?= $idBank ?><?= $editSoal ? '?soal_id=' . $editSoal['id_soal'] . '&' : '?' ?>jenis=3" class="btn btn-outline-warning <?= $selectedJenis === 3 ? 'active font-weight-bold' : '' ?>">
                            <i class="fas fa-bezier-curve mr-1"></i>3. Menjodohkan
                        </a>
                        <a href="/cbt/bank-soal/soal/<?= $idBank ?><?= $editSoal ? '?soal_id=' . $editSoal['id_soal'] . '&' : '?' ?>jenis=4" class="btn btn-outline-secondary <?= $selectedJenis === 4 ? 'active font-weight-bold' : '' ?>">
                            <i class="fas fa-pencil-alt mr-1"></i>4. Isian Singkat
                        </a>
                        <a href="/cbt/bank-soal/soal/<?= $idBank ?><?= $editSoal ? '?soal_id=' . $editSoal['id_soal'] . '&' : '?' ?>jenis=5" class="btn btn-outline-dark <?= $selectedJenis === 5 ? 'active font-weight-bold' : '' ?>">
                            <i class="fas fa-align-left mr-1"></i>5. Esai / Uraian
                        </a>
                    </div>
                </div>
            </div>
        </div>

        <form action="/cbt/bank-soal/soal/<?= $idBank ?>/store" method="POST" id="formSoal">
            <input type="hidden" name="id_soal" value="<?= (int)($editSoal['id_soal'] ?? 0) ?>">
            <input type="hidden" name="jenis" value="<?= $selectedJenis ?>">

            <div class="row">
                <!-- Main Editor Area -->
                <div class="col-lg-9 col-md-12">
                    <!-- Pertanyaan Soal -->
                    <div class="card card-outline card-primary shadow-sm mb-4">
                        <div class="card-header bg-white py-2">
                            <div class="d-flex justify-content-between align-items-center">
                                <h3 class="card-title font-weight-bold text-dark mb-0">
                                    <i class="fas fa-question-circle text-primary mr-1"></i>
                                    Konten Pertanyaan (Nomor <?= $nextNomor ?>)
                                </h3>
                                <div>
                                    <!-- Tool Helper Buttons for LaTeX KaTeX and Arabic Calibri -->
                                    <button type="button" class="btn btn-sm btn-outline-secondary mr-1" onclick="insertLatexTemplate()">
                                        <i class="fas fa-square-root-alt mr-1"></i>LaTeX Equation
                                    </button>
                                    <button type="button" class="btn btn-sm btn-outline-success" onclick="toggleArabicInput()">
                                        <i class="fas fa-globe-asia mr-1"></i>Font Arabic Calibri
                                    </button>
                                </div>
                            </div>
                        </div>
                        <div class="card-body">
                            <div class="form-group mb-2">
                                <textarea name="soal" id="soal_editor" class="form-control summernote" rows="8"><?= htmlspecialchars($editSoal['soal'] ?? '') ?></textarea>
                            </div>

                            <!-- KaTeX Live Formula Preview Panel -->
                            <div class="alert alert-light border p-2 mt-2" id="katexPreviewBox">
                                <small class="text-muted font-weight-bold d-block mb-1">
                                    <i class="fas fa-eye mr-1"></i>Live Preview KaTeX Math & Teks:
                                </small>
                                <div id="katexPreviewContent" class="small"><em>Ketik soal atau rumus LaTeX menggunakan pembatas <code>$rumus$</code> untuk melihat pratinjau rumus otomatis.</em></div>
                            </div>
                        </div>
                    </div>

                    <!-- TIPE 1: Pilihan Ganda Tunggal -->
                    <?php if ($selectedJenis === 1): ?>
                        <div class="card card-outline card-primary shadow-sm mb-4">
                            <div class="card-header bg-white py-2">
                                <h3 class="card-title font-weight-bold text-primary mb-0">
                                    <i class="fas fa-list mr-1"></i>Opsi Jawaban Pilihan Ganda (Tersedia: <?= $opsiCount ?> Pilihan)
                                </h3>
                            </div>
                            <div class="card-body">
                                <p class="text-muted small">Pilih salah satu radio button di sebelah kiri untuk menandai <strong>Kunci Jawaban Benar</strong>.</p>
                                
                                <?php foreach ($activeLetters as $letter): 
                                    $colKey = 'opsi_' . strtolower($letter);
                                    $valOpsi = $editSoal[$colKey] ?? '';
                                    $isChecked = ($editSoal ? (strtoupper(trim((string)$editSoal['jawaban'])) === $letter) : ($letter === 'A'));
                                ?>
                                    <div class="card card-light mb-3 border">
                                        <div class="card-header py-2 bg-light">
                                            <div class="custom-control custom-radio">
                                                <input type="radio" id="jawaban_pg_<?= $letter ?>" name="jawaban_pg" value="<?= $letter ?>" class="custom-control-input" <?= $isChecked ? 'checked' : '' ?>>
                                                <label class="custom-control-label font-weight-bold text-dark" for="jawaban_pg_<?= $letter ?>">
                                                    Opsi <?= $letter ?> (Pilihan Jawaban)
                                                    <span class="badge badge-success ml-2 label-kunci <?= $isChecked ? '' : 'd-none' ?>">KUNCI BENAR</span>
                                                </label>
                                            </div>
                                        </div>
                                        <div class="card-body py-2">
                                            <textarea name="opsi_<?= strtolower($letter) ?>" class="form-control summernote-opsi" rows="2"><?= htmlspecialchars($valOpsi) ?></textarea>
                                        </div>
                                    </div>
                                <?php endforeach; ?>
                            </div>
                        </div>

                    <!-- TIPE 2: Pilihan Ganda Kompleks -->
                    <?php elseif ($selectedJenis === 2): ?>
                        <div class="card card-outline card-info shadow-sm mb-4">
                            <div class="card-header bg-white py-2">
                                <h3 class="card-title font-weight-bold text-info mb-0">
                                    <i class="fas fa-tasks mr-1"></i>Pilihan Ganda Kompleks (Multi-Jawaban)
                                </h3>
                            </div>
                            <div class="card-body">
                                <p class="text-muted small">Centang satu atau lebih kotak checkbox pada opsi yang merupakan <strong>Jawaban Benar</strong>.</p>

                                <?php foreach ($activeLetters as $letter): 
                                    $colKey = 'opsi_' . strtolower($letter);
                                    $valOpsi = $editSoal[$colKey] ?? '';
                                    $isChecked = in_array($letter, $pgkAnswers);
                                ?>
                                    <div class="card card-light mb-3 border">
                                        <div class="card-header py-2 bg-light">
                                            <div class="custom-control custom-checkbox">
                                                <input type="checkbox" id="jawaban_pgk_<?= $letter ?>" name="jawaban_pgk[]" value="<?= $letter ?>" class="custom-control-input" <?= $isChecked ? 'checked' : '' ?>>
                                                <label class="custom-control-label font-weight-bold text-dark" for="jawaban_pgk_<?= $letter ?>">
                                                    Opsi <?= $letter ?>
                                                    <span class="badge badge-info ml-2 label-pgk-kunci <?= $isChecked ? '' : 'd-none' ?>">KUNCI BENAR</span>
                                                </label>
                                            </div>
                                        </div>
                                        <div class="card-body py-2">
                                            <textarea name="opsi_<?= strtolower($letter) ?>" class="form-control summernote-opsi" rows="2"><?= htmlspecialchars($valOpsi) ?></textarea>
                                        </div>
                                    </div>
                                <?php endforeach; ?>
                            </div>
                        </div>

                    <!-- TIPE 3: Menjodohkan (Matching with Line Connector) -->
                    <?php elseif ($selectedJenis === 3): ?>
                        <div class="card card-outline card-warning shadow-sm mb-4">
                            <div class="card-header bg-white py-2">
                                <h3 class="card-title font-weight-bold text-warning text-dark mb-0">
                                    <i class="fas fa-bezier-curve mr-1"></i>Menjodohkan / Matching (Canvas & Line Connector)
                                </h3>
                            </div>
                            <div class="card-body">
                                <div class="alert alert-info py-2 small">
                                    <i class="fas fa-info-circle mr-1"></i>
                                    Atur butir pernyataan pada <strong>Premis Kiri</strong> dan pilihan pada <strong>Respon Kanan</strong>. Hubungkan pasangan kunci jawaban menggunakan garis interaktif di bawah.
                                </div>

                                <div class="row">
                                    <!-- Kolom Kiri: Premis -->
                                    <div class="col-md-6">
                                        <div class="d-flex justify-content-between align-items-center mb-2">
                                            <h6 class="font-weight-bold text-primary mb-0"><i class="fas fa-arrow-right mr-1"></i>Premis Kiri (Pertanyaan)</h6>
                                            <button type="button" class="btn btn-xs btn-primary font-weight-bold" onclick="addPremisRow()">+ Tambah Premis</button>
                                        </div>
                                        <div id="containerPremis">
                                            <?php foreach ($jodohData['baris'] as $i => $itemKiri): ?>
                                                <div class="input-group mb-2 premis-item" data-index="<?= $i ?>">
                                                    <div class="input-group-prepend">
                                                        <span class="input-group-text font-weight-bold bg-light"><?= $i + 1 ?></span>
                                                    </div>
                                                    <input type="text" name="baris[]" class="form-control input-premis" value="<?= htmlspecialchars($itemKiri) ?>" placeholder="Teks atau URL Gambar Premis Kiri..." oninput="updateLineConnector()">
                                                    <div class="input-group-append">
                                                        <button type="button" class="btn btn-outline-danger" onclick="removePremisRow(this)"><i class="fas fa-times"></i></button>
                                                    </div>
                                                </div>
                                            <?php endforeach; ?>
                                        </div>
                                    </div>

                                    <!-- Kolom Kanan: Respon -->
                                    <div class="col-md-6">
                                        <div class="d-flex justify-content-between align-items-center mb-2">
                                            <h6 class="font-weight-bold text-success mb-0"><i class="fas fa-arrow-left mr-1"></i>Respon Kanan (Pilihan Pasangan)</h6>
                                            <button type="button" class="btn btn-xs btn-success font-weight-bold" onclick="addResponRow()">+ Tambah Respon</button>
                                        </div>
                                        <div id="containerRespon">
                                            <?php foreach ($jodohData['kolom'] as $j => $itemKanan): ?>
                                                <div class="input-group mb-2 respon-item" data-index="<?= $j ?>">
                                                    <div class="input-group-prepend">
                                                        <span class="input-group-text font-weight-bold bg-light"><?= chr(65 + $j) ?></span>
                                                    </div>
                                                    <input type="text" name="kolom[]" class="form-control input-respon" value="<?= htmlspecialchars($itemKanan) ?>" placeholder="Teks atau URL Gambar Respon Kanan..." oninput="updateLineConnector()">
                                                    <div class="input-group-append">
                                                        <button type="button" class="btn btn-outline-danger" onclick="removeResponRow(this)"><i class="fas fa-times"></i></button>
                                                    </div>
                                                </div>
                                            <?php endforeach; ?>
                                        </div>
                                    </div>
                                </div>

                                <!-- Line Connector Visualizer / Canvas -->
                                <div class="mt-4 border rounded p-3 bg-light">
                                    <div class="d-flex justify-content-between align-items-center mb-2">
                                        <h6 class="font-weight-bold text-dark mb-0">
                                            <i class="fas fa-draw-polygon mr-1"></i>Kunci Pasangan Garis (Line Matching Connector):
                                        </h6>
                                        <button type="button" class="btn btn-xs btn-outline-secondary" onclick="resetConnections()">
                                            <i class="fas fa-undo mr-1"></i>Reset Garis
                                        </button>
                                    </div>
                                    <p class="small text-muted mb-2">Klik bulatan pada kolom kiri lalu klik bulatan pada kolom kanan untuk membuat garis pasangan yang benar.</p>

                                    <!-- Interactive Line Drawing Area -->
                                    <div id="matchingBoard" style="position: relative; min-height: 220px; background: #ffffff; border: 1px dashed #ccc; border-radius: 6px; overflow: hidden;">
                                        <svg id="matchingSvg" style="position: absolute; top:0; left:0; width:100%; height:100%; pointer-events:none; z-index: 2;"></svg>
                                        <div class="d-flex justify-content-between p-3" style="position: relative; z-index: 3;">
                                            <div id="boardLeftNodes" class="d-flex flex-column" style="gap: 16px;"></div>
                                            <div id="boardRightNodes" class="d-flex flex-column" style="gap: 16px;"></div>
                                        </div>
                                    </div>

                                    <!-- Hidden storage for matching links -->
                                    <div id="linksHiddenInputs"></div>
                                </div>
                            </div>
                        </div>

                    <!-- TIPE 4: Isian Singkat -->
                    <?php elseif ($selectedJenis === 4): ?>
                        <div class="card card-outline card-secondary shadow-sm mb-4">
                            <div class="card-header bg-white py-2">
                                <h3 class="card-title font-weight-bold text-dark mb-0">
                                    <i class="fas fa-pencil-alt mr-1"></i>Kunci Jawaban Isian Singkat
                                </h3>
                            </div>
                            <div class="card-body">
                                <div class="form-group">
                                    <label class="font-weight-bold">Teks Jawaban Benar: <span class="text-danger">*</span></label>
                                    <input type="text" name="jawaban_isian" class="form-control form-control-lg font-weight-bold" value="<?= htmlspecialchars((string)($editSoal['jawaban'] ?? '')) ?>" placeholder="Ketik kata kunci jawaban singkat yang tepat..." required>
                                    <small class="form-text text-muted">
                                        Pemeriksaan jawaban siswa bersifat <em>case-insensitive</em> (huruf besar/kecil dianggap sama, serta spasi berlebih diabaikan).
                                    </small>
                                </div>
                            </div>
                        </div>

                    <!-- TIPE 5: Uraian / Essai -->
                    <?php elseif ($selectedJenis === 5): ?>
                        <div class="card card-outline card-dark shadow-sm mb-4">
                            <div class="card-header bg-white py-2">
                                <h3 class="card-title font-weight-bold text-dark mb-0">
                                    <i class="fas fa-align-left mr-1"></i>Rubrik & Pedoman Penilaian Soal Uraian (Essai)
                                </h3>
                            </div>
                            <div class="card-body">
                                <div class="form-group">
                                    <label class="font-weight-bold">Pedoman Penskoran / Kunci Uraian:</label>
                                    <textarea name="jawaban_esai" class="form-control summernote-opsi" rows="4" placeholder="Tuliskan pedoman jawaban, kata kunci wajib, atau kriteria penilaian esai..."><?= htmlspecialchars((string)($editSoal['jawaban'] ?? '')) ?></textarea>
                                    <small class="form-text text-muted">Pedoman ini akan ditampilkan kepada Guru / Korektor saat melakukan pemeriksaan hasil ujian siswa.</small>
                                </div>
                            </div>
                        </div>
                    <?php endif; ?>

                </div>

                <!-- Sidebar Meta & Controls -->
                <div class="col-lg-3 col-md-12">
                    <div class="card card-outline card-secondary shadow-sm mb-4">
                        <div class="card-header bg-white py-2">
                            <h3 class="card-title font-weight-bold text-dark mb-0">
                                <i class="fas fa-cog mr-1"></i>Pengaturan Soal
                            </h3>
                        </div>
                        <div class="card-body">
                            <div class="form-group mb-3">
                                <label class="font-weight-bold">Nomor Urut Soal:</label>
                                <input type="number" name="nomor_soal" class="form-control font-weight-bold text-center" value="<?= $nextNomor ?>" min="1" required>
                            </div>

                            <div class="form-group mb-3">
                                <label class="font-weight-bold">Bank Soal:</label>
                                <div class="p-2 bg-light rounded border small">
                                    <strong><?= htmlspecialchars($bank['bank_kode'] ?? '-') ?></strong><br>
                                    <?= htmlspecialchars($bank['nama_mapel'] ?? '-') ?><br>
                                    Tingkat: Kelas <?= (int)($bank['bank_level'] ?? 7) ?>
                                </div>
                            </div>

                            <hr>

                            <button type="submit" class="btn btn-success btn-block font-weight-bold py-2 shadow-sm">
                                <i class="fas fa-save mr-1"></i>Simpan Soal
                            </button>
                            <a href="/cbt/bank-soal/detail/<?= $idBank ?>" class="btn btn-outline-secondary btn-block mt-2">
                                <i class="fas fa-times mr-1"></i>Batal & Kembali
                            </a>
                        </div>
                    </div>
                </div>
            </div>
        </form>

    </div>
</section>

<!-- Dynamic Scripts for Summernote, KaTeX, Arabic & Line Connector -->
<script>
var activeConnections = <?= json_encode($jodohData['links'] ?? []) ?>;
var selectedLeftNode = null;

document.addEventListener('DOMContentLoaded', function() {
    // 1. Initialize Summernote with image upload handler
    if (typeof $ !== 'undefined' && $.fn.summernote) {
        $('#soal_editor').summernote({
            height: 220,
            placeholder: 'Tuliskan pertanyaan soal di sini...',
            toolbar: [
                ['style', ['style', 'bold', 'italic', 'underline', 'clear']],
                ['font', ['strikethrough', 'superscript', 'subscript']],
                ['fontsize', ['fontsize']],
                ['color', ['color']],
                ['para', ['ul', 'ol', 'paragraph']],
                ['table', ['table']],
                ['insert', ['link', 'picture', 'video']],
                ['view', ['fullscreen', 'codeview', 'help']]
            ],
            callbacks: {
                onImageUpload: function(files) {
                    uploadSummernoteImage(files[0], '#soal_editor');
                },
                onChange: function(contents) {
                    updateLatexPreview(contents);
                }
            }
        });

        $('.summernote-opsi').summernote({
            height: 100,
            toolbar: [
                ['style', ['bold', 'italic', 'underline', 'clear']],
                ['font', ['superscript', 'subscript']],
                ['color', ['color']],
                ['insert', ['picture', 'link']],
                ['view', ['codeview']]
            ],
            callbacks: {
                onImageUpload: function(files) {
                    uploadSummernoteImage(files[0], this);
                }
            }
        });

        // Trigger initial KaTeX preview
        updateLatexPreview($('#soal_editor').val());
    }

    // 2. Radio/Checkbox labels toggle
    document.querySelectorAll('input[name="jawaban_pg"]').forEach(function(radio) {
        radio.addEventListener('change', function() {
            document.querySelectorAll('.label-kunci').forEach(function(l) { l.classList.add('d-none'); });
            var checkedLabel = this.closest('.card-header').querySelector('.label-kunci');
            if (checkedLabel) checkedLabel.classList.remove('d-none');
        });
    });

    document.querySelectorAll('input[name="jawaban_pgk[]"]').forEach(function(cb) {
        cb.addEventListener('change', function() {
            var label = this.closest('.card-header').querySelector('.label-pgk-kunci');
            if (label) {
                if (this.checked) label.classList.remove('d-none');
                else label.classList.add('d-none');
            }
        });
    });

    // 3. Initialize Line Connector if Tipe 3 (Menjodohkan)
    <?php if ($selectedJenis === 3): ?>
        renderMatchingBoard();
    <?php endif; ?>
});

// Summernote AJAX image uploader
function uploadSummernoteImage(file, editorSelector) {
    var data = new FormData();
    data.append("image", file);
    $.ajax({
        url: "/cbt/bank-soal/upload-image",
        cache: false,
        contentType: false,
        processData: false,
        data: data,
        type: "POST",
        success: function(resp) {
            if (resp.status === 'success' && resp.url) {
                $(editorSelector).summernote('insertImage', resp.url);
            } else {
                alert(resp.message || 'Gagal mengunggah gambar.');
            }
        },
        error: function(err) {
            alert('Kesalahan server saat mengunggah gambar.');
        }
    });
}

// Live KaTeX Math Preview
function updateLatexPreview(html) {
    var previewEl = document.getElementById('katexPreviewContent');
    if (!previewEl) return;
    previewEl.innerHTML = html || '<em>Pratinjau kosong.</em>';
    if (typeof renderMathInElement !== 'undefined') {
        renderMathInElement(previewEl, {
            delimiters: [
                {left: '$$', right: '$$', display: true},
                {left: '$', right: '$', display: false},
                {left: '\\(', right: '\\)', display: false},
                {left: '\\[', right: '\\]', display: true}
            ],
            throwOnError: false
        });
    }
}

// Helper: Insert LaTeX template into editor
function insertLatexTemplate() {
    var formula = prompt("Masukkan rumus LaTeX (contoh: \\frac{a}{b} atau \\sqrt{x^2 + y^2}):", "\\frac{a}{b}");
    if (formula && typeof $ !== 'undefined') {
        $('#soal_editor').summernote('insertText', ' $' + formula + '$ ');
    }
}

// Helper: Toggle Arabic Calibri
function toggleArabicInput() {
    var editor = $('.note-editable');
    if (editor.hasClass('arabic-font')) {
        editor.removeClass('arabic-font').css({'font-family': '', 'direction': 'ltr', 'text-align': 'left'});
    } else {
        editor.addClass('arabic-font').css({'font-family': "'Arabic Calibri', Calibri, Tahoma, sans-serif", 'direction': 'rtl', 'text-align': 'right'});
    }
}

// ==========================================
// MENJODOHKAN (LINE CONNECTOR) LOGIC
// ==========================================
function addPremisRow() {
    var container = document.getElementById('containerPremis');
    var count = container.querySelectorAll('.premis-item').length;
    var div = document.createElement('div');
    div.className = 'input-group mb-2 premis-item';
    div.setAttribute('data-index', count);
    div.innerHTML = '<div class="input-group-prepend"><span class="input-group-text font-weight-bold bg-light">' + (count + 1) + '</span></div>' +
                    '<input type="text" name="baris[]" class="form-control input-premis" placeholder="Teks atau URL Gambar Premis Kiri..." oninput="updateLineConnector()">' +
                    '<div class="input-group-append"><button type="button" class="btn btn-outline-danger" onclick="removePremisRow(this)"><i class="fas fa-times"></i></button></div>';
    container.appendChild(div);
    renderMatchingBoard();
}

function removePremisRow(btn) {
    var row = btn.closest('.premis-item');
    if (document.querySelectorAll('.premis-item').length <= 1) {
        alert('Minimal harus ada 1 Premis Kiri.');
        return;
    }
    row.remove();
    reindexItems('#containerPremis', '.premis-item', false);
    renderMatchingBoard();
}

function addResponRow() {
    var container = document.getElementById('containerRespon');
    var count = container.querySelectorAll('.respon-item').length;
    var letter = String.fromCharCode(65 + count);
    var div = document.createElement('div');
    div.className = 'input-group mb-2 respon-item';
    div.setAttribute('data-index', count);
    div.innerHTML = '<div class="input-group-prepend"><span class="input-group-text font-weight-bold bg-light">' + letter + '</span></div>' +
                    '<input type="text" name="kolom[]" class="form-control input-respon" placeholder="Teks atau URL Gambar Respon Kanan..." oninput="updateLineConnector()">' +
                    '<div class="input-group-append"><button type="button" class="btn btn-outline-danger" onclick="removeResponRow(this)"><i class="fas fa-times"></i></button></div>';
    container.appendChild(div);
    renderMatchingBoard();
}

function removeResponRow(btn) {
    var row = btn.closest('.respon-item');
    if (document.querySelectorAll('.respon-item').length <= 1) {
        alert('Minimal harus ada 1 Respon Kanan.');
        return;
    }
    row.remove();
    reindexItems('#containerRespon', '.respon-item', true);
    renderMatchingBoard();
}

function reindexItems(parentSel, itemSel, isAlphabet) {
    var items = document.querySelectorAll(parentSel + ' ' + itemSel);
    items.forEach(function(item, idx) {
        item.setAttribute('data-index', idx);
        var span = item.querySelector('.input-group-text');
        if (span) span.innerText = isAlphabet ? String.fromCharCode(65 + idx) : (idx + 1);
    });
}

function updateLineConnector() {
    renderMatchingBoard();
}

function renderMatchingBoard() {
    var leftItems = document.querySelectorAll('#containerPremis .premis-item');
    var rightItems = document.querySelectorAll('#containerRespon .respon-item');

    var boardLeft = document.getElementById('boardLeftNodes');
    var boardRight = document.getElementById('boardRightNodes');
    if (!boardLeft || !boardRight) return;

    boardLeft.innerHTML = '';
    boardRight.innerHTML = '';

    leftItems.forEach(function(item, idx) {
        var val = item.querySelector('input').value || ('Premis ' + (idx + 1));
        var node = document.createElement('div');
        node.className = 'd-flex align-items-center';
        node.innerHTML = '<span class="badge badge-primary mr-2" style="cursor:pointer;" onclick="selectLeftNode(' + idx + ', this)">' + (idx + 1) + '. ' + val.substring(0, 25) + '</span>' +
                         '<div class="node-dot" data-left="' + idx + '" style="width:14px; height:14px; border-radius:50%; background:#007bff; cursor:pointer;" onclick="selectLeftNode(' + idx + ', this)"></div>';
        boardLeft.appendChild(node);
    });

    rightItems.forEach(function(item, idx) {
        var letter = String.fromCharCode(65 + idx);
        var val = item.querySelector('input').value || ('Respon ' + letter);
        var node = document.createElement('div');
        node.className = 'd-flex align-items-center justify-content-end';
        node.innerHTML = '<div class="node-dot mr-2" data-right="' + idx + '" style="width:14px; height:14px; border-radius:50%; background:#28a745; cursor:pointer;" onclick="connectRightNode(' + idx + ', this)"></div>' +
                         '<span class="badge badge-success" style="cursor:pointer;" onclick="connectRightNode(' + idx + ', this)">' + letter + '. ' + val.substring(0, 25) + '</span>';
        boardRight.appendChild(node);
    });

    drawLines();
    syncHiddenLinks();
}

function selectLeftNode(leftIdx, el) {
    selectedLeftNode = leftIdx;
    document.querySelectorAll('#boardLeftNodes .node-dot').forEach(function(d) {
        d.style.boxShadow = 'none';
    });
    var activeDot = document.querySelector('#boardLeftNodes .node-dot[data-left="' + leftIdx + '"]');
    if (activeDot) activeDot.style.boxShadow = '0 0 0 4px #ffc107';
}

function connectRightNode(rightIdx, el) {
    if (selectedLeftNode === null) {
        alert('Silakan klik bulatan pada kolom kiri (Premis) terlebih dahulu.');
        return;
    }

    var leftKey = String(selectedLeftNode);
    if (!activeConnections[leftKey]) {
        activeConnections[leftKey] = [];
    }

    var rightKey = String(rightIdx);
    var foundIndex = activeConnections[leftKey].indexOf(rightKey);
    if (foundIndex !== -1) {
        // Toggle remove connection
        activeConnections[leftKey].splice(foundIndex, 1);
        if (activeConnections[leftKey].length === 0) {
            delete activeConnections[leftKey];
        }
    } else {
        activeConnections[leftKey].push(rightKey);
    }

    selectedLeftNode = null;
    document.querySelectorAll('#boardLeftNodes .node-dot').forEach(function(d) {
        d.style.boxShadow = 'none';
    });

    drawLines();
    syncHiddenLinks();
}

function drawLines() {
    var svg = document.getElementById('matchingSvg');
    if (!svg) return;
    svg.innerHTML = '';

    var boardRect = document.getElementById('matchingBoard').getBoundingClientRect();

    for (var leftKey in activeConnections) {
        var targets = activeConnections[leftKey];
        if (!Array.isArray(targets)) continue;

        var leftDot = document.querySelector('#boardLeftNodes .node-dot[data-left="' + leftKey + '"]');
        if (!leftDot) continue;
        var leftRect = leftDot.getBoundingClientRect();
        var x1 = leftRect.left - boardRect.left + (leftRect.width / 2);
        var y1 = leftRect.top - boardRect.top + (leftRect.height / 2);

        targets.forEach(function(rightKey) {
            var rightDot = document.querySelector('#boardRightNodes .node-dot[data-right="' + rightKey + '"]');
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
}

function resetConnections() {
    activeConnections = {};
    selectedLeftNode = null;
    drawLines();
    syncHiddenLinks();
}

function syncHiddenLinks() {
    var container = document.getElementById('linksHiddenInputs');
    if (!container) return;
    container.innerHTML = '';

    for (var leftKey in activeConnections) {
        var targets = activeConnections[leftKey];
        if (!Array.isArray(targets)) continue;
        targets.forEach(function(rightKey) {
            var input = document.createElement('input');
            input.type = 'hidden';
            input.name = 'links[' + leftKey + '][]';
            input.value = rightKey;
            container.appendChild(input);
        });
    }
}
</script>
