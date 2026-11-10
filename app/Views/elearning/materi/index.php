<div class="container-fluid py-3">
    <div class="d-flex justify-content-between align-items-center mb-3">
        <div>
            <h4 class="mb-0 fw-bold"><i class="bi bi-file-earmark-text text-primary me-2"></i>Materi & Tugas Belajar</h4>
            <small class="text-muted">TP <?= escape_html($tp['tahun'] ?? '-') ?> - Semester <?= escape_html($smt['nama_smt'] ?? '-') ?></small>
        </div>
        <div class="d-flex gap-2">
            <a href="<?= base_url('/elearning/materi-tugas/create?jenis=1') ?>" class="btn btn-primary btn-sm">
                <i class="bi bi-plus-circle me-1"></i> Buat Materi
            </a>
            <a href="<?= base_url('/elearning/materi-tugas/create?jenis=2') ?>" class="btn btn-warning btn-sm text-dark">
                <i class="bi bi-pencil-square me-1"></i> Buat Tugas
            </a>
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
            <form action="<?= base_url('/elearning/materi-tugas') ?>" method="GET" class="row g-2 align-items-center">
                <div class="col-auto">
                    <div class="btn-group btn-group-sm" role="group">
                        <a href="<?= base_url('/elearning/materi-tugas') ?>" class="btn <?= $jenis === null ? 'btn-primary' : 'btn-outline-primary' ?>">Semua</a>
                        <a href="<?= base_url('/elearning/materi-tugas?jenis=1') ?>" class="btn <?= $jenis === 1 ? 'btn-primary' : 'btn-outline-primary' ?>">Materi</a>
                        <a href="<?= base_url('/elearning/materi-tugas?jenis=2') ?>" class="btn <?= $jenis === 2 ? 'btn-primary' : 'btn-outline-primary' ?>">Tugas</a>
                    </div>
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
                    <select name="kelas_id" class="form-select form-select-sm" onchange="this.form.submit()">
                        <option value="">-- Semua Kelas --</option>
                        <?php foreach ($kelasList as $k): ?>
                            <option value="<?= $k['id_kelas'] ?>" <?= (int)$k['id_kelas'] === (int)$selectedKelasId ? 'selected' : '' ?>>
                                <?= escape_html($k['nama_kelas']) ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                </div>

                <?php if ($role === 'admin' && !empty($guruList)): ?>
                <div class="col-md-3">
                    <select name="guru_id" class="form-select form-select-sm" onchange="this.form.submit()">
                        <option value="">-- Semua Guru Pengampu --</option>
                        <?php foreach ($guruList as $g): ?>
                            <option value="<?= $g['id_guru'] ?>" <?= (int)$g['id_guru'] === (int)$selectedGuruId ? 'selected' : '' ?>>
                                <?= escape_html($g['nama_guru']) ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                </div>
                <?php endif; ?>
            </form>
        </div>
    </div>

    <!-- Data Table -->
    <div class="card shadow-sm border-0">
        <div class="card-body p-0">
            <div class="table-responsive">
                <table class="table table-hover table-striped align-middle mb-0 datatable" style="font-size: 13px;">
                    <thead class="table-light">
                        <tr>
                            <th style="width: 40px;" class="text-center">No</th>
                            <th style="width: 90px;" class="text-center">Tipe</th>
                            <th>Kode & Judul</th>
                            <th>Mata Pelajaran</th>
                            <th>Guru Pengampu</th>
                            <th>Kelas Penerima</th>
                            <th class="text-center">Media / Lampiran</th>
                            <th class="text-center">Jadwal</th>
                            <th style="width: 140px;" class="text-center">Aksi</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if (empty($items)): ?>
                            <tr>
                                <td colspan="9" class="text-center py-4 text-muted">
                                    <i class="bi bi-inbox fs-2 d-block text-secondary mb-2"></i>
                                    Belum ada data materi atau tugas yang dibuat.
                                </td>
                            </tr>
                        <?php else: ?>
                            <?php $no = 1; foreach ($items as $item): ?>
                                <tr>
                                    <td class="text-center"><?= $no++ ?></td>
                                    <td class="text-center">
                                        <?php if ((int)$item['jenis'] === 1): ?>
                                            <span class="badge bg-primary-subtle text-primary border border-primary-subtle px-2 py-1">
                                                <i class="bi bi-book me-1"></i> Materi
                                            </span>
                                        <?php else: ?>
                                            <span class="badge bg-warning-subtle text-warning-emphasis border border-warning-subtle px-2 py-1">
                                                <i class="bi bi-pencil me-1"></i> Tugas
                                            </span>
                                        <?php endif; ?>
                                    </td>
                                    <td>
                                        <div class="fw-bold text-dark"><?= escape_html($item['judul_materi']) ?></div>
                                        <small class="text-muted font-monospace"><?= escape_html($item['kode_materi']) ?></small>
                                    </td>
                                    <td>
                                        <span class="badge bg-light text-dark border"><?= escape_html($item['nama_mapel']) ?></span>
                                    </td>
                                    <td>
                                        <small><?= escape_html($item['nama_guru']) ?></small>
                                    </td>
                                    <td>
                                        <?php
                                        $kelasNames = [];
                                        foreach ($kelasList as $k) {
                                            if (in_array((int)$k['id_kelas'], $item['kelas_ids'])) {
                                                $kelasNames[] = $k['nama_kelas'];
                                            }
                                        }
                                        ?>
                                        <?php if (empty($kelasNames)): ?>
                                            <span class="text-muted small">-</span>
                                        <?php else: ?>
                                            <?php foreach ($kelasNames as $kn): ?>
                                                <span class="badge bg-secondary-subtle text-dark border me-1" style="font-size: 11px;">
                                                    <?= escape_html($kn) ?>
                                                </span>
                                            <?php endforeach; ?>
                                        <?php endif; ?>
                                    </td>
                                    <td class="text-center">
                                        <?php if (!empty($item['link_video'])): ?>
                                            <span class="badge bg-danger text-white me-1" title="Ada Link Video">
                                                <i class="bi bi-youtube"></i> Video
                                            </span>
                                        <?php endif; ?>
                                        <?php if (!empty($item['file_list'])): ?>
                                            <span class="badge bg-info text-white" title="<?= count($item['file_list']) ?> Berkas Lampiran">
                                                <i class="bi bi-paperclip"></i> <?= count($item['file_list']) ?> File
                                            </span>
                                        <?php endif; ?>
                                        <?php if (empty($item['link_video']) && empty($item['file_list'])): ?>
                                            <span class="text-muted small">Teks saja</span>
                                        <?php endif; ?>
                                    </td>
                                    <td class="text-center">
                                        <span class="badge bg-light text-secondary border">
                                            <?= (int)$item['total_jadwal'] ?> Kelas
                                        </span>
                                    </td>
                                    <td class="text-center">
                                        <div class="btn-group btn-group-sm">
                                            <a href="<?= base_url('/elearning/materi-tugas/detail/' . $item['id_materi']) ?>" class="btn btn-outline-info" title="Lihat Detail & Diskusi">
                                                <i class="bi bi-eye"></i>
                                            </a>
                                            <a href="<?= base_url('/elearning/materi-tugas/edit/' . $item['id_materi']) ?>" class="btn btn-outline-primary" title="Edit">
                                                <i class="bi bi-pencil"></i>
                                            </a>
                                            <a href="<?= base_url('/elearning/materi-tugas/delete/' . $item['id_materi']) ?>" class="btn btn-outline-danger" title="Hapus"
                                               onclick="return confirm('Yakin ingin menghapus materi/tugas ini?');">
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

