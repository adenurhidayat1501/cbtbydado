<div class="container-fluid py-3">
    <div class="d-flex justify-content-between align-items-center mb-3">
        <div>
            <h4 class="mb-0 fw-bold"><i class="bi bi-clock-history text-primary me-2"></i>Jadwal Pelajaran KBM</h4>
            <small class="text-muted">Tahun Pelajaran <?= escape_html($tp['tahun'] ?? '-') ?> - Semester <?= escape_html($smt['nama_smt'] ?? '-') ?></small>
        </div>
        <div class="d-flex gap-2">
            <select class="form-select form-select-sm" style="min-width: 200px;" onchange="window.location.href='<?= base_url('/elearning/jadwal-kbm?kelas_id=') ?>' + this.value;">
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

    <div class="row g-3">
        <!-- Settings Column -->
        <div class="col-lg-3">
            <div class="card shadow-sm border-0 mb-3">
                <div class="card-header bg-white fw-bold py-2">
                    <i class="bi bi-sliders me-1 text-primary"></i> Pengaturan Waktu KBM
                </div>
                <div class="card-body">
                    <form action="<?= base_url('/elearning/jadwal-kbm/settings') ?>" method="POST">
                        <input type="hidden" name="id_kelas" value="<?= $selectedKelasId ?>">

                        <div class="mb-2">
                            <label class="form-label small fw-semibold">Jam Mulai KBM</label>
                            <input type="time" name="kbm_jam_mulai" class="form-control form-control-sm" value="<?= escape_html(substr($setting['kbm_jam_mulai'] ?? '07:00', 0, 5)) ?>" required>
                        </div>

                        <div class="mb-2">
                            <label class="form-label small fw-semibold">Durasi per Jam (Menit)</label>
                            <input type="number" name="kbm_jam_pel" class="form-control form-control-sm" min="15" max="90" value="<?= (int)($setting['kbm_jam_pel'] ?? 40) ?>" required>
                        </div>

                        <div class="mb-3">
                            <label class="form-label small fw-semibold">Jumlah Jam per Hari</label>
                            <input type="number" name="kbm_jml_mapel_hari" class="form-control form-control-sm" min="1" max="12" value="<?= (int)($setting['kbm_jml_mapel_hari'] ?? 6) ?>" required>
                        </div>

                        <hr class="my-2">
                        <div class="fw-semibold small mb-2"><i class="bi bi-cup-hot text-warning me-1"></i>Jam Istirahat</div>

                        <?php
                        $istList = $setting['istirahat'] ?? [];
                        if (is_string($istList)) {
                            $istList = json_decode($istList, true) ?: [];
                        }
                        $ist1 = $istList[0] ?? ['mulai' => '09:40', 'durasi' => 20];
                        $ist2 = $istList[1] ?? ['mulai' => '11:40', 'durasi' => 40];
                        ?>

                        <div class="p-2 border rounded bg-light mb-2">
                            <div class="small fw-semibold text-secondary mb-1">Istirahat 1</div>
                            <div class="row g-1">
                                <div class="col-7">
                                    <label class="small text-muted" style="font-size: 11px;">Mulai</label>
                                    <input type="time" name="ist1_mulai" class="form-control form-control-sm" value="<?= escape_html(substr($ist1['mulai'] ?? '', 0, 5)) ?>">
                                </div>
                                <div class="col-5">
                                    <label class="small text-muted" style="font-size: 11px;">Menit</label>
                                    <input type="number" name="ist1_durasi" class="form-control form-control-sm" min="0" max="60" value="<?= (int)($ist1['durasi'] ?? 0) ?>">
                                </div>
                            </div>
                        </div>

                        <div class="p-2 border rounded bg-light mb-3">
                            <div class="small fw-semibold text-secondary mb-1">Istirahat 2 (Opsional)</div>
                            <div class="row g-1">
                                <div class="col-7">
                                    <label class="small text-muted" style="font-size: 11px;">Mulai</label>
                                    <input type="time" name="ist2_mulai" class="form-control form-control-sm" value="<?= escape_html(substr($ist2['mulai'] ?? '', 0, 5)) ?>">
                                </div>
                                <div class="col-5">
                                    <label class="small text-muted" style="font-size: 11px;">Menit</label>
                                    <input type="number" name="ist2_durasi" class="form-control form-control-sm" min="0" max="90" value="<?= (int)($ist2['durasi'] ?? 0) ?>">
                                </div>
                            </div>
                        </div>

                        <button type="submit" class="btn btn-primary btn-sm w-100">
                            <i class="bi bi-save me-1"></i> Simpan Waktu KBM
                        </button>
                    </form>
                </div>
            </div>
        </div>

        <!-- Matrix Column -->
        <div class="col-lg-9">
            <div class="card shadow-sm border-0">
                <div class="card-header bg-white py-2 d-flex justify-content-between align-items-center">
                    <span class="fw-bold"><i class="bi bi-grid-3x3 me-1 text-primary"></i> Matriks Jadwal Mingguan</span>
                    <span class="badge bg-primary px-2 py-1">
                        <?= escape_html(array_reduce($kelasList, fn($c, $x) => (int)$x['id_kelas'] === (int)$selectedKelasId ? $x['nama_kelas'] : $c, 'Kelas')) ?>
                    </span>
                </div>
                <div class="card-body p-0 table-responsive">
                    <table class="table table-bordered table-hover align-middle mb-0 text-center" style="font-size: 13px;">
                        <thead class="table-light">
                            <tr>
                                <th style="width: 140px;">Jam & Waktu</th>
                                <?php foreach ($days as $idHari => $dayName): ?>
                                    <th><?= $dayName ?></th>
                                <?php endforeach; ?>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($timeSlots as $slot): ?>
                                <?php if ($slot['is_istirahat']): ?>
                                    <tr class="table-warning text-dark fw-semibold" style="background-color: #fff3cd;">
                                        <td class="py-2">
                                            <i class="bi bi-cup-hot text-danger me-1"></i>
                                            <?= $slot['mulai'] ?> - <?= $slot['selesai'] ?>
                                        </td>
                                        <td colspan="6" class="py-2 text-uppercase letter-spacing-1">
                                            <?= escape_html($slot['label']) ?> (<?= $slot['durasi'] ?> Menit)
                                        </td>
                                    </tr>
                                <?php else: ?>
                                    <?php $j = (int)$slot['jam_ke']; ?>
                                    <tr>
                                        <td class="table-light py-2 text-start px-2">
                                            <div class="fw-bold text-primary"><?= escape_html($slot['label']) ?></div>
                                            <div class="text-muted" style="font-size: 11px;"><?= $slot['mulai'] ?> - <?= $slot['selesai'] ?></div>
                                        </td>
                                        <?php foreach ($days as $idHari => $dayName): ?>
                                            <?php
                                            $cell = $matrix[$idHari][$j] ?? null;
                                            $cellId = "cell_{$idHari}_{$j}";
                                            ?>
                                            <td class="p-1 position-relative" id="<?= $cellId ?>" style="min-width: 130px; height: 60px;">
                                                <?php if ($cell): ?>
                                                    <div class="schedule-box border rounded p-1 text-start bg-light position-relative" style="cursor: pointer;"
                                                         onclick="openScheduleModal(<?= $idHari ?>, <?= $j ?>, <?= $cell['id_mapel'] ?>, <?= $cell['id_guru'] ?: 0 ?>, '<?= safe_print_str($dayName) ?>', '<?= safe_print_str($slot['label']) ?>')">
                                                        <div class="fw-bold text-primary text-truncate" style="font-size: 12px;" title="<?= escape_html($cell['nama_mapel']) ?>">
                                                            <?= escape_html($cell['nama_mapel']) ?>
                                                        </div>
                                                        <div class="text-muted text-truncate" style="font-size: 11px;" title="<?= escape_html($cell['nama_guru'] ?? 'Belum ditentukan') ?>">
                                                            <i class="bi bi-person me-1"></i><?= escape_html($cell['nama_guru'] ?? '-') ?>
                                                        </div>
                                                    </div>
                                                <?php else: ?>
                                                    <button type="button" class="btn btn-outline-dashed btn-sm w-100 h-100 py-2 text-muted border-0"
                                                            onclick="openScheduleModal(<?= $idHari ?>, <?= $j ?>, 0, 0, '<?= safe_print_str($dayName) ?>', '<?= safe_print_str($slot['label']) ?>')">
                                                        <i class="bi bi-plus-circle text-secondary"></i>
                                                    </button>
                                                <?php endif; ?>
                                            </td>
                                        <?php endforeach; ?>
                                    </tr>
                                <?php endif; ?>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Modal Atur Mapel / Guru -->
