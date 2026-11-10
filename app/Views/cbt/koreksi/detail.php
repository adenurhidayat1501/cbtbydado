<?php
$title = $title ?? 'Koreksi Lembar Jawaban Siswa — DADO CBT';
$jadwal = $jadwal ?? [];
$siswa = $siswa ?? [];
$questions = $questions ?? [];
$nilai = $nilai ?? [];

$idJadwal = (int)($jadwal['id_jadwal'] ?? 0);
$idSiswa = (int)($siswa['id_siswa'] ?? 0);
?>

<div class="content-header">
    <div class="container-fluid">
        <div class="row mb-2">
            <div class="col-sm-6">
                <h1 class="m-0 text-dark font-weight-bold">
                    <i class="fas fa-file-signature text-primary mr-2"></i>Koreksi Lembar Siswa: <?= htmlspecialchars($siswa['nama'] ?? 'Siswa') ?>
                </h1>
            </div>
            <div class="col-sm-6">
                <ol class="breadcrumb float-sm-right">
                    <li class="breadcrumb-item"><a href="/dashboard">Beranda</a></li>
                    <li class="breadcrumb-item"><a href="/cbt/koreksi">Koreksi</a></li>
                    <li class="breadcrumb-item"><a href="/cbt/koreksi/peserta/<?= $idJadwal ?>">Daftar Peserta</a></li>
                    <li class="breadcrumb-item active">Lembar Koreksi</li>
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

        <!-- Student & Grade Summary Banner -->
        <div class="card card-outline card-primary shadow-sm mb-4">
            <div class="card-body py-3">
                <div class="row align-items-center">
                    <div class="col-md-7">
                        <div class="d-flex align-items-center">
                            <img src="<?= !empty($siswa['foto']) ? upload_url('foto_siswa/' . $siswa['foto']) : asset('img/default-avatar.png') ?>" 
                                 class="rounded-circle mr-3 border" style="width: 50px; height: 50px; object-fit: cover;">
                            <div>
                                <h5 class="font-weight-bold text-dark mb-0"><?= htmlspecialchars($siswa['nama'] ?? '-') ?></h5>
                                <div class="small text-muted">
                                    NIS: <?= htmlspecialchars($siswa['nis'] ?? '-') ?> | Ujian: <?= htmlspecialchars($jadwal['nama_mapel'] ?? '-') ?> (<?= htmlspecialchars($jadwal['bank_kode'] ?? '-') ?>)
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-5 text-md-right mt-3 mt-md-0">
                        <span class="text-muted mr-2">Total Nilai Akhir:</span>
                        <span class="badge badge-primary px-3 py-2 font-weight-bold" style="font-size: 1.4rem;">
                            <?= number_format((float)($nilai['total_nilai'] ?? 0), 2) ?>
                        </span>
                        <a href="/cbt/koreksi/peserta/<?= $idJadwal ?>" class="btn btn-secondary btn-sm ml-2">
                            <i class="fas fa-arrow-left mr-1"></i>Kembali
                        </a>
                    </div>
                </div>
            </div>
        </div>

        <!-- Question Correction List Form -->
        <form action="/cbt/koreksi/save/<?= $idJadwal ?>/<?= $idSiswa ?>" method="POST">
            <div class="card card-outline card-secondary shadow-sm mb-5">
                <div class="card-header bg-white py-3 d-flex justify-content-between align-items-center">
                    <h5 class="card-title font-weight-bold text-dark mb-0">
                        <i class="fas fa-tasks mr-2 text-primary"></i>Lembar Soal & Jawaban Peserta
                    </h5>
                    <button type="submit" class="btn btn-success font-weight-bold shadow-sm px-4">
                        <i class="fas fa-save mr-1"></i>Simpan Hasil Koreksi
                    </button>
                </div>
                <div class="card-body p-3">
                    <?php foreach ($questions as $idx => $q): 
                        $no = (int)$q['no_soal_alias'];
                        $jenis = (int)$q['jenis_soal'];
                        $idSoalSiswa = (int)$q['id_soal_siswa'];
                        $ans = $q['jawaban_siswa'];
                        $key = $q['jawaban_benar'];
                        $point = (float)($q['point_soal'] ?? 0);
                        $koreksiPoint = (float)($q['nilai_koreksi'] ?? 0);
                    ?>
                        <div class="card card-light mb-4 border">
                            <div class="card-header py-2 bg-light d-flex justify-content-between align-items-center">
                                <div>
                                    <span class="badge badge-primary font-weight-bold px-2 py-1 mr-2">No. <?= $no ?></span>
                                    <span class="badge badge-secondary">
                                        <?php if ($jenis === 1): ?>PG Tunggal
                                        <?php elseif ($jenis === 2): ?>PG Kompleks
                                        <?php elseif ($jenis === 3): ?>Menjodohkan
                                        <?php elseif ($jenis === 4): ?>Isian Singkat
                                        <?php elseif ($jenis === 5): ?>Uraian / Essai
                                        <?php endif; ?>
                                    </span>
                                </div>
                                <div>
                                    <?php if ($jenis !== 5): ?>
                                        <span class="badge badge-<?= $point > 0 ? 'success' : 'danger' ?> px-2 py-1">
                                            Poin Otomatis: <?= number_format($point, 2) ?>
                                        </span>
                                    <?php endif; ?>
                                </div>
                            </div>
                            <div class="card-body p-3">
                                <!-- Pertanyaan Soal (Image paths rendered cleanly) -->
                                <div class="mb-3 p-3 bg-white border rounded">
                                    <label class="font-weight-bold text-muted small d-block mb-1">Pertanyaan:</label>
                                    <div class="question-content"><?= $q['soal'] ?></div>
                                </div>

                                <!-- Response & Keys Preview -->
                                <div class="row">
                                    <div class="col-md-6 mb-2">
                                        <div class="p-3 bg-light border rounded h-100">
                                            <label class="font-weight-bold text-primary small d-block mb-1"><i class="fas fa-user-edit mr-1"></i>Jawaban Siswa:</label>
                                            
                                            <?php if ($jenis === 1): ?>
                                                <div class="font-weight-bold" style="font-size: 1.2rem;">
                                                    <?= !empty($ans) ? htmlspecialchars((string)$ans) : '<em class="text-muted">Tidak dijawab</em>' ?>
                                                </div>
                                            <?php elseif ($jenis === 2): 
                                                $arrAns = is_string($ans) ? json_decode($ans, true) : (array)$ans;
                                            ?>
                                                <div class="font-weight-bold">
                                                    <?= !empty($arrAns) ? implode(', ', $arrAns) : '<em class="text-muted">Tidak dijawab</em>' ?>
                                                </div>
                                            <?php elseif ($jenis === 3): 
                                                $jodoh = is_string($ans) ? json_decode($ans, true) : $ans;
                                                $links = $jodoh['links'] ?? [];
                                            ?>
                                                <div class="small">
                                                    <?php if (empty($links)): ?>
                                                        <em class="text-muted">Tidak ada garis yang dihubungkan</em>
                                                    <?php else: ?>
                                                        <ul class="mb-0 pl-3">
                                                            <?php foreach ($links as $from => $toArr): ?>
                                                                <li>Premis <?= (int)$from + 1 ?> &rarr; Respon <?= is_array($toArr) ? implode(', ', array_map(function($x){ return chr(65 + (int)$x); }, $toArr)) : '-' ?></li>
                                                            <?php endforeach; ?>
                                                        </ul>
                                                    <?php endif; ?>
                                                </div>
                                            <?php elseif ($jenis === 4): ?>
                                                <div class="font-weight-bold">
                                                    <code><?= !empty($ans) ? htmlspecialchars((string)$ans) : '-' ?></code>
                                                </div>
                                            <?php elseif ($jenis === 5): ?>
                                                <div class="p-2 bg-white border rounded small" style="max-height: 180px; overflow-y: auto;">
                                                    <?= !empty($ans) ? $ans : '<em class="text-muted">Jawaban esai kosong</em>' ?>
                                                </div>
                                            <?php endif; ?>
                                        </div>
                                    </div>

                                    <div class="col-md-6 mb-2">
                                        <div class="p-3 bg-light border rounded h-100">
                                            <label class="font-weight-bold text-success small d-block mb-1"><i class="fas fa-key mr-1"></i>Kunci Jawaban / Pedoman:</label>
                                            
                                            <?php if ($jenis === 1): ?>
                                                <div class="font-weight-bold text-success" style="font-size: 1.2rem;">
                                                    <?= htmlspecialchars((string)$key) ?>
                                                </div>
                                            <?php elseif ($jenis === 2): 
                                                $arrKey = is_string($key) ? json_decode($key, true) : (array)$key;
                                            ?>
                                                <div class="font-weight-bold text-success">
                                                    <?= is_array($arrKey) ? implode(', ', $arrKey) : '-' ?>
                                                </div>
                                            <?php elseif ($jenis === 3): 
                                                $jodohKunci = is_string($key) ? json_decode($key, true) : $key;
                                                $kunciLinks = $jodohKunci['links'] ?? [];
                                            ?>
                                                <div class="small text-success">
                                                    <ul class="mb-0 pl-3">
                                                        <?php foreach ($kunciLinks as $from => $toArr): ?>
                                                            <li>Premis <?= (int)$from + 1 ?> &rarr; Respon <?= is_array($toArr) ? implode(', ', array_map(function($x){ return chr(65 + (int)$x); }, $toArr)) : '-' ?></li>
                                                        <?php endforeach; ?>
                                                    </ul>
                                                </div>
                                            <?php elseif ($jenis === 4): ?>
                                                <div class="font-weight-bold text-success">
                                                    <code><?= htmlspecialchars((string)$key) ?></code>
                                                </div>
                                            <?php elseif ($jenis === 5): ?>
                                                <div class="p-2 bg-white border rounded small text-muted" style="max-height: 180px; overflow-y: auto;">
                                                    <?= !empty($key) ? $key : '<em>Pedoman penilaian tidak tersedia</em>' ?>
                                                </div>
                                            <?php endif; ?>
                                        </div>
                                    </div>
                                </div>

                                <!-- Manual Essay Score Input -->
                                <?php if ($jenis === 5): ?>
                                    <div class="mt-3 p-3 bg-white border border-warning rounded">
                                        <div class="row align-items-center">
                                            <div class="col-md-8">
                                                <label class="font-weight-bold text-dark mb-0">
                                                    <i class="fas fa-star text-warning mr-1"></i>Input Nilai Koreksi Esai (0 - 100):
                                                </label>
                                                <small class="text-muted d-block">Masukkan nilai penilaian esai untuk butir ini sesuai rubrik penskoran.</small>
                                            </div>
                                            <div class="col-md-4 text-md-right mt-2 mt-md-0">
                                                <div class="input-group" style="max-width: 160px; margin-left: auto;">
                                                    <input type="number" step="0.5" min="0" max="100" name="nilai_koreksi[<?= $idSoalSiswa ?>]" 
                                                           class="form-control font-weight-bold text-center text-primary" 
                                                           value="<?= $koreksiPoint ?>" required>
                                                    <div class="input-group-append">
                                                        <span class="input-group-text bg-light font-weight-bold">Poin</span>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                <?php endif; ?>

                            </div>
                        </div>
                    <?php endforeach; ?>
                </div>

                <div class="card-footer bg-light py-3 d-flex justify-content-between">
                    <a href="/cbt/koreksi/peserta/<?= $idJadwal ?>" class="btn btn-secondary shadow-sm">
                        <i class="fas fa-arrow-left mr-1"></i>Kembali ke Daftar Peserta
                    </a>
                    <button type="submit" class="btn btn-success font-weight-bold shadow-sm px-5">
                        <i class="fas fa-save mr-1"></i>Simpan Nilai Koreksi
                    </button>
                </div>
            </div>
        </form>

    </div>
</section>

<script>
document.addEventListener('DOMContentLoaded', function() {
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
</script>

