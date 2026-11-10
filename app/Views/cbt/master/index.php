<div class="container-fluid py-3">
    <div class="d-flex justify-content-between align-items-center mb-3">
        <div>
            <h4 class="mb-0 fw-bold"><i class="bi bi-sliders text-primary me-2"></i>Master CBT & Ruang Ujian</h4>
            <small class="text-muted">Pengaturan Jenis Ujian, Ruang Laboratorium, dan Sesi Pelaksanaan</small>
        </div>
        <div class="d-flex gap-2">
            <a href="<?= base_url('/cbt/master/ruang-sesi-siswa') ?>" class="btn btn-primary btn-sm">
                <i class="bi bi-people me-1"></i> Atur Ruang & Sesi Siswa
            </a>
            <a href="<?= base_url('/cbt/master/nomor-peserta') ?>" class="btn btn-warning btn-sm text-dark">
                <i class="bi bi-card-heading me-1"></i> Nomor Peserta Ujian
            </a>
        </div>
    </div>

    <?php if (has_flash('success')): ?>
        <div class="alert alert-success alert-dismissible fade show" role="alert">
            <i class="bi bi-check-circle me-1"></i> <?= escape_html(get_flash('success')) ?>
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
    <?php endif; ?>
    <?php if (has_flash('error')): ?>
        <div class="alert alert-danger alert-dismissible fade show" role="alert">
            <i class="bi bi-exclamation-triangle me-1"></i> <?= escape_html(get_flash('error')) ?>
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
    <?php endif; ?>

    <!-- Sub-Nav Tabs -->
    <ul class="nav nav-tabs mb-3" id="masterCbtTabs" role="tablist">
        <li class="nav-item">
            <a class="nav-link <?= $activeTab === 'jenis' ? 'active fw-bold' : '' ?>" href="<?= base_url('/cbt/master?tab=jenis') ?>">
                <i class="bi bi-tag me-1"></i> Jenis Ujian
                <span class="badge bg-secondary ms-1"><?= count($jenisList) ?></span>
            </a>
        </li>
        <li class="nav-item">
            <a class="nav-link <?= $activeTab === 'ruang' ? 'active fw-bold' : '' ?>" href="<?= base_url('/cbt/master?tab=ruang') ?>">
                <i class="bi bi-door-open me-1"></i> Ruang Ujian
                <span class="badge bg-secondary ms-1"><?= count($ruangList) ?></span>
            </a>
        </li>
        <li class="nav-item">
            <a class="nav-link <?= $activeTab === 'sesi' ? 'active fw-bold' : '' ?>" href="<?= base_url('/cbt/master?tab=sesi') ?>">
                <i class="bi bi-clock me-1"></i> Sesi Ujian
                <span class="badge bg-secondary ms-1"><?= count($sesiList) ?></span>
            </a>
        </li>
    </ul>

    <!-- Tab 1: Jenis Ujian -->
    <?php if ($activeTab === 'jenis'): ?>
        <div class="card shadow-sm border-0">
            <div class="card-header bg-white py-2 d-flex justify-content-between align-items-center">
                <span class="fw-bold"><i class="bi bi-tag text-primary me-1"></i> Daftar Jenis Ujian CBT</span>
                <button type="button" class="btn btn-primary btn-sm" data-bs-toggle="modal" data-bs-target="#modalAddJenis">
                    <i class="bi bi-plus-circle me-1"></i> Tambah Jenis Ujian
                </button>
            </div>
            <div class="card-body p-0">
                <div class="table-responsive">
                    <table class="table table-hover table-striped align-middle mb-0" style="font-size: 13px;">
                        <thead class="table-light">
                            <tr>
                                <th style="width: 50px;" class="text-center">No</th>
                                <th style="width: 120px;">Kode Jenis</th>
                                <th>Nama Jenis Ujian</th>
                                <th style="width: 120px;" class="text-center">Aksi</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php $no = 1; foreach ($jenisList as $j): ?>
                                <tr>
                                    <td class="text-center"><?= $no++ ?></td>
                                    <td>
                                        <span class="badge bg-primary-subtle text-primary border border-primary-subtle font-monospace px-2 py-1">
                                            <?= escape_html($j['kode_jenis']) ?>
                                        </span>
                                    </td>
                                    <td class="fw-semibold text-dark"><?= escape_html($j['nama_jenis']) ?></td>
                                    <td class="text-center">
                                        <div class="btn-group btn-group-sm">
                                            <button type="button" class="btn btn-outline-primary" onclick="editJenis(<?= $j['id_jenis'] ?>, '<?= safe_print_str($j['nama_jenis']) ?>', '<?= safe_print_str($j['kode_jenis']) ?>')">
                                                <i class="bi bi-pencil"></i>
                                            </button>
                                            <a href="<?= base_url('/cbt/master/jenis/delete/' . $j['id_jenis']) ?>" class="btn btn-outline-danger" onclick="return confirm('Hapus jenis ujian ini?');">
                                                <i class="bi bi-trash"></i>
                                            </a>
                                        </div>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>

    <!-- Tab 2: Ruang Ujian -->
    <?php elseif ($activeTab === 'ruang'): ?>
        <div class="card shadow-sm border-0">
            <div class="card-header bg-white py-2 d-flex justify-content-between align-items-center">
                <span class="fw-bold"><i class="bi bi-door-open text-primary me-1"></i> Daftar Ruang Ujian</span>
                <button type="button" class="btn btn-primary btn-sm" data-bs-toggle="modal" data-bs-target="#modalAddRuang">
                    <i class="bi bi-plus-circle me-1"></i> Tambah Ruang
                </button>
            </div>
            <div class="card-body p-0">
                <div class="table-responsive">
                    <table class="table table-hover table-striped align-middle mb-0" style="font-size: 13px;">
                        <thead class="table-light">
                            <tr>
                                <th style="width: 50px;" class="text-center">No</th>
                                <th style="width: 140px;">Kode Ruang</th>
                                <th>Nama Ruang / Laboratorium</th>
                                <th style="width: 160px;" class="text-center">Total Siswa Terpetakan</th>
                                <th style="width: 120px;" class="text-center">Aksi</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php if (empty($ruangList)): ?>
                                <tr><td colspan="5" class="text-center py-4 text-muted">Belum ada ruang ujian.</td></tr>
                            <?php else: ?>
                                <?php $no = 1; foreach ($ruangList as $r): ?>
                                    <tr>
                                        <td class="text-center"><?= $no++ ?></td>
                                        <td>
                                            <span class="badge bg-info-subtle text-info-emphasis border border-info-subtle font-monospace px-2 py-1">
                                                <?= escape_html($r['kode_ruang']) ?>
                                            </span>
                                        </td>
                                        <td class="fw-semibold text-dark"><?= escape_html($r['nama_ruang']) ?></td>
                                        <td class="text-center">
                                            <span class="badge bg-light text-dark border">
                                                <?= (int)($r['total_siswa'] ?? 0) ?> Siswa
                                            </span>
                                        </td>
                                        <td class="text-center">
                                            <div class="btn-group btn-group-sm">
                                                <button type="button" class="btn btn-outline-primary" onclick="editRuang(<?= $r['id_ruang'] ?>, '<?= safe_print_str($r['nama_ruang']) ?>', '<?= safe_print_str($r['kode_ruang']) ?>')">
                                                    <i class="bi bi-pencil"></i>
                                                </button>
                                                <a href="<?= base_url('/cbt/master/ruang/delete/' . $r['id_ruang']) ?>" class="btn btn-outline-danger" onclick="return confirm('Hapus ruang ujian ini?');">
                                                    <i class="bi bi-trash"></i>
                                                </a>
                                            </div>
                                        </td>
                                    </tr>
                                <?php endforeach; ?>
                            <?php endif; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>

    <!-- Tab 3: Sesi Ujian -->
    <?php elseif ($activeTab === 'sesi'): ?>
        <div class="card shadow-sm border-0">
            <div class="card-header bg-white py-2 d-flex justify-content-between align-items-center">
                <span class="fw-bold"><i class="bi bi-clock text-primary me-1"></i> Daftar Sesi Ujian</span>
                <button type="button" class="btn btn-primary btn-sm" data-bs-toggle="modal" data-bs-target="#modalAddSesi">
                    <i class="bi bi-plus-circle me-1"></i> Tambah Sesi
                </button>
            </div>
            <div class="card-body p-0">
                <div class="table-responsive">
                    <table class="table table-hover table-striped align-middle mb-0" style="font-size: 13px;">
                        <thead class="table-light">
                            <tr>
                                <th style="width: 50px;" class="text-center">No</th>
                                <th style="width: 140px;">Kode Sesi</th>
                                <th>Nama Sesi</th>
                                <th style="width: 160px;" class="text-center">Rentang Waktu</th>
                                <th style="width: 160px;" class="text-center">Total Siswa Terpetakan</th>
                                <th style="width: 120px;" class="text-center">Aksi</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php if (empty($sesiList)): ?>
                                <tr><td colspan="6" class="text-center py-4 text-muted">Belum ada sesi ujian.</td></tr>
                            <?php else: ?>
                                <?php $no = 1; foreach ($sesiList as $s): ?>
                                    <tr>
                                        <td class="text-center"><?= $no++ ?></td>
                                        <td>
                                            <span class="badge bg-warning-subtle text-warning-emphasis border border-warning-subtle font-monospace px-2 py-1">
                                                <?= escape_html($s['kode_sesi']) ?>
                                            </span>
                                        </td>
                                        <td class="fw-semibold text-dark"><?= escape_html($s['nama_sesi']) ?></td>
                                        <td class="text-center font-monospace">
                                            <?= substr($s['waktu_mulai'], 0, 5) ?> - <?= substr($s['waktu_akhir'], 0, 5) ?> WIB
                                        </td>
                                        <td class="text-center">
                                            <span class="badge bg-light text-dark border">
                                                <?= (int)($s['total_siswa'] ?? 0) ?> Siswa
                                            </span>
                                        </td>
                                        <td class="text-center">
                                            <div class="btn-group btn-group-sm">
                                                <button type="button" class="btn btn-outline-primary" onclick="editSesi(<?= $s['id_sesi'] ?>, '<?= safe_print_str($s['nama_sesi']) ?>', '<?= safe_print_str($s['kode_sesi']) ?>', '<?= substr($s['waktu_mulai'], 0, 5) ?>', '<?= substr($s['waktu_akhir'], 0, 5) ?>')">
                                                    <i class="bi bi-pencil"></i>
                                                </button>
                                                <a href="<?= base_url('/cbt/master/sesi/delete/' . $s['id_sesi']) ?>" class="btn btn-outline-danger" onclick="return confirm('Hapus sesi ujian ini?');">
                                                    <i class="bi bi-trash"></i>
                                                </a>
                                            </div>
                                        </td>
                                    </tr>
                                <?php endforeach; ?>
                            <?php endif; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    <?php endif; ?>
