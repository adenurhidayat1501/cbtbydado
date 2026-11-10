<div class="d-flex flex-wrap justify-content-between align-items-center mb-4">
    <div>
        <h4 class="fw-bold mb-1 text-dark">Profil Lembaga & Sekolah</h4>
        <p class="text-muted small mb-0">Atur identitas lembaga, jenjang pendidikan, kop surat, dan tanda tangan digital Kepala Sekolah.</p>
    </div>
</div>

<form action="<?= base_url('/settings/profile/update') ?>" method="POST" enctype="multipart/form-data">
    <div class="row g-4">
        <!-- Kolom Kiri: Identitas Utama & Lokasi -->
        <div class="col-lg-8">
            <div class="card border-0 shadow-sm rounded-3 mb-4">
                <div class="card-header bg-white border-bottom py-3">
                    <h6 class="fw-bold mb-0 text-dark"><i class="bi bi-building text-primary me-2"></i> Identitas Pokok Lembaga</h6>
                </div>
                <div class="card-body">
                    <div class="row g-3">
                        <div class="col-md-8">
                            <label class="form-label small fw-semibold">Nama Resmi Sekolah / Madrasah</label>
                            <input type="text" name="nama_sekolah" class="form-control" value="<?= escape_html($school['nama_sekolah'] ?? '') ?>" required>
                        </div>
                        <div class="col-md-4">
                            <label class="form-label small fw-semibold">NPSN</label>
                            <input type="text" name="npsn" class="form-control font-monospace" value="<?= escape_html($school['npsn'] ?? '') ?>">
                        </div>
                        <div class="col-md-6">
                            <label class="form-label small fw-semibold">Jenjang Sekolah (Multi-Jenjang Adaptif)</label>
                            <select name="jenjang" class="form-select">
                                <option value="1" <?= (int)($school['jenjang'] ?? 2) === 1 ? 'selected' : '' ?>>SD / MI (Tingkat 1–6, Opsi A–C/D, Tanpa Jurusan)</option>
                                <option value="2" <?= (int)($school['jenjang'] ?? 2) === 2 ? 'selected' : '' ?>>SMP / MTs (Tingkat 7–9, Opsi A–D, Tanpa Jurusan)</option>
                                <option value="3" <?= (int)($school['jenjang'] ?? 2) === 3 ? 'selected' : '' ?>>SMA / SMK / MA (Tingkat 10–12, Opsi A–E, dengan Jurusan)</option>
                            </select>
                            <div class="form-text small">Mengubah jenjang akan menyesuaikan opsi butir soal AKM dan ketersediaan menu Jurusan.</div>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label small fw-semibold">Satuan Pendidikan</label>
                            <input type="text" name="satuan_pendidikan" class="form-control" value="<?= escape_html($school['satuan_pendidikan'] ?? 'SMP') ?>" placeholder="Contoh: SMP, MTs, SMA, SMK">
                        </div>
                        <div class="col-12">
                            <label class="form-label small fw-semibold">Alamat Jalan / Gedung</label>
                            <textarea name="alamat" class="form-control" rows="2"><?= escape_html($school['alamat'] ?? '') ?></textarea>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label small fw-semibold">Desa / Kelurahan</label>
                            <input type="text" name="desa" class="form-control" value="<?= escape_html($school['desa'] ?? '') ?>">
                        </div>
                        <div class="col-md-6">
                            <label class="form-label small fw-semibold">Kecamatan</label>
                            <input type="text" name="kecamatan" class="form-control" value="<?= escape_html($school['kecamatan'] ?? '') ?>">
                        </div>
                        <div class="col-md-4">
                            <label class="form-label small fw-semibold">Kota / Kabupaten</label>
                            <input type="text" name="kota" class="form-control" value="<?= escape_html($school['kota'] ?? '') ?>">
                        </div>
                        <div class="col-md-4">
                            <label class="form-label small fw-semibold">Provinsi</label>
                            <input type="text" name="provinsi" class="form-control" value="<?= escape_html($school['provinsi'] ?? '') ?>">
                        </div>
                        <div class="col-md-4">
                            <label class="form-label small fw-semibold">Kode Pos</label>
                            <input type="text" name="kode_pos" class="form-control" value="<?= escape_html($school['kode_pos'] ?? '') ?>">
                        </div>
                        <div class="col-md-4">
                            <label class="form-label small fw-semibold">No Telepon</label>
                            <input type="text" name="telp" class="form-control" value="<?= escape_html($school['telp'] ?? '') ?>">
                        </div>
                        <div class="col-md-4">
                            <label class="form-label small fw-semibold">Email Resmi</label>
                            <input type="email" name="email" class="form-control" value="<?= escape_html($school['email'] ?? '') ?>">
                        </div>
                        <div class="col-md-4">
                            <label class="form-label small fw-semibold">Website</label>
                            <input type="text" name="web" class="form-control" value="<?= escape_html($school['web'] ?? '') ?>">
                        </div>
                    </div>
                </div>
            </div>

            <div class="card border-0 shadow-sm rounded-3">
                <div class="card-header bg-white border-bottom py-3">
                    <h6 class="fw-bold mb-0 text-dark"><i class="bi bi-person-badge text-primary me-2"></i> Pimpinan Lembaga (Kepala Sekolah)</h6>
                </div>
                <div class="card-body">
                    <div class="row g-3">
                        <div class="col-md-7">
                            <label class="form-label small fw-semibold">Nama Kepala Sekolah & Gelar</label>
                            <input type="text" name="kepsek" class="form-control" value="<?= escape_html($school['kepsek'] ?? '') ?>">
                        </div>
                        <div class="col-md-5">
                            <label class="form-label small fw-semibold">NIP Kepala Sekolah</label>
                            <input type="text" name="nip_kepsek" class="form-control font-monospace" value="<?= escape_html($school['nip_kepsek'] ?? '') ?>">
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Kolom Kanan: Upload Logo & Tanda Tangan -->
        <div class="col-lg-4">
            <div class="card border-0 shadow-sm rounded-3 mb-4">
                <div class="card-header bg-white border-bottom py-3">
                    <h6 class="fw-bold mb-0 text-dark"><i class="bi bi-images text-primary me-2"></i> Logo & Atribut Cetak</h6>
                </div>
                <div class="card-body">
                    <!-- Logo Kiri -->
                    <div class="mb-4 text-center">
                        <label class="form-label small fw-semibold d-block text-start">Logo Kiri (Pemda / Yayasan)</label>
                        <div class="border rounded p-2 bg-light mb-2 d-inline-block" style="width: 100px; height: 100px;">
                            <?php if (!empty($school['logo_kiri'])): ?>
                                <img src="<?= upload_url($school['logo_kiri']) ?>" alt="Logo Kiri" class="w-100 h-100 object-fit-contain">
                            <?php else: ?>
                                <i class="bi bi-image fs-1 text-muted"></i>
                            <?php endif; ?>
                        </div>
                        <input type="file" name="logo_kiri" class="form-control form-control-sm" accept="image/*">
                    </div>

                    <!-- Logo Kanan -->
                    <div class="mb-4 text-center">
                        <label class="form-label small fw-semibold d-block text-start">Logo Kanan (Sekolah)</label>
                        <div class="border rounded p-2 bg-light mb-2 d-inline-block" style="width: 100px; height: 100px;">
                            <?php if (!empty($school['logo_kanan'])): ?>
                                <img src="<?= upload_url($school['logo_kanan']) ?>" alt="Logo Kanan" class="w-100 h-100 object-fit-contain">
                            <?php else: ?>
                                <i class="bi bi-image fs-1 text-muted"></i>
                            <?php endif; ?>
                        </div>
                        <input type="file" name="logo_kanan" class="form-control form-control-sm" accept="image/*">
                    </div>

                    <!-- Tanda Tangan Kepsek -->
                    <div class="text-center">
                        <label class="form-label small fw-semibold d-block text-start">Tanda Tangan Kepsek (Digital)</label>
                        <div class="border rounded p-2 bg-light mb-2 d-inline-block" style="width: 150px; height: 80px;">
                            <?php if (!empty($school['tanda_tangan'])): ?>
                                <img src="<?= upload_url($school['tanda_tangan']) ?>" alt="TTD Kepsek" class="w-100 h-100 object-fit-contain">
                            <?php else: ?>
                                <i class="bi bi-pen fs-2 text-muted"></i>
                            <?php endif; ?>
                        </div>
                        <input type="file" name="tanda_tangan" class="form-control form-control-sm" accept="image/*">
                        <div class="form-text small">Format PNG transparan disarankan.</div>
                    </div>
                </div>
            </div>

            <button type="submit" class="btn btn-primary w-100 py-2 fw-semibold shadow-sm">
                <i class="bi bi-check-circle me-1"></i> Simpan Profil Sekolah
            </button>
        </div>
    </div>
</form>

