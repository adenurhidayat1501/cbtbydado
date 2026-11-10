<?php
$title = $title ?? 'Cetak Berita Acara Ujian';
$jadwal = $jadwal ?? [];
$ruang = $ruang ?? [];
$sesi = $sesi ?? [];
$sekolah = $sekolah ?? [];
$kop = $kop ?? [];
$totalTerdaftar = $totalTerdaftar ?? 0;
$totalHadir = $totalHadir ?? 0;
$totalTidakHadir = $totalTidakHadir ?? 0;
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
            font-size: 10.5pt;
            margin: 0;
            padding: 14mm;
            background: #ffffff;
            color: #000000;
            line-height: 1.5;
        }
        .header-table {
            width: 100%;
            border-bottom: 3px double #000000;
            margin-bottom: 14px;
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
            margin: 14px 0 18px 0;
            text-decoration: underline;
        }
        .paragraf {
            text-align: justify;
            margin-bottom: 12px;
        }
        .point-table {
            width: 100%;
            margin-bottom: 12px;
            font-size: 10pt;
        }
        .point-table td {
            padding: 3px 6px;
            vertical-align: top;
        }
        .catatan-box {
            border: 1px solid #333333;
            min-height: 80px;
            padding: 8px;
            margin: 8px 0 16px 0;
            font-style: italic;
            color: #444444;
        }
        .ttd-container {
            width: 100%;
            margin-top: 25px;
            page-break-inside: avoid;
        }
        .ttd-box {
            text-align: center;
            font-size: 10pt;
        }
        @media print {
            @page {
                size: A4 portrait;
                margin: 12mm;
            }
            body { padding: 0; }
            .no-print { display: none !important; }
        }
    </style>
</head>
<body>

<div class="no-print" style="margin-bottom: 15px; padding: 10px; background: #e9ecef; border-radius: 4px;">
    <button onclick="window.print()" style="padding: 8px 16px; font-weight: bold; cursor: pointer; background: #17a2b8; color: white; border: none; border-radius: 4px;">
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

<div class="judul-halaman">BERITA ACARA PELAKSANAAN UJIAN CBT</div>

<div class="paragraf">
    Pada hari ini <strong><?= date('l') ?></strong>, tanggal <strong><?= date('d') ?></strong> bulan <strong><?= date('F') ?></strong> tahun <strong><?= date('Y') ?></strong>, di <strong><?= htmlspecialchars((string)($sekolah['nama_sekolah'] ?? 'Sekolah'), ENT_QUOTES, 'UTF-8') ?></strong> telah diselenggarakan Ujian Berbasis Komputer (CBT):
</div>

<table class="point-table">
    <tr>
        <td style="width: 220px;">Jenis Ujian</td>
        <td style="width: 10px;">:</td>
        <td style="font-weight: bold;"><?= htmlspecialchars((string)($jadwal['nama_jenis'] ?? ($jadwal['kode_jenis'] ?? 'CBT')), ENT_QUOTES, 'UTF-8') ?></td>
    </tr>
    <tr>
        <td>Mata Pelajaran</td>
        <td>:</td>
        <td style="font-weight: bold;"><?= htmlspecialchars((string)($jadwal['nama_mapel'] ?? '-'), ENT_QUOTES, 'UTF-8') ?> (<?= htmlspecialchars((string)($jadwal['bank_kode'] ?? '-'), ENT_QUOTES, 'UTF-8') ?>)</td>
    </tr>
    <tr>
        <td>Tahun Pelajaran / Semester</td>
        <td>:</td>
        <td><?= htmlspecialchars((string)($tp['tahun'] ?? '-'), ENT_QUOTES, 'UTF-8') ?> / Semester <?= (int)($smt['id_smt'] ?? 1) ?></td>
    </tr>
    <tr>
        <td>Ruang Ujian</td>
        <td>:</td>
        <td><?= htmlspecialchars((string)($ruang['nama_ruang'] ?? '-'), ENT_QUOTES, 'UTF-8') ?></td>
    </tr>
    <tr>
        <td>Sesi Ujian</td>
        <td>:</td>
        <td><?= htmlspecialchars((string)($sesi['nama_sesi'] ?? '-'), ENT_QUOTES, 'UTF-8') ?> (Pukul <?= substr((string)($sesi['waktu_mulai'] ?? '07:30'), 0, 5) ?> s/d <?= substr((string)($sesi['waktu_akhir'] ?? '09:30'), 0, 5) ?>)</td>
    </tr>
    <tr>
        <td>Jumlah Peserta Terdaftar</td>
        <td>:</td>
        <td><strong><?= $totalTerdaftar ?></strong> orang</td>
    </tr>
    <tr>
        <td>Jumlah Peserta Hadir</td>
        <td>:</td>
        <td><strong><?= $totalHadir ?></strong> orang</td>
    </tr>
    <tr>
        <td>Jumlah Peserta Tidak Hadir</td>
        <td>:</td>
        <td><strong><?= $totalTidakHadir ?></strong> orang</td>
    </tr>
</table>

<div style="font-weight: bold; margin-top: 10px;">Catatan Selama Pelaksanaan Ujian:</div>
<div class="catatan-box">
    Ujian terlaksana dengan tertib, lancar, dan aman. Seluruh perangkat komputer dan jaringan server berfungsi normal tanpa kendala teknis yang berarti.
</div>

<div class="paragraf">
    Demikian Berita Acara ini dibuat dengan sebenarnya dalam rangkap secukupnya untuk dipergunakan sebagaimana mestinya.
</div>

<!-- Tanda Tangan Proktor & Pengawas -->
<table class="ttd-container">
    <tr>
        <td style="width: 50%;" class="ttd-box">
            <div>Mengetahui,</div>
            <div style="font-weight: bold;">Proktor Ruang,</div>
            <div style="height: 60px;"></div>
            <div style="font-weight: bold; text-decoration: underline;">
                <?= htmlspecialchars((string)($kop['proktor'] ?? 'Proktor Ruang'), ENT_QUOTES, 'UTF-8') ?>
            </div>
            <div>NIP. ........................................</div>
        </td>
        <td style="width: 50%;" class="ttd-box">
            <div><?= htmlspecialchars((string)($kop['kota'] ?? 'Kota'), ENT_QUOTES, 'UTF-8') ?>, <?= htmlspecialchars((string)($kop['tanggal'] ?? date('d-m-Y')), ENT_QUOTES, 'UTF-8') ?></div>
            <div style="font-weight: bold;">Pengawas Ujian,</div>
            <div style="height: 60px;"></div>
            <div style="font-weight: bold; text-decoration: underline;">
                <?= htmlspecialchars((string)($kop['pengawas_1'] ?? 'Pengawas Ruang 1'), ENT_QUOTES, 'UTF-8') ?>
            </div>
            <div>NIP. ........................................</div>
        </td>
    </tr>
</table>

</body>
</html>

