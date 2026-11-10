<div class="card shadow-sm border-0 mb-4">
    <div class="card-header bg-white py-3 d-flex align-items-center justify-content-between">
        <h5 class="card-title mb-0"><i class="bi bi-mortarboard me-2 text-dark"></i>Keputusan Kenaikan Kelas & Kelulusan</h5>
        <a href="<?= base_url('/rapor/input-nilai') ?>" class="btn btn-sm btn-outline-secondary">
            <i class="bi bi-arrow-left me-1"></i>Kembali
        </a>
    </div>
    <div class="card-body">
        <?php if (!empty($_SESSION['flash_success'])): ?>
            <div class="alert alert-success alert-dismissible fade show" role="alert">
                <i class="bi bi-check-circle me-2"></i><?= htmlspecialchars($_SESSION['flash_success'], ENT_QUOTES, 'UTF-8') ?>
                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
            </div>
            <?php unset($_SESSION['flash_success']); ?>
        <?php endif; ?>

        <div class="alert alert-warning small mb-4">
            <i class="bi bi-info-circle me-1"></i>
            Menu ini digunakan pada akhir Tahun Pelajaran (Semester Genap) untuk menentukan apakah siswa berhak Naik ke tingkat selanjutnya atau Lulus dari satuan pendidikan.
        </div>

        <form method="GET" action="<?= base_url('/rapor/input-nilai/naik') ?>" class="row g-3 align-items-center mb-4">
            <div class="col-md-5">
                <label class="form-label fw-bold mb-1">Pilih Kelas / Rombel:</label>
                <select name="id_kelas" class="form-select" onchange="this.form.submit()">
                    <?php foreach ($kelasList as $k): ?>
                        <option value="<?= $k['id_kelas'] ?>" <?= (int)$selectedKelas === (int)$k['id_kelas'] ? 'selected' : '' ?>>
                            <?= htmlspecialchars($k['nama_kelas'], ENT_QUOTES, 'UTF-8') ?>
                        </option>
                    <?php endforeach; ?>
                </select>
            </div>
        </form>

        <?php if (!empty($students)): ?>
            <form action="<?= base_url('/rapor/input-nilai/naik/save') ?>" method="POST">
                <input type="hidden" name="id_kelas" value="<?= $selectedKelas ?>">

                <div class="table-responsive">
                    <table class="table table-bordered table-hover align-middle">
                        <thead class="table-light">
                            <tr class="text-center">
                                <th style="width: 50px;">No</th>
                                <th style="width: 120px;">NIS</th>
                                <th>Nama Siswa</th>
                                <th style="width: 220px;">Status Keputusan</th>
                                <th>Keterangan / Catatan Tambahan</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php $no = 1; foreach ($students as $row): 
                                $status = isset($row['naik']) ? (int)$row['naik'] : 1;
                            ?>
                                <tr>
                                    <td class="text-center"><?= $no++ ?></td>
                                    <td class="text-center font-monospace"><?= htmlspecialchars($row['nis'] ?? '-', ENT_QUOTES, 'UTF-8') ?></td>
                                    <td><strong><?= htmlspecialchars($row['nama_siswa'], ENT_QUOTES, 'UTF-8') ?></strong></td>
                                    <td>
                                        <select name="naik[<?= $row['id_siswa'] ?>][status]" class="form-select fw-bold <?= $status === 1 ? 'text-success' : 'text-danger' ?>">
                                            <option value="1" <?= $status === 1 ? 'selected' : '' ?>>Naik Kelas / Lulus</option>
                                            <option value="0" <?= $status === 0 ? 'selected' : '' ?>>Tinggal Kelas / Tidak Lulus</option>
                                        </select>
                                    </td>
                                    <td>
                                        <input type="text" name="naik[<?= $row['id_siswa'] ?>][keterangan]" class="form-control form-control-sm" value="<?= htmlspecialchars($row['keterangan'] ?? 'Naik ke kelas berikutnya.', ENT_QUOTES, 'UTF-8') ?>" placeholder="Keterangan naik kelas / lulus...">
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>

                <div class="text-end mt-3">
                    <button type="submit" class="btn btn-dark px-4"><i class="bi bi-save me-1"></i>Simpan Keputusan Kenaikan</button>
                </div>
            </form>
        <?php else: ?>
            <div class="alert alert-info text-center py-4">Belum ada siswa yang terdaftar pada kelas ini.</div>
        <?php endif; ?>
    </div>
</div>

