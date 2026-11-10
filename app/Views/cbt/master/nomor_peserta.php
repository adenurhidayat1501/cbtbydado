<div class="container-fluid py-3">
    <div class="d-flex justify-content-between align-items-center mb-3">
        <div>
            <h4 class="mb-0 fw-bold"><i class="bi bi-card-heading text-primary me-2"></i>Nomor Peserta Ujian</h4>
            <small class="text-muted">Generate otomatis dan kelola nomor peserta ujian CBT siswa</small>
        </div>
        <div class="d-flex gap-2">
            <a href="<?= base_url('/cbt/master') ?>" class="btn btn-outline-secondary btn-sm">
                <i class="bi bi-arrow-left me-1"></i> Kembali ke Master CBT
            </a>
            <select class="form-select form-select-sm" style="min-width: 200px;" onchange="window.location.href='<?= base_url('/cbt/master/nomor-peserta?kelas_id=') ?>' + this.value;">
                <option value="">-- Semua Kelas Rombel --</option>
                <?php foreach ($kelasList as $k): ?>
                    <option value="<?= $k['id_kelas'] ?>" <?= (int)$k['id_kelas'] === (int)$selectedKelasId ? 'selected' : '' ?>>
                        <?= escape_html($k['nama_kelas']) ?>
                    </option>
                <?php endforeach; ?>
            </select>
        </div>
    </div>

    <?php if (has_flash('success')): ?>
        <div class="alert alert-success alert-dismissible fade show" role="alert">
            <i class="bi bi-check-circle me-1"></i> <?= escape_html(get_flash('success')) ?>
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
    <?php endif; ?>
    <?php if (has_flash('error')): ?>
        <div class="alert alert-danger alert-dismissible fade show" role="alert">
            <i class="bi bi-exclamation-triangle me-1"></i> <?= escape_html(get_flash('error')) ?>
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
    <?php endif; ?>

    <!-- Generator Card -->
    <div class="card shadow-sm border-0 mb-3 bg-light">
        <div class="card-body py-3">
            <form action="<?= base_url('/cbt/master/nomor-peserta/generate') ?>" method="POST" class="row g-2 align-items-center">
                <input type="hidden" name="id_kelas" value="<?= $selectedKelasId ?>">

                <div class="col-auto">
                    <span class="fw-semibold small text-primary"><i class="bi bi-magic me-1"></i>Generator Otomatis:</span>
                </div>

                <div class="col-md-4">
                    <div class="input-group input-group-sm">
                        <span class="input-group-text">Prefix</span>
                        <input type="text" name="prefix" class="form-control" placeholder="Kosongkan untuk format default (YY-JJ-SSS-)" value="">
                    </div>
                </div>

                <div class="col-auto">
                    <button type="submit" class="btn btn-warning text-dark btn-sm fw-semibold" onclick="return confirm('Generate nomor peserta ujian untuk siswa pada pilihan kelas ini?');">
                        <i class="bi bi-arrow-repeat me-1"></i> Generate Nomor Peserta
                    </button>
                </div>
                <div class="col-auto text-muted small ms-md-auto">
                    Format Standar: <span class="font-monospace fw-semibold">[Tahun]-[Jenjang]-[KodeSekolah]-[NomorUrut]</span>
                </div>
            </form>
        </div>
    </div>

    <!-- Table Card -->
    <div class="card shadow-sm border-0">
        <div class="card-header bg-white py-2 d-flex justify-content-between align-items-center">
            <span class="fw-bold"><i class="bi bi-list-check text-primary me-1"></i> Daftar Nomor Peserta Ujian</span>
            <span class="badge bg-primary"><?= count($students) ?> Siswa</span>
        </div>
        <div class="card-body p-0">
            <div class="table-responsive">
                <table class="table table-hover table-striped align-middle mb-0 datatable" style="font-size: 13px;">
                    <thead class="table-light">
                        <tr>
                            <th style="width: 40px;" class="text-center">No</th>
                            <th style="width: 110px;">NIS</th>
                            <th style="width: 120px;">Kelas</th>
                            <th>Nama Siswa</th>
                            <th style="width: 250px;">Nomor Peserta Ujian</th>
                            <th style="width: 80px;" class="text-center">Aksi</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if (empty($students)): ?>
                            <tr>
                                <td colspan="6" class="text-center py-4 text-muted">
                                    Tidak ada data siswa.
                                </td>
                            </tr>
                        <?php else: ?>
                            <?php $no = 1; foreach ($students as $stu): ?>
                                <?php $formId = 'form_nomor_' . $stu['id_siswa']; ?>
                                <tr>
                                    <td class="text-center"><?= $no++ ?></td>
                                    <td class="font-monospace"><?= escape_html($stu['nis']) ?></td>
                                    <td><span class="badge bg-light text-dark border"><?= escape_html($stu['nama_kelas']) ?></span></td>
                                    <td><div class="fw-semibold text-dark"><?= escape_html($stu['nama']) ?></div></td>
                                    <td>
                                        <input type="text" form="<?= $formId ?>" name="nomor_peserta" class="form-control form-control-sm font-monospace fw-bold text-primary"
                                               value="<?= escape_html($stu['nomor_peserta'] ?? '') ?>" placeholder="Belum ada nomor peserta" required>
                                    </td>
                                    <td class="text-center">
                                        <form id="<?= $formId ?>" action="<?= base_url('/cbt/master/nomor-peserta/save') ?>" method="POST" onsubmit="saveNomorAjax(event, this)">
                                            <input type="hidden" name="id_siswa" value="<?= $stu['id_siswa'] ?>">
                                            <input type="hidden" name="id_kelas" value="<?= $selectedKelasId ?>">
                                            <button type="submit" class="btn btn-outline-primary btn-sm py-1 px-2" title="Simpan">
                                                <i class="bi bi-save"></i>
                                            </button>
                                        </form>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>

<script>
function saveNomorAjax(e, form) {
    e.preventDefault();
    const btn = form.querySelector('button[type="submit"]');
    const origHtml = btn.innerHTML;
    btn.innerHTML = '<span class="spinner-border spinner-border-sm"></span>';
    btn.disabled = true;

    const formData = new FormData(form);

    fetch('<?= base_url('/cbt/master/nomor-peserta/save') ?>', {
        method: 'POST',
        headers: {
            'X-Requested-With': 'XMLHttpRequest'
        },
        body: formData
    })
    .then(r => r.json())
    .then(res => {
        btn.innerHTML = '<i class="bi bi-check-lg text-success"></i>';
        btn.disabled = false;
        setTimeout(() => {
            btn.innerHTML = origHtml;
        }, 1500);
    })
    .catch(err => {
        btn.innerHTML = origHtml;
        btn.disabled = false;
        alert('Gagal menyimpan nomor peserta.');
    });
}
</script>
