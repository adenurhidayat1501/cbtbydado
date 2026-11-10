<?php
$title = $title ?? 'Cetak Kartu Peserta Ujian';
$students = $students ?? [];
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
            font-size: 11pt;
            margin: 0;
            padding: 10mm;
            background: #ffffff;
        }
        .page-break {
            page-break-after: always;
        }
        .card-grid {
            display: flex;
            flex-wrap: wrap;
            justify-content: space-between;
        }
        .card-item {
            width: 48%;
            border: 2px solid #333333;
            border-radius: 6px;
            margin-bottom: 12mm;
            padding: 8px;
            box-sizing: border-box;
            background: #ffffff;
        }
        .kop-table {
            width: 100%;
            border-bottom: 2px solid #000000;
            margin-bottom: 8px;
            padding-bottom: 4px;
        }
        .kop-table td {
            vertical-align: middle;
        }
        .logo-sekolah {
            height: 48px;
            max-width: 48px;
            object-fit: contain;
        }
        .kop-text {
            text-align: center;
            line-height: 1.2;
        }
        .kop-title {
            font-size: 10pt;
            font-weight: bold;
            text-transform: uppercase;
        }
        .kop-sub {
            font-size: 8pt;
        }
        .kartu-title {
            text-align: center;
            font-size: 11pt;
            font-weight: bold;
            margin: 4px 0 8px 0;
            text-transform: uppercase;
            letter-spacing: 1px;
            text-decoration: underline;
        }
        .info-table {
            width: 100%;
            border-collapse: collapse;
            font-size: 9.5pt;
        }
        .info-table td {
            padding: 2px 4px;
            vertical-align: top;
        }
        .foto-box {
            width: 25mm;
            height: 30mm;
            border: 1px solid #777777;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 8pt;
            color: #888888;
            margin-top: 4px;
        }
        .foto-img {
            width: 100%;
            height: 100%;
            object-fit: cover;
        }
        .ttd-box {
            margin-top: 8px;
            text-align: right;
            font-size: 8.5pt;
        }
        @media print {
            @page {
                size: A4 portrait;
                margin: 10mm;
            }
            body {
                padding: 0;
            }
            .no-print {
                display: none !important;
            }
        }
    </style>
</head>
<body>

<div class="no-print" style="margin-bottom: 15px; padding: 10px; background: #e9ecef; border-radius: 4px;">
    <button onclick="window.print()" style="padding: 8px 16px; font-weight: bold; cursor: pointer; background: #007bff; color: white; border: none; border-radius: 4px;">
        Cetak Sekarang (Print)
    </button>
    <span style="margin-left: 10px; font-size: 9pt; color: #555;">Format optimal: A4 Portrait, Margin Default.</span>
</div>

