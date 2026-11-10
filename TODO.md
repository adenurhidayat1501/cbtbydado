# DADO CBT — EXECUTION TRACKER

## Milestone 1: Core Architecture, Database Schema, & Auth
- [x] **1.1 Struktur Folder & Konfigurasi Dasar:** Inisialisasi struktur direktori MVC, `.env`, konfigurasi koneksi database, session, dan helper fungsi (format tanggal Indonesia, string sanitizer, Fisher-Yates shuffle).
- [x] **1.2 Database Migration (42 Tabel):** Buat skema SQL utuh (`database/schema.sql`) mencakup klaster Master, E-Learning, CBT, Pelaksanaan Ujian, dan E-Rapor beserta Composite Index.
- [x] **1.3 Database Seeder:** Buat data awal (`database/seed.sql`) untuk user Admin default, Tahun Pelajaran, Semester (Ganjil/Genap), Jenis Ujian (PH, PTS, PAS, PAT, USBK, TO, ANBK), dan Setting Sekolah.
- [x] **1.4 Sistem Autentikasi & Role Guard:**
  - [x] Login Reguler (Admin & Guru) + Login Khusus CBT Siswa.
  - [x] Middleware/Guard untuk role `admin`, `guru` (Mapel, Wali Kelas, Pengawas), dan `siswa`.
  - [x] Fitur Single-Device Session Lock untuk siswa ujian & Auto-Logout Admin saat ganti password.
- [x] **1.5 Layout UI Utama:** Template Master Dashboard (Sidebar dinamis sesuai role, Topbar saklar TP/Semester aktif, integrasi KaTeX, Summernote, DataTables, dan Font Arabic Calibri).

## Milestone 2: Modul Data Master & Pengaturan
- [x] **2.1 Beranda (Dashboard):** Statistik jumlah siswa, guru, rombel, bank soal, jadwal aktif hari ini, kotak Token Ujian, dan Running Text pengumuman.
- [x] **2.2 Data Umum — TP, Semester, Jurusan, Mapel, Ekskul:** CRUD lengkap dengan saklar aktivasi TP/Semester dan pengelompokan Mapel (Kelompok A, B, C, Mulok, PAI) sesuai jenjang.
- [x] **2.3 Data Umum — Siswa:** CRUD Siswa, Import Excel massal, dan halaman **Update Foto Siswa Per Kelas**.
- [x] **2.4 Data Umum — Kelas / Rombel:** CRUD Rombel, fitur **Search NIS & Nama Siswa** di edit kelas, hapus siswa dari rombel, dan fitur Naik Kelas / Salin Rombel lintas TP.
- [x] **2.5 Data Umum — Guru:** CRUD Guru, **Import Guru dengan Foto**, penetapan Jabatan (Kepsek, Wali Kelas, Guru Mapel per kelas), dan visualisasi **Struktur Organisasi**.
- [x] **2.6 Pengaturan Sistem:** Profile Sekolah (upload logo & tanda tangan Kepsek), User Management (aktivasi/reset akun Admin, Guru, Siswa), dan Pengumuman.

## Milestone 3: Modul E-Learning & Presensi KBM
- [x] **3.1 Jadwal Pelajaran KBM:** Pengaturan jam mulai KBM, durasi per jam pelajaran, jam istirahat, dan matriks jadwal mapel mingguan per kelas.
- [x] **3.2 Materi & Tugas Belajar:** CRUD Materi (`jenis=1`) & Tugas (`jenis=2`) dengan lampiran file/video serta proteksi isolasi akses per guru mapel.
- [x] **3.3 Jadwal Materi / Tugas:** Penjadwalan tanggal tayang materi dan tugas ke masing-masing kelas.
- [x] **3.4 Ruang Belajar Siswa:** Antarmuka siswa membaca materi, mengunggah tugas, komentar, dan pencatatan presensi otomatis ke `log_materi` saat dibuka pada jam KBM.
- [x] **3.5 Hasil E-Learning (Guru/Admin):** Koreksi & input Nilai Harian, rekap **Kehadiran Harian**, **Kehadiran Bulanan**, dan export **Rekap Nilai E-Learning** ke Excel.

## Milestone 4: Modul Persiapan CBT & Bank Soal 5 Tipe AKM
- [x] **4.1 Master CBT:** CRUD Jenis Ujian, Ruang, Sesi, matriks **Atur Ruang dan Sesi Siswa**, serta generator otomatis **Nomor Peserta Ujian**.
- [x] **4.2 Bank Soal & Editor 5 Tipe AKM:**
  - [x] CRUD Bank Soal (pengaturan bobot & kuota tampil PG, PG Kompleks, Menjodohkan, Isian Singkat, Essai).
  - [x] Editor Butir Soal Tipe 1 (PG Tunggal), Tipe 2 (PG Kompleks), Tipe 3 (Menjodohkan Teks/Gambar & Gambar Tanpa Teks dengan Line Connector), Tipe 4 (Isian Singkat), dan Tipe 5 (Essai).
  - [x] Fitur **Import Soal dari Word (.docx)** dan **Import Excel** beserta ekstraksi gambar.
