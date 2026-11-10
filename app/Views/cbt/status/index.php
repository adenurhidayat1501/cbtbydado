<?php
$title = $title ?? 'Live Status Siswa CBT — DADO CBT';
$jadwalList = $jadwalList ?? [];
$currentJadwal = $currentJadwal ?? null;
$kelasList = $kelasList ?? [];
$ruangList = $ruangList ?? [];
$sesiList = $sesiList ?? [];
$students = $students ?? [];
$selectedJadwal = $selectedJadwal ?? 0;
$selectedKelas = $selectedKelas ?? 0;
$selectedRuang = $selectedRuang ?? 0;
$selectedSesi = $selectedSesi ?? 0;
$search = $search ?? '';

// Counters
$countTotal = count($students);
$countSedang = 0;
$countSelesai = 0;
$countBelum = 0;
$countPelanggaran = 0;

foreach ($students as $s) {
    $st = (int)($s['status_ujian'] ?? 0);
    if ($st === 1) $countSedang++;
    elseif ($st === 2) $countSelesai++;
    else $countBelum++;

    if ((int)($s['pelanggaran_tab'] ?? 0) > 0) {
        $countPelanggaran++;
    }
}
?>

<div class="content-header">
    <div class="container-fluid">
        <div class="row mb-2">
            <div class="col-sm-6">
                <h1 class="m-0 text-dark font-weight-bold">
                    <i class="fas fa-desktop text-primary mr-2"></i>Live Monitoring Status Siswa
                </h1>
            </div>
            <div class="col-sm-6">
                <ol class="breadcrumb float-sm-right">
                    <li class="breadcrumb-item"><a href="/dashboard">Beranda</a></li>
                    <li class="breadcrumb-item"><a href="/cbt/jadwal">CBT</a></li>
                    <li class="breadcrumb-item active">Status Siswa</li>
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

        <?php if ($flash = get_flash('error')): ?>
            <div class="alert alert-danger alert-dismissible fade show" role="alert">
                <i class="fas fa-exclamation-triangle mr-2"></i><?= htmlspecialchars($flash) ?>
                <button type="button" class="close" data-dismiss="alert" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
        <?php endif; ?>

        <!-- Stat Cards -->
        <div class="row">
            <div class="col-lg-3 col-6">
                <div class="small-box bg-info shadow-sm">
                    <div class="inner">
                        <h3><?= $countTotal ?></h3>
                        <p>Total Peserta Terdaftar</p>
                    </div>
                    <div class="icon"><i class="fas fa-users"></i></div>
                </div>
            </div>
            <div class="col-lg-3 col-6">
                <div class="small-box bg-warning shadow-sm">
                    <div class="inner">
                        <h3 class="text-dark"><?= $countSedang ?></h3>
                        <p class="text-dark">Sedang Mengerjakan</p>
                    </div>
                    <div class="icon"><i class="fas fa-spinner fa-spin"></i></div>
                </div>
            </div>
            <div class="col-lg-3 col-6">
                <div class="small-box bg-success shadow-sm">
                    <div class="inner">
                        <h3><?= $countSelesai ?></h3>
                        <p>Ujian Telah Selesai</p>
                    </div>
                    <div class="icon"><i class="fas fa-check-circle"></i></div>
                </div>
            </div>
            <div class="col-lg-3 col-6">
                <div class="small-box bg-danger shadow-sm">
                    <div class="inner">
                        <h3><?= $countPelanggaran ?></h3>
                        <p>Siswa Terdeteksi Tab Blur</p>
                    </div>
                    <div class="icon"><i class="fas fa-exclamation-triangle"></i></div>
                </div>
            </div>
        </div>

        <!-- Filter Card -->
        <div class="card card-outline card-primary shadow-sm mb-4">
            <div class="card-header bg-white py-3">
                <h5 class="card-title font-weight-bold text-dark mb-0">
                    <i class="fas fa-filter mr-1"></i>Filter Jadwal, Ruang, dan Sesi Ujian
                </h5>
            </div>
            <form action="/cbt/status" method="GET">
                <div class="card-body">
                    <div class="row">
                        <div class="col-md-3 col-sm-6 mb-2">
                            <label class="font-weight-bold small">Jadwal Ujian: <span class="text-danger">*</span></label>
                            <select name="jadwal_id" class="form-control select2" onchange="this.form.submit()">
                                <?php foreach ($jadwalList as $j): ?>
                                    <option value="<?= $j['id_jadwal'] ?>" <?= (int)$selectedJadwal === (int)$j['id_jadwal'] ? 'selected' : '' ?>>
                                        [<?= htmlspecialchars($j['bank_kode']) ?>] <?= htmlspecialchars($j['nama_mapel']) ?>
                                    </option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                        <div class="col-md-2 col-sm-6 mb-2">
                            <label class="font-weight-bold small">Kelas:</label>
                            <select name="kelas_id" class="form-control" onchange="this.form.submit()">
                                <option value="0">-- Semua Kelas --</option>
                                <?php foreach ($kelasList as $k): ?>
                                    <option value="<?= $k['id_kelas'] ?>" <?= (int)$selectedKelas === (int)$k['id_kelas'] ? 'selected' : '' ?>>
                                        <?= htmlspecialchars($k['nama_kelas']) ?>
                                    </option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                        <div class="col-md-2 col-sm-6 mb-2">
                            <label class="font-weight-bold small">Ruang:</label>
                            <select name="ruang_id" class="form-control" onchange="this.form.submit()">
                                <option value="0">-- Semua Ruang --</option>
                                <?php foreach ($ruangList as $r): ?>
                                    <option value="<?= $r['id_ruang'] ?>" <?= (int)$selectedRuang === (int)$r['id_ruang'] ? 'selected' : '' ?>>
                                        <?= htmlspecialchars($r['nama_ruang']) ?>
                                    </option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                        <div class="col-md-2 col-sm-6 mb-2">
                            <label class="font-weight-bold small">Sesi:</label>
                            <select name="sesi_id" class="form-control" onchange="this.form.submit()">
                                <option value="0">-- Semua Sesi --</option>
                                <?php foreach ($sesiList as $s): ?>
                                    <option value="<?= $s['id_sesi'] ?>" <?= (int)$selectedSesi === (int)$s['id_sesi'] ? 'selected' : '' ?>>
                                        <?= htmlspecialchars($s['nama_sesi']) ?>
                                    </option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                        <div class="col-md-3 col-sm-12 mb-2">
                            <label class="font-weight-bold small">Cari Siswa / NIS:</label>
                            <div class="input-group">
                                <input type="text" name="search" class="form-control" value="<?= htmlspecialchars($search) ?>" placeholder="Nama / NIS...">
                                <div class="input-group-append">
                                    <button type="submit" class="btn btn-primary"><i class="fas fa-search"></i></button>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </form>
        </div>

        <!-- Monitoring Table Card -->
        <div class="card card-outline card-secondary shadow-sm mb-5">
            <div class="card-header bg-white py-3">
                <div class="d-flex justify-content-between align-items-center">
                    <h5 class="card-title font-weight-bold text-dark mb-0">
                        <i class="fas fa-tv mr-2 text-primary"></i>Tabel Pantauan Realtime Siswa
                        <?php if ($currentJadwal): ?>
                            <span class="badge badge-info ml-2 font-weight-normal"><?= htmlspecialchars($currentJadwal['bank_kode']) ?> - <?= htmlspecialchars($currentJadwal['nama_mapel']) ?></span>
                        <?php endif; ?>
                    </h5>
                    <div>
                        <button type="button" class="btn btn-sm btn-outline-primary" onclick="window.location.reload()">
                            <i class="fas fa-sync-alt mr-1"></i>Segarkan Data
                        </button>
                    </div>
                </div>
            </div>
            <div class="card-body p-0">
                <div class="table-responsive">
                    <table class="table table-bordered table-hover align-middle mb-0">
                        <thead class="bg-light">
                            <tr>
                                <th style="width: 40px;" class="text-center">No</th>
                                <th style="width: 140px;">Nomor / NIS</th>
                                <th>Nama Peserta & Kelas</th>
                                <th style="width: 130px;" class="text-center">Ruang / Sesi</th>
                                <th style="width: 130px;" class="text-center">Status Ujian</th>
                                <th style="width: 110px;" class="text-center">Sisa Waktu</th>
                                <th style="width: 100px;" class="text-center">Pelanggaran</th>
                                <th style="width: 90px;" class="text-center">Nilai</th>
                                <th style="width: 220px;" class="text-center">Aksi Pengawas</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php if (empty($students)): ?>
                                <tr>
                                    <td colspan="9" class="text-center py-5 text-muted">
                                        <i class="fas fa-user-slash fa-2x mb-2 d-block"></i>
                                        Tidak ada data siswa yang cocok dengan filter.
                                    </td>
                                </tr>
                            <?php else: ?>
                                <?php foreach ($students as $idx => $st): 
                                    $idSiswa = (int)$st['id_siswa'];
                                    $stUjian = (int)($st['status_ujian'] ?? 0);
                                    $remSec = (int)($st['remaining_seconds'] ?? 0);
                                    $pelanggaran = (int)($st['pelanggaran_tab'] ?? 0);
                                    $nilai = $st['total_nilai'] !== null ? number_format((float)$st['total_nilai'], 2) : '-';

                                    $remHour = floor($remSec / 3600);
                                    $remMin = floor(($remSec % 3600) / 60);
                                    $remSecOnly = $remSec % 60;
                                    $timeStr = sprintf('%02d:%02d:%02d', $remHour, $remMin, $remSecOnly);
                                ?>
                                    <tr>
                                        <td class="text-center font-weight-bold text-muted"><?= $idx + 1 ?></td>
                                        <td>
                                            <strong><?= htmlspecialchars($st['nomor_peserta'] ?? $st['nis']) ?></strong>
                                            <div class="small text-muted">NIS: <?= htmlspecialchars($st['nis']) ?></div>
                                        </td>
                                        <td>
                                            <div class="d-flex align-items-center">
                                                <img src="<?= !empty($st['foto']) ? upload_url('foto_siswa/' . $st['foto']) : asset('img/default-avatar.png') ?>" 
                                                     class="rounded-circle mr-2 border" style="width: 32px; height: 32px; object-fit: cover;">
                                                <div>
                                                    <strong class="text-dark"><?= htmlspecialchars($st['nama']) ?></strong>
                                                    <div class="small text-muted"><?= htmlspecialchars($st['nama_kelas'] ?? '-') ?></div>
                                                </div>
                                            </div>
                                        </td>
                                        <td class="text-center small">
                                            <div><span class="badge badge-light border"><?= htmlspecialchars($st['nama_ruang'] ?? '-') ?></span></div>
                                            <div><?= htmlspecialchars($st['nama_sesi'] ?? '-') ?></div>
                                        </td>
                                        <td class="text-center">
                                            <?php if ($stUjian === 0): ?>
                                                <span class="badge badge-secondary px-2 py-1">Belum Masuk</span>
                                            <?php elseif ($stUjian === 1): ?>
                                                <span class="badge badge-warning text-dark px-2 py-1"><i class="fas fa-spinner fa-spin mr-1"></i>Mengerjakan</span>
                                            <?php elseif ($stUjian === 2): ?>
                                                <span class="badge badge-success px-2 py-1"><i class="fas fa-check mr-1"></i>Selesai</span>
                                            <?php endif; ?>
                                        </td>
                                        <td class="text-center font-weight-bold">
                                            <?php if ($stUjian === 1): ?>
                                                <span class="badge badge-dark"><?= $timeStr ?></span>
                                            <?php else: ?>
                                                <span class="text-muted">-</span>
                                            <?php endif; ?>
                                        </td>
                                        <td class="text-center font-weight-bold">
                                            <?php if ($pelanggaran > 0): ?>
                                                <span class="badge badge-danger px-2 py-1" title="<?= $pelanggaran ?> kali terdeteksi berpindah tab/jendela">
                                                    <i class="fas fa-exclamation-triangle mr-1"></i><?= $pelanggaran ?>
                                                </span>
                                            <?php else: ?>
                                                <span class="badge badge-light border">0</span>
                                            <?php endif; ?>
                                        </td>
                                        <td class="text-center font-weight-bold text-primary">
                                            <?= $nilai ?>
                                        </td>
                                        <td class="text-center">
                                            <div class="btn-group btn-group-sm">
                                                <!-- 1. Reset Login -->
                                                <button type="button" class="btn btn-outline-info" title="Izinkan Siswa Login Kembali di Perangkat Baru" 
                                                        onclick="actionStatus('reset-login', <?= $selectedJadwal ?>, <?= $idSiswa ?>, '<?= htmlspecialchars(addslashes($st['nama'])) ?>')">
                                                    <i class="fas fa-sign-in-alt"></i> Login
                                                </button>

                                                <!-- 2. Reset Waktu -->
                                                <button type="button" class="btn btn-outline-warning" title="Atur Ulang / Tambah Waktu Ujian" 
                                                        onclick="openModalResetWaktu(<?= $selectedJadwal ?>, <?= $idSiswa ?>, '<?= htmlspecialchars(addslashes($st['nama'])) ?>')">
                                                    <i class="fas fa-history"></i> Waktu
                                                </button>

                                                <!-- 3. Paksa Selesai -->
                                                <button type="button" class="btn btn-outline-success" title="Paksa Akhiri Ujian Siswa Ini" 
                                                        onclick="actionStatus('paksa-selesai', <?= $selectedJadwal ?>, <?= $idSiswa ?>, '<?= htmlspecialchars(addslashes($st['nama'])) ?>')">
                                                    <i class="fas fa-stop"></i>
                                                </button>

                                                <!-- 4. Ulang Ujian -->
                                                <button type="button" class="btn btn-outline-danger" title="Bersihkan Semua Jawaban & Nilai (Ulang dari Awal)" 
                                                        onclick="actionStatus('ulang-ujian', <?= $selectedJadwal ?>, <?= $idSiswa ?>, '<?= htmlspecialchars(addslashes($st['nama'])) ?>')">
                                                    <i class="fas fa-redo"></i>
                                                </button>
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
</section>

