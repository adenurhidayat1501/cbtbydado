<div class="d-flex flex-wrap justify-content-between align-items-center mb-4">
    <div>
        <h4 class="fw-bold mb-1 text-dark">Data Kelas / Rombel</h4>
        <p class="text-muted small mb-0">Kelola rombongan belajar, wali kelas, serta salin data rombel lintas Tahun Pelajaran.</p>
    </div>
    <div class="d-flex flex-wrap gap-2">
        <button class="btn btn-outline-secondary btn-sm d-flex align-items-center gap-1" data-bs-toggle="modal" data-bs-target="#salinRombelModal">
            <i class="bi bi-copy"></i> Salin Rombel Lintas TP
        </button>
        <button class="btn btn-primary btn-sm d-flex align-items-center gap-1" data-bs-toggle="modal" data-bs-target="#addKelasModal">
            <i class="bi bi-plus-circle"></i> Tambah Kelas
        </button>
    </div>
</div>

<div class="card border-0 shadow-sm rounded-3">
    <div class="card-header bg-white border-bottom py-3 d-flex justify-content-between align-items-center">
        <h6 class="fw-bold mb-0 text-dark">
            <i class="bi bi-building text-primary me-2"></i> Daftar Rombel — TP <?= escape_html($tp['tahun'] ?? '-') ?> (<?= escape_html($smt['nama_smt'] ?? '-') ?>)
        </h6>
        <span class="badge bg-primary-subtle text-primary border"><?= count($kelasList) ?> Rombel</span>
    </div>
    <div class="card-body p-0">
        <div class="table-responsive">
            <table class="table table-hover align-middle mb-0 datatable">
                <thead class="table-light">
                    <tr>
                        <th style="width: 50px;">Tingkat</th>
                        <th>Kode Rombel</th>
                        <th>Nama Kelas</th>
                        <?php if (\App\Config\Jenjang::hasJurusan($jenjang)): ?>
                            <th>Jurusan</th>
                        <?php endif; ?>
                        <th>Wali Kelas</th>
                        <th>Jumlah Siswa</th>
                        <th class="text-end" style="width: 200px;">Aksi</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if (empty($kelasList)): ?>
                        <tr>
                            <td colspan="<?= \App\Config\Jenjang::hasJurusan($jenjang) ? 7 : 6 ?>" class="text-center py-4 text-muted">
                                Belum ada rombel kelas yang dibuat untuk periode ini.
                            </td>
                        </tr>
                    <?php else: ?>
                        <?php foreach ($kelasList as $k): ?>
                            <tr>
                                <td><span class="badge bg-light text-dark border font-monospace">Kelas <?= (int)$k['level_id'] ?></span></td>
                                <td class="fw-bold font-monospace text-dark"><?= escape_html($k['kode_kelas']) ?></td>
                                <td class="fw-semibold text-dark"><?= escape_html($k['nama_kelas']) ?></td>
                                <?php if (\App\Config\Jenjang::hasJurusan($jenjang)): ?>
                                    <td><?= escape_html($k['nama_jurusan'] ?? '-') ?></td>
                                <?php endif; ?>
                                <td>
                                    <?php if (!empty($k['wali_kelas'])): ?>
                                        <span class="text-dark"><i class="bi bi-person-check text-success me-1"></i> <?= escape_html($k['wali_kelas']) ?></span>
                                    <?php else: ?>
                                        <span class="text-muted small fst-italic">Belum ditentukan</span>
                                    <?php endif; ?>
                                </td>
                                <td>
                                    <span class="badge bg-info-subtle text-info border">
                                        <i class="bi bi-people me-1"></i> <?= (int)$k['total_siswa'] ?> Siswa
                                    </span>
                                </td>
                                <td class="text-end">
                                    <a href="<?= base_url('/master/kelas/anggota/' . $k['id_kelas']) ?>" class="btn btn-sm btn-outline-info py-1 px-2" title="Kelola Anggota Siswa">
                                        <i class="bi bi-people-fill me-1"></i> Anggota
                                    </a>
                                    <button class="btn btn-sm btn-outline-primary py-1 px-2 ms-1" data-bs-toggle="modal" data-bs-target="#editKelasModal<?= $k['id_kelas'] ?>" title="Edit">
                                        <i class="bi bi-pencil"></i>
                                    </button>
                                    <a href="<?= base_url('/master/kelas/delete/' . $k['id_kelas']) ?>" class="btn btn-sm btn-outline-danger py-1 px-2 ms-1" onclick="return confirm('Hapus rombel kelas ini? Data anggota akan terlepas.')" title="Hapus">
                                        <i class="bi bi-trash"></i>
                                    </a>
                                </td>
                            </tr>

                            <!-- Modal Edit Kelas -->
                            <div class="modal fade" id="editKelasModal<?= $k['id_kelas'] ?>" tabindex="-1" aria-hidden="true">
                                <div class="modal-dialog modal-dialog-centered">
                                    <div class="modal-content border-0 shadow">
                                        <div class="modal-header bg-primary text-white">
                                            <h6 class="modal-title fw-bold"><i class="bi bi-pencil me-1"></i> Edit Rombel Kelas</h6>
                                            <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
                                        </div>
                                        <form action="<?= base_url('/master/kelas/update/' . $k['id_kelas']) ?>" method="POST">
                                            <div class="modal-body">
                                                <div class="mb-3">
                                                    <label class="form-label small fw-semibold">Nama Kelas</label>
                                                    <input type="text" name="nama_kelas" class="form-control" value="<?= escape_html($k['nama_kelas']) ?>" required>
                                                </div>
                                                <div class="row g-2 mb-3">
                                                    <div class="col-6">
                                                        <label class="form-label small fw-semibold">Kode Kelas</label>
                                                        <input type="text" name="kode_kelas" class="form-control" value="<?= escape_html($k['kode_kelas']) ?>" required>
                                                    </div>
                                                    <div class="col-6">
                                                        <label class="form-label small fw-semibold">Tingkat / Level</label>
                                                        <select name="level_id" class="form-select">
                                                            <?php foreach ($levels as $lvl): ?>
                                                                <option value="<?= $lvl ?>" <?= (int)$k['level_id'] === $lvl ? 'selected' : '' ?>>Tingkat <?= $lvl ?></option>
                                                            <?php endforeach; ?>
                                                        </select>
                                                    </div>
                                                </div>

                                                <?php if (\App\Config\Jenjang::hasJurusan($jenjang)): ?>
                                                <div class="mb-3">
                                                    <label class="form-label small fw-semibold">Jurusan</label>
                                                    <select name="jurusan_id" class="form-select">
                                                        <option value="0">-- Tanpa Jurusan / Umum --</option>
                                                        <?php foreach ($jurusanList as $jur): ?>
                                                            <option value="<?= $jur['id_jurusan'] ?>" <?= (int)($k['jurusan_id'] ?? 0) === (int)$jur['id_jurusan'] ? 'selected' : '' ?>>
                                                                <?= escape_html($jur['nama_jurusan']) ?>
                                                            </option>
                                                        <?php endforeach; ?>
                                                    </select>
                                                </div>
                                                <?php endif; ?>

                                                <div class="mb-3">
                                                    <label class="form-label small fw-semibold">Wali Kelas</label>
                                                    <select name="guru_id" class="form-select">
                                                        <option value="0">-- Pilih Guru Wali Kelas --</option>
                                                        <?php foreach ($guruList as $g): ?>
                                                            <option value="<?= $g['id_guru'] ?>" <?= (int)($k['guru_id'] ?? 0) === (int)$g['id_guru'] ? 'selected' : '' ?>>
                                                                <?= escape_html($g['nama_guru']) ?>
                                                            </option>
                                                        <?php endforeach; ?>
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

