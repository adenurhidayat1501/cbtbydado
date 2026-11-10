<div class="container-fluid py-3">
    <div class="d-flex justify-content-between align-items-center mb-3">
        <div>
            <h4 class="mb-0 fw-bold"><i class="bi bi-people text-primary me-2"></i>Atur Ruang & Sesi Siswa</h4>
            <small class="text-muted">Pemetaan siswa ke Ruang Laboratorium dan Sesi Ujian per kelas</small>
        </div>
        <div class="d-flex gap-2">
            <a href="<?= base_url('/cbt/master') ?>" class="btn btn-outline-secondary btn-sm">
                <i class="bi bi-arrow-left me-1"></i> Kembali ke Master CBT
            </a>
            <select class="form-select form-select-sm" style="min-width: 200px;" onchange="window.location.href='<?= base_url('/cbt/master/ruang-sesi-siswa?kelas_id=') ?>' + this.value;">
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

    <!-- Bulk Assignment Bar -->
    <div class="card shadow-sm border-0 mb-3 bg-light">
        <div class="card-body py-2">
            <form action="<?= base_url('/cbt/master/ruang-sesi-siswa/save') ?>" method="POST" id="formBulkAssign" class="row g-2 align-items-center">
                <input type="hidden" name="id_kelas" value="<?= $selectedKelasId ?>">

                <div class="col-auto">
                    <span class="small fw-semibold text-secondary"><i class="bi bi-lightning-charge-fill text-warning me-1"></i>Atur Massal:</span>
                </div>

                <div class="col-md-3">
                    <select name="ruang_id" class="form-select form-select-sm" required>
                        <option value="">-- Pilih Ruang --</option>
                        <?php foreach ($ruangList as $r): ?>
                            <option value="<?= $r['id_ruang'] ?>"><?= escape_html($r['nama_ruang']) ?> (<?= escape_html($r['kode_ruang']) ?>)</option>
                        <?php endforeach; ?>
                    </select>
                </div>

                <div class="col-md-3">
                    <select name="sesi_id" class="form-select form-select-sm" required>
                        <option value="">-- Pilih Sesi --</option>
                        <?php foreach ($sesiList as $s): ?>
                            <option value="<?= $s['id_sesi'] ?>"><?= escape_html($s['nama_sesi']) ?> (<?= substr($s['waktu_mulai'], 0, 5) ?> - <?= substr($s['waktu_akhir'], 0, 5) ?>)</option>
                        <?php endforeach; ?>
                    </select>
                </div>

                <div class="col-auto">
                    <button type="submit" class="btn btn-primary btn-sm">
                        <i class="bi bi-check-all me-1"></i> Terapkan ke Siswa Tercentang
                    </button>
                </div>
            </form>
        </div>
    </div>

    <!-- Students Table -->
    <div class="card shadow-sm border-0">
        <div class="card-header bg-white py-2 d-flex justify-content-between align-items-center">
            <span class="fw-bold"><i class="bi bi-people text-primary me-1"></i> Daftar Siswa Kelas Terpilih</span>
            <span class="badge bg-primary"><?= count($students) ?> Siswa</span>
        </div>
        <div class="card-body p-0">
            <div class="table-responsive">
                <table class="table table-hover table-striped align-middle mb-0" style="font-size: 13px;">
                    <thead class="table-light">
                        <tr>
                            <th style="width: 40px;" class="text-center">
                                <input type="checkbox" class="form-check-input" id="checkAll" onclick="toggleCheckAll(this)">
                            </th>
                            <th style="width: 40px;" class="text-center">No</th>
                            <th style="width: 100px;">NIS</th>
                            <th style="width: 140px;">Nomor Peserta</th>
                            <th>Nama Siswa</th>
                            <th style="width: 220px;">Ruang Ujian</th>
                            <th style="width: 220px;">Sesi Ujian</th>
                            <th style="width: 80px;" class="text-center">Aksi</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if (empty($students)): ?>
                            <tr>
                                <td colspan="8" class="text-center py-4 text-muted">
                                    Tidak ada data siswa pada kelas ini.
                                </td>
                            </tr>
                        <?php else: ?>
                            <?php $no = 1; foreach ($students as $stu): ?>
                                <?php $formId = 'form_row_' . $stu['id_siswa']; ?>
                                <tr>
                                    <td class="text-center">
                                        <input type="checkbox" name="siswa_ids[]" value="<?= $stu['id_siswa'] ?>" form="formBulkAssign" class="form-check-input stu-check">
                                    </td>
                                    <td class="text-center"><?= $no++ ?></td>
                                    <td class="font-monospace"><?= escape_html($stu['nis']) ?></td>
                                    <td class="font-monospace">
                                        <span class="badge bg-light text-dark border">
                                            <?= escape_html($stu['nomor_peserta'] ?? '-') ?>
                                        </span>
                                    </td>
                                    <td>
                                        <div class="fw-semibold text-dark"><?= escape_html($stu['nama']) ?></div>
                                    </td>
                                    <td>
                                        <select name="ruang_id" form="<?= $formId ?>" class="form-select form-select-sm" required>
                                            <option value="">-- Pilih Ruang --</option>
                                            <?php foreach ($ruangList as $r): ?>
                                                <option value="<?= $r['id_ruang'] ?>" <?= (int)($stu['ruang_id'] ?? 0) === (int)$r['id_ruang'] ? 'selected' : '' ?>>
                                                    <?= escape_html($r['nama_ruang']) ?> (<?= escape_html($r['kode_ruang']) ?>)
                                                </option>
                                            <?php endforeach; ?>
                                        </select>
                                    </td>
                                    <td>
                                        <select name="sesi_id" form="<?= $formId ?>" class="form-select form-select-sm" required>
                                            <option value="">-- Pilih Sesi --</option>
                                            <?php foreach ($sesiList as $s): ?>
                                                <option value="<?= $s['id_sesi'] ?>" <?= (int)($stu['sesi_id'] ?? 0) === (int)$s['id_sesi'] ? 'selected' : '' ?>>
                                                    <?= escape_html($s['nama_sesi']) ?> (<?= substr($s['waktu_mulai'], 0, 5) ?> - <?= substr($s['waktu_akhir'], 0, 5) ?>)
                                                </option>
                                            <?php endforeach; ?>
                                        </select>
                                    </td>
                                    <td class="text-center">
                                        <form id="<?= $formId ?>" action="<?= base_url('/cbt/master/ruang-sesi-siswa/save') ?>" method="POST" onsubmit="saveRowAjax(event, this)">
                                            <input type="hidden" name="id_kelas" value="<?= $selectedKelasId ?>">
                                            <input type="hidden" name="id_siswa" value="<?= $stu['id_siswa'] ?>">
                                            <button type="submit" class="btn btn-outline-primary btn-sm py-1 px-2" title="Simpan Baris">
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
function toggleCheckAll(source) {
    const checkboxes = document.querySelectorAll('.stu-check');
    checkboxes.forEach(c => c.checked = source.checked);
}

function saveRowAjax(e, form) {
    e.preventDefault();
    const btn = form.querySelector('button[type="submit"]');
    const origHtml = btn.innerHTML;
    btn.innerHTML = '<span class="spinner-border spinner-border-sm"></span>';
    btn.disabled = true;

    const formData = new FormData(form);

    fetch('<?= base_url('/cbt/master/ruang-sesi-siswa/save') ?>', {
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
        alert('Gagal menyimpan ruang dan sesi.');
    });
}
</script>
