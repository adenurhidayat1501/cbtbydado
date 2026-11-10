<div class="card shadow-sm border-0 mb-4">
    <div class="card-header bg-white py-3 d-flex align-items-center justify-content-between">
        <h5 class="card-title mb-0"><i class="bi bi-heart-pulse me-2 text-success"></i>Entri Nilai Sikap (Spiritual & Sosial)</h5>
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

        <form method="GET" action="<?= base_url('/rapor/input-nilai/sikap') ?>" class="row g-3 align-items-center mb-4">
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
            <div class="col-md-5">
                <label class="form-label fw-bold mb-1">Jenis Sikap:</label>
                <select name="jenis" class="form-select" onchange="this.form.submit()">
                    <option value="1" <?= (int)$selectedJenis === 1 ? 'selected' : '' ?>>Sikap Spiritual (KI-1)</option>
                    <option value="2" <?= (int)$selectedJenis === 2 ? 'selected' : '' ?>>Sikap Sosial (KI-2)</option>
                </select>
            </div>
        </form>

        <?php if (!empty($students)): ?>
            <form action="<?= base_url('/rapor/input-nilai/sikap/save') ?>" method="POST">
                <input type="hidden" name="id_kelas" value="<?= $selectedKelas ?>">
                <input type="hidden" name="jenis" value="<?= $selectedJenis ?>">

                <div class="table-responsive">
                    <table class="table table-bordered table-hover align-middle">
                        <thead class="table-light">
                            <tr class="text-center">
                                <th style="width: 50px;">No</th>
                                <th style="width: 120px;">NIS</th>
                                <th>Nama Siswa</th>
                                <th style="width: 140px;">Predikat Sikap</th>
                                <th>Deskripsi Capaian Sikap</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php $no = 1; foreach ($students as $row): 
                                $nilaiJson = !empty($row['nilai']) ? json_decode($row['nilai'], true) : [];
                                $pred = is_array($nilaiJson) ? ($nilaiJson['predikat'] ?? 'B') : 'B';
                            ?>
                                <tr>
                                    <td class="text-center"><?= $no++ ?></td>
                                    <td class="text-center font-monospace"><?= htmlspecialchars($row['nis'] ?? '-', ENT_QUOTES, 'UTF-8') ?></td>
                                    <td><strong><?= htmlspecialchars($row['nama_siswa'], ENT_QUOTES, 'UTF-8') ?></strong></td>
                                    <td>
                                        <select name="sikap[<?= $row['id_siswa'] ?>][nilai][predikat]" class="form-select text-center fw-bold">
                                            <option value="A" <?= $pred === 'A' ? 'selected' : '' ?>>A (Sangat Baik)</option>
                                            <option value="B" <?= $pred === 'B' ? 'selected' : '' ?>>B (Baik)</option>
                                            <option value="C" <?= $pred === 'C' ? 'selected' : '' ?>>C (Cukup)</option>
                                            <option value="D" <?= $pred === 'D' ? 'selected' : '' ?>>D (Kurang)</option>
                                        </select>
                                    </td>
                                    <td>
                                        <textarea name="sikap[<?= $row['id_siswa'] ?>][deskripsi]" class="form-control form-control-sm" rows="2" placeholder="Catatan deskripsi sikap siswa..."><?= htmlspecialchars($row['deskripsi'] ?? ($selectedJenis === 1 ? 'Selalu taat beribadah dan menunjukkan sikap toleransi beragama yang sangat baik.' : 'Menunjukkan sikap santun, jujur, dan bertanggung jawab dalam pergaulan sehari-hari.'), ENT_QUOTES, 'UTF-8') ?></textarea>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>

                <div class="text-end mt-3">
                    <button type="submit" class="btn btn-success px-4"><i class="bi bi-save me-1"></i>Simpan Nilai Sikap</button>
                </div>
            </form>
        <?php else: ?>
            <div class="alert alert-info text-center py-4">Belum ada siswa yang terdaftar pada kelas ini.</div>
        <?php endif; ?>
    </div>
</div>

