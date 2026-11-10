<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= escape_html($title ?? 'DADO CBT') ?></title>
    <link rel="icon" type="image/png" href="<?= base_url('/assets/img/logo.png') ?>">

    <!-- Bootstrap 5 & Bootstrap Icons -->
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css">

    <!-- DataTables BS5 -->
    <link rel="stylesheet" href="https://cdn.datatables.net/1.13.7/css/dataTables.bootstrap5.min.css">

    <!-- Summernote BS5 -->
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/summernote@0.8.20/dist/summernote-bs5.min.css">

    <!-- KaTeX Math Rendering CSS -->
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/katex@0.16.10/dist/katex.min.css">

    <!-- DADO CBT Custom Assets: Arabic Calibri & Main Theme -->
    <link rel="stylesheet" href="<?= asset('css/arabic-calibri.css') ?>">
    <link rel="stylesheet" href="<?= asset('css/main.css') ?>">
</head>
<body>
    <div id="wrapper">
        <!-- Sidebar Navigation -->
        <?php require __DIR__ . '/sidebar.php'; ?>

        <!-- Content Area -->
        <div id="content-wrapper">
            <!-- Topbar Header -->
            <?php require __DIR__ . '/topbar.php'; ?>

            <!-- Running Text Announcement Ticker -->
            <?php
            $dbRunning = \App\Core\Database::getInstance()->getPdo();
            try {
                $role = auth_role() ?? 'siswa';
                $tujuanFilter = $role === 'admin' ? "('admin','semua')" : ($role === 'guru' ? "('guru','semua')" : "('siswa','semua')");
                $activeTicker = $dbRunning->query("SELECT text FROM running_text WHERE active = 1 AND tujuan IN {$tujuanFilter} ORDER BY id_text DESC LIMIT 1")->fetchColumn();
            } catch (\Throwable $e) {
                $activeTicker = null;
            }
            ?>
            <?php if (!empty($activeTicker)): ?>
            <div class="ticker-wrap">
                <div class="ticker-content">
                    <span class="ticker-icon"><i class="bi bi-megaphone-fill"></i></span>
                    <marquee class="ticker-marquee" behavior="scroll" direction="left" scrollamount="6">
                        <?= escape_html($activeTicker) ?>
                    </marquee>
                </div>
            </div>
            <?php endif; ?>

            <!-- Main Page Content -->
            <main id="main-content">
                <!-- Flash Notification Messages -->
                <?php if (has_flash('success')): ?>
                    <div class="alert alert-success alert-dismissible fade show d-flex align-items-center mb-4" role="alert">
                        <i class="bi bi-check-circle-fill fs-5 me-2"></i>
                        <div><?= escape_html(get_flash('success')) ?></div>
                        <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                    </div>
                <?php endif; ?>

                <?php if (has_flash('error')): ?>
                    <div class="alert alert-danger alert-dismissible fade show d-flex align-items-center mb-4" role="alert">
                        <i class="bi bi-exclamation-triangle-fill fs-5 me-2"></i>
                        <div><?= escape_html(get_flash('error')) ?></div>
                        <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                    </div>
                <?php endif; ?>

                <?php if (has_flash('warning')): ?>
                    <div class="alert alert-warning alert-dismissible fade show d-flex align-items-center mb-4" role="alert">
                        <i class="bi bi-exclamation-circle-fill fs-5 me-2"></i>
                        <div><?= escape_html(get_flash('warning')) ?></div>
                        <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                    </div>
                <?php endif; ?>

                <?= $content ?>
            </main>

            <!-- Footer & Modals -->
            <?php require __DIR__ . '/footer.php'; ?>
        </div>
    </div>

    <!-- Scripts: jQuery, Bootstrap 5 Bundle, DataTables, Summernote, KaTeX & Main JS -->
    <script src="https://code.jquery.com/jquery-3.7.1.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
    <script src="https://cdn.datatables.net/1.13.7/js/jquery.dataTables.min.js"></script>
    <script src="https://cdn.datatables.net/1.13.7/js/dataTables.bootstrap5.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/summernote@0.8.20/dist/summernote-bs5.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/katex@0.16.10/dist/katex.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/katex@0.16.10/dist/contrib/auto-render.min.js"></script>
    <script src="<?= asset('js/main.js') ?>"></script>
</body>
</html>

