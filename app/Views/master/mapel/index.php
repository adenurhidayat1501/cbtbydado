<div class="d-flex flex-wrap justify-content-between align-items-center mb-4">
    <div>
        <h4 class="fw-bold mb-1 text-dark">Data Mata Pelajaran</h4>
        <p class="text-muted small mb-0">Kelola kurikulum mata pelajaran (Kelompok A, B, C, Muatan Lokal, dan PAI Madrasah).</p>
    </div>
    <div class="d-flex gap-2">
        <button class="btn btn-primary btn-sm d-flex align-items-center gap-1" data-bs-toggle="modal" data-bs-target="#addMapelModal">
            <i class="bi bi-plus-circle"></i> Tambah Mapel
        </button>
    </div>
</div>

<!-- Filter Kelompok Tabs -->
<ul class="nav nav-pills mb-3 gap-1">
    <li class="nav-item">
        <a class="nav-link <?= empty($filterKelompok) ? 'active' : '' ?>" href="<?= base_url('/master/mapel') ?>">Semua Kelompok</a>
    </li>
    <li class="nav-item">
        <a class="nav-link <?= $filterKelompok === 'A' ? 'active' : '' ?>" href="<?= base_url('/master/mapel?kelompok=A') ?>">Kelompok A (Umum)</a>
    </li>
    <li class="nav-item">
        <a class="nav-link <?= $filterKelompok === 'B' ? 'active' : '' ?>" href="<?= base_url('/master/mapel?kelompok=B') ?>">Kelompok B (Seni/PJOK)</a>
    </li>
    <li class="nav-item">
        <a class="nav-link <?= $filterKelompok === 'C' ? 'active' : '' ?>" href="<?= base_url('/master/mapel?kelompok=C') ?>">Kelompok C (Peminatan)</a>
    </li>
    <li class="nav-item">
        <a class="nav-link <?= $filterKelompok === 'MULOK' ? 'active' : '' ?>" href="<?= base_url('/master/mapel?kelompok=MULOK') ?>">Muatan Lokal</a>
    </li>
    <li class="nav-item">
        <a class="nav-link <?= $filterKelompok === 'PAI' ? 'active' : '' ?>" href="<?= base_url('/master/mapel?kelompok=PAI') ?>">PAI / Keagamaan</a>
    </li>
</ul>

