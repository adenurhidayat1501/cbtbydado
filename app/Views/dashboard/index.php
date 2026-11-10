<div class="d-flex flex-wrap justify-content-between align-items-center mb-4">
    <div>
        <h4 class="fw-bold mb-1 text-dark">Dashboard Utama Administrator</h4>
        <p class="text-muted small mb-0">
            Sistem Informasi DADO CBT — Tahun Pelajaran <strong><?= escape_html($tp['tahun'] ?? '-') ?></strong> | Semester <strong><?= escape_html($smt['nama_smt'] ?? '-') ?></strong>
        </p>
    </div>
    <div class="d-flex align-items-center gap-2 mt-2 mt-sm-0">
        <button class="btn btn-outline-primary btn-sm d-flex align-items-center gap-1" data-bs-toggle="modal" data-bs-target="#switchTpSmtModal">
            <i class="bi bi-arrow-repeat"></i> Ganti Periode Aktif
        </button>
    </div>
</div>

<!-- Statistics Cards Grid -->
<div class="row g-3 mb-4">
    <!-- Card Siswa -->
    <div class="col-12 col-sm-6 col-xl-3">
        <div class="card stat-card bg-white p-3 h-100">
            <div class="d-flex align-items-center justify-content-between">
                <div>
                    <span class="text-muted small fw-semibold text-uppercase">Peserta Didik</span>
                    <h3 class="fw-bold text-dark mt-1 mb-0"><?= number_format($stats['total_siswa'] ?? 0) ?></h3>
                    <small class="text-success fw-medium"><i class="bi bi-check-circle"></i> Status Aktif</small>
                </div>
                <div class="stat-icon-box bg-primary-subtle text-primary">
                    <i class="bi bi-people-fill"></i>
                </div>
            </div>
        </div>
    </div>

    <!-- Card Guru -->
    <div class="col-12 col-sm-6 col-xl-3">
        <div class="card stat-card bg-white p-3 h-100">
            <div class="d-flex align-items-center justify-content-between">
                <div>
                    <span class="text-muted small fw-semibold text-uppercase">Pendidik & Staf</span>
                    <h3 class="fw-bold text-dark mt-1 mb-0"><?= number_format($stats['total_guru'] ?? 0) ?></h3>
                    <small class="text-primary fw-medium"><i class="bi bi-person-badge"></i> Terdaftar</small>
                </div>
                <div class="stat-icon-box bg-success-subtle text-success">
                    <i class="bi bi-person-workspace"></i>
                </div>
            </div>
        </div>
    </div>

    <!-- Card Bank Soal -->
    <div class="col-12 col-sm-6 col-xl-3">
        <div class="card stat-card bg-white p-3 h-100">
            <div class="d-flex align-items-center justify-content-between">
                <div>
                    <span class="text-muted small fw-semibold text-uppercase">Bank Soal (AKM)</span>
                    <h3 class="fw-bold text-dark mt-1 mb-0"><?= number_format($stats['total_bank'] ?? 0) ?></h3>
                    <small class="text-muted fw-medium">Semester Berjalan</small>
                </div>
                <div class="stat-icon-box bg-warning-subtle text-warning">
                    <i class="bi bi-archive-fill"></i>
                </div>
            </div>
        </div>
    </div>

    <!-- Card Jadwal Ujian Hari Ini -->
    <div class="col-12 col-sm-6 col-xl-3">
        <div class="card stat-card bg-white p-3 h-100">
            <div class="d-flex align-items-center justify-content-between">
                <div>
                    <span class="text-muted small fw-semibold text-uppercase">Ujian Hari Ini</span>
                    <h3 class="fw-bold text-danger mt-1 mb-0"><?= number_format($stats['total_jadwal_hari_ini'] ?? 0) ?></h3>
                    <small class="text-danger fw-medium"><i class="bi bi-broadcast"></i> Sesi Aktif</small>
                </div>
                <div class="stat-icon-box bg-danger-subtle text-danger">
                    <i class="bi bi-clock-history"></i>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Row: CBT Token & Fast Actions & Render Verification -->
