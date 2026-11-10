<div class="d-flex flex-wrap justify-content-between align-items-center mb-4">
    <div>
        <h4 class="fw-bold mb-1 text-dark">Ruang Asesmen Siswa</h4>
        <p class="text-muted small mb-0">
            Nama: <strong><?= escape_html($siswa['nama'] ?? 'Peserta Ujian') ?></strong> | NIS: <strong><?= escape_html($siswa['nis'] ?? '-') ?></strong> | Kelas: <strong><?= escape_html($siswa['nama_kelas'] ?? '-') ?></strong>
        </p>
    </div>
    <div class="tp-smt-badge">
        <i class="bi bi-person-check-fill text-success"></i>
        <span>Sesi Ujian Aktif (Single Device Locked)</span>
    </div>
</div>

<div class="row g-4">
    <div class="col-lg-8">
        <div class="card border-0 shadow-sm rounded-3">
            <div class="card-header bg-white border-bottom py-3">
                <h6 class="fw-bold mb-0 text-dark">
                    <i class="bi bi-journal-text text-primary me-2"></i> Jadwal Ujian Tersedia Hari Ini
                </h6>
            </div>
            <div class="card-body p-0">
                <?php if (empty($exams)): ?>
                    <div class="text-center py-5 text-muted">
                        <i class="bi bi-calendar-x fs-1 opacity-50 mb-2"></i>
                        <p class="mb-0">Tidak ada jadwal ujian yang sedang aktif untuk Anda saat ini.</p>
                    </div>
                <?php else: ?>
                    <div class="table-responsive">
                        <table class="table table-hover align-middle mb-0">
                            <thead class="table-light">
                                <tr>
                                    <th>Mata Pelajaran</th>
                                    <th>Jenis Ujian</th>
                                    <th>Durasi</th>
                                    <th>Status</th>
                                    <th class="text-end">Aksi</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach ($exams as $ex): ?>
                                    <tr>
                                        <td>
                                            <div class="fw-semibold text-dark"><?= escape_html($ex['nama_mapel']) ?></div>
                                            <small class="text-muted">Kode Bank: <?= escape_html($ex['bank_kode']) ?></small>
                                        </td>
                                        <td><span class="badge bg-info-subtle text-info border"><?= escape_html($ex['nama_jenis']) ?></span></td>
                                        <td><i class="bi bi-clock me-1 text-secondary"></i><?= (int)$ex['durasi_ujian'] ?> Menit</td>
                                        <td>
                                            <?php if ((int)$ex['status_durasi'] === 2): ?>
                                                <span class="badge bg-success"><i class="bi bi-check2"></i> Selesai</span>
                                            <?php elseif ((int)$ex['status_durasi'] === 1): ?>
                                                <span class="badge bg-warning text-dark"><i class="bi bi-hourglass-split"></i> Sedang Berjalan</span>
                                            <?php else: ?>
                                                <span class="badge bg-secondary">Belum Mulai</span>
                                            <?php endif; ?>
                                        </td>
                                        <td class="text-end">
                                            <?php if ((int)$ex['status_durasi'] === 2): ?>
                                                <button class="btn btn-sm btn-light border text-muted" disabled>Selesai</button>
                                            <?php else: ?>
                                                <a href="<?= base_url('/cbt/exam/' . $ex['id_jadwal']) ?>" class="btn btn-sm btn-primary fw-semibold px-3">
                                                    <?= (int)$ex['status_durasi'] === 1 ? 'Lanjutkan &rarr;' : 'Mulai Ujian &rarr;' ?>
                                                </a>
                                            <?php endif; ?>
                                        </td>
                                    </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                    </div>
                <?php endif; ?>
            </div>
        </div>
    </div>

    <div class="col-lg-4">
        <div class="card border-0 shadow-sm rounded-3">
            <div class="card-header bg-white border-bottom py-3">
                <h6 class="fw-bold mb-0 text-dark"><i class="bi bi-info-circle text-primary me-2"></i> Tata Tertib Ujian</h6>
            </div>
            <div class="card-body small text-secondary">
                <ol class="ps-3 mb-0" style="line-height: 1.8;">
                    <li>Pastikan koneksi internet stabil sebelum menekan tombol Mulai Ujian.</li>
                    <li><strong>Dilarang membuka tab lain</strong> atau berpindah aplikasi selama ujian berlangsung. Pelanggaran akan terekam oleh sistem pengawas.</li>
                    <li>Timer ujian dihitung sinkron dengan waktu server dan tidak akan ter-reset saat halaman dimuat ulang.</li>
                    <li>Jawaban disimpan otomatis ke server setiap kali Anda memilih atau mengubah pilihan.</li>
                    <li>Selesaikan seluruh soal sebelum menekan tombol Selesai Ujian.</li>
                </ol>
            </div>
        </div>
    </div>
</div>