</div>

<!-- Modal Tambah / Edit Jenis -->
<div class="modal fade" id="modalAddJenis" tabindex="-1">
    <div class="modal-dialog modal-sm modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-header py-2">
                <h6 class="modal-title fw-bold" id="titleModalJenis">Tambah Jenis Ujian</h6>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <form id="formJenis" action="<?= base_url('/cbt/master/jenis/store') ?>" method="POST">
                <div class="modal-body">
                    <div class="mb-3">
                        <label class="form-label small fw-semibold">Kode Jenis (Singkatan)</label>
                        <input type="text" name="kode_jenis" id="jenisKode" class="form-control form-control-sm text-uppercase" placeholder="PH, PTS, PAS, PAT, USBK" required>
                    </div>
                    <div class="mb-2">
                        <label class="form-label small fw-semibold">Nama Jenis Ujian</label>
                        <input type="text" name="nama_jenis" id="jenisNama" class="form-control form-control-sm" placeholder="Contoh: Penilaian Harian" required>
                    </div>
                </div>
                <div class="modal-footer py-2">
                    <button type="button" class="btn btn-light btn-sm" data-bs-dismiss="modal">Batal</button>
                    <button type="submit" class="btn btn-primary btn-sm">Simpan</button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- Modal Tambah / Edit Ruang -->
