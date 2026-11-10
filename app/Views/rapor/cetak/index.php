<div class="card shadow-sm border-0 mb-4">
    <div class="card-header bg-white py-3 d-flex align-items-center justify-content-between flex-wrap gap-2">
        <h5 class="card-title mb-0"><i class="bi bi-printer me-2 text-primary"></i>Pusat Cetak E-Rapor & Daftar Kumpulan Nilai (DKN)</h5>
        <?php if ($selectedKelas > 0): ?>
            <div class="d-flex gap-2">
                <a href="<?= base_url('/rapor/cetak/dkn/' . $selectedKelas) ?>" target="_blank" class="btn btn-sm btn-outline-dark">
                    <i class="bi bi-table me-1"></i>Cetak DKN Kelas
                </a>
                <a href="<?= base_url('/rapor/cetak/dkn-excel/' . $selectedKelas) ?>" class="btn btn-sm btn-outline-success">
                    <i class="bi bi-file-earmark-excel me-1"></i>Export DKN (Excel)
                </a>
            </div>
        <?php endif; ?>
    </div>
    <div class="card-body">
        <?php if (!empty($_SESSION['flash_error'])): ?>
            <div class="alert alert-danger alert-dismissible fade show" role="alert">
                <i class="bi bi-exclamation-triangle me-2"></i><?= htmlspecialchars($_SESSION['flash_error'], ENT_QUOTES, 'UTF-8') ?>
                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
            </div>
            <?php unset($_SESSION['flash_error']); ?>
        <?php endif; ?>

        <form method="GET" action="<?= base_url('/rapor/cetak') ?>" class="row g-3 align-items-center mb-4">
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
            <div class="table-responsive">
                <table class="table table-bordered table-hover align-middle">
                    <thead class="table-light">
                        <tr class="text-center">
                            <th style="width: 50px;">No</th>
                            <th style="width: 120px;">NIS</th>
                            <th style="width: 130px;">NISN</th>
                            <th>Nama Lengkap Siswa</th>
                            <th style="width: 80px;">L/P</th>
                            <th style="width: 280px;">Aksi Cetak Dokumen Rapor</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php $no = 1; foreach ($students as $s): ?>
                            <tr>
                                <td class="text-center"><?= $no++ ?></td>
                                <td class="text-center font-monospace"><?= htmlspecialchars($s['nis'] ?? '-', ENT_QUOTES, 'UTF-8') ?></td>
                                <td class="text-center font-monospace"><?= htmlspecialchars($s['nisn'] ?? '-', ENT_QUOTES, 'UTF-8') ?></td>
                                <td>
                                    <strong><?= htmlspecialchars($s['nama'], ENT_QUOTES, 'UTF-8') ?></strong>
                                </td>
                                <td class="text-center"><?= htmlspecialchars($s['jenis_kelamin'] ?? '-', ENT_QUOTES, 'UTF-8') ?></td>
                                <td class="text-center">
                                    <div class="btn-group btn-group-sm">
                                        <a href="<?= base_url('/rapor/cetak/pts/' . $selectedKelas . '/' . $s['id_siswa']) ?>" target="_blank" class="btn btn-outline-primary" title="Cetak Rapor Tengah Semester">
                                            <i class="bi bi-printer me-1"></i>Rapor PTS
                                        </a>
                                        <a href="<?= base_url('/rapor/cetak/pas/' . $selectedKelas . '/' . $s['id_siswa']) ?>" target="_blank" class="btn btn-primary" title="Cetak Rapor Akhir Semester Lengkap">
                                            <i class="bi bi-file-earmark-pdf me-1"></i>Rapor Akhir (PAS)
                                        </a>
                                    </div>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        <?php else: ?>
            <div class="alert alert-info text-center py-4">Belum ada data siswa pada rombel ini.</div>
        <?php endif; ?>
    </div>
</div>

