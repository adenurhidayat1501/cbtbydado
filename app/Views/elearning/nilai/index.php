<div class="container-fluid py-3">
    <div class="d-flex justify-content-between align-items-center mb-3">
        <div>
            <h4 class="mb-0 fw-bold"><i class="bi bi-check2-circle text-primary me-2"></i>Presensi & Penilaian E-Learning</h4>
            <small class="text-muted">Koreksi tugas siswa, rekap presensi KBM harian dan bulanan</small>
        </div>
        <div class="d-flex gap-2">
            <a href="<?= base_url('/elearning/presensi-nilai/harian') ?>" class="btn btn-outline-primary btn-sm">
                <i class="bi bi-calendar-check me-1"></i> Rekap Presensi Harian
            </a>
            <a href="<?= base_url('/elearning/presensi-nilai/bulanan') ?>" class="btn btn-outline-info btn-sm">
                <i class="bi bi-calendar-month me-1"></i> Rekap Presensi Bulanan
            </a>
        </div>
    </div>

    <?php if (has_flash('success')): ?>
        <div class="alert alert-success alert-dismissible fade show" role="alert">
            <i class="bi bi-check-circle me-1"></i> <?= escape_html(get_flash('success')) ?>
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
    <?php endif; ?>
    <?php if (has_flash('error')): ?>
        <div class="alert alert-danger alert-dismissible fade show" role="alert">
            <i class="bi bi-exclamation-triangle me-1"></i> <?= escape_html(get_flash('error')) ?>
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
    <?php endif; ?>

    <!-- Filter & Export Card -->
    <div class="card shadow-sm border-0 mb-3">
        <div class="card-body py-2">
            <form action="<?= base_url('/elearning/presensi-nilai') ?>" method="GET" class="row g-2 align-items-center">
                <div class="col-md-4">
                    <select name="kelas_id" class="form-select form-select-sm" onchange="this.form.submit()">
                        <option value="">-- Semua Kelas --</option>
                        <?php foreach ($kelasList as $k): ?>
                            <option value="<?= $k['id_kelas'] ?>" <?= (int)$k['id_kelas'] === (int)$selectedKelasId ? 'selected' : '' ?>>
                                <?= escape_html($k['nama_kelas']) ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                </div>

                <div class="col-md-4">
                    <select name="mapel_id" class="form-select form-select-sm" onchange="this.form.submit()">
                        <option value="">-- Semua Mata Pelajaran --</option>
                        <?php foreach ($mapelList as $m): ?>
                            <option value="<?= $m['id_mapel'] ?>" <?= (int)$m['id_mapel'] === (int)$selectedMapelId ? 'selected' : '' ?>>
                                <?= escape_html($m['nama_mapel']) ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                </div>

                <div class="col-md-4 text-end">
                    <?php if ($selectedKelasId): ?>
                        <a href="<?= base_url('/elearning/presensi-nilai/export-nilai?kelas_id=' . $selectedKelasId . ($selectedMapelId ? '&mapel_id=' . $selectedMapelId : '')) ?>" class="btn btn-success btn-sm">
                            <i class="bi bi-file-earmark-excel me-1"></i> Export Rekap Nilai Tugas (.csv)
                        </a>
                    <?php endif; ?>
                </div>
            </form>
        </div>
    </div>

    <!-- Tasks List Table -->
    <div class="card shadow-sm border-0">
        <div class="card-header bg-white py-2 fw-bold">
            <i class="bi bi-journal-check text-primary me-1"></i> Daftar Penugasan Siswa
        </div>
        <div class="card-body p-0">
            <div class="table-responsive">
                <table class="table table-hover table-striped align-middle mb-0 datatable" style="font-size: 13px;">
                    <thead class="table-light">
                        <tr>
                            <th style="width: 40px;" class="text-center">No</th>
                            <th style="width: 120px;" class="text-center">Tanggal Tugas</th>
                            <th>Judul Tugas</th>
                            <th>Kelas</th>
                            <th>Mata Pelajaran</th>
                            <th>Guru Pengampu</th>
                            <th class="text-center">Progres Pengumpulan</th>
                            <th style="width: 130px;" class="text-center">Aksi</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if (empty($tasks)): ?>
                            <tr>
                                <td colspan="8" class="text-center py-4 text-muted">
                                    <i class="bi bi-inbox fs-2 d-block text-secondary mb-2"></i>
                                    Belum ada tugas belajar yang dijadwalkan pada kelas/mapel ini.
                                </td>
                            </tr>
                        <?php else: ?>
                            <?php $no = 1; foreach ($tasks as $t): ?>
                                <tr>
                                    <td class="text-center"><?= $no++ ?></td>
                                    <td class="text-center font-monospace">
                                        <span class="badge bg-light text-dark border">
                                            <?= format_indo_date($t['jadwal_materi']) ?>
                                        </span>
                                    </td>
                                    <td>
                                        <div class="fw-bold text-dark"><?= escape_html($t['judul_materi']) ?></div>
                                        <small class="text-muted font-monospace"><?= escape_html($t['kode_materi']) ?></small>
                                    </td>
                                    <td>
                                        <span class="badge bg-primary text-white"><?= escape_html($t['nama_kelas']) ?></span>
                                    </td>
                                    <td>
                                        <span class="badge bg-light text-secondary border"><?= escape_html($t['nama_mapel']) ?></span>
                                    </td>
                                    <td>
                                        <small><?= escape_html($t['nama_guru']) ?></small>
                                    </td>
                                    <td class="text-center">
                                        <?php
                                        $totKumpul = (int)$t['total_kumpul'];
                                        $totSiswa = max(1, (int)$t['total_siswa_kelas']);
                                        $pct = round(($totKumpul / $totSiswa) * 100);
                                        ?>
                                        <div class="d-flex align-items-center justify-content-center gap-2">
                                            <div class="progress flex-grow-1" style="height: 6px; max-width: 100px;">
                                                <div class="progress-bar bg-success" style="width: <?= $pct ?>%;"></div>
                                            </div>
                                            <span class="badge bg-light text-dark border">
                                                <?= $totKumpul ?> / <?= $totSiswa ?>
                                            </span>
                                        </div>
                                    </td>
                                    <td class="text-center">
                                        <a href="<?= base_url('/elearning/presensi-nilai/submissions/' . $t['id_kjm']) ?>" class="btn btn-primary btn-sm px-3">
                                            <i class="bi bi-pencil-square me-1"></i> Koreksi & Nilai
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