<div class="row g-4">
    <!-- Token Widget -->
    <div class="col-lg-5">
        <div class="card border-0 shadow-sm rounded-3 overflow-hidden h-100">
            <div class="card-header bg-dark text-white d-flex align-items-center justify-content-between py-3">
                <span class="fw-bold"><i class="bi bi-key-fill text-warning me-2"></i> Token Ujian CBT Aktif</span>
                <span class="badge bg-primary">Otomatis / Manual</span>
            </div>
            <div class="card-body text-center py-4">
                <div class="text-muted small mb-2">Token Peserta Ujian Saat Ini:</div>
                <div id="tokenDisplay" class="display-4 fw-bold letter-spacing-2 text-primary border rounded-3 py-2 bg-light mb-3 font-monospace">
                    <?= escape_html($tokenData['token'] ?? 'DADO') ?>
                </div>
                <div class="text-muted small mb-3">
                    <i class="bi bi-clock"></i> Terakhir Diperbarui: <strong id="tokenTime"><?= format_indo_date($tokenData['updated_at'] ?? date('Y-m-d H:i:s'), true) ?></strong>
                </div>
                <div class="d-flex justify-content-center gap-2">
                    <button type="button" class="btn btn-primary btn-sm" onclick="generateNewCbtToken()">
                        <i class="bi bi-arrow-repeat me-1"></i> Rilis Token Baru
                    </button>
                    <a href="<?= base_url('/cbt/token') ?>" class="btn btn-outline-secondary btn-sm">
                        <i class="bi bi-gear me-1"></i> Pengaturan
                    </a>
                </div>
            </div>
        </div>
    </div>

    <!-- Integrasi KaTeX & Arabic Calibri Verification Card -->
    <div class="col-lg-7">
        <div class="card border-0 shadow-sm rounded-3 h-100">
            <div class="card-header bg-white border-bottom py-3 d-flex align-items-center justify-content-between">
                <h6 class="fw-bold mb-0 text-dark"><i class="bi bi-check2-circle text-success me-2"></i> Status Modul & Integrasi Aset</h6>
                <span class="badge bg-success">Ready</span>
            </div>
            <div class="card-body">
                <p class="text-muted small mb-3">
                    Sistem telah terkonfigurasi dengan parser rumus matematika KaTeX dan rendering teks Arab (Font Arabic Calibri) untuk mendukung butir soal Pendidikan Agama Islam dan sains:
                </p>

                <!-- KaTeX Test Box -->
                <div class="p-3 bg-light rounded-3 border mb-3">
                    <span class="badge bg-secondary mb-1">KaTeX Formula Preview:</span>
                    <div class="text-primary fw-bold">
                        $$S_{total} = S_{pg} + S_{pgk} + S_{jodoh} + S_{isian} + S_{esai}$$
                    </div>
                </div>

                <!-- Arabic Font Test Box -->
                <div class="p-3 bg-light rounded-3 border">
                    <span class="badge bg-success mb-1">Font Arabic Calibri Preview:</span>
                    <div class="arabic-text text-dark">
                        بِسْمِ اللَّهِ الرَّحْمَٰنِ الرَّحِيمِ — اقْرَأْ بِاسْمِ رَبِّكَ الَّذِي خَلَقَ
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
function generateNewCbtToken() {
    fetch('<?= base_url('/cbt/token/generate') ?>', {
        method: 'POST',
        headers: { 'X-Requested-With': 'XMLHttpRequest' }
    })
    .then(res => res.json())
    .then(data => {
        if (data.success) {
            document.getElementById('tokenDisplay').innerText = data.token;
            document.getElementById('tokenTime').innerText = data.updated_at;
        } else {
            alert('Gagal merilis token baru.');
        }
    })
    .catch(() => alert('Terjadi kesalahan koneksi server.'));
}
</script>

