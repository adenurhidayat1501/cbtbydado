<?php
$title = $title ?? 'Atur Pengawas Ujian — DADO CBT';
$jadwal = $jadwal ?? [];
$ruangList = $ruangList ?? [];
$sesiList = $sesiList ?? [];
$guruList = $guruList ?? [];
$matrix = $matrix ?? [];
$idJadwal = (int)($jadwal['id_jadwal'] ?? 0);
?>

<div class="content-header">
    <div class="container-fluid">
        <div class="row mb-2">
            <div class="col-sm-6">
                <h1 class="m-0 text-dark font-weight-bold">
                    <i class="fas fa-users-cog text-primary mr-2"></i>Atur Pengawas Ujian
                </h1>
            </div>
            <div class="col-sm-6">
                <ol class="breadcrumb float-sm-right">
                    <li class="breadcrumb-item"><a href="/dashboard">Beranda</a></li>
                    <li class="breadcrumb-item"><a href="/cbt/jadwal">Jadwal Ujian</a></li>
                    <li class="breadcrumb-item active">Pengawas</li>
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

        <!-- Jadwal Info Banner -->
        <div class="card card-outline card-info shadow-sm mb-4">
            <div class="card-body py-3">
                <div class="d-flex flex-wrap justify-content-between align-items-center">
                    <div>
                        <h5 class="font-weight-bold text-dark mb-1">
                            <span class="badge badge-info mr-1"><?= htmlspecialchars($jadwal['kode_jenis'] ?? 'CBT') ?></span>
                            Bank Soal: <?= htmlspecialchars($jadwal['bank_kode'] ?? '-') ?> — <?= htmlspecialchars($jadwal['nama_mapel'] ?? '-') ?>
                        </h5>
                        <div class="text-muted small">
                            <i class="fas fa-calendar mr-1"></i>Pelaksanaan: <?= date('d M Y, H:i', strtotime($jadwal['tgl_mulai'])) ?> s/d <?= date('d M Y, H:i', strtotime($jadwal['tgl_selesai'])) ?>
                            | <i class="fas fa-clock mr-1"></i>Durasi: <?= (int)$jadwal['durasi_ujian'] ?> Menit (Jam Ke-<?= (int)($jadwal['jam_ke'] ?? 1) ?>)
                        </div>
                    </div>
                    <div>
                        <a href="/cbt/jadwal" class="btn btn-secondary btn-sm shadow-sm">
                            <i class="fas fa-arrow-left mr-1"></i>Kembali ke Jadwal
                        </a>
                    </div>
                </div>
            </div>
        </div>

        <!-- Proctor Assignment Matrix: Ruang vs Sesi -->
        <div class="card card-outline card-primary shadow-sm mb-5">
            <div class="card-header bg-white py-3">
                <h3 class="card-title font-weight-bold text-dark mb-0">
                    <i class="fas fa-th mr-1"></i>Matriks Penetapan Pengawas (Ruang vs Sesi)
                </h3>
            </div>
            <div class="card-body">
                <p class="text-muted small mb-3">
                    <i class="fas fa-info-circle mr-1"></i>
                    Hanya guru yang dipilih yang akan memiliki hak akses sebagai pengawas di menu Ruang Pengawas. Klik tombol <strong>Atur</strong> pada masing-masing kotak ruang dan sesi untuk menentukan guru pengawas.
                </p>

                <?php if (empty($ruangList) || empty($sesiList)): ?>
                    <div class="alert alert-warning">
                        <i class="fas fa-exclamation-circle mr-1"></i>
                        Data Master Ruang atau Master Sesi belum dibuat. Silakan atur terlebih dahulu di menu <strong>Master CBT</strong>.
                    </div>
                <?php else: ?>
                    <div class="table-responsive">
                        <table class="table table-bordered text-center align-middle">
                            <thead class="thead-light">
                                <tr>
                                    <th style="width: 180px;" class="align-middle bg-light">Ruang Ujian</th>
                                    <?php foreach ($sesiList as $sesi): ?>
                                        <th class="align-middle">
                                            <strong><?= htmlspecialchars($sesi['nama_sesi']) ?></strong>
                                            <div class="small text-muted font-weight-normal">
                                                (<?= substr($sesi['waktu_mulai'], 0, 5) ?> - <?= substr($sesi['waktu_akhir'], 0, 5) ?>)
                                            </div>
                                        </th>
                                    <?php endforeach; ?>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach ($ruangList as $ruang): 
                                    $idR = (int)$ruang['id_ruang'];
                                ?>
                                    <tr>
                                        <td class="text-left font-weight-bold bg-light align-middle">
                                            <i class="fas fa-door-open text-primary mr-1"></i>
                                            <?= htmlspecialchars($ruang['nama_ruang']) ?>
                                            <span class="badge badge-light border ml-1"><?= htmlspecialchars($ruang['kode_ruang']) ?></span>
                                        </td>
                                        <?php foreach ($sesiList as $sesi): 
                                            $idS = (int)$sesi['id_sesi'];
                                            $cellData = $matrix[$idR][$idS] ?? ['guru_ids' => [], 'guru_names' => []];
                                            $assignedNames = $cellData['guru_names'];
                                            $assignedIds = $cellData['guru_ids'];
                                        ?>
                                            <td class="align-middle p-2">
                                                <div class="assigned-box mb-2" style="min-height: 48px;">
                                                    <?php if (empty($assignedNames)): ?>
                                                        <span class="text-muted font-italic small d-block py-2">-</span>
                                                    <?php else: ?>
                                                        <?php foreach ($assignedNames as $gName): ?>
                                                            <span class="badge badge-success px-2 py-1 mb-1 d-block text-truncate" title="<?= htmlspecialchars($gName) ?>">
                                                                <i class="fas fa-user-check mr-1"></i><?= htmlspecialchars($gName) ?>
                                                            </span>
                                                        <?php endforeach; ?>
                                                    <?php endif; ?>
                                                </div>
                                                <button type="button" class="btn btn-xs btn-outline-primary font-weight-bold" 
                                                        onclick="openModalPengawas(<?= $idR ?>, '<?= htmlspecialchars(addslashes($ruang['nama_ruang'])) ?>', <?= $idS ?>, '<?= htmlspecialchars(addslashes($sesi['nama_sesi'])) ?>', <?= htmlspecialchars(json_encode($assignedIds)) ?>)">
                                                    <i class="fas fa-user-edit mr-1"></i>Atur Pengawas
                                                </button>
                                            </td>
                                        <?php endforeach; ?>
                                    </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                    </div>
                <?php endif; ?>
            </div>
        </div>

    </div>
