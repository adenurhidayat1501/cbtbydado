<div class="container-fluid py-3">
    <div class="d-flex justify-content-between align-items-center mb-3">
        <div>
            <h4 class="mb-0 fw-bold"><i class="bi bi-calendar3 text-primary me-2"></i>Jadwal Mengajar Saya</h4>
            <small class="text-muted">Guru: <?= escape_html($guru['nama_guru'] ?? '-') ?> (NIP: <?= escape_html($guru['nip'] ?? '-') ?>)</small>
        </div>
        <div>
            <span class="badge bg-primary px-3 py-2">
                TP <?= escape_html($tp['tahun'] ?? '-') ?> - Semester <?= escape_html($smt['nama_smt'] ?? '-') ?>
            </span>
        </div>
    </div>

    <div class="row g-3">
        <?php foreach ($days as $idHari => $dayName): ?>
            <div class="col-md-6 col-lg-4">
                <div class="card shadow-sm border-0 h-100">
                    <div class="card-header bg-white py-2 fw-bold d-flex justify-content-between align-items-center">
                        <span><i class="bi bi-calendar-day text-primary me-1"></i> <?= $dayName ?></span>
                        <span class="badge bg-light text-secondary border">
                            <?= count($groupedSchedules[$idHari] ?? []) ?> Jam
                        </span>
                    </div>
                    <div class="card-body p-2">
                        <?php $schedules = $groupedSchedules[$idHari] ?? []; ?>
                        <?php if (empty($schedules)): ?>
                            <div class="text-muted text-center py-4 small">
                                <i class="bi bi-cup-hot d-block fs-3 mb-1 text-secondary"></i>
                                Tidak ada jadwal mengajar pada hari ini.
                            </div>
                        <?php else: ?>
                            <div class="list-group list-group-flush">
                                <?php foreach ($schedules as $s): ?>
                                    <div class="list-group-item px-2 py-2 d-flex justify-content-between align-items-center">
                                        <div>
                                            <span class="badge bg-primary-subtle text-primary border border-primary-subtle me-1">
                                                Jam Ke-<?= $s['jam_ke'] ?>
                                            </span>
                                            <strong class="text-dark"><?= escape_html($s['nama_mapel']) ?></strong>
                                        </div>
                                        <span class="badge bg-info text-white">
                                            <?= escape_html($s['nama_kelas']) ?>
                                        </span>
                                    </div>
                                <?php endforeach; ?>
                            </div>
                        <?php endif; ?>
                    </div>
                </div>
            </div>
        <?php endforeach; ?>
    </div>
</div>

