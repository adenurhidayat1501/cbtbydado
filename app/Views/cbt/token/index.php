<?php
$title = $title ?? 'Token Ujian CBT — DADO CBT';
$tokenData = $tokenData ?? [];
$currentToken = $tokenData['token'] ?? '------';
$autoRefresh = (int)($tokenData['auto'] ?? 0);
$intervalMinutes = (int)($tokenData['jarak'] ?? 15);
$lastUpdated = $tokenData['updated_at'] ?? date('Y-m-d H:i:s');
?>

<div class="content-header">
    <div class="container-fluid">
        <div class="row mb-2">
            <div class="col-sm-6">
                <h1 class="m-0 text-dark font-weight-bold">
                    <i class="fas fa-key text-primary mr-2"></i>Token Ujian CBT
                </h1>
            </div>
            <div class="col-sm-6">
                <ol class="breadcrumb float-sm-right">
                    <li class="breadcrumb-item"><a href="/dashboard">Beranda</a></li>
                    <li class="breadcrumb-item active">Token Ujian</li>
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

        <div class="row justify-content-center">
            <!-- Token Display Card -->
            <div class="col-lg-6 col-md-8">
                <div class="card card-outline card-primary shadow-sm mb-4 text-center">
                    <div class="card-header bg-white py-3">
                        <h4 class="card-title font-weight-bold text-dark w-100 mb-0">
                            <i class="fas fa-lock mr-2 text-primary"></i>TOKEN UJIAN AKTIF SAAT INI
                        </h4>
                    </div>
                    <div class="card-body py-5">
                        <p class="text-muted mb-3">Bagikan token 6 karakter berikut kepada peserta ujian saat ujian dimulai:</p>
                        
                        <div class="p-3 my-3 bg-light rounded border border-primary d-inline-block shadow-sm" style="min-width: 280px;">
                            <span id="tokenDisplay" class="font-weight-bold text-primary tracking-widest" style="font-size: 3.5rem; letter-spacing: 12px; font-family: monospace;">
                                <?= htmlspecialchars($currentToken) ?>
                            </span>
                        </div>

                        <div class="text-muted small mt-2">
                            <i class="fas fa-history mr-1"></i>Terakhir diperbarui: 
                            <span id="lastUpdatedDisplay" class="font-weight-bold"><?= date('d M Y, H:i:s', strtotime($lastUpdated)) ?></span>
                        </div>

                        <div class="mt-4">
                            <button type="button" class="btn btn-primary btn-lg font-weight-bold px-4 shadow-sm" id="btnGenerateToken" onclick="generateNewToken()">
                                <i class="fas fa-sync-alt mr-2" id="iconSync"></i>RILIS TOKEN BARU
                            </button>
                        </div>
                    </div>
                </div>

                <!-- Token Refresh Configuration -->
                <div class="card card-outline card-secondary shadow-sm mb-5">
                    <div class="card-header bg-white py-3">
                        <h5 class="card-title font-weight-bold text-dark mb-0">
                            <i class="fas fa-cogs mr-1"></i>Pengaturan Auto-Refresh Token
                        </h5>
                    </div>
                    <form action="/cbt/token/setting" method="POST">
                        <div class="card-body">
                            <div class="form-group mb-3">
                                <div class="custom-control custom-switch">
                                    <input type="checkbox" class="custom-control-input" id="switchAuto" name="auto" value="1" <?= $autoRefresh === 1 ? 'checked' : '' ?>>
                                    <label class="custom-control-label font-weight-bold" for="switchAuto">
                                        Aktifkan Pergantian Token Otomatis (Auto-Refresh)
                                    </label>
                                </div>
                                <small class="text-muted">Jika diaktifkan, server akan otomatis merilis kombinasi token baru sesuai interval waktu yang ditentukan.</small>
                            </div>

                            <div class="form-group mb-3">
                                <label class="font-weight-bold">Interval Waktu Auto-Refresh (Menit):</label>
                                <div class="input-group" style="max-width: 220px;">
                                    <input type="number" name="jarak" class="form-control font-weight-bold text-center" value="<?= $intervalMinutes ?>" min="5" max="120" required>
                                    <div class="input-group-append">
                                        <span class="input-group-text bg-light">Menit</span>
                                    </div>
                                </div>
                                <small class="text-muted">Rekomendasi: 15 sampai 30 menit sekali.</small>
                            </div>
                        </div>
                        <div class="card-footer bg-light text-right py-3">
                            <button type="submit" class="btn btn-secondary font-weight-bold shadow-sm">
                                <i class="fas fa-save mr-1"></i>Simpan Pengaturan
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>

    </div>
</section>

<script>
function generateNewToken() {
    var btn = document.getElementById('btnGenerateToken');
    var icon = document.getElementById('iconSync');
    btn.disabled = true;
    icon.classList.add('fa-spin');

    fetch('/cbt/token/generate', {
        method: 'POST',
        headers: {
            'X-Requested-With': 'XMLHttpRequest'
        }
    })
    .then(function(res) { return res.json(); })
    .then(function(data) {
        btn.disabled = false;
        icon.classList.remove('fa-spin');
        if (data.status === 'success') {
            document.getElementById('tokenDisplay').innerText = data.token;
            document.getElementById('lastUpdatedDisplay').innerText = data.updated_at;
            alert(data.message || 'Token baru berhasil dirilis.');
        } else {
            alert('Gagal merefresh token.');
        }
    })
    .catch(function(err) {
        btn.disabled = false;
        icon.classList.remove('fa-spin');
        // Fallback standard submit
        window.location.href = '/cbt/token/generate';
    });
}
</script>
