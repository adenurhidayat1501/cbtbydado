<div class="d-flex flex-wrap justify-content-between align-items-center mb-4">
    <div>
        <h4 class="fw-bold mb-1 text-dark">Data Peserta Didik (Siswa)</h4>
        <p class="text-muted small mb-0">Kelola data siswa, akun login CBT, status keaktifan, dan import data massal.</p>
    </div>
    <div class="d-flex flex-wrap gap-2">
        <a href="<?= base_url('/master/siswa/foto-per-kelas') ?>" class="btn btn-outline-primary btn-sm d-flex align-items-center gap-1">
            <i class="bi bi-images"></i> Update Foto Per Kelas
        </a>
        <button class="btn btn-outline-success btn-sm d-flex align-items-center gap-1" data-bs-toggle="modal" data-bs-target="#importSiswaModal">
            <i class="bi bi-file-earmark-spreadsheet"></i> Import CSV/Excel
        </button>
        <button class="btn btn-primary btn-sm d-flex align-items-center gap-1" data-bs-toggle="modal" data-bs-target="#addSiswaModal">
            <i class="bi bi-person-plus"></i> Tambah Siswa
        </button>
    </div>
</div>

<div class="card border-0 shadow-sm rounded-3">
    <div class="card-header bg-white border-bottom py-3 d-flex justify-content-between align-items-center">
        <h6 class="fw-bold mb-0 text-dark"><i class="bi bi-people text-primary me-2"></i> Daftar Peserta Didik Terdaftar</h6>
        <span class="badge bg-primary-subtle text-primary border">Total: <?= count($students) ?> Siswa</span>
    </div>
    <div class="card-body p-0">
        <div class="table-responsive">
            <table class="table table-hover align-middle mb-0 datatable">
                <thead class="table-light">
                    <tr>
                        <th style="width: 50px;">Foto</th>
                        <th>NIS / NISN</th>
                        <th>Nama Siswa</th>
                        <th>L/P</th>
                        <th>Rombel Aktif</th>
                        <th>Status</th>
                        <th class="text-end" style="width: 130px;">Aksi</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($students as $s): ?>
                        <tr>
                            <td>
                                <div class="rounded-circle overflow-hidden bg-light border d-flex align-items-center justify-content-center" style="width: 38px; height: 38px;">
                                    <?php if (!empty($s['foto'])): ?>
                                        <img src="<?= upload_url($s['foto']) ?>" alt="Foto" class="w-100 h-100 object-fit-cover">
                                    <?php else: ?>
                                        <i class="bi bi-person text-secondary"></i>
                                    <?php endif; ?>
                                </div>
                            </td>
                            <td>
                                <div class="fw-bold font-monospace text-dark"><?= escape_html($s['nis']) ?></div>
                                <small class="text-muted">NISN: <?= escape_html($s['nisn'] ?? '-') ?></small>
                            </td>
                            <td>
                                <div class="fw-semibold text-dark"><?= escape_html($s['nama']) ?></div>
                                <small class="text-muted"><i class="bi bi-geo-alt"></i> <?= escape_html($s['tempat_lahir'] ?? '-') ?>, <?= format_indo_date($s['tgl_lahir'] ?? '') ?></small>
                            </td>
                            <td>
                                <span class="badge <?= $s['jk'] === 'L' ? 'bg-primary' : 'bg-danger' ?>"><?= escape_html($s['jk']) ?></span>
                            </td>
                            <td>
                                <?php if (!empty($s['nama_kelas'])): ?>
                                    <span class="badge bg-success-subtle text-success border"><?= escape_html($s['nama_kelas']) ?></span>
                                <?php else: ?>
                                    <span class="badge bg-warning-subtle text-warning border">Belum ada rombel</span>
                                <?php endif; ?>
                            </td>
                            <td>
                                <?php if ((int)$s['status'] === 1): ?>
                                    <span class="badge bg-success">Aktif</span>
                                <?php elseif ((int)$s['status'] === 2): ?>
                                    <span class="badge bg-info">Lulus</span>
                                <?php else: ?>
                                    <span class="badge bg-secondary">Keluar</span>
                                <?php endif; ?>
                            </td>
                            <td class="text-end">
                                <button class="btn btn-sm btn-outline-primary py-1 px-2" data-bs-toggle="modal" data-bs-target="#editSiswaModal<?= $s['id_siswa'] ?>" title="Edit Siswa">
                                    <i class="bi bi-pencil"></i>
                                </button>
                                <a href="<?= base_url('/master/siswa/delete/' . $s['id_siswa']) ?>" class="btn btn-sm btn-outline-danger py-1 px-2 ms-1" onclick="return confirm('Hapus siswa <?= escape_html($s['nama']) ?> beserta akun user login-nya?')" title="Hapus">
                                    <i class="bi bi-trash"></i>
                                </a>
                            </td>
                        </tr>

                        <!-- Modal Edit Siswa -->
                        <div class="modal fade" id="editSiswaModal<?= $s['id_siswa'] ?>" tabindex="-1" aria-hidden="true">
                            <div class="modal-dialog modal-dialog-centered modal-lg">
                                <div class="modal-content border-0 shadow">
                                    <div class="modal-header bg-primary text-white">
                                        <h6 class="modal-title fw-bold"><i class="bi bi-pencil me-1"></i> Edit Data Siswa</h6>
                                        <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
                                    </div>
                                    <form action="<?= base_url('/master/siswa/update/' . $s['id_siswa']) ?>" method="POST">
                                        <div class="modal-body">
                                            <div class="row g-3">
                                                <div class="col-md-6">
                                                    <label class="form-label small fw-semibold">NIS (Nomor Induk Siswa)</label>
                                                    <input type="text" name="nis" class="form-control" value="<?= escape_html($s['nis']) ?>" required>
                                                </div>
                                                <div class="col-md-6">
                                                    <label class="form-label small fw-semibold">NISN</label>
                                                    <input type="text" name="nisn" class="form-control" value="<?= escape_html($s['nisn'] ?? '') ?>">
                                                </div>
                                                <div class="col-md-8">
                                                    <label class="form-label small fw-semibold">Nama Lengkap Siswa</label>
                                                    <input type="text" name="nama" class="form-control" value="<?= escape_html($s['nama']) ?>" required>
                                                </div>
                                                <div class="col-md-4">
                                                    <label class="form-label small fw-semibold">Jenis Kelamin</label>
                                                    <select name="jk" class="form-select">
                                                        <option value="L" <?= $s['jk'] === 'L' ? 'selected' : '' ?>>Laki-laki (L)</option>
                                                        <option value="P" <?= $s['jk'] === 'P' ? 'selected' : '' ?>>Perempuan (P)</option>
                                                    </select>
                                                </div>
                                                <div class="col-md-6">
                                                    <label class="form-label small fw-semibold">Tempat Lahir</label>
                                                    <input type="text" name="tempat_lahir" class="form-control" value="<?= escape_html($s['tempat_lahir'] ?? '') ?>">
                                                </div>
                                                <div class="col-md-6">
                                                    <label class="form-label small fw-semibold">Tanggal Lahir</label>
                                                    <input type="date" name="tgl_lahir" class="form-control" value="<?= $s['tgl_lahir'] ?? '' ?>">
                                                </div>
                                                <div class="col-md-6">
                                                    <label class="form-label small fw-semibold">Agama</label>
                                                    <input type="text" name="agama" class="form-control" value="<?= escape_html($s['agama'] ?? 'Islam') ?>">
                                                </div>
                                                <div class="col-md-6">
                                                    <label class="form-label small fw-semibold">Rombel / Kelas Aktif</label>
                                                    <select name="id_kelas" class="form-select">
                                                        <option value="0">-- Belum Masuk Rombel --</option>
                                                        <?php foreach ($kelasList as $k): ?>
                                                            <option value="<?= $k['id_kelas'] ?>" <?= ($s['nama_kelas'] ?? '') === $k['nama_kelas'] ? 'selected' : '' ?>>
                                                                <?= escape_html($k['nama_kelas']) ?> (Tingkat <?= $k['level_id'] ?>)
                                                            </option>
                                                        <?php endforeach; ?>
                                                    </select>
                                                </div>
                                                <div class="col-12">
                                                    <label class="form-label small fw-semibold">Alamat Lengkap</label>
                                                    <textarea name="alamat" class="form-control" rows="2"><?= escape_html($s['alamat'] ?? '') ?></textarea>
                                                </div>
                                                <div class="col-md-4">
                                                    <label class="form-label small fw-semibold">Nama Ayah</label>
                                                    <input type="text" name="nama_ayah" class="form-control" value="<?= escape_html($s['nama_ayah'] ?? '') ?>">
                                                </div>
                                                <div class="col-md-4">
                                                    <label class="form-label small fw-semibold">Nama Ibu</label>
                                                    <input type="text" name="nama_ibu" class="form-control" value="<?= escape_html($s['nama_ibu'] ?? '') ?>">
                                                </div>
                                                <div class="col-md-4">
                                                    <label class="form-label small fw-semibold">No HP / WhatsApp</label>
                                                    <input type="text" name="no_hp" class="form-control" value="<?= escape_html($s['no_hp'] ?? '') ?>">
                                                </div>
                                                <div class="col-md-6">
                                                    <label class="form-label small fw-semibold">Status Siswa</label>
                                                    <select name="status" class="form-select">
                                                        <option value="1" <?= (int)$s['status'] === 1 ? 'selected' : '' ?>>Aktif</option>
                                                        <option value="2" <?= (int)$s['status'] === 2 ? 'selected' : '' ?>>Lulus</option>
                                                        <option value="3" <?= (int)$s['status'] === 3 ? 'selected' : '' ?>>Pindah / Keluar</option>
                                                    </select>
                                                </div>
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

