<div class="d-flex flex-wrap justify-content-between align-items-center mb-4">
    <div>
        <h4 class="fw-bold mb-1 text-dark">Data Ekstrakurikuler</h4>
        <p class="text-muted small mb-0">Kelola daftar kegiatan ekstrakurikuler peserta didik untuk penilaian rapor.</p>
    </div>
    <button class="btn btn-primary btn-sm d-flex align-items-center gap-1" data-bs-toggle="modal" data-bs-target="#addEkstraModal">
        <i class="bi bi-plus-circle"></i> Tambah Ekstrakurikuler
    </button>
</div>

<div class="card border-0 shadow-sm rounded-3">
    <div class="card-header bg-white border-bottom py-3">
        <h6 class="fw-bold mb-0 text-dark"><i class="bi bi-trophy text-primary me-2"></i> Daftar Kegiatan Ekstrakurikuler</h6>
    </div>
    <div class="card-body p-0">
        <div class="table-responsive">
            <table class="table table-hover align-middle mb-0 datatable">
                <thead class="table-light">
                    <tr>
                        <th style="width: 60px;">No</th>
                        <th>Kode Ekstrakurikuler</th>
                        <th>Nama Kegiatan Ekstrakurikuler</th>
                        <th class="text-end" style="width: 140px;">Aksi</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($list as $idx => $e): ?>
                        <tr>
                            <td><?= $idx + 1 ?></td>
                            <td><span class="badge bg-light text-dark border font-monospace"><?= escape_html($e['kode_ekstra']) ?></span></td>
                            <td class="fw-semibold text-dark"><?= escape_html($e['nama_ekstra']) ?></td>
                            <td class="text-end">
                                <button class="btn btn-sm btn-outline-primary py-1 px-2" data-bs-toggle="modal" data-bs-target="#editEkstraModal<?= $e['id_ekstra'] ?>" title="Edit">
                                    <i class="bi bi-pencil"></i>
                                </button>
                                <a href="<?= base_url('/master/ekstra/delete/' . $e['id_ekstra']) ?>" class="btn btn-sm btn-outline-danger py-1 px-2 ms-1" onclick="return confirm('Hapus kegiatan ekstra ini?')" title="Hapus">
                                    <i class="bi bi-trash"></i>
                                </a>
                            </td>
                        </tr>

                        <!-- Modal Edit Ekstra -->
                        <div class="modal fade" id="editEkstraModal<?= $e['id_ekstra'] ?>" tabindex="-1" aria-hidden="true">
                            <div class="modal-dialog modal-dialog-centered">
                                <div class="modal-content border-0 shadow">
                                    <div class="modal-header bg-primary text-white">
                                        <h6 class="modal-title fw-bold"><i class="bi bi-pencil me-1"></i> Edit Ekstrakurikuler</h6>
                                        <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
                                    </div>
                                    <form action="<?= base_url('/master/ekstra/update/' . $e['id_ekstra']) ?>" method="POST">
                                        <div class="modal-body">
                                            <div class="mb-3">
                                                <label class="form-label small fw-semibold">Kode Ekstrakurikuler</label>
                                                <input type="text" name="kode_ekstra" class="form-control" value="<?= escape_html($e['kode_ekstra']) ?>" required>
                                            </div>
                                            <div class="mb-3">
                                                <label class="form-label small fw-semibold">Nama Ekstrakurikuler</label>
                                                <input type="text" name="nama_ekstra" class="form-control" value="<?= escape_html($e['nama_ekstra']) ?>" required>
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
                </tbody>
            </table>
        </div>
    </div>
</div>

<!-- Modal Tambah Ekstra -->
<div class="modal fade" id="addEkstraModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content border-0 shadow">
            <div class="modal-header bg-primary text-white">
                <h6 class="modal-title fw-bold"><i class="bi bi-plus-circle me-1"></i> Tambah Ekstrakurikuler</h6>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
            </div>
            <form action="<?= base_url('/master/ekstra/store') ?>" method="POST">
                <div class="modal-body">
                    <div class="mb-3">
                        <label class="form-label small fw-semibold">Kode Ekstrakurikuler</label>
                        <input type="text" name="kode_ekstra" class="form-control" placeholder="Contoh: PRAM, PMR, BASKET" required>
                    </div>
                    <div class="mb-3">
                        <label class="form-label small fw-semibold">Nama Ekstrakurikuler</label>
                        <input type="text" name="nama_ekstra" class="form-control" placeholder="Contoh: Praja Muda Karana (Pramuka)" required>
                    </div>
                </div>
                <div class="modal-footer bg-light py-2">
                    <button type="button" class="btn btn-sm btn-secondary" data-bs-dismiss="modal">Batal</button>
                    <button type="submit" class="btn btn-sm btn-primary">Simpan Ekstrakurikuler</button>
                </div>
            </form>
        </div>
    </div>
</div>

