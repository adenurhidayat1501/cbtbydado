<?php
$tpList = (new \App\Models\MasterTp())->all('tahun DESC');
$smtList = (new \App\Models\MasterSmt())->all('id_smt ASC');
$activeTp = active_tp();
$activeSmt = active_smt();
$isAdmin = auth_role() === 'admin';
?>

<footer id="footer" class="d-flex flex-wrap justify-content-between align-items-center">
    <div>
        <strong>DADO CBT v1.5.3 (FIXED)</strong> — AKM, E-Learning & E-Rapor Terpadu
    </div>
    <div>
        &copy; <?= date('Y') ?> Hak Cipta Terpelihara. Server Time: <span class="badge bg-light text-dark border"><?= date('d M Y, H:i') ?> WIB</span>
    </div>
</footer>

<!-- MODAL SAKLAR TAHUN PELAJARAN & SEMESTER AKTIF -->
<div class="modal fade" id="switchTpSmtModal" tabindex="-1" aria-labelledby="switchTpSmtModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content border-0 shadow">
            <div class="modal-header bg-primary text-white">
                <h6 class="modal-title fw-bold" id="switchTpSmtModalLabel">
                    <i class="bi bi-arrow-repeat me-1"></i> Ganti Tahun Pelajaran & Semester Aktif
                </h6>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <form action="<?= base_url('/settings/tp-smt/switch') ?>" method="POST">
                <div class="modal-body">
                    <p class="text-muted small mb-3">
                        Pilih Tahun Pelajaran dan Semester yang ingin Anda aktifkan. Semua data rombel, jadwal ujian, materi, dan rapor akan disesuaikan dengan periode yang dipilih.
                    </p>

                    <div class="mb-3">
                        <label class="form-label fw-semibold small">Tahun Pelajaran</label>
                        <select name="id_tp" class="form-select form-select-sm" required>
                            <?php foreach ($tpList as $t): ?>
                                <option value="<?= $t['id_tp'] ?>" <?= (int)$t['id_tp'] === (int)($activeTp['id_tp'] ?? 0) ? 'selected' : '' ?>>
                                    <?= escape_html($t['tahun']) ?> <?= (int)$t['active'] === 1 ? '(Default Global)' : '' ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                    </div>

                    <div class="mb-3">
                        <label class="form-label fw-semibold small">Semester</label>
                        <select name="id_smt" class="form-select form-select-sm" required>
                            <?php foreach ($smtList as $s): ?>
                                <option value="<?= $s['id_smt'] ?>" <?= (int)$s['id_smt'] === (int)($activeSmt['id_smt'] ?? 0) ? 'selected' : '' ?>>
                                    Semester <?= escape_html($s['smt']) ?> (<?= escape_html($s['nama_smt']) ?>) <?= (int)$s['active'] === 1 ? '(Default Global)' : '' ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                    </div>

                    <?php if ($isAdmin): ?>
                    <div class="form-check form-switch mt-3 pt-2 border-top">
                        <input class="form-check-input" type="checkbox" name="global" value="1" id="globalSwitchCheck">
                        <label class="form-check-label small fw-semibold text-danger" for="globalSwitchCheck">
                            Terapkan sebagai Default Global untuk semua pengguna
                        </label>
                    </div>
                    <?php endif; ?>
                </div>
                <div class="modal-footer bg-light py-2">
                    <button type="button" class="btn btn-sm btn-secondary" data-bs-dismiss="modal">Batal</button>
                    <button type="submit" class="btn btn-sm btn-primary">
                        <i class="bi bi-check-circle me-1"></i> Simpan & Terapkan
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- MODAL UBAH PASSWORD (DENGAN AUTO-LOGOUT GUARD) -->
<div class="modal fade" id="changePasswordModal" tabindex="-1" aria-labelledby="changePasswordModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content border-0 shadow">
            <div class="modal-header bg-dark text-white">
                <h6 class="modal-title fw-bold" id="changePasswordModalLabel">
                    <i class="bi bi-key-fill text-warning me-1"></i> Ubah Password Akun
                </h6>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <form action="<?= base_url('/user/change-password') ?>" method="POST">
                <div class="modal-body">
                    <div class="alert alert-warning py-2 small d-flex align-items-center mb-3">
                        <i class="bi bi-exclamation-triangle-fill fs-5 me-2 flex-shrink-0"></i>
                        <div>
                            <strong>Perhatian Keamanan:</strong> Setelah Anda mengganti password, sistem akan secara otomatis melakukan <em>Auto-Logout</em> dan meminta login kembali dengan password baru.
                        </div>
                    </div>

                    <div class="mb-3">
                        <label class="form-label small fw-semibold">Password Saat Ini</label>
                        <input type="password" name="current_password" class="form-control form-control-sm" required placeholder="Masukkan password lama Anda">
                    </div>

                    <div class="mb-3">
                        <label class="form-label small fw-semibold">Password Baru (Minimal 6 Karakter)</label>
                        <input type="password" name="new_password" class="form-control form-control-sm" minlength="6" required placeholder="Masukkan password baru">
                    </div>

                    <div class="mb-3">
                        <label class="form-label small fw-semibold">Konfirmasi Password Baru</label>
                        <input type="password" name="confirm_password" class="form-control form-control-sm" minlength="6" required placeholder="Ulangi password baru">
                    </div>
                </div>
                <div class="modal-footer bg-light py-2">
                    <button type="button" class="btn btn-sm btn-secondary" data-bs-dismiss="modal">Batal</button>
                    <button type="submit" class="btn btn-sm btn-warning fw-semibold">
                        <i class="bi bi-shield-lock me-1"></i> Perbarui Password
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