- [x] **4.3 Jadwal Ujian, Pengawas, & Token:**
  - [x] CRUD Jadwal Ujian (durasi, acak soal/opsi, tampil hasil, proteksi edit/hapus saat ujian berlangsung).
  - [x] Pengaturan **Alokasi Waktu** (jam ke-1, ke-2, dst.) dan pemetaan **Pengawas per Ruang & Sesi** (cegah bug `undefined` dan pembatasan hak akses khusus guru pengawas).
  - [x] Generator **Token Ujian** (manual dan auto-refresh interval menit).

## Milestone 5: Engine Pelaksanaan Ujian, Monitoring, & Rekap Nilai
- [x] **5.1 Antarmuka Ujian Siswa (Exam Engine):**
  - [x] Validasi Token Ujian & inisialisasi soal (`QuestionRandomizerService` dengan bulk insert & pengacakan alias opsi).
  - [x] Lembar ujian responsif: Timer sinkron waktu server, tombol **Zoom In / Zoom Out Soal**, kanvas interaktif tarik garis soal menjodohkan, **Editor Header Jawaban Essai**, indikator ragu-ragu, dan penyimpanan AJAX satu baris.
  - [x] Validasi status terjawab soal menjodohkan (wajib memiliki minimal 1 link garis aktif).
  - [x] Sistem Anti-Cheat: **Deteksi New Tab / Pindah Jendela (`visibilitychange` & `blur`)** dengan pencatatan pelanggaran dan penguncian sesi.
- [x] **5.2 Cetak Administrasi Ujian:** Cetak Kartu Peserta Ujian, Daftar Kehadiran, dan Berita Acara Pelaksanaan Ujian (aman terhadap karakter tanda petik di header/nama).
- [x] **5.3 Live Monitoring (Status Siswa):** Dashboard real-time Pengawas & Admin dengan filter/search Kelas, Ruang, Sesi, serta aksi **Reset Login**, **Reset Waktu Ujian**, **Paksa Selesai**, dan **Ulang Ujian** (membersihkan nilai lama).
- [x] **5.4 Koreksi, Analisis Soal, & Rekap Nilai:**
  - [x] Engine Penilaian Otomatis (`AutoGraderService`) untuk PG, PG Kompleks, Menjodohkan, dan Isian Singkat (`case-insensitive & trim`).
  - [x] Menu Koreksi Ujian per siswa (mendukung render gambar soal dan input poin esai).
  - [x] Menu **Analisis Butir Soal**, fitur **Katrol / Konversi Nilai**, Search/Paging Rekap Nilai, Download Arsip Soal, dan **Export Nilai Excel Dinamis** (header tabel tetap rapi meskipun tanpa soal PG).

## Milestone 6: Modul E-Rapor, DKN, Buku Induk, & Utilitas DB
- [x] **6.1 Setting Rapor & KKM:** Konfigurasi KKM tunggal/multi, bobot PH/PTS/PAS, rentang predikat, KIKD, serta tanggal dan tanda tangan rapor.
- [x] **6.2 Kumpulan Nilai Rapor:** Sinkronisasi nilai dari E-Learning & CBT, import Excel, input Nilai Pengetahuan (KI-3), Keterampilan (KI-4), Sikap Spiritual & Sosial, Ekstrakurikuler, Absensi, Catatan Wali, Kesehatan/Fisik, Prestasi, dan Kenaikan Kelas.
- [x] **6.3 Cetak Rapor, DKN, & Buku Induk:**
  - [x] Generator cetak **Rapor PTS**, **Rapor Akhir Semester (PAS/PAT)**, dan **DKN** (sinkronisasi predikat, kehadiran, prestasi, kesehatan, dan urutan mapel).
  - [x] Modul **Buku Induk & Alumni** (pengarsipan otomatis riwayat nilai lintas semester dan kelulusan).
- [x] **6.4 Utilitas Database & Web Installer:** Fitur Backup/Restore `.sql`, menu **Database Update (Migrasi Skema)**, dan folder `installer/` untuk instalasi awal.


## Milestone 7: Deployment & Otomasi Produksi (Ubuntu 24.04 LTS & GitHub CI/CD)
- [x] **7.1 Auto Installer VPS Ubuntu 24.04 LTS (`autoinstall.sh` & `deploy/install-vps.sh`):** Instalasi 1-baris Nginx, PHP 8.3-FPM, MariaDB, UFW Firewall, SSL Certbot, fitur Clean Reset/Hapus project lama masa test, dan tuning konkurensi 1.000 user ujian serentak.
- [x] **7.2 Auto Update GitHub Engine (`deploy/update.sh` & `scripts/migrate.php`):** Script penarik kode GitHub dengan auto-backup database pra-update, verifikasi skema tabel, perbaikan hak akses `www-data`, dan restart worker PHP/Nginx.
- [x] **7.3 GitHub Webhook Listener (`public/webhook.php`):** Penerima push webhook terlindungi HMAC SHA-256 untuk pembaruan instan otomatis tanpa SSH.
- [x] **7.4 CI/CD Pipeline & Windows Push Utility (`.github/workflows/deploy.yml` & `autopushgithub.bat`):** Otomasi testing CI/CD dan script 1-klik upload GitHub dari Windows.
- [x] **7.5 Antarmuka Admin & Utilitas CLI:** Panel GitHub Auto-Update pada Dashboard Admin dan perintah cepat VPS (`dadocbt-update`, `dadocbt-backup`, `dadocbt-status`).
