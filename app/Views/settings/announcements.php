<div class="d-flex flex-wrap justify-content-between align-items-center mb-4">
    <div>
        <h4 class="fw-bold mb-1 text-dark">Pengumuman & Running Text</h4>
        <p class="text-muted small mb-0">Kelola teks berjalan (running text ticker) yang tampil pada dashboard pengguna.</p>
    </div>
    <button class="btn btn-primary btn-sm d-flex align-items-center gap-1" data-bs-toggle="modal" data-bs-target="#addTextModal">
        <i class="bi bi-megaphone"></i> Tambah Pengumuman
    </button>
</div>

<div class="card border-0 shadow-sm rounded-3">
    <div class="card-header bg-white border-bottom py-3 d-flex justify-content-between align-items-center">
        <h6 class="fw-bold mb-0 text-dark"><i class="bi bi-broadcast text-primary me-2"></i> Daftar Teks Pengumuman</h6>
        <span class="badge bg-primary-subtle text-primary border"><?= count($list) ?> Pengumuman</span>
    </div>
    <div class="card-body p-0">
        <div class="table-responsive">
            <table class="table table-hover align-middle mb-0 datatable">
                <thead class="table-light">
                    <tr>
                        <th style="width: 60px;">No</th>
                        <th>Isi Teks Pengumuman</th>
                        <th>Target Sasaran</th>
                        <th>Status Tayang</th>
                        <th class="text-end" style="width: 150px;">Aksi</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if (empty($list)): ?>
                        <tr>
                            <td colspan="5" class="text-center py-4 text-muted">Belum ada pengumuman running text.</td>
                        </tr>
                    <?php else: ?>
                        <?php foreach ($list as $idx => $t): ?>
                            <tr>
                                <td><?= $idx + 1 ?></td>
                                <td>
                                    <div class="fw-semibold text-dark"><?= escape_html($t['text']) ?></div>
                                </td>
                                <td>
                                    <?php if ($t['tujuan'] === 'semua'): ?>
                                        <span class="badge bg-primary">Semua Pengguna</span>
                                    <?php elseif ($t['tujuan'] === 'guru'): ?>
                                        <span class="badge bg-info text-dark">Khusus Guru</span>
                                    <?php else: ?>
                                        <span class="badge bg-success">Khusus Siswa</span>
                                    <?php endif; ?>
                                </td>
                                <td>
                                    <?php if ((int)$t['active'] === 1): ?>
                                        <span class="badge bg-success"><i class="bi bi-check2"></i> Tayang</span>
                                    <?php else: ?>
                                        <span class="badge bg-secondary">Mati</span>
                                    <?php endif; ?>
                                </td>
                                <td class="text-end">
                                    <a href="<?= base_url('/settings/announcements/toggle/' . $t['id_text']) ?>" class="btn btn-outline-secondary btn-sm py-1 px-2" title="Aktif/Nonaktif">
                                        <i class="bi <?= (int)$t['active'] === 1 ? 'bi-toggle-on text-success' : 'bi-toggle-off text-muted' ?>"></i>
                                    </a>
                                    <button class="btn btn-outline-primary btn-sm py-1 px-2 ms-1" data-bs-toggle="modal" data-bs-target="#editTextModal<?= $t['id_text'] ?>" title="Edit">
                                        <i class="bi bi-pencil"></i>
                                    </button>
                                    <a href="<?= base_url('/settings/announcements/delete/' . $t['id_text']) ?>" class="btn btn-outline-danger btn-sm py-1 px-2 ms-1" onclick="return confirm('Hapus pengumuman ini?')" title="Hapus">
                                        <i class="bi bi-trash"></i>
                                    </a>
                                </td>
                            </tr>

                            <!-- Modal Edit Text -->
                            <div class="modal fade" id="editTextModal<?= $t['id_text'] ?>" tabindex="-1" aria-hidden="true">
                                <div class="modal-dialog modal-dialog-centered">
                                    <div class="modal-content border-0 shadow">
                                        <div class="modal-header bg-primary text-white">
                                            <h6 class="modal-title fw-bold"><i class="bi bi-pencil me-1"></i> Edit Pengumuman</h6>
                                            <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
                                        </div>
                                        <form action="<?= base_url('/settings/announcements/update/' . $t['id_text']) ?>" method="POST">
                                            <div class="modal-body">
                                                <div class="mb-3">
                                                    <label class="form-label small fw-semibold">Isi Teks Pengumuman</label>
                                                    <textarea name="text" class="form-control" rows="3" required><?= escape_html($t['text']) ?></textarea>
                                                </div>
                                                <div class="mb-3">
                                                    <label class="form-label small fw-semibold">Target Penerima</label>
                                                    <select name="tujuan" class="form-select">
                                                        <option value="semua" <?= $t['tujuan'] === 'semua' ? 'selected' : '' ?>>Semua Pengguna (Umum)</option>
                                                        <option value="guru" <?= $t['tujuan'] === 'guru' ? 'selected' : '' ?>>Khusus Guru</option>
                                                        <option value="siswa" <?= $t['tujuan'] === 'siswa' ? 'selected' : '' ?>>Khusus Siswa</option>
                                                    </select>
                                                </div>
                                                <div class="form-check form-switch">
                                                    <input class="form-check-input" type="checkbox" name="active" value="1" id="activeEdit_<?= $t['id_text'] ?>" <?= (int)$t['active'] === 1 ? 'checked' : '' ?>>
                                                    <label class="form-check-label small fw-semibold" for="activeEdit_<?= $t['id_text'] ?>">Aktifkan Tayang di Ticker</label>
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

<!-- Modal Tambah Pengumuman -->
<div class="modal fade" id="addTextModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content border-0 shadow">
            <div class="modal-header bg-primary text-white">
                <h6 class="modal-title fw-bold"><i class="bi bi-megaphone me-1"></i> Tambah Pengumuman Baru</h6>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
            </div>
            <form action="<?= base_url('/settings/announcements/store') ?>" method="POST">
                <div class="modal-body">
                    <div class="mb-3">
                        <label class="form-label small fw-semibold">Isi Teks Pengumuman</label>
                        <textarea name="text" class="form-control" rows="3" placeholder="Tuliskan isi pengumuman yang akan berjalan pada ticker beranda..." required></textarea>
                    </div>
                    <div class="mb-3">
                        <label class="form-label small fw-semibold">Target Penerima</label>
                        <select name="tujuan" class="form-select">
                            <option value="semua">Semua Pengguna (Umum)</option>
                            <option value="guru">Khusus Guru</option>
                            <option value="siswa">Khusus Siswa</option>
                        </select>
                    </div>
                    <div class="form-check form-switch">
                        <input class="form-check-input" type="checkbox" name="active" value="1" id="activeAddCheck" checked>
                        <label class="form-check-label small fw-semibold" for="activeAddCheck">Langsung Aktifkan Tayang di Ticker</label>
                    </div>
                </div>
                <div class="modal-footer bg-light py-2">
                    <button type="button" class="btn btn-sm btn-secondary" data-bs-dismiss="modal">Batal</button>
                    <button type="submit" class="btn btn-sm btn-primary">Simpan Pengumuman</button>
                </div>
            </form>
        </div>
    </div>
</div>

