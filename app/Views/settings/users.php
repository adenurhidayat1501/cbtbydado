<div class="d-flex flex-wrap justify-content-between align-items-center mb-4">
    <div>
        <h4 class="fw-bold mb-1 text-dark">Manajemen Pengguna (User Management)</h4>
        <p class="text-muted small mb-0">Kelola kredensial akses seluruh pengguna sistem (Admin, Guru, dan Siswa).</p>
    </div>
    <button class="btn btn-primary btn-sm d-flex align-items-center gap-1" data-bs-toggle="modal" data-bs-target="#addAdminModal">
        <i class="bi bi-person-plus"></i> Tambah Administrator
    </button>
</div>

<!-- Filter Role & Status -->
<div class="card border-0 shadow-sm rounded-3 mb-4">
    <div class="card-body py-3">
        <form method="GET" action="<?= base_url('/settings/users') ?>" class="row g-2 align-items-center">
            <div class="col-md-3">
                <select name="role" class="form-select form-select-sm" onchange="this.form.submit()">
                    <option value="">-- Semua Role Akun --</option>
                    <option value="admin" <?= $roleFilter === 'admin' ? 'selected' : '' ?>>Administrator</option>
                    <option value="guru" <?= $roleFilter === 'guru' ? 'selected' : '' ?>>Pendidik (Guru)</option>
                    <option value="siswa" <?= $roleFilter === 'siswa' ? 'selected' : '' ?>>Peserta Didik (Siswa)</option>
                </select>
            </div>
            <div class="col-md-3">
                <select name="status" class="form-select form-select-sm" onchange="this.form.submit()">
                    <option value="">-- Semua Status --</option>
                    <option value="1" <?= $statusFilter === '1' ? 'selected' : '' ?>>Hanya Aktif</option>
                    <option value="0" <?= $statusFilter === '0' ? 'selected' : '' ?>>Hanya Nonaktif</option>
                </select>
            </div>
            <div class="col-auto">
                <a href="<?= base_url('/settings/users') ?>" class="btn btn-outline-secondary btn-sm">Reset Filter</a>
            </div>
        </form>
    </div>
</div>

<div class="card border-0 shadow-sm rounded-3">
    <div class="card-header bg-white border-bottom py-3 d-flex justify-content-between align-items-center">
        <h6 class="fw-bold mb-0 text-dark"><i class="bi bi-person-gear text-primary me-2"></i> Daftar Akun Pengguna</h6>
        <span class="badge bg-primary-subtle text-primary border"><?= count($users) ?> Akun Terdaftar</span>
    </div>
    <div class="card-body p-0">
        <div class="table-responsive">
            <table class="table table-hover align-middle mb-0 datatable">
                <thead class="table-light">
                    <tr>
                        <th style="width: 60px;">No</th>
                        <th>Username (ID Login)</th>
                        <th>Nama Pengguna</th>
                        <th>Hak Akses (Role)</th>
                        <th>Status</th>
                        <th>Sesi Perangkat</th>
                        <th class="text-end" style="width: 220px;">Aksi</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($users as $idx => $u): ?>
                        <tr>
                            <td><?= $idx + 1 ?></td>
                            <td>
                                <span class="fw-bold font-monospace text-dark"><?= escape_html($u['username']) ?></span>
                                <?php if (!empty($u['email'])): ?>
                                    <small class="text-muted d-block"><?= escape_html($u['email']) ?></small>
                                <?php endif; ?>
                            </td>
                            <td class="fw-semibold text-dark"><?= escape_html($u['display_name'] ?? '-') ?></td>
                            <td>
                                <?php if ($u['role'] === 'admin'): ?>
                                    <span class="badge bg-danger"><i class="bi bi-shield-lock me-1"></i> ADMIN</span>
                                <?php elseif ($u['role'] === 'guru'): ?>
                                    <span class="badge bg-primary"><i class="bi bi-person-badge me-1"></i> GURU</span>
                                <?php else: ?>
                                    <span class="badge bg-success"><i class="bi bi-mortarboard me-1"></i> SISWA</span>
                                <?php endif; ?>
                            </td>
                            <td>
                                <?php if ((int)$u['active'] === 1): ?>
                                    <span class="badge bg-success">Aktif</span>
                                <?php else: ?>
                                    <span class="badge bg-secondary">Nonaktif</span>
                                <?php endif; ?>
                            </td>
                            <td>
                                <?php if (!empty($u['session_token'])): ?>
                                    <span class="badge bg-info-subtle text-info border font-monospace" style="font-size: 0.72rem;">
                                        <i class="bi bi-dot"></i> Terhubung
                                    </span>
                                <?php else: ?>
                                    <span class="badge bg-light text-muted border font-monospace" style="font-size: 0.72rem;">Offline</span>
                                <?php endif; ?>
                            </td>
                            <td class="text-end">
                                <a href="<?= base_url('/settings/users/reset/' . $u['id']) ?>" class="btn btn-outline-warning btn-sm py-1 px-2" onclick="return confirm('Reset password akun <?= escape_html($u['username']) ?> ke default?')" title="Reset Password ke Default">
                                    <i class="bi bi-key"></i> Reset
                                </a>
                                <a href="<?= base_url('/settings/users/toggle/' . $u['id']) ?>" class="btn btn-outline-secondary btn-sm py-1 px-2 ms-1" title="Aktif/Nonaktif">
                                    <i class="bi <?= (int)$u['active'] === 1 ? 'bi-toggle-on text-success' : 'bi-toggle-off text-muted' ?>"></i>
                                </a>
                                <?php if ((int)$u['id'] !== (int)auth_id()): ?>
                                    <a href="<?= base_url('/settings/users/delete/' . $u['id']) ?>" class="btn btn-outline-danger btn-sm py-1 px-2 ms-1" onclick="return confirm('Hapus akun ini secara permanen?')" title="Hapus">
                                        <i class="bi bi-trash"></i>
                                    </a>
                                <?php endif; ?>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>

<!-- Modal Tambah Admin -->
<div class="modal fade" id="addAdminModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content border-0 shadow">
            <div class="modal-header bg-dark text-white">
                <h6 class="modal-title fw-bold"><i class="bi bi-shield-plus me-1"></i> Tambah Akun Administrator</h6>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
            </div>
            <form action="<?= base_url('/settings/users/store-admin') ?>" method="POST">
                <div class="modal-body">
                    <div class="mb-3">
                        <label class="form-label small fw-semibold">Username Login</label>
                        <input type="text" name="username" class="form-control" placeholder="Contoh: admin_proktor" required>
                    </div>
                    <div class="mb-3">
                        <label class="form-label small fw-semibold">Email (Opsional)</label>
                        <input type="email" name="email" class="form-control" placeholder="proktor@sekolah.sch.id">
                    </div>
                    <div class="mb-3">
                        <label class="form-label small fw-semibold">Password Awal (Minimal 6 Karakter)</label>
                        <input type="password" name="password" class="form-control" minlength="6" placeholder="Masukkan password" required>
                    </div>
                </div>
                <div class="modal-footer bg-light py-2">
                    <button type="button" class="btn btn-sm btn-secondary" data-bs-dismiss="modal">Batal</button>
                    <button type="submit" class="btn btn-sm btn-dark">Buat Administrator</button>
                </div>
            </form>
        </div>
    </div>
</div>

