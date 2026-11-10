<?php
$user = auth_user();
$tp = active_tp();
$smt = active_smt();
$school = setting_sekolah();
?>

<header id="topbar">
    <div class="d-flex align-items-center gap-3">
        <button type="button" class="btn-sidebar-toggle" id="sidebarToggleBtn" title="Buka/Tutup Sidebar">
            <i class="bi bi-list"></i>
        </button>

        <div class="d-none d-md-flex align-items-center gap-2">
            <img src="<?= base_url('/assets/img/logo.png') ?>" alt="Logo" style="height: 32px; width: auto; object-fit: contain;">
            <span class="fw-bold text-dark fs-6"><?= escape_html($school['nama_sekolah'] ?? 'SMP NEGERI SATU ATAP 2 CIPATUJAH') ?></span>
        </div>
    </div>

    <div class="d-flex align-items-center gap-3">
        <!-- Saklar TP / Semester Aktif -->
        <div class="tp-smt-badge" data-bs-toggle="modal" data-bs-target="#switchTpSmtModal" title="Klik untuk mengganti Tahun Pelajaran atau Semester aktif">
            <i class="bi bi-calendar-check text-primary"></i>
            <span>TP: <?= escape_html($tp['tahun'] ?? '-') ?></span>
            <span class="text-muted">|</span>
            <span>Smt: <?= escape_html($smt['nama_smt'] ?? ($smt['smt'] ?? '-')) ?></span>
            <i class="bi bi-chevron-down ms-1" style="font-size: 0.75rem;"></i>
        </div>

        <!-- User Profile Dropdown -->
        <div class="dropdown">
            <button class="btn btn-sm btn-light border dropdown-toggle d-flex align-items-center gap-2 px-2 py-1" type="button" data-bs-toggle="dropdown" aria-expanded="false">
                <div class="bg-primary text-white rounded-circle d-flex align-items-center justify-content-center" style="width: 32px; height: 32px; font-size: 0.85rem; font-weight: 600;">
                    <?php if (!empty($user['foto'])): ?>
                        <img src="<?= upload_url($user['foto']) ?>" alt="Foto" class="rounded-circle w-100 h-100 object-fit-cover">
                    <?php else: ?>
                        <?= strtoupper(substr($user['display_name'] ?? $user['username'] ?? 'U', 0, 1)) ?>
                    <?php endif; ?>
                </div>
                <div class="text-start d-none d-sm-block">
                    <div class="fw-semibold text-truncate" style="max-width: 130px; font-size: 0.82rem;"><?= escape_html($user['display_name'] ?? $user['username'] ?? 'User') ?></div>
                    <div class="text-muted text-capitalize" style="font-size: 0.72rem;"><?= escape_html($user['role'] ?? 'User') ?></div>
                </div>
            </button>
            <ul class="dropdown-menu dropdown-menu-end shadow-sm border-0 py-2" style="min-width: 200px;">
                <li class="px-3 py-2 border-bottom">
                    <div class="fw-bold text-dark text-truncate"><?= escape_html($user['display_name'] ?? 'User') ?></div>
                    <small class="text-muted">Role: <span class="badge bg-secondary"><?= strtoupper(escape_html($user['role'] ?? '')) ?></span></small>
                </li>
                <li>
                    <button class="dropdown-item py-2" type="button" data-bs-toggle="modal" data-bs-target="#changePasswordModal">
                        <i class="bi bi-key text-warning me-2"></i> Ubah Password
                    </button>
                </li>
                <li><hr class="dropdown-divider my-1"></li>
                <li>
                    <a class="dropdown-item py-2 text-danger" href="<?= base_url('/logout') ?>">
                        <i class="bi bi-box-arrow-right me-2"></i> Keluar (Logout)
                    </a>
                </li>
            </ul>
        </div>
    </div>
</header>