<div class="modal fade" id="modalAddRuang" tabindex="-1">
    <div class="modal-dialog modal-sm modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-header py-2">
                <h6 class="modal-title fw-bold" id="titleModalRuang">Tambah Ruang Ujian</h6>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <form id="formRuang" action="<?= base_url('/cbt/master/ruang/store') ?>" method="POST">
                <div class="modal-body">
                    <div class="mb-3">
                        <label class="form-label small fw-semibold">Kode Ruang</label>
                        <input type="text" name="kode_ruang" id="ruangKode" class="form-control form-control-sm text-uppercase" placeholder="LAB-01, R-01" required>
                    </div>
                    <div class="mb-2">
                        <label class="form-label small fw-semibold">Nama Ruang</label>
                        <input type="text" name="nama_ruang" id="ruangNama" class="form-control form-control-sm" placeholder="Contoh: Lab Komputer 01" required>
                    </div>
                </div>
                <div class="modal-footer py-2">
                    <button type="button" class="btn btn-light btn-sm" data-bs-dismiss="modal">Batal</button>
                    <button type="submit" class="btn btn-primary btn-sm">Simpan</button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- Modal Tambah / Edit Sesi -->
<div class="modal fade" id="modalAddSesi" tabindex="-1">
    <div class="modal-dialog modal-sm modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-header py-2">
                <h6 class="modal-title fw-bold" id="titleModalSesi">Tambah Sesi Ujian</h6>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <form id="formSesi" action="<?= base_url('/cbt/master/sesi/store') ?>" method="POST">
                <div class="modal-body">
                    <div class="mb-3">
                        <label class="form-label small fw-semibold">Kode Sesi</label>
                        <input type="text" name="kode_sesi" id="sesiKode" class="form-control form-control-sm text-uppercase" placeholder="SESI-1, SESI-2" required>
                    </div>
                    <div class="mb-3">
                        <label class="form-label small fw-semibold">Nama Sesi</label>
                        <input type="text" name="nama_sesi" id="sesiNama" class="form-control form-control-sm" placeholder="Contoh: Sesi Pagi Ke-1" required>
                    </div>
                    <div class="row g-2 mb-2">
                        <div class="col-6">
                            <label class="form-label small fw-semibold">Waktu Mulai</label>
                            <input type="time" name="waktu_mulai" id="sesiMulai" class="form-control form-control-sm" value="07:30" required>
                        </div>
                        <div class="col-6">
                            <label class="form-label small fw-semibold">Waktu Akhir</label>
                            <input type="time" name="waktu_akhir" id="sesiAkhir" class="form-control form-control-sm" value="09:30" required>
                        </div>
                    </div>
                </div>
                <div class="modal-footer py-2">
                    <button type="button" class="btn btn-light btn-sm" data-bs-dismiss="modal">Batal</button>
                    <button type="submit" class="btn btn-primary btn-sm">Simpan</button>
                </div>
            </form>
        </div>
    </div>
