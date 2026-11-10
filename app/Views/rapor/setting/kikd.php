<div class="row">
    <div class="col-md-4">
        <div class="card shadow-sm border-0 mb-4">
            <div class="card-header bg-white py-3">
                <h6 class="card-title mb-0 fw-bold"><i class="bi bi-plus-circle me-1 text-primary"></i>Tambah Butir KI / KD</h6>
            </div>
            <form action="<?= base_url('/rapor/kikd/save') ?>" method="POST">
                <div class="card-body">
                    <div class="mb-3">
                        <label class="form-label fw-bold">Mata Pelajaran</label>
                        <select name="id_mapel_kelas" class="form-select" required>
                            <?php foreach ($mapelList as $m): ?>
                                <option value="<?= $m['id_mapel'] ?>" <?= (int)$selectedMapel === (int)$m['id_mapel'] ? 'selected' : '' ?>>
                                    <?= htmlspecialchars($m['nama_mapel'], ENT_QUOTES, 'UTF-8') ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                    </div>

                    <div class="mb-3">
                        <label class="form-label fw-bold">Aspek Kompetensi</label>
                        <select name="aspek" class="form-select" required>
                            <option value="1" <?= (int)$selectedAspek === 1 ? 'selected' : '' ?>>KI-3 (Pengetahuan)</option>
                            <option value="2" <?= (int)$selectedAspek === 2 ? 'selected' : '' ?>>KI-4 (Keterampilan)</option>
                        </select>
                    </div>

                    <div class="mb-3">
                        <label class="form-label fw-bold">Nomor KD</label>
                        <input type="text" name="nomor_kikd" class="form-control" placeholder="Contoh: 3.1 atau 4.1" required>
                    </div>

                    <div class="mb-3">
                        <label class="form-label fw-bold">Isi Ringkas Kompetensi / Materi</label>
                        <textarea name="isi_kikd" class="form-control" rows="4" placeholder="Uraian kompetensi dasar yang dipelajari..." required></textarea>
                        <small class="text-muted">Akan ditampilkan pada deskripsi capaian otomatis di lembar rapor.</small>
                    </div>
                </div>
                <div class="card-footer bg-light text-end">
                    <button type="submit" class="btn btn-primary"><i class="bi bi-save me-1"></i>Simpan KI/KD</button>
                </div>
            </form>
        </div>
    </div>

    <div class="col-md-8">
        <div class="card shadow-sm border-0 mb-4">
            <div class="card-header bg-white py-3 d-flex align-items-center justify-content-between">
                <h5 class="card-title mb-0"><i class="bi bi-bookmark-check me-2 text-primary"></i>Daftar Capaian KI / KD</h5>
            </div>
            <div class="card-body">
                <?php if (!empty($_SESSION['flash_success'])): ?>
                    <div class="alert alert-success alert-dismissible fade show" role="alert">
                        <i class="bi bi-check-circle me-2"></i><?= htmlspecialchars($_SESSION['flash_success'], ENT_QUOTES, 'UTF-8') ?>
                        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                    </div>
                    <?php unset($_SESSION['flash_success']); ?>
                <?php endif; ?>

                <form method="GET" action="<?= base_url('/rapor/kikd') ?>" class="row g-2 mb-4">
                    <div class="col-md-6">
                        <select name="id_mapel" class="form-select" onchange="this.form.submit()">
                            <?php foreach ($mapelList as $m): ?>
                                <option value="<?= $m['id_mapel'] ?>" <?= (int)$selectedMapel === (int)$m['id_mapel'] ? 'selected' : '' ?>>
                                    <?= htmlspecialchars($m['nama_mapel'], ENT_QUOTES, 'UTF-8') ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    <div class="col-md-6">
                        <select name="aspek" class="form-select" onchange="this.form.submit()">
                            <option value="1" <?= (int)$selectedAspek === 1 ? 'selected' : '' ?>>KI-3 (Pengetahuan)</option>
                            <option value="2" <?= (int)$selectedAspek === 2 ? 'selected' : '' ?>>KI-4 (Keterampilan)</option>
                        </select>
                    </div>
                </form>

                <div class="table-responsive">
                    <table class="table table-bordered table-hover align-middle">
                        <thead class="table-light">
                            <tr>
                                <th style="width: 100px;" class="text-center">Nomor KD</th>
                                <th>Uraian Kompetensi Dasar</th>
                                <th style="width: 80px;" class="text-center">Aksi</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php if (!empty($kikdList)): ?>
                                <?php foreach ($kikdList as $kd): ?>
                                    <tr>
                                        <td class="text-center fw-bold"><?= htmlspecialchars($kd['nomor_kikd'], ENT_QUOTES, 'UTF-8') ?></td>
                                        <td><?= nl2br(htmlspecialchars($kd['isi_kikd'], ENT_QUOTES, 'UTF-8')) ?></td>
                                        <td class="text-center">
                                            <a href="<?= base_url('/rapor/kikd/delete/' . $kd['id_kikd']) ?>" class="btn btn-sm btn-outline-danger" onclick="return confirm('Hapus butir KI/KD ini?')">
                                                <i class="bi bi-trash"></i>
                                            </a>
                                        </td>
                                    </tr>
                                <?php endforeach; ?>
                            <?php else: ?>
                                <tr>
                                    <td colspan="3" class="text-center py-4 text-muted">Belum ada butir KI/KD yang ditambahkan untuk mapel ini.</td>
                                </tr>
                            <?php endif; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
</div>

