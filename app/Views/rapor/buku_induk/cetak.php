<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Lembar Buku Induk — <?= htmlspecialchars($buku['nama'], ENT_QUOTES, 'UTF-8') ?></title>
    <style>
        @page {
            size: A4 portrait;
            margin: 1.5cm;
        }
        body {
            font-family: 'Times New Roman', Times, serif;
            font-size: 10pt;
            line-height: 1.3;
            color: #000;
            background: #fff;
            margin: 0;
            padding: 15px;
        }
        .page-break {
            page-break-after: always;
            break-after: page;
        }
        .text-center { text-align: center; }
        .text-right { text-align: right; }
        .fw-bold { font-weight: bold; }
        .header-title {
            text-align: center;
            font-size: 13pt;
            font-weight: bold;
            margin-bottom: 5px;
            text-transform: uppercase;
        }
        .header-sub {
            text-align: center;
            font-size: 11pt;
            margin-bottom: 20px;
        }
        .info-table {
            width: 100%;
            margin-bottom: 12px;
            border-collapse: collapse;
        }
        .info-table td {
            padding: 2.5px 0;
            font-size: 9.5pt;
        }
        .section-title {
            font-weight: bold;
            font-size: 10.5pt;
            margin-top: 10px;
            margin-bottom: 4px;
            border-bottom: 1px solid #000;
            padding-bottom: 2px;
        }
        .data-table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 8px;
            margin-bottom: 15px;
        }
        .data-table th, .data-table td {
            border: 1px solid #000;
            padding: 4px;
            font-size: 8.5pt;
        }
        .data-table th {
            background-color: #f2f2f2;
            text-align: center;
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
    <button class="btn-print" onclick="window.print()">Cetak Lembar Buku Induk</button>
</div>

<div class="header-title">LEMBAR BUKU INDUK PESERTA DIDIK</div>
<div class="header-sub"><?= htmlspecialchars($sekolah['nama_sekolah'] ?? 'SEKOLAH', ENT_QUOTES, 'UTF-8') ?></div>

<div style="float: right; width: 3cm; height: 4cm; border: 1px solid #000; margin-left: 15px; text-align: center; line-height: 4cm; font-size: 8pt; color: #777;">
    <?php if (!empty($buku['foto'])): ?>
        <img src="<?= base_url('/' . ltrim($buku['foto'], '/')) ?>" style="width: 100%; height: 100%; object-fit: cover;">
    <?php else: ?>
        Pas Foto 3x4
    <?php endif; ?>
</div>

<div class="section-title">A. KETERANGAN TENTANG DIRI PESERTA DIDIK</div>
<table class="info-table" style="width: calc(100% - 3.5cm);">
    <tr><td style="width: 5%;">1.</td><td style="width: 40%;">Nama Lengkap</td><td style="width: 3%;">:</td><td class="fw-bold"><?= htmlspecialchars($buku['nama'], ENT_QUOTES, 'UTF-8') ?></td></tr>
    <tr><td>2.</td><td>Nama Panggilan</td><td>:</td><td><?= htmlspecialchars($buku['nama_panggilan'] ?? '-', ENT_QUOTES, 'UTF-8') ?></td></tr>
    <tr><td>3.</td><td>Nomor Induk Siswa (NIS)</td><td>:</td><td><?= htmlspecialchars($buku['nis'] ?? '-', ENT_QUOTES, 'UTF-8') ?></td></tr>
    <tr><td>4.</td><td>NISN</td><td>:</td><td><?= htmlspecialchars($buku['nisn'] ?? '-', ENT_QUOTES, 'UTF-8') ?></td></tr>
    <tr><td>5.</td><td>Tempat, Tanggal Lahir</td><td>:</td><td><?= htmlspecialchars($buku['tempat_lahir'] ?? '-', ENT_QUOTES, 'UTF-8') ?>, <?= !empty($buku['tanggal_lahir']) ? format_indo($buku['tanggal_lahir']) : '-' ?></td></tr>
    <tr><td>6.</td><td>Jenis Kelamin</td><td>:</td><td><?= ($buku['jenis_kelamin'] ?? 'L') === 'L' ? 'Laki-laki' : 'Perempuan' ?></td></tr>
    <tr><td>7.</td><td>Agama</td><td>:</td><td><?= htmlspecialchars($buku['agama'] ?? 'Islam', ENT_QUOTES, 'UTF-8') ?></td></tr>
    <tr><td>8.</td><td>Bahasa Sehari-hari</td><td>:</td><td><?= htmlspecialchars($buku['bahasa'] ?? 'Indonesia', ENT_QUOTES, 'UTF-8') ?></td></tr>
    <tr><td>9.</td><td>Alamat Tempat Tinggal</td><td>:</td><td><?= htmlspecialchars($buku['alamat'] ?? '-', ENT_QUOTES, 'UTF-8') ?></td></tr>
</table>
<div style="clear: both;"></div>

<div class="section-title">B. KETERANGAN ORANG TUA / WALI & KELUARGA</div>
<table class="info-table">
    <tr><td style="width: 5%;">10.</td><td style="width: 35%;">Nama Ayah Kandung</td><td style="width: 3%;">:</td><td><?= htmlspecialchars($buku['nama_ayah'] ?? '-', ENT_QUOTES, 'UTF-8') ?></td></tr>
    <tr><td>11.</td><td>Nama Ibu Kandung</td><td>:</td><td><?= htmlspecialchars($buku['nama_ibu'] ?? '-', ENT_QUOTES, 'UTF-8') ?></td></tr>
    <tr><td>12.</td><td>Jumlah Saudara</td><td>:</td><td>Kandung: <?= (int)($buku['jml_saudara_kandung'] ?? 0) ?>, Tiri: <?= (int)($buku['jml_saudara_tiri'] ?? 0) ?>, Angkat: <?= (int)($buku['jml_saudara_angkat'] ?? 0) ?></td></tr>
    <tr><td>13.</td><td>Status Keberadaan Orang Tua</td><td>:</td><td><?= htmlspecialchars($buku['status_yatim'] ?? 'Lengkap', ENT_QUOTES, 'UTF-8') ?></td></tr>
    <tr><td>14.</td><td>Nomor Telepon / HP</td><td>:</td><td><?= htmlspecialchars($buku['hp'] ?? '-', ENT_QUOTES, 'UTF-8') ?></td></tr>
</table>

<div class="section-title">C. KEADAAN JASMANI & KESEHATAN</div>
<table class="info-table">
    <tr><td style="width: 5%;">15.</td><td style="width: 35%;">Golongan Darah</td><td style="width: 3%;">:</td><td><?= htmlspecialchars($buku['gol_darah'] ?? '-', ENT_QUOTES, 'UTF-8') ?></td></tr>
    <tr><td>16.</td><td>Tinggi / Berat Badan</td><td>:</td><td><?= $buku['tinggi'] ?? '-' ?> cm / <?= $buku['berat'] ?? '-' ?> kg</td></tr>
    <tr><td>17.</td><td>Riwayat Penyakit Pernah Diderita</td><td>:</td><td><?= htmlspecialchars($buku['penyakit'] ?? 'Tidak ada', ENT_QUOTES, 'UTF-8') ?></td></tr>
    <tr><td>18.</td><td>Kelainan Jasmani</td><td>:</td><td><?= htmlspecialchars($buku['kelainan_fisik'] ?? 'Tidak ada', ENT_QUOTES, 'UTF-8') ?></td></tr>
</table>

<div class="section-title">D. PENDIDIKAN SEBELUMNYA & KELULUSAN</div>
<table class="info-table">
    <tr><td style="width: 5%;">19.</td><td style="width: 35%;">Tamat Belajar Dari Sekolah</td><td style="width: 3%;">:</td><td><?= htmlspecialchars($buku['tamat_dari'] ?? '-', ENT_QUOTES, 'UTF-8') ?></td></tr>
    <tr><td>20.</td><td>Nomor Ijazah Sebelumnya</td><td>:</td><td><?= htmlspecialchars($buku['no_ijazah_sebelumnya'] ?? '-', ENT_QUOTES, 'UTF-8') ?></td></tr>
    <tr><td>21.</td><td>Tahun Kelulusan di Sekolah Ini</td><td>:</td><td><?= htmlspecialchars($buku['tahun_lulus'] ?? '-', ENT_QUOTES, 'UTF-8') ?></td></tr>
    <tr><td>22.</td><td>Nomor Seri Ijazah</td><td>:</td><td class="fw-bold"><?= htmlspecialchars($buku['no_ijazah'] ?? '-', ENT_QUOTES, 'UTF-8') ?></td></tr>
</table>

<div class="page-break"></div>

<div class="section-title">E. TRANSKRIP ARSIP NILAI RAPOR LINTAS SEMESTER</div>
<?php if (!empty($arsip)): ?>
    <?php foreach ($arsip as $key => $sem): ?>
        <div style="margin-top: 12px; margin-bottom: 4px; font-weight: bold; font-size: 9pt;">
            Tahun Pelajaran <?= htmlspecialchars($sem['tahun_pelajaran'] ?? '-', ENT_QUOTES, 'UTF-8') ?> — <?= htmlspecialchars($sem['semester'] ?? '-', ENT_QUOTES, 'UTF-8') ?> (Kelas <?= htmlspecialchars($sem['kelas'] ?? '-', ENT_QUOTES, 'UTF-8') ?>) | Rata-rata: <?= number_format((float)($sem['rata_rata'] ?? 0), 2) ?>
        </div>
        <table class="data-table">
            <thead>
                <tr>
                    <th style="width: 35px;">No</th>
                    <th>Mata Pelajaran</th>
                    <th style="width: 70px;">Nilai Akhir</th>
                    <th style="width: 60px;">Predikat</th>
                </tr>
            </thead>
            <tbody>
                <?php $mNo = 1; foreach ($sem['grades'] ?? [] as $g): ?>
                    <tr>
                        <td class="text-center"><?= $mNo++ ?></td>
                        <td><?= htmlspecialchars($g['nama_mapel'], ENT_QUOTES, 'UTF-8') ?></td>
                        <td class="text-center fw-bold"><?= number_format((float)$g['nilai'], 1) ?></td>
                        <td class="text-center fw-bold"><?= htmlspecialchars($g['predikat'] ?? '-', ENT_QUOTES, 'UTF-8') ?></td>
                    </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    <?php endforeach; ?>
<?php else: ?>
    <div style="padding: 20px; text-align: center; color: #888; font-style: italic;">
        Belum ada transkrip nilai semester yang diarsipkan ke buku induk siswa ini.
    </div>
<?php endif; ?>

</body>
</html>

