<div class="card shadow-sm border-0 mb-4">
    <div class="card-header bg-white py-3 d-flex align-items-center justify-content-between">
        <h5 class="card-title mb-0"><i class="bi bi-award me-2 text-primary"></i>Pengaturan KKM & Bobot per Mata Pelajaran</h5>
    </div>
    <div class="card-body">
        <?php if (!empty($_SESSION['flash_success'])): ?>
            <div class="alert alert-success alert-dismissible fade show" role="alert">
                <i class="bi bi-check-circle me-2"></i><?= htmlspecialchars($_SESSION['flash_success'], ENT_QUOTES, 'UTF-8') ?>
                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
            </div>
            <?php unset($_SESSION['flash_success']); ?>
        <?php endif; ?>

        <form method="GET" action="<?= base_url('/rapor/kkm') ?>" class="row g-3 align-items-center mb-4">
            <div class="col-auto">
                <label class="form-label fw-bold mb-0">Pilih Rombel / Kelas:</label>
            </div>
            <div class="col-auto">
                <select name="id_kelas" class="form-select" onchange="this.form.submit()">
                    <?php foreach ($kelasList as $k): ?>
                        <option value="<?= $k['id_kelas'] ?>" <?= (int)$selectedKelas === (int)$k['id_kelas'] ? 'selected' : '' ?>>
                            <?= htmlspecialchars($k['nama_kelas'], ENT_QUOTES, 'UTF-8') ?>
                        </option>
                    <?php endforeach; ?>
                </select>
            </div>
        </form>

        <?php if (!empty($kkmList)): ?>
            <form action="<?= base_url('/rapor/kkm/save') ?>" method="POST">
                <input type="hidden" name="id_kelas" value="<?= $selectedKelas ?>">
                <div class="table-responsive">
                    <table class="table table-bordered table-hover align-middle">
                        <thead class="table-light text-center">
                            <tr>
                                <th style="width: 50px;">No</th>
                                <th>Mata Pelajaran</th>
                                <th style="width: 100px;">Kelompok</th>
                                <th style="width: 120px;">KKM</th>
                                <th style="width: 120px;">Bobot PH (%)</th>
                                <th style="width: 120px;">Bobot PTS (%)</th>
                                <th style="width: 120px;">Bobot PAS (%)</th>
                                <th style="width: 120px;">Beban Jam</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php $no = 1; foreach ($kkmList as $item): ?>
                                <tr>
                                    <td class="text-center"><?= $no++ ?></td>
                                    <td>
                                        <strong><?= htmlspecialchars($item['nama_mapel'], ENT_QUOTES, 'UTF-8') ?></strong>
                                        <div class="small text-muted">Kode: <?= htmlspecialchars($item['kode_mapel'], ENT_QUOTES, 'UTF-8') ?></div>
                                    </td>
                                    <td class="text-center"><span class="badge bg-secondary"><?= htmlspecialchars($item['kelompok'] ?? 'A', ENT_QUOTES, 'UTF-8') ?></span></td>
                                    <td>
                                        <input type="number" name="kkm[<?= $item['id_mapel'] ?>][kkm]" class="form-control text-center" value="<?= (int)$item['kkm'] ?>" min="1" max="100" required>
                                    </td>
                                    <td>
                                        <input type="number" name="kkm[<?= $item['id_mapel'] ?>][bobot_ph]" class="form-control text-center" value="<?= (int)$item['bobot_ph'] ?>" min="0" max="100" required>
                                    </td>
                                    <td>
                                        <input type="number" name="kkm[<?= $item['id_mapel'] ?>][bobot_pts]" class="form-control text-center" value="<?= (int)$item['bobot_pts'] ?>" min="0" max="100" required>
                                    </td>
                                    <td>
                                        <input type="number" name="kkm[<?= $item['id_mapel'] ?>][bobot_pas]" class="form-control text-center" value="<?= (int)$item['bobot_pas'] ?>" min="0" max="100" required>
                                    </td>
                                    <td>
                                        <input type="number" name="kkm[<?= $item['id_mapel'] ?>][beban_jam]" class="form-control text-center" value="<?= (int)$item['beban_jam'] ?>" min="1" max="10" required>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
                <div class="text-end mt-3">
                    <button type="submit" class="btn btn-primary px-4"><i class="bi bi-save me-1"></i>Simpan KKM Seluruh Mapel</button>
                </div>
            </form>
        <?php else: ?>
            <div class="alert alert-info">Silakan pilih kelas untuk mengonfigurasi KKM mata pelajaran.</div>
        <?php endif; ?>
    </div>
</div>