<!-- Modal Reset Waktu -->
<div class="modal fade" id="modalResetWaktu" tabindex="-1" role="dialog" aria-hidden="true">
    <div class="modal-dialog" role="document">
        <form action="/cbt/status/reset-waktu" method="POST">
            <input type="hidden" name="id_jadwal" id="rw_id_jadwal" value="">
            <input type="hidden" name="id_siswa" id="rw_id_siswa" value="">
            <input type="hidden" name="redirect_url" value="<?= $_SERVER['REQUEST_URI'] ?? '/cbt/status' ?>">

            <div class="modal-content">
                <div class="modal-header bg-warning py-2">
                    <h5 class="modal-title font-weight-bold text-dark"><i class="fas fa-clock mr-1"></i>Atur / Tambah Waktu Ujian</h5>
                    <button type="button" class="close text-dark" data-dismiss="modal" aria-label="Close">
                        <span aria-hidden="true">&times;</span>
                    </button>
                </div>
                <div class="modal-body">
                    <p class="mb-2">Siswa: <strong id="rw_nama_siswa">-</strong></p>
                    <div class="form-group mb-3">
                        <label class="font-weight-bold">Pilih Aksi Reset Waktu:</label>
                        <select name="extra_minutes" class="form-control" id="selectExtraMinutes">
                            <option value="0">Reset Waktu Penuh Sesuai Durasi Asli Ujian</option>
                            <option value="10">+ Tambah 10 Menit Waktu Tambahan</option>
                            <option value="15">+ Tambah 15 Menit Waktu Tambahan</option>
                            <option value="30">+ Tambah 30 Menit Waktu Tambahan</option>
                        </select>
                    </div>
                </div>
                <div class="modal-footer py-2 justify-content-between">
                    <button type="button" class="btn btn-secondary font-weight-bold" data-dismiss="modal">Batal</button>
                    <button type="submit" class="btn btn-warning font-weight-bold px-4">Terapkan Waktu</button>
                </div>
            </div>
        </form>
    </div>
