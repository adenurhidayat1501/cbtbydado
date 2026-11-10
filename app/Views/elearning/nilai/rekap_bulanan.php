<div class="container-fluid py-3">
    <div class="d-flex justify-content-between align-items-center mb-3">
        <div>
            <h4 class="mb-0 fw-bold"><i class="bi bi-calendar-month text-primary me-2"></i>Rekap Presensi Bulanan KBM</h4>
            <small class="text-muted">Akumulasi kehadiran per pertemuan dalam satu bulan kalender</small>
        </div>
        <div class="d-flex gap-2">
            <a href="<?= base_url('/elearning/presensi-nilai') ?>" class="btn btn-outline-secondary btn-sm">
                <i class="bi bi-arrow-left me-1"></i> Kembali ke Penilaian
            </a>
            <?php if ($selectedKelasId && $selectedMapelId): ?>
                <a href="<?= base_url('/elearning/presensi-nilai/export-bulanan?kelas_id=' . $selectedKelasId . '&mapel_id=' . $selectedMapelId . '&bulan=' . $selectedBulan . '&tahun=' . $selectedTahun) ?>" class="btn btn-success btn-sm">
                    <i class="bi bi-file-earmark-excel me-1"></i> Unduh Presensi Bulanan (.csv)
                </a>
            <?php endif; ?>
        </div>
    </div>

    <!-- Filter Card -->
    <div class="card shadow-sm border-0 mb-3">
        <div class="card-body py-2">
            <form action="<?= base_url('/elearning/presensi-nilai/bulanan') ?>" method="GET" class="row g-2 align-items-center">
                <div class="col-md-3">
                    <label class="form-label small fw-semibold mb-1">Kelas Rombel</label>
                    <select name="kelas_id" class="form-select form-select-sm" onchange="this.form.submit()">
                        <?php foreach ($kelasList as $k): ?>
                            <option value="<?= $k['id_kelas'] ?>" <?= (int)$k['id_kelas'] === (int)$selectedKelasId ? 'selected' : '' ?>>
                                <?= escape_html($k['nama_kelas']) ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                </div>

                <div class="col-md-3">
                    <label class="form-label small fw-semibold mb-1">Mata Pelajaran</label>
                    <select name="mapel_id" class="form-select form-select-sm" onchange="this.form.submit()">
                        <?php foreach ($mapelList as $m): ?>
                            <option value="<?= $m['id_mapel'] ?>" <?= (int)$m['id_mapel'] === (int)$selectedMapelId ? 'selected' : '' ?>>
                                <?= escape_html($m['nama_mapel']) ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                </div>

                <div class="col-md-3">
                    <label class="form-label small fw-semibold mb-1">Bulan</label>
                    <select name="bulan" class="form-select form-select-sm" onchange="this.form.submit()">
                        <?php foreach ($months as $num => $namaBulan): ?>
                            <option value="<?= $num ?>" <?= $num === $selectedBulan ? 'selected' : '' ?>>
                                <?= $namaBulan ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                </div>

                <div class="col-md-3">
                    <label class="form-label small fw-semibold mb-1">Tahun</label>
                    <input type="number" name="tahun" class="form-control form-control-sm" value="<?= $selectedTahun ?>" min="2020" max="2035" onchange="this.form.submit()">
                </div>
            </form>
        </div>
    </div>

    <!-- Matrix Card -->
    <div class="card shadow-sm border-0">
        <div class="card-header bg-white py-2 fw-bold d-flex justify-content-between align-items-center">
            <span>
                <i class="bi bi-grid me-1 text-primary"></i>
                Rekap Kehadiran Bulan <?= $months[$selectedBulan] ?? $selectedBulan ?> <?= $selectedTahun ?>
            </span>
            <span class="badge bg-light text-secondary border">
                <?= count($meetings) ?> Kali Pertemuan KBM
            </span>
        </div>
        <div class="card-body p-0">
            <div class="table-responsive">
                <table class="table table-bordered table-hover align-middle mb-0 text-center" style="font-size: 13px;">
                    <thead class="table-light">
                        <tr>
                            <th style="width: 40px;">No</th>
                            <th style="width: 100px;">NIS</th>
                            <th class="text-start" style="min-width: 180px;">Nama Siswa</th>
                            <th style="width: 50px;">L/P</th>
                            <?php if (empty($meetings)): ?>
                                <th class="text-muted fst-italic">Belum ada pertemuan di bulan ini</th>
                            <?php else: ?>
                                <?php foreach ($meetings as $m): ?>
                                    <th style="min-width: 65px;" title="<?= escape_html($m['judul_materi']) ?>">
                                        <div class="fw-bold"><?= date('d/m', strtotime($m['jadwal_materi'])) ?></div>
                                        <small class="text-muted" style="font-size: 10px;">
                                            <?= (int)$m['jenis'] === 1 ? 'Mat' : 'Tgs' ?>
                                        </small>
                                    </th>
                                <?php endforeach; ?>
                            <?php endif; ?>
                            <th style="width: 80px;" class="table-info">Hadir</th>
                            <th style="width: 80px;" class="table-info">Total</th>
                            <th style="width: 90px;" class="table-success">Persen</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if (empty($studentsRekap)): ?>
                            <tr>
                                <td colspan="<?= 7 + count($meetings) ?>" class="py-4 text-muted">
                                    Tidak ada data siswa untuk kelas ini.
                                </td>
                            </tr>
                        <?php else: ?>
                            <?php $no = 1; foreach ($studentsRekap as $sr): ?>
                                <?php $stu = $sr['siswa']; ?>
                                <tr>
                                    <td><?= $no++ ?></td>
                                    <td class="font-monospace"><?= escape_html($stu['nis']) ?></td>
                                    <td class="text-start fw-semibold text-dark"><?= escape_html($stu['nama']) ?></td>
                                    <td><?= escape_html($stu['jk'] ?? '-') ?></td>
                                    <?php if (empty($meetings)): ?>
                                        <td class="text-muted">-</td>
                                    <?php else: ?>
                                        <?php foreach ($meetings as $m): ?>
                                            <?php $st = $sr['meeting_status'][$m['id_kjm']] ?? ['hadir' => false]; ?>
                                            <td>
                                                <?php if ($st['hadir']): ?>
                                                    <span class="badge bg-success" title="Hadir: <?= $st['log_time'] ?? '' ?>">H</span>
                                                <?php else: ?>
                                                    <span class="text-muted">-</span>
                                                <?php endif; ?>
                                            </td>
                                        <?php endforeach; ?>
                                    <?php endif; ?>
                                    <td class="table-info fw-bold text-success"><?= $sr['total_hadir'] ?></td>
                                    <td class="table-info text-secondary"><?= $sr['total_pertemuan'] ?></td>
                                    <td class="table-success fw-bold text-dark"><?= $sr['persentase'] ?>%</td>
                                </tr>
                            <?php endforeach; ?>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>

