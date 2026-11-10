<div class="container-fluid py-3">
    <div class="d-flex justify-content-between align-items-center mb-3">
        <div>
            <h4 class="mb-0 fw-bold"><i class="bi bi-pencil-square text-primary me-2"></i>Koreksi Tugas Siswa</h4>
            <small class="text-muted">
                <?= escape_html($schedule['nama_kelas']) ?> | <?= escape_html($schedule['nama_mapel']) ?> | Tanggal: <?= format_indo_date($schedule['jadwal_materi']) ?>
            </small>
        </div>
        <div>
            <a href="<?= base_url('/elearning/presensi-nilai?kelas_id=' . $schedule['id_kelas']) ?>" class="btn btn-outline-secondary btn-sm">
                <i class="bi bi-arrow-left me-1"></i> Kembali ke Daftar Tugas
            </a>
        </div>
    </div>

    <!-- Task Information Banner -->
    <div class="card shadow-sm border-0 mb-3 bg-light">
        <div class="card-body py-3">
            <div class="row align-items-center">
                <div class="col-md-9">
                    <span class="badge bg-warning-subtle text-warning-emphasis border border-warning-subtle mb-1">
                        TUGAS SISWA
                    </span>
                    <h5 class="fw-bold mb-1 text-dark"><?= escape_html($schedule['judul_materi']) ?></h5>
                    <div class="small text-muted">
                        Kode: <span class="font-monospace fw-semibold"><?= escape_html($schedule['kode_materi']) ?></span> |
                        Guru: <?= escape_html($schedule['nama_guru']) ?>
                    </div>
                </div>
                <div class="col-md-3 text-md-end mt-2 mt-md-0">
                    <a href="<?= base_url('/elearning/materi-tugas/detail/' . $schedule['id_materi']) ?>" class="btn btn-sm btn-outline-primary">
                        <i class="bi bi-eye me-1"></i> Lihat Petunjuk Tugas
                    </a>
                </div>
            </div>
        </div>
    </div>

    <!-- Submissions Table -->
    <div class="card shadow-sm border-0">
        <div class="card-header bg-white py-2 fw-bold d-flex justify-content-between align-items-center">
            <span><i class="bi bi-people me-1 text-primary"></i> Daftar Siswa & Hasil Pengerjaan</span>
            <span class="badge bg-primary"><?= count($submissions) ?> Siswa</span>
        </div>
        <div class="card-body p-0">
            <div class="table-responsive">
                <table class="table table-hover table-striped align-middle mb-0" style="font-size: 13px;">
                    <thead class="table-light">
                        <tr>
                            <th style="width: 40px;" class="text-center">No</th>
                            <th style="width: 100px;">NIS</th>
                            <th>Nama Siswa</th>
                            <th style="width: 130px;" class="text-center">Status Submisi</th>
                            <th>Jawaban & Lampiran Berkas Siswa</th>
                            <th style="width: 110px;" class="text-center">Nilai (0-100)</th>
                            <th style="width: 250px;">Catatan Revisi / Guru</th>
                            <th style="width: 80px;" class="text-center">Aksi</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if (empty($submissions)): ?>
                            <tr>
                                <td colspan="8" class="text-center py-4 text-muted">
                                    Tidak ada data siswa pada kelas ini.
                                </td>
                            </tr>
                        <?php else: ?>
                            <?php $no = 1; foreach ($submissions as $sub): ?>
                                <?php
                                $isSubmitted = ((int)($sub['log_type'] ?? 0) === 3);
                                $hasLog = !empty($sub['id_log']);
                                $formId = "form_grade_" . $sub['id_siswa'];
                                ?>
                                <tr>
                                    <td class="text-center"><?= $no++ ?></td>
                                    <td class="font-monospace"><?= escape_html($sub['nis']) ?></td>
                                    <td>
                                        <div class="fw-semibold text-dark"><?= escape_html($sub['nama']) ?></div>
                                    </td>
                                    <td class="text-center">
                                        <?php if ($isSubmitted): ?>
                                            <span class="badge bg-success-subtle text-success border border-success-subtle">
                                                <i class="bi bi-check2-circle me-1"></i> Terkumpul
                                            </span>
                                            <div class="text-muted" style="font-size: 11px;">
                                                <?= date('d/m H:i', strtotime($sub['finish_time'] ?? $sub['log_time'])) ?>
                                            </div>
                                        <?php elseif ($hasLog): ?>
                                            <span class="badge bg-info-subtle text-info-emphasis border border-info-subtle">
                                                <i class="bi bi-eye me-1"></i> Membaca
                                            </span>
                                        <?php else: ?>
                                            <span class="badge bg-secondary-subtle text-secondary border">
                                                Belum Buka
                                            </span>
                                        <?php endif; ?>
                                    </td>
                                    <td>
                                        <?php if ($isSubmitted): ?>
                                            <?php if (!empty($sub['isi_tugas'])): ?>
                                                <div class="p-2 border rounded bg-light mb-1 small text-dark" style="max-height: 80px; overflow-y: auto;">
                                                    <?= nl2br(escape_html($sub['isi_tugas'])) ?>
                                                </div>
                                            <?php endif; ?>
                                            <?php if (!empty($sub['file_list'])): ?>
                                                <div class="d-flex flex-wrap gap-1">
                                                    <?php foreach ($sub['file_list'] as $sf): ?>
                                                        <a href="<?= base_url(escape_html($sf['path'])) ?>" target="_blank" class="btn btn-outline-primary btn-sm py-0 px-2" style="font-size: 11px;">
                                                            <i class="bi bi-download me-1"></i><?= escape_html($sf['orig_name'] ?? 'Berkas Tugas') ?>
                                                        </a>
                                                    <?php endforeach; ?>
                                                </div>
                                            <?php endif; ?>
                                        <?php else: ?>
                                            <span class="text-muted small">- Belum mengumpulkan jawaban -</span>
                                        <?php endif; ?>
                                    </td>
                                    <td class="text-center">
                                        <input type="number" step="0.01" min="0" max="100" form="<?= $formId ?>" name="nilai" class="form-control form-control-sm text-center fw-bold"
                                               value="<?= $sub['nilai'] !== null ? number_format((float)$sub['nilai'], 2, '.', '') : '' ?>" placeholder="0.00">
                                    </td>
                                    <td>
                                        <input type="text" form="<?= $formId ?>" name="catatan" class="form-control form-control-sm"
                                               value="<?= escape_html($sub['catatan'] ?? '') ?>" placeholder="Catatan revisi untuk siswa...">
                                    </td>
                                    <td class="text-center">
                                        <form id="<?= $formId ?>" onsubmit="saveGradeAjax(event, this)">
                                            <input type="hidden" name="id_log" value="<?= (int)($sub['id_log'] ?? 0) ?>">
                                            <input type="hidden" name="id_siswa" value="<?= $sub['id_siswa'] ?>">
                                            <input type="hidden" name="id_kjm" value="<?= $schedule['id_kjm'] ?>">
                                            <button type="submit" class="btn btn-primary btn-sm py-1 px-2" title="Simpan Nilai">
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
function saveGradeAjax(e, form) {
    e.preventDefault();
    const btn = form.querySelector('button[type="submit"]');
    const origHtml = btn.innerHTML;
    btn.innerHTML = '<span class="spinner-border spinner-border-sm"></span>';
    btn.disabled = true;

    const formData = new FormData(form);

    fetch('<?= base_url('/elearning/presensi-nilai/grade') ?>', {
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
        alert('Gagal menyimpan nilai.');
    });
}
</script>

