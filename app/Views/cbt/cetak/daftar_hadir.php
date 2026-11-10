<?php
$title = $title ?? 'Cetak Daftar Kehadiran Ujian';
$students = $students ?? [];
$jadwal = $jadwal ?? [];
$ruang = $ruang ?? [];
$sesi = $sesi ?? [];
$sekolah = $sekolah ?? [];
$kop = $kop ?? [];
$tp = $tp ?? [];
$smt = $smt ?? [];
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="utf-8">
    <title><?= htmlspecialchars($title) ?></title>
    <style>
        body {
            font-family: Arial, Helvetica, sans-serif;
            font-size: 10pt;
            margin: 0;
            padding: 12mm;
            background: #ffffff;
            color: #000000;
        }
        .header-table {
            width: 100%;
            border-bottom: 3px double #000000;
            margin-bottom: 12px;
            padding-bottom: 6px;
        }
        .header-table td {
            vertical-align: middle;
        }
        .logo-instansi {
            height: 60px;
            max-width: 60px;
            object-fit: contain;
        }
        .header-text {
            text-align: center;
            line-height: 1.25;
        }
        .judul-halaman {
            text-align: center;
            font-size: 13pt;
            font-weight: bold;
            text-transform: uppercase;
            margin: 10px 0 14px 0;
            text-decoration: underline;
        }
        .meta-table {
            width: 100%;
            margin-bottom: 12px;
            font-size: 9.5pt;
        }
        .meta-table td {
            padding: 2px 4px;
            vertical-align: top;
        }
        .data-table {
            width: 100%;
            border-collapse: collapse;
            font-size: 9.5pt;
            margin-bottom: 16px;
        }
        .data-table th, .data-table td {
            border: 1px solid #000000;
            padding: 5px 6px;
        }
        .data-table th {
            background-color: #f0f0f0;
            text-align: center;
            font-weight: bold;
        }
        .ttd-container {
            width: 100%;
            margin-top: 20px;
            page-break-inside: avoid;
        }
        .ttd-box {
            text-align: center;
            font-size: 9.5pt;
        }
        @media print {
            @page {
                size: A4 portrait;
                margin: 10mm;
            }
            body { padding: 0; }
            .no-print { display: none !important; }
        }
    </style>
</head>
<body>

<div class="no-print" style="margin-bottom: 15px; padding: 10px; background: #e9ecef; border-radius: 4px;">
    <button onclick="window.print()" style="padding: 8px 16px; font-weight: bold; cursor: pointer; background: #28a745; color: white; border: none; border-radius: 4px;">
        Cetak Sekarang (Print)
    </button>
</div>

<!-- Header Kop -->
<table class="header-table">
    <tr>
        <td style="width: 70px; text-align: left;">
            <img src="<?= !empty($sekolah['logo_kiri']) ? upload_url('sekolah/' . $sekolah['logo_kiri']) : asset('img/logo.png') ?>" class="logo-instansi" alt="Logo">
        </td>
        <td class="header-text">
            <div style="font-size: 11pt;"><?= htmlspecialchars((string)($kop['header_1'] ?? 'PEMERINTAH KABUPATEN TASIKMALAYA'), ENT_QUOTES, 'UTF-8') ?></div>
            <div style="font-size: 12pt; font-weight: bold;"><?= htmlspecialchars((string)($kop['header_2'] ?? 'DINAS PENDIDIKAN'), ENT_QUOTES, 'UTF-8') ?></div>
            <div style="font-size: 14pt; font-weight: bold; text-transform: uppercase;"><?= htmlspecialchars((string)($sekolah['nama_sekolah'] ?? 'SMP NEGERI SATU ATAP 2 CIPATUJAH'), ENT_QUOTES, 'UTF-8') ?></div>
            <div style="font-size: 9pt;"><?= htmlspecialchars((string)($sekolah['alamat'] ?? 'Cipatujah, Kab. Tasikmalaya'), ENT_QUOTES, 'UTF-8') ?></div>
        </td>
        <td style="width: 70px; text-align: right;">
            <?php if (!empty($sekolah['logo_kanan'])): ?>
                <img src="<?= upload_url('sekolah/' . $sekolah['logo_kanan']) ?>" class="logo-instansi" alt="Logo">
            <?php endif; ?>
        </td>
    </tr>
</table>

<div class="judul-halaman">DAFTAR KEHADIRAN PESERTA UJIAN CBT</div>

