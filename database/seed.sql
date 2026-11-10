-- ==============================================================================
-- GARUDACBT REBUILD v1.5.3 (FIXED) — DATABASE SEEDER
-- Default Initial Data (Admin, Master TP, Semester, Jenis Ujian, Setting Sekolah)
-- ==============================================================================

SET FOREIGN_KEY_CHECKS = 0;

-- 1. Default Users (Admin, Demo Guru, Demo Siswa)
-- admin: admin123 ($2y$10$ciN9.t.PYAXXIzFuriX.2ewQyqLzKakk4rz6VkYtc3uf/3gAWer/q)
-- guru: guru123 ($2y$10$wLSgFFuRHQu9j24fTUU.DeO4I.4K4WojCtC04JrInBB3WIJnBV/Uu)
-- siswa: siswa123 ($2y$10$PIzcltxLTt8GOwydN.pyI.vK4jllCTuKZ8egJ.LJXL9A4/6ocpxO6)
DELETE FROM `users`;
INSERT INTO `users` (`id`, `username`, `password`, `email`, `role`, `session_token`, `active`) VALUES
(1, 'admin', '$2y$10$ciN9.t.PYAXXIzFuriX.2ewQyqLzKakk4rz6VkYtc3uf/3gAWer/q', 'admin@garudacbt.sch.id', 'admin', NULL, 1),
(2, 'guru', '$2y$10$wLSgFFuRHQu9j24fTUU.DeO4I.4K4WojCtC04JrInBB3WIJnBV/Uu', 'guru@garudacbt.sch.id', 'guru', NULL, 1),
(3, 'siswa', '$2y$10$PIzcltxLTt8GOwydN.pyI.vK4jllCTuKZ8egJ.LJXL9A4/6ocpxO6', 'siswa@garudacbt.sch.id', 'siswa', NULL, 1);

-- 2. Setting Profil Sekolah
DELETE FROM `setting_sekolah`;
INSERT INTO `setting_sekolah` (
  `id_setting`, `nama_sekolah`, `npsn`, `jenjang`, `satuan_pendidikan`, `alamat`, 
  `desa`, `kecamatan`, `kota`, `provinsi`, `kode_pos`, `telp`, `fax`, 
  `email`, `web`, `kepsek`, `nip_kepsek`, `tanda_tangan`, `logo_kiri`, `logo_kanan`
) VALUES (
  1, 
  'SMP GARUDA NUSANTARA', 
  '20234567', 
  2, -- 1=SD/MI, 2=SMP/MTs, 3=SMA/SMK/MA
  'SMP', 
  'Jl. Merdeka Nusantara No. 45', 
  'Cibadak', 
  'Sukmajaya', 
  'Kota Bandung', 
  'Jawa Barat', 
  '40115', 
  '(022) 7654321', 
  '(022) 7654322', 
  'info@smpgarudanusantara.sch.id', 
  'https://smpgarudanusantara.sch.id', 
  'Dr. H. Ahmad Fauzi, M.Pd.', 
  '197505122000031001', 
  NULL, 
  NULL, 
  NULL
);

-- 3. Master Tahun Pelajaran (TP)
DELETE FROM `master_tp`;
INSERT INTO `master_tp` (`id_tp`, `tahun`, `active`) VALUES
(1, '2024/2025', 0),
(2, '2025/2026', 1);

-- 4. Master Semester (SMT)
DELETE FROM `master_smt`;
INSERT INTO `master_smt` (`id_smt`, `smt`, `nama_smt`, `active`) VALUES
(1, '1', 'Ganjil', 1),
(2, '2', 'Genap', 0);

-- 5. Master Jenis Ujian CBT
DELETE FROM `cbt_jenis`;
INSERT INTO `cbt_jenis` (`id_jenis`, `nama_jenis`, `kode_jenis`) VALUES
(1, 'Penilaian Harian', 'PH'),
(2, 'Penilaian Tengah Semester', 'PTS'),
(3, 'Penilaian Akhir Semester', 'PAS'),
(4, 'Penilaian Akhir Tahun', 'PAT'),
(5, 'Ujian Sekolah Berbasis Komputer', 'USBK'),
(6, 'Try Out Ujian', 'TO'),
(7, 'Asesmen Nasional Berbasis Komputer', 'ANBK');

