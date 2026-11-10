<?php
$title = $title ?? 'Portal Ujian CBT Siswa — DADO CBT';
$siswa = $siswa ?? [];
$tp = $tp ?? [];
$smt = $smt ?? [];
$schedules = $schedules ?? [];
?>

<div class="content-header">
    <div class="container-fluid">
        <div class="row mb-2">
            <div class="col-sm-6">
                <h1 class="m-0 text-dark font-weight-bold">
                    <i class="fas fa-laptop-code text-primary mr-2"></i>Portal Ujian CBT Siswa
                </h1>
            </div>
            <div class="col-sm-6">
                <ol class="breadcrumb float-sm-right">
                    <li class="breadcrumb-item"><a href="/student/dashboard">Beranda</a></li>
                    <li class="breadcrumb-item active">Ujian CBT</li>
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

        <!-- Student Profile Card -->
        <div class="card card-outline card-primary shadow-sm mb-4">
            <div class="card-body py-3">
                <div class="d-flex align-items-center">
                    <div class="mr-3">
                        <img src="<?= !empty($siswa['foto']) ? upload_url('foto_siswa/' . $siswa['foto']) : asset('img/default-avatar.png') ?>" 
                             alt="Foto Siswa" class="rounded-circle border border-primary shadow-sm" style="width: 55px; height: 55px; object-fit: cover;">
                    </div>
                    <div>
                        <h5 class="font-weight-bold text-dark mb-0"><?= htmlspecialchars($siswa['nama'] ?? 'Peserta Ujian') ?></h5>
                        <div class="text-muted small">
                            NIS / NISN: <strong><?= htmlspecialchars($siswa['nis'] ?? '-') ?></strong> / <?= htmlspecialchars($siswa['nisn'] ?? '-') ?>
                            | Periode: <?= htmlspecialchars($tp['tahun'] ?? ($tp['nama_tp'] ?? '-')) ?> (Semester <?= (int)($smt['id_smt'] ?? 1) ?>)
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Available Schedules List -->
        <div class="card card-outline card-info shadow-sm mb-5">
            <div class="card-header bg-white py-3">
                <h3 class="card-title font-weight-bold text-dark mb-0">
                    <i class="fas fa-clipboard-list mr-2"></i>Daftar Jadwal Ujian Tersedia
                </h3>
            </div>
            <div class="card-body p-0">
                <?php if (empty($schedules)): ?>
                    <div class="text-center py-5">
                        <i class="fas fa-calendar-check fa-3x text-muted mb-3"></i>
                        <h5 class="text-muted">Tidak Ada Jadwal Ujian Aktif Saat Ini</h5>
                        <p class="text-muted">Jadwal ujian akan muncul di sini jika proktor atau guru telah mengaktifkan jadwal untuk kelas Anda.</p>
                    </div>
                <?php else: ?>
                    <div class="table-responsive">
                        <table class="table table-hover align-middle mb-0">
                            <thead class="bg-light">
                                <tr>
                                    <th style="width: 50px;" class="text-center">No</th>
                                    <th>Mata Pelajaran & Ujian</th>
                                    <th style="width: 130px;" class="text-center">Alokasi Waktu</th>
                                    <th style="width: 170px;">Batas Pelaksanaan</th>
                                    <th style="width: 100px;" class="text-center">Durasi</th>
                                    <th style="width: 140px;" class="text-center">Status</th>
                                    <th style="width: 160px;" class="text-center">Aksi</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach ($schedules as $idx => $sch): 
                                    $idJadwal = (int)$sch['id_jadwal'];
                                    $stPengerjaan = (int)($sch['status_pengerjaan'] ?? 0);
                                    $tampilHasil = (int)($sch['hasil_tampil'] ?? 0);
                                    $nilai = $sch['nilai'] ?? null;
                                ?>
                                    <tr>
                                        <td class="text-center font-weight-bold text-muted"><?= $idx + 1 ?></td>
                                        <td>
                                            <span class="badge badge-info mr-1"><?= htmlspecialchars($sch['kode_jenis'] ?? 'CBT') ?></span>
                                            <strong class="text-dark font-weight-bold"><?= htmlspecialchars($sch['nama_mapel'] ?? '-') ?></strong>
                                            <div class="small text-muted mt-1">
                                                Bank: <code><?= htmlspecialchars($sch['bank_kode'] ?? '-') ?></code> | Guru: <?= htmlspecialchars($sch['nama_guru'] ?? '-') ?>
                                            </div>
                                        </td>
                                        <td class="text-center">
                                            <span class="badge badge-secondary px-2 py-1">
                                                Jam Ke-<?= (int)($sch['jam_ke'] ?? 1) ?>
                                            </span>
                                        </td>
                                        <td class="small">
                                            <div><i class="fas fa-play text-success mr-1"></i><?= date('d M Y, H:i', strtotime($sch['tgl_mulai'])) ?></div>
                                            <div><i class="fas fa-stop text-danger mr-1"></i><?= date('d M Y, H:i', strtotime($sch['tgl_selesai'])) ?></div>
                                        </td>
                                        <td class="text-center font-weight-bold text-primary">
                                            <?= (int)$sch['durasi_ujian'] ?> Menit
                                        </td>
                                        <td class="text-center">
                                            <?php if ($stPengerjaan === 0): ?>
                                                <span class="badge badge-secondary px-2 py-1"><i class="fas fa-clock mr-1"></i>Belum Ujian</span>
                                            <?php elseif ($stPengerjaan === 1): ?>
                                                <span class="badge badge-warning text-dark px-2 py-1"><i class="fas fa-spinner fa-spin mr-1"></i>Sedang Ujian</span>
                                            <?php elseif ($stPengerjaan === 2): ?>
                                                <span class="badge badge-success px-2 py-1"><i class="fas fa-check-circle mr-1"></i>Selesai</span>
                                            <?php endif; ?>
                                        </td>
                                        <td class="text-center">
                                            <?php if ($stPengerjaan === 0): ?>
                                                <a href="/student/cbt/konfirmasi/<?= $idJadwal ?>" class="btn btn-sm btn-primary font-weight-bold shadow-sm">
                                                    <i class="fas fa-pencil-alt mr-1"></i>Mulai Ujian
                                                </a>
                                            <?php elseif ($stPengerjaan === 1): ?>
                                                <a href="/student/cbt/ujian/<?= $idJadwal ?>" class="btn btn-sm btn-warning font-weight-bold shadow-sm">
                                                    <i class="fas fa-play mr-1"></i>Lanjutkan
                                                </a>
                                            <?php elseif ($stPengerjaan === 2): ?>
                                                <a href="/student/cbt/selesai/<?= $idJadwal ?>" class="btn btn-sm btn-outline-info">
                                                    <i class="fas fa-eye mr-1"></i>Lihat Rincian
                                                </a>
                                            <?php endif; ?>
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

