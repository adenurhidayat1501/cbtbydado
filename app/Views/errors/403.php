<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= escape_html($title ?? '403 - Akses Ditolak') ?></title>
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css">
</head>
<body class="bg-light d-flex align-items-center justify-content-center min-vh-100 text-center p-3">
    <div class="card border-0 shadow-sm p-4 p-md-5" style="max-width: 480px;">
        <div class="text-danger mb-3">
            <i class="bi bi-shield-x display-1"></i>
        </div>
        <h3 class="fw-bold text-dark mb-2">403 — Akses Ditolak</h3>
        <p class="text-muted small mb-4">
            <?= escape_html($message ?? 'Anda tidak memiliki hak akses yang cukup untuk membuka halaman atau modul ini.') ?>
        </p>
        <a href="<?= base_url('/dashboard') ?>" class="btn btn-primary btn-sm px-4">
            <i class="bi bi-arrow-left me-1"></i> Kembali ke Beranda
        </a>
    </div>
</body>
</html>

