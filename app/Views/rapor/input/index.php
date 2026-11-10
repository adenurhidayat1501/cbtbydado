<div class="card shadow-sm border-0 mb-4">
    <div class="card-header bg-white py-3">
        <h5 class="card-title mb-0"><i class="bi bi-calculator me-2 text-primary"></i>Kumpulan Nilai Rapor & Pengolahan Hasil Belajar</h5>
    </div>
    <div class="card-body">
        <p class="text-muted">Pilih modul entri atau penarikan nilai rapor berikut untuk mengisi data akademik, kepribadian, kehadiran, dan fisik siswa.</p>

        <div class="row g-4">
            <!-- 1. Nilai Mata Pelajaran -->
            <div class="col-md-4">
                <div class="card h-100 border border-primary border-2 shadow-none hover-shadow">
                    <div class="card-body">
                        <div class="d-flex align-items-center mb-3">
                            <div class="bg-primary-subtle text-primary p-3 rounded-circle me-3">
                                <i class="bi bi-journal-text fs-3"></i>
                            </div>
                            <div>
                                <h6 class="card-title fw-bold mb-0">Nilai Mata Pelajaran</h6>
                                <small class="text-muted">KI-3, KI-4, PTS, PAS</small>
                            </div>
                        </div>
                        <p class="card-text small text-secondary">
                            Input nilai pengetahuan dan keterampilan per KD, sinkronisasi otomatis nilai tugas E-Learning & ujian CBT, serta import CSV/Excel.
                        </p>
                    </div>
                    <div class="card-footer bg-transparent border-0 pt-0">
                        <a href="<?= base_url('/rapor/input-nilai/mapel') ?>" class="btn btn-sm btn-primary w-100">
                            <i class="bi bi-pencil-square me-1"></i>Buka Entri Nilai Mapel
                        </a>
                    </div>
                </div>
            </div>

            <!-- 2. Nilai Sikap -->
            <div class="col-md-4">
                <div class="card h-100 border border-success border-2 shadow-none hover-shadow">
                    <div class="card-body">
                        <div class="d-flex align-items-center mb-3">
                            <div class="bg-success-subtle text-success p-3 rounded-circle me-3">
                                <i class="bi bi-heart-pulse fs-3"></i>
                            </div>
                            <div>
                                <h6 class="card-title fw-bold mb-0">Sikap Spiritual & Sosial</h6>
                                <small class="text-muted">KI-1 & KI-2</small>
                            </div>
                        </div>
                        <p class="card-text small text-secondary">
                            Pengisian predikat dan catatan deskripsi perilaku ketaatan beribadah, toleransi, kejujuran, kedisiplinan, dan tanggung jawab.
                        </p>
                    </div>
                    <div class="card-footer bg-transparent border-0 pt-0">
                        <a href="<?= base_url('/rapor/input-nilai/sikap') ?>" class="btn btn-sm btn-success w-100">
                            <i class="bi bi-pencil-square me-1"></i>Buka Entri Sikap
                        </a>
                    </div>
                </div>
            </div>

            <!-- 3. Ekstrakurikuler -->
            <div class="col-md-4">
                <div class="card h-100 border border-info border-2 shadow-none hover-shadow">
                    <div class="card-body">
                        <div class="d-flex align-items-center mb-3">
                            <div class="bg-info-subtle text-info p-3 rounded-circle me-3">
                                <i class="bi bi-trophy fs-3"></i>
                            </div>
                            <div>
                                <h6 class="card-title fw-bold mb-0">Nilai Ekstrakurikuler</h6>
                                <small class="text-muted">Pramuka, Olahraga, Seni</small>
                            </div>
                        </div>
                        <p class="card-text small text-secondary">
                            Input nilai predikat (A, B, C) serta keterangan kegiatan ekstrakurikuler yang diikuti oleh masing-masing siswa di sekolah.
                        </p>
                    </div>
                    <div class="card-footer bg-transparent border-0 pt-0">
                        <a href="<?= base_url('/rapor/input-nilai/ekstra') ?>" class="btn btn-sm btn-info text-white w-100">
                            <i class="bi bi-pencil-square me-1"></i>Buka Entri Ekstra
                        </a>
                    </div>
                </div>
            </div>

            <!-- 4. Catatan Wali & Absensi -->
            <div class="col-md-4">
                <div class="card h-100 border border-warning border-2 shadow-none hover-shadow">
                    <div class="card-body">
                        <div class="d-flex align-items-center mb-3">
                            <div class="bg-warning-subtle text-warning p-3 rounded-circle me-3">
                                <i class="bi bi-chat-quote fs-3"></i>
                            </div>
                            <div>
                                <h6 class="card-title fw-bold mb-0">Catatan Wali & Presensi</h6>
                                <small class="text-muted">Sakit, Izin, Alfa</small>
                            </div>
                        </div>
                        <p class="card-text small text-secondary">
                            Rekap total ketidakhadiran selama satu semester beserta catatan motivasi dan saran pembinaan dari wali kelas.
                        </p>
                    </div>
                    <div class="card-footer bg-transparent border-0 pt-0">
                        <a href="<?= base_url('/rapor/input-nilai/catatan') ?>" class="btn btn-sm btn-warning w-100">
                            <i class="bi bi-pencil-square me-1"></i>Buka Catatan & Absen
                        </a>
                    </div>
                </div>
            </div>

            <!-- 5. Kesehatan & Fisik -->
            <div class="col-md-4">
                <div class="card h-100 border border-secondary border-2 shadow-none hover-shadow">
                    <div class="card-body">
                        <div class="d-flex align-items-center mb-3">
                            <div class="bg-secondary-subtle text-secondary p-3 rounded-circle me-3">
                                <i class="bi bi-activity fs-3"></i>
                            </div>
                            <div>
                                <h6 class="card-title fw-bold mb-0">Kesehatan & Fisik</h6>
                                <small class="text-muted">Tinggi, Berat, Panca Indera</small>
                            </div>
                        </div>
                        <p class="card-text small text-secondary">
                            Pencatatan perkembangan fisik tinggi badan (cm), berat badan (kg), kondisi pendengaran, penglihatan, serta kebersihan gigi.
                        </p>
                    </div>
                    <div class="card-footer bg-transparent border-0 pt-0">
                        <a href="<?= base_url('/rapor/input-nilai/fisik') ?>" class="btn btn-sm btn-secondary w-100">
                            <i class="bi bi-pencil-square me-1"></i>Buka Entri Fisik
                        </a>
                    </div>
                </div>
            </div>

            <!-- 6. Prestasi -->
            <div class="col-md-4">
                <div class="card h-100 border border-danger border-2 shadow-none hover-shadow">
                    <div class="card-body">
                        <div class="d-flex align-items-center mb-3">
                            <div class="bg-danger-subtle text-danger p-3 rounded-circle me-3">
                                <i class="bi bi-star fs-3"></i>
                            </div>
                            <div>
                                <h6 class="card-title fw-bold mb-0">Prestasi & Penghargaan</h6>
                                <small class="text-muted">Lomba & Prestasi Siswa</small>
                            </div>
                        </div>
                        <p class="card-text small text-secondary">
                            Pencatatan peringkat ranking kelas serta piagam/kejuaraan kurikuler maupun ekstrakurikuler yang berhasil diraih siswa.
                        </p>
                    </div>
                    <div class="card-footer bg-transparent border-0 pt-0">
                        <a href="<?= base_url('/rapor/input-nilai/prestasi') ?>" class="btn btn-sm btn-danger w-100">
                            <i class="bi bi-pencil-square me-1"></i>Buka Entri Prestasi
                        </a>
                    </div>
                </div>
            </div>

            <!-- 7. Kenaikan Kelas / Kelulusan -->
            <div class="col-md-12">
                <div class="card border border-dark shadow-sm">
                    <div class="card-body d-flex align-items-center justify-content-between flex-wrap gap-3">
                        <div class="d-flex align-items-center">
                            <div class="bg-dark text-white p-3 rounded-circle me-3">
                                <i class="bi bi-mortarboard fs-3"></i>
                            </div>
                            <div>
                                <h6 class="card-title fw-bold mb-1">Keputusan Kenaikan Kelas / Kelulusan (Semester Genap)</h6>
                                <p class="card-text small text-muted mb-0">
                                    Penetapan resmi status Naik ke Kelas Berikutnya atau Tinggal Kelas (Lulus / Belum Lulus untuk tingkat akhir).
                                </p>
                            </div>
                        </div>
                        <div>
                            <a href="<?= base_url('/rapor/input-nilai/naik') ?>" class="btn btn-dark px-4">
                                <i class="bi bi-check2-all me-1"></i>Atur Kenaikan Kelas
                            </a>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

