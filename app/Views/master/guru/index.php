<div class="d-flex flex-wrap justify-content-between align-items-center mb-4">
    <div>
        <h4 class="fw-bold mb-1 text-dark">Data Guru & Tenaga Pendidik</h4>
        <p class="text-muted small mb-0">Kelola guru, penetapan jabatan (Kepsek, Wali Kelas, Guru Mapel, Pengawas), serta struktur organisasi.</p>
    </div>
    <div class="d-flex flex-wrap gap-2">
        <a href="<?= base_url('/master/guru/struktur') ?>" class="btn btn-outline-info btn-sm d-flex align-items-center gap-1">
            <i class="bi bi-diagram-2"></i> Bagan Struktur Organisasi
        </a>
        <button class="btn btn-outline-success btn-sm d-flex align-items-center gap-1" data-bs-toggle="modal" data-bs-target="#importGuruModal">
            <i class="bi bi-file-earmark-spreadsheet"></i> Import CSV Guru
        </button>
        <button class="btn btn-primary btn-sm d-flex align-items-center gap-1" data-bs-toggle="modal" data-bs-target="#addGuruModal">
            <i class="bi bi-person-plus"></i> Tambah Guru
        </button>
    </div>
</div>

<div class="card border-0 shadow-sm rounded-3">
    <div class="card-header bg-white border-bottom py-3 d-flex justify-content-between align-items-center">
        <h6 class="fw-bold mb-0 text-dark"><i class="bi bi-person-workspace text-primary me-2"></i> Daftar Pendidik & Staf</h6>
        <span class="badge bg-primary-subtle text-primary border"><?= count($guruList) ?> Pendidik</span>
    </div>
    <div class="card-body p-0">
        <div class="table-responsive">
            <table class="table table-hover align-middle mb-0 datatable">
                <thead class="table-light">
                    <tr>
                        <th style="width: 50px;">Foto</th>
                        <th>NIP / Kode</th>
                        <th>Nama Guru & Gelar</th>
                        <th>L/P</th>
                        <th>Penugasan / Jabatan</th>
                        <th>No HP</th>
                        <th>Status</th>
                        <th class="text-end" style="width: 130px;">Aksi</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($guruList as $g): ?>
                        <tr>
                            <td>
                                <div class="rounded-circle overflow-hidden bg-light border d-flex align-items-center justify-content-center" style="width: 38px; height: 38px;">
                                    <?php if (!empty($g['foto'])): ?>
                                        <img src="<?= upload_url($g['foto']) ?>" alt="Foto" class="w-100 h-100 object-fit-cover">
                                    <?php else: ?>
                                        <i class="bi bi-person text-secondary"></i>
                                    <?php endif; ?>
                                </div>
                            </td>
                            <td>
                                <div class="fw-bold font-monospace text-dark"><?= escape_html($g['nip'] ?? '-') ?></div>
                                <small class="badge bg-light text-dark border"><?= escape_html($g['kode_guru'] ?? '-') ?></small>
                            </td>
                            <td>
                                <div class="fw-semibold text-dark"><?= escape_html($g['nama_guru']) ?></div>
                                <small class="text-muted"><i class="bi bi-geo-alt"></i> <?= escape_html($g['alamat'] ?? '-') ?></small>
                            </td>
                            <td><span class="badge <?= $g['jk'] === 'L' ? 'bg-primary' : 'bg-danger' ?>"><?= escape_html($g['jk']) ?></span></td>
                            <td>
                                <?php
                                $jabatanId = (int)($g['id_jabatan'] ?? 3);
                                if ($jabatanId === 1) {
                                    echo '<span class="badge bg-danger"><i class="bi bi-award me-1"></i> Kepala Sekolah</span>';
                                } elseif ($jabatanId === 2) {
                                    $waliKelas = !empty($g['wali_kelas_nama']) ? $g['wali_kelas_nama'] : 'Rombel';
                                    echo '<span class="badge bg-success"><i class="bi bi-people me-1"></i> Wali ' . escape_html($waliKelas) . '</span>';
                                } elseif ($jabatanId === 4) {
                                    echo '<span class="badge bg-info text-dark"><i class="bi bi-shield-check me-1"></i> Pengawas CBT</span>';
                                } else {
                                    echo '<span class="badge bg-secondary">Guru Mata Pelajaran</span>';
                                }
                                ?>
                            </td>
                            <td><?= escape_html($g['no_hp'] ?? '-') ?></td>
                            <td>
                                <?php if ((int)$g['status'] === 1): ?>
                                    <span class="badge bg-success">Aktif</span>
                                <?php else: ?>
                                    <span class="badge bg-secondary">Nonaktif</span>
                                <?php endif; ?>
                            </td>
                            <td class="text-end">
                                <button class="btn btn-sm btn-outline-primary py-1 px-2" data-bs-toggle="modal" data-bs-target="#editGuruModal<?= $g['id_guru'] ?>" title="Edit">
                                    <i class="bi bi-pencil"></i>
                                </button>
                                <a href="<?= base_url('/master/guru/delete/' . $g['id_guru']) ?>" class="btn btn-sm btn-outline-danger py-1 px-2 ms-1" onclick="return confirm('Hapus data guru ini?')" title="Hapus">
                                    <i class="bi bi-trash"></i>
                                </a>
                            </td>
                        </tr>

                        <!-- Modal Edit Guru -->
                        <div class="modal fade" id="editGuruModal<?= $g['id_guru'] ?>" tabindex="-1" aria-hidden="true">
                            <div class="modal-dialog modal-dialog-centered modal-lg">
                                <div class="modal-content border-0 shadow">
                                    <div class="modal-header bg-primary text-white">
                                        <h6 class="modal-title fw-bold"><i class="bi bi-pencil me-1"></i> Edit Data Guru</h6>
                                        <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
                                    </div>
                                    <form action="<?= base_url('/master/guru/update/' . $g['id_guru']) ?>" method="POST" enctype="multipart/form-data">
                                        <div class="modal-body">
                                            <div class="row g-3">
                                                <div class="col-md-6">
                                                    <label class="form-label small fw-semibold">NIP (Nomor Induk Pegawai)</label>
                                                    <input type="text" name="nip" class="form-control" value="<?= escape_html($g['nip'] ?? '') ?>">
                                                </div>
                                                <div class="col-md-6">
                                                    <label class="form-label small fw-semibold">Kode Guru (Inisial)</label>
                                                    <input type="text" name="kode_guru" class="form-control" value="<?= escape_html($g['kode_guru'] ?? '') ?>">
                                                </div>
                                                <div class="col-md-8">
                                                    <label class="form-label small fw-semibold">Nama Lengkap & Gelar</label>
                                                    <input type="text" name="nama_guru" class="form-control" value="<?= escape_html($g['nama_guru']) ?>" required>
                                                </div>
                                                <div class="col-md-4">
                                                    <label class="form-label small fw-semibold">Jenis Kelamin</label>
                                                    <select name="jk" class="form-select">
                                                        <option value="L" <?= $g['jk'] === 'L' ? 'selected' : '' ?>>Laki-laki (L)</option>
                                                        <option value="P" <?= $g['jk'] === 'P' ? 'selected' : '' ?>>Perempuan (P)</option>
                                                    </select>
                                                </div>
                                                <div class="col-md-6">
                                                    <label class="form-label small fw-semibold">Penetapan Jabatan</label>
                                                    <select name="id_jabatan" class="form-select" id="selectJabatanEdit_<?= $g['id_guru'] ?>">
                                                        <option value="1" <?= (int)($g['id_jabatan'] ?? 3) === 1 ? 'selected' : '' ?>>Kepala Sekolah</option>
                                                        <option value="2" <?= (int)($g['id_jabatan'] ?? 3) === 2 ? 'selected' : '' ?>>Wali Kelas</option>
                                                        <option value="3" <?= (int)($g['id_jabatan'] ?? 3) === 3 ? 'selected' : '' ?>>Guru Mata Pelajaran</option>
                                                        <option value="4" <?= (int)($g['id_jabatan'] ?? 3) === 4 ? 'selected' : '' ?>>Pengawas CBT</option>
                                                    </select>
                                                </div>
                                                <div class="col-md-6">
                                                    <label class="form-label small fw-semibold">Wali Kelas untuk Rombel:</label>
                                                    <select name="id_kelas" class="form-select">
                                                        <option value="0">-- Bukan Wali Kelas / Pilih Rombel --</option>
                                                        <?php foreach ($kelasList as $k): ?>
                                                            <option value="<?= $k['id_kelas'] ?>" <?= (int)($g['wali_kelas_id'] ?? 0) === (int)$k['id_kelas'] ? 'selected' : '' ?>>
                                                                <?= escape_html($k['nama_kelas']) ?> (Tingkat <?= $k['level_id'] ?>)
                                                            </option>
                                                        <?php endforeach; ?>
                                                    </select>
                                                </div>
                                                <div class="col-md-6">
                                                    <label class="form-label small fw-semibold">No HP / WhatsApp</label>
                                                    <input type="text" name="no_hp" class="form-control" value="<?= escape_html($g['no_hp'] ?? '') ?>">
                                                </div>
                                                <div class="col-md-6">
                                                    <label class="form-label small fw-semibold">Ganti Pas Foto (JPG/PNG)</label>
                                                    <input type="file" name="foto" class="form-control" accept="image/*">
                                                </div>
                                                <div class="col-12">
                                                    <label class="form-label small fw-semibold">Alamat</label>
                                                    <textarea name="alamat" class="form-control" rows="2"><?= escape_html($g['alamat'] ?? '') ?></textarea>
                                                </div>
                                                <div class="col-md-6">
                                                    <label class="form-label small fw-semibold">Status Keaktifan</label>
                                                    <select name="status" class="form-select">
                                                        <option value="1" <?= (int)$g['status'] === 1 ? 'selected' : '' ?>>Aktif</option>
                                                        <option value="0" <?= (int)$g['status'] === 0 ? 'selected' : '' ?>>Nonaktif</option>
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