<div class="card border-0 shadow-sm rounded-3">
    <div class="card-header bg-white border-bottom py-3">
        <h6 class="fw-bold mb-0 text-dark"><i class="bi bi-journal-bookmark text-primary me-2"></i> Daftar Mata Pelajaran</h6>
    </div>
    <div class="card-body p-0">
        <div class="table-responsive">
            <table class="table table-hover align-middle mb-0 datatable">
                <thead class="table-light">
                    <tr>
                        <th style="width: 50px;">Urut</th>
                        <th>Kode</th>
                        <th>Nama Mata Pelajaran</th>
                        <th>Kelompok</th>
                        <th>Bobot</th>
                        <th>Status</th>
                        <th class="text-end" style="width: 140px;">Aksi</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($list as $m): ?>
                        <tr>
                            <td><span class="badge bg-light text-dark border"><?= (int)$m['urutan_tampil'] ?></span></td>
                            <td><span class="badge bg-info-subtle text-info border font-monospace"><?= escape_html($m['kode']) ?></span></td>
                            <td class="fw-semibold text-dark"><?= escape_html($m['nama_mapel']) ?></td>
                            <td>
                                <span class="badge <?= $m['kelompok'] === 'PAI' ? 'bg-success' : ($m['kelompok'] === 'MULOK' ? 'bg-warning text-dark' : 'bg-secondary') ?>">
                                    <?= escape_html($m['kelompok']) ?>
                                </span>
                            </td>
                            <td><?= (int)$m['bobot'] ?></td>
                            <td>
                                <?php if ((int)$m['status'] === 1): ?>
                                    <span class="badge bg-success">Aktif</span>
                                <?php else: ?>
                                    <span class="badge bg-secondary">Nonaktif</span>
                                <?php endif; ?>
                            </td>
                            <td class="text-end">
                                <button class="btn btn-sm btn-outline-primary py-1 px-2" data-bs-toggle="modal" data-bs-target="#editMapelModal<?= $m['id_mapel'] ?>" title="Edit">
                                    <i class="bi bi-pencil"></i>
                                </button>
                                <a href="<?= base_url('/master/mapel/delete/' . $m['id_mapel']) ?>" class="btn btn-sm btn-outline-danger py-1 px-2 ms-1" onclick="return confirm('Hapus mapel ini?')" title="Hapus">
                                    <i class="bi bi-trash"></i>
                                </a>
                            </td>
                        </tr>

                        <!-- Modal Edit Mapel -->
                        <div class="modal fade" id="editMapelModal<?= $m['id_mapel'] ?>" tabindex="-1" aria-hidden="true">
                            <div class="modal-dialog modal-dialog-centered">
                                <div class="modal-content border-0 shadow">
                                    <div class="modal-header bg-primary text-white">
                                        <h6 class="modal-title fw-bold"><i class="bi bi-pencil me-1"></i> Edit Mata Pelajaran</h6>
                                        <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
                                    </div>
                                    <form action="<?= base_url('/master/mapel/update/' . $m['id_mapel']) ?>" method="POST">
                                        <div class="modal-body">
                                            <div class="mb-3">
                                                <label class="form-label small fw-semibold">Nama Mata Pelajaran</label>
                                                <input type="text" name="nama_mapel" class="form-control" value="<?= escape_html($m['nama_mapel']) ?>" required>
                                            </div>
                                            <div class="row g-2 mb-3">
                                                <div class="col-6">
                                                    <label class="form-label small fw-semibold">Kode Mapel</label>
                                                    <input type="text" name="kode" class="form-control" value="<?= escape_html($m['kode']) ?>" required>
                                                </div>
                                                <div class="col-6">
                                                    <label class="form-label small fw-semibold">Kelompok</label>
                                                    <select name="kelompok" class="form-select">
                                                        <option value="A" <?= $m['kelompok'] === 'A' ? 'selected' : '' ?>>A (Umum)</option>
                                                        <option value="B" <?= $m['kelompok'] === 'B' ? 'selected' : '' ?>>B (Keterampilan)</option>
                                                        <option value="C" <?= $m['kelompok'] === 'C' ? 'selected' : '' ?>>C (Peminatan)</option>
                                                        <option value="MULOK" <?= $m['kelompok'] === 'MULOK' ? 'selected' : '' ?>>Muatan Lokal</option>
                                                        <option value="PAI" <?= $m['kelompok'] === 'PAI' ? 'selected' : '' ?>>PAI / Madrasah</option>
                                                    </select>
                                                </div>
                                            </div>
                                            <div class="row g-2 mb-3">
                                                <div class="col-6">
                                                    <label class="form-label small fw-semibold">Bobot SKS / Jam</label>
                                                    <input type="number" name="bobot" class="form-control" value="<?= (int)$m['bobot'] ?>" required>
                                                </div>
                                                <div class="col-6">
                                                    <label class="form-label small fw-semibold">Urutan Tampil</label>
                                                    <input type="number" name="urutan_tampil" class="form-control" value="<?= (int)$m['urutan_tampil'] ?>" required>
                                                </div>
                                            </div>
                                            <div class="mb-3">
                                                <label class="form-label small fw-semibold">Status</label>
                                                <select name="status" class="form-select">
                                                    <option value="1" <?= (int)$m['status'] === 1 ? 'selected' : '' ?>>Aktif</option>
                                                    <option value="0" <?= (int)$m['status'] === 0 ? 'selected' : '' ?>>Nonaktif</option>
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
                </tbody>
            </table>
        </div>
    </div>
</div>

<!-- Modal Tambah Mapel -->
<div class="modal fade" id="addMapelModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content border-0 shadow">
            <div class="modal-header bg-primary text-white">
                <h6 class="modal-title fw-bold"><i class="bi bi-plus-circle me-1"></i> Tambah Mata Pelajaran Baru</h6>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
            </div>
            <form action="<?= base_url('/master/mapel/store') ?>" method="POST">
                <div class="modal-body">
                    <div class="mb-3">
                        <label class="form-label small fw-semibold">Nama Mata Pelajaran</label>
                        <input type="text" name="nama_mapel" class="form-control" placeholder="Contoh: Matematika" required>
                    </div>
                    <div class="row g-2 mb-3">
                        <div class="col-6">
                            <label class="form-label small fw-semibold">Kode Mapel</label>
                            <input type="text" name="kode" class="form-control" placeholder="Contoh: MAT" required>
                        </div>
                        <div class="col-6">
                            <label class="form-label small fw-semibold">Kelompok</label>
                            <select name="kelompok" class="form-select">
                                <option value="A">A (Umum)</option>
                                <option value="B">B (Keterampilan)</option>
                                <option value="C">C (Peminatan)</option>
                                <option value="MULOK">Muatan Lokal</option>
                                <option value="PAI">PAI / Madrasah</option>
                            </select>
                        </div>
                    </div>
                    <div class="row g-2 mb-3">
                        <div class="col-6">
                            <label class="form-label small fw-semibold">Bobot Nilai Default</label>
                            <input type="number" name="bobot" class="form-control" value="100" required>
                        </div>
                        <div class="col-6">
                            <label class="form-label small fw-semibold">Nomor Urutan Tampil</label>
                            <input type="number" name="urutan_tampil" class="form-control" value="1" required>
                        </div>
                    </div>
                </div>
                <div class="modal-footer bg-light py-2">
                    <button type="button" class="btn btn-sm btn-secondary" data-bs-dismiss="modal">Batal</button>
                    <button type="submit" class="btn btn-sm btn-primary">Simpan Mata Pelajaran</button>
                </div>
            </form>
        </div>
    </div>
</div>

