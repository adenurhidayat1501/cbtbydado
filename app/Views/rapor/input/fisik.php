<div class="card shadow-sm border-0 mb-4">
    <div class="card-header bg-white py-3 d-flex align-items-center justify-content-between">
        <h5 class="card-title mb-0"><i class="bi bi-activity me-2 text-secondary"></i>Kondisi Kesehatan & Fisik Siswa</h5>
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

        <form method="GET" action="<?= base_url('/rapor/input-nilai/fisik') ?>" class="row g-3 align-items-center mb-4">
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
            <form action="<?= base_url('/rapor/input-nilai/fisik/save') ?>" method="POST">
                <input type="hidden" name="id_kelas" value="<?= $selectedKelas ?>">

                <div class="table-responsive">
                    <table class="table table-bordered table-hover align-middle">
                        <thead class="table-light">
                            <tr class="text-center">
                                <th style="width: 40px;">No</th>
                                <th style="width: 110px;">NIS</th>
                                <th>Nama Siswa</th>
                                <th style="width: 100px;">Tinggi (cm)</th>
                                <th style="width: 100px;">Berat (kg)</th>
                                <th style="width: 130px;">Pendengaran</th>
                                <th style="width: 130px;">Penglihatan</th>
                                <th style="width: 130px;">Gigi</th>
                                <th>Kelainan Lainnya</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php $no = 1; foreach ($students as $row): ?>
                                <tr>
                                    <td class="text-center"><?= $no++ ?></td>
                                    <td class="text-center font-monospace"><?= htmlspecialchars($row['nis'] ?? '-', ENT_QUOTES, 'UTF-8') ?></td>
                                    <td><strong><?= htmlspecialchars($row['nama_siswa'], ENT_QUOTES, 'UTF-8') ?></strong></td>
                                    <td>
                                        <input type="number" name="fisik[<?= $row['id_siswa'] ?>][tinggi]" class="form-control form-control-sm text-center" value="<?= $row['tinggi'] ?? '' ?>" min="50" max="250" placeholder="cm">
                                    </td>
                                    <td>
                                        <input type="number" name="fisik[<?= $row['id_siswa'] ?>][berat]" class="form-control form-control-sm text-center" value="<?= $row['berat'] ?? '' ?>" min="10" max="200" placeholder="kg">
                                    </td>
                                    <td>
                                        <select name="fisik[<?= $row['id_siswa'] ?>][pendengaran]" class="form-select form-select-sm">
                                            <option value="Baik" <?= ($row['pendengaran'] ?? 'Baik') === 'Baik' ? 'selected' : '' ?>>Baik</option>
                                            <option value="Kurang" <?= ($row['pendengaran'] ?? '') === 'Kurang' ? 'selected' : '' ?>>Kurang</option>
                                        </select>
                                    </td>
                                    <td>
                                        <select name="fisik[<?= $row['id_siswa'] ?>][penglihatan]" class="form-select form-select-sm">
                                            <option value="Baik" <?= ($row['penglihatan'] ?? 'Baik') === 'Baik' ? 'selected' : '' ?>>Baik</option>
                                            <option value="Kurang" <?= ($row['penglihatan'] ?? '') === 'Kurang' ? 'selected' : '' ?>>Kurang</option>
                                        </select>
                                    </td>
                                    <td>
                                        <select name="fisik[<?= $row['id_siswa'] ?>][gigi]" class="form-select form-select-sm">
                                            <option value="Baik" <?= ($row['gigi'] ?? 'Baik') === 'Baik' ? 'selected' : '' ?>>Baik</option>
                                            <option value="Kurang" <?= ($row['gigi'] ?? '') === 'Kurang' ? 'selected' : '' ?>>Kurang</option>
                                        </select>
                                    </td>
                                    <td>
                                        <input type="text" name="fisik[<?= $row['id_siswa'] ?>][lainnya]" class="form-control form-control-sm" value="<?= htmlspecialchars($row['lainnya'] ?? '', ENT_QUOTES, 'UTF-8') ?>" placeholder="-">
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>

                <div class="text-end mt-3">
                    <button type="submit" class="btn btn-secondary px-4"><i class="bi bi-save me-1"></i>Simpan Kondisi Fisik</button>
                </div>
            </form>
        <?php else: ?>
            <div class="alert alert-info text-center py-4">Belum ada siswa yang terdaftar pada kelas ini.</div>
        <?php endif; ?>
    </div>
</div>

