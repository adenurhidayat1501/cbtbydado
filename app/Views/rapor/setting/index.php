<div class="row">
    <div class="col-md-8 mx-auto">
        <div class="card shadow-sm border-0">
            <div class="card-header bg-primary text-white d-flex align-items-center justify-content-between">
                <h5 class="card-title mb-0"><i class="bi bi-gear-wide me-2"></i>Pengaturan Umum E-Rapor & KKM</h5>
                <span class="badge bg-light text-primary">TP: <?= htmlspecialchars($activeTp['tahun'] ?? '-', ENT_QUOTES, 'UTF-8') ?> | SMT: <?= htmlspecialchars($activeSmt['nama_smt'] ?? '-', ENT_QUOTES, 'UTF-8') ?></span>
            </div>
            <form action="<?= base_url('/rapor/setting/save') ?>" method="POST">
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

                    <div class="mb-3">
                        <label class="form-label fw-bold">Model Penentuan KKM</label>
                        <div class="form-check">
                            <input class="form-check-input" type="radio" name="kkm_tunggal" id="kkm1" value="1" <?= (int)($setting['kkm_tunggal'] ?? 1) === 1 ? 'checked' : '' ?>>
                            <label class="form-check-label" for="kkm1">
                                <strong>KKM Tunggal / Satuan Pendidikan</strong> (Satu nilai KKM berlaku untuk seluruh mata pelajaran)
                            </label>
                        </div>
                        <div class="form-check">
                            <input class="form-check-input" type="radio" name="kkm_tunggal" id="kkm0" value="0" <?= (int)($setting['kkm_tunggal'] ?? 1) === 0 ? 'checked' : '' ?>>
                            <label class="form-check-label" for="kkm0">
                                <strong>Multi KKM</strong> (Setiap mata pelajaran memiliki nilai KKM dan bobot masing-masing)
                            </label>
                        </div>
                    </div>

                    <div class="mb-3">
                        <label class="form-label fw-bold">Nilai Standar KKM Sekolah</label>
                        <input type="number" name="kkm" class="form-control" value="<?= (int)($setting['kkm'] ?? 75) ?>" min="1" max="100" required>
                        <small class="text-muted">Digunakan sebagai KKM default untuk predikat jika KKM khusus mapel belum ditentukan.</small>
                    </div>

                    <div class="row g-3 mb-3">
                        <div class="col-md-4">
                            <label class="form-label fw-bold">Bobot Penilaian Harian (PH)</label>
                            <div class="input-group">
                                <input type="number" name="bobot_ph" class="form-control" value="<?= (int)($setting['bobot_ph'] ?? 50) ?>" min="0" max="100" required>
                                <span class="input-group-text">%</span>
                            </div>
                        </div>
                        <div class="col-md-4">
                            <label class="form-label fw-bold">Bobot Penilaian Tengah Smt (PTS)</label>
                            <div class="input-group">
                                <input type="number" name="bobot_pts" class="form-control" value="<?= (int)($setting['bobot_pts'] ?? 25) ?>" min="0" max="100" required>
                                <span class="input-group-text">%</span>
                            </div>
                        </div>
                        <div class="col-md-4">
                            <label class="form-label fw-bold">Bobot Penilaian Akhir Smt (PAS)</label>
                            <div class="input-group">
                                <input type="number" name="bobot_pas" class="form-control" value="<?= (int)($setting['bobot_pas'] ?? 25) ?>" min="0" max="100" required>
                                <span class="input-group-text">%</span>
                            </div>
                        </div>
                    </div>

                    <div class="row g-3 mb-3">
                        <div class="col-md-4">
                            <label class="form-label fw-bold">Tanggal Cetak Rapor PTS</label>
                            <input type="date" name="tgl_rapor_pts" class="form-control" value="<?= htmlspecialchars($setting['tgl_rapor_pts'] ?? date('Y-m-d'), ENT_QUOTES, 'UTF-8') ?>">
                        </div>
                        <div class="col-md-4">
                            <label class="form-label fw-bold">Tanggal Cetak Rapor Akhir Smt</label>
                            <input type="date" name="tgl_rapor_akhir" class="form-control" value="<?= htmlspecialchars($setting['tgl_rapor_akhir'] ?? date('Y-m-d'), ENT_QUOTES, 'UTF-8') ?>">
                        </div>
                        <div class="col-md-4">
                            <label class="form-label fw-bold">Tanggal Rapor Kelas Akhir</label>
                            <input type="date" name="tgl_rapor_kelas_akhir" class="form-control" value="<?= htmlspecialchars($setting['tgl_rapor_kelas_akhir'] ?? date('Y-m-d'), ENT_QUOTES, 'UTF-8') ?>">
                        </div>
                    </div>

                    <div class="row g-3 mb-3">
                        <div class="col-md-6">
                            <div class="form-check form-switch mt-2">
                                <input class="form-check-input" type="checkbox" name="nip_kepsek" value="1" id="swNipKepsek" <?= (int)($setting['nip_kepsek'] ?? 1) === 1 ? 'checked' : '' ?>>
                                <label class="form-check-label fw-bold" for="swNipKepsek">Tampilkan NIP Kepala Sekolah pada Cetak Rapor</label>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="form-check form-switch mt-2">
                                <input class="form-check-input" type="checkbox" name="nip_walikelas" value="1" id="swNipWali" <?= (int)($setting['nip_walikelas'] ?? 1) === 1 ? 'checked' : '' ?>>
                                <label class="form-check-label fw-bold" for="swNipWali">Tampilkan NIP Wali Kelas pada Cetak Rapor</label>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="card-footer bg-light text-end">
                    <button type="submit" class="btn btn-primary px-4"><i class="bi bi-save me-1"></i>Simpan Pengaturan</button>
                </div>
            </form>
        </div>
    </div>
</div>