<div class="card-grid">
    <?php foreach ($students as $idx => $stu): 
        $namaSiswa = htmlspecialchars((string)($stu['nama'] ?? '-'), ENT_QUOTES, 'UTF-8');
        $nis = htmlspecialchars((string)($stu['nis'] ?? '-'), ENT_QUOTES, 'UTF-8');
        $nisn = htmlspecialchars((string)($stu['nisn'] ?? '-'), ENT_QUOTES, 'UTF-8');
        $kelas = htmlspecialchars((string)($stu['nama_kelas'] ?? '-'), ENT_QUOTES, 'UTF-8');
        $ruang = htmlspecialchars((string)($stu['nama_ruang'] ?? '-'), ENT_QUOTES, 'UTF-8');
        $sesi = htmlspecialchars((string)($stu['nama_sesi'] ?? '-'), ENT_QUOTES, 'UTF-8');
        $nomorPeserta = htmlspecialchars((string)($stu['nomor_peserta'] ?? $nis), ENT_QUOTES, 'UTF-8');
        $username = htmlspecialchars((string)($stu['nis'] ?? '-'), ENT_QUOTES, 'UTF-8');
        $password = htmlspecialchars((string)($stu['nis'] ?? '123456'), ENT_QUOTES, 'UTF-8');
    ?>
        <div class="card-item">
            <!-- Kop Kartu -->
            <table class="kop-table">
                <tr>
                    <td style="width: 52px; text-align: left;">
                        <img src="<?= !empty($sekolah['logo_kiri']) ? upload_url('sekolah/' . $sekolah['logo_kiri']) : asset('img/logo.png') ?>" class="logo-sekolah" alt="Logo">
                    </td>
                    <td class="kop-text">
                        <div class="kop-sub"><?= htmlspecialchars((string)($kop['header_1'] ?? 'PEMERINTAH KABUPATEN TASIKMALAYA'), ENT_QUOTES, 'UTF-8') ?></div>
                        <div class="kop-title"><?= htmlspecialchars((string)($sekolah['nama_sekolah'] ?? 'SMP NEGERI SATU ATAP 2 CIPATUJAH'), ENT_QUOTES, 'UTF-8') ?></div>
                        <div class="kop-sub"><?= htmlspecialchars((string)($sekolah['alamat'] ?? 'Cipatujah, Kab. Tasikmalaya'), ENT_QUOTES, 'UTF-8') ?></div>
                    </td>
                    <td style="width: 52px; text-align: right;">
                        <?php if (!empty($sekolah['logo_kanan'])): ?>
                            <img src="<?= upload_url('sekolah/' . $sekolah['logo_kanan']) ?>" class="logo-sekolah" alt="Logo">
                        <?php endif; ?>
                    </td>
                </tr>
            </table>

            <div class="kartu-title">KARTU PESERTA CBT</div>

            <table style="width: 100%;">
                <tr>
                    <td style="vertical-align: top;">
                        <table class="info-table">
                            <tr>
                                <td style="width: 110px;">Nomor Peserta</td>
                                <td style="width: 8px;">:</td>
                                <td style="font-weight: bold;"><?= $nomorPeserta ?></td>
                            </tr>
                            <tr>
                                <td>Nama Siswa</td>
                                <td>:</td>
                                <td style="font-weight: bold;"><?= $namaSiswa ?></td>
                            </tr>
                            <tr>
                                <td>NIS / NISN</td>
                                <td>:</td>
                                <td><?= $nis ?> / <?= $nisn ?></td>
                            </tr>
                            <tr>
                                <td>Kelas / Rombel</td>
                                <td>:</td>
                                <td><?= $kelas ?></td>
                            </tr>
                            <tr>
                                <td>Ruang / Sesi</td>
                                <td>:</td>
                                <td><?= $ruang ?> / <?= $sesi ?></td>
                            </tr>
                            <tr>
                                <td>Username / Sesi</td>
                                <td>:</td>
                                <td><code><?= $username ?></code></td>
                            </tr>
                            <tr>
                                <td>Password</td>
                                <td>:</td>
                                <td><code><?= $password ?></code></td>
                            </tr>
                        </table>
                    </td>
                    <td style="width: 28mm; vertical-align: top; text-align: right;">
                        <div class="foto-box">
                            <?php if (!empty($stu['foto'])): ?>
                                <img src="<?= upload_url('foto_siswa/' . $stu['foto']) ?>" class="foto-img" alt="Foto">
                            <?php else: ?>
                                Foto 2x3
                            <?php endif; ?>
                        </div>
                    </td>
                </tr>
            </table>

            <div class="ttd-box">
                <div><?= htmlspecialchars((string)($kop['kota'] ?? 'Kota'), ENT_QUOTES, 'UTF-8') ?>, <?= htmlspecialchars((string)($kop['tanggal'] ?? date('d-m-Y')), ENT_QUOTES, 'UTF-8') ?></div>
                <div style="margin-top: 2px;">Kepala Sekolah,</div>
                <div style="margin-top: 28px; font-weight: bold; text-decoration: underline;">
                    <?= htmlspecialchars((string)($sekolah['kepsek'] ?? 'Kepala Sekolah'), ENT_QUOTES, 'UTF-8') ?>
                </div>
                <div>NIP. <?= htmlspecialchars((string)($sekolah['nip_kepsek'] ?? '-'), ENT_QUOTES, 'UTF-8') ?></div>
            </div>
        </div>

        <?php if (($idx + 1) % 4 === 0 && ($idx + 1) < count($students)): ?>
            </div><div class="page-break"></div><div class="card-grid">
        <?php endif; ?>
    <?php endforeach; ?>
</div>

</body>
</html>