-- 6. Master Mata Pelajaran Default (Kurikulum Nasional & PAI)
DELETE FROM `master_mapel`;
INSERT INTO `master_mapel` (`id_mapel`, `nama_mapel`, `kode`, `kelompok`, `bobot`, `jenjang`, `status`, `urutan_tampil`) VALUES
(1, 'Pendidikan Agama Islam & Budi Pekerti', 'PAI', 'A', 100, 2, 1, 1),
(2, 'Pendidikan Pancasila dan Kewarganegaraan', 'PPKn', 'A', 100, 2, 1, 2),
(3, 'Bahasa Indonesia', 'BIN', 'A', 100, 2, 1, 3),
(4, 'Matematika', 'MAT', 'A', 100, 2, 1, 4),
(5, 'Ilmu Pengetahuan Alam', 'IPA', 'A', 100, 2, 1, 5),
(6, 'Ilmu Pengetahuan Sosial', 'IPS', 'A', 100, 2, 1, 6),
(7, 'Bahasa Inggris', 'BIG', 'A', 100, 2, 1, 7),
(8, 'Seni Budaya', 'SBK', 'B', 100, 2, 1, 8),
(9, 'Pendidikan Jasmani, Olahraga, dan Kesehatan', 'PJOK', 'B', 100, 2, 1, 9),
(10, 'Informatika', 'INF', 'B', 100, 2, 1, 10),
(11, 'Bahasa Sunda / Daerah', 'BSND', 'MULOK', 100, 2, 1, 11),
(12, 'Al-Qur\'an Hadis', 'QH', 'PAI', 100, 2, 1, 12),
(13, 'Akidah Akhlak', 'AA', 'PAI', 100, 2, 1, 13),
(14, 'Fikih', 'FIK', 'PAI', 100, 2, 1, 14),
(15, 'Sejarah Kebudayaan Islam', 'SKI', 'PAI', 100, 2, 1, 15),
(16, 'Bahasa Arab', 'BAR', 'PAI', 100, 2, 1, 16);

-- 7. Master Ekstrakurikuler
DELETE FROM `master_ekstra`;
INSERT INTO `master_ekstra` (`id_ekstra`, `nama_ekstra`, `kode_ekstra`) VALUES
(1, 'Praja Muda Karana (Pramuka)', 'PRAM'),
(2, 'Palang Merah Remaja (PMR)', 'PMR'),
(3, 'Pasukan Pengibar Bendera (Paskibra)', 'PASKIB'),
(4, 'Rohani Islam (Rohis)', 'ROHIS'),
(5, 'Klub Olahraga Bola Voli / Basket / Futsal', 'OR'),
(6, 'Klub Sains & Robotika', 'KSR');

-- 8. Master Ruang Ujian CBT
DELETE FROM `cbt_ruang`;
INSERT INTO `cbt_ruang` (`id_ruang`, `nama_ruang`, `kode_ruang`) VALUES
(1, 'Laboratorium Komputer 01', 'LAB-01'),
(2, 'Laboratorium Komputer 02', 'LAB-02'),
(3, 'Ruang Multimedia', 'MM-01');

-- 9. Master Sesi Ujian CBT
DELETE FROM `cbt_sesi`;
INSERT INTO `cbt_sesi` (`id_sesi`, `nama_sesi`, `kode_sesi`, `waktu_mulai`, `waktu_akhir`) VALUES
(1, 'Sesi Pagi Ke-1', 'SESI-1', '07:30:00', '09:30:00'),
(2, 'Sesi Siang Ke-2', 'SESI-2', '10:00:00', '12:00:00'),
(3, 'Sesi Sore Ke-3', 'SESI-3', '13:00:00', '15:00:00');

-- 10. CBT Token Aktif
DELETE FROM `cbt_token`;
INSERT INTO `cbt_token` (`id_token`, `token`, `auto`, `jarak`, `updated_at`) VALUES
(1, 'GARUDA', 0, 15, NOW());

