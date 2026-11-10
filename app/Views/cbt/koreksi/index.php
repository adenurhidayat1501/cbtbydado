<?php
$title = $title ?? 'Koreksi Ujian CBT — DADO CBT';
$schedules = $schedules ?? [];
$tp = $tp ?? [];
$smt = $smt ?? [];
?>

<div class="content-header">
    <div class="container-fluid">
        <div class="row mb-2">
            <div class="col-sm-6">
                <h1 class="m-0 text-dark font-weight-bold">
                    <i class="fas fa-edit text-primary mr-2"></i>Koreksi Hasil Ujian CBT
                </h1>
            </div>
            <div class="col-sm-6">
                <ol class="breadcrumb float-sm-right">
                    <li class="breadcrumb-item"><a href="/dashboard">Beranda</a></li>
                    <li class="breadcrumb-item"><a href="/cbt/jadwal">CBT</a></li>
                    <li class="breadcrumb-item active">Koreksi</li>
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

        <div class="card card-outline card-primary shadow-sm mb-5">
            <div class="card-header bg-white py-3">
                <h5 class="card-title font-weight-bold text-dark mb-0">
                    <i class="fas fa-list-ol mr-1"></i>Pilih Paket Ujian yang Akan Dikoreksi
                </h5>
            </div>
            <div class="card-body p-0">
                <div class="table-responsive">
                    <table class="table table-bordered table-hover align-middle mb-0">
                        <thead class="bg-light">
                            <tr>
                                <th style="width: 50px;" class="text-center">No</th>
                                <th style="width: 100px;" class="text-center">Jenis</th>
                                <th>Mata Pelajaran & Bank Soal</th>
                                <th style="width: 140px;" class="text-center">Komposisi Esai</th>
                                <th style="width: 160px;">Rentang Pelaksanaan</th>
                                <th style="width: 100px;" class="text-center">Status</th>
                                <th style="width: 140px;" class="text-center">Aksi</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php if (empty($schedules)): ?>
                                <tr>
                                    <td colspan="7" class="text-center py-5 text-muted">
                                        <i class="fas fa-calendar-times fa-2x mb-2 d-block"></i>
                                        Tidak ada jadwal ujian aktif pada periode ini.
                                    </td>
                                </tr>
                            <?php else: ?>
                                <?php foreach ($schedules as $idx => $sch): 
                                    $idJadwal = (int)$sch['id_jadwal'];
                                    $hasEsai = (int)($sch['tampil_esai'] ?? 0) > 0;
                                ?>
                                    <tr>
                                        <td class="text-center font-weight-bold text-muted"><?= $idx + 1 ?></td>
                                        <td class="text-center">
                                            <span class="badge badge-info px-2 py-1 font-weight-bold"><?= htmlspecialchars($sch['kode_jenis'] ?? 'CBT') ?></span>
                                        </td>
                                        <td>
                                            <span class="badge badge-primary mr-1"><?= htmlspecialchars($sch['bank_kode']) ?></span>
                                            <strong class="text-dark font-weight-bold"><?= htmlspecialchars($sch['nama_mapel']) ?></strong>
                                            <div class="small text-muted mt-1">Level: Kelas <?= (int)$sch['bank_level'] ?> | Guru: <?= htmlspecialchars($sch['nama_guru']) ?></div>
                                        </td>
                                        <td class="text-center">
                                            <?php if ($hasEsai): ?>
                                                <span class="badge badge-warning text-dark px-2 py-1">
                                                    <i class="fas fa-pencil-alt mr-1"></i><?= (int)$sch['tampil_esai'] ?> Butir Esai
                                                </span>
                                            <?php else: ?>
                                                <span class="badge badge-light border">Otomatis (Tanpa Esai)</span>
                                            <?php endif; ?>
                                        </td>
                                        <td class="small">
                                            <div>Mulai: <?= date('d M Y, H:i', strtotime($sch['tgl_mulai'])) ?></div>
                                            <div>Selesai: <?= date('d M Y, H:i', strtotime($sch['tgl_selesai'])) ?></div>
                                        </td>
                                        <td class="text-center">
                                            <?php if ((int)$sch['status'] === 1): ?>
                                                <span class="badge badge-success">Aktif</span>
                                            <?php else: ?>
                                                <span class="badge badge-secondary">Selesai</span>
                                            <?php endif; ?>
                                        </td>
                                        <td class="text-center">
                                            <a href="/cbt/koreksi/peserta/<?= $idJadwal ?>" class="btn btn-sm btn-primary font-weight-bold shadow-sm">
                                                <i class="fas fa-user-edit mr-1"></i>Periksa Jawaban
                                            </a>
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

