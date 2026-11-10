<?php
$title = $title ?? 'Analisis Butir Soal — DADO CBT';
$jadwal = $jadwal ?? [];
$analysis = $analysis ?? [];
$totalPeserta = $totalPeserta ?? 0;
$idJadwal = (int)($jadwal['id_jadwal'] ?? 0);
?>

<div class="content-header">
    <div class="container-fluid">
        <div class="row mb-2">
            <div class="col-sm-6">
                <h1 class="m-0 text-dark font-weight-bold">
                    <i class="fas fa-microscope text-primary mr-2"></i>Analisis Butir Soal CBT
                </h1>
            </div>
            <div class="col-sm-6">
                <ol class="breadcrumb float-sm-right">
                    <li class="breadcrumb-item"><a href="/dashboard">Beranda</a></li>
                    <li class="breadcrumb-item"><a href="/cbt/rekap-nilai?jadwal_id=<?= $idJadwal ?>">Rekap Nilai</a></li>
                    <li class="breadcrumb-item active">Analisis Soal</li>
                </ol>
            </div>
        </div>
    </div>
</div>

<section class="content">
    <div class="container-fluid">

        <!-- Info Banner -->
        <div class="card card-outline card-info shadow-sm mb-4">
            <div class="card-body py-3">
                <div class="d-flex justify-content-between align-items-center">
                    <div>
                        <h5 class="font-weight-bold text-dark mb-1">
                            <?= htmlspecialchars($jadwal['nama_mapel'] ?? '-') ?>
                            <span class="badge badge-info ml-1"><?= htmlspecialchars($jadwal['bank_kode'] ?? '-') ?></span>
                        </h5>
                        <div class="small text-muted">
                            Total Responden Siswa: <strong><?= $totalPeserta ?></strong> orang | Guru Pengampu: <?= htmlspecialchars($jadwal['nama_guru'] ?? '-') ?>
                        </div>
                    </div>
                    <div>
                        <a href="/cbt/rekap-nilai?jadwal_id=<?= $idJadwal ?>" class="btn btn-secondary btn-sm shadow-sm">
                            <i class="fas fa-arrow-left mr-1"></i>Kembali ke Rekap Nilai
                        </a>
                    </div>
                </div>
            </div>
        </div>

        <!-- Analysis Table -->
        <div class="card card-outline card-primary shadow-sm mb-5">
            <div class="card-header bg-white py-3">
                <h5 class="card-title font-weight-bold text-dark mb-0">
                    <i class="fas fa-chart-bar mr-2 text-primary"></i>Statistik Tingkat Ketercapaian & Daya Pembeda Butir Soal
                </h5>
            </div>
            <div class="card-body p-0">
                <div class="table-responsive">
                    <table class="table table-bordered table-striped table-hover align-middle mb-0">
                        <thead class="bg-light">
                            <tr>
                                <th style="width: 50px;" class="text-center">No</th>
                                <th style="width: 120px;" class="text-center">Tipe AKM</th>
                                <th>Konten Cuplikan Soal</th>
                                <th style="width: 100px;" class="text-center">Responden</th>
                                <th style="width: 100px;" class="text-center">Jawaban Benar</th>
                                <th style="width: 110px;" class="text-center">Rata-rata Poin</th>
                                <th style="width: 120px;" class="text-center">Ketercapaian</th>
                                <th style="width: 120px;" class="text-center">Tingkat Kesulitan</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php if (empty($analysis)): ?>
                                <tr>
                                    <td colspan="8" class="text-center py-5 text-muted">
                                        Tidak ada butir soal dalam bank soal ini.
                                    </td>
                                </tr>
                            <?php else: ?>
                                <?php foreach ($analysis as $idx => $an): 
                                    $q = $an['soal'];
                                    $jenis = (int)$q['jenis'];
                                    $kategori = $an['kategori'];
                                    $ketercapaian = (float)$an['ketercapaian'];

                                    $badgeClass = 'badge-warning text-dark';
                                    if ($kategori === 'Mudah') $badgeClass = 'badge-success';
                                    elseif ($kategori === 'Sukar') $badgeClass = 'badge-danger';
                                ?>
                                    <tr>
                                        <td class="text-center font-weight-bold text-muted"><?= $idx + 1 ?></td>
                                        <td class="text-center">
                                            <?php if ($jenis === 1): ?><span class="badge badge-primary">PG</span>
                                            <?php elseif ($jenis === 2): ?><span class="badge badge-info">PG Kompleks</span>
                                            <?php elseif ($jenis === 3): ?><span class="badge badge-warning text-dark">Menjodohkan</span>
                                            <?php elseif ($jenis === 4): ?><span class="badge badge-secondary">Isian</span>
                                            <?php elseif ($jenis === 5): ?><span class="badge badge-dark">Essai</span>
                                            <?php endif; ?>
                                        </td>
                                        <td>
                                            <div class="question-preview small p-2 bg-white rounded border" style="max-height: 80px; overflow-y: auto;">
                                                <?= $q['soal'] ?>
                                            </div>
                                        </td>
                                        <td class="text-center font-weight-bold"><?= $an['total_responden'] ?></td>
                                        <td class="text-center font-weight-bold text-success"><?= $an['total_benar'] ?></td>
                                        <td class="text-center font-weight-bold"><?= $an['avg_point'] ?></td>
                                        <td class="text-center">
                                            <div class="font-weight-bold"><?= $ketercapaian ?>%</div>
                                            <div class="progress progress-xs mt-1">
                                                <div class="progress-bar bg-<?= $ketercapaian >= 75 ? 'success' : ($ketercapaian >= 30 ? 'warning' : 'danger') ?>" 
                                                     style="width: <?= $ketercapaian ?>%"></div>
                                            </div>
                                        </td>
                                        <td class="text-center">
                                            <span class="badge <?= $badgeClass ?> px-2 py-1 font-weight-bold">
                                                <?= $kategori ?>
                                            </span>
                                        </td>
                                    </tr>
                                <?php endforeach; ?>
                            <?php endif; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>

    </div>
</section>

<script>
document.addEventListener('DOMContentLoaded', function() {
    if (typeof renderMathInElement !== 'undefined') {
        document.querySelectorAll('.question-preview').forEach(function(el) {
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

