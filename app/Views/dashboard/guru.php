<div class="d-flex flex-wrap justify-content-between align-items-center mb-4">
    <div>
        <h4 class="fw-bold mb-1 text-dark">Portal Pendidik (Guru)</h4>
        <p class="text-muted small mb-0">
            Selamat datang, <strong><?= escape_html($guru['nama_guru'] ?? 'Bapak/Ibu Guru') ?></strong> | NIP: <?= escape_html($guru['nip'] ?? '-') ?>
        </p>
    </div>
    <div class="tp-smt-badge">
        <i class="bi bi-calendar-check"></i>
        <span>TP: <?= escape_html($tp['tahun'] ?? '-') ?> (<?= escape_html($smt['nama_smt'] ?? '-') ?>)</span>
    </div>
</div>

<div class="row g-3 mb-4">
    <div class="col-md-4">
        <div class="card stat-card bg-white p-3 h-100">
            <div class="d-flex align-items-center justify-content-between">
                <div>
                    <span class="text-muted small fw-semibold text-uppercase">Materi & Tugas</span>
                    <h4 class="fw-bold text-dark mt-1 mb-0">E-Learning</h4>
                    <a href="<?= base_url('/guru/materi') ?>" class="small text-primary text-decoration-none">Kelola Materi &rarr;</a>
                </div>
                <div class="stat-icon-box bg-primary-subtle text-primary">
                    <i class="bi bi-file-earmark-text-fill"></i>
                </div>
            </div>
        </div>
    </div>

    <div class="col-md-4">
        <div class="card stat-card bg-white p-3 h-100">
            <div class="d-flex align-items-center justify-content-between">
                <div>
                    <span class="text-muted small fw-semibold text-uppercase">Bank Soal CBT</span>
                    <h4 class="fw-bold text-dark mt-1 mb-0">5 Tipe AKM</h4>
                    <a href="<?= base_url('/guru/bank-soal') ?>" class="small text-warning text-decoration-none">Kelola Soal &rarr;</a>
                </div>
                <div class="stat-icon-box bg-warning-subtle text-warning">
                    <i class="bi bi-archive-fill"></i>
                </div>
            </div>
        </div>
    </div>

    <div class="col-md-4">
        <div class="card stat-card bg-white p-3 h-100">
            <div class="d-flex align-items-center justify-content-between">
                <div>
                    <span class="text-muted small fw-semibold text-uppercase">Pengawasan CBT</span>
                    <h4 class="fw-bold text-dark mt-1 mb-0">Ruang & Sesi</h4>
                    <a href="<?= base_url('/guru/pengawas') ?>" class="small text-success text-decoration-none">Buka Ruang &rarr;</a>
                </div>
                <div class="stat-icon-box bg-success-subtle text-success">
                    <i class="bi bi-shield-check"></i>
                </div>
            </div>
        </div>
    </div>
</div>

