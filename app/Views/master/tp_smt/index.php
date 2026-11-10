<div class="d-flex flex-wrap justify-content-between align-items-center mb-4">
    <div>
        <h4 class="fw-bold mb-1 text-dark">Tahun Pelajaran & Semester</h4>
        <p class="text-muted small mb-0">Kelola dan tentukan periode akademik aktif yang mengikat seluruh rombel, materi, dan ujian.</p>
    </div>
    <button class="btn btn-primary btn-sm d-flex align-items-center gap-1" data-bs-toggle="modal" data-bs-target="#addTpModal">
        <i class="bi bi-plus-circle"></i> Tambah Tahun Pelajaran
    </button>
</div>

<div class="row g-4 mb-4">
    <!-- Active Status Card -->
    <div class="col-md-6">
        <div class="card border-0 shadow-sm rounded-3 p-3 bg-primary text-white">
            <div class="d-flex align-items-center justify-content-between">
                <div>
                    <span class="small opacity-75 text-uppercase fw-semibold">Tahun Pelajaran Aktif</span>
                    <h2 class="fw-bold mt-1 mb-0"><?= escape_html($activeTp['tahun'] ?? '-') ?></h2>
                    <small class="opacity-75"><i class="bi bi-check-circle-fill"></i> Digunakan oleh seluruh modul CBT</small>
                </div>
                <div class="bg-white bg-opacity-25 rounded-circle p-3 fs-3">
                    <i class="bi bi-calendar3"></i>
                </div>
            </div>
        </div>
    </div>

    <div class="col-md-6">
        <div class="card border-0 shadow-sm rounded-3 p-3 bg-info text-white">
            <div class="d-flex align-items-center justify-content-between">
                <div>
                    <span class="small opacity-75 text-uppercase fw-semibold">Semester Aktif</span>
                    <h2 class="fw-bold mt-1 mb-0">Semester <?= escape_html($activeSmt['smt'] ?? '-') ?> (<?= escape_html($activeSmt['nama_smt'] ?? '-') ?>)</h2>
                    <small class="opacity-75"><i class="bi bi-check-circle-fill"></i> Status Periode Berjalan</small>
                </div>
                <div class="bg-white bg-opacity-25 rounded-circle p-3 fs-3">
                    <i class="bi bi-clock-history"></i>
                </div>
            </div>
        </div>
    </div>
</div>

<div class="row g-4">
    <!-- Table Tahun Pelajaran -->
    <div class="col-lg-7">
        <div class="card border-0 shadow-sm rounded-3">
            <div class="card-header bg-white border-bottom py-3">
                <h6 class="fw-bold mb-0 text-dark"><i class="bi bi-calendar-range text-primary me-2"></i> Daftar Tahun Pelajaran</h6>
            </div>
            <div class="card-body p-0">
                <div class="table-responsive">
                    <table class="table table-hover align-middle mb-0">
                        <thead class="table-light">
                            <tr>
                                <th style="width: 60px;">No</th>
                                <th>Tahun Pelajaran</th>
                                <th>Status</th>
                                <th class="text-end" style="width: 180px;">Aksi</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($tpList as $idx => $tp): ?>
                                <tr>
                                    <td><?= $idx + 1 ?></td>
                                    <td class="fw-bold text-dark"><?= escape_html($tp['tahun']) ?></td>
                                    <td>
                                        <?php if ((int)$tp['active'] === 1): ?>
                                            <span class="badge bg-success"><i class="bi bi-check2"></i> Aktif</span>
                                        <?php else: ?>
                                            <span class="badge bg-secondary">Nonaktif</span>
                                        <?php endif; ?>
                                    </td>
                                    <td class="text-end">
                                        <?php if ((int)$tp['active'] !== 1): ?>
                                            <a href="<?= base_url('/master/tp/activate/' . $tp['id_tp']) ?>" class="btn btn-outline-success btn-sm py-1 px-2" title="Jadikan Aktif">
                                                <i class="bi bi-toggle-on me-1"></i> Aktifkan
                                            </a>
                                            <a href="<?= base_url('/master/tp/delete/' . $tp['id_tp']) ?>" class="btn btn-outline-danger btn-sm py-1 px-2 ms-1" onclick="return confirm('Yakin ingin menghapus Tahun Pelajaran ini?')" title="Hapus">
                                                <i class="bi bi-trash"></i>
                                            </a>
                                        <?php else: ?>
                                            <span class="badge bg-light text-muted border py-2 px-3">Periode Utama</span>
                                        <?php endif; ?>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>

    <!-- Table Semester -->
    <div class="col-lg-5">
        <div class="card border-0 shadow-sm rounded-3">
            <div class="card-header bg-white border-bottom py-3">
                <h6 class="fw-bold mb-0 text-dark"><i class="bi bi-layers text-info me-2"></i> Pilihan Semester</h6>
            </div>
            <div class="card-body p-0">
                <div class="table-responsive">
                    <table class="table table-hover align-middle mb-0">
                        <thead class="table-light">
                            <tr>
                                <th>Semester</th>
                                <th>Status</th>
                                <th class="text-end">Aksi</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($smtList as $smt): ?>
                                <tr>
                                    <td>
                                        <div class="fw-bold text-dark">Semester <?= escape_html($smt['smt']) ?></div>
                                        <small class="text-muted"><?= escape_html($smt['nama_smt']) ?></small>
                                    </td>
                                    <td>
                                        <?php if ((int)$smt['active'] === 1): ?>
                                            <span class="badge bg-success"><i class="bi bi-check2"></i> Aktif</span>
                                        <?php else: ?>
                                            <span class="badge bg-secondary">Nonaktif</span>
                                        <?php endif; ?>
                                    </td>
                                    <td class="text-end">
                                        <?php if ((int)$smt['active'] !== 1): ?>
                                            <a href="<?= base_url('/master/smt/activate/' . $smt['id_smt']) ?>" class="btn btn-sm btn-outline-info">
                                                <i class="bi bi-check2-circle me-1"></i> Aktifkan
                                            </a>
                                        <?php else: ?>
                                            <span class="badge bg-light text-muted border py-2 px-3">Aktif Berjalan</span>
                                        <?php endif; ?>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Modal Tambah TP -->
<div class="modal fade" id="addTpModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content border-0 shadow">
            <div class="modal-header bg-primary text-white">
                <h6 class="modal-title fw-bold"><i class="bi bi-calendar-plus me-1"></i> Tambah Tahun Pelajaran</h6>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
            </div>
            <form action="<?= base_url('/master/tp/store') ?>" method="POST">
                <div class="modal-body">
                    <div class="mb-3">
                        <label class="form-label small fw-semibold">Format Tahun Pelajaran</label>
                        <input type="text" name="tahun" class="form-control" placeholder="Contoh: 2026/2027" required pattern="\d{4}/\d{4}">
                        <div class="form-text small">Gunakan format 4 digit garis miring 4 digit (misal: 2026/2027).</div>
                    </div>
                    <div class="form-check form-switch">
                        <input class="form-check-input" type="checkbox" name="active" value="1" id="activeCheck">
                        <label class="form-check-label small fw-semibold" for="activeCheck">Langsung jadikan Tahun Pelajaran aktif</label>
                    </div>
                </div>
                <div class="modal-footer bg-light py-2">
                    <button type="button" class="btn btn-sm btn-secondary" data-bs-dismiss="modal">Batal</button>
                    <button type="submit" class="btn btn-sm btn-primary">Simpan Tahun Pelajaran</button>
                </div>
            </form>
        </div>
    </div>
</div>