<!-- Modal Tambah Kelas -->
<div class="modal fade" id="addKelasModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content border-0 shadow">
            <div class="modal-header bg-primary text-white">
                <h6 class="modal-title fw-bold"><i class="bi bi-plus-circle me-1"></i> Tambah Rombel Kelas Baru</h6>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
            </div>
            <form action="<?= base_url('/master/kelas/store') ?>" method="POST">
                <div class="modal-body">
                    <div class="mb-3">
                        <label class="form-label small fw-semibold">Nama Kelas</label>
                        <input type="text" name="nama_kelas" class="form-control" placeholder="Contoh: Kelas 7A, Kelas 8B, X MIPA 1" required>
                    </div>
                    <div class="row g-2 mb-3">
                        <div class="col-6">
                            <label class="form-label small fw-semibold">Kode Kelas</label>
                            <input type="text" name="kode_kelas" class="form-control" placeholder="Contoh: 7A, 8B" required>
                        </div>
                        <div class="col-6">
                            <label class="form-label small fw-semibold">Tingkat / Level</label>
                            <select name="level_id" class="form-select">
                                <?php foreach ($levels as $lvl): ?>
                                    <option value="<?= $lvl ?>">Tingkat <?= $lvl ?></option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                    </div>

                    <?php if (\App\Config\Jenjang::hasJurusan($jenjang)): ?>
                    <div class="mb-3">
                        <label class="form-label small fw-semibold">Jurusan</label>
                        <select name="jurusan_id" class="form-select">
                            <option value="0">-- Tanpa Jurusan / Umum --</option>
                            <?php foreach ($jurusanList as $jur): ?>
                                <option value="<?= $jur['id_jurusan'] ?>"><?= escape_html($jur['nama_jurusan']) ?></option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    <?php endif; ?>

                    <div class="mb-3">
                        <label class="form-label small fw-semibold">Wali Kelas</label>
                        <select name="guru_id" class="form-select">
                            <option value="0">-- Pilih Guru Wali Kelas --</option>
                            <?php foreach ($guruList as $g): ?>
                                <option value="<?= $g['id_guru'] ?>"><?= escape_html($g['nama_guru']) ?></option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                </div>
                <div class="modal-footer bg-light py-2">
                    <button type="button" class="btn btn-sm btn-secondary" data-bs-dismiss="modal">Batal</button>
                    <button type="submit" class="btn btn-sm btn-primary">Simpan Rombel</button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- Modal Salin Rombel Lintas TP -->