<!-- Modal Tambah Guru -->
<div class="modal fade" id="addGuruModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered modal-lg">
        <div class="modal-content border-0 shadow">
            <div class="modal-header bg-primary text-white">
                <h6 class="modal-title fw-bold"><i class="bi bi-person-plus me-1"></i> Tambah Guru & Tenaga Pendidik</h6>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
            </div>
            <form action="<?= base_url('/master/guru/store') ?>" method="POST" enctype="multipart/form-data">
                <div class="modal-body">
                    <div class="row g-3">
                        <div class="col-md-6">
                            <label class="form-label small fw-semibold">NIP (Nomor Induk Pegawai)</label>
                            <input type="text" name="nip" class="form-control" placeholder="Contoh: 198001012005011002">
                        </div>
                        <div class="col-md-6">
                            <label class="form-label small fw-semibold">Kode Guru (Inisial Singkat)</label>
                            <input type="text" name="kode_guru" class="form-control" placeholder="Contoh: G01, BDS">
                        </div>
                        <div class="col-md-8">
                            <label class="form-label small fw-semibold">Nama Lengkap & Gelar</label>
                            <input type="text" name="nama_guru" class="form-control" placeholder="Contoh: Drs. H. Budi Santoso, M.Pd." required>
                        </div>
                        <div class="col-md-4">
                            <label class="form-label small fw-semibold">Jenis Kelamin</label>
                            <select name="jk" class="form-select">
                                <option value="L">Laki-laki (L)</option>
                                <option value="P">Perempuan (P)</option>
                            </select>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label small fw-semibold">Jabatan Utama</label>
                            <select name="id_jabatan" class="form-select">
                                <option value="3" selected>Guru Mata Pelajaran</option>
                                <option value="2">Wali Kelas</option>
                                <option value="1">Kepala Sekolah</option>
                                <option value="4">Pengawas CBT</option>
                            </select>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label small fw-semibold">Wali Kelas untuk Rombel:</label>
                            <select name="id_kelas" class="form-select">
                                <option value="0">-- Bukan Wali Kelas / Pilih Rombel --</option>
                                <?php foreach ($kelasList as $k): ?>
                                    <option value="<?= $k['id_kelas'] ?>"><?= escape_html($k['nama_kelas']) ?></option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label small fw-semibold">No HP / WhatsApp</label>
                            <input type="text" name="no_hp" class="form-control" placeholder="08xxxxxxxxxx">
                        </div>
                        <div class="col-md-6">
                            <label class="form-label small fw-semibold">Pas Foto (JPG/PNG)</label>
                            <input type="file" name="foto" class="form-control" accept="image/*">
                        </div>
                        <div class="col-12">
                            <label class="form-label small fw-semibold">Alamat</label>
                            <textarea name="alamat" class="form-control" rows="2" placeholder="Alamat domisili guru"></textarea>
                        </div>
                    </div>
                </div>
                <div class="modal-footer bg-light py-2">
                    <button type="button" class="btn btn-sm btn-secondary" data-bs-dismiss="modal">Batal</button>
                    <button type="submit" class="btn btn-sm btn-primary">Simpan Guru</button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- Modal Import CSV Guru -->
<div class="modal fade" id="importGuruModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content border-0 shadow">
            <div class="modal-header bg-success text-white">
                <h6 class="modal-title fw-bold"><i class="bi bi-file-earmark-spreadsheet me-1"></i> Import Guru Massal</h6>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
            </div>
            <form action="<?= base_url('/master/guru/import') ?>" method="POST" enctype="multipart/form-data">
                <div class="modal-body">
                    <p class="text-muted small mb-3">
                        Gunakan format berkas CSV sesuai template resmi. Akun login guru akan dibuatkan otomatis dengan default password <code>guru123</code>.
                    </p>

                    <div class="mb-3">
                        <a href="<?= base_url('/master/guru/template') ?>" class="btn btn-sm btn-outline-success w-100">
                            <i class="bi bi-download me-1"></i> Unduh Template CSV Guru
                        </a>
                    </div>

                    <div class="mb-3">
                        <label class="form-label small fw-semibold">Pilih Berkas CSV Guru</label>
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

