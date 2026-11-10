<div class="container-fluid py-3">
    <div class="d-flex justify-content-between align-items-center mb-3">
        <div>
            <h4 class="mb-0 fw-bold"><i class="bi bi-pencil-square text-warning me-2"></i>Pengerjaan Tugas Siswa</h4>
            <small class="text-muted"><?= escape_html($schedule['nama_mapel']) ?> | Kelas <?= escape_html($schedule['nama_kelas']) ?></small>
        </div>
        <div>
            <a href="<?= base_url('/student/elearning') ?>" class="btn btn-outline-secondary btn-sm">
                <i class="bi bi-arrow-left me-1"></i> Kembali ke Ruang Belajar
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

    <?php
    $isSubmitted = ($studentLog && (int)$studentLog['log_type'] === 3);
    $hasScore = ($studentLog && $studentLog['nilai'] !== null);
    ?>

    <!-- Status Banner -->
    <?php if ($hasScore): ?>
        <div class="alert alert-success d-flex align-items-center border-0 shadow-sm py-3 mb-3">
            <i class="bi bi-award-fill fs-2 text-success me-3"></i>
            <div class="flex-grow-1">
                <div class="d-flex align-items-center gap-2 mb-1">
                    <strong class="text-success fs-5">Tugas Anda Telah Dinilai!</strong>
                    <span class="badge bg-success fs-6">Skor: <?= number_format((float)$studentLog['nilai'], 2) ?> / 100</span>
                </div>
                <?php if (!empty($studentLog['catatan'])): ?>
                    <div class="small text-dark mt-1 p-2 bg-white rounded border">
                        <i class="bi bi-chat-left-quote text-primary me-1"></i>
                        <strong>Catatan Guru:</strong> <?= escape_html($studentLog['catatan']) ?>
                    </div>
                <?php endif; ?>
            </div>
        </div>
    <?php elseif ($isSubmitted): ?>
        <div class="alert alert-info d-flex align-items-center border-0 shadow-sm py-2 mb-3">
            <i class="bi bi-clock-history fs-3 text-info me-3"></i>
            <div>
                <strong class="text-info-emphasis">Tugas Berhasil Dikumpulkan!</strong>
                <div class="small text-muted">
                    Dikirim pada <?= date('d F Y, H:i:s', strtotime($studentLog['finish_time'] ?? $studentLog['log_time'])) ?> WIB.
                    Menunggu penilaian dan catatan dari guru pengampu.
                </div>
            </div>
        </div>
    <?php endif; ?>

    <div class="row g-3">
        <!-- Task Instructions & Content -->
        <div class="col-lg-7">
            <div class="card shadow-sm border-0 mb-3">
                <div class="card-header bg-white py-2 fw-bold">
                    <i class="bi bi-card-text text-primary me-1"></i> Petunjuk & Instruksi Tugas
                </div>
                <div class="card-body p-4">
                    <span class="badge bg-warning-subtle text-warning-emphasis border border-warning-subtle mb-2">
                        <?= escape_html($schedule['nama_mapel']) ?>
                    </span>
                    <h4 class="fw-bold text-dark mb-2"><?= escape_html($schedule['judul_materi']) ?></h4>
                    <div class="text-muted small mb-3 pb-2 border-bottom">
                        Guru Pengampu: <strong><?= escape_html($schedule['nama_guru']) ?></strong> |
                        Jadwal: <?= format_indo_date($schedule['jadwal_materi']) ?>
                    </div>

                    <!-- Video if provided -->
                    <?php if (!empty($schedule['link_video'])): ?>
                        <div class="mb-4">
                            <h6 class="fw-bold text-danger"><i class="bi bi-play-circle me-1"></i> Video Pendukung</h6>
                            <?php
                            $videoUrl = $schedule['link_video'];
                            $isYoutube = preg_match('/(?:youtube\.com\/(?:watch\?v=|embed\/)|youtu\.be\/)([a-zA-Z0-9_-]+)/', $videoUrl, $ytMatch);
                            ?>
                            <?php if ($isYoutube): ?>
                                <div class="ratio ratio-16x9 rounded overflow-hidden shadow-sm">
                                    <iframe src="https://www.youtube.com/embed/<?= $ytMatch[1] ?>" allowfullscreen></iframe>
                                </div>
                            <?php else: ?>
                                <video src="<?= escape_html($videoUrl) ?>" controls class="w-100 rounded shadow-sm"></video>
                            <?php endif; ?>
                        </div>
                    <?php endif; ?>

                    <!-- Rich Text Instructions -->
                    <div class="tugas-content arabic-text lh-lg mb-4 text-dark" style="font-size: 15px;">
                        <?= $schedule['isi_materi'] ?>
                    </div>

                    <!-- Attachments from Teacher -->
                    <?php if (!empty($schedule['file_list'])): ?>
                        <div class="mt-4 pt-3 border-top">
                            <h6 class="fw-bold mb-2"><i class="bi bi-paperclip text-primary me-1"></i> Lembar Kerja / Berkas dari Guru:</h6>
                            <div class="list-group">
                                <?php foreach ($schedule['file_list'] as $f): ?>
                                    <a href="<?= base_url(escape_html($f['path'])) ?>" target="_blank" class="list-group-item list-group-item-action d-flex justify-content-between align-items-center">
                                        <div>
                                            <i class="bi bi-file-earmark-arrow-down fs-5 text-primary me-2"></i>
                                            <strong class="text-dark"><?= escape_html($f['orig_name'] ?? $f['filename']) ?></strong>
                                        </div>
                                        <span class="badge bg-light text-muted border">
                                            <?= round(($f['size'] ?? 0) / 1024, 1) ?> KB
                                        </span>
                                    </a>
                                <?php endforeach; ?>
                            </div>
                        </div>
                    <?php endif; ?>
                </div>
            </div>
        </div>

        <!-- Student Submission & Discussion -->
        <div class="col-lg-5">
            <!-- Submission Card -->
            <div class="card shadow-sm border-0 mb-3">
                <div class="card-header bg-white py-2 fw-bold d-flex justify-content-between align-items-center">
                    <span><i class="bi bi-upload text-primary me-1"></i> Lembar Pengumpulan Tugas</span>
                    <?php if ($isSubmitted): ?>
                        <span class="badge bg-success">Sudah Dikirim</span>
                    <?php else: ?>
                        <span class="badge bg-warning text-dark">Belum Kirim</span>
                    <?php endif; ?>
                </div>
                <div class="card-body p-3">
                    <?php if ($isSubmitted): ?>
                        <div class="mb-3">
                            <label class="form-label small fw-semibold text-secondary">Jawaban Teks Terkirim:</label>
                            <div class="p-3 border rounded bg-light small text-dark" style="max-height: 150px; overflow-y: auto;">
                                <?= nl2br(escape_html($studentLog['isi_tugas'] ?? '- Tidak ada jawaban teks -')) ?>
                            </div>
                        </div>

                        <?php if (!empty($studentLog['file_list'])): ?>
                            <div class="mb-3">
                                <label class="form-label small fw-semibold text-secondary">Berkas yang Anda Unggah:</label>
                                <div class="list-group list-group-flush border rounded">
                                    <?php foreach ($studentLog['file_list'] as $sf): ?>
                                        <div class="list-group-item px-2 py-2 d-flex justify-content-between align-items-center">
                                            <div class="text-truncate">
                                                <i class="bi bi-file-earmark-check text-success me-1"></i>
                                                <a href="<?= base_url(escape_html($sf['path'])) ?>" target="_blank" class="text-decoration-none small">
                                                    <?= escape_html($sf['orig_name'] ?? 'Berkas Anda') ?>
                                                </a>
                                            </div>
                                            <span class="badge bg-light text-muted border" style="font-size: 10px;">
                                                <?= round(($sf['size'] ?? 0) / 1024, 1) ?> KB
                                            </span>
                                        </div>
                                    <?php endforeach; ?>
                                </div>
                            </div>
                        <?php endif; ?>

                        <hr class="my-3">
                        <div class="small text-muted mb-2">Ingin memperbarui jawaban atau mengunggah berkas baru?</div>
                    <?php endif; ?>

                    <form action="<?= base_url('/student/elearning/kumpul/' . $schedule['id_kjm']) ?>" method="POST" enctype="multipart/form-data">
                        <div class="mb-3">
                            <label class="form-label small fw-semibold">Jawaban Teks / Uraian</label>
                            <textarea name="isi_tugas" class="form-control form-control-sm" rows="4" placeholder="Tuliskan jawaban atau keterangan tugas di sini..."><?= escape_html($studentLog['isi_tugas'] ?? '') ?></textarea>
                        </div>

                        <div class="mb-3">
                            <label class="form-label small fw-semibold">Unggah Berkas / Dokumen / Foto Tugas</label>
                            <input type="file" name="files[]" class="form-control form-control-sm" multiple>
                            <div class="form-text" style="font-size: 11px;">Mendukung format PDF, Word, Excel, JPG, PNG, ZIP.</div>
                        </div>

                        <button type="submit" class="btn btn-warning text-dark fw-bold btn-sm w-100 py-2">
                            <i class="bi bi-send-check me-1"></i> <?= $isSubmitted ? 'Perbarui Pengumpulan Tugas' : 'Kumpulkan Tugas Sekarang' ?>
                        </button>
                    </form>
                </div>
            </div>

            <!-- Discussion Section -->
            <div class="card shadow-sm border-0">
                <div class="card-header bg-white py-2 fw-bold d-flex justify-content-between align-items-center">
                    <span><i class="bi bi-chat-dots text-primary me-1"></i> Tanya Guru / Diskusi</span>
                    <span class="badge bg-secondary"><?= count($comments) ?></span>
                </div>
                <div class="card-body p-3">
                    <div class="comments-list mb-3" style="max-height: 250px; overflow-y: auto;">
                        <?php if (empty($comments)): ?>
                            <div class="text-muted text-center py-3 small">
                                Belum ada diskusi pada tugas ini.
                            </div>
                        <?php else: ?>
                            <?php foreach ($comments as $c): ?>
                                <div class="p-2 border rounded mb-2 <?= $c['role'] === 'guru' ? 'bg-primary-subtle border-primary-subtle' : 'bg-light' ?>">
                                    <div class="d-flex justify-content-between align-items-center mb-1">
                                        <span class="fw-bold text-dark" style="font-size: 11px;">
                                            <?= escape_html($c['author_name']) ?>
                                            <?php if ($c['role'] === 'guru'): ?>
                                                <span class="badge bg-primary ms-1" style="font-size: 9px;">Guru</span>
                                            <?php endif; ?>
                                        </span>
                                        <span class="text-muted" style="font-size: 10px;">
                                            <?= date('d/m H:i', strtotime($c['created_at'])) ?>
                                        </span>
                                    </div>
                                    <div class="text-secondary" style="font-size: 12px;">
                                        <?= nl2br(escape_html($c['komentar'])) ?>
                                    </div>
                                </div>
                            <?php endforeach; ?>
                        <?php endif; ?>
                    </div>

                    <form action="<?= base_url('/student/elearning/komentar/' . $schedule['id_materi']) ?>" method="POST">
                        <input type="hidden" name="id_kjm" value="<?= $schedule['id_kjm'] ?>">
                        <div class="input-group input-group-sm">
                            <input type="text" name="komentar" class="form-control" placeholder="Tanyakan hal yang membingungkan..." required>
                            <button type="submit" class="btn btn-primary">
                                <i class="bi bi-send"></i>
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>

