<?php
$title = $title ?? 'Daftar Siswa Koreksi — DADO CBT';
$jadwal = $jadwal ?? [];
$students = $students ?? [];
$idJadwal = (int)($jadwal['id_jadwal'] ?? 0);
?>

<div class="content-header">
    <div class="container-fluid">
        <div class="row mb-2">
            <div class="col-sm-6">
                <h1 class="m-0 text-dark font-weight-bold">
                    <i class="fas fa-user-edit text-primary mr-2"></i>Daftar Siswa Ujian: <?= htmlspecialchars($jadwal['bank_kode'] ?? 'CBT') ?>
                </h1>
            </div>
            <div class="col-sm-6">
                <ol class="breadcrumb float-sm-right">
                    <li class="breadcrumb-item"><a href="/dashboard">Beranda</a></li>
                    <li class="breadcrumb-item"><a href="/cbt/koreksi">Koreksi</a></li>
                    <li class="breadcrumb-item active">Peserta</li>
                </ol>
            </div>
        </div>
    </div>
</div>

<section class="content">
    <div class="container-fluid">

        <!-- Jadwal Summary Banner -->
        <div class="card card-outline card-info shadow-sm mb-4">
            <div class="card-body py-3">
                <div class="d-flex justify-content-between align-items-center">
                    <div>
                        <h5 class="font-weight-bold text-dark mb-1">
                            <?= htmlspecialchars($jadwal['nama_mapel'] ?? '-') ?>
                            <span class="badge badge-info ml-1"><?= htmlspecialchars($jadwal['bank_kode'] ?? '-') ?></span>
                        </h5>
                        <div class="small text-muted">
                            Guru: <?= htmlspecialchars($jadwal['nama_guru'] ?? '-') ?> | Durasi: <?= (int)$jadwal['durasi_ujian'] ?> Menit | Komposisi: <?= (int)$jadwal['tampil_esai'] ?> Esai (Bobot <?= (int)$jadwal['bobot_esai'] ?>%)
                        </div>
                    </div>
                    <div>
                        <a href="/cbt/koreksi" class="btn btn-secondary btn-sm shadow-sm">
                            <i class="fas fa-arrow-left mr-1"></i>Kembali
                        </a>
                    </div>
                </div>
            </div>
        </div>

        <!-- Student List Card -->
        <div class="card card-outline card-primary shadow-sm mb-5">
            <div class="card-header bg-white py-3">
                <h5 class="card-title font-weight-bold text-dark mb-0">
                    <i class="fas fa-users mr-1"></i>Daftar Lembar Jawaban Siswa (<?= count($students) ?> Peserta)
                </h5>
            </div>
            <div class="card-body p-0">
                <div class="table-responsive">
                    <table class="table table-bordered table-hover align-middle mb-0">
                        <thead class="bg-light">
                            <tr>
                                <th style="width: 50px;" class="text-center">No</th>
                                <th style="width: 140px;">Nomor / NIS</th>
                                <th>Nama Peserta</th>
                                <th style="width: 110px;" class="text-center">Kelas</th>
                                <th style="width: 110px;" class="text-center">Waktu Pengerjaan</th>
                                <th style="width: 120px;" class="text-center">Status Koreksi</th>
                                <th style="width: 90px;" class="text-center">Total Skor</th>
                                <th style="width: 130px;" class="text-center">Aksi</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php if (empty($students)): ?>
                                <tr>
                                    <td colspan="8" class="text-center py-5 text-muted">
                                        <i class="fas fa-user-slash fa-2x mb-2 d-block"></i>
                                        Belum ada peserta yang mengumpulkan ujian untuk jadwal ini.
                                    </td>
                                </tr>
                            <?php else: ?>
                                <?php foreach ($students as $idx => $st): 
                                    $idSiswa = (int)$st['id_siswa'];
                                    $isDikoreksi = (int)($st['dikoreksi'] ?? 0) === 1;
                                    $totalNilai = number_format((float)$st['total_nilai'], 2);
                                ?>
                                    <tr>
                                        <td class="text-center font-weight-bold text-muted"><?= $idx + 1 ?></td>
                                        <td>
                                            <strong><?= htmlspecialchars($st['nomor_peserta'] ?? $st['nis']) ?></strong>
                                            <div class="small text-muted">NIS: <?= htmlspecialchars($st['nis']) ?></div>
                                        </td>
                                        <td>
                                            <strong class="text-dark"><?= htmlspecialchars($st['nama_siswa']) ?></strong>
                                        </td>
                                        <td class="text-center">
                                            <span class="badge badge-light border"><?= htmlspecialchars($st['nama_kelas'] ?? '-') ?></span>
                                        </td>
                                        <td class="text-center small">
                                            <?= (int)($st['lama_ujian'] ?? 0) ?> Menit
                                        </td>
                                        <td class="text-center">
                                            <?php if ($isDikoreksi): ?>
                                                <span class="badge badge-success px-2 py-1"><i class="fas fa-check-circle mr-1"></i>Sudah Dikoreksi</span>
                                            <?php else: ?>
                                                <span class="badge badge-warning text-dark px-2 py-1"><i class="fas fa-clock mr-1"></i>Perlu Koreksi</span>
                                            <?php endif; ?>
                                        </td>
                                        <td class="text-center font-weight-bold text-primary" style="font-size: 1.1rem;">
                                            <?= $totalNilai ?>
                                        </td>
                                        <td class="text-center">
                                            <a href="/cbt/koreksi/detail/<?= $idJadwal ?>/<?= $idSiswa ?>" class="btn btn-sm btn-primary font-weight-bold shadow-sm">
                                                <i class="fas fa-pencil-alt mr-1"></i>Koreksi Lembar
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

