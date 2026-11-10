<?php
$title = $title ?? 'Rekap Nilai Ujian CBT — DADO CBT';
$jadwalList = $jadwalList ?? [];
$currentJadwal = $currentJadwal ?? null;
$kelasList = $kelasList ?? [];
$scores = $scores ?? [];
$selectedJadwal = $selectedJadwal ?? 0;
$selectedKelas = $selectedKelas ?? 0;
$search = $search ?? '';

$hasScores = !empty($scores);
?>

<div class="content-header">
    <div class="container-fluid">
        <div class="row mb-2">
            <div class="col-sm-6">
                <h1 class="m-0 text-dark font-weight-bold">
                    <i class="fas fa-chart-line text-primary mr-2"></i>Rekapitulasi Nilai Ujian CBT
                </h1>
            </div>
            <div class="col-sm-6">
                <ol class="breadcrumb float-sm-right">
                    <li class="breadcrumb-item"><a href="/dashboard">Beranda</a></li>
                    <li class="breadcrumb-item"><a href="/cbt/jadwal">CBT</a></li>
                    <li class="breadcrumb-item active">Rekap Nilai</li>
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

        <!-- Filter Card -->
        <div class="card card-outline card-primary shadow-sm mb-4">
            <div class="card-header bg-white py-3">
                <h5 class="card-title font-weight-bold text-dark mb-0">
                    <i class="fas fa-filter mr-1"></i>Pilih Paket Ujian & Filter Kelas
                </h5>
            </div>
            <form action="/cbt/rekap-nilai" method="GET">
                <div class="card-body">
                    <div class="row">
                        <div class="col-md-5 mb-2">
                            <label class="font-weight-bold small">Jadwal Ujian: <span class="text-danger">*</span></label>
                            <select name="jadwal_id" class="form-control select2" onchange="this.form.submit()">
                                <?php foreach ($jadwalList as $j): ?>
                                    <option value="<?= $j['id_jadwal'] ?>" <?= (int)$selectedJadwal === (int)$j['id_jadwal'] ? 'selected' : '' ?>>
                                        [<?= htmlspecialchars($j['bank_kode']) ?>] <?= htmlspecialchars($j['nama_mapel']) ?>
                                    </option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                        <div class="col-md-3 mb-2">
                            <label class="font-weight-bold small">Filter Kelas:</label>
                            <select name="kelas_id" class="form-control" onchange="this.form.submit()">
                                <option value="0">-- Semua Kelas --</option>
                                <?php foreach ($kelasList as $k): ?>
                                    <option value="<?= $k['id_kelas'] ?>" <?= (int)$selectedKelas === (int)$k['id_kelas'] ? 'selected' : '' ?>>
                                        <?= htmlspecialchars($k['nama_kelas']) ?>
                                    </option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                        <div class="col-md-4 mb-2">
                            <label class="font-weight-bold small">Cari Siswa / NIS:</label>
                            <div class="input-group">
                                <input type="text" name="search" class="form-control" value="<?= htmlspecialchars($search) ?>" placeholder="Nama / NIS...">
                                <div class="input-group-append">
                                    <button type="submit" class="btn btn-primary"><i class="fas fa-search"></i></button>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </form>
        </div>

        <!-- Action Toolbar & Recap Table -->
        <div class="card card-outline card-secondary shadow-sm mb-5">
            <div class="card-header bg-white py-3 d-flex flex-wrap justify-content-between align-items-center">
                <h5 class="card-title font-weight-bold text-dark mb-0">
                    <i class="fas fa-table mr-2 text-primary"></i>Daftar Perolehan Nilai Siswa (<?= count($scores) ?> Peserta)
                </h5>
                <div class="my-1">
                    <?php if ($selectedJadwal > 0): ?>
                        <button type="button" class="btn btn-sm btn-warning font-weight-bold shadow-sm mr-1" data-toggle="modal" data-target="#modalKatrol">
                            <i class="fas fa-sliders-h mr-1"></i>Katrol / Konversi Nilai
                        </button>
                        <a href="/cbt/analisis/<?= $selectedJadwal ?>" class="btn btn-sm btn-info font-weight-bold shadow-sm mr-1">
                            <i class="fas fa-microscope mr-1"></i>Analisis Butir Soal
                        </a>
                        <a href="/cbt/rekap-nilai/export/<?= $selectedJadwal ?>" class="btn btn-sm btn-success font-weight-bold shadow-sm">
                            <i class="fas fa-file-excel mr-1"></i>Export Excel Dinamis
                        </a>
                    <?php endif; ?>
                </div>
            </div>
            <div class="card-body p-0">
                <div class="table-responsive">
                    <table class="table table-bordered table-striped table-hover align-middle mb-0 datatable">
                        <thead class="bg-light">
                            <tr>
                                <th style="width: 40px;" class="text-center">No</th>
                                <th style="width: 140px;">Nomor / NIS</th>
                                <th>Nama Peserta</th>
                                <th style="width: 100px;" class="text-center">Kelas</th>
                                <th style="width: 80px;" class="text-center">PG</th>
                                <th style="width: 90px;" class="text-center">Kompleks</th>
                                <th style="width: 90px;" class="text-center">Menjodohkan</th>
                                <th style="width: 80px;" class="text-center">Isian</th>
                                <th style="width: 80px;" class="text-center">Esai</th>
                                <th style="width: 100px;" class="text-center">Total Nilai</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php if (empty($scores)): ?>
                                <tr>
                                    <td colspan="10" class="text-center py-5 text-muted">
                                        <i class="fas fa-poll fa-2x mb-2 d-block"></i>
                                        Tidak ada data nilai pada jadwal ini.
                                    </td>
                                </tr>
                            <?php else: ?>
                                <?php foreach ($scores as $idx => $sc): 
                                    $tot = (float)($sc['total_nilai'] ?? 0);
                                ?>
                                    <tr>
                                        <td class="text-center font-weight-bold text-muted"><?= $idx + 1 ?></td>
                                        <td>
                                            <strong><?= htmlspecialchars($sc['nomor_peserta'] ?? $sc['nis']) ?></strong>
                                            <div class="small text-muted">NIS: <?= htmlspecialchars($sc['nis']) ?></div>
                                        </td>
                                        <td>
                                            <strong class="text-dark"><?= htmlspecialchars($sc['nama_siswa']) ?></strong>
                                        </td>
                                        <td class="text-center">
                                            <span class="badge badge-light border"><?= htmlspecialchars($sc['nama_kelas'] ?? '-') ?></span>
                                        </td>
                                        <td class="text-center"><?= number_format((float)$sc['pg_nilai'], 2) ?></td>
                                        <td class="text-center"><?= number_format((float)$sc['kompleks_nilai'], 2) ?></td>
                                        <td class="text-center"><?= number_format((float)$sc['jodohkan_nilai'], 2) ?></td>
                                        <td class="text-center"><?= number_format((float)$sc['isian_nilai'], 2) ?></td>
                                        <td class="text-center"><?= number_format((float)$sc['essai_nilai'], 2) ?></td>
                                        <td class="text-center font-weight-bold text-primary" style="font-size: 1.15rem;">
                                            <?= number_format($tot, 2) ?>
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