<div class="modal fade" id="modalScheduleCell" tabindex="-1">
    <div class="modal-dialog modal-sm modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-header py-2">
                <h6 class="modal-title fw-bold" id="modalCellTitle">Atur Jadwal</h6>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <form id="formScheduleCell">
                    <input type="hidden" id="modalHari" name="id_hari">
                    <input type="hidden" id="modalJamKe" name="jam_ke">
                    <input type="hidden" name="id_kelas" value="<?= $selectedKelasId ?>">

                    <div class="mb-3">
                        <label class="form-label small fw-semibold">Mata Pelajaran</label>
                        <select class="form-select form-select-sm" id="modalMapel" name="id_mapel" required>
                            <option value="">-- Pilih Mapel --</option>
                            <?php foreach ($mapelList as $m): ?>
                                <option value="<?= $m['id_mapel'] ?>"><?= escape_html($m['nama_mapel']) ?> (<?= escape_html($m['kode']) ?>)</option>
                            <?php endforeach; ?>
                        </select>
                    </div>

                    <div class="mb-3">
                        <label class="form-label small fw-semibold">Guru Pengampu</label>
                        <select class="form-select form-select-sm" id="modalGuru" name="id_guru">
                            <option value="">-- Belum Ditentukan / Guru Kosong --</option>
                            <?php foreach ($guruList as $g): ?>
                                <option value="<?= $g['id_guru'] ?>"><?= escape_html($g['nama_guru']) ?></option>
                            <?php endforeach; ?>
                        </select>
                    </div>

                    <div class="d-flex justify-content-between gap-2">
                        <button type="button" class="btn btn-outline-danger btn-sm" onclick="clearScheduleCell()">
                            <i class="bi bi-trash me-1"></i> Kosongkan
                        </button>
                        <button type="submit" class="btn btn-primary btn-sm">
                            <i class="bi bi-save me-1"></i> Simpan
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>

