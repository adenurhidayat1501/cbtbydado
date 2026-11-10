# WORKSPACE RULES — GARUDACBT REBUILD

## 1. Konteks Proyek
Proyek ini adalah pembangunan ulang (rebuild) sistem **GarudaCBT v1.5.3 (FIXED)** (Gabungan Aplikasi Rapor, Ujian CBT Standar AKM, dan E-Learning) berdasarkan spesifikasi lengkap di dalam file `PRD.md` dan daftar antrean tugas di `TODO.md`.

## 2. Standar Penulisan Kode (Strict Coding Standards)
- **Full Code Generation:** Setiap kali membuat atau memperbarui file, tulis kode secara utuh dari baris pertama hingga baris terakhir. Dilarang menulis komentar pemotong seperti `// ... kode lainnya tetap sama`, `// TODO: implementasi nanti`, atau placeholder kosong.
- **Database & Konkurensi Tinggi (Target 1.000 User Serentak):**
  - Gunakan engine `InnoDB` dan karakter `utf8mb4_unicode_ci`.
  - Pastikan query pada `cbt_soal_siswa`, `cbt_durasi_siswa`, dan `cbt_nilai` menggunakan *prepared statements* dan memanfaatkan *composite index* `(id_jadwal, id_siswa)`.
  - Saat siswa menyimpan jawaban ujian via AJAX, lakukan *single-row atomic update* berdasarkan Primary Key `id_soal_siswa` tanpa men-trigger query berat.
- **Defensive String & HTML Handling:**
  - Gunakan escaping yang aman agar karakter tanda petik tunggal (`'`) dan ganda (`"`) pada nama siswa, nama guru, maupun kop kartu/berita acara tidak merusak query SQL atau tampilan cetak.
  - Pertahankan tag HTML gambar (`<img>`), rumus LaTeX (KaTeX), dan teks Arab (`font-family: 'Arabic Calibri'`) pada render soal dan opsi jawaban.
- **Multi-Jenjang Dinamis:**
  - Periksa konfigurasi `jenjang` pada tabel `setting_sekolah` (`1` = SD/MI, `2` = SMP/MTs, `3` = SMA/SMK/MA).
  - Sembunyikan fitur Jurusan jika jenjang adalah SD/MI atau SMP/MTs.
  - Sesuaikan jumlah opsi Pilihan Ganda default (3, 4, atau 5 opsi) mengikuti pengaturan bank soal dan jenjang.

## 3. Alur Kerja Agent (Workflow Protocol)
1. Baca `PRD.md` dan `TODO.md` sebelum memulai tugas.
2. Kerjakan tugas secara berurutan per sub-modul sesuai perintah pengguna.
3. Setelah selesai membuat dan menguji file pada satu sub-modul, perbarui status checkbox `[ ]` menjadi `[x]` di dalam file `TODO.md`.
4. Jangan melompat ke Milestone berikutnya sebelum seluruh item pada Milestone aktif selesai dan tervalidasi.