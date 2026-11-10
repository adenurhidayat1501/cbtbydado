<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>DKN — <?= htmlspecialchars($kelas['nama_kelas'], ENT_QUOTES, 'UTF-8') ?></title>
    <style>
        @page {
            size: A4 landscape;
            margin: 1cm;
        }
        body {
            font-family: Arial, Helvetica, sans-serif;
            font-size: 8.5pt;
            line-height: 1.25;
            color: #000;
            background: #fff;
            margin: 0;
            padding: 10px;
        }
        .text-center { text-align: center; }
        .text-right { text-align: right; }
        .fw-bold { font-weight: bold; }
        .header-title {
            text-align: center;
            font-size: 12pt;
            font-weight: bold;
            margin-bottom: 12px;
            text-transform: uppercase;
        }
        .info-table {
            width: 100%;
            margin-bottom: 10px;
            border-collapse: collapse;
            font-size: 8.5pt;
        }
        .info-table td {
            padding: 2px 0;
        }
        .data-table {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 15px;
        }
        .data-table th, .data-table td {
            border: 1px solid #000;
            padding: 3px 4px;
            font-size: 8pt;
        }
        .data-table th {
            background-color: #e9ecef;
            text-align: center;
        }
        .signature-table {
            width: 100%;
            margin-top: 20px;
            border-collapse: collapse;
        }
        .signature-table td {
            text-align: center;
            vertical-align: top;
            font-size: 8.5pt;
        }
        .no-print {
            margin-bottom: 15px;
            text-align: right;
        }
        .btn-print {
            padding: 6px 14px;
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
    <button class="btn-print" onclick="window.print()">Cetak DKN (Landscape)</button>
</div>

<div class="header-title">
    DAFTAR KUMPULAN NILAI (DKN) HASIL BELAJAR PESERTA DIDIK<br>
    <?= htmlspecialchars($sekolah['nama_sekolah'] ?? 'SEKOLAH', ENT_QUOTES, 'UTF-8') ?><br>
    TAHUN PELAJARAN <?= htmlspecialchars($activeTp['tahun'] ?? '-', ENT_QUOTES, 'UTF-8') ?>
</div>

<table class="info-table">
    <tr>
        <td style="width: 10%;">Kelas / Rombel</td>
        <td style="width: 2%;">:</td>
        <td style="width: 40%;" class="fw-bold"><?= htmlspecialchars($kelas['nama_kelas'], ENT_QUOTES, 'UTF-8') ?></td>
        <td style="width: 10%;">Semester</td>
        <td style="width: 2%;">:</td>
        <td style="width: 36%;"><?= htmlspecialchars($activeSmt['nama_smt'] ?? '-', ENT_QUOTES, 'UTF-8') ?></td>
    </tr>
    <tr>
        <td>Wali Kelas</td>
        <td>:</td>
        <td><?= htmlspecialchars($waliKelas['nama_guru'] ?? '-', ENT_QUOTES, 'UTF-8') ?></td>
        <td>Tanggal Rekap</td>
        <td>:</td>
        <td><?= format_indo($raporSetting['tgl_rapor_akhir'] ?? date('Y-m-d')) ?></td>
    </tr>
</table>

<table class="data-table">
    <thead>
        <tr>
            <th style="width: 25px;" rowspan="2">No</th>
            <th style="width: 65px;" rowspan="2">NIS</th>
            <th rowspan="2">Nama Peserta Didik</th>
            <th style="width: 25px;" rowspan="2">L/P</th>
            <th colspan="<?= count($mapelList) ?>">Mata Pelajaran (Nilai Akhir)</th>
            <th style="width: 45px;" rowspan="2">Jml Nilai</th>
            <th style="width: 40px;" rowspan="2">Rata-Rata</th>
            <th style="width: 30px;" rowspan="2">Rank</th>
            <th colspan="3">Absensi</th>
            <th style="width: 70px;" rowspan="2">Status</th>
        </tr>
        <tr>
            <?php foreach ($mapelList as $m): ?>
                <th style="width: 32px; font-size: 7pt;" title="<?= htmlspecialchars($m['nama_mapel'], ENT_QUOTES, 'UTF-8') ?>">
                    <?= htmlspecialchars($m['kode_mapel'] ?? substr($m['nama_mapel'], 0, 4), ENT_QUOTES, 'UTF-8') ?>
                </th>
            <?php endforeach; ?>
            <th style="width: 22px;">S</th>
            <th style="width: 22px;">I</th>
            <th style="width: 22px;">A</th>
        </tr>
    </thead>
    <tbody>
        <?php $no = 1; foreach ($studentsData as $row): 
            $s = $row['siswa'];
        ?>
            <tr>
                <td class="text-center"><?= $no++ ?></td>
                <td class="text-center font-monospace"><?= htmlspecialchars($s['nis'] ?? '-', ENT_QUOTES, 'UTF-8') ?></td>
                <td><strong><?= htmlspecialchars($s['nama'], ENT_QUOTES, 'UTF-8') ?></strong></td>
                <td class="text-center"><?= htmlspecialchars($s['jenis_kelamin'] ?? '-', ENT_QUOTES, 'UTF-8') ?></td>
                <?php foreach ($mapelList as $m): 
                    $idMapel = (int)$m['id_mapel'];
                    $sc = $row['scores'][$idMapel]['akhir'] ?? null;
                ?>
                    <td class="text-center"><?= $sc !== null ? number_format((float)$sc, 0) : '-' ?></td>
                <?php endforeach; ?>
                <td class="text-center fw-bold"><?= number_format((float)$row['total_nilai'], 0) ?></td>
                <td class="text-center fw-bold"><?= number_format((float)$row['rata_rata'], 1) ?></td>
                <td class="text-center fw-bold"><?= $row['ranking'] ?></td>
                <td class="text-center"><?= (int)($row['absensi']['sakit'] ?? 0) ?></td>
                <td class="text-center"><?= (int)($row['absensi']['izin'] ?? 0) ?></td>
                <td class="text-center"><?= (int)($row['absensi']['alfa'] ?? 0) ?></td>
                <td class="text-center" style="font-size: 7.5pt;">
                    <?= $row['status_naik'] === 1 ? '<span style="color: green;">Naik/Lulus</span>' : '<span style="color: red;">Tinggal</span>' ?>
                </td>
            </tr>
        <?php endforeach; ?>
    </tbody>
</table>

<table class="signature-table">
    <tr>
        <td style="width: 50%;">
            Mengetahui,<br>
            Kepala Sekolah<br><br><br><br>
            <span class="fw-bold" style="text-decoration: underline;"><?= htmlspecialchars($sekolah['nama_kepsek'] ?? 'Kepala Sekolah', ENT_QUOTES, 'UTF-8') ?></span><br>
            <?php if (!empty($raporSetting['nip_kepsek']) && !empty($sekolah['nip_kepsek'])): ?>
                NIP. <?= htmlspecialchars($sekolah['nip_kepsek'], ENT_QUOTES, 'UTF-8') ?>
            <?php endif; ?>
        </td>
        <td style="width: 50%;">
            <?= htmlspecialchars($sekolah['kota'] ?? 'Kota', ENT_QUOTES, 'UTF-8') ?>, <?= format_indo($raporSetting['tgl_rapor_akhir'] ?? date('Y-m-d')) ?><br>
            Wali Kelas,<br><br><br><br>
            <span class="fw-bold" style="text-decoration: underline;"><?= htmlspecialchars($waliKelas['nama_guru'] ?? 'Wali Kelas', ENT_QUOTES, 'UTF-8') ?></span><br>
            <?php if (!empty($raporSetting['nip_walikelas']) && !empty($waliKelas['nip'])): ?>
                NIP. <?= htmlspecialchars($waliKelas['nip'], ENT_QUOTES, 'UTF-8') ?>
            <?php endif; ?>
        </td>
    </tr>
</table>

</body>
</html>

