<?php
$title = $title ?? 'Ruang Pengawas Ujian — DADO CBT';
$guru = $guru ?? [];
$tp = $tp ?? [];
$smt = $smt ?? [];
$monitoringSchedules = $monitoringSchedules ?? [];
?>

<div class="content-header">
    <div class="container-fluid">
        <div class="row mb-2">
            <div class="col-sm-6">
                <h1 class="m-0 text-dark font-weight-bold">
                    <i class="fas fa-user-shield text-primary mr-2"></i>Ruang Pengawas Ujian
                </h1>
            </div>
            <div class="col-sm-6">
                <ol class="breadcrumb float-sm-right">
                    <li class="breadcrumb-item"><a href="/dashboard">Beranda</a></li>
                    <li class="breadcrumb-item active">Ruang Pengawas</li>
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

        <!-- Teacher Proctor Profile Card -->
        <div class="card card-outline card-primary shadow-sm mb-4">
            <div class="card-body py-3">
                <div class="d-flex align-items-center">
                    <div class="mr-3">
                        <div class="bg-primary text-white rounded-circle d-flex align-items-center justify-content-center" style="width: 50px; height: 50px; font-size: 1.5rem;">
                            <i class="fas fa-chalkboard-teacher"></i>
                        </div>
                    </div>
                    <div>
                        <h5 class="font-weight-bold text-dark mb-0"><?= htmlspecialchars($guru['nama_guru'] ?? 'Guru Pengawas') ?></h5>
                        <div class="text-muted small">
                            NIP: <?= htmlspecialchars($guru['nip'] ?? '-') ?> | Tahun Pelajaran: <?= htmlspecialchars($tp['nama_tp'] ?? '-') ?> (Semester <?= (int)($smt['id_smt'] ?? 1) ?>)
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Assigned Monitoring Schedules -->
        <div class="card card-outline card-success shadow-sm mb-5">
            <div class="card-header bg-white py-3">
                <h3 class="card-title font-weight-bold text-dark mb-0">
                    <i class="fas fa-list-alt mr-1"></i>Jadwal Penugasan Mengawas
                </h3>
            </div>
            <div class="card-body">
                <?php if (empty($monitoringSchedules)): ?>
                    <div class="text-center py-5">
                        <i class="fas fa-user-clock fa-3x text-muted mb-3"></i>
                        <h5 class="text-muted">Tidak Ada Jadwal Mengawas Aktif</h5>
                        <p class="text-muted">Anda belum ditugaskan sebagai pengawas pada jadwal ujian yang sedang berlangsung atau belum dijadwalkan oleh proktor utama.</p>
                    </div>
                <?php else: ?>
                    <div class="table-responsive">
                        <table class="table table-bordered table-striped table-hover align-middle">
                            <thead class="thead-light">
                                <tr>
                                    <th style="width: 50px;" class="text-center">No</th>
                                    <th>Ujian & Mata Pelajaran</th>
                                    <th style="width: 140px;" class="text-center">Ruang</th>
                                    <th style="width: 140px;" class="text-center">Sesi</th>
                                    <th style="width: 180px;">Rentang Pelaksanaan</th>
                                    <th style="width: 100px;" class="text-center">Durasi</th>
                                    <th style="width: 120px;" class="text-center">Aksi</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach ($monitoringSchedules as $idx => $sch): ?>
                                    <tr>
                                        <td class="text-center font-weight-bold text-muted"><?= $idx + 1 ?></td>
                                        <td>
                                            <span class="badge badge-info mr-1"><?= htmlspecialchars($sch['kode_jenis'] ?? 'CBT') ?></span>
                                            <strong class="text-dark"><?= htmlspecialchars($sch['nama_mapel'] ?? '-') ?></strong>
                                            <div class="small text-muted mt-1">
                                                Kode: <?= htmlspecialchars($sch['bank_kode'] ?? '-') ?> | Tingkat: Kelas <?= (int)($sch['bank_level'] ?? 7) ?>
                                            </div>
                                        </td>
                                        <td class="text-center font-weight-bold">
                                            <span class="badge badge-primary px-2 py-1">
                                                <i class="fas fa-door-open mr-1"></i><?= htmlspecialchars($sch['nama_ruang'] ?? '-') ?>
                                            </span>
                                        </td>
                                        <td class="text-center font-weight-bold">
                                            <span class="badge badge-warning text-dark px-2 py-1">
                                                <i class="fas fa-clock mr-1"></i><?= htmlspecialchars($sch['nama_sesi'] ?? '-') ?>
                                            </span>
                                            <div class="small text-muted mt-1">
                                                <?= substr($sch['waktu_mulai'] ?? '00:00:00', 0, 5) ?> - <?= substr($sch['waktu_akhir'] ?? '00:00:00', 0, 5) ?>
                                            </div>
                                        </td>
                                        <td class="small">
                                            <div><i class="fas fa-play text-success mr-1"></i>Mulai: <?= date('d M Y, H:i', strtotime($sch['tgl_mulai'])) ?></div>
                                            <div><i class="fas fa-stop text-danger mr-1"></i>Selesai: <?= date('d M Y, H:i', strtotime($sch['tgl_selesai'])) ?></div>
                                        </td>
                                        <td class="text-center font-weight-bold text-primary">
                                            <?= (int)$sch['durasi_ujian'] ?> Menit
                                        </td>
                                        <td class="text-center">
                                            <a href="/cbt/pengawas/ruang/<?= (int)$sch['id_jadwal'] ?>?ruang=<?= (int)$sch['id_ruang'] ?>&sesi=<?= (int)$sch['id_sesi'] ?>" class="btn btn-sm btn-success font-weight-bold shadow-sm">
                                                <i class="fas fa-tv mr-1"></i>Pantau
                                            </a>
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
