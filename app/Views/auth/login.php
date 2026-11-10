<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= escape_html($title ?? 'Login — DADO CBT') ?></title>
    <link rel="icon" type="image/png" href="<?= base_url('/assets/img/logo.png') ?>">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css">
    <style>
        body {
            background: linear-gradient(135deg, #0f172a 0%, #1e3a8a 50%, #0369a1 100%);
            min-height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
            font-family: system-ui, -apple-system, sans-serif;
            padding: 1rem;
        }
        .login-card {
            max-width: 420px;
            width: 100%;
            background: #ffffff;
            border-radius: 1rem;
            box-shadow: 0 20px 25px -5px rgba(0, 0, 0, 0.2), 0 8px 10px -6px rgba(0, 0, 0, 0.2);
            overflow: hidden;
        }
        .login-header {
            background: #f8fafc;
            padding: 2rem 2rem 1.5rem;
            text-align: center;
            border-bottom: 1px solid #e2e8f0;
        }
        .brand-icon {
            width: 60px;
            height: 60px;
            background: #2563eb;
            color: #ffffff;
            border-radius: 50%;
            display: inline-flex;
            align-items: center;
            justify-content: center;
            font-size: 2rem;
            margin-bottom: 1rem;
            box-shadow: 0 4px 6px -1px rgba(37, 99, 235, 0.3);
        }
        .login-body {
            padding: 2rem;
        }
    </style>
</head>
<body>
    <div class="login-card">
        <div class="login-header">
            <div class="mb-3 text-center">
                <img src="<?= base_url('/assets/img/logo.png') ?>" alt="Logo" style="height: 75px; width: auto; object-fit: contain;">
            </div>
            <h4 class="fw-bold text-dark mb-1">DADO CBT</h4>
            <p class="text-primary fw-semibold small mb-1">SMP NEGERI SATU ATAP 2 CIPATUJAH</p>
            <p class="text-muted small mb-0">Portal Login Staf & Tenaga Pendidik</p>
        </div>

        <div class="login-body">
            <?php if (!empty($error)): ?>
                <div class="alert alert-danger py-2 small d-flex align-items-center mb-3">
                    <i class="bi bi-exclamation-circle-fill me-2 fs-6"></i>
                    <div><?= escape_html($error) ?></div>
                </div>
            <?php endif; ?>

            <?php if (!empty($success)): ?>
                <div class="alert alert-success py-2 small d-flex align-items-center mb-3">
                    <i class="bi bi-check-circle-fill me-2 fs-6"></i>
                    <div><?= escape_html($success) ?></div>
                </div>
            <?php endif; ?>

            <form action="<?= base_url('/login') ?>" method="POST">
                <div class="mb-3">
                    <label class="form-label small fw-semibold text-secondary">Username</label>
                    <div class="input-group">
                        <span class="input-group-text bg-light text-muted"><i class="bi bi-person"></i></span>
                        <input type="text" name="username" class="form-control" placeholder="Masukkan username" required autofocus>
                    </div>
                </div>

                <div class="mb-3">
                    <label class="form-label small fw-semibold text-secondary">Password</label>
                    <div class="input-group">
                        <span class="input-group-text bg-light text-muted"><i class="bi bi-key"></i></span>
                        <input type="password" name="password" class="form-control" placeholder="Masukkan password" required>
                    </div>
                </div>

                <button type="submit" class="btn btn-primary w-100 py-2 fw-semibold mb-3">
                    <i class="bi bi-box-arrow-in-right me-1"></i> Masuk ke Dashboard
                </button>
            </form>

            <div class="text-center pt-2 border-top">
                <span class="text-muted small">Peserta Ujian?</span>
                <a href="<?= base_url('/cbt/login') ?>" class="text-primary fw-semibold small text-decoration-none ms-1">
                    Masuk ke Ruang CBT Siswa &rarr;
                </a>
            </div>
        </div>
    </div>
</body>
</html>