<!-- Identitas Ujian & Ruang -->
<table class="meta-table">
    <tr>
        <td style="width: 140px; font-weight: bold;">Mata Pelajaran</td>
        <td style="width: 8px;">:</td>
        <td style="width: 320px;"><?= htmlspecialchars((string)($jadwal['nama_mapel'] ?? '-'), ENT_QUOTES, 'UTF-8') ?> (<?= htmlspecialchars((string)($jadwal['bank_kode'] ?? '-'), ENT_QUOTES, 'UTF-8') ?>)</td>
        <td style="width: 130px; font-weight: bold;">Hari, Tanggal</td>
        <td style="width: 8px;">:</td>
        <td><?= !empty($jadwal['tgl_mulai']) ? date('d-m-Y', strtotime($jadwal['tgl_mulai'])) : date('d-m-Y') ?></td>
    </tr>
    <tr>
        <td style="font-weight: bold;">Jenis Ujian</td>
        <td>:</td>
        <td><?= htmlspecialchars((string)($jadwal['nama_jenis'] ?? ($jadwal['kode_jenis'] ?? 'CBT')), ENT_QUOTES, 'UTF-8') ?></td>
        <td style="font-weight: bold;">Ruang / Sesi</td>
        <td>:</td>
        <td><?= htmlspecialchars((string)($ruang['nama_ruang'] ?? '-'), ENT_QUOTES, 'UTF-8') ?> / <?= htmlspecialchars((string)($sesi['nama_sesi'] ?? '-'), ENT_QUOTES, 'UTF-8') ?></td>
    </tr>
    <tr>
        <td style="font-weight: bold;">Tahun Pelajaran</td>
        <td>:</td>
        <td><?= htmlspecialchars((string)($tp['tahun'] ?? '-'), ENT_QUOTES, 'UTF-8') ?> (Semester <?= (int)($smt['id_smt'] ?? 1) ?>)</td>
        <td style="font-weight: bold;">Waktu Ujian</td>
        <td>:</td>
        <td><?= substr((string)($sesi['waktu_mulai'] ?? '07:30'), 0, 5) ?> - <?= substr((string)($sesi['waktu_akhir'] ?? '09:30'), 0, 5) ?> (<?= (int)($jadwal['durasi_ujian'] ?? 90) ?> Menit)</td>
    </tr>
</table>

<!-- Tabel Siswa -->
<table class="data-table">
    <thead>
        <tr>
            <th style="width: 35px;">No</th>
            <th style="width: 140px;">Nomor Peserta</th>
            <th>Nama Lengkap Peserta</th>
            <th style="width: 90px;">Kelas</th>
            <th style="width: 170px;" colspan="2">Tanda Tangan</th>
            <th style="width: 90px;">Ket.</th>
        </tr>
    </thead>
    <tbody>
        <?php if (empty($students)): ?>
            <tr>
                <td colspan="7" style="text-align: center; padding: 20px;">Tidak ada peserta terdaftar pada ruang dan sesi ini.</td>
            </tr>
        <?php else: ?>
            <?php foreach ($students as $idx => $st): 
                $num = $idx + 1;
                $isOdd = ($num % 2 !== 0);
            ?>
                <tr>
                    <td style="text-align: center;"><?= $num ?></td>
                    <td style="text-align: center; font-weight: bold;"><?= htmlspecialchars((string)($st['nomor_peserta'] ?? $st['nis']), ENT_QUOTES, 'UTF-8') ?></td>
                    <td><?= htmlspecialchars((string)$st['nama'], ENT_QUOTES, 'UTF-8') ?></td>
                    <td style="text-align: center;"><?= htmlspecialchars((string)($st['nama_kelas'] ?? '-'), ENT_QUOTES, 'UTF-8') ?></td>
                    <td style="width: 85px; vertical-align: bottom; height: 32px;">
                        <?= $isOdd ? "<span style='font-size: 8pt;'>{$num}.</span>" : '' ?>
                    </td>
                    <td style="width: 85px; vertical-align: bottom; height: 32px;">
                        <?= !$isOdd ? "<span style='font-size: 8pt;'>{$num}.</span>" : '' ?>
                    </td>
                    <td></td>
                </tr>
            <?php endforeach; ?>
        <?php endif; ?>
    </tbody>
</table>

<!-- Tanda Tangan Pengawas & Proktor -->
<table class="ttd-container">
    <tr>
        <td style="width: 50%;" class="ttd-box">
            <div>Mengetahui,</div>
            <div style="font-weight: bold;">Proktor Ruang,</div>
            <div style="height: 55px;"></div>
            <div style="font-weight: bold; text-decoration: underline;">
                <?= htmlspecialchars((string)($kop['proktor'] ?? 'Proktor Ruang'), ENT_QUOTES, 'UTF-8') ?>
            </div>
            <div>NIP. ........................................</div>
        </td>
        <td style="width: 50%;" class="ttd-box">
            <div><?= htmlspecialchars((string)($kop['kota'] ?? 'Kota'), ENT_QUOTES, 'UTF-8') ?>, <?= htmlspecialchars((string)($kop['tanggal'] ?? date('d-m-Y')), ENT_QUOTES, 'UTF-8') ?></div>
            <div style="font-weight: bold;">Pengawas Ujian,</div>
            <div style="height: 55px;"></div>
            <div style="font-weight: bold; text-decoration: underline;">
                <?= htmlspecialchars((string)($kop['pengawas_1'] ?? 'Pengawas Ruang 1'), ENT_QUOTES, 'UTF-8') ?>
            </div>
            <div>NIP. ........................................</div>
        </td>
    </tr>
</table>

</body>
</html>

