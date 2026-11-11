<?php
$title = $title ?? 'Detail Bank Soal — DADO CBT';
$bank = $bank ?? [];
$questions = $questions ?? [];
$isLocked = $isLocked ?? false;
$role = $role ?? 'admin';

$idBank = (int)($bank['id_bank'] ?? 0);
$kelasNames = [];
if (!empty($bank['bank_kelas'])) {
    $rawKelas = is_string($bank['bank_kelas']) ? json_decode($bank['bank_kelas'], true) : $bank['bank_kelas'];
    if (is_array($rawKelas)) {
        $kelasNames = $rawKelas;
    }
}

// Counts per question type
$countPg = 0;
$countPgk = 0;
$countJodoh = 0;
$countIsian = 0;
$countEsai = 0;

foreach ($questions as $q) {
    $j = (int)($q['jenis'] ?? 1);
    if ($j === 1) $countPg++;
    elseif ($j === 2) $countPgk++;
    elseif ($j === 3) $countJodoh++;
    elseif ($j === 4) $countIsian++;
    elseif ($j === 5) $countEsai++;
}
$totalSoal = count($questions);
?>

<div class="content-header">
    <div class="container-fluid">
        <div class="row mb-2">
            <div class="col-sm-6">
                <h1 class="m-0 text-dark font-weight-bold">
                    <i class="fas fa-layer-group text-primary mr-2"></i>Bank Soal: <?= htmlspecialchars($bank['bank_kode'] ?? '-') ?>
                </h1>
            </div>
            <div class="col-sm-6">
                <ol class="breadcrumb float-sm-right">
                    <li class="breadcrumb-item"><a href="/dashboard">Beranda</a></li>
                    <li class="breadcrumb-item"><a href="/cbt/bank-soal">Bank Soal</a></li>
                    <li class="breadcrumb-item active"><?= htmlspecialchars($bank['bank_kode'] ?? '-') ?></li>
                </ol>
            </div>
        </div>
    </div>
</div>

