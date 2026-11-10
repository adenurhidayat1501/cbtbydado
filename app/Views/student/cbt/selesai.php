<?php
$title = $title ?? 'Ujian Selesai — DADO CBT';
$siswa = $siswa ?? [];
$jadwal = $jadwal ?? [];
$session = $session ?? [];
$nilai = $nilai ?? null;

$hasilTampil = (int)($jadwal['hasil_tampil'] ?? 0) === 1;
?>

<div class="content-header">
    <div class="container-fluid">
        <div class="row mb-2">
            <div class="col-sm-6">
                <h1 class="m-0 text-dark font-weight-bold">
                    <i class="fas fa-check-circle text-success mr-2"></i>Ujian Selesai
                </h1>
            </div>
            <div class="col-sm-6">
                <ol class="breadcrumb float-sm-right">
                    <li class="breadcrumb-item"><a href="/student/dashboard">Beranda</a></li>
                    <li class="breadcrumb-item"><a href="/student/cbt">Portal Ujian</a></li>
                    <li class="breadcrumb-item active">Selesai</li>
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

        <div class="row justify-content-center">
            <div class="col-lg-7 col-md-9">
                <div class="card card-outline card-success shadow-sm mb-5 text-center">
                    <div class="card-header bg-white py-3">
                        <h4 class="card-title font-weight-bold text-success w-100 mb-0">
                            <i class="fas fa-clipboard-check mr-2"></i>UJIAN TELAH BERHASIL DISELESAIKAN
                        </h4>
                    </div>
                    <div class="card-body py-4">
                        <div class="my-3">
                            <div class="bg-success text-white rounded-circle d-inline-flex align-items-center justify-content-center shadow" style="width: 80px; height: 80px; font-size: 2.5rem;">
                                <i class="fas fa-check"></i>
                            </div>
                        </div>

                        <h4 class="font-weight-bold text-dark mt-2 mb-1"><?= htmlspecialchars($jadwal['nama_mapel'] ?? 'Mata Pelajaran') ?></h4>
                        <div class="text-muted small mb-3">
                            Bank Soal: <strong><?= htmlspecialchars($jadwal['bank_kode'] ?? '-') ?></strong> | Jenis: <?= htmlspecialchars($jadwal['kode_jenis'] ?? 'CBT') ?>
                        </div>

                        <div class="p-3 bg-light rounded border text-left d-inline-block w-100 my-2">
                            <table class="table table-sm table-borderless mb-0">
                                <tr>
                                    <th style="width: 160px;" class="text-muted">Nama Peserta:</th>
                                    <td class="font-weight-bold text-dark"><?= htmlspecialchars($siswa['nama'] ?? '-') ?></td>
                                </tr>
                                <tr>
                                    <th class="text-muted">NIS / NISN:</th>
                                    <td><?= htmlspecialchars($siswa['nis'] ?? '-') ?> / <?= htmlspecialchars($siswa['nisn'] ?? '-') ?></td>
                                </tr>
                                <tr>
                                    <th class="text-muted">Waktu Mulai:</th>
                                    <td><?= !empty($session['mulai']) ? date('d M Y, H:i:s', strtotime($session['mulai'])) : '-' ?></td>
                                </tr>
                                <tr>
                                    <th class="text-muted">Waktu Selesai:</th>
                                    <td><?= !empty($session['selesai']) ? date('d M Y, H:i:s', strtotime($session['selesai'])) : date('d M Y, H:i:s') ?></td>
                                </tr>
                            </table>
                        </div>

                        <!-- Score Display Section -->
                        <?php if ($hasilTampil && $nilai): ?>
                            <div class="mt-4 p-3 bg-light border border-success rounded">
                                <h6 class="font-weight-bold text-success mb-2"><i class="fas fa-award mr-1"></i>HASIL SKOR UJIAN ANDA:</h6>
                                
                                <div class="my-2">
                                    <span class="font-weight-bold text-primary" style="font-size: 3rem;">
                                        <?= number_format((float)$nilai['total_nilai'], 2) ?>
                                    </span>
                                </div>

                                <div class="row text-center mt-3 pt-2 border-top">
                                    <div class="col-4">
                                        <div class="small text-muted">Pilihan Ganda</div>
                                        <div class="font-weight-bold text-dark"><?= number_format((float)$nilai['pg_nilai'], 2) ?></div>
                                    </div>
                                    <div class="col-4">
                                        <div class="small text-muted">AKM Kompleks/Jodoh</div>
                                        <div class="font-weight-bold text-dark"><?= number_format((float)$nilai['kompleks_nilai'] + (float)$nilai['jodohkan_nilai'], 2) ?></div>
                                    </div>
                                    <div class="col-4">
                                        <div class="small text-muted">Isian & Esai</div>
                                        <div class="font-weight-bold text-dark"><?= number_format((float)$nilai['isian_nilai'] + (float)$nilai['essai_nilai'], 2) ?></div>
                                    </div>
                                </div>
                            </div>
                        <?php else: ?>
                            <div class="alert alert-secondary mt-3 small">
                                <i class="fas fa-info-circle mr-1"></i>
                                Nilai ujian disimpan oleh server dan tidak dipublikasikan ke siswa sesuai konfigurasi jadwal.
                            </div>
                        <?php endif; ?>

                        <div class="mt-4">
                            <a href="/student/cbt" class="btn btn-primary font-weight-bold px-4 shadow-sm">
                                <i class="fas fa-arrow-left mr-1"></i>Kembali ke Portal Ujian
                            </a>
                            <a href="/student/dashboard" class="btn btn-outline-secondary ml-2">
                                <i class="fas fa-home mr-1"></i>Beranda
                            </a>
                        </div>
                    </div>
                </div>
            </div>
        </div>

    </div>
</section>

