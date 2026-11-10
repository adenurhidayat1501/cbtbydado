<div class="card shadow-sm border-0 mb-4">
    <div class="card-header bg-white py-3 d-flex align-items-center justify-content-between">
        <h5 class="card-title mb-0"><i class="bi bi-star me-2 text-danger"></i>Prestasi & Penghargaan Siswa</h5>
        <a href="<?= base_url('/rapor/input-nilai') ?>" class="btn btn-sm btn-outline-secondary">
            <i class="bi bi-arrow-left me-1"></i>Kembali
        </a>
    </div>
    <div class="card-body">
        <?php if (!empty($_SESSION['flash_success'])): ?>
            <div class="alert alert-success alert-dismissible fade show" role="alert">
                <i class="bi bi-check-circle me-2"></i><?= htmlspecialchars($_SESSION['flash_success'], ENT_QUOTES, 'UTF-8') ?>
                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
            </div>
            <?php unset($_SESSION['flash_success']); ?>
        <?php endif; ?>

        <form method="GET" action="<?= base_url('/rapor/input-nilai/prestasi') ?>" class="row g-3 align-items-center mb-4">
            <div class="col-md-5">
                <label class="form-label fw-bold mb-1">Pilih Kelas / Rombel:</label>
                <select name="id_kelas" class="form-select" onchange="this.form.submit()">
                    <?php foreach ($kelasList as $k): ?>
                        <option value="<?= $k['id_kelas'] ?>" <?= (int)$selectedKelas === (int)$k['id_kelas'] ? 'selected' : '' ?>>
                            <?= htmlspecialchars($k['nama_kelas'], ENT_QUOTES, 'UTF-8') ?>
                        </option>
                    <?php endforeach; ?>
                </select>
            </div>
        </form>

        <?php if (!empty($students)): ?>
            <form action="<?= base_url('/rapor/input-nilai/prestasi/save') ?>" method="POST">
                <input type="hidden" name="id_kelas" value="<?= $selectedKelas ?>">

                <div class="table-responsive">
                    <table class="table table-bordered table-hover align-middle">
                        <thead class="table-light">
                            <tr class="text-center">
                                <th style="width: 40px;">No</th>
                                <th style="width: 110px;">NIS</th>
                                <th style="width: 200px;">Nama Siswa</th>
                                <th style="width: 90px;">Ranking</th>
                                <th>Prestasi 1 (Nama Lomba / Keterangan)</th>
                                <th>Prestasi 2 (Nama Lomba / Keterangan)</th>
                                <th>Prestasi 3 (Nama Lomba / Keterangan)</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php $no = 1; foreach ($students as $row): ?>
                                <tr>
                                    <td class="text-center"><?= $no++ ?></td>
                                    <td class="text-center font-monospace"><?= htmlspecialchars($row['nis'] ?? '-', ENT_QUOTES, 'UTF-8') ?></td>
                                    <td><strong><?= htmlspecialchars($row['nama_siswa'], ENT_QUOTES, 'UTF-8') ?></strong></td>
                                    <td>
                                        <input type="number" name="prestasi[<?= $row['id_siswa'] ?>][ranking]" class="form-control form-control-sm text-center" value="<?= $row['ranking'] ?? '' ?>" min="1" placeholder="-">
                                    </td>
                                    <td>
                                        <input type="text" name="prestasi[<?= $row['id_siswa'] ?>][p1]" class="form-control form-control-sm mb-1" value="<?= htmlspecialchars($row['p1'] ?? '', ENT_QUOTES, 'UTF-8') ?>" placeholder="Nama Prestasi/Kejuaraan">
                                        <input type="text" name="prestasi[<?= $row['id_siswa'] ?>][p1_desk]" class="form-control form-control-sm text-muted" value="<?= htmlspecialchars($row['p1_desk'] ?? '', ENT_QUOTES, 'UTF-8') ?>" placeholder="Tingkat/Keterangan">
                                    </td>
                                    <td>
                                        <input type="text" name="prestasi[<?= $row['id_siswa'] ?>][p2]" class="form-control form-control-sm mb-1" value="<?= htmlspecialchars($row['p2'] ?? '', ENT_QUOTES, 'UTF-8') ?>" placeholder="Nama Prestasi/Kejuaraan">
                                        <input type="text" name="prestasi[<?= $row['id_siswa'] ?>][p2_desk]" class="form-control form-control-sm text-muted" value="<?= htmlspecialchars($row['p2_desk'] ?? '', ENT_QUOTES, 'UTF-8') ?>" placeholder="Tingkat/Keterangan">
                                    </td>
                                    <td>
                                        <input type="text" name="prestasi[<?= $row['id_siswa'] ?>][p3]" class="form-control form-control-sm mb-1" value="<?= htmlspecialchars($row['p3'] ?? '', ENT_QUOTES, 'UTF-8') ?>" placeholder="Nama Prestasi/Kejuaraan">
                                        <input type="text" name="prestasi[<?= $row['id_siswa'] ?>][p3_desk]" class="form-control form-control-sm text-muted" value="<?= htmlspecialchars($row['p3_desk'] ?? '', ENT_QUOTES, 'UTF-8') ?>" placeholder="Tingkat/Keterangan">
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>

                <div class="text-end mt-3">
                    <button type="submit" class="btn btn-danger px-4"><i class="bi bi-save me-1"></i>Simpan Prestasi Siswa</button>
                </div>
            </form>
        <?php else: ?>
            <div class="alert alert-info text-center py-4">Belum ada siswa yang terdaftar pada kelas ini.</div>
        <?php endif; ?>
    </div>
</div>