<section class="content">
    <div class="container-fluid">

        <?php if ($flash = get_flash('success')): ?>
            <div class="alert alert-success alert-dismissible fade show" role="alert">
                <i class="fas fa-check-circle mr-2"></i><?= htmlspecialchars($flash) ?>
                <button type="button" class="close" data-dismiss="alert" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
        <?php endif; ?>

        <?php if ($flash = get_flash('error')): ?>
            <div class="alert alert-danger alert-dismissible fade show" role="alert">
                <i class="fas fa-exclamation-triangle mr-2"></i><?= htmlspecialchars($flash) ?>
                <button type="button" class="close" data-dismiss="alert" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
        <?php endif; ?>

        <?php if ($isLocked): ?>
            <div class="alert alert-warning">
                <i class="fas fa-lock mr-2"></i><strong>Peringatan Jadwal Aktif:</strong> Bank Soal ini sedang diujikan kepada siswa pada jadwal aktif. Perubahan struktur soal dapat mempengaruhi data nilai siswa.
            </div>
        <?php endif; ?>

        <!-- Bank Soal Meta & Composition Summary -->
        <div class="row">
            <div class="col-lg-8 col-md-12">
                <div class="card card-outline card-primary shadow-sm mb-4">
                    <div class="card-header bg-white py-3">
                        <div class="d-flex justify-content-between align-items-center">
                            <h3 class="card-title font-weight-bold text-primary mb-0">
                                <i class="fas fa-info-circle mr-1"></i>Informasi Bank Soal
                            </h3>
                            <div>
                                <a href="/cbt/bank-soal/edit/<?= $idBank ?>" class="btn btn-warning btn-sm font-weight-bold shadow-sm">
                                    <i class="fas fa-edit mr-1"></i>Edit Pengaturan
                                </a>
                                <a href="/cbt/bank-soal" class="btn btn-secondary btn-sm shadow-sm ml-1">
                                    <i class="fas fa-arrow-left mr-1"></i>Kembali
                                </a>
                            </div>
                        </div>
                    </div>
                    <div class="card-body">
                        <div class="row">
                            <div class="col-md-6">
                                <table class="table table-sm table-borderless">
                                    <tr>
                                        <th style="width: 140px;" class="text-muted">Kode Bank</th>
                                        <td><span class="badge badge-primary px-2 py-1 font-weight-bold" style="font-size: 1rem;"><?= htmlspecialchars($bank['bank_kode'] ?? '-') ?></span></td>
                                    </tr>
                                    <tr>
                                        <th class="text-muted">Mata Pelajaran</th>
                                        <td class="font-weight-bold text-dark"><?= htmlspecialchars($bank['nama_mapel'] ?? '-') ?> <span class="badge badge-light border">(<?= htmlspecialchars($bank['kode_mapel'] ?? '-') ?>)</span></td>
                                    </tr>
                                    <tr>
                                        <th class="text-muted">Guru Pengampu</th>
                                        <td><?= htmlspecialchars($bank['nama_guru'] ?? '-') ?></td>
                                    </tr>
                                    <tr>
                                        <th class="text-muted">Tingkat / Level</th>
                                        <td>Kelas <?= (int)($bank['bank_level'] ?? 7) ?></td>
                                    </tr>
                                </table>
                            </div>
                            <div class="col-md-6">
                                <table class="table table-sm table-borderless">
                                    <tr>
                                        <th style="width: 140px;" class="text-muted">Jenis Ujian</th>
                                        <td><span class="badge badge-info"><?= htmlspecialchars($bank['kode_jenis'] ?? ($bank['nama_jenis'] ?? 'Umum')) ?></span></td>
                                    </tr>
                                    <tr>
                                        <th class="text-muted">Opsi PG Default</th>
                                        <td><strong><?= (int)($bank['opsi'] ?? 4) ?> Pilihan</strong> (A - <?= chr(64 + (int)($bank['opsi'] ?? 4)) ?>)</td>
                                    </tr>
                                    <tr>
                                        <th class="text-muted">Status Bank</th>
                                        <td>
                                            <?php if ((int)($bank['status'] ?? 1) === 1): ?>
                                                <span class="badge badge-success"><i class="fas fa-check mr-1"></i>Aktif</span>
                                            <?php else: ?>
                                                <span class="badge badge-secondary">Nonaktif</span>
                                            <?php endif; ?>
                                        </td>
                                    </tr>
                                    <tr>
                                        <th class="text-muted">Status Soal</th>
                                        <td>
                                            <?php if ((int)($bank['status_soal'] ?? 0) === 1): ?>
                                                <span class="badge badge-primary"><i class="fas fa-lock mr-1"></i>Terkunci / Siap Ujian</span>
                                            <?php else: ?>
                                                <span class="badge badge-warning text-dark"><i class="fas fa-pencil-alt mr-1"></i>Draft / Masih Diedit</span>
                                            <?php endif; ?>
                                        </td>
                                    </tr>
                                </table>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- AKM Composition Stats Card -->
            <div class="col-lg-4 col-md-12">
                <div class="card card-outline card-success shadow-sm mb-4">
                    <div class="card-header bg-white py-3">
                        <h3 class="card-title font-weight-bold text-success mb-0">
                            <i class="fas fa-tasks mr-1"></i>Komposisi 5 Tipe AKM
                        </h3>
                    </div>
                    <div class="card-body p-0">
                        <table class="table table-striped table-sm mb-0">
                            <thead class="bg-light">
                                <tr>
                                    <th>Tipe Soal</th>
                                    <th class="text-center">Tersedia</th>
                                    <th class="text-center">Tampil</th>
                                    <th class="text-center">Bobot</th>
                                </tr>
                            </thead>
                            <tbody>
                                <tr>
                                    <td><span class="badge badge-primary">PG Tunggal</span></td>
                                    <td class="text-center font-weight-bold"><?= $countPg ?></td>
                                    <td class="text-center"><?= (int)($bank['tampil_pg'] ?? 0) ?></td>
                                    <td class="text-center"><?= (int)($bank['bobot_pg'] ?? 0) ?>%</td>
                                </tr>
                                <tr>
                                    <td><span class="badge badge-info">PG Kompleks</span></td>
                                    <td class="text-center font-weight-bold"><?= $countPgk ?></td>
                                    <td class="text-center"><?= (int)($bank['tampil_kompleks'] ?? 0) ?></td>
                                    <td class="text-center"><?= (int)($bank['bobot_kompleks'] ?? 0) ?>%</td>
                                </tr>
                                <tr>
                                    <td><span class="badge badge-warning text-dark">Menjodohkan</span></td>
                                    <td class="text-center font-weight-bold"><?= $countJodoh ?></td>
                                    <td class="text-center"><?= (int)($bank['tampil_jodohkan'] ?? 0) ?></td>
                                    <td class="text-center"><?= (int)($bank['bobot_jodohkan'] ?? 0) ?>%</td>
                                </tr>
                                <tr>
                                    <td><span class="badge badge-secondary">Isian Singkat</span></td>
                                    <td class="text-center font-weight-bold"><?= $countIsian ?></td>
                                    <td class="text-center"><?= (int)($bank['tampil_isian'] ?? 0) ?></td>
                                    <td class="text-center"><?= (int)($bank['bobot_isian'] ?? 0) ?>%</td>
                                </tr>
                                <tr>
                                    <td><span class="badge badge-dark">Uraian / Esai</span></td>
                                    <td class="text-center font-weight-bold"><?= $countEsai ?></td>
                                    <td class="text-center"><?= (int)($bank['tampil_esai'] ?? 0) ?></td>
                                    <td class="text-center"><?= (int)($bank['bobot_esai'] ?? 0) ?>%</td>
                                </tr>
                            </tbody>
                            <tfoot class="bg-light font-weight-bold">
                                <tr>
                                    <td>Total</td>
                                    <td class="text-center text-primary"><?= $totalSoal ?> Butir</td>
                                    <td class="text-center"><?= (int)($bank['tampil_pg'] ?? 0) + (int)($bank['tampil_kompleks'] ?? 0) + (int)($bank['tampil_jodohkan'] ?? 0) + (int)($bank['tampil_isian'] ?? 0) + (int)($bank['tampil_esai'] ?? 0) ?></td>
                                    <td class="text-center text-success"><?= (int)($bank['bobot_pg'] ?? 0) + (int)($bank['bobot_kompleks'] ?? 0) + (int)($bank['bobot_jodohkan'] ?? 0) + (int)($bank['bobot_isian'] ?? 0) + (int)($bank['bobot_esai'] ?? 0) ?>%</td>
                                </tr>
                            </tfoot>
                        </table>
                    </div>
                </div>
            </div>
        </div>

        <!-- Action Toolbar -->
        <div class="card shadow-sm mb-4">
            <div class="card-body py-2">
                <div class="d-flex flex-wrap justify-content-between align-items-center">
                    <div class="btn-group mr-2 my-1">
                        <button type="button" class="btn btn-primary font-weight-bold dropdown-toggle" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
                            <i class="fas fa-plus mr-1"></i> Tambah Butir Soal
                        </button>
                        <div class="dropdown-menu">
                            <a class="dropdown-item" href="/cbt/bank-soal/soal/<?= $idBank ?>?jenis=1">
                                <i class="fas fa-check-circle text-primary mr-2"></i>1. Pilihan Ganda Tunggal
                            </a>
                            <a class="dropdown-item" href="/cbt/bank-soal/soal/<?= $idBank ?>?jenis=2">
                                <i class="fas fa-check-double text-info mr-2"></i>2. Pilihan Ganda Kompleks
                            </a>
                            <a class="dropdown-item" href="/cbt/bank-soal/soal/<?= $idBank ?>?jenis=3">
                                <i class="fas fa-bezier-curve text-warning mr-2"></i>3. Menjodohkan / Matching
                            </a>
                            <a class="dropdown-item" href="/cbt/bank-soal/soal/<?= $idBank ?>?jenis=4">
                                <i class="fas fa-pencil-alt text-secondary mr-2"></i>4. Isian Singkat
                            </a>
                            <a class="dropdown-item" href="/cbt/bank-soal/soal/<?= $idBank ?>?jenis=5">
                                <i class="fas fa-align-left text-dark mr-2"></i>5. Uraian / Essai
                            </a>
                        </div>
                    </div>

                    <div class="my-1">
                        <button type="button" class="btn btn-success font-weight-bold shadow-sm mr-1" data-toggle="modal" data-target="#modalImportWord">
                            <i class="fas fa-file-word mr-1"></i>Import Word (.docx)
                        </button>
                        <button type="button" class="btn btn-info font-weight-bold shadow-sm mr-1" data-toggle="modal" data-target="#modalImportExcel">
                            <i class="fas fa-file-excel mr-1"></i>Import Excel (.csv/.xlsx)
                        </button>
                        <a href="/cbt/bank-soal/template-word" class="btn btn-outline-success font-weight-bold shadow-sm mr-1" title="Unduh Format Word">
                            <i class="fas fa-download mr-1"></i>Template Word (.docx)
                        </a>
                        <a href="/cbt/bank-soal/template-excel" class="btn btn-outline-info font-weight-bold shadow-sm" title="Unduh Format Excel/CSV">
                            <i class="fas fa-download mr-1"></i>Template Excel (.csv)
                        </a>
                    </div>
                </div>
            </div>
        </div>

        <!-- Question List -->
        <div class="card card-outline card-secondary shadow-sm mb-5">
            <div class="card-header bg-white py-3">
                <h3 class="card-title font-weight-bold text-dark mb-0">
                    <i class="fas fa-list-ol mr-2"></i>Daftar Butir Soal (<?= $totalSoal ?>)
                </h3>
            </div>
            <div class="card-body p-0">
                <?php if (empty($questions)): ?>
                    <div class="text-center py-5">
                        <i class="fas fa-file-alt fa-3x text-muted mb-3"></i>
                        <h5 class="text-muted">Belum ada butir soal di dalam Bank Soal ini.</h5>
                        <p class="text-muted">Klik tombol <strong>Tambah Butir Soal</strong> atau lakukan <strong>Import Word / Excel</strong> untuk mengisi soal ujian.</p>
                        <a href="/cbt/bank-soal/soal/<?= $idBank ?>?jenis=1" class="btn btn-primary mt-2">
                            <i class="fas fa-plus mr-1"></i>Buat Soal Pertama
                        </a>
                    </div>
                <?php else: ?>
                    <div class="table-responsive">
                        <table class="table table-hover align-middle mb-0">
                            <thead class="bg-light">
                                <tr>
                                    <th style="width: 60px;" class="text-center">No</th>
                                    <th style="width: 140px;">Tipe AKM</th>
                                    <th>Konten Pertanyaan & Opsi / Kunci</th>
                                    <th style="width: 130px;" class="text-center">Aksi</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach ($questions as $idx => $q): 
                                    $jenisQ = (int)($q['jenis'] ?? 1);
                                    $soalId = (int)$q['id_soal'];
                                ?>
                                    <tr>
                                        <td class="text-center font-weight-bold text-muted"><?= (int)($q['nomor_soal'] ?? ($idx + 1)) ?></td>
                                        <td>
                                            <?php if ($jenisQ === 1): ?>
                                                <span class="badge badge-primary px-2 py-1"><i class="fas fa-check-circle mr-1"></i>PG Tunggal</span>
                                            <?php elseif ($jenisQ === 2): ?>
                                                <span class="badge badge-info px-2 py-1"><i class="fas fa-check-double mr-1"></i>PG Kompleks</span>
                                            <?php elseif ($jenisQ === 3): ?>
                                                <span class="badge badge-warning text-dark px-2 py-1"><i class="fas fa-bezier-curve mr-1"></i>Menjodohkan</span>
                                            <?php elseif ($jenisQ === 4): ?>
                                                <span class="badge badge-secondary px-2 py-1"><i class="fas fa-pencil-alt mr-1"></i>Isian Singkat</span>
                                            <?php elseif ($jenisQ === 5): ?>
                                                <span class="badge badge-dark px-2 py-1"><i class="fas fa-align-left mr-1"></i>Uraian / Esai</span>
                                            <?php endif; ?>
                                        </td>
                                        <td>
                                            <!-- Render Question Text with KaTeX and Arabic support -->
                                            <div class="question-content mb-2 p-2 bg-light rounded border" style="max-height: 160px; overflow-y: auto;">
                                                <?= $q['soal'] ?>
                                            </div>

                                            <!-- Render Answer / Options Preview -->
                                            <div class="options-preview small">
                                                <?php if ($jenisQ === 1): ?>
                                                    <div class="text-muted">
                                                        <span class="badge badge-success mr-1">Kunci: <?= htmlspecialchars((string)($q['jawaban'] ?? 'A')) ?></span>
                                                        <strong>Opsi A:</strong> <?= strip_tags($q['opsi_a'] ?? '-') ?> | 
                                                        <strong>Opsi B:</strong> <?= strip_tags($q['opsi_b'] ?? '-') ?> | 
                                                        <strong>Opsi C:</strong> <?= strip_tags($q['opsi_c'] ?? '-') ?>
                                                        <?php if (!empty($q['opsi_d'])): ?> | <strong>Opsi D:</strong> <?= strip_tags($q['opsi_d']) ?><?php endif; ?>
                                                        <?php if (!empty($q['opsi_e'])): ?> | <strong>Opsi E:</strong> <?= strip_tags($q['opsi_e']) ?><?php endif; ?>
                                                    </div>
                                                <?php elseif ($jenisQ === 2): ?>
                                                    <div class="text-muted">
                                                        <?php
                                                        $keys = is_string($q['jawaban']) ? json_decode($q['jawaban'], true) : $q['jawaban'];
                                                        $keyStr = is_array($keys) ? implode(', ', $keys) : (string)$q['jawaban'];
                                                        ?>
                                                        <span class="badge badge-info mr-1">Kunci Multi: [<?= htmlspecialchars($keyStr) ?>]</span>
                                                        <strong>Opsi A:</strong> <?= strip_tags($q['opsi_a'] ?? '-') ?> | 
                                                        <strong>Opsi B:</strong> <?= strip_tags($q['opsi_b'] ?? '-') ?> | 
                                                        <strong>Opsi C:</strong> <?= strip_tags($q['opsi_c'] ?? '-') ?>
                                                        <?php if (!empty($q['opsi_d'])): ?> | <strong>Opsi D:</strong> <?= strip_tags($q['opsi_d']) ?><?php endif; ?>
                                                        <?php if (!empty($q['opsi_e'])): ?> | <strong>Opsi E:</strong> <?= strip_tags($q['opsi_e']) ?><?php endif; ?>
                                                    </div>
                                                <?php elseif ($jenisQ === 3): ?>
                                                    <div class="text-muted">
                                                        <span class="badge badge-warning text-dark mr-1">Menjodohkan</span>
                                                        <?php
                                                        $jodohData = is_string($q['jawaban']) ? json_decode($q['jawaban'], true) : $q['jawaban'];
                                                        $barisCount = isset($jodohData['baris']) ? count($jodohData['baris']) : 0;
                                                        $kolomCount = isset($jodohData['kolom']) ? count($jodohData['kolom']) : 0;
                                                        ?>
                                                        <em><?= $barisCount ?> Premis Kiri &harr; <?= $kolomCount ?> Respon Kanan</em>
                                                    </div>
                                                <?php elseif ($jenisQ === 4): ?>
                                                    <div class="text-muted">
                                                        <span class="badge badge-secondary mr-1">Kunci Isian:</span>
                                                        <code><?= htmlspecialchars((string)($q['jawaban'] ?? '')) ?></code>
                                                    </div>
                                                <?php elseif ($jenisQ === 5): ?>
                                                    <div class="text-muted">
                                                        <span class="badge badge-dark mr-1">Pedoman Esai:</span>
                                                        <em><?= htmlspecialchars(mb_strimwidth((string)($q['jawaban'] ?? ''), 0, 90, '...')) ?></em>
                                                    </div>
                                                <?php endif; ?>
                                            </div>
                                        </td>
                                        <td class="text-center">
                                            <a href="/cbt/bank-soal/soal/<?= $idBank ?>?soal_id=<?= $soalId ?>&jenis=<?= $jenisQ ?>" class="btn btn-sm btn-info shadow-sm" title="Edit Soal">
                                                <i class="fas fa-pencil-alt"></i>
                                            </a>
                                            <button type="button" class="btn btn-sm btn-danger shadow-sm ml-1" title="Hapus Soal" onclick="hapusSoal(<?= $idBank ?>, <?= $soalId ?>, <?= (int)($q['nomor_soal'] ?? ($idx + 1)) ?>)">
                                                <i class="fas fa-trash"></i>
                                            </button>
                                        </td>
                                    </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                    </div>
                <?php endif; ?>
            </div>
        </div>

    </div>
