<div class="card shadow-sm border-0 mb-4">
    <div class="card-header bg-white py-3 d-flex align-items-center justify-content-between">
        <h5 class="card-title mb-0"><i class="bi bi-chat-quote me-2 text-warning"></i>Catatan Wali Kelas & Presensi Kehadiran</h5>
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

        <form method="GET" action="<?= base_url('/rapor/input-nilai/catatan') ?>" class="row g-3 align-items-center mb-4">
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
            <form action="<?= base_url('/rapor/input-nilai/catatan/save') ?>" method="POST">
                <input type="hidden" name="id_kelas" value="<?= $selectedKelas ?>">

                <div class="table-responsive">
                    <table class="table table-bordered table-hover align-middle">
                        <thead class="table-light">
                            <tr class="text-center">
                                <th style="width: 40px;" rowspan="2">No</th>
                                <th style="width: 110px;" rowspan="2">NIS</th>
                                <th rowspan="2">Nama Siswa</th>
                                <th colspan="3" class="bg-warning-subtle text-dark">Ketidakhadiran (Hari)</th>
                                <th rowspan="2">Catatan / Saran Motivasi Wali Kelas</th>
                            </tr>
                            <tr class="text-center bg-warning-subtle">
                                <th style="width: 80px;">Sakit</th>
                                <th style="width: 80px;">Izin</th>
                                <th style="width: 80px;">Alfa</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php $no = 1; foreach ($students as $row): 
                                $absensi = ['sakit' => 0, 'izin' => 0, 'alfa' => 0];
                                if (!empty($row['nilai'])) {
                                    $dec = json_decode($row['nilai'], true);
                                    if (is_array($dec)) {
                                        $absensi = array_merge($absensi, $dec);
                                    }
                                }
                            ?>
                                <tr>
                                    <td class="text-center"><?= $no++ ?></td>
                                    <td class="text-center font-monospace"><?= htmlspecialchars($row['nis'] ?? '-', ENT_QUOTES, 'UTF-8') ?></td>
                                    <td><strong><?= htmlspecialchars($row['nama_siswa'], ENT_QUOTES, 'UTF-8') ?></strong></td>
                                    <td>
                                        <input type="number" name="catatan[<?= $row['id_siswa'] ?>][sakit]" class="form-control form-control-sm text-center" value="<?= (int)$absensi['sakit'] ?>" min="0">
                                    </td>
                                    <td>
                                        <input type="number" name="catatan[<?= $row['id_siswa'] ?>][izin]" class="form-control form-control-sm text-center" value="<?= (int)$absensi['izin'] ?>" min="0">
                                    </td>
                                    <td>
                                        <input type="number" name="catatan[<?= $row['id_siswa'] ?>][alfa]" class="form-control form-control-sm text-center" value="<?= (int)$absensi['alfa'] ?>" min="0">
                                    </td>
                                    <td>
                                        <textarea name="catatan[<?= $row['id_siswa'] ?>][deskripsi]" class="form-control form-control-sm" rows="2" placeholder="Tuliskan motivasi, saran perbaikan, atau apresiasi prestasi siswa..."><?= htmlspecialchars($row['deskripsi'] ?? 'Tingkatkan terus semangat belajar dan pertahankan prestasi yang telah diraih.', ENT_QUOTES, 'UTF-8') ?></textarea>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>

                <div class="text-end mt-3">
                    <button type="submit" class="btn btn-warning px-4"><i class="bi bi-save me-1"></i>Simpan Catatan & Presensi</button>
                </div>
            </form>
        <?php else: ?>
            <div class="alert alert-info text-center py-4">Belum ada siswa yang terdaftar pada kelas ini.</div>
        <?php endif; ?>
    </div>
</div>