</div>

<script>
function editJenis(id, nama, kode) {
    document.getElementById('titleModalJenis').innerText = 'Edit Jenis Ujian';
    document.getElementById('formJenis').action = '<?= base_url('/cbt/master/jenis/update/') ?>' + id;
    document.getElementById('jenisKode').value = kode;
    document.getElementById('jenisNama').value = nama;
    new bootstrap.Modal(document.getElementById('modalAddJenis')).show();
}

function editRuang(id, nama, kode) {
    document.getElementById('titleModalRuang').innerText = 'Edit Ruang Ujian';
    document.getElementById('formRuang').action = '<?= base_url('/cbt/master/ruang/update/') ?>' + id;
    document.getElementById('ruangKode').value = kode;
    document.getElementById('ruangNama').value = nama;
    new bootstrap.Modal(document.getElementById('modalAddRuang')).show();
}

function editSesi(id, nama, kode, mulai, akhir) {
    document.getElementById('titleModalSesi').innerText = 'Edit Sesi Ujian';
    document.getElementById('formSesi').action = '<?= base_url('/cbt/master/sesi/update/') ?>' + id;
    document.getElementById('sesiKode').value = kode;
    document.getElementById('sesiNama').value = nama;
    document.getElementById('sesiMulai').value = mulai;
    document.getElementById('sesiAkhir').value = akhir;
    new bootstrap.Modal(document.getElementById('modalAddSesi')).show();
}
</script>
