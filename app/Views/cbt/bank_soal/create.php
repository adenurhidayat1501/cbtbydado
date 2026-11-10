<div class="container-fluid py-3">
    <div class="d-flex justify-content-between align-items-center mb-3">
        <div>
            <h4 class="mb-0 fw-bold"><i class="bi bi-plus-circle text-primary me-2"></i>Buat Bank Soal Baru</h4>
            <small class="text-muted">Konfigurasi bobot, kuota tampil 5 AKM, dan target rombel</small>
        </div>
        <div>
            <a href="<?= base_url('/cbt/bank-soal') ?>" class="btn btn-outline-secondary btn-sm">
                <i class="bi bi-arrow-left me-1"></i> Kembali ke Bank Soal
            </a>
        </div>
    </div>

    <?php if (has_flash('error')): ?>
        <div class="alert alert-danger alert-dismissible fade show" role="alert">
            <i class="bi bi-exclamation-triangle me-1"></i> <?= escape_html(get_flash('error')) ?>
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
    <?php endif; ?>

    <div class="card shadow-sm border-0">
        <div class="card-body p-4">
            <form action="<?= base_url('/cbt/bank-soal/store') ?>" method="POST">
                <div class="row g-3 mb-3">
                    <div class="col-md-4">
                        <label class="form-label fw-semibold">Kode Bank Soal <span class="text-danger">*</span></label>
                        <input type="text" name="bank_kode" class="form-control text-uppercase font-monospace" placeholder="Contoh: PAI-7-PTS1" required>
                    </div>

                    <div class="col-md-4">
                        <label class="form-label fw-semibold">Mata Pelajaran <span class="text-danger">*</span></label>
                        <select name="bank_mapel_id" class="form-select" required>
                            <option value="">-- Pilih Mata Pelajaran --</option>
                            <?php foreach ($mapelList as $m): ?>
                                <option value="<?= $m['id_mapel'] ?>"><?= escape_html($m['nama_mapel']) ?> (<?= escape_html($m['kode']) ?>)</option>
                            <?php endforeach; ?>
                        </select>
                    </div>

                    <div class="col-md-4">
                        <label class="form-label fw-semibold">Jenis Ujian</label>
                        <select name="bank_jenis_id" class="form-select">
                            <option value="">-- Bebas / Semua Jenis --</option>
                            <?php foreach ($jenisList as $j): ?>
                                <option value="<?= $j['id_jenis'] ?>"><?= escape_html($j['nama_jenis']) ?> (<?= escape_html($j['kode_jenis']) ?>)</option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                </div>

                <div class="row g-3 mb-3">
                    <div class="col-md-4">
                        <label class="form-label fw-semibold">Tingkat Kelas (Level) <span class="text-danger">*</span></label>
                        <select name="bank_level" class="form-select" required>
                            <?php for ($lv = 1; $lv <= 12; $lv++): ?>
                                <option value="<?= $lv ?>" <?= $lv === 7 ? 'selected' : '' ?>>Tingkat Kelas <?= $lv ?></option>
                            <?php endfor; ?>
                        </select>
                    </div>

                    <div class="col-md-4">
                        <label class="form-label fw-semibold">Jumlah Pilihan Opsi PG <span class="text-danger">*</span></label>
                        <select name="opsi" class="form-select" required>
                            <option value="3" <?= $defaultOpsi === 3 ? 'selected' : '' ?>>3 Opsi (A, B, C) — Standar SD/MI</option>
                            <option value="4" <?= $defaultOpsi === 4 ? 'selected' : '' ?>>4 Opsi (A, B, C, D) — Standar SMP/MTs</option>
                            <option value="5" <?= $defaultOpsi === 5 ? 'selected' : '' ?>>5 Opsi (A, B, C, D, E) — Standar SMA/SMK</option>
                        </select>
                    </div>

                    <?php if ($role === 'admin'): ?>
                        <div class="col-md-4">
                            <label class="form-label fw-semibold">Guru Pembuat <span class="text-danger">*</span></label>
                            <select name="bank_guru_id" class="form-select" required>
                                <option value="">-- Pilih Guru --</option>
                                <?php foreach ($guruList as $g): ?>
                                    <option value="<?= $g['id_guru'] ?>"><?= escape_html($g['nama_guru']) ?></option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                    <?php else: ?>
                        <input type="hidden" name="bank_guru_id" value="<?= $currentGuru['id_guru'] ?? 0 ?>">
                        <div class="col-md-4">
                            <label class="form-label fw-semibold">Guru Pembuat</label>
                            <input type="text" class="form-control bg-light" value="<?= escape_html($currentGuru['nama_guru'] ?? '-') ?>" readonly>
                        </div>
                    <?php endif; ?>
                </div>

                <!-- Target Kelas Rombel -->
                <div class="mb-3">
                    <label class="form-label fw-semibold">Pilih Rombel Kelas Terkait</label>
                    <div class="border rounded p-3 bg-light">
                        <div class="row g-2">
                            <?php foreach ($kelasList as $k): ?>
                                <div class="col-6 col-md-3">
                                    <div class="form-check">
                                        <input class="form-check-input" type="checkbox" name="bank_kelas[]" value="<?= $k['id_kelas'] ?>" id="bk_<?= $k['id_kelas'] ?>" checked>
                                        <label class="form-check-label small" for="bk_<?= $k['id_kelas'] ?>">
                                            <?= escape_html($k['nama_kelas']) ?>
                                        </label>
                                    </div>
                                </div>
                            <?php endforeach; ?>
                        </div>
                    </div>
                </div>

                <!-- Pengaturan Bobot & Kuota Tampil 5 AKM -->
                <h5 class="fw-bold text-dark border-bottom pb-2 mt-4 mb-3">
                    <i class="bi bi-pie-chart text-primary me-2"></i>Komposisi Soal & Bobot Nilai 5 Tipe AKM
                </h5>

                <div class="table-responsive mb-4">
                    <table class="table table-bordered align-middle text-center mb-0" style="font-size: 13px;">
                        <thead class="table-light">
                            <tr>
                                <th class="text-start">Tipe Soal Standar AKM</th>
                                <th style="width: 200px;">Jumlah Tampil Ujian</th>
                                <th style="width: 200px;">Bobot Nilai (%)</th>
                            </tr>
                        </thead>
                        <tbody>
                            <tr>
                                <td class="text-start fw-semibold">
                                    <i class="bi bi-ui-radios text-primary me-1"></i> 1. Pilihan Ganda Tunggal (PG)
                                </td>
                                <td>
                                    <input type="number" name="tampil_pg" class="form-control form-control-sm text-center" min="0" value="20" required>
                                </td>
                                <td>
                                    <input type="number" name="bobot_pg" class="form-control form-control-sm text-center" min="0" max="100" value="40" required>
                                </td>
                            </tr>
                            <tr>
                                <td class="text-start fw-semibold">
                                    <i class="bi bi-ui-checks text-info me-1"></i> 2. Pilihan Ganda Kompleks (PGK)
                                </td>
                                <td>
                                    <input type="number" name="tampil_kompleks" class="form-control form-control-sm text-center" min="0" value="5" required>
                                </td>
                                <td>
                                    <input type="number" name="bobot_kompleks" class="form-control form-control-sm text-center" min="0" max="100" value="20" required>
                                </td>
                            </tr>
                            <tr>
                                <td class="text-start fw-semibold">
                                    <i class="bi bi-bezier2 text-warning me-1"></i> 3. Menjodohkan / Matching (Canvas Line)
                                </td>
                                <td>
                                    <input type="number" name="tampil_jodohkan" class="form-control form-control-sm text-center" min="0" value="5" required>
                                </td>
                                <td>
                                    <input type="number" name="bobot_jodohkan" class="form-control form-control-sm text-center" min="0" max="100" value="15" required>
                                </td>
                            </tr>
                            <tr>
                                <td class="text-start fw-semibold">
                                    <i class="bi bi-input-cursor-text text-secondary me-1"></i> 4. Isian Singkat
                                </td>
                                <td>
                                    <input type="number" name="tampil_isian" class="form-control form-control-sm text-center" min="0" value="5" required>
                                </td>
                                <td>
                                    <input type="number" name="bobot_isian" class="form-control form-control-sm text-center" min="0" max="100" value="10" required>
                                </td>
                            </tr>
                            <tr>
                                <td class="text-start fw-semibold">
                                    <i class="bi bi-textarea-t text-dark me-1"></i> 5. Uraian / Essai
                                </td>
                                <td>
                                    <input type="number" name="tampil_esai" class="form-control form-control-sm text-center" min="0" value="5" required>
                                </td>
                                <td>
                                    <input type="number" name="bobot_esai" class="form-control form-control-sm text-center" min="0" max="100" value="15" required>
                                </td>
                            </tr>
                        </tbody>
                    </table>
                </div>

                <div class="d-flex justify-content-between align-items-center border-top pt-3">
                    <div class="form-check form-switch">
                        <input class="form-check-input" type="checkbox" name="status" value="1" id="statusBank" checked>
                        <label class="form-check-label small fw-semibold" for="statusBank">Aktifkan Bank Soal</label>
                    </div>

                    <div class="d-flex gap-2">
                        <a href="<?= base_url('/cbt/bank-soal') ?>" class="btn btn-light">Batal</a>
                        <button type="submit" class="btn btn-primary px-4">
                            <i class="bi bi-save me-1"></i> Simpan Bank Soal
                        </button>
                    </div>
                </div>
            </form>
        </div>
    </div>
</div>