</div>

<script>
function actionStatus(actionType, idJadwal, idSiswa, namaSiswa) {
    var promptMsg = '';
    var targetUrl = '';

    if (actionType === 'reset-login') {
        promptMsg = 'Izinkan siswa "' + namaSiswa + '" untuk login kembali di perangkat baru?';
        targetUrl = '/cbt/status/reset-login';
    } else if (actionType === 'paksa-selesai') {
        promptMsg = 'Paksa akhiri ujian siswa "' + namaSiswa + '" dan hitung nilai sekarang?';
        targetUrl = '/cbt/status/paksa-selesai';
    } else if (actionType === 'ulang-ujian') {
        promptMsg = 'PERINGATAN: Tindakan ini akan MENGHAPUS seluruh jawaban dan nilai "' + namaSiswa + '" agar siswa dapat mengulang dari awal. Lanjutkan?';
        targetUrl = '/cbt/status/ulang-ujian';
    }

    if (confirm(promptMsg)) {
        var form = document.createElement('form');
        form.method = 'POST';
        form.action = targetUrl;

        var inputJadwal = document.createElement('input');
        inputJadwal.type = 'hidden';
        inputJadwal.name = 'id_jadwal';
        inputJadwal.value = idJadwal;
        form.appendChild(inputJadwal);

        var inputSiswa = document.createElement('input');
        inputSiswa.type = 'hidden';
        inputSiswa.name = 'id_siswa';
        inputSiswa.value = idSiswa;
        form.appendChild(inputSiswa);

        var inputRedirect = document.createElement('input');
        inputRedirect.type = 'hidden';
        inputRedirect.name = 'redirect_url';
        inputRedirect.value = window.location.href;
        form.appendChild(inputRedirect);

        document.body.appendChild(form);
        form.submit();
    }
}

function openModalResetWaktu(idJadwal, idSiswa, namaSiswa) {
    document.getElementById('rw_id_jadwal').value = idJadwal;
    document.getElementById('rw_id_siswa').value = idSiswa;
    document.getElementById('rw_nama_siswa').innerText = namaSiswa;
    $('#modalResetWaktu').modal('show');
}
</script>