</section>

<!-- Modal Atur Pengawas -->
<div class="modal fade" id="modalPengawas" tabindex="-1" role="dialog" aria-labelledby="modalPengawasLabel" aria-hidden="true">
    <div class="modal-dialog" role="document">
        <form action="/cbt/jadwal/pengawas/<?= $idJadwal ?>/save" method="POST">
            <input type="hidden" name="id_ruang" id="modal_id_ruang" value="">
            <input type="hidden" name="id_sesi" id="modal_id_sesi" value="">

            <div class="modal-content">
                <div class="modal-header bg-primary text-white">
                    <h5 class="modal-title font-weight-bold" id="modalPengawasLabel">
                        <i class="fas fa-user-shield mr-2"></i>Tetapkan Guru Pengawas
                    </h5>
                    <button type="button" class="close text-white" data-dismiss="modal" aria-label="Close">
                        <span aria-hidden="true">&times;</span>
                    </button>
                </div>
                <div class="modal-body">
                    <div class="alert alert-light border small mb-3">
                        <div><strong>Ruang:</strong> <span id="label_nama_ruang">-</span></div>
                        <div><strong>Sesi:</strong> <span id="label_nama_sesi">-</span></div>
                    </div>

                    <div class="form-group">
                        <label class="font-weight-bold">Pilih Guru Pengawas:</label>
                        <p class="text-muted small">Centang satu atau lebih guru yang bertugas mengawasi ruangan ini.</p>
                        <div class="border rounded p-2" style="max-height: 250px; overflow-y: auto;">
                            <?php foreach ($guruList as $guru): 
                                $gId = (int)$guru['id_guru'];
                            ?>
                                <div class="custom-control custom-checkbox mb-2">
                                    <input type="checkbox" name="guru_ids[]" value="<?= $gId ?>" class="custom-control-input check-guru" id="guru_<?= $gId ?>">
                                    <label class="custom-control-label font-weight-normal" for="guru_<?= $gId ?>">
                                        <?= htmlspecialchars($guru['nama_guru']) ?>
                                    </label>
                                </div>
                            <?php endforeach; ?>
                        </div>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-dismiss="modal">Batal</button>
                    <button type="submit" class="btn btn-primary font-weight-bold">
                        <i class="fas fa-save mr-1"></i>Simpan Pengawas
                    </button>
                </div>
            </div>
        </form>
    </div>
</div>

<script>
function openModalPengawas(idRuang, namaRuang, idSesi, namaSesi, currentGuruIds) {
    document.getElementById('modal_id_ruang').value = idRuang;
    document.getElementById('modal_id_sesi').value = idSesi;
    document.getElementById('label_nama_ruang').innerText = namaRuang;
    document.getElementById('label_nama_sesi').innerText = namaSesi;

    // Reset checkboxes
    document.querySelectorAll('.check-guru').forEach(function(cb) {
        cb.checked = false;
    });

    // Check currently assigned gurus
    if (Array.isArray(currentGuruIds)) {
        currentGuruIds.forEach(function(gId) {
            var el = document.getElementById('guru_' + gId);
            if (el) el.checked = true;
        });
    }

    $('#modalPengawas').modal('show');
}
</script>
