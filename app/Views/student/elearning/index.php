<div class="container-fluid py-3">
    <div class="d-flex justify-content-between align-items-center mb-3">
        <div>
            <h4 class="mb-0 fw-bold"><i class="bi bi-book-half text-primary me-2"></i>Ruang Belajar & Tugas</h4>
            <small class="text-muted">Halo, <?= escape_html($siswa['nama']) ?>! Pelajari materi dan selesaikan tugas belajarmu.</small>
        </div>
        <div>
            <span class="badge bg-primary px-3 py-2">
                TP <?= escape_html($tp['tahun'] ?? '-') ?> - Semester <?= escape_html($smt['nama_smt'] ?? '-') ?>
            </span>
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

    <!-- Navigation Tabs -->
    <ul class="nav nav-pills mb-3" id="elearningTab" role="tablist">
        <li class="nav-item" role="presentation">
            <button class="nav-link active fw-semibold" id="materi-tab" data-bs-toggle="pill" data-bs-target="#tab-materi" type="button" role="tab">
                <i class="bi bi-journal-text me-1"></i> Materi Belajar
                <span class="badge bg-light text-primary border ms-1"><?= count($materiList) ?></span>
            </button>
        </li>
        <li class="nav-item" role="presentation">
            <button class="nav-link fw-semibold" id="tugas-tab" data-bs-toggle="pill" data-bs-target="#tab-tugas" type="button" role="tab">
                <i class="bi bi-pencil-square me-1"></i> Tugas Siswa
                <span class="badge bg-light text-warning-emphasis border ms-1"><?= count($tugasList) ?></span>
            </button>
        </li>
    </ul>

    <div class="tab-content" id="elearningTabContent">
        <!-- Tab 1: Materi Belajar -->
        <div class="tab-pane fade show active" id="tab-materi" role="tabpanel">
            <?php if (empty($materiList)): ?>
                <div class="card border-0 shadow-sm text-center py-5">
                    <div class="card-body">
                        <i class="bi bi-journal-x fs-1 text-secondary mb-2 d-block"></i>
                        <h6 class="text-muted fw-bold">Belum Ada Materi Belajar</h6>
                        <p class="text-muted small mb-0">Guru pengampu belum mengunggah jadwal materi untuk kelasmu.</p>
                    </div>
                </div>
            <?php else: ?>
                <div class="row g-3">
                    <?php foreach ($materiList as $m): ?>
                        <?php $isRead = !empty($m['id_log']); ?>
                        <div class="col-md-6 col-lg-4">
                            <div class="card border-0 shadow-sm h-100 hover-card">
                                <div class="card-body d-flex flex-column p-3">
                                    <div class="d-flex justify-content-between align-items-center mb-2">
                                        <span class="badge bg-primary-subtle text-primary border border-primary-subtle">
                                            <?= escape_html($m['nama_mapel']) ?>
                                        </span>
                                        <?php if ($isRead): ?>
                                            <span class="badge bg-success-subtle text-success border border-success-subtle">
                                                <i class="bi bi-check2 me-1"></i> Sudah Dibaca
                                            </span>
                                        <?php else: ?>
                                            <span class="badge bg-secondary-subtle text-secondary border">
                                                Belum Dibaca
                                            </span>
                                        <?php endif; ?>
                                    </div>

                                    <h6 class="fw-bold text-dark mb-1 flex-grow-1">
                                        <?= escape_html($m['judul_materi']) ?>
                                    </h6>
                                    <div class="text-muted small mb-3">
                                        <i class="bi bi-person me-1"></i> <?= escape_html($m['nama_guru']) ?>
                                    </div>

                                    <div class="d-flex justify-content-between align-items-center pt-2 border-top text-muted small mt-auto">
                                        <span><i class="bi bi-calendar3 me-1"></i> <?= format_indo_date($m['jadwal_materi']) ?></span>
                                        <a href="<?= base_url('/student/elearning/baca/' . $m['id_kjm']) ?>" class="btn btn-primary btn-sm px-3">
                                            Buka Materi <i class="bi bi-arrow-right ms-1"></i>
                                        </a>
                                    </div>
                                </div>
                            </div>
                        </div>
                    <?php endforeach; ?>
                </div>
            <?php endif; ?>
        </div>

        <!-- Tab 2: Tugas Siswa -->
        <div class="tab-pane fade" id="tab-tugas" role="tabpanel">
            <?php if (empty($tugasList)): ?>
                <div class="card border-0 shadow-sm text-center py-5">
                    <div class="card-body">
                        <i class="bi bi-clipboard-x fs-1 text-secondary mb-2 d-block"></i>
                        <h6 class="text-muted fw-bold">Belum Ada Tugas Belajar</h6>
                        <p class="text-muted small mb-0">Tidak ada tugas yang harus dikumpulkan saat ini.</p>
                    </div>
                </div>
            <?php else: ?>
                <div class="row g-3">
                    <?php foreach ($tugasList as $t): ?>
                        <?php
                        $isSubmitted = ((int)($t['log_type'] ?? 0) === 3);
                        $hasScore = ($t['nilai'] !== null);
                        ?>
                        <div class="col-md-6 col-lg-4">
                            <div class="card border-0 shadow-sm h-100 hover-card">
                                <div class="card-body d-flex flex-column p-3">
                                    <div class="d-flex justify-content-between align-items-center mb-2">
                                        <span class="badge bg-warning-subtle text-warning-emphasis border border-warning-subtle">
                                            <?= escape_html($t['nama_mapel']) ?>
                                        </span>
                                        <?php if ($hasScore): ?>
                                            <span class="badge bg-success text-white">
                                                Nilai: <?= number_format((float)$t['nilai'], 1) ?>
                                            </span>
                                        <?php elseif ($isSubmitted): ?>
                                            <span class="badge bg-info-subtle text-info-emphasis border border-info-subtle">
                                                <i class="bi bi-clock-history me-1"></i> Menunggu Koreksi
                                            </span>
                                        <?php else: ?>
                                            <span class="badge bg-danger-subtle text-danger border border-danger-subtle">
                                                Belum Dikerjakan
                                            </span>
                                        <?php endif; ?>
                                    </div>

                                    <h6 class="fw-bold text-dark mb-1 flex-grow-1">
                                        <?= escape_html($t['judul_materi']) ?>
                                    </h6>
                                    <div class="text-muted small mb-3">
                                        <i class="bi bi-person me-1"></i> <?= escape_html($t['nama_guru']) ?>
                                    </div>

                                    <div class="d-flex justify-content-between align-items-center pt-2 border-top text-muted small mt-auto">
                                        <span><i class="bi bi-calendar-event me-1"></i> <?= format_indo_date($t['jadwal_materi']) ?></span>
                                        <a href="<?= base_url('/student/elearning/tugas/' . $t['id_kjm']) ?>" class="btn btn-warning btn-sm text-dark px-3 fw-semibold">
                                            <?= $isSubmitted ? 'Lihat Tugas' : 'Kerjakan Tugas' ?> <i class="bi bi-arrow-right ms-1"></i>
                                        </a>
                                    </div>
                                </div>
                            </div>
                        </div>
                    <?php endforeach; ?>
                </div>
            <?php endif; ?>
        </div>
    </div>
</div>

