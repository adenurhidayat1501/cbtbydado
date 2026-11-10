<div class="container-fluid py-3">
    <div class="d-flex justify-content-between align-items-center mb-3">
        <div>
            <h4 class="mb-0 fw-bold"><i class="bi bi-archive text-primary me-2"></i>Bank Soal (5 Tipe AKM)</h4>
            <small class="text-muted">Kelola paket bank soal PG, PG Kompleks, Menjodohkan, Isian, dan Esai</small>
        </div>
        <div>
            <a href="<?= base_url('/cbt/bank-soal/create') ?>" class="btn btn-primary btn-sm">
                <i class="bi bi-plus-circle me-1"></i> Buat Bank Soal Baru
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

    <!-- Filter Card -->
    <div class="card shadow-sm border-0 mb-3">
        <div class="card-body py-2">
            <form action="<?= base_url('/cbt/bank-soal') ?>" method="GET" class="row g-2 align-items-center">
                <div class="col-md-3">
                    <select name="mapel_id" class="form-select form-select-sm" onchange="this.form.submit()">
                        <option value="">-- Semua Mata Pelajaran --</option>
                        <?php foreach ($mapelList as $m): ?>
                            <option value="<?= $m['id_mapel'] ?>" <?= (int)$m['id_mapel'] === (int)$selectedMapelId ? 'selected' : '' ?>>
                                <?= escape_html($m['nama_mapel']) ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                </div>

                <div class="col-md-3">
                    <select name="level" class="form-select form-select-sm" onchange="this.form.submit()">
                        <option value="">-- Semua Tingkat/Level --</option>
                        <?php for ($lv = 1; $lv <= 12; $lv++): ?>
                            <option value="<?= $lv ?>" <?= $lv === (int)$selectedLevel ? 'selected' : '' ?>>Tingkat <?= $lv ?></option>
                        <?php endfor; ?>
                    </select>
                </div>

                <?php if ($role === 'admin' && !empty($guruList)): ?>
                <div class="col-md-4">
                    <select name="guru_id" class="form-select form-select-sm" onchange="this.form.submit()">
                        <option value="">-- Semua Guru Pembuat --</option>
                        <?php foreach ($guruList as $g): ?>
                            <option value="<?= $g['id_guru'] ?>" <?= (int)$g['id_guru'] === (int)$selectedGuruId ? 'selected' : '' ?>>
                                <?= escape_html($g['nama_guru']) ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                </div>
                <?php endif; ?>
            </form>
        </div>
    </div>

    <!-- Bank Soal Table -->
    <div class="card shadow-sm border-0">
        <div class="card-body p-0">
            <div class="table-responsive">
                <table class="table table-hover table-striped align-middle mb-0 datatable" style="font-size: 13px;">
                    <thead class="table-light">
                        <tr>
                            <th style="width: 40px;" class="text-center">No</th>
                            <th style="width: 140px;">Kode Bank</th>
                            <th>Mata Pelajaran</th>
                            <th style="width: 80px;" class="text-center">Level</th>
                            <th>Guru Pengampu</th>
                            <th class="text-center">Komposisi Soal 5 AKM</th>
                            <th style="width: 90px;" class="text-center">Opsi PG</th>
                            <th style="width: 90px;" class="text-center">Status</th>
                            <th style="width: 160px;" class="text-center">Aksi</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if (empty($banks)): ?>
                            <tr>
                                <td colspan="9" class="text-center py-4 text-muted">
                                    <i class="bi bi-inbox fs-2 d-block text-secondary mb-2"></i>
                                    Belum ada bank soal yang dibuat.
                                </td>
                            </tr>
                        <?php else: ?>
                            <?php $no = 1; foreach ($banks as $b): ?>
                                <tr>
                                    <td class="text-center"><?= $no++ ?></td>
                                    <td>
                                        <div class="fw-bold font-monospace text-primary"><?= escape_html($b['bank_kode']) ?></div>
                                        <small class="text-muted"><?= escape_html($b['nama_jenis'] ?? 'Ujian') ?></small>
                                    </td>
                                    <td>
                                        <div class="fw-semibold text-dark"><?= escape_html($b['nama_mapel']) ?></div>
                                    </td>
                                    <td class="text-center">
                                        <span class="badge bg-light text-dark border">Kls <?= $b['bank_level'] ?></span>
                                    </td>
                                    <td>
                                        <small><?= escape_html($b['nama_guru']) ?></small>
                                    </td>
                                    <td class="text-center">
                                        <div class="d-flex justify-content-center gap-1 flex-wrap" style="font-size: 11px;">
                                            <span class="badge bg-primary" title="Pilihan Ganda">PG: <?= $b['jml_soal'] ?> (T:<?= $b['tampil_pg'] ?>)</span>
                                            <span class="badge bg-info text-white" title="PG Kompleks">PGK: <?= $b['jml_kompleks'] ?> (T:<?= $b['tampil_kompleks'] ?>)</span>
                                            <span class="badge bg-warning text-dark" title="Menjodohkan">Jodoh: <?= $b['jml_jodohkan'] ?> (T:<?= $b['tampil_jodohkan'] ?>)</span>
                                            <span class="badge bg-secondary" title="Isian Singkat">Isian: <?= $b['jml_isian'] ?> (T:<?= $b['tampil_isian'] ?>)</span>
                                            <span class="badge bg-dark" title="Essai / Uraian">Esai: <?= $b['jml_esai'] ?> (T:<?= $b['tampil_esai'] ?>)</span>
                                        </div>
                                    </td>
                                    <td class="text-center">
                                        <span class="badge bg-light text-secondary border font-monospace">
                                            <?= $b['opsi'] ?> Opsi (<?= chr(64 + $b['opsi']) ?>)
                                        </span>
                                    </td>
                                    <td class="text-center">
                                        <?php if ((int)$b['status'] === 1): ?>
                                            <span class="badge bg-success-subtle text-success border border-success-subtle">Aktif</span>
                                        <?php else: ?>
                                            <span class="badge bg-danger-subtle text-danger border border-danger-subtle">Nonaktif</span>
                                        <?php endif; ?>
                                    </td>
                                    <td class="text-center">
                                        <div class="btn-group btn-group-sm">
                                            <a href="<?= base_url('/cbt/bank-soal/detail/' . $b['id_bank']) ?>" class="btn btn-outline-info" title="Kelola Butir Soal">
                                                <i class="bi bi-eye"></i>
                                            </a>
                                            <a href="<?= base_url('/cbt/bank-soal/edit/' . $b['id_bank']) ?>" class="btn btn-outline-primary" title="Pengaturan Bank Soal">
                                                <i class="bi bi-gear"></i>
                                            </a>
                                            <a href="<?= base_url('/cbt/bank-soal/delete/' . $b['id_bank']) ?>" class="btn btn-outline-danger" title="Hapus"
                                               onclick="return confirm('Yakin ingin menghapus bank soal ini? Seluruh butir soal di dalamnya akan terhapus.');">
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
</div>
