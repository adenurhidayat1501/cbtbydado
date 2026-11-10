<div class="container-fluid py-3">
    <div class="d-flex justify-content-between align-items-center mb-3">
        <div>
            <h4 class="mb-0 fw-bold"><i class="bi bi-book-half text-primary me-2"></i>Materi Belajar</h4>
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

    <!-- Attendance Notification Banner -->
    <?php if (!empty($studentLog)): ?>
        <div class="alert alert-success d-flex align-items-center border-0 shadow-sm py-2 mb-3">
            <i class="bi bi-check-circle-fill fs-4 text-success me-3"></i>
            <div>
                <strong class="text-success">Kehadiran KBM Anda Berhasil Dicatat Otomatis!</strong>
                <div class="small text-muted">
                    Waktu Akses: <?= date('d F Y, H:i:s', strtotime($studentLog['log_time'])) ?> WIB
                    <?php if (!empty($studentLog['jam_ke'])): ?>
                        | Tercatat pada <strong>Jam Pelajaran Ke-<?= $studentLog['jam_ke'] ?></strong>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    <?php endif; ?>

    <div class="row g-3">
        <!-- Content Column -->
        <div class="col-lg-8">
            <div class="card shadow-sm border-0 mb-3">
                <div class="card-body p-4">
                    <div class="d-flex align-items-center gap-2 mb-2">
                        <span class="badge bg-primary-subtle text-primary border border-primary-subtle">
                            <?= escape_html($schedule['nama_mapel']) ?>
                        </span>
                        <span class="badge bg-light text-secondary border">
                            Tanggal: <?= format_indo_date($schedule['jadwal_materi']) ?>
                        </span>
                    </div>

                    <h3 class="fw-bold text-dark mb-3"><?= escape_html($schedule['judul_materi']) ?></h3>

                    <div class="d-flex align-items-center gap-2 text-muted small mb-4 pb-3 border-bottom">
                        <i class="bi bi-person-circle fs-5 text-primary"></i>
                        <span>Guru Pengampu: <strong><?= escape_html($schedule['nama_guru']) ?></strong></span>
                    </div>

                    <!-- Video Pembelajaran -->
                    <?php if (!empty($schedule['link_video'])): ?>
                        <div class="mb-4">
                            <h6 class="fw-bold text-danger"><i class="bi bi-play-circle me-1"></i> Video Pembelajaran</h6>
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

                    <!-- Rich HTML Material Body -->
                    <div class="materi-body arabic-text lh-lg mb-4 text-dark" style="font-size: 15px;">
                        <?= $schedule['isi_materi'] ?>
                    </div>

                    <!-- Attachments -->
                    <?php if (!empty($schedule['file_list'])): ?>
                        <div class="mt-4 pt-3 border-top">
                            <h6 class="fw-bold mb-2"><i class="bi bi-paperclip text-primary me-1"></i> Berkas Lampiran untuk Dipelajari:</h6>
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

        <!-- Comments & Discussion Column -->
        <div class="col-lg-4">
            <div class="card shadow-sm border-0">
                <div class="card-header bg-white py-2 fw-bold d-flex justify-content-between align-items-center">
                    <span><i class="bi bi-chat-dots text-primary me-1"></i> Ruang Diskusi Siswa</span>
                    <span class="badge bg-secondary"><?= count($comments) ?></span>
                </div>
                <div class="card-body p-3">
                    <div class="comments-list mb-3" style="max-height: 420px; overflow-y: auto;">
                        <?php if (empty($comments)): ?>
                            <div class="text-muted text-center py-4 small">
                                <i class="bi bi-chat-square-dots fs-3 d-block mb-1 text-secondary"></i>
                                Belum ada pertanyaan atau diskusi pada materi ini. Tanyakan jika ada yang belum kamu pahami!
                            </div>
                        <?php else: ?>
                            <?php foreach ($comments as $c): ?>
                                <div class="p-2 border rounded mb-2 <?= $c['role'] === 'guru' ? 'bg-primary-subtle border-primary-subtle' : 'bg-light' ?>">
                                    <div class="d-flex justify-content-between align-items-center mb-1">
                                        <span class="fw-bold small text-dark">
                                            <?= escape_html($c['author_name']) ?>
                                            <?php if ($c['role'] === 'guru'): ?>
                                                <span class="badge bg-primary ms-1" style="font-size: 10px;">Guru Pengampu</span>
                                            <?php elseif ($c['role'] === 'admin'): ?>
                                                <span class="badge bg-dark ms-1" style="font-size: 10px;">Admin</span>
                                            <?php else: ?>
                                                <span class="badge bg-secondary ms-1" style="font-size: 10px;">Siswa</span>
                                            <?php endif; ?>
                                        </span>
                                        <span class="text-muted" style="font-size: 10px;">
                                            <?= date('d/m H:i', strtotime($c['created_at'])) ?>
                                        </span>
                                    </div>
                                    <div class="small text-secondary" style="font-size: 12px;">
                                        <?= nl2br(escape_html($c['komentar'])) ?>
                                    </div>
                                </div>
                            <?php endforeach; ?>
                        <?php endif; ?>
                    </div>

                    <form action="<?= base_url('/student/elearning/komentar/' . $schedule['id_materi']) ?>" method="POST">
                        <input type="hidden" name="id_kjm" value="<?= $schedule['id_kjm'] ?>">
                        <div class="mb-2">
                            <textarea name="komentar" class="form-control form-control-sm" rows="3" placeholder="Tuliskan pertanyaan atau komentar untuk gurumu..." required></textarea>
                        </div>
                        <button type="submit" class="btn btn-primary btn-sm w-100">
                            <i class="bi bi-send me-1"></i> Kirim Pertanyaan
                        </button>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>