<!-- Modal Katrol / Konversi Nilai Ujian (PRD 4.4) -->
<div class="modal fade" id="modalKatrol" tabindex="-1" role="dialog" aria-labelledby="modalKatrolLabel" aria-hidden="true">
    <div class="modal-dialog" role="document">
        <form action="/cbt/rekap-nilai/katrol/<?= $selectedJadwal ?>" method="POST">
            <div class="modal-content">
                <div class="modal-header bg-warning py-2">
                    <h5 class="modal-title font-weight-bold text-dark" id="modalKatrolLabel">
                        <i class="fas fa-sliders-h mr-2"></i>Konversi Rentang Nilai (Katrol Nilai)
                    </h5>
                    <button type="button" class="close text-dark" data-dismiss="modal" aria-label="Close">
                        <span aria-hidden="true">&times;</span>
                    </button>
                </div>
                <div class="modal-body">
                    <div class="callout callout-info small mb-3">
                        <strong>Formula Katrol Nilai (PRD Bagian 4.4):</strong><br>
                        <code>N_konversi = T_min + ((N_asli - N_min_asli) / (N_max_asli - N_min_asli)) * (T_max - T_min)</code>
                    </div>

                    <div class="row">
                        <div class="col-6">
                            <div class="form-group mb-3">
                                <label class="font-weight-bold">Target Minimum (T_min): <span class="text-danger">*</span></label>
                                <input type="number" step="0.5" name="t_min" class="form-control font-weight-bold text-center" value="70.0" min="0" max="99" required>
                            </div>
                        </div>
                        <div class="col-6">
                            <div class="form-group mb-3">
                                <label class="font-weight-bold">Target Maksimum (T_max): <span class="text-danger">*</span></label>
                                <input type="number" step="0.5" name="t_max" class="form-control font-weight-bold text-center" value="95.0" min="1" max="100" required>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="modal-footer py-2 justify-content-between">
                    <button type="button" class="btn btn-secondary" data-dismiss="modal">Batal</button>
                    <button type="submit" class="btn btn-warning font-weight-bold px-4">
                        <i class="fas fa-check mr-1"></i>Terapkan Katrol Nilai
                    </button>
                </div>
            </div>
        </form>
    </div>
</div>