-- 11. Kop Berita Acara & Kartu Absensi Ujian
DELETE FROM `cbt_kop_absensi`;
INSERT INTO `cbt_kop_absensi` (
  `id_kop`, `header_1`, `header_2`, `header_3`, `header_4`, 
  `proktor`, `pengawas_1`, `pengawas_2`, `tanggal`, `kota`
) VALUES (
  1, 
  'PEMERINTAH DAERAH PROVINSI JAWA BARAT', 
  'DINAS PENDIDIKAN', 
  'SMP GARUDA NUSANTARA KOTA BANDUNG', 
  'ASESMEN SUMATIF BERBASIS KOMPUTER (CBT AKM)', 
  'Proktor Utama CBT', 
  'Pengawas Ruang 1', 
  'Pengawas Ruang 2', 
  '10 November 2026', 
  'Kota Bandung'
);

-- 12. Running Text Pengumuman
DELETE FROM `running_text`;
INSERT INTO `running_text` (`id_text`, `text`, `tujuan`, `active`) VALUES
(1, 'Selamat datang di GarudaCBT v1.5.3 (FIXED) Rebuild — Sistem Ujian AKM, E-Learning, dan Rapor Terpadu. Selalu jaga kejujuran dalam menuntut ilmu!', 'semua', 1);

-- 13. Data Demo Guru & Siswa
DELETE FROM `master_guru`;
INSERT INTO `master_guru` (
  `id_guru`, `id_user`, `nip`, `nama_guru`, `kode_guru`, `jk`, `no_hp`, `alamat`, `foto`, `id_jabatan`, `status`
) VALUES (
  1, 2, '198001012005011002', 'Budi Santoso, S.Pd., M.Kom.', 'G01', 'L', '081234567890', 'Jl. Sukajadi No. 12 Bandung', NULL, 3, 1
);

DELETE FROM `master_siswa`;
INSERT INTO `master_siswa` (
  `id_siswa`, `id_user`, `nisn`, `nis`, `nama`, `jk`, `tempat_lahir`, `tgl_lahir`, 
  `agama`, `alamat`, `nama_ayah`, `nama_ibu`, `no_hp`, `tahun_masuk`, `kelas_awal`, `foto`, `status`
) VALUES (
  1, 3, '0081234567', '2025001', 'Muhammad Rizky Pratama', 'L', 'Bandung', '2010-05-15', 
  'Islam', 'Jl. Cikutra Barat No. 8 Bandung', 'Pratama Senior', 'Siti Rahmawati', '089876543210', '2025', '7A', NULL, 1
);

-- 14. Demo Kelas (Tingkat 7)
DELETE FROM `master_kelas`;
INSERT INTO `master_kelas` (`id_kelas`, `id_tp`, `id_smt`, `nama_kelas`, `kode_kelas`, `jurusan_id`, `level_id`, `guru_id`, `siswa_id`) VALUES
(1, 2, 1, 'Kelas 7A', '7A', NULL, 7, 1, '[1]');

-- 15. Demo Hubungan Kelas Siswa
DELETE FROM `kelas_siswa`;
INSERT INTO `kelas_siswa` (`id_kelas_siswa`, `id_tp`, `id_smt`, `id_kelas`, `id_siswa`) VALUES
(1, 2, 1, 1, 1);

-- 16. Demo Sesi Siswa & Nomor Peserta Ujian
DELETE FROM `cbt_sesi_siswa`;
INSERT INTO `cbt_sesi_siswa` (`id_sesi_siswa`, `siswa_id`, `kelas_id`, `ruang_id`, `sesi_id`, `tp_id`, `smt_id`) VALUES
(1, 1, 1, 1, 1, 2, 1);

DELETE FROM `cbt_nomor_peserta`;
INSERT INTO `cbt_nomor_peserta` (`id_nomor`, `id_siswa`, `id_tp`, `nomor_peserta`) VALUES
(1, 1, 2, '26-02-001-0001');

-- 17. Pengaturan Rapor Default
DELETE FROM `rapor_admin_setting`;
INSERT INTO `rapor_admin_setting` (
  `id_setting`, `id_tp`, `id_smt`, `kkm_tunggal`, `kkm`, `bobot_ph`, `bobot_pts`, `bobot_pas`, 
  `bobot_absen`, `tgl_rapor_akhir`, `tgl_rapor_pts`, `tgl_rapor_kelas_akhir`, `nip_kepsek`, `nip_walikelas`
) VALUES (
  1, 2, 1, 1, 75, 50, 25, 25, 0, '2026-12-20', '2026-10-15', '2027-06-25', 1, 1
);

SET FOREIGN_KEY_CHECKS = 1;