</section>

<!-- Modal Import Word (.docx) -->
<div class="modal fade" id="modalImportWord" tabindex="-1" role="dialog" aria-labelledby="modalImportWordLabel" aria-hidden="true">
    <div class="modal-dialog modal-lg" role="document">
        <form action="/cbt/bank-soal/import-word/<?= $idBank ?>" method="POST" enctype="multipart/form-data">
            <div class="modal-content">
                <div class="modal-header bg-success text-white">
                    <h5 class="modal-title font-weight-bold" id="modalImportWordLabel">
                        <i class="fas fa-file-word mr-2"></i>Import Soal dari Microsoft Word (.docx)
                    </h5>
                    <button type="button" class="close text-white" data-dismiss="modal" aria-label="Close">
                        <span aria-hidden="true">&times;</span>
                    </button>
                </div>
                <div class="modal-body">
                    <div class="callout callout-success">
                        <h6 class="font-weight-bold text-success"><i class="fas fa-info-circle mr-1"></i>Format Resmi Template Word 5 Tipe AKM:</h6>
                        <ul class="mb-1 pl-3 small">
                            <li>Format file harus <strong>.docx</strong> (Microsoft Word 2007 ke atas).</li>
                            <li><strong>Gambar di dalam soal atau jawaban:</strong> Diekstrak secara otomatis dari file Word dan disimpan permanen ke server.</li>
                            <li><strong>Tipe 1 (PG Tunggal):</strong> Tabel 6 kolom (NO, SOAL, JENIS=1, OPSI, JAWABAN, KUNCI diberi tanda <code>v</code> pada baris opsi yang benar).</li>
                            <li><strong>Tipe 2 (PG Kompleks):</strong> Tabel 6 kolom (NO, SOAL, JENIS=2, OPSI, JAWABAN, KUNCI diberi tanda <code>v</code> pada beberapa opsi yang benar).</li>
                            <li><strong>Tipe 3 (Menjodohkan):</strong> Tabel 9 kolom (NO, SOAL, JENIS=3, KODE BARIS, NAMA BARIS, KODE KOLOM, NAMA KOLOM, KODE BARIS, KODE KOLOM kunci sambungan).</li>
                            <li><strong>Tipe 4 (Isian Singkat):</strong> Tabel 4 kolom (NO, SOAL, JENIS=4, JAWABAN).</li>
                            <li><strong>Tipe 5 (Uraian / Essai):</strong> Tabel 4 kolom (NO, SOAL, JENIS=5, JAWABAN pedoman penskoran).</li>
                            <li>Mendukung penuh rumus matematika <strong>LaTeX</strong> (<code>$...$</code>) dan huruf <strong>Arab</strong>.</li>
                        </ul>
                    </div>
                    <div class="form-group mt-3">
                        <label class="font-weight-bold">Pilih File Word (.docx): <span class="text-danger">*</span></label>
                        <div class="custom-file">
                            <input type="file" name="file_word" class="custom-file-input" id="file_word" accept=".docx" required>
                            <label class="custom-file-label" for="file_word">Pilih file .docx...</label>
                        </div>
                    </div>
                </div>
                <div class="modal-footer">
                    <a href="/cbt/bank-soal/template-word" class="btn btn-outline-success mr-auto font-weight-bold">
                        <i class="fas fa-download mr-1"></i>Unduh Template Word (.docx)
                    </a>
                    <button type="button" class="btn btn-secondary" data-dismiss="modal">Batal</button>
                    <button type="submit" class="btn btn-success font-weight-bold">
                        <i class="fas fa-cloud-upload-alt mr-1"></i>Mulai Import Word
                    </button>
                </div>
            </div>
        </form>
    </div>
