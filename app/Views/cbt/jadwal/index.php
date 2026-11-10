<?php
$title = $title ?? 'Jadwal Ujian CBT — DADO CBT';
$schedules = $schedules ?? [];
$jenisList = $jenisList ?? [];
$tp = $tp ?? [];
$smt = $smt ?? [];
$role = $role ?? 'admin';
?>

<div class="content-header">
    <div class="container-fluid">
        <div class="row mb-2">
            <div class="col-sm-6">
                <h1 class="m-0 text-dark font-weight-bold">
                    <i class="fas fa-calendar-alt text-primary mr-2"></i>Jadwal Ujian CBT
                </h1>
            </div>
            <div class="col-sm-6">
                <ol class="breadcrumb float-sm-right">
                    <li class="breadcrumb-item"><a href="/dashboard">Beranda</a></li>
                    <li class="breadcrumb-item active">Jadwal Ujian</li>
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

        <div class="card card-outline card-primary shadow-sm mb-4">
            <div class="card-header bg-white py-3">
                <div class="d-flex justify-content-between align-items-center">
                    <h3 class="card-title font-weight-bold text-dark mb-0">
                        <i class="fas fa-clock mr-1"></i>Daftar Jadwal Ujian & Alokasi Waktu
                    </h3>
                    <div>
                        <a href="/cbt/jadwal/create" class="btn btn-primary font-weight-bold shadow-sm">
                            <i class="fas fa-plus mr-1"></i>Tambah Jadwal Ujian
                        </a>
                    </div>
                </div>
            </div>
            <div class="card-body">
                <div class="table-responsive">
                    <table class="table table-bordered table-striped table-hover align-middle datatable" id="tableJadwal">
                        <thead class="thead-light">
                            <tr>
                                <th style="width: 40px;" class="text-center">No</th>
                                <th style="width: 90px;" class="text-center">Jenis</th>
                                <th>Bank Soal & Mata Pelajaran</th>
                                <th style="width: 130px;" class="text-center">Alokasi Waktu</th>
                                <th style="width: 170px;">Rentang Waktu Pelaksanaan</th>
                                <th style="width: 90px;" class="text-center">Durasi</th>
                                <th style="width: 140px;">Pengawas Ujian</th>
                                <th style="width: 80px;" class="text-center">Status</th>
                                <th style="width: 140px;" class="text-center">Aksi</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php if (empty($schedules)): ?>
                                <tr>
                                    <td colspan="9" class="text-center py-4 text-muted">
                                        <i class="fas fa-calendar-times fa-2x mb-2 d-block"></i>
                                        Belum ada jadwal ujian yang dibuat pada periode ini.
                                    </td>
                                </tr>
                            <?php else: ?>
                                <?php foreach ($schedules as $idx => $item): 
                                    $idJadwal = (int)$item['id_jadwal'];
                                    $jamKe = (int)($item['jam_ke'] ?? 1);
                                    
                                    // Safeguard pengawas formatting to prevent 'undefined' bug
                                    $pengawasSummary = '-';
                                    if (!empty($item['pengawas_summary'])) {
                                        $pengawasSummary = $item['pengawas_summary'];
                                    } else {
                                        $pengawasModel = new \App\Models\CbtPengawas();
                                        $pengawasSummary = $pengawasModel->safeFormatProctors($idJadwal);
                                    }
                                ?>
                                    <tr>
                                        <td class="text-center font-weight-bold text-muted"><?= $idx + 1 ?></td>
                                        <td class="text-center">
                                            <span class="badge badge-info px-2 py-1 font-weight-bold">
                                                <?= htmlspecialchars($item['kode_jenis'] ?? ($item['nama_jenis'] ?? 'CBT')) ?>
                                            </span>
                                        </td>
                                        <td>
                                            <span class="badge badge-primary mr-1"><?= htmlspecialchars($item['bank_kode'] ?? '-') ?></span>
                                            <strong class="text-dark"><?= htmlspecialchars($item['nama_mapel'] ?? '-') ?></strong>
                                            <div class="small text-muted mt-1">
                                                Level: Kelas <?= (int)($item['bank_level'] ?? 7) ?> | Guru: <?= htmlspecialchars($item['nama_guru'] ?? '-') ?>
                                            </div>
                                        </td>
                                        <td class="text-center">
                                            <span class="badge badge-secondary px-2 py-1">
                                                <i class="fas fa-hourglass-start mr-1"></i>Jam Ke-<?= $jamKe ?>
                                            </span>
                                            <?php if (!empty($item['jarak'])): ?>
                                                <div class="small text-muted mt-1">Jeda: <?= (int)$item['jarak'] ?> mnt</div>
                                            <?php endif; ?>
                                        </td>
                                        <td>
                                            <div class="small">
                                                <div><i class="fas fa-play text-success mr-1"></i><strong>Mulai:</strong> <?= date('d M Y, H:i', strtotime($item['tgl_mulai'])) ?></div>
                                                <div><i class="fas fa-stop text-danger mr-1"></i><strong>Selesai:</strong> <?= date('d M Y, H:i', strtotime($item['tgl_selesai'])) ?></div>
                                            </div>
                                        </td>
                                        <td class="text-center font-weight-bold">
                                            <span class="text-primary"><?= (int)$item['durasi_ujian'] ?></span> mnt
                                        </td>
                                        <td>
                                            <?php if ($pengawasSummary === '-'): ?>
                                                <span class="text-muted font-italic">- Belum Diatur -</span>
                                            <?php else: ?>
                                                <span class="small font-weight-bold text-dark d-block text-truncate" style="max-width: 140px;" title="<?= htmlspecialchars($pengawasSummary) ?>">
                                                    <i class="fas fa-user-shield text-info mr-1"></i><?= htmlspecialchars($pengawasSummary) ?>
                                                </span>
                                            <?php endif; ?>
                                            <div class="mt-1">
                                                <a href="/cbt/jadwal/pengawas/<?= $idJadwal ?>" class="btn btn-xs btn-outline-info">
                                                    <i class="fas fa-users-cog mr-1"></i>Atur Pengawas
                                                </a>
                                            </div>
                                        </td>
                                        <td class="text-center">
                                            <?php if ((int)$item['status'] === 1): ?>
                                                <span class="badge badge-success"><i class="fas fa-check mr-1"></i>Aktif</span>
                                            <?php else: ?>
                                                <span class="badge badge-secondary">Nonaktif</span>
                                            <?php endif; ?>
                                        </td>
                                        <td class="text-center">
                                            <div class="btn-group">
                                                <a href="/cbt/jadwal/edit/<?= $idJadwal ?>" class="btn btn-sm btn-warning shadow-sm" title="Edit Jadwal">
                                                    <i class="fas fa-edit"></i>
                                                </a>
                                                <button type="button" class="btn btn-sm btn-danger shadow-sm ml-1" title="Hapus Jadwal" onclick="hapusJadwal(<?= $idJadwal ?>, '<?= htmlspecialchars($item['bank_kode'] ?? '') ?>')">
                                                    <i class="fas fa-trash"></i>
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

<script>
function hapusJadwal(id, kode) {
    if (confirm('Apakah Anda yakin ingin menghapus jadwal ujian untuk bank soal "' + kode + '"?')) {
        window.location.href = '/cbt/jadwal/delete/' + id;
    }
}
</script>