<!-- Modal Tambah Siswa -->
<div class="modal fade" id="addSiswaModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered modal-lg">
        <div class="modal-content border-0 shadow">
            <div class="modal-header bg-primary text-white">
                <h6 class="modal-title fw-bold"><i class="bi bi-person-plus me-1"></i> Tambah Siswa Baru</h6>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
            </div>
            <form action="<?= base_url('/master/siswa/store') ?>" method="POST">
                <div class="modal-body">
                    <div class="row g-3">
                        <div class="col-md-6">
                            <label class="form-label small fw-semibold">NIS (Wajib / Nomor Login Siswa)</label>
                            <input type="text" name="nis" class="form-control" placeholder="Contoh: 2026001" required>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label small fw-semibold">NISN</label>
                            <input type="text" name="nisn" class="form-control" placeholder="Contoh: 0081234567">
                        </div>
                        <div class="col-md-8">
                            <label class="form-label small fw-semibold">Nama Lengkap Siswa</label>
                            <input type="text" name="nama" class="form-control" placeholder="Nama lengkap siswa" required>
                        </div>
                        <div class="col-md-4">
                            <label class="form-label small fw-semibold">Jenis Kelamin</label>
                            <select name="jk" class="form-select">
                                <option value="L">Laki-laki (L)</option>
                                <option value="P">Perempuan (P)</option>
                            </select>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label small fw-semibold">Tempat Lahir</label>
                            <input type="text" name="tempat_lahir" class="form-control" placeholder="Kota lahir">
                        </div>
                        <div class="col-md-6">
                            <label class="form-label small fw-semibold">Tanggal Lahir</label>
                            <input type="date" name="tgl_lahir" class="form-control">
                        </div>
                        <div class="col-md-6">
                            <label class="form-label small fw-semibold">Agama</label>
                            <input type="text" name="agama" class="form-control" value="Islam">
                        </div>
                        <div class="col-md-6">
                            <label class="form-label small fw-semibold">Pilih Kelas / Rombel</label>
                            <select name="id_kelas" class="form-select">
                                <option value="0">-- Belum Masuk Rombel --</option>
                                <?php foreach ($kelasList as $k): ?>
                                    <option value="<?= $k['id_kelas'] ?>"><?= escape_html($k['nama_kelas']) ?> (Tingkat <?= $k['level_id'] ?>)</option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                        <div class="col-12">
                            <label class="form-label small fw-semibold">Alamat Lengkap</label>
                            <textarea name="alamat" class="form-control" rows="2" placeholder="Alamat tempat tinggal siswa"></textarea>
                        </div>
                        <div class="col-md-4">
                            <label class="form-label small fw-semibold">Nama Ayah</label>
                            <input type="text" name="nama_ayah" class="form-control" placeholder="Nama ayah">
                        </div>
                        <div class="col-md-4">
                            <label class="form-label small fw-semibold">Nama Ibu</label>
                            <input type="text" name="nama_ibu" class="form-control" placeholder="Nama ibu">
                        </div>
                        <div class="col-md-4">
                            <label class="form-label small fw-semibold">No HP</label>
                            <input type="text" name="no_hp" class="form-control" placeholder="08xxxxxxxxxx">
                        </div>
                    </div>
                </div>
                <div class="modal-footer bg-light py-2">
                    <button type="button" class="btn btn-sm btn-secondary" data-bs-dismiss="modal">Batal</button>
                    <button type="submit" class="btn btn-sm btn-primary">Simpan Siswa</button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- Modal Import CSV/Excel Siswa -->
