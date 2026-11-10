<div class="d-flex flex-wrap justify-content-between align-items-center mb-4">
    <div>
        <h4 class="fw-bold mb-1 text-dark">Anggota Rombel: <?= escape_html($kelas['nama_kelas']) ?></h4>
        <p class="text-muted small mb-0">
            Tingkat: <strong><?= $kelas['level_id'] ?></strong> | Wali Kelas: <strong><?= escape_html($kelas['wali_kelas'] ?? 'Belum ditentukan') ?></strong> | Periode: <strong><?= escape_html($tp['tahun'] ?? '-') ?> (<?= escape_html($smt['nama_smt'] ?? '-') ?>)</strong>
        </p>
    </div>
    <div class="d-flex gap-2">
        <button class="btn btn-primary btn-sm d-flex align-items-center gap-1" data-bs-toggle="modal" data-bs-target="#tambahSiswaModal">
            <i class="bi bi-person-plus"></i> Tambah Anggota Siswa
        </button>
        <a href="<?= base_url('/master/kelas') ?>" class="btn btn-outline-secondary btn-sm d-flex align-items-center gap-1">
            <i class="bi bi-arrow-left"></i> Kembali
        </a>
    </div>
</div>

<div class="card border-0 shadow-sm rounded-3">
    <div class="card-header bg-white border-bottom py-3 d-flex justify-content-between align-items-center">
        <h6 class="fw-bold mb-0 text-dark"><i class="bi bi-people text-primary me-2"></i> Daftar Siswa dalam Rombel Ini</h6>
        <span class="badge bg-success-subtle text-success border"><?= count($studentsInClass) ?> Siswa Terdaftar</span>
    </div>
    <div class="card-body p-0">
        <div class="table-responsive">
            <table class="table table-hover align-middle mb-0 datatable">
                <thead class="table-light">
                    <tr>
                        <th style="width: 50px;">Foto</th>
                        <th>NIS</th>
                        <th>NISN</th>
                        <th>Nama Lengkap Siswa</th>
                        <th>L/P</th>
                        <th>No HP</th>
                        <th class="text-end" style="width: 120px;">Aksi</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if (empty($studentsInClass)): ?>
                        <tr>
                            <td colspan="7" class="text-center py-5 text-muted">
                                <i class="bi bi-person-dash fs-1 opacity-50 mb-2"></i>
                                <p class="mb-0">Belum ada siswa yang dimasukkan ke rombel kelas ini. Klik tombol <strong>Tambah Anggota Siswa</strong> di atas.</p>
                            </td>
                        </tr>
                    <?php else: ?>
                        <?php foreach ($studentsInClass as $st): ?>
                            <tr>
                                <td>
                                    <div class="rounded-circle overflow-hidden bg-light border d-flex align-items-center justify-content-center" style="width: 36px; height: 36px;">
                                        <?php if (!empty($st['foto'])): ?>
                                            <img src="<?= upload_url($st['foto']) ?>" alt="Foto" class="w-100 h-100 object-fit-cover">
                                        <?php else: ?>
                                            <i class="bi bi-person text-secondary"></i>
                                        <?php endif; ?>
                                    </div>
                                </td>
                                <td class="fw-bold font-monospace text-dark"><?= escape_html($st['nis']) ?></td>
                                <td><?= escape_html($st['nisn'] ?? '-') ?></td>
                                <td class="fw-semibold text-dark"><?= escape_html($st['nama']) ?></td>
                                <td><span class="badge <?= $st['jk'] === 'L' ? 'bg-primary' : 'bg-danger' ?>"><?= escape_html($st['jk']) ?></span></td>
                                <td><?= escape_html($st['no_hp'] ?? '-') ?></td>
                                <td class="text-end">
                                    <form action="<?= base_url('/master/kelas/remove-student') ?>" method="POST" class="d-inline" onsubmit="return confirm('Keluarkan siswa <?= escape_html($st['nama']) ?> dari rombel ini?')">
                                        <input type="hidden" name="id_kelas" value="<?= $kelas['id_kelas'] ?>">
                                        <input type="hidden" name="id_siswa" value="<?= $st['id_siswa'] ?>">
                                        <button type="submit" class="btn btn-outline-danger btn-sm py-1 px-2" title="Keluarkan dari Rombel">
                                            <i class="bi bi-person-x"></i> Keluarkan
                                        </button>
                                    </form>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>

