<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Rapor PTS — <?= htmlspecialchars($siswa['nama'], ENT_QUOTES, 'UTF-8') ?></title>
    <style>
        @page {
            size: A4 portrait;
            margin: 1.5cm;
        }
        body {
            font-family: 'Times New Roman', Times, serif;
            font-size: 11pt;
            line-height: 1.3;
            color: #000;
            background: #fff;
            margin: 0;
            padding: 20px;
        }
        .text-center { text-align: center; }
        .text-right { text-align: right; }
        .fw-bold { font-weight: bold; }
        .header-title {
            text-align: center;
            font-size: 13pt;
            font-weight: bold;
            margin-bottom: 20px;
            text-transform: uppercase;
        }
        .info-table {
            width: 100%;
            margin-bottom: 15px;
            border-collapse: collapse;
        }
        .info-table td {
            padding: 3px 0;
            font-size: 10.5pt;
        }
        .data-table {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 15px;
        }
        .data-table th, .data-table td {
            border: 1px solid #000;
            padding: 5px 6px;
            font-size: 10pt;
        }
        .data-table th {
            background-color: #f2f2f2;
            text-align: center;
        }
        .signature-table {
            width: 100%;
            margin-top: 30px;
            border-collapse: collapse;
        }
        .signature-table td {
            text-align: center;
            vertical-align: top;
            font-size: 10.5pt;
        }
        .no-print {
            margin-bottom: 15px;
            text-align: right;
        }
        .btn-print {
            padding: 8px 16px;
            background: #0d6efd;
            color: #fff;
            border: none;
            border-radius: 4px;
            cursor: pointer;
            font-weight: bold;
        }
        @media print {
            .no-print { display: none; }
            body { padding: 0; }
        }
    </style>
</head>
<body>

<div class="no-print">
    <button class="btn-print" onclick="window.print()">Cetak Dokumen Rapor</button>
</div>

<div class="header-title">
    LAPORAN PENILAIAN TENGAH SEMESTER (PTS)<br>
    <?= htmlspecialchars($sekolah['nama_sekolah'] ?? 'SEKOLAH', ENT_QUOTES, 'UTF-8') ?><br>
    TAHUN PELAJARAN <?= htmlspecialchars($activeTp['tahun'] ?? '-', ENT_QUOTES, 'UTF-8') ?>
</div>

<table class="info-table">
    <tr>
        <td style="width: 18%;">Nama Peserta Didik</td>
        <td style="width: 2%;">:</td>
        <td style="width: 45%;" class="fw-bold"><?= htmlspecialchars($siswa['nama'], ENT_QUOTES, 'UTF-8') ?></td>
        <td style="width: 15%;">Kelas / Rombel</td>
        <td style="width: 2%;">:</td>
        <td style="width: 18%;"><?= htmlspecialchars($kelas['nama_kelas'], ENT_QUOTES, 'UTF-8') ?></td>
    </tr>
    <tr>
        <td>Nomor Induk / NISN</td>
        <td>:</td>
        <td><?= htmlspecialchars($siswa['nis'] ?? '-', ENT_QUOTES, 'UTF-8') ?> / <?= htmlspecialchars($siswa['nisn'] ?? '-', ENT_QUOTES, 'UTF-8') ?></td>
        <td>Semester</td>
        <td>:</td>
        <td><?= htmlspecialchars($activeSmt['nama_smt'] ?? '-', ENT_QUOTES, 'UTF-8') ?></td>
    </tr>
</table>

