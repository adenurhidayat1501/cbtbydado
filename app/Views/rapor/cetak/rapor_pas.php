<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Rapor Akhir Semester — <?= htmlspecialchars($siswa['nama'], ENT_QUOTES, 'UTF-8') ?></title>
    <style>
        @page {
            size: A4 portrait;
            margin: 1.5cm;
        }
        body {
            font-family: 'Times New Roman', Times, serif;
            font-size: 11pt;
            line-height: 1.35;
            color: #000;
            background: #fff;
            margin: 0;
            padding: 20px;
        }
        .page-break {
            page-break-after: always;
            break-after: page;
        }
        .text-center { text-align: center; }
        .text-right { text-align: right; }
        .fw-bold { font-weight: bold; }
        .cover-box {
            text-align: center;
            padding-top: 100px;
            padding-bottom: 50px;
        }
        .cover-logo {
            width: 140px;
            height: auto;
            margin-bottom: 30px;
        }
        .cover-title {
            font-size: 18pt;
            font-weight: bold;
            margin-bottom: 10px;
            text-transform: uppercase;
        }
        .cover-subtitle {
            font-size: 14pt;
            margin-bottom: 60px;
            text-transform: uppercase;
        }
        .cover-student-box {
            border: 2px solid #000;
            display: inline-block;
            padding: 20px 40px;
            margin-bottom: 80px;
            min-width: 320px;
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
            margin-top: 25px;
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
    <button class="btn-print" onclick="window.print()">Cetak Dokumen Rapor Lengkap</button>
</div>

<!-- ========================================== -->
<!-- HALAMAN 1: SAMPUL / COVER RAPOR             -->
<!-- ========================================== -->
<div class="cover-box">
    <?php if (!empty($sekolah['logo'])): ?>
        <img src="<?= base_url('/' . ltrim($sekolah['logo'], '/')) ?>" alt="Logo" class="cover-logo"><br>
    <?php endif; ?>

    <div class="cover-title">RAPOR PESERTA DIDIK</div>
    <div class="cover-subtitle"><?= htmlspecialchars($sekolah['nama_sekolah'] ?? 'SEKOLAH', ENT_QUOTES, 'UTF-8') ?></div>

    <div style="font-size: 11pt; margin-bottom: 15px;">Nama Peserta Didik:</div>
    <div class="cover-student-box">
        <div style="font-size: 14pt; font-weight: bold;"><?= htmlspecialchars($siswa['nama'], ENT_QUOTES, 'UTF-8') ?></div>
        <div style="margin-top: 8px; font-size: 11pt;">NIS / NISN: <?= htmlspecialchars($siswa['nis'] ?? '-', ENT_QUOTES, 'UTF-8') ?> / <?= htmlspecialchars($siswa['nisn'] ?? '-', ENT_QUOTES, 'UTF-8') ?></div>
    </div>

    <div style="font-size: 12pt; font-weight: bold; margin-top: 50px;">
        KEMENTERIAN PENDIDIKAN, KEBUDAYAAN, RISET, DAN TEKNOLOGI<br>
        REPUBLIK INDONESIA
    </div>
</div>

<div class="page-break"></div>

<!-- ========================================== -->
<!-- HALAMAN 2: IDENTITAS PESERTA DIDIK         -->
<!-- ========================================== -->
<div class="text-center fw-bold" style="font-size: 13pt; margin-bottom: 25px; text-transform: uppercase;">
    IDENTITAS PESERTA DIDIK
</div>

<table class="info-table" style="line-height: 1.8;">
    <tr><td style="width: 5%;">1.</td><td style="width: 35%;">Nama Lengkap Peserta Didik</td><td style="width: 3%;">:</td><td class="fw-bold"><?= htmlspecialchars($siswa['nama'], ENT_QUOTES, 'UTF-8') ?></td></tr>
    <tr><td>2.</td><td>Nomor Induk Siswa (NIS)</td><td>:</td><td><?= htmlspecialchars($siswa['nis'] ?? '-', ENT_QUOTES, 'UTF-8') ?></td></tr>
    <tr><td>3.</td><td>Nomor Induk Siswa Nasional (NISN)</td><td>:</td><td><?= htmlspecialchars($siswa['nisn'] ?? '-', ENT_QUOTES, 'UTF-8') ?></td></tr>
    <tr><td>4.</td><td>Tempat, Tanggal Lahir</td><td>:</td><td><?= htmlspecialchars($siswa['tempat_lahir'] ?? '-', ENT_QUOTES, 'UTF-8') ?>, <?= !empty($siswa['tanggal_lahir']) ? format_indo($siswa['tanggal_lahir']) : '-' ?></td></tr>
    <tr><td>5.</td><td>Jenis Kelamin</td><td>:</td><td><?= ($siswa['jenis_kelamin'] ?? 'L') === 'L' ? 'Laki-laki' : 'Perempuan' ?></td></tr>
    <tr><td>6.</td><td>Agama</td><td>:</td><td><?= htmlspecialchars($siswa['agama'] ?? 'Islam', ENT_QUOTES, 'UTF-8') ?></td></tr>
    <tr><td>7.</td><td>Alamat Tempat Tinggal</td><td>:</td><td><?= htmlspecialchars($siswa['alamat'] ?? '-', ENT_QUOTES, 'UTF-8') ?></td></tr>
    <tr><td>8.</td><td>Nama Orang Tua</td><td></td><td></td></tr>
    <tr><td></td><td style="padding-left: 15px;">a. Nama Ayah</td><td>:</td><td><?= htmlspecialchars($siswa['nama_ayah'] ?? '-', ENT_QUOTES, 'UTF-8') ?></td></tr>
    <tr><td></td><td style="padding-left: 15px;">b. Nama Ibu</td><td>:</td><td><?= htmlspecialchars($siswa['nama_ibu'] ?? '-', ENT_QUOTES, 'UTF-8') ?></td></tr>
    <tr><td>9.</td><td>Nomor Telepon / HP</td><td>:</td><td><?= htmlspecialchars($siswa['hp'] ?? '-', ENT_QUOTES, 'UTF-8') ?></td></tr>
</table>

<table class="signature-table" style="margin-top: 50px;">
    <tr>
        <td style="width: 30%;">
            <div style="width: 3cm; height: 4cm; border: 1px solid #999; margin: 0 auto; line-height: 4cm; color: #888; font-size: 9pt;">
                <?php if (!empty($siswa['foto'])): ?>
                    <img src="<?= base_url('/' . ltrim($siswa['foto'], '/')) ?>" style="width: 100%; height: 100%; object-fit: cover;">
                <?php else: ?>
                    Pas Foto 3x4
                <?php endif; ?>
            </div>
        </td>
        <td style="width: 30%;"></td>
        <td style="width: 40%; text-align: left;">
            <?= htmlspecialchars($sekolah['kota'] ?? 'Kota', ENT_QUOTES, 'UTF-8') ?>, <?= format_indo($raporSetting['tgl_rapor_akhir'] ?? date('Y-m-d')) ?><br>
            Kepala Sekolah,<br><br><br><br><br>
            <span class="fw-bold" style="text-decoration: underline;"><?= htmlspecialchars($sekolah['nama_kepsek'] ?? 'Kepala Sekolah', ENT_QUOTES, 'UTF-8') ?></span><br>
            <?php if (!empty($raporSetting['nip_kepsek']) && !empty($sekolah['nip_kepsek'])): ?>
                NIP. <?= htmlspecialchars($sekolah['nip_kepsek'], ENT_QUOTES, 'UTF-8') ?>
            <?php endif; ?>
        </td>
    </tr>
</table>

<div class="page-break"></div>

<!-- ========================================== -->
<!-- HALAMAN 3: CAPAIAN HASIL BELAJAR AKADEMIK  -->
<!-- ========================================== -->
<table class="info-table">
    <tr>
        <td style="width: 18%;">Nama Peserta Didik</td><td style="width: 2%;">:</td><td style="width: 45%;" class="fw-bold"><?= htmlspecialchars($siswa['nama'], ENT_QUOTES, 'UTF-8') ?></td>
        <td style="width: 15%;">Kelas / Rombel</td><td style="width: 2%;">:</td><td style="width: 18%;"><?= htmlspecialchars($kelas['nama_kelas'], ENT_QUOTES, 'UTF-8') ?></td>
    </tr>
    <tr>
        <td>Nomor Induk / NISN</td><td>:</td><td><?= htmlspecialchars($siswa['nis'] ?? '-', ENT_QUOTES, 'UTF-8') ?> / <?= htmlspecialchars($siswa['nisn'] ?? '-', ENT_QUOTES, 'UTF-8') ?></td>
        <td>Semester</td><td>:</td><td><?= htmlspecialchars($activeSmt['nama_smt'] ?? '-', ENT_QUOTES, 'UTF-8') ?></td>
    </tr>
    <tr>
        <td>Nama Sekolah</td><td>:</td><td><?= htmlspecialchars($sekolah['nama_sekolah'] ?? 'SEKOLAH', ENT_QUOTES, 'UTF-8') ?></td>
        <td>Tahun Pelajaran</td><td>:</td><td><?= htmlspecialchars($activeTp['tahun'] ?? '-', ENT_QUOTES, 'UTF-8') ?></td>
    </tr>
</table>

<div class="fw-bold" style="font-size: 11pt; margin-bottom: 6px;">A. SIKAP</div>
<table class="data-table mb-3">
    <thead>
        <tr>
            <th style="width: 150px;">Aspek Sikap</th>
            <th style="width: 80px;">Predikat</th>
            <th>Deskripsi Catatan Perilaku</th>
        </tr>
    </thead>
    <tbody>
        <tr>
            <td class="fw-bold">1. Sikap Spiritual</td>
            <td class="text-center fw-bold">
                <?php 
                    $spNilai = !empty($sikapSpiritual['nilai']) ? json_decode($sikapSpiritual['nilai'], true) : [];
                    echo htmlspecialchars($spNilai['predikat'] ?? 'B', ENT_QUOTES, 'UTF-8');
                ?>
            </td>
            <td><?= htmlspecialchars($sikapSpiritual['deskripsi'] ?? 'Selalu taat beribadah dan menunjukkan sikap toleransi beragama yang sangat baik.', ENT_QUOTES, 'UTF-8') ?></td>
        </tr>
        <tr>
            <td class="fw-bold">2. Sikap Sosial</td>
            <td class="text-center fw-bold">
                <?php 
                    $soNilai = !empty($sikapSosial['nilai']) ? json_decode($sikapSosial['nilai'], true) : [];
                    echo htmlspecialchars($soNilai['predikat'] ?? 'B', ENT_QUOTES, 'UTF-8');
                ?>
            </td>
            <td><?= htmlspecialchars($sikapSosial['deskripsi'] ?? 'Menunjukkan sikap santun, jujur, peduli, dan bertanggung jawab dalam pergaulan.', ENT_QUOTES, 'UTF-8') ?></td>
        </tr>
    </tbody>
</table>

<div class="fw-bold" style="font-size: 11pt; margin-bottom: 6px;">B. PENGETAHUAN DAN KETERAMPILAN</div>
<table class="data-table">
    <thead>
        <tr>
            <th style="width: 30px;" rowspan="2">No</th>
            <th rowspan="2">Mata Pelajaran</th>
            <th style="width: 45px;" rowspan="2">KKM</th>
            <th colspan="3">Pengetahuan (KI-3)</th>
            <th colspan="3">Keterampilan (KI-4)</th>
        </tr>
        <tr>
            <th style="width: 45px;">Angka</th>
            <th style="width: 40px;">Pred</th>
            <th>Deskripsi Kemajuan</th>
            <th style="width: 45px;">Angka</th>
            <th style="width: 40px;">Pred</th>
            <th>Deskripsi Kemajuan</th>
        </tr>
    </thead>
    <tbody>
        <?php foreach (['A' => 'Kelompok A (Umum / PAI)', 'B' => 'Kelompok B (Umum)', 'C' => 'Kelompok C (Peminatan / Kejuruan)'] as $grpKey => $grpLabel): ?>
            <?php if (!empty($groupedGrades[$grpKey])): ?>
                <tr>
                    <td colspan="9" class="fw-bold" style="background-color: #fafafa; padding-left: 10px;"><?= $grpLabel ?></td>
                </tr>
                <?php $no = 1; foreach ($groupedGrades[$grpKey] as $g): ?>
                    <tr>
                        <td class="text-center"><?= $no++ ?></td>
                        <td><?= htmlspecialchars($g['mapel']['nama_mapel'], ENT_QUOTES, 'UTF-8') ?></td>
                        <td class="text-center"><?= (int)$g['kkm'] ?></td>
                        <td class="text-center"><?= $g['p_nilai'] !== null ? number_format((float)$g['p_nilai'], 1) : '-' ?></td>
                        <td class="text-center fw-bold"><?= htmlspecialchars($g['p_predikat'] ?? '-', ENT_QUOTES, 'UTF-8') ?></td>
                        <td style="font-size: 9pt;"><?= htmlspecialchars($g['p_deskripsi'], ENT_QUOTES, 'UTF-8') ?></td>
                        <td class="text-center"><?= $g['k_nilai'] !== null ? number_format((float)$g['k_nilai'], 1) : '-' ?></td>
                        <td class="text-center fw-bold"><?= htmlspecialchars($g['k_predikat'] ?? '-', ENT_QUOTES, 'UTF-8') ?></td>
                        <td style="font-size: 9pt;"><?= htmlspecialchars($g['k_deskripsi'], ENT_QUOTES, 'UTF-8') ?></td>
                    </tr>
                <?php endforeach; ?>
            <?php endif; ?>
        <?php endforeach; ?>
    </tbody>
</table>

<div class="page-break"></div>

<!-- ========================================== -->
<!-- HALAMAN 4: EKSTRAKURIKULER, KESEHATAN, TTD -->
<!-- ========================================== -->
<div class="fw-bold" style="font-size: 11pt; margin-bottom: 6px;">C. EKSTRAKURIKULER</div>
<table class="data-table mb-3">
    <thead>
        <tr>
            <th style="width: 40px;">No</th>
            <th style="width: 200px;">Kegiatan Ekstrakurikuler</th>
            <th style="width: 80px;">Predikat</th>
            <th>Keterangan Capaian</th>
        </tr>
    </thead>
    <tbody>
        <?php if (!empty($ekstraList)): ?>
            <?php $no = 1; foreach ($ekstraList as $e): ?>
                <tr>
                    <td class="text-center"><?= $no++ ?></td>
                    <td><?= htmlspecialchars($e['nama_ekstra'], ENT_QUOTES, 'UTF-8') ?></td>
                    <td class="text-center fw-bold"><?= htmlspecialchars($e['nilai'] ?? 'B', ENT_QUOTES, 'UTF-8') ?></td>
                    <td><?= htmlspecialchars($e['deskripsi'] ?? 'Aktif mengikuti kegiatan dengan baik.', ENT_QUOTES, 'UTF-8') ?></td>
                </tr>
            <?php endforeach; ?>
        <?php else: ?>
            <tr><td colspan="4" class="text-center text-muted">Belum ada catatan kegiatan ekstrakurikuler.</td></tr>
        <?php endif; ?>
    </tbody>
</table>

<div class="fw-bold" style="font-size: 11pt; margin-bottom: 6px;">D. PRESTASI</div>
<table class="data-table mb-3">
    <thead>
        <tr>
            <th style="width: 40px;">No</th>
            <th style="width: 220px;">Jenis Prestasi / Kejuaraan</th>
            <th>Keterangan / Tingkat</th>
        </tr>
    </thead>
    <tbody>
        <?php if ($prestasi && (!empty($prestasi['p1']) || !empty($prestasi['p2']) || !empty($prestasi['p3']))): ?>
            <?php $pNo = 1; ?>
            <?php if (!empty($prestasi['p1'])): ?>
                <tr><td class="text-center"><?= $pNo++ ?></td><td><?= htmlspecialchars($prestasi['p1'], ENT_QUOTES, 'UTF-8') ?></td><td><?= htmlspecialchars($prestasi['p1_desk'] ?? '-', ENT_QUOTES, 'UTF-8') ?></td></tr>
            <?php endif; ?>
            <?php if (!empty($prestasi['p2'])): ?>
                <tr><td class="text-center"><?= $pNo++ ?></td><td><?= htmlspecialchars($prestasi['p2'], ENT_QUOTES, 'UTF-8') ?></td><td><?= htmlspecialchars($prestasi['p2_desk'] ?? '-', ENT_QUOTES, 'UTF-8') ?></td></tr>
            <?php endif; ?>
            <?php if (!empty($prestasi['p3'])): ?>
                <tr><td class="text-center"><?= $pNo++ ?></td><td><?= htmlspecialchars($prestasi['p3'], ENT_QUOTES, 'UTF-8') ?></td><td><?= htmlspecialchars($prestasi['p3_desk'] ?? '-', ENT_QUOTES, 'UTF-8') ?></td></tr>
            <?php endif; ?>
        <?php else: ?>
            <tr><td colspan="3" class="text-center text-muted">- Tidak ada catatan prestasi khusus semester ini -</td></tr>
        <?php endif; ?>
    </tbody>
</table>

<table style="width: 100%; border-collapse: collapse; margin-bottom: 15px;">
    <tr>
        <td style="width: 50%; vertical-align: top;">
            <div class="fw-bold" style="font-size: 11pt; margin-bottom: 6px;">E. KETIDAKHADIRAN</div>
            <table class="data-table">
                <tr><td style="width: 60%;">Sakit</td><td class="text-center"><?= (int)($absensi['sakit'] ?? 0) ?> hari</td></tr>
                <tr><td>Izin</td><td class="text-center"><?= (int)($absensi['izin'] ?? 0) ?> hari</td></tr>
                <tr><td>Tanpa Keterangan (Alfa)</td><td class="text-center"><?= (int)($absensi['alfa'] ?? 0) ?> hari</td></tr>
            </table>
        </td>
        <td style="width: 5%;"></td>
        <td style="width: 45%; vertical-align: top;">
            <div class="fw-bold" style="font-size: 11pt; margin-bottom: 6px;">F. KONDISI FISIK</div>
            <table class="data-table">
                <tr><td>Tinggi Badan</td><td class="text-center"><?= $fisik['tinggi'] ?? '-' ?> cm</td></tr>
                <tr><td>Berat Badan</td><td class="text-center"><?= $fisik['berat'] ?? '-' ?> kg</td></tr>
                <tr><td>Pendengaran / Penglihatan</td><td class="text-center"><?= htmlspecialchars($fisik['pendengaran'] ?? 'Baik', ENT_QUOTES, 'UTF-8') ?> / <?= htmlspecialchars($fisik['penglihatan'] ?? 'Baik', ENT_QUOTES, 'UTF-8') ?></td></tr>
            </table>
        </td>
    </tr>
</table>

<div class="fw-bold" style="font-size: 11pt; margin-bottom: 6px;">G. CATATAN WALI KELAS</div>
<div style="border: 1px solid #000; padding: 10px; min-height: 40px; margin-bottom: 15px; font-size: 10.5pt;">
    <?= !empty($catatan) ? nl2br(htmlspecialchars($catatan, ENT_QUOTES, 'UTF-8')) : 'Pertahankan prestasi belajar dan tingkatkan rasa tanggung jawab dalam setiap tugas.' ?>
</div>

<?php if (str_contains(strtolower($activeSmt['nama_smt'] ?? ''), 'genap')): ?>
    <div class="fw-bold" style="font-size: 11pt; margin-bottom: 6px;">H. KEPUTUSAN KENAIKAN KELAS / KELULUSAN</div>
    <div style="border: 2px solid #000; padding: 10px; margin-bottom: 15px; font-size: 10.5pt;">
        Berdasarkan hasil pencapaian kompetensi peserta didik selama tahun pelajaran berjalan, ditetapkan:<br>
        <span class="fw-bold" style="font-size: 11pt;">
            <?= ($naik && (int)$naik['naik'] === 1) ? 'NAIK KE KELAS BERIKUTNYA / LULUS' : 'TINGGAL DI KELAS / BELUM LULUS' ?>
        </span>
        <?php if (!empty($naik['keterangan'])): ?>
            <br><em>(<?= htmlspecialchars($naik['keterangan'], ENT_QUOTES, 'UTF-8') ?>)</em>
        <?php endif; ?>
    </div>
<?php endif; ?>

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
            <?= htmlspecialchars($sekolah['kota'] ?? 'Kota', ENT_QUOTES, 'UTF-8') ?>, <?= format_indo($raporSetting['tgl_rapor_akhir'] ?? date('Y-m-d')) ?><br>
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