<!-- Modal Tambah Anggota Siswa dengan Search NIS & Nama -->
<div class="modal fade" id="tambahSiswaModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered modal-lg">
        <div class="modal-content border-0 shadow">
            <div class="modal-header bg-primary text-white">
                <h6 class="modal-title fw-bold"><i class="bi bi-person-plus me-1"></i> Pilih Siswa untuk Dimasukkan ke Rombel</h6>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body p-3">
                <!-- Search Box Filter Live -->
                <div class="mb-3">
                    <div class="input-group">
                        <span class="input-group-text bg-light"><i class="bi bi-search"></i></span>
                        <input type="text" id="searchSiswaInput" class="form-control" placeholder="Ketik NIS, NISN, atau Nama Siswa untuk menyaring...">
                    </div>
                </div>

                <div class="table-responsive" style="max-height: 380px; overflow-y: auto;">
                    <table class="table table-hover align-middle mb-0" id="tableAvailableStudents">
                        <thead class="table-light sticky-top">
                            <tr>
                                <th>NIS</th>
                                <th>Nama Lengkap</th>
                                <th>L/P</th>
                                <th class="text-end">Aksi</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php if (empty($availableStudents)): ?>
                                <tr>
                                    <td colspan="4" class="text-center py-4 text-muted">
                                        Semua siswa aktif sudah terdaftar di rombel kelas lain pada periode ini.
                                    </td>
                                </tr>
                            <?php else: ?>
                                <?php foreach ($availableStudents as $as): ?>
                                    <tr class="student-row" data-search="<?= strtolower(escape_html($as['nis'] . ' ' . $as['nama'] . ' ' . ($as['nisn'] ?? ''))) ?>">
                                        <td class="font-monospace fw-bold text-dark"><?= escape_html($as['nis']) ?></td>
                                        <td class="fw-semibold text-dark"><?= escape_html($as['nama']) ?></td>
                                        <td><span class="badge <?= $as['jk'] === 'L' ? 'bg-primary' : 'bg-danger' ?>"><?= escape_html($as['jk']) ?></span></td>
                                        <td class="text-end">
                                            <form action="<?= base_url('/master/kelas/add-student') ?>" method="POST" class="d-inline">
                                                <input type="hidden" name="id_kelas" value="<?= $kelas['id_kelas'] ?>">
                                                <input type="hidden" name="id_siswa" value="<?= $as['id_siswa'] ?>">
                                                <button type="submit" class="btn btn-sm btn-primary py-1 px-3">
                                                    <i class="bi bi-plus me-1"></i> Masukkan
                                                </button>
                                            </form>
                                        </td>
                                    </tr>
                                <?php endforeach; ?>
                            <?php endif; ?>
                        </tbody>
                    </table>
                </div>
            </div>
            <div class="modal-footer bg-light py-2">
                <button type="button" class="btn btn-sm btn-secondary" data-bs-dismiss="modal">Tutup</button>
            </div>
        </div>
    </div>
</div>

<script>
// Live Search Filter for NIS & Nama Siswa
document.getElementById('searchSiswaInput')?.addEventListener('input', function() {
    const q = this.value.toLowerCase().trim();
    const rows = document.querySelectorAll('#tableAvailableStudents .student-row');
    rows.forEach(row => {
        const searchData = row.getAttribute('data-search') || '';
        if (searchData.includes(q)) {
            row.style.display = '';
        } else {
            row.style.display = 'none';
        }
    });
});
</script>

