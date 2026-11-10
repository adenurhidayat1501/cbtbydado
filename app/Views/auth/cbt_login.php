<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= escape_html($title ?? 'Login Ujian Siswa — DADO CBT') ?></title>
    <link rel="icon" type="image/png" href="<?= base_url('/assets/img/logo.png') ?>">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css">
    <style>
        body {
            background-color: #0f172a;
            background-image: radial-gradient(#1e293b 1px, transparent 1px);
            background-size: 24px 24px;
            min-height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
            font-family: system-ui, -apple-system, sans-serif;
            padding: 1.5rem;
        }
        .cbt-container {
            max-width: 460px;
            width: 100%;
        }
        .cbt-card {
            background: #ffffff;
            border-radius: 1rem;
            box-shadow: 0 25px 50px -12px rgba(0, 0, 0, 0.5);
            overflow: hidden;
            border: 1px solid rgba(255, 255, 255, 0.1);
        }
        .cbt-header {
            background: linear-gradient(135deg, #1e3a8a, #0284c7);
            color: #ffffff;
            padding: 2.25rem 2rem 1.75rem;
            text-align: center;
        }
        .cbt-icon {
            width: 64px;
            height: 64px;
            background: rgba(255, 255, 255, 0.15);
            backdrop-filter: blur(8px);
            border: 1px solid rgba(255, 255, 255, 0.25);
            color: #ffffff;
            border-radius: 1rem;
            display: inline-flex;
            align-items: center;
            justify-content: center;
            font-size: 2rem;
            margin-bottom: 1rem;
        }
        .cbt-body {
            padding: 2rem;
        }
    </style>
</head>
<body>
    <div class="cbt-container">
        <div class="cbt-card">
            <div class="cbt-header">
                <div class="mb-3 text-center">
                    <img src="<?= base_url('/assets/img/logo.png') ?>" alt="Logo" style="height: 80px; width: auto; object-fit: contain;">
                </div>
                <h4 class="fw-bold mb-1">ASESMEN BERBASIS KOMPUTER</h4>
                <p class="text-white-50 small mb-1">SMP NEGERI SATU ATAP 2 CIPATUJAH</p>
                <p class="mb-0 opacity-75 small">Gerbang Ujian CBT — DADO CBT Standar AKM</p>
            </div>

            <div class="cbt-body">
                <?php if (!empty($error)): ?>
                    <div class="alert alert-danger py-2 small d-flex align-items-center mb-3">
                        <i class="bi bi-exclamation-triangle-fill me-2 fs-6 flex-shrink-0"></i>
                        <div><?= escape_html($error) ?></div>
                    </div>
                <?php endif; ?>

                <?php if (!empty($success)): ?>
                    <div class="alert alert-success py-2 small d-flex align-items-center mb-3">
                        <i class="bi bi-check-circle-fill me-2 fs-6 flex-shrink-0"></i>
                        <div><?= escape_html($success) ?></div>
                    </div>
                <?php endif; ?>

                <div class="alert alert-light border small text-muted mb-3 py-2">
                    <i class="bi bi-shield-exclamation text-primary me-1"></i>
                    <strong>Pemberitahuan Sistem:</strong> Akun ujian dilindungi dengan <em>Single-Device Session Lock</em>. Anda tidak dapat login di dua perangkat atau browser secara bersamaan.
                </div>

                <form action="<?= base_url('/cbt/login') ?>" method="POST">
                    <div class="mb-3">
                        <label class="form-label small fw-semibold text-secondary">Nomor Peserta / NIS / Username</label>
                        <div class="input-group">
                            <span class="input-group-text bg-light"><i class="bi bi-person-vcard"></i></span>
                            <input type="text" name="identifier" class="form-control" placeholder="Contoh: 2025001" required autofocus>
                        </div>
                    </div>

                    <div class="mb-4">
                        <label class="form-label small fw-semibold text-secondary">Password Peserta</label>
                        <div class="input-group">
                            <span class="input-group-text bg-light"><i class="bi bi-lock"></i></span>
                            <input type="password" name="password" class="form-control" placeholder="Masukkan password Anda" required>
                        </div>
                    </div>

                    <button type="submit" class="btn btn-primary w-100 py-2 fw-semibold mb-3">
                        <i class="bi bi-box-arrow-in-right me-1"></i> Masuk Ruang Ujian Siswa
                    </button>
                </form>

                <div class="text-center pt-2 border-top">
                    <span class="text-muted small">Guru atau Administrator?</span>
                    <a href="<?= base_url('/login') ?>" class="text-decoration-none small fw-semibold text-secondary ms-1">
                        Login Petugas &rarr;
                    </a>
                </div>
            </div>
        </div>
    </div>
</body>
</html>

