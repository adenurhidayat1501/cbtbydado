<div class="container-fluid py-3">
    <div class="d-flex justify-content-between align-items-center mb-3">
        <div>
            <h4 class="mb-0 fw-bold">
                <i class="bi bi-<?= $jenis === 1 ? 'book text-primary' : 'pencil-square text-warning' ?> me-2"></i>
                Buat <?= $jenis === 1 ? 'Materi Belajar Baru' : 'Tugas Siswa Baru' ?>
            </h4>
            <small class="text-muted">TP <?= escape_html($tp['tahun'] ?? '-') ?> - Semester <?= escape_html($smt['nama_smt'] ?? '-') ?></small>
        </div>
        <div>
            <a href="<?= base_url('/elearning/materi-tugas?jenis=' . $jenis) ?>" class="btn btn-outline-secondary btn-sm">
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
            <form action="<?= base_url('/elearning/materi-tugas/store') ?>" method="POST" enctype="multipart/form-data">
                <input type="hidden" name="jenis" value="<?= $jenis ?>">

                <div class="row g-3 mb-3">
                    <div class="col-md-8">
                        <label class="form-label fw-semibold">Judul <?= $jenis === 1 ? 'Materi' : 'Tugas' ?> <span class="text-danger">*</span></label>
                        <input type="text" name="judul_materi" class="form-control" placeholder="Contoh: Bab 1 - Struktur Teks Deskripsi dan Kaidah Bahasa" required>
                    </div>
                    <div class="col-md-4">
                        <label class="form-label fw-semibold">Kode <?= $jenis === 1 ? 'Materi' : 'Tugas' ?> <span class="text-muted small">(Opsional / Auto)</span></label>
                        <input type="text" name="kode_materi" class="form-control" placeholder="Otomatis digenerate jika kosong">
                    </div>
                </div>

                <div class="row g-3 mb-3">
                    <div class="col-md-6">
                        <label class="form-label fw-semibold">Mata Pelajaran <span class="text-danger">*</span></label>
                        <select name="id_mapel" class="form-select" required>
                            <option value="">-- Pilih Mata Pelajaran --</option>
                            <?php foreach ($mapelList as $m): ?>
                                <option value="<?= $m['id_mapel'] ?>"><?= escape_html($m['nama_mapel']) ?> (<?= escape_html($m['kode']) ?>)</option>
                            <?php endforeach; ?>
                        </select>
                    </div>

                    <?php if ($role === 'admin'): ?>
                        <div class="col-md-6">
                            <label class="form-label fw-semibold">Guru Pengampu <span class="text-danger">*</span></label>
                            <select name="id_guru" class="form-select" required>
                                <option value="">-- Pilih Guru --</option>
                                <?php foreach ($guruList as $g): ?>
                                    <option value="<?= $g['id_guru'] ?>"><?= escape_html($g['nama_guru']) ?> (NIP: <?= escape_html($g['nip'] ?? '-') ?>)</option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                    <?php else: ?>
                        <input type="hidden" name="id_guru" value="<?= $currentGuru['id_guru'] ?? 0 ?>">
                        <div class="col-md-6">
                            <label class="form-label fw-semibold">Guru Pengampu</label>
                            <input type="text" class="form-control bg-light" value="<?= escape_html($currentGuru['nama_guru'] ?? '-') ?>" readonly>
                        </div>
                    <?php endif; ?>
                </div>

                <!-- Target Kelas Selection -->
                <div class="mb-3">
                    <label class="form-label fw-semibold">Pilih Kelas Penerima <span class="text-danger">*</span></label>
                    <div class="border rounded p-3 bg-light">
                        <div class="d-flex justify-content-between align-items-center mb-2">
                            <span class="small text-muted">Centang satu atau beberapa kelas yang akan menerima <?= $jenis === 1 ? 'materi' : 'tugas' ?> ini:</span>
                            <button type="button" class="btn btn-outline-secondary btn-sm py-0" style="font-size: 11px;" onclick="toggleSelectAllKelas(this)">
                                Pilih Semua
                            </button>
                        </div>
                        <div class="row g-2">
                            <?php foreach ($kelasList as $k): ?>
                                <div class="col-6 col-md-4 col-lg-3">
                                    <div class="form-check">
                                        <input class="form-check-input kelas-checkbox" type="checkbox" name="id_kelas[]" value="<?= $k['id_kelas'] ?>" id="kelas_<?= $k['id_kelas'] ?>">
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
                        Uraian Isi <?= $jenis === 1 ? 'Materi Belajar' : 'Petunjuk Tugas' ?> <span class="text-danger">*</span>
                    </label>
                    <textarea id="isi_materi" name="isi_materi" class="form-control summernote" rows="12"></textarea>
                    <div class="form-text small text-muted">
                        Mendukung formula KaTeX LaTeX, teks Arab (font Arabic Calibri), dan gambar.
                    </div>
                </div>

                <!-- Video Link -->
                <div class="mb-3">
                    <label class="form-label fw-semibold"><i class="bi bi-youtube text-danger me-1"></i> Link Video Pembelajaran (Opsional)</label>
                    <div class="input-group">
                        <span class="input-group-text"><i class="bi bi-link-45deg"></i></span>
                        <input type="url" name="link_video" class="form-control" placeholder="https://www.youtube.com/watch?v=... atau link embed MP4">
                    </div>
                    <div class="form-text small">Sematkan tautan video YouTube untuk memudahkan siswa menonton penjelasan materi.</div>
                </div>

                <!-- File Attachments -->
                <div class="mb-3">
                    <label class="form-label fw-semibold"><i class="bi bi-paperclip text-primary me-1"></i> Lampiran Berkas / Dokumen Pendukung (Opsional)</label>
                    <input type="file" name="files[]" class="form-control" multiple>
                    <div class="form-text small">Dapat mengunggah beberapa berkas sekaligus (Format: PDF, Word, Excel, PowerPoint, MP4, MP3, ZIP, Foto).</div>
                </div>

                <div class="row g-3 mb-4">
                    <div class="col-md-6">
                        <div class="form-check form-switch mt-2">
                            <input class="form-check-input" type="checkbox" name="auto_schedule_today" value="1" id="autoSchedule" checked>
                            <label class="form-check-label small fw-semibold" for="autoSchedule">
                                <i class="bi bi-calendar-check text-success me-1"></i> Langsung jadwalkan tayang untuk hari ini (<?= format_indo_date(date('Y-m-d')) ?>)
                            </label>
                        </div>
                    </div>
                    <div class="col-md-6 text-end">
                        <div class="form-check form-switch d-inline-block">
                            <input class="form-check-input" type="checkbox" name="status" value="1" id="statusMateri" checked>
                            <label class="form-check-label small fw-semibold" for="statusMateri">Status Aktif</label>
                        </div>
                    </div>
                </div>

                <div class="d-flex justify-content-end gap-2 border-top pt-3">
                    <a href="<?= base_url('/elearning/materi-tugas?jenis=' . $jenis) ?>" class="btn btn-light">Batal</a>
                    <button type="submit" class="btn btn-primary px-4">
                        <i class="bi bi-save me-1"></i> Simpan <?= $jenis === 1 ? 'Materi' : 'Tugas' ?>
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
            placeholder: 'Tuliskan penjelasan materi atau instruksi pengerjaan tugas secara rinci di sini...',
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

