<div class="container-fluid py-3">
    <div class="d-flex justify-content-between align-items-center mb-3">
        <div>
            <h4 class="mb-0 fw-bold"><i class="bi bi-calendar-event text-primary me-2"></i>Jadwal Tayang Materi / Tugas</h4>
            <small class="text-muted">Atur tanggal tayang materi dan tugas per kelas rombel</small>
        </div>
        <div>
            <button type="button" class="btn btn-primary btn-sm" data-bs-toggle="modal" data-bs-target="#modalTambahJadwal">
                <i class="bi bi-plus-circle me-1"></i> Jadwalkan Materi/Tugas
            </button>
        </div>
    </div>

    <?php if (has_flash('success')): ?>
        <div class="alert alert-success alert-dismissible fade show" role="alert">
            <i class="bi bi-check-circle me-1"></i> <?= escape_html(get_flash('success')) ?>
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
    <?php endif; ?>
    <?php if (has_flash('error')): ?>
        <div class="alert alert-danger alert-dismissible fade show" role="alert">
            <i class="bi bi-exclamation-triangle me-1"></i> <?= escape_html(get_flash('error')) ?>
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
    <?php endif; ?>

    <!-- Filter Card -->
    <div class="card shadow-sm border-0 mb-3">
        <div class="card-body py-2">
            <form action="<?= base_url('/elearning/jadwal-materi') ?>" method="GET" class="row g-2 align-items-center">
                <div class="col-md-3">
                    <select name="kelas_id" class="form-select form-select-sm" onchange="this.form.submit()">
                        <option value="">-- Semua Kelas --</option>
                        <?php foreach ($kelasList as $k): ?>
                            <option value="<?= $k['id_kelas'] ?>" <?= (int)$k['id_kelas'] === (int)$selectedKelasId ? 'selected' : '' ?>>
                                <?= escape_html($k['nama_kelas']) ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                </div>

                <div class="col-md-3">
                    <select name="mapel_id" class="form-select form-select-sm" onchange="this.form.submit()">
                        <option value="">-- Semua Mata Pelajaran --</option>
                        <?php foreach ($mapelList as $m): ?>
                            <option value="<?= $m['id_mapel'] ?>" <?= (int)$m['id_mapel'] === (int)$selectedMapelId ? 'selected' : '' ?>>
                                <?= escape_html($m['nama_mapel']) ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                </div>

                <div class="col-md-3">
                    <input type="date" name="date" class="form-control form-control-sm" value="<?= escape_html($selectedDate ?? '') ?>" onchange="this.form.submit()">
                </div>

                <div class="col-md-3">
                    <div class="btn-group btn-group-sm w-100">
                        <a href="<?= base_url('/elearning/jadwal-materi') ?>" class="btn <?= $selectedJenis === null ? 'btn-primary' : 'btn-outline-primary' ?>">Semua</a>
                        <a href="<?= base_url('/elearning/jadwal-materi?jenis=1') ?>" class="btn <?= $selectedJenis === 1 ? 'btn-primary' : 'btn-outline-primary' ?>">Materi</a>
                        <a href="<?= base_url('/elearning/jadwal-materi?jenis=2') ?>" class="btn <?= $selectedJenis === 2 ? 'btn-primary' : 'btn-outline-primary' ?>">Tugas</a>
                    </div>
                </div>
            </form>
        </div>
    </div>

    <!-- Schedules Table -->
    <div class="card shadow-sm border-0">
        <div class="card-body p-0">
            <div class="table-responsive">
                <table class="table table-hover table-striped align-middle mb-0 datatable" style="font-size: 13px;">
                    <thead class="table-light">
                        <tr>
                            <th style="width: 40px;" class="text-center">No</th>
                            <th style="width: 110px;" class="text-center">Tanggal Tayang</th>
                            <th style="width: 90px;" class="text-center">Tipe</th>
                            <th>Kelas</th>
                            <th>Mata Pelajaran</th>
                            <th>Judul Materi / Tugas</th>
                            <th>Guru Pengampu</th>
                            <th class="text-center">Partisipasi Siswa</th>
                            <th style="width: 100px;" class="text-center">Aksi</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if (empty($schedules)): ?>
                            <tr>
                                <td colspan="9" class="text-center py-4 text-muted">
                                    <i class="bi bi-calendar-x fs-2 d-block text-secondary mb-2"></i>
                                    Belum ada jadwal materi atau tugas yang ditayangkan.
                                </td>
                            </tr>
                        <?php else: ?>
                            <?php $no = 1; foreach ($schedules as $s): ?>
                                <tr>
                                    <td class="text-center"><?= $no++ ?></td>
                                    <td class="text-center font-monospace">
                                        <span class="badge bg-light text-dark border">
                                            <?= format_indo_date($s['jadwal_materi']) ?>
                                        </span>
                                    </td>
                                    <td class="text-center">
                                        <?php if ((int)$s['jenis'] === 1): ?>
                                            <span class="badge bg-primary-subtle text-primary border border-primary-subtle px-2 py-1">
                                                Materi
                                            </span>
                                        <?php else: ?>
                                            <span class="badge bg-warning-subtle text-warning-emphasis border border-warning-subtle px-2 py-1">
                                                Tugas
                                            </span>
                                        <?php endif; ?>
                                    </td>
                                    <td>
                                        <span class="badge bg-primary text-white"><?= escape_html($s['nama_kelas']) ?></span>
                                    </td>
                                    <td>
                                        <span class="badge bg-light text-secondary border"><?= escape_html($s['nama_mapel']) ?></span>
                                    </td>
                                    <td>
                                        <div class="fw-bold text-dark"><?= escape_html($s['judul_materi']) ?></div>
                                        <small class="text-muted font-monospace"><?= escape_html($s['kode_materi']) ?></small>
                                    </td>
                                    <td>
                                        <small><?= escape_html($s['nama_guru']) ?></small>
                                    </td>
                                    <td class="text-center">
                                        <?php if ((int)$s['jenis'] === 2): ?>
                                            <a href="<?= base_url('/elearning/presensi-nilai/submissions/' . $s['id_kjm']) ?>" class="badge bg-success text-white text-decoration-none" title="Lihat Submisi Siswa">
                                                <i class="bi bi-check2-all me-1"></i><?= $s['total_kumpul'] ?> / <?= $s['total_siswa_kelas'] ?> Kumpul
                                            </a>
                                        <?php else: ?>
                                            <span class="badge bg-info text-white">
                                                <i class="bi bi-eye me-1"></i><?= $s['total_akses'] ?> / <?= $s['total_siswa_kelas'] ?> Membaca
                                            </span>
                                        <?php endif; ?>
                                    </td>
                                    <td class="text-center">
                                        <div class="btn-group btn-group-sm">
                                            <a href="<?= base_url('/elearning/materi-tugas/detail/' . $s['id_materi']) ?>" class="btn btn-outline-info" title="Lihat Isi">
                                                <i class="bi bi-eye"></i>
                                            </a>
                                            <a href="<?= base_url('/elearning/jadwal-materi/delete/' . $s['id_kjm']) ?>" class="btn btn-outline-danger" title="Hapus Jadwal"
                                               onclick="return confirm('Yakin ingin menghapus jadwal tayang ini?');">
                                                <i class="bi bi-trash"></i>
                                            </a>
                                        </div>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>

