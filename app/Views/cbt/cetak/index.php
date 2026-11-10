<?php
$title = $title ?? 'Cetak Administrasi CBT — DADO CBT';
$kelasList = $kelasList ?? [];
$jadwalList = $jadwalList ?? [];
$ruangList = $ruangList ?? [];
$sesiList = $sesiList ?? [];
$kop = $kop ?? [];
$tp = $tp ?? [];
$smt = $smt ?? [];
?>

<div class="content-header">
    <div class="container-fluid">
        <div class="row mb-2">
            <div class="col-sm-6">
                <h1 class="m-0 text-dark font-weight-bold">
                    <i class="fas fa-print text-primary mr-2"></i>Cetak Administrasi Ujian CBT
                </h1>
            </div>
            <div class="col-sm-6">
                <ol class="breadcrumb float-sm-right">
                    <li class="breadcrumb-item"><a href="/dashboard">Beranda</a></li>
                    <li class="breadcrumb-item"><a href="/cbt/jadwal">CBT</a></li>
                    <li class="breadcrumb-item active">Cetak Administrasi</li>
                </ol>
            </div>
        </div>
    </div>
</div>

<section class="content">
    <div class="container-fluid">

        <?php if ($flash = get_flash('success')): ?>
            <div class="alert alert-success alert-dismissible fade show" role="alert">
                <i class="fas fa-check-circle mr-2"></i><?= htmlspecialchars($flash) ?>
                <button type="button" class="close" data-dismiss="alert" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
        <?php endif; ?>

        <div class="row">
            <!-- Left: Print Option Cards -->
            <div class="col-lg-7 col-md-12">
                <!-- 1. Kartu Peserta Ujian -->
                <div class="card card-outline card-primary shadow-sm mb-4">
                    <div class="card-header bg-white py-3">
                        <h5 class="card-title font-weight-bold text-primary mb-0">
                            <i class="fas fa-id-card mr-2"></i>1. Cetak Kartu Peserta Ujian
                        </h5>
                    </div>
                    <form action="/cbt/cetak/kartu-peserta" method="GET" target="_blank">
                        <div class="card-body">
                            <p class="text-muted small">Cetak kartu ujian per kelas atau cetak seluruh siswa terdaftar sekaligus dengan foto siswa dan QR Code.</p>
                            
                            <div class="form-group mb-3">
                                <label class="font-weight-bold">Pilih Kelas / Rombel:</label>
                                <select name="kelas_id" class="form-control select2">
                                    <option value="0">-- Cetak Semua Kelas --</option>
                                    <?php foreach ($kelasList as $k): ?>
                                        <option value="<?= $k['id_kelas'] ?>"><?= htmlspecialchars($k['nama_kelas']) ?></option>
                                    <?php endforeach; ?>
                                </select>
                            </div>
                        </div>
                        <div class="card-footer bg-light py-2 text-right">
                            <button type="submit" class="btn btn-primary font-weight-bold shadow-sm">
                                <i class="fas fa-print mr-1"></i>Buka Halaman Cetak Kartu
                            </button>
                        </div>
                    </form>
                </div>

                <!-- 2. Daftar Kehadiran Siswa -->
                <div class="card card-outline card-success shadow-sm mb-4">
                    <div class="card-header bg-white py-3">
                        <h5 class="card-title font-weight-bold text-success mb-0">
                            <i class="fas fa-clipboard-check mr-2"></i>2. Cetak Daftar Kehadiran Ujian
                        </h5>
                    </div>
                    <form action="/cbt/cetak/daftar-hadir" method="GET" target="_blank">
                        <div class="card-body">
                            <p class="text-muted small">Daftar kehadiran peserta ujian per jadwal, ruang, dan sesi untuk ditandatangani siswa dan pengawas.</p>

                            <div class="form-group mb-3">
                                <label class="font-weight-bold">Pilih Jadwal Ujian: <span class="text-danger">*</span></label>
                                <select name="jadwal_id" class="form-control select2" required>
                                    <option value="">-- Pilih Jadwal Ujian --</option>
                                    <?php foreach ($jadwalList as $j): ?>
                                        <option value="<?= $j['id_jadwal'] ?>">[<?= htmlspecialchars($j['bank_kode']) ?>] <?= htmlspecialchars($j['nama_mapel']) ?> (Jam Ke-<?= $j['jam_ke'] ?>)</option>
                                    <?php endforeach; ?>
                                </select>
                            </div>

                            <div class="row">
                                <div class="col-md-6">
                                    <div class="form-group mb-3">
                                        <label class="font-weight-bold">Pilih Ruang: <span class="text-danger">*</span></label>
                                        <select name="ruang_id" class="form-control" required>
                                            <option value="">-- Pilih Ruang --</option>
                                            <?php foreach ($ruangList as $r): ?>
                                                <option value="<?= $r['id_ruang'] ?>"><?= htmlspecialchars($r['nama_ruang']) ?></option>
                                            <?php endforeach; ?>
                                        </select>
                                    </div>
                                </div>
                                <div class="col-md-6">
                                    <div class="form-group mb-3">
                                        <label class="font-weight-bold">Pilih Sesi: <span class="text-danger">*</span></label>
                                        <select name="sesi_id" class="form-control" required>
                                            <option value="">-- Pilih Sesi --</option>
                                            <?php foreach ($sesiList as $s): ?>
                                                <option value="<?= $s['id_sesi'] ?>"><?= htmlspecialchars($s['nama_sesi']) ?></option>
                                            <?php endforeach; ?>
                                        </select>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <div class="card-footer bg-light py-2 text-right">
                            <button type="submit" class="btn btn-success font-weight-bold shadow-sm">
                                <i class="fas fa-print mr-1"></i>Cetak Daftar Hadir
                            </button>
                        </div>
                    </form>
                </div>

                <!-- 3. Berita Acara Pelaksanaan -->
                <div class="card card-outline card-info shadow-sm mb-4">
                    <div class="card-header bg-white py-3">
                        <h5 class="card-title font-weight-bold text-info mb-0">
                            <i class="fas fa-file-contract mr-2"></i>3. Cetak Berita Acara Pelaksanaan Ujian
                        </h5>
                    </div>
                    <form action="/cbt/cetak/berita-acara" method="GET" target="_blank">
                        <div class="card-body">
                            <p class="text-muted small">Berita acara resmi pelaksanaan tes untuk pengawas ruang dan penanggung jawab proktor.</p>

                            <div class="form-group mb-3">
                                <label class="font-weight-bold">Pilih Jadwal Ujian: <span class="text-danger">*</span></label>
                                <select name="jadwal_id" class="form-control select2" required>
                                    <option value="">-- Pilih Jadwal Ujian --</option>
                                    <?php foreach ($jadwalList as $j): ?>
                                        <option value="<?= $j['id_jadwal'] ?>">[<?= htmlspecialchars($j['bank_kode']) ?>] <?= htmlspecialchars($j['nama_mapel']) ?> (Jam Ke-<?= $j['jam_ke'] ?>)</option>
                                    <?php endforeach; ?>
                                </select>
                            </div>

                            <div class="row">
                                <div class="col-md-6">
                                    <div class="form-group mb-3">
                                        <label class="font-weight-bold">Pilih Ruang: <span class="text-danger">*</span></label>
                                        <select name="ruang_id" class="form-control" required>
                                            <option value="">-- Pilih Ruang --</option>
                                            <?php foreach ($ruangList as $r): ?>
                                                <option value="<?= $r['id_ruang'] ?>"><?= htmlspecialchars($r['nama_ruang']) ?></option>
                                            <?php endforeach; ?>
                                        </select>
                                    </div>
                                </div>
                                <div class="col-md-6">
                                    <div class="form-group mb-3">
                                        <label class="font-weight-bold">Pilih Sesi: <span class="text-danger">*</span></label>
                                        <select name="sesi_id" class="form-control" required>
                                            <option value="">-- Pilih Sesi --</option>
                                            <?php foreach ($sesiList as $s): ?>
                                                <option value="<?= $s['id_sesi'] ?>"><?= htmlspecialchars($s['nama_sesi']) ?></option>
                                            <?php endforeach; ?>
                                        </select>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <div class="card-footer bg-light py-2 text-right">
                            <button type="submit" class="btn btn-info font-weight-bold shadow-sm">
                                <i class="fas fa-print mr-1"></i>Cetak Berita Acara
                            </button>
                        </div>
                    </form>
                </div>
            </div>

            <!-- Right: Kop & Penandatangan Settings Form -->
            <div class="col-lg-5 col-md-12">
                <div class="card card-outline card-secondary shadow-sm mb-4">
                    <div class="card-header bg-white py-3">
                        <h5 class="card-title font-weight-bold text-dark mb-0">
                            <i class="fas fa-heading mr-2"></i>Pengaturan Kop & Penandatangan
                        </h5>
                    </div>
                    <form action="/cbt/cetak/save-kop" method="POST">
                        <div class="card-body">
                            <div class="form-group mb-2">
                                <label class="font-weight-bold small">Header Baris 1 (Instansi/Kemenag/Pemda):</label>
                                <input type="text" name="header_1" class="form-control form-control-sm" value="<?= htmlspecialchars($kop['header_1'] ?? 'PEMERINTAH KABUPATEN / KOTA') ?>">
                            </div>
                            <div class="form-group mb-2">
                                <label class="font-weight-bold small">Header Baris 2 (Dinas / Kantor):</label>
                                <input type="text" name="header_2" class="form-control form-control-sm" value="<?= htmlspecialchars($kop['header_2'] ?? 'DINAS PENDIDIKAN DAN KEBUDAYAAN') ?>">
                            </div>
                            <div class="form-group mb-2">
                                <label class="font-weight-bold small">Header Baris 3 (Nama Satuan Pendidikan):</label>
                                <input type="text" name="header_3" class="form-control form-control-sm font-weight-bold" value="<?= htmlspecialchars($kop['header_3'] ?? 'SMP / MTs / SMA NEGERI 1') ?>">
                            </div>
                            <div class="form-group mb-2">
                                <label class="font-weight-bold small">Header Baris 4 (Alamat & Kontak):</label>
                                <input type="text" name="header_4" class="form-control form-control-sm" value="<?= htmlspecialchars($kop['header_4'] ?? 'Jl. Pendidikan No. 1, Email: info@sekolah.sch.id') ?>">
                            </div>

                            <hr>
                            <h6 class="font-weight-bold text-muted small"><i class="fas fa-pen-nib mr-1"></i>Nama Pejabat & Pengawas:</h6>

                            <div class="form-group mb-2">
                                <label class="font-weight-bold small">Nama Proktor Utama:</label>
                                <input type="text" name="proktor" class="form-control form-control-sm" value="<?= htmlspecialchars($kop['proktor'] ?? 'Proktor Utama, S.Kom.') ?>">
                            </div>
                            <div class="form-group mb-2">
                                <label class="font-weight-bold small">Nama Pengawas 1:</label>
                                <input type="text" name="pengawas_1" class="form-control form-control-sm" value="<?= htmlspecialchars($kop['pengawas_1'] ?? 'Pengawas Ruang 1, S.Pd.') ?>">
                            </div>
                            <div class="form-group mb-2">
                                <label class="font-weight-bold small">Nama Pengawas 2 (Opsional):</label>
                                <input type="text" name="pengawas_2" class="form-control form-control-sm" value="<?= htmlspecialchars($kop['pengawas_2'] ?? '') ?>">
                            </div>
                            <div class="row">
                                <div class="col-6">
                                    <div class="form-group mb-2">
                                        <label class="font-weight-bold small">Kota Tanda Tangan:</label>
                                        <input type="text" name="kota" class="form-control form-control-sm" value="<?= htmlspecialchars($kop['kota'] ?? 'Jakarta') ?>">
                                    </div>
                                </div>
                                <div class="col-6">
                                    <div class="form-group mb-2">
                                        <label class="font-weight-bold small">Tanggal Cetak:</label>
                                        <input type="text" name="tanggal" class="form-control form-control-sm" value="<?= htmlspecialchars($kop['tanggal'] ?? date('d-m-Y')) ?>">
                                    </div>
                                </div>
                            </div>
                        </div>
                        <div class="card-footer bg-light py-2 text-right">
                            <button type="submit" class="btn btn-secondary font-weight-bold shadow-sm">
                                <i class="fas fa-save mr-1"></i>Simpan Pengaturan Kop
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>

    </div>
</section>

