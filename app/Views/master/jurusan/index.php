<div class="d-flex flex-wrap justify-content-between align-items-center mb-4">
    <div>
        <h4 class="fw-bold mb-1 text-dark">Jurusan & Program Keahlian</h4>
        <p class="text-muted small mb-0">Kelola daftar peminatan akademik dan kompetensi keahlian peserta didik.</p>
    </div>
    <button class="btn btn-primary btn-sm d-flex align-items-center gap-1" data-bs-toggle="modal" data-bs-target="#addJurusanModal">
        <i class="bi bi-plus-circle"></i> Tambah Jurusan
    </button>
</div>

<div class="card border-0 shadow-sm rounded-3">
    <div class="card-header bg-white border-bottom py-3">
        <h6 class="fw-bold mb-0 text-dark"><i class="bi bi-diagram-3 text-primary me-2"></i> Daftar Jurusan Aktif</h6>
    </div>
    <div class="card-body p-0">
        <div class="table-responsive">
            <table class="table table-hover align-middle mb-0">
                <thead class="table-light">
                    <tr>
                        <th style="width: 60px;">No</th>
                        <th>Kode Jurusan</th>
                        <th>Nama Program / Jurusan</th>
                        <th>Status</th>
                        <th class="text-end" style="width: 150px;">Aksi</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if (empty($list)): ?>
                        <tr>
                            <td colspan="5" class="text-center py-4 text-muted">Belum ada jurusan yang ditambahkan.</td>
                        </tr>
                    <?php else: ?>
                        <?php foreach ($list as $idx => $jur): ?>
                            <tr>
                                <td><?= $idx + 1 ?></td>
                                <td><span class="badge bg-light text-dark border font-monospace"><?= escape_html($jur['kode_jurusan']) ?></span></td>
                                <td class="fw-semibold text-dark"><?= escape_html($jur['nama_jurusan']) ?></td>
                                <td>
                                    <?php if ((int)$jur['status'] === 1): ?>
                                        <span class="badge bg-success">Aktif</span>
                                    <?php else: ?>
                                        <span class="badge bg-secondary">Nonaktif</span>
                                    <?php endif; ?>
                                </td>
                                <td class="text-end">
                                    <button class="btn btn-sm btn-outline-primary py-1 px-2" data-bs-toggle="modal" data-bs-target="#editModal<?= $jur['id_jurusan'] ?>" title="Edit">
                                        <i class="bi bi-pencil"></i>
                                    </button>
                                    <a href="<?= base_url('/master/jurusan/delete/' . $jur['id_jurusan']) ?>" class="btn btn-sm btn-outline-danger py-1 px-2 ms-1" onclick="return confirm('Hapus jurusan ini?')" title="Hapus">
                                        <i class="bi bi-trash"></i>
                                    </a>
                                </td>
                            </tr>

                            <!-- Edit Modal -->
                            <div class="modal fade" id="editModal<?= $jur['id_jurusan'] ?>" tabindex="-1" aria-hidden="true">
                                <div class="modal-dialog modal-dialog-centered">
                                    <div class="modal-content border-0 shadow">
                                        <div class="modal-header bg-primary text-white">
                                            <h6 class="modal-title fw-bold"><i class="bi bi-pencil me-1"></i> Edit Jurusan</h6>
                                            <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
                                        </div>
                                        <form action="<?= base_url('/master/jurusan/update/' . $jur['id_jurusan']) ?>" method="POST">
                                            <div class="modal-body">
                                                <div class="mb-3">
                                                    <label class="form-label small fw-semibold">Kode Jurusan</label>
                                                    <input type="text" name="kode_jurusan" class="form-control" value="<?= escape_html($jur['kode_jurusan']) ?>" required>
                                                </div>
                                                <div class="mb-3">
                                                    <label class="form-label small fw-semibold">Nama Jurusan</label>
                                                    <input type="text" name="nama_jurusan" class="form-control" value="<?= escape_html($jur['nama_jurusan']) ?>" required>
                                                </div>
                                                <div class="mb-3">
                                                    <label class="form-label small fw-semibold">Status</label>
                                                    <select name="status" class="form-select">
                                                        <option value="1" <?= (int)$jur['status'] === 1 ? 'selected' : '' ?>>Aktif</option>
                                                        <option value="0" <?= (int)$jur['status'] === 0 ? 'selected' : '' ?>>Nonaktif</option>
                                                    </select>
                                                </div>
                                            </div>
                                            <div class="modal-footer bg-light py-2">
                                                <button type="button" class="btn btn-sm btn-secondary" data-bs-dismiss="modal">Batal</button>
                                                <button type="submit" class="btn btn-sm btn-primary">Simpan Perubahan</button>
                                            </div>
                                        </form>
                                    </div>
                                </div>
                            </div>
                        <?php endforeach; ?>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>

<!-- Modal Tambah Jurusan -->
<div class="modal fade" id="addJurusanModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content border-0 shadow">
            <div class="modal-header bg-primary text-white">
                <h6 class="modal-title fw-bold"><i class="bi bi-plus-circle me-1"></i> Tambah Jurusan Baru</h6>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
            </div>
            <form action="<?= base_url('/master/jurusan/store') ?>" method="POST">
                <div class="modal-body">
                    <div class="mb-3">
                        <label class="form-label small fw-semibold">Kode Jurusan</label>
                        <input type="text" name="kode_jurusan" class="form-control" placeholder="Contoh: MIPA, IPS, TKJ, RPL" required>
                    </div>
                    <div class="mb-3">
                        <label class="form-label small fw-semibold">Nama Jurusan / Program Keahlian</label>
                        <input type="text" name="nama_jurusan" class="form-control" placeholder="Contoh: Matematika dan Ilmu Pengetahuan Alam" required>
                    </div>
                </div>
                <div class="modal-footer bg-light py-2">
                    <button type="button" class="btn btn-sm btn-secondary" data-bs-dismiss="modal">Batal</button>
                    <button type="submit" class="btn btn-sm btn-primary">Simpan Jurusan</button>
                </div>
            </form>
        </div>
    </div>
</div>

