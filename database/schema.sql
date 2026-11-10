-- ==============================================================================
-- GARUDACBT REBUILD v1.5.3 (FIXED) — COMPLETE DATABASE SCHEMA
-- Engine: InnoDB | Charset: utf8mb4 | Collation: utf8mb4_unicode_ci
-- High Concurrency Optimized (Target: 1,000+ Concurrent Exam Takers)
-- ==============================================================================

SET FOREIGN_KEY_CHECKS = 0;

-- ------------------------------------------------------------------------------
-- 1. KLASTER PENGATURAN, AUTENTIKASI, & DATA MASTER
-- ------------------------------------------------------------------------------

-- 1.1 Tabel users
DROP TABLE IF EXISTS `users`;
CREATE TABLE `users` (
  `id` INT AUTO_INCREMENT PRIMARY KEY,
  `username` VARCHAR(100) NOT NULL UNIQUE,
  `password` VARCHAR(255) NOT NULL,
  `email` VARCHAR(150) NULL,
  `role` ENUM('admin', 'guru', 'siswa') NOT NULL DEFAULT 'siswa',
  `session_token` VARCHAR(100) NULL,
  `active` TINYINT(1) NOT NULL DEFAULT 1,
  `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  `updated_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  INDEX `idx_users_role` (`role`),
  INDEX `idx_users_active` (`active`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- 1.2 Tabel setting_sekolah
DROP TABLE IF EXISTS `setting_sekolah`;
CREATE TABLE `setting_sekolah` (
  `id_setting` INT AUTO_INCREMENT PRIMARY KEY,
  `nama_sekolah` VARCHAR(200) NOT NULL,
  `npsn` VARCHAR(30) NULL,
  `jenjang` TINYINT NOT NULL DEFAULT 2 COMMENT '1=SD/MI, 2=SMP/MTs, 3=SMA/SMK/MA',
  `satuan_pendidikan` VARCHAR(50) NULL DEFAULT 'SMP',
  `alamat` TEXT NULL,
  `desa` VARCHAR(100) NULL,
  `kecamatan` VARCHAR(100) NULL,
  `kota` VARCHAR(100) NULL,
  `provinsi` VARCHAR(100) NULL,
  `kode_pos` VARCHAR(10) NULL,
  `telp` VARCHAR(50) NULL,
  `fax` VARCHAR(50) NULL,
  `email` VARCHAR(100) NULL,
  `web` VARCHAR(150) NULL,
  `kepsek` VARCHAR(150) NULL,
  `nip_kepsek` VARCHAR(50) NULL,
  `tanda_tangan` VARCHAR(255) NULL,
  `logo_kiri` VARCHAR(255) NULL,
  `logo_kanan` VARCHAR(255) NULL,
  `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  `updated_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- 1.3 Tabel master_tp
DROP TABLE IF EXISTS `master_tp`;
CREATE TABLE `master_tp` (
  `id_tp` INT AUTO_INCREMENT PRIMARY KEY,
  `tahun` VARCHAR(20) NOT NULL,
  `active` TINYINT(1) NOT NULL DEFAULT 0,
  INDEX `idx_tp_active` (`active`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- 1.4 Tabel master_smt
DROP TABLE IF EXISTS `master_smt`;
CREATE TABLE `master_smt` (
  `id_smt` INT AUTO_INCREMENT PRIMARY KEY,
  `smt` VARCHAR(10) NOT NULL COMMENT '1 atau 2',
  `nama_smt` VARCHAR(30) NOT NULL COMMENT 'Ganjil / Genap',
  `active` TINYINT(1) NOT NULL DEFAULT 0,
  INDEX `idx_smt_active` (`active`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- 1.5 Tabel master_jurusan
DROP TABLE IF EXISTS `master_jurusan`;
CREATE TABLE `master_jurusan` (
  `id_jurusan` INT AUTO_INCREMENT PRIMARY KEY,
  `kode_jurusan` VARCHAR(20) NOT NULL,
  `nama_jurusan` VARCHAR(100) NOT NULL,
  `mapel_peminatan` JSON NULL,
  `status` TINYINT(1) NOT NULL DEFAULT 1 COMMENT 'Aktif jika jenjang=3',
  INDEX `idx_jurusan_status` (`status`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- 1.6 Tabel master_mapel
DROP TABLE IF EXISTS `master_mapel`;
CREATE TABLE `master_mapel` (
  `id_mapel` INT AUTO_INCREMENT PRIMARY KEY,
  `nama_mapel` VARCHAR(100) NOT NULL,
  `kode` VARCHAR(30) NOT NULL,
  `kelompok` VARCHAR(20) NOT NULL DEFAULT 'A' COMMENT 'A, B, C, MULOK, PAI',
  `bobot` INT NOT NULL DEFAULT 100,
  `jenjang` TINYINT NOT NULL DEFAULT 2,
  `status` TINYINT(1) NOT NULL DEFAULT 1,
  `urutan_tampil` INT NOT NULL DEFAULT 0,
  INDEX `idx_mapel_kelompok` (`kelompok`),
  INDEX `idx_mapel_status` (`status`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- 1.7 Tabel master_ekstra
DROP TABLE IF EXISTS `master_ekstra`;
CREATE TABLE `master_ekstra` (
  `id_ekstra` INT AUTO_INCREMENT PRIMARY KEY,
  `nama_ekstra` VARCHAR(100) NOT NULL,
  `kode_ekstra` VARCHAR(30) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- 1.8 Tabel master_siswa
DROP TABLE IF EXISTS `master_siswa`;
CREATE TABLE `master_siswa` (
  `id_siswa` INT AUTO_INCREMENT PRIMARY KEY,
  `id_user` INT NULL,
  `nisn` VARCHAR(20) NULL,
  `nis` VARCHAR(30) NOT NULL UNIQUE,
  `nama` VARCHAR(150) NOT NULL,
  `jk` ENUM('L', 'P') NOT NULL DEFAULT 'L',
  `tempat_lahir` VARCHAR(100) NULL,
  `tgl_lahir` DATE NULL,
  `agama` VARCHAR(50) NULL DEFAULT 'Islam',
  `alamat` TEXT NULL,
  `nama_ayah` VARCHAR(150) NULL,
  `nama_ibu` VARCHAR(150) NULL,
  `no_hp` VARCHAR(30) NULL,
  `tahun_masuk` VARCHAR(10) NULL,
  `kelas_awal` VARCHAR(20) NULL,
  `foto` VARCHAR(255) NULL,
  `status` TINYINT NOT NULL DEFAULT 1 COMMENT '1=Aktif, 2=Lulus, 3=Pindah/Keluar',
  `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  `updated_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  INDEX `idx_siswa_user` (`id_user`),
  INDEX `idx_siswa_nis` (`nis`),
  INDEX `idx_siswa_nisn` (`nisn`),
  INDEX `idx_siswa_status` (`status`),
  CONSTRAINT `fk_siswa_user` FOREIGN KEY (`id_user`) REFERENCES `users` (`id`) ON DELETE SET NULL ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- 1.9 Tabel master_guru
DROP TABLE IF EXISTS `master_guru`;
CREATE TABLE `master_guru` (
  `id_guru` INT AUTO_INCREMENT PRIMARY KEY,
  `id_user` INT NULL,
  `nip` VARCHAR(50) NULL,
  `nama_guru` VARCHAR(150) NOT NULL,
  `kode_guru` VARCHAR(20) NULL,
  `jk` ENUM('L', 'P') NOT NULL DEFAULT 'L',
  `no_hp` VARCHAR(30) NULL,
  `alamat` TEXT NULL,
  `foto` VARCHAR(255) NULL,
  `id_jabatan` INT NULL,
  `status` TINYINT(1) NOT NULL DEFAULT 1,
  `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  `updated_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  INDEX `idx_guru_user` (`id_user`),
  INDEX `idx_guru_nip` (`nip`),
  CONSTRAINT `fk_guru_user` FOREIGN KEY (`id_user`) REFERENCES `users` (`id`) ON DELETE SET NULL ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- 1.10 Tabel master_kelas
DROP TABLE IF EXISTS `master_kelas`;
CREATE TABLE `master_kelas` (
  `id_kelas` INT AUTO_INCREMENT PRIMARY KEY,
  `id_tp` INT NOT NULL,
  `id_smt` INT NOT NULL,
  `nama_kelas` VARCHAR(50) NOT NULL,
  `kode_kelas` VARCHAR(30) NOT NULL,
  `jurusan_id` INT NULL,
  `level_id` TINYINT NOT NULL COMMENT '1-12',
  `guru_id` INT NULL COMMENT 'FK Wali Kelas',
  `siswa_id` JSON NULL,
  INDEX `idx_kelas_tpsmt_level` (`id_tp`, `id_smt`, `level_id`),
  CONSTRAINT `fk_kelas_tp` FOREIGN KEY (`id_tp`) REFERENCES `master_tp` (`id_tp`) ON DELETE CASCADE ON UPDATE CASCADE,
  CONSTRAINT `fk_kelas_smt` FOREIGN KEY (`id_smt`) REFERENCES `master_smt` (`id_smt`) ON DELETE CASCADE ON UPDATE CASCADE,
  CONSTRAINT `fk_kelas_guru` FOREIGN KEY (`guru_id`) REFERENCES `master_guru` (`id_guru`) ON DELETE SET NULL ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- 1.11 Tabel kelas_siswa
DROP TABLE IF EXISTS `kelas_siswa`;
CREATE TABLE `kelas_siswa` (
  `id_kelas_siswa` INT AUTO_INCREMENT PRIMARY KEY,
  `id_tp` INT NOT NULL,
  `id_smt` INT NOT NULL,
  `id_kelas` INT NOT NULL,
  `id_siswa` INT NOT NULL,
  UNIQUE INDEX `uq_kelas_siswa_tpsmt` (`id_tp`, `id_smt`, `id_siswa`),
  INDEX `idx_kelas_siswa_kelas` (`id_kelas`),
  CONSTRAINT `fk_ks_tp` FOREIGN KEY (`id_tp`) REFERENCES `master_tp` (`id_tp`) ON DELETE CASCADE,
  CONSTRAINT `fk_ks_smt` FOREIGN KEY (`id_smt`) REFERENCES `master_smt` (`id_smt`) ON DELETE CASCADE,
  CONSTRAINT `fk_ks_kelas` FOREIGN KEY (`id_kelas`) REFERENCES `master_kelas` (`id_kelas`) ON DELETE CASCADE,
  CONSTRAINT `fk_ks_siswa` FOREIGN KEY (`id_siswa`) REFERENCES `master_siswa` (`id_siswa`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- 1.12 Tabel jabatan_guru
DROP TABLE IF EXISTS `jabatan_guru`;
CREATE TABLE `jabatan_guru` (
  `id_jabatan_guru` INT AUTO_INCREMENT PRIMARY KEY,
  `id_guru` INT NOT NULL,
  `id_tp` INT NOT NULL,
  `id_smt` INT NOT NULL,
  `id_jabatan` INT NOT NULL DEFAULT 3 COMMENT '1=Kepsek, 2=Wali, 3=Guru Mapel, 4=Pengawas',
  `id_kelas` INT NULL COMMENT 'Wali kelas untuk id_kelas tertentu',
  `mapel_kelas` JSON NULL COMMENT 'Pemetaan mapel & kelas yang diampu',
  UNIQUE INDEX `uq_jabatan_guru_tpsmt` (`id_guru`, `id_tp`, `id_smt`),
  CONSTRAINT `fk_jg_guru` FOREIGN KEY (`id_guru`) REFERENCES `master_guru` (`id_guru`) ON DELETE CASCADE,
  CONSTRAINT `fk_jg_tp` FOREIGN KEY (`id_tp`) REFERENCES `master_tp` (`id_tp`) ON DELETE CASCADE,
  CONSTRAINT `fk_jg_smt` FOREIGN KEY (`id_smt`) REFERENCES `master_smt` (`id_smt`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- 1.13 Tabel running_text
DROP TABLE IF EXISTS `running_text`;
CREATE TABLE `running_text` (
  `id_text` INT AUTO_INCREMENT PRIMARY KEY,
  `text` TEXT NOT NULL,
  `tujuan` ENUM('guru', 'siswa', 'semua') NOT NULL DEFAULT 'semua',
  `active` TINYINT(1) NOT NULL DEFAULT 1
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ------------------------------------------------------------------------------
-- 2. KLASTER E-LEARNING (KBM HARIAN)
-- ------------------------------------------------------------------------------

-- 2.1 Tabel kelas_jadwal_kbm
DROP TABLE IF EXISTS `kelas_jadwal_kbm`;
CREATE TABLE `kelas_jadwal_kbm` (
  `id_kbm` INT AUTO_INCREMENT PRIMARY KEY,
  `id_tp` INT NOT NULL,
  `id_smt` INT NOT NULL,
  `id_kelas` INT NOT NULL,
  `kbm_jam_pel` INT NOT NULL DEFAULT 40 COMMENT 'menit per jam pelajaran',
  `kbm_jam_mulai` TIME NOT NULL DEFAULT '07:00:00',
  `kbm_jml_mapel_hari` INT NOT NULL DEFAULT 6,
  `istirahat` JSON NULL COMMENT '[{"ist":1,"mulai":"09:40","durasi":20}]',
  UNIQUE INDEX `uq_jadwal_kbm_kelas` (`id_tp`, `id_smt`, `id_kelas`),
  CONSTRAINT `fk_kbm_tp` FOREIGN KEY (`id_tp`) REFERENCES `master_tp` (`id_tp`) ON DELETE CASCADE,
  CONSTRAINT `fk_kbm_smt` FOREIGN KEY (`id_smt`) REFERENCES `master_smt` (`id_smt`) ON DELETE CASCADE,
  CONSTRAINT `fk_kbm_kelas` FOREIGN KEY (`id_kelas`) REFERENCES `master_kelas` (`id_kelas`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- 2.2 Tabel kelas_jadwal_mapel
DROP TABLE IF EXISTS `kelas_jadwal_mapel`;
CREATE TABLE `kelas_jadwal_mapel` (
  `id_jadwal` INT AUTO_INCREMENT PRIMARY KEY,
  `id_tp` INT NOT NULL,
  `id_smt` INT NOT NULL,
  `id_kelas` INT NOT NULL,
  `id_hari` TINYINT NOT NULL COMMENT '1=Senin, 2=Selasa, 3=Rabu, 4=Kamis, 5=Jumat, 6=Sabtu, 7=Minggu',
  `jam_ke` INT NOT NULL,
  `id_mapel` INT NOT NULL,
  `id_guru` INT NULL,
  INDEX `idx_jadwal_mapel_matrix` (`id_tp`, `id_smt`, `id_kelas`, `id_hari`),
  CONSTRAINT `fk_kjm_tp` FOREIGN KEY (`id_tp`) REFERENCES `master_tp` (`id_tp`) ON DELETE CASCADE,
  CONSTRAINT `fk_kjm_smt` FOREIGN KEY (`id_smt`) REFERENCES `master_smt` (`id_smt`) ON DELETE CASCADE,
  CONSTRAINT `fk_kjm_kelas` FOREIGN KEY (`id_kelas`) REFERENCES `master_kelas` (`id_kelas`) ON DELETE CASCADE,
  CONSTRAINT `fk_kjm_mapel` FOREIGN KEY (`id_mapel`) REFERENCES `master_mapel` (`id_mapel`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- 2.3 Tabel kelas_materi
DROP TABLE IF EXISTS `kelas_materi`;
CREATE TABLE `kelas_materi` (
  `id_materi` INT AUTO_INCREMENT PRIMARY KEY,
  `id_tp` INT NOT NULL,
  `id_smt` INT NOT NULL,
  `jenis` TINYINT NOT NULL DEFAULT 1 COMMENT '1=Materi, 2=Tugas',
  `kode_materi` VARCHAR(50) NOT NULL,
  `id_guru` INT NOT NULL,
  `id_mapel` INT NOT NULL,
  `id_kelas` JSON NOT NULL COMMENT 'Array ID kelas penerima materi/tugas',
  `judul_materi` VARCHAR(255) NOT NULL,
  `isi_materi` LONGTEXT NOT NULL,
  `file` JSON NULL COMMENT 'Array lampiran berkas',
  `link_video` VARCHAR(255) NULL,
  `status` TINYINT(1) NOT NULL DEFAULT 1,
  `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  `updated_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  INDEX `idx_materi_tpsmt_guru_jenis` (`id_tp`, `id_smt`, `id_guru`, `jenis`),
  CONSTRAINT `fk_km_tp` FOREIGN KEY (`id_tp`) REFERENCES `master_tp` (`id_tp`) ON DELETE CASCADE,
  CONSTRAINT `fk_km_smt` FOREIGN KEY (`id_smt`) REFERENCES `master_smt` (`id_smt`) ON DELETE CASCADE,
  CONSTRAINT `fk_km_guru` FOREIGN KEY (`id_guru`) REFERENCES `master_guru` (`id_guru`) ON DELETE CASCADE,
  CONSTRAINT `fk_km_mapel` FOREIGN KEY (`id_mapel`) REFERENCES `master_mapel` (`id_mapel`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- 2.4 Tabel kelas_jadwal_materi
DROP TABLE IF EXISTS `kelas_jadwal_materi`;
CREATE TABLE `kelas_jadwal_materi` (
  `id_kjm` INT AUTO_INCREMENT PRIMARY KEY,
  `id_tp` INT NOT NULL,
  `id_smt` INT NOT NULL,
  `id_kelas` INT NOT NULL,
  `id_materi` INT NOT NULL,
  `id_mapel` INT NOT NULL,
  `jenis` TINYINT NOT NULL DEFAULT 1 COMMENT '1=Materi, 2=Tugas',
  `jadwal_materi` DATE NOT NULL,
  INDEX `idx_jadwal_materi_lookup` (`id_kelas`, `jadwal_materi`, `jenis`),
  CONSTRAINT `fk_kjmat_tp` FOREIGN KEY (`id_tp`) REFERENCES `master_tp` (`id_tp`) ON DELETE CASCADE,
  CONSTRAINT `fk_kjmat_smt` FOREIGN KEY (`id_smt`) REFERENCES `master_smt` (`id_smt`) ON DELETE CASCADE,
  CONSTRAINT `fk_kjmat_kelas` FOREIGN KEY (`id_kelas`) REFERENCES `master_kelas` (`id_kelas`) ON DELETE CASCADE,
  CONSTRAINT `fk_kjmat_materi` FOREIGN KEY (`id_materi`) REFERENCES `kelas_materi` (`id_materi`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- 2.5 Tabel log_materi
DROP TABLE IF EXISTS `log_materi`;
CREATE TABLE `log_materi` (
  `id_log` INT AUTO_INCREMENT PRIMARY KEY,
  `id_siswa` INT NOT NULL,
  `id_kjm` INT NOT NULL,
  `jam_ke` INT NULL,
  `log_type` TINYINT NOT NULL DEFAULT 1 COMMENT '1=Baca, 2=Selesai, 3=Kumpul Tugas',
  `log_time` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `finish_time` DATETIME NULL,
  `isi_tugas` TEXT NULL,
  `file` JSON NULL,
  `nilai` DECIMAL(5,2) NULL,
  `catatan` TEXT NULL,
  UNIQUE INDEX `uq_log_materi_siswa` (`id_siswa`, `id_kjm`),
  CONSTRAINT `fk_lm_siswa` FOREIGN KEY (`id_siswa`) REFERENCES `master_siswa` (`id_siswa`) ON DELETE CASCADE,
  CONSTRAINT `fk_lm_kjm` FOREIGN KEY (`id_kjm`) REFERENCES `kelas_jadwal_materi` (`id_kjm`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- 2.6 Tabel kelas_materi_komentar
DROP TABLE IF EXISTS `kelas_materi_komentar`;
CREATE TABLE `kelas_materi_komentar` (
  `id_komentar` INT AUTO_INCREMENT PRIMARY KEY,
  `id_materi` INT NOT NULL,
  `id_user` INT NOT NULL,
  `komentar` TEXT NOT NULL,
  `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  INDEX `idx_materi_komentar` (`id_materi`, `created_at`),
  CONSTRAINT `fk_kmk_materi` FOREIGN KEY (`id_materi`) REFERENCES `kelas_materi` (`id_materi`) ON DELETE CASCADE,
  CONSTRAINT `fk_kmk_user` FOREIGN KEY (`id_user`) REFERENCES `users` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ------------------------------------------------------------------------------
-- 3. KLASTER PERSIAPAN & BANK SOAL CBT
-- ------------------------------------------------------------------------------

-- 3.1 Tabel cbt_jenis
DROP TABLE IF EXISTS `cbt_jenis`;
CREATE TABLE `cbt_jenis` (
  `id_jenis` INT AUTO_INCREMENT PRIMARY KEY,
  `nama_jenis` VARCHAR(100) NOT NULL,
  `kode_jenis` VARCHAR(30) NOT NULL COMMENT 'PH, PTS, PAS, PAT, USBK, TO, ANBK'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- 3.2 Tabel cbt_ruang
DROP TABLE IF EXISTS `cbt_ruang`;
CREATE TABLE `cbt_ruang` (
  `id_ruang` INT AUTO_INCREMENT PRIMARY KEY,
  `nama_ruang` VARCHAR(100) NOT NULL,
  `kode_ruang` VARCHAR(30) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- 3.3 Tabel cbt_sesi
DROP TABLE IF EXISTS `cbt_sesi`;
CREATE TABLE `cbt_sesi` (
  `id_sesi` INT AUTO_INCREMENT PRIMARY KEY,
  `nama_sesi` VARCHAR(100) NOT NULL,
  `kode_sesi` VARCHAR(30) NOT NULL,
  `waktu_mulai` TIME NOT NULL,
  `waktu_akhir` TIME NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- 3.4 Tabel cbt_sesi_siswa
DROP TABLE IF EXISTS `cbt_sesi_siswa`;
CREATE TABLE `cbt_sesi_siswa` (
  `id_sesi_siswa` INT AUTO_INCREMENT PRIMARY KEY,
  `siswa_id` INT NOT NULL,
  `kelas_id` INT NOT NULL,
  `ruang_id` INT NOT NULL,
  `sesi_id` INT NOT NULL,
  `tp_id` INT NOT NULL,
  `smt_id` INT NOT NULL,
  UNIQUE INDEX `uq_sesi_siswa_tpsmt` (`siswa_id`, `tp_id`, `smt_id`),
  INDEX `idx_sesi_siswa_ruang` (`ruang_id`, `sesi_id`),
  CONSTRAINT `fk_css_siswa` FOREIGN KEY (`siswa_id`) REFERENCES `master_siswa` (`id_siswa`) ON DELETE CASCADE,
  CONSTRAINT `fk_css_ruang` FOREIGN KEY (`ruang_id`) REFERENCES `cbt_ruang` (`id_ruang`) ON DELETE CASCADE,
  CONSTRAINT `fk_css_sesi` FOREIGN KEY (`sesi_id`) REFERENCES `cbt_sesi` (`id_sesi`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- 3.5 Tabel cbt_nomor_peserta
DROP TABLE IF EXISTS `cbt_nomor_peserta`;
CREATE TABLE `cbt_nomor_peserta` (
  `id_nomor` INT AUTO_INCREMENT PRIMARY KEY,
  `id_siswa` INT NOT NULL,
  `id_tp` INT NOT NULL,
  `nomor_peserta` VARCHAR(30) NOT NULL,
  UNIQUE INDEX `uq_nomor_peserta_tp` (`id_siswa`, `id_tp`),
  INDEX `idx_nomor_peserta_val` (`nomor_peserta`),
  CONSTRAINT `fk_cnp_siswa` FOREIGN KEY (`id_siswa`) REFERENCES `master_siswa` (`id_siswa`) ON DELETE CASCADE,
  CONSTRAINT `fk_cnp_tp` FOREIGN KEY (`id_tp`) REFERENCES `master_tp` (`id_tp`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- 3.6 Tabel cbt_bank_soal
DROP TABLE IF EXISTS `cbt_bank_soal`;
CREATE TABLE `cbt_bank_soal` (
  `id_bank` INT AUTO_INCREMENT PRIMARY KEY,
  `id_tp` INT NOT NULL,
  `id_smt` INT NOT NULL,
  `bank_jenis_id` INT NULL,
  `bank_kode` VARCHAR(50) NOT NULL,
  `bank_level` TINYINT NOT NULL COMMENT 'Tingkat kelas 1-12',
  `bank_kelas` JSON NULL COMMENT 'Array id_kelas terkait',
  `bank_mapel_id` INT NOT NULL,
  `bank_jurusan_id` INT NULL,
  `bank_guru_id` INT NOT NULL,
  `jml_soal` INT NOT NULL DEFAULT 0,
  `bobot_pg` INT NOT NULL DEFAULT 0,
  `tampil_pg` INT NOT NULL DEFAULT 0,
  `opsi` TINYINT NOT NULL DEFAULT 4 COMMENT '3=A-C, 4=A-D, 5=A-E',
  `jml_kompleks` INT NOT NULL DEFAULT 0,
  `bobot_kompleks` INT NOT NULL DEFAULT 0,
  `tampil_kompleks` INT NOT NULL DEFAULT 0,
  `jml_jodohkan` INT NOT NULL DEFAULT 0,
  `bobot_jodohkan` INT NOT NULL DEFAULT 0,
  `tampil_jodohkan` INT NOT NULL DEFAULT 0,
  `jml_isian` INT NOT NULL DEFAULT 0,
  `bobot_isian` INT NOT NULL DEFAULT 0,
  `tampil_isian` INT NOT NULL DEFAULT 0,
  `jml_esai` INT NOT NULL DEFAULT 0,
  `bobot_esai` INT NOT NULL DEFAULT 0,
  `tampil_esai` INT NOT NULL DEFAULT 0,
  `soal_agama` VARCHAR(50) NULL DEFAULT '-',
  `status` TINYINT(1) NOT NULL DEFAULT 1 COMMENT '1=Aktif, 0=Nonaktif',
  `status_soal` TINYINT NOT NULL DEFAULT 0 COMMENT '0=Draft, 1=Selesai',
  `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  `updated_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  INDEX `idx_bank_tpsmt_mapel_guru` (`id_tp`, `id_smt`, `bank_mapel_id`, `bank_guru_id`),
  CONSTRAINT `fk_cbs_tp` FOREIGN KEY (`id_tp`) REFERENCES `master_tp` (`id_tp`) ON DELETE CASCADE,
  CONSTRAINT `fk_cbs_smt` FOREIGN KEY (`id_smt`) REFERENCES `master_smt` (`id_smt`) ON DELETE CASCADE,
  CONSTRAINT `fk_cbs_mapel` FOREIGN KEY (`bank_mapel_id`) REFERENCES `master_mapel` (`id_mapel`) ON DELETE CASCADE,
  CONSTRAINT `fk_cbs_guru` FOREIGN KEY (`bank_guru_id`) REFERENCES `master_guru` (`id_guru`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- 3.7 Tabel cbt_soal
DROP TABLE IF EXISTS `cbt_soal`;
CREATE TABLE `cbt_soal` (
  `id_soal` INT AUTO_INCREMENT PRIMARY KEY,
  `bank_id` INT NOT NULL,
  `mapel_id` INT NULL,
  `jenis` TINYINT NOT NULL DEFAULT 1 COMMENT '1=PG, 2=PGK, 3=Menjodohkan, 4=Isian, 5=Esai',
  `nomor_soal` INT NOT NULL,
  `soal` LONGTEXT NOT NULL,
  `opsi_a` LONGTEXT NULL,
  `opsi_b` LONGTEXT NULL,
  `opsi_c` LONGTEXT NULL,
  `opsi_d` LONGTEXT NULL,
  `opsi_e` LONGTEXT NULL,
  `jawaban` LONGTEXT NOT NULL COMMENT 'Kunci jawaban: huruf, JSON array, JSON structure menjodohkan, atau teks',
  `tampilkan` TINYINT(1) NOT NULL DEFAULT 1,
  `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  `updated_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  INDEX `idx_soal_bank_jenis_tampil` (`bank_id`, `jenis`, `tampilkan`),
  CONSTRAINT `fk_soal_bank` FOREIGN KEY (`bank_id`) REFERENCES `cbt_bank_soal` (`id_bank`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- 3.8 Tabel cbt_jadwal
DROP TABLE IF EXISTS `cbt_jadwal`;
CREATE TABLE `cbt_jadwal` (
  `id_jadwal` INT AUTO_INCREMENT PRIMARY KEY,
  `id_tp` INT NOT NULL,
  `id_smt` INT NOT NULL,
  `id_bank` INT NOT NULL,
  `id_jenis` INT NOT NULL,
  `tgl_mulai` DATE NOT NULL,
  `tgl_selesai` DATE NOT NULL,
  `durasi_ujian` INT NOT NULL COMMENT 'durasi menit',
  `pengawas` JSON NULL,
  `acak_soal` TINYINT(1) NOT NULL DEFAULT 1,
  `acak_opsi` TINYINT(1) NOT NULL DEFAULT 1,
  `hasil_tampil` TINYINT(1) NOT NULL DEFAULT 0,
  `token` TINYINT(1) NOT NULL DEFAULT 1,
  `status` TINYINT(1) NOT NULL DEFAULT 1,
  `ulang` TINYINT(1) NOT NULL DEFAULT 0,
  `reset_login` TINYINT(1) NOT NULL DEFAULT 0,
  `rekap` TINYINT(1) NOT NULL DEFAULT 0,
  `jam_ke` INT NOT NULL DEFAULT 1 COMMENT 'Alokasi waktu ke-1, 2, dst.',
  `jarak` INT NOT NULL DEFAULT 0 COMMENT 'Jeda menit antar ujian',
  `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  `updated_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  INDEX `idx_jadwal_tpsmt_status_tgl` (`id_tp`, `id_smt`, `status`, `tgl_mulai`),
  CONSTRAINT `fk_cj_tp` FOREIGN KEY (`id_tp`) REFERENCES `master_tp` (`id_tp`) ON DELETE CASCADE,
  CONSTRAINT `fk_cj_smt` FOREIGN KEY (`id_smt`) REFERENCES `master_smt` (`id_smt`) ON DELETE CASCADE,
  CONSTRAINT `fk_cj_bank` FOREIGN KEY (`id_bank`) REFERENCES `cbt_bank_soal` (`id_bank`) ON DELETE CASCADE,
  CONSTRAINT `fk_cj_jenis` FOREIGN KEY (`id_jenis`) REFERENCES `cbt_jenis` (`id_jenis`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- 3.9 Tabel cbt_pengawas
DROP TABLE IF EXISTS `cbt_pengawas`;
CREATE TABLE `cbt_pengawas` (
  `id_pengawas` INT AUTO_INCREMENT PRIMARY KEY,
  `id_tp` INT NOT NULL,
  `id_smt` INT NOT NULL,
  `id_jadwal` INT NOT NULL,
  `id_ruang` INT NOT NULL,
  `id_sesi` INT NOT NULL,
  `id_guru` JSON NOT NULL COMMENT 'JSON array ID guru pengawas',
  UNIQUE INDEX `uq_pengawas_jadwal_ruang_sesi` (`id_jadwal`, `id_ruang`, `id_sesi`),
  CONSTRAINT `fk_cp_jadwal` FOREIGN KEY (`id_jadwal`) REFERENCES `cbt_jadwal` (`id_jadwal`) ON DELETE CASCADE,
  CONSTRAINT `fk_cp_ruang` FOREIGN KEY (`id_ruang`) REFERENCES `cbt_ruang` (`id_ruang`) ON DELETE CASCADE,
  CONSTRAINT `fk_cp_sesi` FOREIGN KEY (`id_sesi`) REFERENCES `cbt_sesi` (`id_sesi`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- 3.10 Tabel cbt_token
DROP TABLE IF EXISTS `cbt_token`;
CREATE TABLE `cbt_token` (
  `id_token` INT AUTO_INCREMENT PRIMARY KEY,
  `token` VARCHAR(6) NOT NULL,
  `auto` TINYINT(1) NOT NULL DEFAULT 0 COMMENT '0=Manual, 1=Otomatis',
  `jarak` INT NOT NULL DEFAULT 15 COMMENT 'menit interval generate baru',
  `updated_at` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- 3.11 Tabel cbt_kop_absensi
DROP TABLE IF EXISTS `cbt_kop_absensi`;
CREATE TABLE `cbt_kop_absensi` (
  `id_kop` INT AUTO_INCREMENT PRIMARY KEY,
  `header_1` VARCHAR(255) NULL,
  `header_2` VARCHAR(255) NULL,
  `header_3` VARCHAR(255) NULL,
  `header_4` VARCHAR(255) NULL,
  `proktor` VARCHAR(150) NULL,
  `pengawas_1` VARCHAR(150) NULL,
  `pengawas_2` VARCHAR(150) NULL,
  `tanggal` VARCHAR(50) NULL,
  `kota` VARCHAR(100) NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ------------------------------------------------------------------------------
-- 4. KLASTER PELAKSANAAN & SKOR UJIAN (HIGH-TRAFFIC TABLES)
-- ------------------------------------------------------------------------------

-- 4.1 Tabel cbt_durasi_siswa
DROP TABLE IF EXISTS `cbt_durasi_siswa`;
CREATE TABLE `cbt_durasi_siswa` (
  `id_durasi` BIGINT AUTO_INCREMENT PRIMARY KEY,
  `id_siswa` INT NOT NULL,
  `id_jadwal` INT NOT NULL,
  `status` TINYINT NOT NULL DEFAULT 0 COMMENT '0=Belum, 1=Sedang Ujian, 2=Selesai',
  `lama_ujian` INT NOT NULL DEFAULT 0 COMMENT 'durasi terpakai dalam detik/menit',
  `mulai` DATETIME NULL,
  `selesai` DATETIME NULL,
  `reset` TINYINT NOT NULL DEFAULT 0 COMMENT '0=Normal, 1=Izin Reset Waktu, 2=Reset Login, 3=Paksa Selesai',
  `pelanggaran_tab` INT NOT NULL DEFAULT 0,
  `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  `updated_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  UNIQUE INDEX `uq_durasi_jadwal_siswa` (`id_jadwal`, `id_siswa`),
  INDEX `idx_durasi_status` (`status`),
  CONSTRAINT `fk_cds_siswa` FOREIGN KEY (`id_siswa`) REFERENCES `master_siswa` (`id_siswa`) ON DELETE CASCADE,
  CONSTRAINT `fk_cds_jadwal` FOREIGN KEY (`id_jadwal`) REFERENCES `cbt_jadwal` (`id_jadwal`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- 4.2 Tabel cbt_soal_siswa
DROP TABLE IF EXISTS `cbt_soal_siswa`;
CREATE TABLE `cbt_soal_siswa` (
  `id_soal_siswa` BIGINT AUTO_INCREMENT PRIMARY KEY,
  `id_bank` INT NOT NULL,
  `id_jadwal` INT NOT NULL,
  `id_soal` INT NOT NULL,
  `id_siswa` INT NOT NULL,
  `jenis_soal` TINYINT NOT NULL COMMENT '1=PG, 2=PGK, 3=Menjodohkan, 4=Isian, 5=Esai',
  `no_soal_alias` INT NOT NULL,
  `opsi_alias_a` VARCHAR(10) NULL,
  `opsi_alias_b` VARCHAR(10) NULL,
  `opsi_alias_c` VARCHAR(10) NULL,
  `opsi_alias_d` VARCHAR(10) NULL,
  `opsi_alias_e` VARCHAR(10) NULL,
  `jawaban_alias` LONGTEXT NULL,
  `jawaban_siswa` LONGTEXT NULL,
  `jawaban_benar` LONGTEXT NULL,
  `point_soal` DECIMAL(6,2) NOT NULL DEFAULT 0.00,
  `nilai_koreksi` DECIMAL(6,2) NOT NULL DEFAULT 0.00,
  `nilai_otomatis` TINYINT NOT NULL DEFAULT 0 COMMENT '0=Otomatis, 1=Manual',
  `ragu` TINYINT(1) NOT NULL DEFAULT 0,
  `updated_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  INDEX `idx_css_jadwal_siswa_alias` (`id_jadwal`, `id_siswa`, `no_soal_alias`),
  INDEX `idx_css_soal` (`id_soal`),
  CONSTRAINT `fk_csoal_bank` FOREIGN KEY (`id_bank`) REFERENCES `cbt_bank_soal` (`id_bank`) ON DELETE CASCADE,
  CONSTRAINT `fk_csoal_jadwal` FOREIGN KEY (`id_jadwal`) REFERENCES `cbt_jadwal` (`id_jadwal`) ON DELETE CASCADE,
  CONSTRAINT `fk_csoal_soal` FOREIGN KEY (`id_soal`) REFERENCES `cbt_soal` (`id_soal`) ON DELETE CASCADE,
  CONSTRAINT `fk_csoal_siswa` FOREIGN KEY (`id_siswa`) REFERENCES `master_siswa` (`id_siswa`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- 4.3 Tabel cbt_nilai
DROP TABLE IF EXISTS `cbt_nilai`;
CREATE TABLE `cbt_nilai` (
  `id_nilai` BIGINT AUTO_INCREMENT PRIMARY KEY,
  `id_tp` INT NOT NULL,
  `id_smt` INT NOT NULL,
  `id_jadwal` INT NOT NULL,
  `id_siswa` INT NOT NULL,
  `pg_benar` INT NOT NULL DEFAULT 0,
  `pg_nilai` DECIMAL(6,2) NOT NULL DEFAULT 0.00,
  `kompleks_nilai` DECIMAL(6,2) NOT NULL DEFAULT 0.00,
  `jodohkan_nilai` DECIMAL(6,2) NOT NULL DEFAULT 0.00,
  `isian_nilai` DECIMAL(6,2) NOT NULL DEFAULT 0.00,
  `essai_nilai` DECIMAL(6,2) NOT NULL DEFAULT 0.00,
  `total_nilai` DECIMAL(6,2) NOT NULL DEFAULT 0.00,
  `dikoreksi` TINYINT(1) NOT NULL DEFAULT 0,
  `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  `updated_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  UNIQUE INDEX `uq_nilai_jadwal_siswa` (`id_jadwal`, `id_siswa`),
  INDEX `idx_nilai_tpsmt` (`id_tp`, `id_smt`),
  CONSTRAINT `fk_cn_tp` FOREIGN KEY (`id_tp`) REFERENCES `master_tp` (`id_tp`) ON DELETE CASCADE,
  CONSTRAINT `fk_cn_smt` FOREIGN KEY (`id_smt`) REFERENCES `master_smt` (`id_smt`) ON DELETE CASCADE,
  CONSTRAINT `fk_cn_jadwal` FOREIGN KEY (`id_jadwal`) REFERENCES `cbt_jadwal` (`id_jadwal`) ON DELETE CASCADE,
  CONSTRAINT `fk_cn_siswa` FOREIGN KEY (`id_siswa`) REFERENCES `master_siswa` (`id_siswa`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ------------------------------------------------------------------------------
-- 5. KLASTER E-RAPOR & BUKU INDUK
-- ------------------------------------------------------------------------------

-- 5.1 Tabel rapor_admin_setting
DROP TABLE IF EXISTS `rapor_admin_setting`;
CREATE TABLE `rapor_admin_setting` (
  `id_setting` INT AUTO_INCREMENT PRIMARY KEY,
  `id_tp` INT NOT NULL,
  `id_smt` INT NOT NULL,
  `kkm_tunggal` TINYINT(1) NOT NULL DEFAULT 1,
  `kkm` INT NOT NULL DEFAULT 75,
  `bobot_ph` INT NOT NULL DEFAULT 50,
  `bobot_pts` INT NOT NULL DEFAULT 25,
  `bobot_pas` INT NOT NULL DEFAULT 25,
  `bobot_absen` INT NOT NULL DEFAULT 0,
  `tgl_rapor_akhir` DATE NULL,
  `tgl_rapor_pts` DATE NULL,
  `tgl_rapor_kelas_akhir` DATE NULL,
  `nip_kepsek` TINYINT(1) NOT NULL DEFAULT 1,
  `nip_walikelas` TINYINT(1) NOT NULL DEFAULT 1,
  UNIQUE INDEX `uq_ras_tpsmt` (`id_tp`, `id_smt`),
  CONSTRAINT `fk_ras_tp` FOREIGN KEY (`id_tp`) REFERENCES `master_tp` (`id_tp`) ON DELETE CASCADE,
  CONSTRAINT `fk_ras_smt` FOREIGN KEY (`id_smt`) REFERENCES `master_smt` (`id_smt`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- 5.2 Tabel rapor_kkm
DROP TABLE IF EXISTS `rapor_kkm`;
CREATE TABLE `rapor_kkm` (
  `id_kkm` INT AUTO_INCREMENT PRIMARY KEY,
  `id_tp` INT NOT NULL,
  `id_smt` INT NOT NULL,
  `jenis` TINYINT NOT NULL DEFAULT 1 COMMENT '1=Mapel, 2=Ekskul',
  `id_kelas` INT NOT NULL,
  `id_mapel` INT NOT NULL,
  `kkm` INT NOT NULL DEFAULT 75,
  `bobot_ph` INT NOT NULL DEFAULT 50,
  `bobot_pts` INT NOT NULL DEFAULT 25,
  `bobot_pas` INT NOT NULL DEFAULT 25,
  `beban_jam` INT NOT NULL DEFAULT 2,
  INDEX `idx_rkkm_lookup` (`id_tp`, `id_smt`, `id_kelas`, `id_mapel`),
  CONSTRAINT `fk_rkkm_tp` FOREIGN KEY (`id_tp`) REFERENCES `master_tp` (`id_tp`) ON DELETE CASCADE,
  CONSTRAINT `fk_rkkm_smt` FOREIGN KEY (`id_smt`) REFERENCES `master_smt` (`id_smt`) ON DELETE CASCADE,
  CONSTRAINT `fk_rkkm_kelas` FOREIGN KEY (`id_kelas`) REFERENCES `master_kelas` (`id_kelas`) ON DELETE CASCADE,
  CONSTRAINT `fk_rkkm_mapel` FOREIGN KEY (`id_mapel`) REFERENCES `master_mapel` (`id_mapel`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- 5.3 Tabel rapor_kikd
DROP TABLE IF EXISTS `rapor_kikd`;
CREATE TABLE `rapor_kikd` (
  `id_kikd` INT AUTO_INCREMENT PRIMARY KEY,
  `id_tp` INT NOT NULL,
  `id_smt` INT NOT NULL,
  `id_mapel_kelas` INT NOT NULL COMMENT 'ID Mapel',
  `aspek` TINYINT NOT NULL COMMENT '1=KI-3 Pengetahuan, 2=KI-4 Keterampilan',
  `nomor_kikd` VARCHAR(20) NOT NULL COMMENT '3.1, 4.1, dsb',
  `isi_kikd` TEXT NOT NULL,
  INDEX `idx_rkikd_lookup` (`id_tp`, `id_smt`, `id_mapel_kelas`, `aspek`),
  CONSTRAINT `fk_rkikd_tp` FOREIGN KEY (`id_tp`) REFERENCES `master_tp` (`id_tp`) ON DELETE CASCADE,
  CONSTRAINT `fk_rkikd_smt` FOREIGN KEY (`id_smt`) REFERENCES `master_smt` (`id_smt`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- 5.4 Tabel rapor_nilai_harian
DROP TABLE IF EXISTS `rapor_nilai_harian`;
CREATE TABLE `rapor_nilai_harian` (
  `id_nilai_harian` BIGINT AUTO_INCREMENT PRIMARY KEY,
  `id_tp` INT NOT NULL,
  `id_smt` INT NOT NULL,
  `id_kelas` INT NOT NULL,
  `id_mapel` INT NOT NULL,
  `id_siswa` INT NOT NULL,
  `p1` DECIMAL(5,2) NULL,
  `p2` DECIMAL(5,2) NULL,
  `p3` DECIMAL(5,2) NULL,
  `p4` DECIMAL(5,2) NULL,
  `p5` DECIMAL(5,2) NULL,
  `p6` DECIMAL(5,2) NULL,
  `p7` DECIMAL(5,2) NULL,
  `p8` DECIMAL(5,2) NULL,
  `p_rata_rata` DECIMAL(5,2) NULL,
  `p_predikat` VARCHAR(5) NULL,
  `p_deskripsi` TEXT NULL,
  `k1` DECIMAL(5,2) NULL,
  `k2` DECIMAL(5,2) NULL,
  `k3` DECIMAL(5,2) NULL,
  `k4` DECIMAL(5,2) NULL,
  `k5` DECIMAL(5,2) NULL,
  `k6` DECIMAL(5,2) NULL,
  `k7` DECIMAL(5,2) NULL,
  `k8` DECIMAL(5,2) NULL,
  `k_rata_rata` DECIMAL(5,2) NULL,
  `k_predikat` VARCHAR(5) NULL,
  `k_deskripsi` TEXT NULL,
  UNIQUE INDEX `uq_rnh_entry` (`id_tp`, `id_smt`, `id_kelas`, `id_mapel`, `id_siswa`),
  CONSTRAINT `fk_rnh_siswa` FOREIGN KEY (`id_siswa`) REFERENCES `master_siswa` (`id_siswa`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- 5.5 Tabel rapor_nilai_pts
DROP TABLE IF EXISTS `rapor_nilai_pts`;
CREATE TABLE `rapor_nilai_pts` (
  `id_nilai_pts` BIGINT AUTO_INCREMENT PRIMARY KEY,
  `id_tp` INT NOT NULL,
  `id_smt` INT NOT NULL,
  `id_kelas` INT NOT NULL,
  `id_mapel` INT NOT NULL,
  `id_siswa` INT NOT NULL,
  `nilai` DECIMAL(5,2) NULL,
  `predikat` VARCHAR(5) NULL,
  UNIQUE INDEX `uq_rnpts_entry` (`id_tp`, `id_smt`, `id_kelas`, `id_mapel`, `id_siswa`),
  CONSTRAINT `fk_rnpts_siswa` FOREIGN KEY (`id_siswa`) REFERENCES `master_siswa` (`id_siswa`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- 5.6 Tabel rapor_nilai_akhir
DROP TABLE IF EXISTS `rapor_nilai_akhir`;
CREATE TABLE `rapor_nilai_akhir` (
  `id_nilai_akhir` BIGINT AUTO_INCREMENT PRIMARY KEY,
  `id_tp` INT NOT NULL,
  `id_smt` INT NOT NULL,
  `id_kelas` INT NOT NULL,
  `id_mapel` INT NOT NULL,
  `id_siswa` INT NOT NULL,
  `nilai` DECIMAL(5,2) NULL COMMENT 'Nilai PAS/PAT',
  `akhir` DECIMAL(5,2) NULL COMMENT 'Nilai Akhir Rapor Final',
  `predikat` VARCHAR(5) NULL,
  UNIQUE INDEX `uq_rna_entry` (`id_tp`, `id_smt`, `id_kelas`, `id_mapel`, `id_siswa`),
  CONSTRAINT `fk_rna_siswa` FOREIGN KEY (`id_siswa`) REFERENCES `master_siswa` (`id_siswa`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- 5.7 Tabel rapor_nilai_sikap
DROP TABLE IF EXISTS `rapor_nilai_sikap`;
CREATE TABLE `rapor_nilai_sikap` (
  `id_nilai_sikap` BIGINT AUTO_INCREMENT PRIMARY KEY,
  `id_tp` INT NOT NULL,
  `id_smt` INT NOT NULL,
  `id_kelas` INT NOT NULL,
  `id_siswa` INT NOT NULL,
  `jenis` TINYINT NOT NULL COMMENT '1=Spiritual, 2=Sosial',
  `nilai` JSON NULL COMMENT 'Butir nilai sikap',
  `deskripsi` TEXT NULL,
  UNIQUE INDEX `uq_rns_entry` (`id_tp`, `id_smt`, `id_kelas`, `id_siswa`, `jenis`),
  CONSTRAINT `fk_rns_siswa` FOREIGN KEY (`id_siswa`) REFERENCES `master_siswa` (`id_siswa`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- 5.8 Tabel rapor_nilai_ekstra
DROP TABLE IF EXISTS `rapor_nilai_ekstra`;
CREATE TABLE `rapor_nilai_ekstra` (
  `id_nilai_ekstra` BIGINT AUTO_INCREMENT PRIMARY KEY,
  `id_tp` INT NOT NULL,
  `id_smt` INT NOT NULL,
  `id_ekstra` INT NOT NULL,
  `id_kelas` INT NOT NULL,
  `id_siswa` INT NOT NULL,
  `nilai` VARCHAR(5) NULL COMMENT 'A, B, C, D',
  `predikat` VARCHAR(50) NULL,
  `deskripsi` TEXT NULL,
  UNIQUE INDEX `uq_rne_entry` (`id_tp`, `id_smt`, `id_ekstra`, `id_siswa`),
  CONSTRAINT `fk_rne_ekstra` FOREIGN KEY (`id_ekstra`) REFERENCES `master_ekstra` (`id_ekstra`) ON DELETE CASCADE,
  CONSTRAINT `fk_rne_siswa` FOREIGN KEY (`id_siswa`) REFERENCES `master_siswa` (`id_siswa`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- 5.9 Tabel rapor_catatan_wali
DROP TABLE IF EXISTS `rapor_catatan_wali`;
CREATE TABLE `rapor_catatan_wali` (
  `id_catatan` BIGINT AUTO_INCREMENT PRIMARY KEY,
  `id_tp` INT NOT NULL,
  `id_smt` INT NOT NULL,
  `id_kelas` INT NOT NULL,
  `id_siswa` INT NOT NULL,
  `nilai` JSON NULL COMMENT '{"sakit":0, "izin":0, "alfa":0}',
  `deskripsi` TEXT NULL COMMENT 'Catatan/Saran Wali Kelas',
  UNIQUE INDEX `uq_rcw_entry` (`id_tp`, `id_smt`, `id_kelas`, `id_siswa`),
  CONSTRAINT `fk_rcw_siswa` FOREIGN KEY (`id_siswa`) REFERENCES `master_siswa` (`id_siswa`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- 5.10 Tabel rapor_fisik
DROP TABLE IF EXISTS `rapor_fisik`;
CREATE TABLE `rapor_fisik` (
  `id_fisik` BIGINT AUTO_INCREMENT PRIMARY KEY,
  `id_tp` INT NOT NULL,
  `id_smt` INT NOT NULL,
  `id_kelas` INT NOT NULL,
  `id_siswa` INT NOT NULL,
  `tinggi` INT NULL COMMENT 'Tinggi Badan (cm)',
  `berat` INT NULL COMMENT 'Berat Badan (kg)',
  `pendengaran` VARCHAR(50) NULL DEFAULT 'Baik',
  `penglihatan` VARCHAR(50) NULL DEFAULT 'Baik',
  `gigi` VARCHAR(50) NULL DEFAULT 'Baik',
  `lainnya` VARCHAR(100) NULL,
  UNIQUE INDEX `uq_rf_entry` (`id_tp`, `id_smt`, `id_kelas`, `id_siswa`),
  CONSTRAINT `fk_rf_siswa` FOREIGN KEY (`id_siswa`) REFERENCES `master_siswa` (`id_siswa`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- 5.11 Tabel rapor_prestasi
DROP TABLE IF EXISTS `rapor_prestasi`;
CREATE TABLE `rapor_prestasi` (
  `id_prestasi` BIGINT AUTO_INCREMENT PRIMARY KEY,
  `id_tp` INT NOT NULL,
  `id_smt` INT NOT NULL,
  `id_kelas` INT NOT NULL,
  `id_siswa` INT NOT NULL,
  `ranking` INT NULL,
  `deskripsi_ranking` TEXT NULL,
  `p1` VARCHAR(150) NULL,
  `p1_desk` TEXT NULL,
  `p2` VARCHAR(150) NULL,
  `p2_desk` TEXT NULL,
  `p3` VARCHAR(150) NULL,
  `p3_desk` TEXT NULL,
  UNIQUE INDEX `uq_rp_entry` (`id_tp`, `id_smt`, `id_kelas`, `id_siswa`),
  CONSTRAINT `fk_rp_siswa` FOREIGN KEY (`id_siswa`) REFERENCES `master_siswa` (`id_siswa`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- 5.12 Tabel rapor_naik
DROP TABLE IF EXISTS `rapor_naik`;
CREATE TABLE `rapor_naik` (
  `id_naik` BIGINT AUTO_INCREMENT PRIMARY KEY,
  `id_tp` INT NOT NULL,
  `id_smt` INT NOT NULL,
  `id_siswa` INT NOT NULL,
  `naik` TINYINT(1) NOT NULL DEFAULT 1 COMMENT '1=Naik/Lulus, 0=Tinggal Kelas/Tidak Lulus',
  `keterangan` TEXT NULL,
  UNIQUE INDEX `uq_rnk_entry` (`id_tp`, `id_smt`, `id_siswa`),
  CONSTRAINT `fk_rnk_siswa` FOREIGN KEY (`id_siswa`) REFERENCES `master_siswa` (`id_siswa`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- 5.13 Tabel buku_induk
DROP TABLE IF EXISTS `buku_induk`;
CREATE TABLE `buku_induk` (
  `id_buku` BIGINT AUTO_INCREMENT PRIMARY KEY,
  `id_siswa` INT NOT NULL UNIQUE,
  `uid` VARCHAR(100) NULL,
  `rombel_awal` VARCHAR(50) NULL,
  `nama_panggilan` VARCHAR(100) NULL,
  `bahasa` VARCHAR(100) NULL DEFAULT 'Indonesia',
  `jml_saudara_kandung` INT NULL DEFAULT 0,
  `jml_saudara_tiri` INT NULL DEFAULT 0,
  `jml_saudara_angkat` INT NULL DEFAULT 0,
  `status_yatim` VARCHAR(50) NULL DEFAULT 'Lengkap',
  `gol_darah` VARCHAR(5) NULL,
  `penyakit` TEXT NULL,
  `kelainan_fisik` TEXT NULL,
  `tinggi` INT NULL,
  `berat` INT NULL,
  `tamat_dari` VARCHAR(150) NULL,
  `no_ijazah_sebelumnya` VARCHAR(100) NULL,
  `tgl_ijazah_sebelumnya` DATE NULL,
  `tahun_lulus` VARCHAR(10) NULL,
  `no_ijazah` VARCHAR(100) NULL,
  `arsip_rapor_json` LONGTEXT NULL,
  `status` TINYINT NOT NULL DEFAULT 1 COMMENT '1=Aktif, 2=Lulus, 3=Pindah',
  `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  `updated_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  CONSTRAINT `fk_bi_siswa` FOREIGN KEY (`id_siswa`) REFERENCES `master_siswa` (`id_siswa`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

SET FOREIGN_KEY_CHECKS = 1;

