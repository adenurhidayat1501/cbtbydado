<div class="card shadow-sm border-0 mb-4">
    <div class="card-header bg-white py-3 d-flex align-items-center justify-content-between flex-wrap gap-2">
        <h5 class="card-title mb-0"><i class="bi bi-book me-2 text-primary"></i>Buku Induk Siswa & Riwayat Alumni</h5>
    </div>
    <div class="card-body">
        <?php if (!empty($_SESSION['flash_success'])): ?>
            <div class="alert alert-success alert-dismissible fade show" role="alert">
                <i class="bi bi-check-circle me-2"></i><?= htmlspecialchars($_SESSION['flash_success'], ENT_QUOTES, 'UTF-8') ?>
                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
            </div>
            <?php unset($_SESSION['flash_success']); ?>
        <?php endif; ?>

        <?php if (!empty($_SESSION['flash_error'])): ?>
            <div class="alert alert-danger alert-dismissible fade show" role="alert">
                <i class="bi bi-exclamation-triangle me-2"></i><?= htmlspecialchars($_SESSION['flash_error'], ENT_QUOTES, 'UTF-8') ?>
                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
            </div>
            <?php unset($_SESSION['flash_error']); ?>
        <?php endif; ?>

        <form method="GET" action="<?= base_url('/rapor/buku-induk') ?>" class="row g-3 align-items-center mb-4">
            <div class="col-md-4">
                <label class="form-label fw-bold mb-1">Filter Rombel / Kelas:</label>
                <select name="id_kelas" class="form-select" onchange="this.form.submit()">
                    <option value="">-- Semua Kelas --</option>
                    <?php foreach ($kelasList as $k): ?>
                        <option value="<?= $k['id_kelas'] ?>" <?= (int)$selectedKelas === (int)$k['id_kelas'] ? 'selected' : '' ?>>
                            <?= htmlspecialchars($k['nama_kelas'], ENT_QUOTES, 'UTF-8') ?>
                        </option>
                    <?php endforeach; ?>
                </select>
            </div>
            <div class="col-md-4">
                <label class="form-label fw-bold mb-1">Status Siswa:</label>
                <select name="status" class="form-select" onchange="this.form.submit()">
                    <option value="">-- Semua Status --</option>
                    <option value="1" <?= $selectedStatus === 1 ? 'selected' : '' ?>>Aktif Belajar</option>
                    <option value="2" <?= $selectedStatus === 2 ? 'selected' : '' ?>>Alumni (Lulus)</option>
                    <option value="3" <?= $selectedStatus === 3 ? 'selected' : '' ?>>Pindah / Keluar</option>
                </select>
            </div>
        </form>

        <div class="table-responsive">
            <table class="table table-bordered table-hover align-middle">
                <thead class="table-light text-center">
                    <tr>
                        <th style="width: 40px;">No</th>
                        <th style="width: 100px;">NIS</th>
                        <th style="width: 110px;">NISN</th>
                        <th>Nama Siswa</th>
                        <th style="width: 110px;">Kelas</th>
                        <th style="width: 110px;">Status</th>
                        <th style="width: 120px;">Tahun Lulus</th>
                        <th style="width: 140px;">No. Ijazah</th>
                        <th style="width: 200px;">Aksi</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if (!empty($students)): ?>
                        <?php $no = 1; foreach ($students as $s): ?>
                            <tr>
                                <td class="text-center"><?= $no++ ?></td>
                                <td class="text-center font-monospace"><?= htmlspecialchars($s['nis'] ?? '-', ENT_QUOTES, 'UTF-8') ?></td>
                                <td class="text-center font-monospace"><?= htmlspecialchars($s['nisn'] ?? '-', ENT_QUOTES, 'UTF-8') ?></td>
                                <td>
                                    <strong><?= htmlspecialchars($s['nama'], ENT_QUOTES, 'UTF-8') ?></strong>
                                </td>
                                <td class="text-center"><?= htmlspecialchars($s['nama_kelas'] ?? '-', ENT_QUOTES, 'UTF-8') ?></td>
                                <td class="text-center">
                                    <?php if ((int)$s['status_buku'] === 2): ?>
                                        <span class="badge bg-success">Alumni (Lulus)</span>
                                    <?php elseif ((int)$s['status_buku'] === 3): ?>
                                        <span class="badge bg-danger">Pindah</span>
                                    <?php else: ?>
                                        <span class="badge bg-primary">Aktif</span>
                                    <?php endif; ?>
                                </td>
                                <td class="text-center"><?= htmlspecialchars($s['tahun_lulus'] ?? '-', ENT_QUOTES, 'UTF-8') ?></td>
                                <td class="text-center font-monospace"><?= htmlspecialchars($s['no_ijazah'] ?? '-', ENT_QUOTES, 'UTF-8') ?></td>
                                <td class="text-center">
                                    <div class="btn-group btn-group-sm">
                                        <a href="<?= base_url('/rapor/buku-induk/detail/' . $s['id_siswa']) ?>" class="btn btn-outline-primary" title="Buka Detail Buku Induk">
                                            <i class="bi bi-pencil-square"></i>
                                        </a>
                                        <a href="<?= base_url('/rapor/buku-induk/cetak/' . $s['id_siswa']) ?>" target="_blank" class="btn btn-outline-dark" title="Cetak Lembar Buku Induk">
                                            <i class="bi bi-printer"></i>
                                        </a>
                                        <button type="button" class="btn btn-outline-success" data-bs-toggle="modal" data-bs-target="#modalStatus<?= $s['id_siswa'] ?>" title="Update Status Alumni">
                                            <i class="bi bi-mortarboard"></i>
                                        </button>
                                    </div>

                                    <!-- Modal Update Status Alumni -->
                                    <div class="modal fade" id="modalStatus<?= $s['id_siswa'] ?>" tabindex="-1">
                                        <div class="modal-dialog">
                                            <div class="modal-content text-start">
                                                <div class="modal-header">
                                                    <h6 class="modal-title fw-bold">Update Status Siswa & Alumni</h6>
                                                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                                                </div>
                                                <form action="<?= base_url('/rapor/buku-induk/alumni-status') ?>" method="POST">
                                                    <input type="hidden" name="id_siswa" value="<?= $s['id_siswa'] ?>">
                                                    <div class="modal-body">
                                                        <div class="mb-3">
                                                            <label class="form-label fw-bold">Nama Siswa</label>
                                                            <input type="text" class="form-control" value="<?= htmlspecialchars($s['nama'], ENT_QUOTES, 'UTF-8') ?>" disabled>
                                                        </div>
                                                        <div class="mb-3">
                                                            <label class="form-label fw-bold">Status Keberadaan</label>
                                                            <select name="status" class="form-select">
                                                                <option value="1" <?= (int)$s['status_buku'] === 1 ? 'selected' : '' ?>>Aktif Belajar</option>
                                                                <option value="2" <?= (int)$s['status_buku'] === 2 ? 'selected' : '' ?>>Alumni (Tamat / Lulus)</option>
                                                                <option value="3" <?= (int)$s['status_buku'] === 3 ? 'selected' : '' ?>>Pindah Sekolah</option>
                                                            </select>
                                                        </div>
                                                        <div class="mb-3">
                                                            <label class="form-label fw-bold">Tahun Lulus (jika sudah alumni)</label>
                                                            <input type="text" name="tahun_lulus" class="form-control" value="<?= htmlspecialchars($s['tahun_lulus'] ?? '', ENT_QUOTES, 'UTF-8') ?>" placeholder="Contoh: 2024">
                                                        </div>
                                                        <div class="mb-3">
                                                            <label class="form-label fw-bold">Nomor Seri Ijazah</label>
                                                            <input type="text" name="no_ijazah" class="form-control" value="<?= htmlspecialchars($s['no_ijazah'] ?? '', ENT_QUOTES, 'UTF-8') ?>" placeholder="Contoh: DN-01/D-SMK/13/0012345">
                                                        </div>
                                                    </div>
                                                    <div class="modal-footer">
                                                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Batal</button>
                                                        <button type="submit" class="btn btn-primary"><i class="bi bi-save me-1"></i>Simpan Perubahan</button>
                                                    </div>
                                                </form>
                                            </div>
                                        </div>
                                    </div>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    <?php else: ?>
                        <tr><td colspan="9" class="text-center py-4 text-muted">Tidak ada siswa yang sesuai filter.</td></tr>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>

