<div class="d-flex flex-wrap justify-content-between align-items-center mb-4">
    <div>
        <h4 class="fw-bold mb-1 text-dark">Struktur Organisasi Sekolah</h4>
        <p class="text-muted small mb-0">
            Bagan kepemimpinan, jajaran wali kelas, dan guru pengampu mata pelajaran — TP <?= escape_html($tp['tahun'] ?? '-') ?> (<?= escape_html($smt['nama_smt'] ?? '-') ?>).
        </p>
    </div>
    <a href="<?= base_url('/master/guru') ?>" class="btn btn-outline-secondary btn-sm d-flex align-items-center gap-1">
        <i class="bi bi-arrow-left"></i> Kembali ke Daftar Guru
    </a>
</div>

<!-- Bagan Organisasi Visual -->
<div class="card border-0 shadow-sm rounded-3 p-4">
    <div class="text-center mb-5">
        <h5 class="fw-bold text-uppercase text-dark mb-1"><?= escape_html($school['nama_sekolah'] ?? 'Sekolah') ?></h5>
        <div class="badge bg-primary px-3 py-2 fs-6">STRUKTUR ORGANISASI & MANAJEMEN KBM</div>
    </div>

    <!-- 1. KEPALA SEKOLAH (TIER 1) -->
    <div class="d-flex justify-content-center mb-5">
        <div class="card border-primary shadow text-center p-3" style="max-width: 320px; width: 100%; border-top: 5px solid #2563eb !important;">
            <div class="mx-auto mb-2 rounded-circle overflow-hidden bg-light border d-flex align-items-center justify-content-center shadow-sm" style="width: 80px; height: 80px;">
                <?php if (!empty($school['tanda_tangan'])): ?>
                    <img src="<?= upload_url($school['tanda_tangan']) ?>" alt="Kepsek" class="w-100 h-100 object-fit-cover">
                <?php else: ?>
                    <i class="bi bi-person-fill text-primary fs-1"></i>
                <?php endif; ?>
            </div>
            <span class="badge bg-danger text-uppercase mb-1">Kepala Sekolah</span>
            <div class="fw-bold text-dark fs-6"><?= escape_html($school['kepsek'] ?? 'Nama Kepala Sekolah') ?></div>
            <small class="text-muted font-monospace">NIP: <?= escape_html($school['nip_kepsek'] ?? '-') ?></small>
        </div>
    </div>

    <!-- Divider Line -->
    <div class="position-relative text-center my-4">
        <hr class="border-secondary opacity-25">
        <span class="position-absolute top-50 start-50 translate-middle bg-white px-3 text-muted small fw-semibold text-uppercase">
            <i class="bi bi-diagram-3 me-1"></i> Jajaran Wali Kelas (Rombel)
        </span>
    </div>

    <!-- 2. WALI KELAS (TIER 2) -->
    <div class="row g-3 justify-content-center mb-5">
        <?php if (empty($waliList)): ?>
            <div class="col-12 text-center text-muted py-3">
                <i class="bi bi-info-circle me-1"></i> Belum ada penugasan wali kelas pada periode aktif ini.
            </div>
        <?php else: ?>
            <?php foreach ($waliList as $w): ?>
                <div class="col-12 col-sm-6 col-md-4 col-xl-3">
                    <div class="card border-0 shadow-sm text-center p-3 h-100 bg-light" style="border-top: 4px solid #10b981 !important;">
                        <span class="badge bg-success mb-2">Wali Kelas: <?= escape_html($w['nama_kelas']) ?></span>
                        <div class="mx-auto mb-2 rounded-circle overflow-hidden bg-white border d-flex align-items-center justify-content-center" style="width: 60px; height: 60px;">
                            <?php if (!empty($w['foto'])): ?>
                                <img src="<?= upload_url($w['foto']) ?>" alt="Wali" class="w-100 h-100 object-fit-cover">
                            <?php else: ?>
                                <i class="bi bi-person text-secondary fs-3"></i>
                            <?php endif; ?>
                        </div>
                        <div class="fw-bold text-dark text-truncate small"><?= escape_html($w['nama_guru']) ?></div>
                        <small class="text-muted font-monospace" style="font-size: 0.75rem;">NIP: <?= escape_html($w['nip'] ?? '-') ?></small>
                    </div>
                </div>
            <?php endforeach; ?>
        <?php endif; ?>
    </div>

    <!-- Divider Line -->
    <div class="position-relative text-center my-4">
        <hr class="border-secondary opacity-25">
        <span class="position-absolute top-50 start-50 translate-middle bg-white px-3 text-muted small fw-semibold text-uppercase">
            <i class="bi bi-mortarboard me-1"></i> Dewan Guru Mata Pelajaran
        </span>
    </div>

    <!-- 3. DEWAN GURU (TIER 3) -->
    <div class="row g-3">
        <?php foreach ($guruMapel as $gm): ?>
            <div class="col-6 col-sm-4 col-md-3 col-xl-2">
                <div class="card border-0 shadow-sm text-center p-2 h-100 bg-white">
                    <div class="mx-auto mb-2 rounded-circle overflow-hidden bg-light border d-flex align-items-center justify-content-center" style="width: 50px; height: 50px;">
                        <?php if (!empty($gm['foto'])): ?>
                            <img src="<?= upload_url($gm['foto']) ?>" alt="Guru" class="w-100 h-100 object-fit-cover">
                        <?php else: ?>
                            <i class="bi bi-person text-secondary fs-4"></i>
                        <?php endif; ?>
                    </div>
                    <div class="fw-bold text-dark text-truncate" style="font-size: 0.8rem;" title="<?= escape_html($gm['nama_guru']) ?>">
                        <?= escape_html($gm['nama_guru']) ?>
                    </div>
                    <small class="badge bg-light text-muted border font-monospace mt-1" style="font-size: 0.7rem;">
                        <?= escape_html($gm['kode_guru'] ?? 'Guru') ?>
                    </small>
                </div>
            </div>
        <?php endforeach; ?>
    </div>
</div>

