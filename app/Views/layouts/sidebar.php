<?php
$role = auth_role() ?? 'siswa';
$currentUri = (new \App\Core\Request())->getUri();
$school = setting_sekolah();
$jenjang = (int)($school['jenjang'] ?? 2);
?>

<nav id="sidebar">
    <a href="<?= base_url('/dashboard') ?>" class="sidebar-brand">
        <img src="<?= base_url('/assets/img/logo.png') ?>" alt="Logo">
        <span>DADO CBT</span>
    </a>

    <div class="sidebar-heading">Menu Utama</div>
    <ul class="nav-sidebar">
        <?php if ($role === 'admin'): ?>
            <li class="nav-item">
                <a href="<?= base_url('/dashboard') ?>" class="nav-link <?= $currentUri === '/dashboard' || $currentUri === '/' ? 'active' : '' ?>">
                    <i class="bi bi-grid-1x2"></i>
                    <span>Beranda</span>
                </a>
            </li>

            <div class="sidebar-heading">Data Master</div>
            <li class="nav-item">
                <a href="<?= base_url('/master/tp-smt') ?>" class="nav-link <?= str_starts_with($currentUri, '/master/tp-smt') ? 'active' : '' ?>">
                    <i class="bi bi-calendar3"></i>
                    <span>TP & Semester</span>
                </a>
            </li>
            <?php if (\App\Config\Jenjang::hasJurusan($jenjang)): ?>
            <li class="nav-item">
                <a href="<?= base_url('/master/jurusan') ?>" class="nav-link <?= str_starts_with($currentUri, '/master/jurusan') ? 'active' : '' ?>">
                    <i class="bi bi-diagram-3"></i>
                    <span>Jurusan / Program</span>
                </a>
            </li>
            <?php endif; ?>
            <li class="nav-item">
                <a href="<?= base_url('/master/kelas') ?>" class="nav-link <?= str_starts_with($currentUri, '/master/kelas') ? 'active' : '' ?>">
                    <i class="bi bi-building"></i>
                    <span>Kelas & Rombel</span>
                </a>
            </li>
            <li class="nav-item">
                <a href="<?= base_url('/master/siswa') ?>" class="nav-link <?= str_starts_with($currentUri, '/master/siswa') ? 'active' : '' ?>">
                    <i class="bi bi-people"></i>
                    <span>Data Siswa</span>
                </a>
            </li>
            <li class="nav-item">
                <a href="<?= base_url('/master/guru') ?>" class="nav-link <?= str_starts_with($currentUri, '/master/guru') ? 'active' : '' ?>">
                    <i class="bi bi-person-badge"></i>
                    <span>Data Guru & Staff</span>
                </a>
            </li>
            <li class="nav-item">
                <a href="<?= base_url('/master/mapel') ?>" class="nav-link <?= str_starts_with($currentUri, '/master/mapel') ? 'active' : '' ?>">
                    <i class="bi bi-journal-bookmark"></i>
                    <span>Mata Pelajaran</span>
                </a>
            </li>
            <li class="nav-item">
                <a href="<?= base_url('/master/ekstra') ?>" class="nav-link <?= str_starts_with($currentUri, '/master/ekstra') ? 'active' : '' ?>">
                    <i class="bi bi-trophy"></i>
                    <span>Ekstrakurikuler</span>
                </a>
            </li>

            <div class="sidebar-heading">E-Learning & KBM</div>
            <li class="nav-item">
                <a href="<?= base_url('/elearning/jadwal-kbm') ?>" class="nav-link <?= str_starts_with($currentUri, '/elearning/jadwal-kbm') ? 'active' : '' ?>">
                    <i class="bi bi-clock-history"></i>
                    <span>Jadwal Pelajaran KBM</span>
                </a>
            </li>
            <li class="nav-item">
                <a href="<?= base_url('/elearning/materi-tugas') ?>" class="nav-link <?= str_starts_with($currentUri, '/elearning/materi-tugas') ? 'active' : '' ?>">
                    <i class="bi bi-file-earmark-text"></i>
                    <span>Materi & Tugas</span>
                </a>
            </li>
            <li class="nav-item">
                <a href="<?= base_url('/elearning/jadwal-materi') ?>" class="nav-link <?= str_starts_with($currentUri, '/elearning/jadwal-materi') ? 'active' : '' ?>">
                    <i class="bi bi-calendar-event"></i>
                    <span>Jadwal Materi / Tugas</span>
                </a>
            </li>
            <li class="nav-item">
                <a href="<?= base_url('/elearning/presensi-nilai') ?>" class="nav-link <?= str_starts_with($currentUri, '/elearning/presensi-nilai') ? 'active' : '' ?>">
                    <i class="bi bi-check2-circle"></i>
                    <span>Presensi & Nilai KBM</span>
                </a>
            </li>

            <div class="sidebar-heading">Ujian CBT (AKM)</div>
            <li class="nav-item">
                <a href="<?= base_url('/cbt/master') ?>" class="nav-link <?= str_starts_with($currentUri, '/cbt/master') ? 'active' : '' ?>">
                    <i class="bi bi-sliders"></i>
                    <span>Master CBT & Ruang</span>
                </a>
            </li>
            <li class="nav-item">
                <a href="<?= base_url('/cbt/bank-soal') ?>" class="nav-link <?= str_starts_with($currentUri, '/cbt/bank-soal') ? 'active' : '' ?>">
                    <i class="bi bi-archive"></i>
                    <span>Bank Soal (5 AKM)</span>
                </a>
            </li>
            <li class="nav-item">
                <a href="<?= base_url('/cbt/jadwal') ?>" class="nav-link <?= str_starts_with($currentUri, '/cbt/jadwal') ? 'active' : '' ?>">
                    <i class="bi bi-alarm"></i>
                    <span>Jadwal Ujian</span>
                </a>
            </li>
            <li class="nav-item">
                <a href="<?= base_url('/cbt/token') ?>" class="nav-link <?= str_starts_with($currentUri, '/cbt/token') ? 'active' : '' ?>">
                    <i class="bi bi-key"></i>
                    <span>Token Ujian</span>
                </a>
            </li>
            <li class="nav-item">
                <a href="<?= base_url('/cbt/cetak') ?>" class="nav-link <?= str_starts_with($currentUri, '/cbt/cetak') ? 'active' : '' ?>">
                    <i class="bi bi-printer"></i>
                    <span>Cetak Administrasi</span>
                </a>
            </li>
            <li class="nav-item">
                <a href="<?= base_url('/cbt/status') ?>" class="nav-link <?= str_starts_with($currentUri, '/cbt/status') ? 'active' : '' ?>">
                    <i class="bi bi-activity"></i>
                    <span>Live Status Siswa</span>
                </a>
            </li>
            <li class="nav-item">
                <a href="<?= base_url('/cbt/koreksi') ?>" class="nav-link <?= str_starts_with($currentUri, '/cbt/koreksi') ? 'active' : '' ?>">
                    <i class="bi bi-pencil-square"></i>
                    <span>Koreksi & Analisis</span>
                </a>
            </li>
            <li class="nav-item">
                <a href="<?= base_url('/cbt/rekap-nilai') ?>" class="nav-link <?= str_starts_with($currentUri, '/cbt/rekap-nilai') ? 'active' : '' ?>">
                    <i class="bi bi-bar-chart-line"></i>
                    <span>Rekap Nilai CBT</span>
                </a>
            </li>

            <div class="sidebar-heading">E-Rapor & Buku Induk</div>
            <li class="nav-item">
                <a href="<?= base_url('/rapor/setting') ?>" class="nav-link <?= str_starts_with($currentUri, '/rapor/setting') ? 'active' : '' ?>">
                    <i class="bi bi-gear-wide"></i>
                    <span>Pengaturan Rapor</span>
                </a>
            </li>
            <li class="nav-item">
                <a href="<?= base_url('/rapor/kkm') ?>" class="nav-link <?= str_starts_with($currentUri, '/rapor/kkm') ? 'active' : '' ?>">
                    <i class="bi bi-award"></i>
                    <span>KKM & Bobot</span>
                </a>
            </li>
            <li class="nav-item">
                <a href="<?= base_url('/rapor/kikd') ?>" class="nav-link <?= str_starts_with($currentUri, '/rapor/kikd') ? 'active' : '' ?>">
                    <i class="bi bi-bookmark-check"></i>
                    <span>Capaian KI/KD</span>
                </a>
            </li>
            <li class="nav-item">
                <a href="<?= base_url('/rapor/input-nilai') ?>" class="nav-link <?= str_starts_with($currentUri, '/rapor/input-nilai') ? 'active' : '' ?>">
                    <i class="bi bi-calculator"></i>
                    <span>Kumpulan Nilai Rapor</span>
                </a>
            </li>
            <li class="nav-item">
                <a href="<?= base_url('/rapor/cetak') ?>" class="nav-link <?= str_starts_with($currentUri, '/rapor/cetak') ? 'active' : '' ?>">
                    <i class="bi bi-printer"></i>
                    <span>Cetak Rapor & DKN</span>
                </a>
            </li>
            <li class="nav-item">
                <a href="<?= base_url('/rapor/buku-induk') ?>" class="nav-link <?= str_starts_with($currentUri, '/rapor/buku-induk') ? 'active' : '' ?>">
                    <i class="bi bi-book"></i>
                    <span>Buku Induk & Alumni</span>
                </a>
            </li>

            <div class="sidebar-heading">Pengaturan Sistem</div>
            <li class="nav-item">
                <a href="<?= base_url('/settings/profile') ?>" class="nav-link <?= str_starts_with($currentUri, '/settings/profile') ? 'active' : '' ?>">
                    <i class="bi bi-building-gear"></i>
                    <span>Profil Sekolah</span>
                </a>
            </li>
            <li class="nav-item">
                <a href="<?= base_url('/settings/users') ?>" class="nav-link <?= str_starts_with($currentUri, '/settings/users') ? 'active' : '' ?>">
                    <i class="bi bi-person-gear"></i>
                    <span>Manajemen User</span>
                </a>
            </li>
            <li class="nav-item">
                <a href="<?= base_url('/settings/announcements') ?>" class="nav-link <?= str_starts_with($currentUri, '/settings/announcements') ? 'active' : '' ?>">
                    <i class="bi bi-megaphone"></i>
                    <span>Pengumuman (Ticker)</span>
                </a>
            </li>
            <li class="nav-item">
                <a href="<?= base_url('/settings/database') ?>" class="nav-link <?= str_starts_with($currentUri, '/settings/database') ? 'active' : '' ?>">
                    <i class="bi bi-database"></i>
                    <span>Backup & Restore DB</span>
                </a>
            </li>

        <?php elseif ($role === 'guru'): ?>
            <li class="nav-item">
                <a href="<?= base_url('/guru/dashboard') ?>" class="nav-link <?= $currentUri === '/guru/dashboard' ? 'active' : '' ?>">
                    <i class="bi bi-grid-1x2"></i>
                    <span>Beranda Guru</span>
                </a>
            </li>
            <li class="nav-item">
                <a href="<?= base_url('/guru/jadwal') ?>" class="nav-link <?= str_starts_with($currentUri, '/guru/jadwal') ? 'active' : '' ?>">
                    <i class="bi bi-calendar3"></i>
                    <span>Jadwal Mengajar Saya</span>
                </a>
            </li>
            <li class="nav-item">
                <a href="<?= base_url('/guru/materi') ?>" class="nav-link <?= str_starts_with($currentUri, '/guru/materi') ? 'active' : '' ?>">
                    <i class="bi bi-file-earmark-text"></i>
                    <span>Materi & Tugas Saya</span>
                </a>
            </li>
            <li class="nav-item">
                <a href="<?= base_url('/guru/bank-soal') ?>" class="nav-link <?= str_starts_with($currentUri, '/guru/bank-soal') ? 'active' : '' ?>">
                    <i class="bi bi-archive"></i>
                    <span>Bank Soal Saya</span>
                </a>
            </li>
            <li class="nav-item">
                <a href="<?= base_url('/guru/pengawas') ?>" class="nav-link <?= str_starts_with($currentUri, '/guru/pengawas') ? 'active' : '' ?>">
                    <i class="bi bi-shield-check"></i>
                    <span>Ruang Pengawas Ujian</span>
                </a>
            </li>
            <li class="nav-item">
                <a href="<?= base_url('/guru/wali-kelas') ?>" class="nav-link <?= str_starts_with($currentUri, '/guru/wali-kelas') ? 'active' : '' ?>">
                    <i class="bi bi-people"></i>
                    <span>Menu Wali Kelas</span>
                </a>
            </li>

        <?php else: /* Siswa */ ?>
            <li class="nav-item">
                <a href="<?= base_url('/student/dashboard') ?>" class="nav-link <?= $currentUri === '/student/dashboard' ? 'active' : '' ?>">
                    <i class="bi bi-laptop"></i>
                    <span>Beranda Siswa</span>
                </a>
            </li>
            <li class="nav-item">
                <a href="<?= base_url('/student/cbt') ?>" class="nav-link <?= str_starts_with($currentUri, '/student/cbt') ? 'active' : '' ?>">
                    <i class="bi bi-file-earmark-check"></i>
                    <span>Daftar Ujian CBT</span>
                </a>
            </li>
            <li class="nav-item">
                <a href="<?= base_url('/student/elearning') ?>" class="nav-link <?= str_starts_with($currentUri, '/student/elearning') ? 'active' : '' ?>">
                    <i class="bi bi-book-half"></i>
                    <span>Ruang Belajar & Tugas</span>
                </a>
            </li>
            <li class="nav-item">
                <a href="<?= base_url('/student/riwayat') ?>" class="nav-link <?= str_starts_with($currentUri, '/student/riwayat') ? 'active' : '' ?>">
                    <i class="bi bi-journal-check"></i>
                    <span>Riwayat Hasil Ujian</span>
                </a>
            </li>
        <?php endif; ?>
    </ul>
</nav>