<div class="modal fade" id="importSiswaModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content border-0 shadow">
            <div class="modal-header bg-success text-white">
                <h6 class="modal-title fw-bold"><i class="bi bi-file-earmark-spreadsheet me-1"></i> Import Data Siswa Massal</h6>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
            </div>
            <form action="<?= base_url('/master/siswa/import') ?>" method="POST" enctype="multipart/form-data">
                <div class="modal-body">
                    <p class="text-muted small mb-3">
                        Gunakan format file CSV / Excel sesuai template. Akun login CBT siswa (username = NIS, password = siswa123) akan dibuatkan otomatis oleh sistem.
                    </p>

                    <div class="mb-3">
                        <a href="<?= base_url('/master/siswa/template') ?>" class="btn btn-sm btn-outline-success w-100">
                            <i class="bi bi-download me-1"></i> Unduh Template CSV Siswa
                        </a>
                    </div>

                    <div class="mb-3">
                        <label class="form-label small fw-semibold">Pilih Berkas CSV</label>
                        <input type="file" name="file_csv" class="form-control form-control-sm" accept=".csv, text/csv" required>
                    </div>
                </div>
                <div class="modal-footer bg-light py-2">
                    <button type="button" class="btn btn-sm btn-secondary" data-bs-dismiss="modal">Batal</button>
                    <button type="submit" class="btn btn-sm btn-success">Mulai Import</button>
                </div>
            </form>
        </div>
    </div>
</div>

