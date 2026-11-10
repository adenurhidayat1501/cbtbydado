<div class="row">
    <div class="col-md-7">
        <div class="card shadow-sm border-0 mb-4">
            <div class="card-header bg-white py-3 d-flex align-items-center justify-content-between">
                <h5 class="card-title mb-0"><i class="bi bi-person-lines-fill me-2 text-primary"></i>Lembar Buku Induk Siswa</h5>
                <a href="<?= base_url('/rapor/buku-induk') ?>" class="btn btn-sm btn-outline-secondary">
                    <i class="bi bi-arrow-left me-1"></i>Kembali
                </a>
            </div>
            <form action="<?= base_url('/rapor/buku-induk/save/' . $buku['id_siswa']) ?>" method="POST">
                <div class="card-body">
                    <?php if (!empty($_SESSION['flash_success'])): ?>
                        <div class="alert alert-success alert-dismissible fade show" role="alert">
                            <i class="bi bi-check-circle me-2"></i><?= htmlspecialchars($_SESSION['flash_success'], ENT_QUOTES, 'UTF-8') ?>
                            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                        </div>
                        <?php unset($_SESSION['flash_success']); ?>
                    <?php endif; ?>

                    <h6 class="fw-bold text-primary mb-3"><i class="bi bi-person me-1"></i>A. Keterangan Tentang Diri Peserta Didik</h6>
                    <div class="row g-3 mb-3">
                        <div class="col-md-6">
                            <label class="form-label fw-bold">Nama Lengkap</label>
                            <input type="text" class="form-control" value="<?= htmlspecialchars($buku['nama'], ENT_QUOTES, 'UTF-8') ?>" disabled>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label fw-bold">Nama Panggilan</label>
                            <input type="text" name="nama_panggilan" class="form-control" value="<?= htmlspecialchars($buku['nama_panggilan'] ?? '', ENT_QUOTES, 'UTF-8') ?>">
                        </div>
                        <div class="col-md-6">
                            <label class="form-label fw-bold">Nomor Induk Siswa (NIS)</label>
                            <input type="text" class="form-control" value="<?= htmlspecialchars($buku['nis'] ?? '-', ENT_QUOTES, 'UTF-8') ?>" disabled>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label fw-bold">NISN</label>
                            <input type="text" class="form-control" value="<?= htmlspecialchars($buku['nisn'] ?? '-', ENT_QUOTES, 'UTF-8') ?>" disabled>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label fw-bold">Rombel Awal Masuk</label>
                            <input type="text" name="rombel_awal" class="form-control" value="<?= htmlspecialchars($buku['rombel_awal'] ?? '', ENT_QUOTES, 'UTF-8') ?>" placeholder="Contoh: VII-A atau X-MIPA-1">
                        </div>
                        <div class="col-md-6">
                            <label class="form-label fw-bold">Bahasa Sehari-hari</label>
                            <input type="text" name="bahasa" class="form-control" value="<?= htmlspecialchars($buku['bahasa'] ?? 'Indonesia', ENT_QUOTES, 'UTF-8') ?>">
                        </div>
                    </div>

                    <h6 class="fw-bold text-primary mb-3 mt-4"><i class="bi bi-people me-1"></i>B. Keadaan Keluarga & Saudara</h6>
                    <div class="row g-3 mb-3">
                        <div class="col-md-4">
                            <label class="form-label fw-bold">Jml Sdr Kandung</label>
                            <input type="number" name="jml_saudara_kandung" class="form-control" value="<?= (int)($buku['jml_saudara_kandung'] ?? 0) ?>" min="0">
                        </div>
                        <div class="col-md-4">
                            <label class="form-label fw-bold">Jml Sdr Tiri</label>
                            <input type="number" name="jml_saudara_tiri" class="form-control" value="<?= (int)($buku['jml_saudara_tiri'] ?? 0) ?>" min="0">
                        </div>
                        <div class="col-md-4">
                            <label class="form-label fw-bold">Jml Sdr Angkat</label>
                            <input type="number" name="jml_saudara_angkat" class="form-control" value="<?= (int)($buku['jml_saudara_angkat'] ?? 0) ?>" min="0">
                        </div>
                        <div class="col-md-6">
                            <label class="form-label fw-bold">Status Keberadaan Orang Tua</label>
                            <select name="status_yatim" class="form-select">
                                <option value="Lengkap" <?= ($buku['status_yatim'] ?? 'Lengkap') === 'Lengkap' ? 'selected' : '' ?>>Lengkap (Ayah & Ibu)</option>
                                <option value="Yatim" <?= ($buku['status_yatim'] ?? '') === 'Yatim' ? 'selected' : '' ?>>Yatim (Ayah Meninggal)</option>
                                <option value="Piatu" <?= ($buku['status_yatim'] ?? '') === 'Piatu' ? 'selected' : '' ?>>Piatu (Ibu Meninggal)</option>
                                <option value="Yatim Piatu" <?= ($buku['status_yatim'] ?? '') === 'Yatim Piatu' ? 'selected' : '' ?>>Yatim Piatu</option>
                            </select>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label fw-bold">Golongan Darah</label>
                            <input type="text" name="gol_darah" class="form-control" value="<?= htmlspecialchars($buku['gol_darah'] ?? '', ENT_QUOTES, 'UTF-8') ?>" placeholder="A / B / AB / O">
                        </div>
                    </div>

                    <h6 class="fw-bold text-primary mb-3 mt-4"><i class="bi bi-mortarboard me-1"></i>C. Riwayat Pendidikan & Kelulusan</h6>
                    <div class="row g-3 mb-3">
                        <div class="col-md-6">
                            <label class="form-label fw-bold">Tamat Belajar Dari Sekolah</label>
                            <input type="text" name="tamat_dari" class="form-control" value="<?= htmlspecialchars($buku['tamat_dari'] ?? '', ENT_QUOTES, 'UTF-8') ?>" placeholder="Nama Sekolah Asal">
                        </div>
                        <div class="col-md-6">
                            <label class="form-label fw-bold">Nomor Ijazah Sebelumnya</label>
                            <input type="text" name="no_ijazah_sebelumnya" class="form-control" value="<?= htmlspecialchars($buku['no_ijazah_sebelumnya'] ?? '', ENT_QUOTES, 'UTF-8') ?>">
                        </div>
                        <div class="col-md-6">
                            <label class="form-label fw-bold">Tahun Lulus (di sekolah ini)</label>
                            <input type="text" name="tahun_lulus" class="form-control" value="<?= htmlspecialchars($buku['tahun_lulus'] ?? '', ENT_QUOTES, 'UTF-8') ?>" placeholder="Contoh: 2024">
                        </div>
                        <div class="col-md-6">
                            <label class="form-label fw-bold">Nomor Seri Ijazah (di sekolah ini)</label>
                            <input type="text" name="no_ijazah" class="form-control" value="<?= htmlspecialchars($buku['no_ijazah'] ?? '', ENT_QUOTES, 'UTF-8') ?>">
                        </div>
                    </div>
                </div>
                <div class="card-footer bg-light text-end">
                    <button type="submit" class="btn btn-primary"><i class="bi bi-save me-1"></i>Simpan Biodata Buku Induk</button>
                </div>
            </form>
        </div>
    </div>

    <div class="col-md-5">
        <!-- Panel Aksi Arsip Nilai -->
        <div class="card shadow-sm border-0 mb-4">
            <div class="card-header bg-white py-3 d-flex align-items-center justify-content-between">
                <h6 class="card-title fw-bold mb-0"><i class="bi bi-archive me-1 text-success"></i>Arsip Nilai Lintas Semester</h6>
                <div class="d-flex gap-2">
                    <a href="<?= base_url('/rapor/buku-induk/cetak/' . $buku['id_siswa']) ?>" target="_blank" class="btn btn-sm btn-outline-dark">
                        <i class="bi bi-printer me-1"></i>Cetak
                    </a>
                    <a href="<?= base_url('/rapor/buku-induk/sync-rapor/' . $buku['id_siswa']) ?>" class="btn btn-sm btn-success" onclick="return confirm('Arsipkan snapshot nilai rapor semester aktif siswa ini ke buku induk?')">
                        <i class="bi bi-cloud-arrow-down me-1"></i>Arsipkan Sekarang
                    </a>
                </div>
            </div>
            <div class="card-body">
                <p class="small text-muted mb-3">
                    Buku Induk secara permanen mencatat dan menyimpan rekapitulasi nilai rapor peserta didik untuk seluruh semester (Semester 1 s.d. 6).
                </p>

                <?php if (!empty($arsip)): ?>
                    <div class="accordion" id="accArsip">
                        <?php $idx = 1; foreach ($arsip as $key => $sem): ?>
                            <div class="accordion-item mb-2 border rounded">
                                <h2 class="accordion-header" id="heading<?= $idx ?>">
                                    <button class="accordion-button <?= $idx > 1 ? 'collapsed' : '' ?> py-2" type="button" data-bs-toggle="collapse" data-bs-target="#collapse<?= $idx ?>">
                                        <div class="d-flex justify-content-between w-100 me-3">
                                            <strong>TP <?= htmlspecialchars($sem['tahun_pelajaran'] ?? '-', ENT_QUOTES, 'UTF-8') ?> — <?= htmlspecialchars($sem['semester'] ?? '-', ENT_QUOTES, 'UTF-8') ?></strong>
                                            <span class="badge bg-primary">Rata-rata: <?= number_format((float)($sem['rata_rata'] ?? 0), 2) ?></span>
                                        </div>
                                    </button>
                                </h2>
                                <div id="collapse<?= $idx ?>" class="accordion-collapse collapse <?= $idx === 1 ? 'show' : '' ?>" data-bs-parent="#accArsip">
                                    <div class="accordion-body p-2">
                                        <div class="small text-muted mb-2">Kelas: <strong><?= htmlspecialchars($sem['kelas'] ?? '-', ENT_QUOTES, 'UTF-8') ?></strong> | Diarsipkan: <?= htmlspecialchars($sem['archived_at'] ?? '-', ENT_QUOTES, 'UTF-8') ?></div>
                                        <div class="table-responsive">
                                            <table class="table table-sm table-bordered mb-0" style="font-size: 8.5pt;">
                                                <thead class="table-light">
                                                    <tr>
                                                        <th>Mata Pelajaran</th>
                                                        <th style="width: 50px;" class="text-center">Nilai</th>
                                                        <th style="width: 40px;" class="text-center">Pred</th>
                                                    </tr>
                                                </thead>
                                                <tbody>
                                                    <?php foreach ($sem['grades'] ?? [] as $g): ?>
                                                        <tr>
                                                            <td><?= htmlspecialchars($g['nama_mapel'], ENT_QUOTES, 'UTF-8') ?></td>
                                                            <td class="text-center"><?= number_format((float)$g['nilai'], 1) ?></td>
                                                            <td class="text-center fw-bold"><?= htmlspecialchars($g['predikat'] ?? '-', ENT_QUOTES, 'UTF-8') ?></td>
                                                        </tr>
                                                    <?php endforeach; ?>
                                                </tbody>
                                            </table>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        <?php $idx++; endforeach; ?>
                    </div>
                <?php else: ?>
                    <div class="alert alert-light border text-center py-4 text-muted small">
                        Belum ada riwayat semester yang diarsipkan ke buku induk.<br>
                        Klik tombol <strong>"Arsipkan Sekarang"</strong> di atas.
                    </div>
                <?php endif; ?>
            </div>
        </div>
    </div>
</div>

