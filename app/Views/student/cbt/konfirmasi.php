<?php
$title = $title ?? 'Konfirmasi Tes — DADO CBT';
$siswa = $siswa ?? [];
$jadwal = $jadwal ?? [];
$session = $session ?? null;
$idJadwal = (int)($jadwal['id_jadwal'] ?? 0);
$wajibToken = ((int)($jadwal['token'] ?? 1)) === 1;
?>

<div class="content-header">
    <div class="container-fluid">
        <div class="row mb-2">
            <div class="col-sm-6">
                <h1 class="m-0 text-dark font-weight-bold">
                    <i class="fas fa-check-double text-primary mr-2"></i>Konfirmasi Pelaksanaan Ujian
                </h1>
            </div>
            <div class="col-sm-6">
                <ol class="breadcrumb float-sm-right">
                    <li class="breadcrumb-item"><a href="/student/dashboard">Beranda</a></li>
                    <li class="breadcrumb-item"><a href="/student/cbt">Portal Ujian</a></li>
                    <li class="breadcrumb-item active">Konfirmasi</li>
                </ol>
            </div>
        </div>
    </div>
</div>

<section class="content">
    <div class="container-fluid">

        <?php if ($flash = get_flash('error')): ?>
            <div class="alert alert-danger alert-dismissible fade show" role="alert">
                <i class="fas fa-exclamation-triangle mr-2"></i><?= htmlspecialchars($flash) ?>
                <button type="button" class="close" data-dismiss="alert" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
        <?php endif; ?>

        <div class="row justify-content-center">
            <div class="col-lg-8 col-md-10">
                <div class="card card-outline card-primary shadow-sm mb-5">
                    <div class="card-header bg-white py-3">
                        <h4 class="card-title font-weight-bold text-primary mb-0">
                            <i class="fas fa-info-circle mr-2"></i>Rincian Jadwal & Lembar Persetujuan
                        </h4>
                    </div>

                    <form action="/student/cbt/start/<?= $idJadwal ?>" method="POST" id="formKonfirmasi">
                        <div class="card-body">
                            <!-- Student & Exam Details Table -->
                            <div class="table-responsive mb-4">
                                <table class="table table-bordered table-striped">
                                    <tbody>
                                        <tr>
                                            <th style="width: 200px;" class="bg-light text-muted">Nama Peserta</th>
                                            <td class="font-weight-bold text-dark"><?= htmlspecialchars($siswa['nama'] ?? '-') ?></td>
                                        </tr>
                                        <tr>
                                            <th class="bg-light text-muted">NIS / NISN</th>
                                            <td><?= htmlspecialchars($siswa['nis'] ?? '-') ?> / <?= htmlspecialchars($siswa['nisn'] ?? '-') ?></td>
                                        </tr>
                                        <tr>
                                            <th class="bg-light text-muted">Mata Pelajaran</th>
                                            <td class="font-weight-bold text-primary"><?= htmlspecialchars($jadwal['nama_mapel'] ?? '-') ?> (<?= htmlspecialchars($jadwal['bank_kode'] ?? '-') ?>)</td>
                                        </tr>
                                        <tr>
                                            <th class="bg-light text-muted">Jenis Ujian</th>
                                            <td><span class="badge badge-info"><?= htmlspecialchars($jadwal['kode_jenis'] ?? ($jadwal['nama_jenis'] ?? 'CBT')) ?></span></td>
                                        </tr>
                                        <tr>
                                            <th class="bg-light text-muted">Alokasi Waktu</th>
                                            <td>Jam Ke-<?= (int)($jadwal['jam_ke'] ?? 1) ?></td>
                                        </tr>
                                        <tr>
                                            <th class="bg-light text-muted">Durasi Ujian</th>
                                            <td class="font-weight-bold text-danger"><?= (int)$jadwal['durasi_ujian'] ?> Menit</td>
                                        </tr>
                                    </tbody>
                                </table>
                            </div>

                            <!-- Examination Rules & Anti-Cheat Callout -->
                            <div class="callout callout-warning bg-light">
                                <h6 class="font-weight-bold text-warning text-dark">
                                    <i class="fas fa-shield-alt mr-1"></i>Tata Tertib & Ketentuan Sistem Anti-Cheat:
                                </h6>
                                <ul class="mb-1 pl-3 small text-muted">
                                    <li>Ujian ini terintegrasi dengan <strong>Sistem Deteksi Pelanggaran Jendela (Anti-Cheat)</strong>.</li>
                                    <li>Dilarang membuka tab baru, browser lain, berpindah jendela, atau meminimize aplikasi ujian.</li>
                                    <li>Setiap aksi meninggalkan jendela ujian akan tercatat sebagai pelanggaran pada layar pengawas.</li>
                                    <li>Timer ujian tersinkronisasi otomatis dengan server. Me-refresh browser <strong>tidak akan menambah</strong> waktu ujian Anda.</li>
                                    <li>Gunakan tombol <strong>Zoom (A+, A, A-)</strong> di lembar soal untuk memperbesar ukuran teks jika diperlukan.</li>
                                </ul>
                            </div>

                            <!-- Token Input Form -->
                            <?php if ($wajibToken): ?>
                                <div class="form-group text-center my-4 p-3 bg-light rounded border border-primary">
                                    <label class="font-weight-bold text-dark d-block mb-2" style="font-size: 1.1rem;">
                                        <i class="fas fa-key text-primary mr-1"></i>Masukkan Token Ujian: <span class="text-danger">*</span>
                                    </label>
                                    <input type="text" name="token" id="inputToken" class="form-control form-control-lg text-center font-weight-bold text-primary mx-auto" 
                                           style="max-width: 260px; font-size: 1.8rem; letter-spacing: 6px; text-transform: uppercase;" 
                                           placeholder="------" maxlength="6" autocomplete="off" required>
                                    <small class="text-muted d-block mt-2">Token 6 digit diberikan oleh Pengawas Ruang saat ujian dimulai.</small>
                                </div>
                            <?php else: ?>
                                <input type="hidden" name="token" value="BYPASS">
                            <?php endif; ?>

                            <div class="custom-control custom-checkbox text-center mb-2">
                                <input type="checkbox" class="custom-control-input" id="checkSetuju" required>
                                <label class="custom-control-label font-weight-bold text-dark" for="checkSetuju">
                                    Saya telah membaca dan bersedia menaati seluruh tata tertib ujian di atas.
                                </label>
                            </div>
                        </div>

                        <div class="card-footer bg-white text-center py-3">
                            <a href="/student/cbt" class="btn btn-secondary px-4 mr-2">
                                <i class="fas fa-arrow-left mr-1"></i>Batal
                            </a>
                            <button type="submit" class="btn btn-primary btn-lg font-weight-bold px-5 shadow-sm" id="btnStartExam">
                                <i class="fas fa-play mr-2"></i>MULAI MENGERJAKAN
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>

    </div>
</section>

<script>
document.addEventListener('DOMContentLoaded', function() {
    var checkSetuju = document.getElementById('checkSetuju');
    var btnStart = document.getElementById('btnStartExam');
    
    btnStart.disabled = !checkSetuju.checked;
    checkSetuju.addEventListener('change', function() {
        btnStart.disabled = !this.checked;
    });

    var tokenInput = document.getElementById('inputToken');
    if (tokenInput) {
        tokenInput.focus();
        tokenInput.addEventListener('input', function() {
            this.value = this.value.toUpperCase();
        });
    }
});
</script>

