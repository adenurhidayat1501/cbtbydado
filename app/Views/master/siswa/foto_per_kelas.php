<div class="d-flex flex-wrap justify-content-between align-items-center mb-4">
    <div>
        <h4 class="fw-bold mb-1 text-dark">Update Foto Siswa Per Kelas</h4>
        <p class="text-muted small mb-0">Unggah dan perbarui pas foto resmi siswa secara cepat dalam satu tampilan rombel kelas.</p>
    </div>
    <a href="<?= base_url('/master/siswa') ?>" class="btn btn-outline-secondary btn-sm d-flex align-items-center gap-1">
        <i class="bi bi-arrow-left"></i> Kembali ke Data Siswa
    </a>
</div>

<!-- Class Selector Card -->
<div class="card border-0 shadow-sm rounded-3 mb-4">
    <div class="card-body py-3">
        <form method="GET" action="<?= base_url('/master/siswa/foto-per-kelas') ?>" class="row g-3 align-items-center">
            <div class="col-auto">
                <label class="col-form-label fw-semibold small text-dark"><i class="bi bi-filter me-1"></i> Pilih Rombel Kelas:</label>
            </div>
            <div class="col-auto">
                <select name="kelas_id" class="form-select form-select-sm" onchange="this.form.submit()">
                    <?php if (empty($kelasList)): ?>
                        <option value="0">Belum ada kelas</option>
                    <?php else: ?>
                        <?php foreach ($kelasList as $k): ?>
                            <option value="<?= $k['id_kelas'] ?>" <?= (int)$k['id_kelas'] === (int)$selectedKelasId ? 'selected' : '' ?>>
                                <?= escape_html($k['nama_kelas']) ?> (Tingkat <?= $k['level_id'] ?>) — <?= $k['total_siswa'] ?> Siswa
                            </option>
                        <?php endforeach; ?>
                    <?php endif; ?>
                </select>
            </div>
        </form>
    </div>
</div>

<!-- Students Grid -->
<?php if (empty($students)): ?>
    <div class="card border-0 shadow-sm rounded-3 text-center py-5 text-muted">
        <i class="bi bi-person-x fs-1 opacity-50 mb-2"></i>
        <p class="mb-0">Tidak ada siswa yang terdaftar pada rombel kelas ini.</p>
    </div>
<?php else: ?>
    <div class="row g-3">
        <?php foreach ($students as $s): ?>
            <div class="col-6 col-sm-4 col-md-3 col-xl-2">
                <div class="card border-0 shadow-sm rounded-3 text-center p-3 h-100 position-relative">
                    <div class="position-relative mx-auto mb-2" style="width: 100px; height: 120px;">
                        <img id="imgPreview_<?= $s['id_siswa'] ?>" 
                             src="<?= !empty($s['foto']) ? upload_url($s['foto']) : 'https://placehold.co/100x120/e2e8f0/64748b?text=Foto' ?>" 
                             alt="<?= escape_html($s['nama']) ?>" 
                             class="w-100 h-100 object-fit-cover rounded border shadow-sm">
                        <span id="spinner_<?= $s['id_siswa'] ?>" class="spinner-border spinner-border-sm text-primary position-absolute top-50 start-50 translate-middle d-none"></span>
                    </div>

                    <div class="fw-bold text-dark text-truncate small" title="<?= escape_html($s['nama']) ?>"><?= escape_html($s['nama']) ?></div>
                    <small class="text-muted font-monospace d-block mb-2" style="font-size: 0.75rem;">NIS: <?= escape_html($s['nis']) ?></small>

                    <div class="mt-auto">
                        <label class="btn btn-outline-primary btn-sm w-100 py-1" style="font-size: 0.75rem; cursor: pointer;">
                            <i class="bi bi-camera me-1"></i> Ganti Foto
                            <input type="file" class="d-none" accept="image/*" onchange="uploadFotoSiswa(<?= $s['id_siswa'] ?>, this)">
                        </label>
                    </div>
                </div>
            </div>
        <?php endforeach; ?>
    </div>
<?php endif; ?>

<script>
function uploadFotoSiswa(idSiswa, inputElem) {
    if (!inputElem.files || !inputElem.files[0]) return;

    const file = inputElem.files[0];
    const formData = new FormData();
    formData.append('id_siswa', idSiswa);
    formData.append('foto', file);

    const spinner = document.getElementById('spinner_' + idSiswa);
    const imgPreview = document.getElementById('imgPreview_' + idSiswa);

    if (spinner) spinner.classList.remove('d-none');

    fetch('<?= base_url('/master/siswa/upload-foto') ?>', {
        method: 'POST',
        body: formData,
        headers: { 'X-Requested-With': 'XMLHttpRequest' }
    })
    .then(res => res.json())
    .then(data => {
        if (spinner) spinner.classList.add('d-none');
        if (data.success && data.foto_url) {
            imgPreview.src = data.foto_url;
        } else {
            alert(data.message || 'Gagal mengunggah foto.');
        }
    })
    .catch(() => {
        if (spinner) spinner.classList.add('d-none');
        alert('Terjadi kesalahan koneksi.');
    });
}
</script>

