<div class="card shadow-sm border-0 mb-4">
    <div class="card-header bg-white py-3 d-flex align-items-center justify-content-between flex-wrap gap-2">
        <h5 class="card-title mb-0"><i class="bi bi-pencil-square me-2 text-primary"></i>Entri Nilai Rapor Mata Pelajaran</h5>
        <div class="d-flex gap-2">
            <a href="<?= base_url('/rapor/input-nilai') ?>" class="btn btn-sm btn-outline-secondary">
                <i class="bi bi-arrow-left me-1"></i>Kembali
            </a>
            <?php if ($selectedKelas > 0 && $selectedMapel > 0): ?>
                <form action="<?= base_url('/rapor/input-nilai/sync') ?>" method="POST" class="d-inline">
                    <input type="hidden" name="id_kelas" value="<?= $selectedKelas ?>">
                    <input type="hidden" name="id_mapel" value="<?= $selectedMapel ?>">
                    <button type="submit" class="btn btn-sm btn-outline-success" onclick="return confirm('Tarik otomatis nilai rata-rata tugas E-Learning dan hasil ujian CBT siswa?')">
                        <i class="bi bi-arrow-repeat me-1"></i>Tarik Nilai E-Learning & CBT
                    </button>
                </form>
                <button type="button" class="btn btn-sm btn-outline-primary" data-bs-toggle="modal" data-bs-target="#modalImport">
                    <i class="bi bi-file-earmark-excel me-1"></i>Import Excel / CSV
                </button>
            <?php endif; ?>
        </div>
    </div>
    <div class="card-body">
        <?php if (!empty($_SESSION['flash_success'])): ?>
            <div class="alert alert-success alert-dismissible fade show" role="alert">
                <i class="bi bi-check-circle me-2"></i><?= htmlspecialchars($_SESSION['flash_success'], ENT_QUOTES, 'UTF-8') ?>
                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
            </div>
            <?php unset($_SESSION['flash_success']); ?>
        <?php endif; ?>

        <?php if (!empty($_SESSION['flash_error'])): ?>
            <div class="alert alert-danger alert-dismissible fade show" role="alert">
                <i class="bi bi-exclamation-triangle me-2"></i><?= htmlspecialchars($_SESSION['flash_error'], ENT_QUOTES, 'UTF-8') ?>
                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
            </div>
            <?php unset($_SESSION['flash_error']); ?>
        <?php endif; ?>

        <!-- Filter Kelas & Mapel -->
        <form method="GET" action="<?= base_url('/rapor/input-nilai/mapel') ?>" class="row g-3 align-items-center mb-4">
            <div class="col-md-4">
                <label class="form-label fw-bold mb-1">Pilih Kelas / Rombel:</label>
                <select name="id_kelas" class="form-select" onchange="this.form.submit()">
                    <?php foreach ($kelasList as $k): ?>
                        <option value="<?= $k['id_kelas'] ?>" <?= (int)$selectedKelas === (int)$k['id_kelas'] ? 'selected' : '' ?>>
                            <?= htmlspecialchars($k['nama_kelas'], ENT_QUOTES, 'UTF-8') ?>
                        </option>
                    <?php endforeach; ?>
                </select>
            </div>
            <div class="col-md-5">
                <label class="form-label fw-bold mb-1">Pilih Mata Pelajaran:</label>
                <select name="id_mapel" class="form-select" onchange="this.form.submit()">
                    <?php foreach ($mapelList as $m): ?>
                        <option value="<?= $m['id_mapel'] ?>" <?= (int)$selectedMapel === (int)$m['id_mapel'] ? 'selected' : '' ?>>
                            <?= htmlspecialchars($m['nama_mapel'], ENT_QUOTES, 'UTF-8') ?> (<?= htmlspecialchars($m['kode'] ?? ($m['kode_mapel'] ?? ''), ENT_QUOTES, 'UTF-8') ?>)
                        </option>
                    <?php endforeach; ?>
                </select>
            </div>
            <div class="col-md-3">
                <div class="p-2 border rounded bg-light text-center mt-3">
                    <span class="small fw-bold text-secondary">KKM: </span><span class="badge bg-primary fs-6"><?= (int)$kkmSetting['kkm'] ?></span>
                    <span class="small text-muted ms-2">(PH: <?= (int)$kkmSetting['bobot_ph'] ?>% | PTS: <?= (int)$kkmSetting['bobot_pts'] ?>% | PAS: <?= (int)$kkmSetting['bobot_pas'] ?>%)</span>
                </div>
            </div>
        </form>

        <?php if (!empty($students)): ?>
            <form action="<?= base_url('/rapor/input-nilai/mapel/save') ?>" method="POST">
                <input type="hidden" name="id_kelas" value="<?= $selectedKelas ?>">
                <input type="hidden" name="id_mapel" value="<?= $selectedMapel ?>">

                <div class="table-responsive">
                    <table class="table table-bordered table-hover align-middle">
                        <thead class="table-light text-center">
                            <tr>
                                <th style="width: 40px;" rowspan="2">No</th>
                                <th style="width: 110px;" rowspan="2">NIS</th>
                                <th rowspan="2">Nama Siswa</th>
                                <th colspan="2" class="bg-primary-subtle text-primary">Pengetahuan (KI-3)</th>
                                <th colspan="2" class="bg-info-subtle text-info">Keterampilan (KI-4)</th>
                                <th style="width: 100px;" rowspan="2">Nilai PTS</th>
                                <th style="width: 100px;" rowspan="2">Nilai PAS</th>
                                <th colspan="2" class="bg-success-subtle text-success">Nilai Akhir (NA)</th>
                            </tr>
                            <tr>
                                <th style="width: 90px;" class="bg-primary-subtle">Nilai PH</th>
                                <th style="width: 60px;" class="bg-primary-subtle">Pred</th>
                                <th style="width: 90px;" class="bg-info-subtle">Nilai K</th>
                                <th style="width: 60px;" class="bg-info-subtle">Pred</th>
                                <th style="width: 90px;" class="bg-success-subtle">Skor NA</th>
                                <th style="width: 60px;" class="bg-success-subtle">Pred</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php $no = 1; foreach ($students as $row): ?>
                                <tr>
                                    <td class="text-center"><?= $no++ ?></td>
                                    <td class="text-center font-monospace"><?= htmlspecialchars($row['nis'] ?? '-', ENT_QUOTES, 'UTF-8') ?></td>
                                    <td>
                                        <strong><?= htmlspecialchars($row['nama_siswa'], ENT_QUOTES, 'UTF-8') ?></strong>
                                    </td>
                                    <td>
                                        <input type="number" step="0.01" name="nilai[<?= $row['id_siswa'] ?>][ph]" class="form-control form-control-sm text-center" value="<?= $row['p_rata_rata'] !== null ? htmlspecialchars((string)$row['p_rata_rata'], ENT_QUOTES, 'UTF-8') : '' ?>" min="0" max="100" placeholder="0">
                                    </td>
                                    <td class="text-center fw-bold text-primary">
                                        <?= htmlspecialchars($row['p_predikat'] ?? '-', ENT_QUOTES, 'UTF-8') ?>
                                    </td>
                                    <td>
                                        <input type="number" step="0.01" name="nilai[<?= $row['id_siswa'] ?>][k]" class="form-control form-control-sm text-center" value="<?= $row['k_rata_rata'] !== null ? htmlspecialchars((string)$row['k_rata_rata'], ENT_QUOTES, 'UTF-8') : '' ?>" min="0" max="100" placeholder="0">
                                    </td>
                                    <td class="text-center fw-bold text-info">
                                        <?= htmlspecialchars($row['k_predikat'] ?? '-', ENT_QUOTES, 'UTF-8') ?>
                                    </td>
                                    <td>
                                        <input type="number" step="0.01" name="nilai[<?= $row['id_siswa'] ?>][pts]" class="form-control form-control-sm text-center" value="<?= $row['pts'] !== null ? htmlspecialchars((string)$row['pts'], ENT_QUOTES, 'UTF-8') : '' ?>" min="0" max="100" placeholder="-">
                                    </td>
                                    <td>
                                        <input type="number" step="0.01" name="nilai[<?= $row['id_siswa'] ?>][pas]" class="form-control form-control-sm text-center" value="<?= $row['pas'] !== null ? htmlspecialchars((string)$row['pas'], ENT_QUOTES, 'UTF-8') : '' ?>" min="0" max="100" placeholder="-">
                                    </td>
                                    <td class="text-center fw-bold bg-success-subtle">
                                        <?= $row['akhir'] !== null ? number_format((float)$row['akhir'], 2) : '-' ?>
                                    </td>
                                    <td class="text-center fw-bold bg-success-subtle text-success">
                                        <?= htmlspecialchars($row['predikat'] ?? '-', ENT_QUOTES, 'UTF-8') ?>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>

                <div class="text-end mt-3">
                    <button type="submit" class="btn btn-primary px-4"><i class="bi bi-save me-1"></i>Simpan Seluruh Nilai Rapor</button>
                </div>
            </form>
        <?php else: ?>
            <div class="alert alert-info text-center py-4">Belum ada siswa yang terdaftar pada kelas ini.</div>
        <?php endif; ?>
    </div>
</div>

<!-- Modal Import Excel/CSV -->
<div class="modal fade" id="modalImport" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title"><i class="bi bi-file-earmark-arrow-up me-2"></i>Import Nilai CSV / Excel</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <form action="<?= base_url('/rapor/input-nilai/import-excel') ?>" method="POST" enctype="multipart/form-data">
                <input type="hidden" name="id_kelas" value="<?= $selectedKelas ?>">
                <input type="hidden" name="id_mapel" value="<?= $selectedMapel ?>">
                <div class="modal-body">
                    <p class="small text-muted mb-3">
                        Format file CSV/Excel harus memiliki kolom berurutan: <br>
                        <code>NIS, Nama Siswa, Nilai PH, Nilai Keterampilan, Nilai PTS, Nilai PAS</code>
                    </p>
                    <div class="mb-3">
                        <label class="form-label fw-bold">Pilih File (.csv / .txt):</label>
                        <input type="file" name="excel_file" class="form-control" accept=".csv, .txt, .xlsx" required>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Batal</button>
                    <button type="submit" class="btn btn-primary"><i class="bi bi-upload me-1"></i>Proses Import</button>
                </div>
            </form>
        </div>
    </div>
</div>