<table class="data-table">
    <thead>
        <tr>
            <th style="width: 35px;" rowspan="2">No</th>
            <th rowspan="2">Mata Pelajaran</th>
            <th style="width: 50px;" rowspan="2">KKM</th>
            <th colspan="2">Pengetahuan (KI-3)</th>
            <th colspan="2">Keterampilan (KI-4)</th>
            <th colspan="2">Nilai PTS</th>
        </tr>
        <tr>
            <th style="width: 55px;">Nilai</th>
            <th style="width: 45px;">Pred</th>
            <th style="width: 55px;">Nilai</th>
            <th style="width: 45px;">Pred</th>
            <th style="width: 55px;">Nilai</th>
            <th style="width: 45px;">Pred</th>
        </tr>
    </thead>
    <tbody>
        <?php $no = 1; foreach ($grades as $g): ?>
            <tr>
                <td class="text-center"><?= $no++ ?></td>
                <td><?= htmlspecialchars($g['mapel']['nama_mapel'], ENT_QUOTES, 'UTF-8') ?></td>
                <td class="text-center"><?= (int)$g['kkm'] ?></td>
                <td class="text-center"><?= $g['ph'] !== null ? number_format((float)$g['ph'], 1) : '-' ?></td>
                <td class="text-center"><?= htmlspecialchars($g['ph_pred'] ?? '-', ENT_QUOTES, 'UTF-8') ?></td>
                <td class="text-center"><?= $g['k'] !== null ? number_format((float)$g['k'], 1) : '-' ?></td>
                <td class="text-center"><?= htmlspecialchars($g['k_pred'] ?? '-', ENT_QUOTES, 'UTF-8') ?></td>
                <td class="text-center fw-bold"><?= $g['pts'] !== null ? number_format((float)$g['pts'], 1) : '-' ?></td>
                <td class="text-center fw-bold"><?= htmlspecialchars($g['pts_pred'] ?? '-', ENT_QUOTES, 'UTF-8') ?></td>
            </tr>
        <?php endforeach; ?>
    </tbody>
</table>

<table style="width: 100%; border-collapse: collapse; margin-top: 10px;">
    <tr>
        <td style="width: 45%; vertical-align: top;">
            <table class="data-table">
                <thead>
                    <tr>
                        <th colspan="2">Ketidakhadiran</th>
                    </tr>
                </thead>
                <tbody>
                    <tr>
                        <td style="width: 60%;">Sakit</td>
                        <td class="text-center" style="width: 40%;"><?= (int)($absensi['sakit'] ?? 0) ?> hari</td>
                    </tr>
                    <tr>
                        <td>Izin</td>
                        <td class="text-center"><?= (int)($absensi['izin'] ?? 0) ?> hari</td>
                    </tr>
                    <tr>
                        <td>Tanpa Keterangan</td>
                        <td class="text-center"><?= (int)($absensi['alfa'] ?? 0) ?> hari</td>
                    </tr>
                </tbody>
            </table>
        </td>
        <td style="width: 5%;"></td>
        <td style="width: 50%; vertical-align: top;">
            <table class="data-table">
                <thead>
                    <tr>
                        <th>Catatan Wali Kelas</th>
                    </tr>
                </thead>
                <tbody>
                    <tr>
                        <td style="height: 70px; vertical-align: top;">
                            <?= !empty($catatan) ? nl2br(htmlspecialchars($catatan, ENT_QUOTES, 'UTF-8')) : 'Tingkatkan terus keaktifan dan semangat belajar.' ?>
                        </td>
                    </tr>
                </tbody>
            </table>
        </td>
    </tr>
</table>

<table class="signature-table">
    <tr>
        <td style="width: 33%;">
            Mengetahui,<br>
            Orang Tua / Wali Siswa<br><br><br><br><br>
            ...............................................
        </td>
        <td style="width: 34%;">
            Mengetahui,<br>
            Kepala Sekolah<br><br><br><br><br>
            <span class="fw-bold" style="text-decoration: underline;"><?= htmlspecialchars($sekolah['nama_kepsek'] ?? 'Kepala Sekolah', ENT_QUOTES, 'UTF-8') ?></span><br>
            <?php if (!empty($raporSetting['nip_kepsek']) && !empty($sekolah['nip_kepsek'])): ?>
                NIP. <?= htmlspecialchars($sekolah['nip_kepsek'], ENT_QUOTES, 'UTF-8') ?>
            <?php endif; ?>
        </td>
        <td style="width: 33%;">
            <?= htmlspecialchars($sekolah['kota'] ?? 'Kota', ENT_QUOTES, 'UTF-8') ?>, <?= format_indo($raporSetting['tgl_rapor_pts'] ?? date('Y-m-d')) ?><br>
            Wali Kelas,<br><br><br><br><br>
            <span class="fw-bold" style="text-decoration: underline;"><?= htmlspecialchars($waliKelas['nama_guru'] ?? 'Wali Kelas', ENT_QUOTES, 'UTF-8') ?></span><br>
            <?php if (!empty($raporSetting['nip_walikelas']) && !empty($waliKelas['nip'])): ?>
                NIP. <?= htmlspecialchars($waliKelas['nip'], ENT_QUOTES, 'UTF-8') ?>
            <?php endif; ?>
        </td>
    </tr>
</table>

</body>
</html>

