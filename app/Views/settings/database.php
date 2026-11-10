<div class="row">
    <div class="col-md-11 mx-auto">
        <div class="card shadow-sm border-0 mb-4">
            <div class="card-header bg-primary text-white py-3">
                <h5 class="card-title mb-0"><i class="bi bi-database me-2"></i>Utilitas & Pemeliharaan Database</h5>
            </div>
            <div class="card-body">
                <?php if (!empty($_SESSION['flash_success'])): ?>
                    <div class="alert alert-success alert-dismissible fade show" role="alert">
                        <i class="bi bi-check-circle me-2"></i><?= nl2br(htmlspecialchars($_SESSION['flash_success'], ENT_QUOTES, 'UTF-8')) ?>
                        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                    </div>
                    <?php unset($_SESSION['flash_success']); ?>
                <?php endif; ?>

                <?php if (!empty($_SESSION['flash_warning'])): ?>
                    <div class="alert alert-warning alert-dismissible fade show" role="alert">
                        <i class="bi bi-exclamation-triangle me-2"></i><?= nl2br(htmlspecialchars($_SESSION['flash_warning'], ENT_QUOTES, 'UTF-8')) ?>
                        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                    </div>
                    <?php unset($_SESSION['flash_warning']); ?>
                <?php endif; ?>

                <?php if (!empty($_SESSION['flash_error'])): ?>
                    <div class="alert alert-danger alert-dismissible fade show" role="alert">
                        <i class="bi bi-x-circle me-2"></i><?= nl2br(htmlspecialchars($_SESSION['flash_error'], ENT_QUOTES, 'UTF-8')) ?>
                        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                    </div>
                    <?php unset($_SESSION['flash_error']); ?>
                <?php endif; ?>

                <!-- Ringkasan Statistik Database & Sistem -->
                <div class="row g-3 mb-4">
                    <div class="col-md-3">
                        <div class="p-3 border rounded bg-light text-center h-100">
                            <div class="text-muted small">Database Aktif</div>
                            <div class="fs-5 fw-bold text-primary font-monospace"><?= htmlspecialchars($dbName, ENT_QUOTES, 'UTF-8') ?></div>
                        </div>
                    </div>
                    <div class="col-md-3">
                        <div class="p-3 border rounded bg-light text-center h-100">
                            <div class="text-muted small">Total Tabel Terdaftar</div>
                            <div class="fs-5 fw-bold text-success font-monospace"><?= $tableCount ?> Tabel</div>
                        </div>
                    </div>
                    <div class="col-md-3">
                        <div class="p-3 border rounded bg-light text-center h-100">
                            <div class="text-muted small">Estimasi Ukuran Data</div>
                            <div class="fs-5 fw-bold text-dark font-monospace"><?= $dbSizeMb ?> MB</div>
                        </div>
                    </div>
                    <div class="col-md-3">
                        <div class="p-3 border rounded bg-light text-center h-100">
                            <div class="text-muted small">Branch Git Aktif</div>
                            <div class="fs-5 fw-bold text-info font-monospace"><?= htmlspecialchars($gitBranch, ENT_QUOTES, 'UTF-8') ?></div>
                        </div>
                    </div>
                </div>

                <div class="row g-4">
                    <!-- Panel 1: Backup Database -->
                    <div class="col-md-6">
                        <div class="card h-100 border shadow-none">
                            <div class="card-header bg-light py-2">
                                <h6 class="card-title fw-bold mb-0 text-primary"><i class="bi bi-download me-1"></i>1. Cadangkan Database (Backup)</h6>
                            </div>
                            <div class="card-body">
                                <p class="small text-muted mb-3">
                                    Unduh seluruh struktur tabel (DDL) dan isi data (DML) aplikasi dalam bentuk file berekstensi <code>.sql</code>. Sangat dianjurkan dilakukan secara berkala sebelum pelaksanaan ujian atau migrasi semester baru.
                                </p>
                                <a href="<?= base_url('/settings/database/backup') ?>" class="btn btn-primary w-100">
                                    <i class="bi bi-cloud-arrow-down me-1"></i>Download Cadangan Database (.sql)
                                </a>
                            </div>
                        </div>
                    </div>

                    <!-- Panel 2: Restore Database -->
                    <div class="col-md-6">
                        <div class="card h-100 border shadow-none">
                            <div class="card-header bg-light py-2">
                                <h6 class="card-title fw-bold mb-0 text-danger"><i class="bi bi-upload me-1"></i>2. Pulihkan Database (Restore)</h6>
                            </div>
                            <div class="card-body">
                                <p class="small text-muted mb-3">
                                    Unggah file cadangan <code>.sql</code> untuk mengembalikan kondisi data sistem. <br>
                                    <span class="text-danger fw-bold"><i class="bi bi-exclamation-triangle"></i> Perhatian:</span> Seluruh data saat ini akan ditimpa oleh data dari file cadangan.
                                </p>
                                <form action="<?= base_url('/settings/database/restore') ?>" method="POST" enctype="multipart/form-data">
                                    <div class="mb-3">
                                        <input type="file" name="sql_file" class="form-control form-control-sm" accept=".sql" required>
                                    </div>
                                    <button type="submit" class="btn btn-danger w-100" onclick="return confirm('Apakah Anda yakin ingin menimpa database saat ini dengan file cadangan ini?')">
                                        <i class="bi bi-arrow-counterclockwise me-1"></i>Pulihkan Database Sekarang
                                    </button>
                                </form>
                            </div>
                        </div>
                    </div>

                    <!-- Panel 3: Database Update / Schema Migrator -->
                    <div class="col-md-12">
                        <div class="card border border-warning shadow-none">
                            <div class="card-header bg-warning-subtle py-2">
                                <h6 class="card-title fw-bold mb-0 text-dark"><i class="bi bi-wrench-adjustable me-1"></i>3. Database Update (Sinkronisasi Skema 42 Tabel)</h6>
                            </div>
                            <div class="card-body d-flex align-items-center justify-content-between flex-wrap gap-3">
                                <div>
                                    <p class="small text-muted mb-0">
                                        Jika aplikasi mengalami penambahan modul baru atau ada tabel yang belum terbentuk, jalankan utilitas ini untuk memverifikasi dan membangun seluruh 42 tabel sesuai spesifikasi PRD secara otomatis tanpa menghapus data yang sudah ada.
                                    </p>
                                </div>
                                <div>
                                    <form action="<?= base_url('/settings/database/update') ?>" method="POST" class="m-0">
                                        <button type="submit" class="btn btn-warning px-4 fw-bold">
                                            <i class="bi bi-gear-wide-connected me-1"></i>Periksa & Update Skema
                                        </button>
                                    </form>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Panel 4: Sinkronisasi GitHub (Auto Push & Auto Update) -->
                    <div class="col-md-12">
                        <div class="card border border-info shadow-none">
                            <div class="card-header bg-info-subtle py-2 d-flex justify-content-between align-items-center">
                                <h6 class="card-title fw-bold mb-0 text-dark"><i class="bi bi-github me-1"></i>4. Sinkronisasi GitHub (Auto Push & Auto Update)</h6>
                                <span class="badge bg-primary">v1.5.3</span>
                            </div>
                            <div class="card-body">
                                <div class="row g-4">
                                    <!-- Sisi Kiri: Upload ke GitHub (Auto Push) -->
                                    <div class="col-md-6 border-end">
                                        <h6 class="fw-bold text-primary mb-2">
                                            <i class="bi bi-cloud-arrow-up me-1"></i>A. Upload Kode ke GitHub (Auto Push Windows)
                                        </h6>
                                        <p class="small text-muted mb-2">
                                            Telah disediakan file batch otomatis di folder utama proyek untuk mengunggah semua perubahan kode ke GitHub dengan 1 kali klik:
                                        </p>
                                        <div class="p-2 bg-light rounded border small mb-3">
                                            <i class="bi bi-file-earmark-code text-primary me-1"></i>
                                            <strong>File Eksekusi:</strong> <code>autopushgithub.bat</code> (di direktori utama).
                                        </div>
                                        <div class="small text-secondary mb-3">
                                            <strong>Cara Menggunakan:</strong>
                                            <ol class="mb-0 ps-3 mt-1">
                                                <li>Buka File Explorer di komputer Anda, lalu masuk ke folder proyek.</li>
                                                <li>Klik ganda (double-click) file <code>autopushgithub.bat</code>.</li>
                                                <li>Jendela terminal akan terbuka otomatis, menambahkan perubahan, melakukan commit, dan push ke GitHub.</li>
                                            </ol>
                                        </div>
                                        <div class="p-2 bg-white rounded border small font-monospace">
                                            <strong>Commit Terakhir:</strong> <?= htmlspecialchars($gitCommit, ENT_QUOTES, 'UTF-8') ?>
                                        </div>
                                    </div>

                                    <!-- Sisi Kanan: Pembaruan di Server (Auto Update) -->
                                    <div class="col-md-6 ps-md-4">
                                        <h6 class="fw-bold text-success mb-2">
                                            <i class="bi bi-arrow-repeat me-1"></i>B. Pembaruan Aplikasi di Server (Auto Update)
                                        </h6>
                                        <div class="mb-2">
                                            <span class="badge bg-success-subtle text-success border border-success px-2 py-1 small">
                                                <i class="bi bi-shield-check me-1"></i>Backup Pra-Update Otomatis Aktif
                                            </span>
                                        </div>
                                        <div class="small text-muted mb-3">
                                            <strong>Pengaturan GitHub Webhook:</strong>
                                            <ul class="mb-0 ps-3 mt-1">
                                                <li>Payload URL: <code><?= htmlspecialchars($webhookUrl, ENT_QUOTES, 'UTF-8') ?></code></li>
                                                <li>Content type: <code>application/json</code></li>
                                                <li>Secret: <code><?= htmlspecialchars($webhookSecret, ENT_QUOTES, 'UTF-8') ?></code></li>
                                                <li>Events: <em>Just the push event</em></li>
                                            </ul>
                                        </div>
                                        <form action="<?= base_url('/settings/app/update') ?>" method="POST" onsubmit="return confirm('Pembaruan aplikasi akan menarik kode terbaru dari GitHub dan merestart worker. Lanjutkan?');">
                                            <button type="submit" class="btn btn-outline-primary w-100 fw-bold py-2">
                                                <i class="bi bi-cloud-download me-1"></i>Jalankan Auto-Update GitHub Sekarang
                                            </button>
                                        </form>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                </div>
            </div>
        </div>
    </div>
</div>