<div class="modal fade" id="salinRombelModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content border-0 shadow">
            <div class="modal-header bg-secondary text-white">
                <h6 class="modal-title fw-bold"><i class="bi bi-copy me-1"></i> Salin Rombel Kelas Lintas TP</h6>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
            </div>
            <form action="<?= base_url('/master/kelas/salin') ?>" method="POST">
                <div class="modal-body">
                    <p class="text-muted small mb-3">
                        Fitur ini menyalin seluruh struktur nama kelas, kode rombel, dan tingkat dari Tahun Pelajaran lama ke Tahun Pelajaran aktif saat ini (<strong><?= escape_html($tp['tahun'] ?? '-') ?></strong>).
                    </p>

                    <div class="mb-3">
                        <label class="form-label small fw-semibold">Pilih Tahun Pelajaran Sumber (Asal)</label>
                        <select name="from_tp_id" class="form-select form-select-sm" required>
                            <?php foreach ($allTp as $atp): ?>
                                <?php if ((int)$atp['id_tp'] !== (int)($tp['id_tp'] ?? 0)): ?>
                                    <option value="<?= $atp['id_tp'] ?>"><?= escape_html($atp['tahun']) ?></option>
                                <?php endif; ?>
                            <?php endforeach; ?>
                        </select>
                    </div>
                </div>
                <div class="modal-footer bg-light py-2">
                    <button type="button" class="btn btn-sm btn-secondary" data-bs-dismiss="modal">Batal</button>
                    <button type="submit" class="btn btn-sm btn-secondary">Mulai Salin Rombel</button>
                </div>
            </form>
        </div>
    </div>
</div>