<script>
let scheduleModal;
document.addEventListener('DOMContentLoaded', function() {
    scheduleModal = new bootstrap.Modal(document.getElementById('modalScheduleCell'));

    document.getElementById('formScheduleCell').addEventListener('submit', function(e) {
        e.preventDefault();
        saveCellAjax();
    });
});

function openScheduleModal(idHari, jamKe, idMapel, idGuru, dayName, slotLabel) {
    document.getElementById('modalHari').value = idHari;
    document.getElementById('modalJamKe').value = jamKe;
    document.getElementById('modalMapel').value = idMapel > 0 ? idMapel : '';
    document.getElementById('modalGuru').value = idGuru > 0 ? idGuru : '';
    document.getElementById('modalCellTitle').innerText = dayName + ' — ' + slotLabel;
    scheduleModal.show();
}

function clearScheduleCell() {
    document.getElementById('modalMapel').value = '';
    document.getElementById('modalGuru').value = '';
    saveCellAjax();
}

function saveCellAjax() {
    const formData = new FormData(document.getElementById('formScheduleCell'));

    fetch('<?= base_url('/elearning/jadwal-kbm/cell') ?>', {
        method: 'POST',
        headers: {
            'X-Requested-With': 'XMLHttpRequest'
        },
        body: formData
    })
    .then(r => r.json())
    .then(res => {
        scheduleModal.hide();
        if (res.status === 'success') {
            window.location.reload();
        } else {
            alert(res.message || 'Gagal menyimpan jadwal');
        }
    })
    .catch(err => {
        alert('Terjadi kesalahan jaringan');
    });
}
</script>

