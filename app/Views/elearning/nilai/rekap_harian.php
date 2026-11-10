<div class="container-fluid py-3">
    <div class="d-flex justify-content-between align-items-center mb-3">
        <div>
            <h4 class="mb-0 fw-bold"><i class="bi bi-calendar-check text-primary me-2"></i>Rekap Presensi Harian KBM</h4>
            <small class="text-muted">Pencatatan kehadiran otomatis siswa pada jam pelajaran</small>
        </div>
        <div class="d-flex gap-2">
            <a href="<?= base_url('/elearning/presensi-nilai') ?>" class="btn btn-outline-secondary btn-sm">
                <i class="bi bi-arrow-left me-1"></i> Kembali ke Penilaian
            </a>
            <?php if ($selectedKelasId && $selectedMapelId): ?>
                <a href="<?= base_url('/elearning/presensi-nilai/export-harian?kelas_id=' . $selectedKelasId . '&mapel_id=' . $selectedMapelId . '&date=' . $selectedDate) ?>" class="btn btn-success btn-sm">
                    <i class="bi bi-file-earmark-excel me-1"></i> Unduh Presensi (.csv)
                </a>
            <?php endif; ?>
        </div>
    </div>

    <!-- Filter Card -->
    <div class="card shadow-sm border-0 mb-3">
        <div class="card-body py-2">
            <form action="<?= base_url('/elearning/presensi-nilai/harian') ?>" method="GET" class="row g-2 align-items-center">
                <div class="col-md-4">
                    <label class="form-label small fw-semibold mb-1">Kelas Rombel</label>
                    <select name="kelas_id" class="form-select form-select-sm" onchange="this.form.submit()">
                        <?php foreach ($kelasList as $k): ?>
                            <option value="<?= $k['id_kelas'] ?>" <?= (int)$k['id_kelas'] === (int)$selectedKelasId ? 'selected' : '' ?>>
                                <?= escape_html($k['nama_kelas']) ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                </div>

                <div class="col-md-4">
                    <label class="form-label small fw-semibold mb-1">Mata Pelajaran</label>
                    <select name="mapel_id" class="form-select form-select-sm" onchange="this.form.submit()">
                        <?php foreach ($mapelList as $m): ?>
                            <option value="<?= $m['id_mapel'] ?>" <?= (int)$m['id_mapel'] === (int)$selectedMapelId ? 'selected' : '' ?>>
                                <?= escape_html($m['nama_mapel']) ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                </div>

                <div class="col-md-4">
                    <label class="form-label small fw-semibold mb-1">Tanggal KBM</label>
                    <input type="date" name="date" class="form-control form-control-sm" value="<?= escape_html($selectedDate) ?>" onchange="this.form.submit()">
                </div>
            </form>
        </div>
    </div>

    <!-- Summary Stats -->
    <?php
    $totalSiswa = count($attendanceData);
    $totalHadir = 0;
    foreach ($attendanceData as $row) {
        if (!empty($row['id_log'])) {
            $totalHadir++;
        }
    }
    $totalTidakHadir = $totalSiswa - $totalHadir;
    $pctHadir = $totalSiswa > 0 ? round(($totalHadir / $totalSiswa) * 100, 1) : 0;
    ?>

    <div class="row g-3 mb-3">
        <div class="col-6 col-md-3">
            <div class="card border-0 shadow-sm bg-primary text-white">
                <div class="card-body p-3">
                    <div class="small text-white-50">Total Siswa</div>
                    <div class="fs-4 fw-bold"><?= $totalSiswa ?></div>
                </div>
            </div>
        </div>
        <div class="col-6 col-md-3">
            <div class="card border-0 shadow-sm bg-success text-white">
                <div class="card-body p-3">
                    <div class="small text-white-50">Hadir KBM</div>
                    <div class="fs-4 fw-bold"><?= $totalHadir ?></div>
                </div>
            </div>
        </div>
        <div class="col-6 col-md-3">
            <div class="card border-0 shadow-sm bg-danger text-white">
                <div class="card-body p-3">
                    <div class="small text-white-50">Belum Hadir</div>
                    <div class="fs-4 fw-bold"><?= $totalTidakHadir ?></div>
                </div>
            </div>
        </div>
        <div class="col-6 col-md-3">
            <div class="card border-0 shadow-sm bg-info text-white">
                <div class="card-body p-3">
                    <div class="small text-white-50">Persentase Hadir</div>
                    <div class="fs-4 fw-bold"><?= $pctHadir ?>%</div>
                </div>
            </div>
        </div>
    </div>

    <!-- Attendance Table -->
    <div class="card shadow-sm border-0">
        <div class="card-header bg-white py-2 fw-bold">
            <i class="bi bi-table text-primary me-1"></i> Data Kehadiran Tanggal <?= format_indo_date($selectedDate, false, true) ?>
        </div>
        <div class="card-body p-0">
            <div class="table-responsive">
                <table class="table table-hover table-striped align-middle mb-0 datatable" style="font-size: 13px;">
                    <thead class="table-light">
                        <tr>
                            <th style="width: 40px;" class="text-center">No</th>
                            <th style="width: 110px;">NIS</th>
                            <th>Nama Siswa</th>
                            <th style="width: 60px;" class="text-center">L/P</th>
                            <th style="width: 140px;" class="text-center">Status Kehadiran</th>
                            <th style="width: 140px;" class="text-center">Waktu Presensi</th>
                            <th style="width: 100px;" class="text-center">Jam Ke</th>
                            <th>Aktivitas Terakhir</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if (empty($attendanceData)): ?>
                            <tr>
                                <td colspan="8" class="text-center py-4 text-muted">
                                    Tidak ada data kehadiran atau rombel kosong.
                                </td>
                            </tr>
                        <?php else: ?>
                            <?php $no = 1; foreach ($attendanceData as $row): ?>
                                <?php $isHadir = !empty($row['id_log']); ?>
                                <tr>
                                    <td class="text-center"><?= $no++ ?></td>
                                    <td class="font-monospace"><?= escape_html($row['nis']) ?></td>
                                    <td>
                                        <div class="fw-semibold text-dark"><?= escape_html($row['nama']) ?></div>
                                    </td>
                                    <td class="text-center"><?= escape_html($row['jk'] ?? '-') ?></td>
                                    <td class="text-center">
                                        <?php if ($isHadir): ?>
                                            <span class="badge bg-success-subtle text-success border border-success-subtle px-2 py-1">
                                                <i class="bi bi-check-circle me-1"></i> Hadir
                                            </span>
                                        <?php else: ?>
                                            <span class="badge bg-danger-subtle text-danger border border-danger-subtle px-2 py-1">
                                                <i class="bi bi-x-circle me-1"></i> Tidak Hadir
                                            </span>
                                        <?php endif; ?>
                                    </td>
                                    <td class="text-center font-monospace small">
                                        <?= $isHadir ? date('H:i:s', strtotime($row['log_time'])) . ' WIB' : '-' ?>
                                    </td>
                                    <td class="text-center">
                                        <?php if ($isHadir && !empty($row['jam_ke'])): ?>
                                            <span class="badge bg-light text-primary border">Jam Ke-<?= $row['jam_ke'] ?></span>
                                        <?php elseif ($isHadir): ?>
                                            <span class="badge bg-light text-muted border">-</span>
                                        <?php else: ?>
                                            <span class="text-muted">-</span>
                                        <?php endif; ?>
                                    </td>
                                    <td>
                                        <?php if ($isHadir): ?>
                                            <?php if ((int)($row['log_type'] ?? 0) === 3): ?>
                                                <span class="badge bg-primary text-white">Mengumpulkan Tugas</span>
                                            <?php else: ?>
                                                <span class="badge bg-info text-white">Membaca Materi</span>
                                            <?php endif; ?>
                                            <small class="text-muted ms-1"><?= escape_html($row['judul_materi'] ?? '') ?></small>
                                        <?php else: ?>
                                            <span class="text-muted small">-</span>
                                        <?php endif; ?>
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