<!-- Modal Jadwalkan Materi / Tugas -->
<div class="modal fade" id="modalTambahJadwal" tabindex="-1">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-header py-2">
                <h6 class="modal-title fw-bold"><i class="bi bi-calendar-plus text-primary me-1"></i> Jadwalkan Materi / Tugas</h6>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <form action="<?= base_url('/elearning/jadwal-materi/store') ?>" method="POST">
                <div class="modal-body">
                    <div class="mb-3">
                        <label class="form-label small fw-semibold">Pilih Materi / Tugas <span class="text-danger">*</span></label>
                        <select name="id_materi" class="form-select form-select-sm" required>
                            <option value="">-- Pilih Materi / Tugas --</option>
                            <?php foreach ($availableMateri as $am): ?>
                                <option value="<?= $am['id_materi'] ?>">
                                    [<?= (int)$am['jenis'] === 1 ? 'Materi' : 'Tugas' ?>] <?= escape_html($am['judul_materi']) ?> (<?= escape_html($am['nama_mapel']) ?> - <?= escape_html($am['nama_guru']) ?>)
                                </option>
                            <?php endforeach; ?>
                        </select>
                    </div>

                    <div class="mb-3">
                        <label class="form-label small fw-semibold">Tanggal Tayang <span class="text-danger">*</span></label>
                        <input type="date" name="jadwal_materi" class="form-control form-control-sm" value="<?= date('Y-m-d') ?>" required>
                    </div>

                    <div class="mb-2">
                        <label class="form-label small fw-semibold">Target Kelas <span class="text-danger">*</span></label>
                        <div class="border rounded p-2 bg-light" style="max-height: 180px; overflow-y: auto;">
                            <?php foreach ($kelasList as $k): ?>
                                <div class="form-check">
                                    <input class="form-check-input" type="checkbox" name="id_kelas[]" value="<?= $k['id_kelas'] ?>" id="modal_k_<?= $k['id_kelas'] ?>">
                                    <label class="form-check-label small" for="modal_k_<?= $k['id_kelas'] ?>">
                                        <?= escape_html($k['nama_kelas']) ?>
                                    </label>
                                </div>
                            <?php endforeach; ?>
                        </div>
                    </div>
                </div>
                <div class="modal-footer py-2">
                    <button type="button" class="btn btn-light btn-sm" data-bs-dismiss="modal">Batal</button>
                    <button type="submit" class="btn btn-primary btn-sm">
                        <i class="bi bi-save me-1"></i> Simpan Jadwal
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