</div>

<!-- Modal Import Excel (.csv/.xlsx) -->
<div class="modal fade" id="modalImportExcel" tabindex="-1" role="dialog" aria-labelledby="modalImportExcelLabel" aria-hidden="true">
    <div class="modal-dialog modal-lg" role="document">
        <form action="/cbt/bank-soal/import-excel/<?= $idBank ?>" method="POST" enctype="multipart/form-data">
            <div class="modal-content">
                <div class="modal-header bg-info text-white">
                    <h5 class="modal-title font-weight-bold" id="modalImportExcelLabel">
                        <i class="fas fa-file-excel mr-2"></i>Import Soal dari Excel (.xlsx) / CSV
                    </h5>
                    <button type="button" class="close text-white" data-dismiss="modal" aria-label="Close">
                        <span aria-hidden="true">&times;</span>
                    </button>
                </div>
                <div class="modal-body">
                    <div class="callout callout-info">
                        <h6 class="font-weight-bold text-info"><i class="fas fa-info-circle mr-1"></i>Petunjuk Import Excel / CSV:</h6>
                        <ul class="mb-1 pl-3 small">
                            <li>Mendukung file berformat <strong>.xlsx</strong> (Microsoft Excel Modern) dan <strong>.csv</strong> (UTF-8).</li>
                            <li>Format tabel sama persis dengan tabel Word:
                                <ul>
                                    <li><strong>PG Tunggal & Kompleks (Jenis 1 & 2):</strong> 6 kolom (NO, SOAL, JENIS, OPSI, JAWABAN, KUNCI).</li>
                                    <li><strong>Menjodohkan (Jenis 3):</strong> 9 kolom (NO, SOAL, JENIS, KODE BARIS, NAMA BARIS, KODE KOLOM, NAMA KOLOM, KODE BARIS, KODE KOLOM).</li>
                                    <li><strong>Isian & Esai (Jenis 4 & 5):</strong> 4 kolom (NO, SOAL, JENIS, JAWABAN).</li>
                                </ul>
                            </li>
                        </ul>
                    </div>
                    <div class="form-group mt-3">
                        <label class="font-weight-bold">Pilih File Excel / CSV: <span class="text-danger">*</span></label>
                        <div class="custom-file">
                            <input type="file" name="file_excel" class="custom-file-input" id="file_excel" accept=".csv, .xlsx, .xls" required>
                            <label class="custom-file-label" for="file_excel">Pilih file .xlsx atau .csv...</label>
                        </div>
                    </div>
                </div>
                <div class="modal-footer">
                    <a href="/cbt/bank-soal/template-excel" class="btn btn-outline-info mr-auto font-weight-bold">
                        <i class="fas fa-download mr-1"></i>Unduh Template Excel (.csv)
                    </a>
                    <button type="button" class="btn btn-secondary" data-dismiss="modal">Batal</button>
                    <button type="submit" class="btn btn-info font-weight-bold">
                        <i class="fas fa-cloud-upload-alt mr-1"></i>Mulai Import Excel
                    </button>
                </div>
            </div>
        </form>
    </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', function() {
    // Custom file input label update
    if (typeof bsCustomFileInput !== 'undefined') {
        bsCustomFileInput.init();
    } else {
        document.querySelectorAll('.custom-file-input').forEach(function(input) {
            input.addEventListener('change', function(e) {
                var fileName = e.target.files[0] ? e.target.files[0].name : 'Pilih file...';
                var nextSibling = e.target.nextElementSibling;
                if (nextSibling) nextSibling.innerText = fileName;
            });
        });
    }

    // Auto-render KaTeX math equations in question preview
    if (typeof renderMathInElement !== 'undefined') {
        document.querySelectorAll('.question-content').forEach(function(el) {
            renderMathInElement(el, {
                delimiters: [
                    {left: '$$', right: '$$', display: true},
                    {left: '$', right: '$', display: false},
                    {left: '\\(', right: '\\)', display: false},
                    {left: '\\[', right: '\\]', display: true}
                ],
                throwOnError: false
            });
        });
    }
});

function hapusSoal(bankId, soalId, noSoal) {
    if (confirm('Apakah Anda yakin ingin menghapus butir soal nomor ' + noSoal + '? Tindakan ini tidak dapat dibatalkan.')) {
        window.location.href = '/cbt/bank-soal/soal/' + bankId + '/delete/' + soalId;
    }
}
</script>
