<div class="container-fluid py-3">
    <div class="d-flex justify-content-between align-items-center mb-3">
        <div>
            <h4 class="mb-0 fw-bold">
                <i class="bi bi-pencil-square text-primary me-2"></i>
                Edit <?= (int)$materi['jenis'] === 1 ? 'Materi Belajar' : 'Tugas Siswa' ?>
            </h4>
            <small class="text-muted">Kode: <?= escape_html($materi['kode_materi']) ?> | Guru: <?= escape_html($materi['nama_guru']) ?></small>
        </div>
        <div>
            <a href="<?= base_url('/elearning/materi-tugas?jenis=' . $materi['jenis']) ?>" class="btn btn-outline-secondary btn-sm">
                <i class="bi bi-arrow-left me-1"></i> Kembali ke Daftar
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
            <form action="<?= base_url('/elearning/materi-tugas/update/' . $materi['id_materi']) ?>" method="POST" enctype="multipart/form-data">
                <div class="row g-3 mb-3">
                    <div class="col-md-8">
                        <label class="form-label fw-semibold">Judul <?= (int)$materi['jenis'] === 1 ? 'Materi' : 'Tugas' ?> <span class="text-danger">*</span></label>
                        <input type="text" name="judul_materi" class="form-control" value="<?= escape_html($materi['judul_materi']) ?>" required>
                    </div>
                    <div class="col-md-4">
                        <label class="form-label fw-semibold">Kode <?= (int)$materi['jenis'] === 1 ? 'Materi' : 'Tugas' ?></label>
                        <input type="text" class="form-control bg-light font-monospace" value="<?= escape_html($materi['kode_materi']) ?>" readonly>
                    </div>
                </div>

                <div class="row g-3 mb-3">
                    <div class="col-md-6">
                        <label class="form-label fw-semibold">Mata Pelajaran <span class="text-danger">*</span></label>
                        <select name="id_mapel" class="form-select" required>
                            <?php foreach ($mapelList as $m): ?>
                                <option value="<?= $m['id_mapel'] ?>" <?= (int)$m['id_mapel'] === (int)$materi['id_mapel'] ? 'selected' : '' ?>>
                                    <?= escape_html($m['nama_mapel']) ?> (<?= escape_html($m['kode']) ?>)
                                </option>
                            <?php endforeach; ?>
                        </select>
                    </div>

                    <div class="col-md-6">
                        <label class="form-label fw-semibold">Guru Pengampu</label>
                        <input type="text" class="form-control bg-light" value="<?= escape_html($materi['nama_guru']) ?>" readonly>
                    </div>
                </div>

                <!-- Target Kelas Selection -->
                <div class="mb-3">
                    <label class="form-label fw-semibold">Pilih Kelas Penerima <span class="text-danger">*</span></label>
                    <div class="border rounded p-3 bg-light">
                        <div class="d-flex justify-content-between align-items-center mb-2">
                            <span class="small text-muted">Centang kelas penerima:</span>
                            <button type="button" class="btn btn-outline-secondary btn-sm py-0" style="font-size: 11px;" onclick="toggleSelectAllKelas(this)">
                                Pilih Semua
                            </button>
                        </div>
                        <div class="row g-2">
                            <?php foreach ($kelasList as $k): ?>
                                <?php $isSelected = in_array((int)$k['id_kelas'], $materi['kelas_ids'] ?? []); ?>
                                <div class="col-6 col-md-4 col-lg-3">
                                    <div class="form-check">
                                        <input class="form-check-input kelas-checkbox" type="checkbox" name="id_kelas[]" value="<?= $k['id_kelas'] ?>" id="kelas_<?= $k['id_kelas'] ?>" <?= $isSelected ? 'checked' : '' ?>>
                                        <label class="form-check-label small" for="kelas_<?= $k['id_kelas'] ?>">
                                            <?= escape_html($k['nama_kelas']) ?>
                                        </label>
                                    </div>
                                </div>
                            <?php endforeach; ?>
                        </div>
                    </div>
                </div>

                <!-- Rich Text Content with Summernote -->
                <div class="mb-3">
                    <label class="form-label fw-semibold">
                        Uraian Isi <?= (int)$materi['jenis'] === 1 ? 'Materi Belajar' : 'Petunjuk Tugas' ?> <span class="text-danger">*</span>
                    </label>
                    <textarea id="isi_materi" name="isi_materi" class="form-control summernote" rows="12"><?= htmlspecialchars($materi['isi_materi'], ENT_QUOTES, 'UTF-8') ?></textarea>
                </div>

                <!-- Video Link -->
                <div class="mb-3">
                    <label class="form-label fw-semibold"><i class="bi bi-youtube text-danger me-1"></i> Link Video Pembelajaran (Opsional)</label>
                    <div class="input-group">
                        <span class="input-group-text"><i class="bi bi-link-45deg"></i></span>
                        <input type="url" name="link_video" class="form-control" value="<?= escape_html($materi['link_video'] ?? '') ?>" placeholder="https://www.youtube.com/watch?v=...">
                    </div>
                </div>

                <!-- Existing & New File Attachments (Preservation) -->
                <div class="mb-3">
                    <label class="form-label fw-semibold"><i class="bi bi-paperclip text-primary me-1"></i> Lampiran Berkas</label>

                    <?php if (!empty($materi['file_list'])): ?>
                        <div class="border rounded p-3 mb-2 bg-light">
                            <div class="small fw-semibold text-secondary mb-2">Berkas Tersimpan (Hilangkan centang jika ingin menghapus berkas):</div>
                            <?php foreach ($materi['file_list'] as $f): ?>
                                <div class="form-check mb-1">
                                    <input class="form-check-input" type="checkbox" name="keep_files[]" value="<?= escape_html($f['filename']) ?>" id="file_<?= md5($f['filename']) ?>" checked>
                                    <label class="form-check-label small" for="file_<?= md5($f['filename']) ?>">
                                        <i class="bi bi-file-earmark me-1 text-primary"></i>
                                        <a href="<?= base_url(escape_html($f['path'])) ?>" target="_blank" class="text-decoration-none">
                                            <?= escape_html($f['orig_name'] ?? $f['filename']) ?>
                                        </a>
                                        <span class="text-muted ms-1" style="font-size: 11px;">(<?= round(($f['size'] ?? 0) / 1024, 1) ?> KB)</span>
                                    </label>
                                </div>
                            <?php endforeach; ?>
                        </div>
                    <?php endif; ?>

                    <div class="small text-muted mb-1">Unggah berkas tambahan:</div>
                    <input type="file" name="files[]" class="form-control" multiple>
                </div>

                <div class="mb-4 text-end">
                    <div class="form-check form-switch d-inline-block">
                        <input class="form-check-input" type="checkbox" name="status" value="1" id="statusMateri" <?= (int)$materi['status'] === 1 ? 'checked' : '' ?>>
                        <label class="form-check-label small fw-semibold" for="statusMateri">Status Aktif</label>
                    </div>
                </div>

                <div class="d-flex justify-content-end gap-2 border-top pt-3">
                    <a href="<?= base_url('/elearning/materi-tugas?jenis=' . $materi['jenis']) ?>" class="btn btn-light">Batal</a>
                    <button type="submit" class="btn btn-primary px-4">
                        <i class="bi bi-save me-1"></i> Simpan Perubahan
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

<script>
function toggleSelectAllKelas(btn) {
    const checkboxes = document.querySelectorAll('.kelas-checkbox');
    const allChecked = Array.from(checkboxes).every(c => c.checked);
    checkboxes.forEach(c => c.checked = !allChecked);
    btn.innerText = allChecked ? 'Pilih Semua' : 'Batal Pilih';
}

document.addEventListener('DOMContentLoaded', function() {
    if (typeof jQuery !== 'undefined' && jQuery.fn.summernote) {
        $('#isi_materi').summernote({
            placeholder: 'Tuliskan penjelasan materi atau instruksi tugas di sini...',
            tabsize: 2,
            height: 300,
            toolbar: [
                ['style', ['style', 'bold', 'italic', 'underline', 'clear']],
                ['font', ['strikethrough', 'superscript', 'subscript']],
                ['fontsize', ['fontsize']],
                ['color', ['color']],
                ['para', ['ul', 'ol', 'paragraph']],
                ['insert', ['link', 'picture', 'video', 'table', 'hr']],
                ['view', ['fullscreen', 'codeview', 'help']]
            ]
        });
    }
});
</script>

