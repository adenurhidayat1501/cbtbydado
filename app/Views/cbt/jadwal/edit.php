<?php
$title = $title ?? 'Edit Jadwal Ujian — DADO CBT';
$jadwal = $jadwal ?? [];
$bankList = $bankList ?? [];
$jenisList = $jenisList ?? [];
$isOngoing = $isOngoing ?? false;
$idJadwal = (int)($jadwal['id_jadwal'] ?? 0);

// Convert SQL datetime to datetime-local input format
$formatDateForInput = function($dt) {
    if (empty($dt)) return '';
    $timestamp = strtotime($dt);
    return date('Y-m-d\TH:i', $timestamp);
};
?>

<div class="content-header">
    <div class="container-fluid">
        <div class="row mb-2">
            <div class="col-sm-6">
                <h1 class="m-0 text-dark font-weight-bold">
                    <i class="fas fa-edit text-primary mr-2"></i>Edit Jadwal Ujian
                </h1>
            </div>
            <div class="col-sm-6">
                <ol class="breadcrumb float-sm-right">
                    <li class="breadcrumb-item"><a href="/dashboard">Beranda</a></li>
                    <li class="breadcrumb-item"><a href="/cbt/jadwal">Jadwal Ujian</a></li>
                    <li class="breadcrumb-item active">Edit</li>
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

        <?php if ($isOngoing): ?>
            <div class="alert alert-warning">
                <i class="fas fa-lock mr-2"></i><strong>Peringatan Ujian Berlangsung:</strong> Jadwal ujian ini telah diikuti oleh siswa aktif. Sejumlah opsi waktu atau bank soal mungkin berdampak langsung pada sesi ujian berjalan.
            </div>
        <?php endif; ?>

        <div class="card card-outline card-warning shadow-sm mb-5">
            <div class="card-header bg-white py-3">
                <h3 class="card-title font-weight-bold text-dark mb-0">
                    <i class="fas fa-edit mr-1"></i>Perbarui Jadwal Ujian
                </h3>
            </div>
            <form action="/cbt/jadwal/update/<?= $idJadwal ?>" method="POST">
                <div class="card-body">
                    <div class="row">
                        <!-- Left Column: Bank Soal & Jenis -->
                        <div class="col-md-6">
                            <div class="form-group mb-3">
                                <label class="font-weight-bold">Bank Soal: <span class="text-danger">*</span></label>
                                <select name="id_bank" class="form-control select2" required>
                                    <?php foreach ($bankList as $bank): ?>
                                        <option value="<?= $bank['id_bank'] ?>" <?= (int)$jadwal['id_bank'] === (int)$bank['id_bank'] ? 'selected' : '' ?>>
                                            [<?= htmlspecialchars($bank['bank_kode']) ?>] <?= htmlspecialchars($bank['nama_mapel']) ?> (Kls <?= (int)$bank['bank_level'] ?>) - <?= htmlspecialchars($bank['nama_guru']) ?>
                                        </option>
                                    <?php endforeach; ?>
                                </select>
                            </div>

                            <div class="form-group mb-3">
                                <label class="font-weight-bold">Jenis Ujian: <span class="text-danger">*</span></label>
                                <select name="id_jenis" class="form-control" required>
                                    <?php foreach ($jenisList as $j): ?>
                                        <option value="<?= $j['id_jenis'] ?>" <?= (int)$jadwal['id_jenis'] === (int)$j['id_jenis'] ? 'selected' : '' ?>>
                                            <?= htmlspecialchars($j['kode_jenis']) ?> - <?= htmlspecialchars($j['nama_jenis']) ?>
                                        </option>
                                    <?php endforeach; ?>
                                </select>
                            </div>

                            <div class="row">
                                <div class="col-md-6">
                                    <div class="form-group mb-3">
                                        <label class="font-weight-bold">Alokasi Waktu: <span class="text-danger">*</span></label>
                                        <div class="input-group">
                                            <div class="input-group-prepend">
                                                <span class="input-group-text bg-light font-weight-bold">Jam Ke-</span>
                                            </div>
                                            <input type="number" name="jam_ke" class="form-control text-center font-weight-bold" value="<?= (int)($jadwal['jam_ke'] ?? 1) ?>" min="1" max="10" required>
                                        </div>
                                    </div>
                                </div>
                                <div class="col-md-6">
                                    <div class="form-group mb-3">
                                        <label class="font-weight-bold">Durasi Ujian: <span class="text-danger">*</span></label>
                                        <div class="input-group">
                                            <input type="number" name="durasi_ujian" class="form-control text-center font-weight-bold" value="<?= (int)$jadwal['durasi_ujian'] ?>" min="5" max="300" required>
                                            <div class="input-group-append">
                                                <span class="input-group-text bg-light">Menit</span>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>

                            <div class="form-group mb-3">
                                <label class="font-weight-bold">Jeda Istirahat / Jarak Sesi (Menit):</label>
                                <div class="input-group">
                                    <input type="number" name="jarak" class="form-control text-center" value="<?= (int)($jadwal['jarak'] ?? 0) ?>" min="0" max="120">
                                    <div class="input-group-append">
                                        <span class="input-group-text bg-light">Menit</span>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- Right Column: Tanggal & CBT Configuration Switches -->
                        <div class="col-md-6">
                            <div class="row">
                                <div class="col-md-6">
                                    <div class="form-group mb-3">
                                        <label class="font-weight-bold">Waktu Mulai: <span class="text-danger">*</span></label>
                                        <input type="datetime-local" name="tgl_mulai" class="form-control" value="<?= $formatDateForInput($jadwal['tgl_mulai']) ?>" required>
                                    </div>
                                </div>
                                <div class="col-md-6">
                                    <div class="form-group mb-3">
                                        <label class="font-weight-bold">Waktu Selesai: <span class="text-danger">*</span></label>
                                        <input type="datetime-local" name="tgl_selesai" class="form-control" value="<?= $formatDateForInput($jadwal['tgl_selesai']) ?>" required>
                                    </div>
                                </div>
                            </div>

                            <div class="card card-light border p-3">
                                <h6 class="font-weight-bold text-dark mb-3"><i class="fas fa-sliders-h mr-1"></i>Opsi Keamanan & Tampilan CBT:</h6>
                                
                                <div class="row">
                                    <div class="col-sm-6 mb-2">
                                        <div class="custom-control custom-switch">
                                            <input type="checkbox" class="custom-control-input" id="swAcakSoal" name="acak_soal" value="1" <?= (int)$jadwal['acak_soal'] === 1 ? 'checked' : '' ?>>
                                            <label class="custom-control-label" for="swAcakSoal">Acak Butir Soal</label>
                                        </div>
                                    </div>
                                    <div class="col-sm-6 mb-2">
                                        <div class="custom-control custom-switch">
                                            <input type="checkbox" class="custom-control-input" id="swAcakOpsi" name="acak_opsi" value="1" <?= (int)$jadwal['acak_opsi'] === 1 ? 'checked' : '' ?>>
                                            <label class="custom-control-label" for="swAcakOpsi">Acak Pilihan Opsi</label>
                                        </div>
                                    </div>
                                    <div class="col-sm-6 mb-2">
                                        <div class="custom-control custom-switch">
                                            <input type="checkbox" class="custom-control-input" id="swToken" name="token" value="1" <?= (int)$jadwal['token'] === 1 ? 'checked' : '' ?>>
                                            <label class="custom-control-label" for="swToken">Wajib Token Ujian</label>
                                        </div>
                                    </div>
                                    <div class="col-sm-6 mb-2">
                                        <div class="custom-control custom-switch">
                                            <input type="checkbox" class="custom-control-input" id="swHasil" name="hasil_tampil" value="1" <?= (int)$jadwal['hasil_tampil'] === 1 ? 'checked' : '' ?>>
                                            <label class="custom-control-label" for="swHasil">Tampilkan Nilai ke Siswa</label>
                                        </div>
                                    </div>
                                    <div class="col-sm-6 mb-2">
                                        <div class="custom-control custom-switch">
                                            <input type="checkbox" class="custom-control-input" id="swReset" name="reset_login" value="1" <?= (int)$jadwal['reset_login'] === 1 ? 'checked' : '' ?>>
                                            <label class="custom-control-label" for="swReset">Izinkan Reset Login</label>
                                        </div>
                                    </div>
                                    <div class="col-sm-6 mb-2">
                                        <div class="custom-control custom-switch">
                                            <input type="checkbox" class="custom-control-input" id="swStatus" name="status" value="1" <?= (int)$jadwal['status'] === 1 ? 'checked' : '' ?>>
                                            <label class="custom-control-label font-weight-bold text-success" for="swStatus">Status Aktif</label>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="card-footer bg-light py-3 d-flex justify-content-between">
                    <a href="/cbt/jadwal" class="btn btn-secondary shadow-sm">
                        <i class="fas fa-arrow-left mr-1"></i>Batal & Kembali
                    </a>
                    <button type="submit" class="btn btn-warning font-weight-bold shadow-sm px-4">
                        <i class="fas fa-save mr-1"></i>Perbarui Jadwal Ujian
                    </button>
                </div>
            </form>
        </div>

    </div>
</section>
