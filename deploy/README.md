# PANDUAN DEPLOYMENT & AUTO-UPDATE DADO CBT v1.5.3 (UBUNTU 24.04 LTS)

Dokumen ini menjelaskan tata cara instalasi otomatis pada VPS Ubuntu 24.04 LTS (Noble Numbat) serta konfigurasi **Auto-Update GitHub** (via Webhook, GitHub Actions, dan CLI).

---

## 🚀 BAGIAN 1: AUTO INSTALLER VPS UBUNTU 24.04 LTS

Installer otomatis telah dikonfigurasi khusus untuk menangani beban **1.000 peserta ujian serentak** dengan mengoptimalkan:
- **Nginx 1.24+**: FastCGI buffering, static asset caching (KaTeX & Arabic Calibri), Gzip compression.
- **PHP 8.3-FPM**: Pool dinamis (`pm.max_children = 120`, `max_requests = 1000`, `memory_limit = 512M`).
- **MariaDB 10.11+**: `max_connections = 1200`, `innodb_flush_log_at_trx_commit = 2`, buffer pool dinamis sesuai RAM server.
- **Mode Uji Coba (Clean Reset)**: Otomatis menghapus seluruh direktori web lama dan basis data pengujian sebelumnya agar server berada dalam kondisi 100% bersih (*fresh slate*).
- **Firewall UFW**: Port 22 (SSH), 80 (HTTP), dan 443 (HTTPS).
- **SSL Gratis**: Integrasi Let's Encrypt Certbot otomatis.

### Cara 1: Eksekusi 1-Baris Langsung di VPS Baru
Login ke VPS via SSH (`ssh root@ip-vps-anda`), kemudian jalankan perintah berikut:

```bash
curl -fsSL https://raw.githubusercontent.com/<username-anda>/<repo-anda>/main/autoinstall.sh | sudo bash
```

### Cara 2: Jalankan Langsung dari Direktori Proyek
```bash
sudo bash autoinstall.sh
```

---

## 🔄 BAGIAN 2: FITUR AUTO-UPDATE GITHUB

Tersedia 4 metode untuk memperbarui aplikasi saat ada pembaruan kode di GitHub:

### Opsi A: GitHub Webhook (Otomatis Setiap Kali `git push`)
Setiap kali Anda melakukan push ke branch `main`, GitHub akan memicu pembaruan di VPS tanpa perlu login ke SSH.

1. Buka repository GitHub Anda di browser.
2. Masuk ke **Settings** > **Webhooks** > **Add webhook**.
3. Isi kolom berikut:
   - **Payload URL**: `https://cbt.sekolah-anda.sch.id/webhook.php` (atau `http://IP_VPS/webhook.php`)
   - **Content type**: `application/json`
   - **Secret**: Masukkan nilai `GITHUB_WEBHOOK_SECRET` dari file `/var/www/dadocbt/.env` (dapat dilihat juga di menu Admin *Utilitas Database*).
   - **Which events would you like to trigger this webhook?**: Pilih `Just the push event`.
   - Centang **Active**.
4. Klik **Add webhook**.
5. Selesai! Setiap kali Anda melakukan `git push origin main`, server VPS akan:
   - Membuat snapshot cadangan basis data otomatis ke `storage/backups/`.
   - Menarik kode terbaru (`git pull origin main`).
   - Menjalankan migrasi skema tabel (`php scripts/migrate.php`).
   - Menyetel kepemilikan file `www-data`.
   - Me-restart worker PHP-FPM dan membersihkan OPcache.

---

### Opsi B: GitHub Actions CI/CD (Pipa Otomasi)
Telah disediakan file alur kerja di [`.github/workflows/deploy.yml`](../.github/workflows/deploy.yml).

1. Di repository GitHub Anda, buka **Settings** > **Secrets and variables** > **Actions**.
2. Tambahkan rahasia berikut (*Repository Secrets*):
   - `VPS_HOST`: Alamat IP VPS Anda (contoh: `103.xxx.xxx.xxx`).
   - `VPS_USERNAME`: User login VPS (`root` atau user dengan akses sudo).
   - `VPS_SSH_KEY`: Private SSH Key (`id_rsa` / `id_ed25519`).
   - `VPS_PASSWORD`: (Opsional jika memakai autentikasi password).
   - `VPS_PORT`: Port SSH Anda (default: `22`).
3. Setiap kali ada commit masuk ke `main`, GitHub Actions akan menjalankan validasi sintaks PHP dan memicu `dadocbt-update` di VPS.

---

### Opsi C: Manual via CLI VPS (Shortcut Command)
Login ke VPS via SSH, lalu jalankan:

```bash
sudo dadocbt-update
```
*(Perintah ini adalah symlink global ke `/var/www/dadocbt/deploy/update.sh`)*

---

### Opsi D: Satu Klik dari Web Admin DADO CBT
1. Login sebagai Administrator di [`http://cbt.sekolah-anda.sch.id/login`](http://cbt.sekolah-anda.sch.id/login).
2. Buka menu **Pengaturan** > **Utilitas Database**.
3. Pada Panel 4 (**GitHub Auto-Update & Webhook**), klik tombol **"Jalankan Auto-Update GitHub Sekarang"**.
4. Sistem akan mengeksekusi skrip pembaruan secara aman dan menampilkan log commit terbaru.

---

### Opsi E: Terjadwal Otomatis via Cron Job (Opsional)
Jika ingin server memeriksa dan memperbarui sistem secara berkala (misal setiap hari Minggu pukul 02:00 pagi):

```bash
sudo crontab -e
```
Tambahkan baris berikut:
```cron
0 2 * * 0 /var/www/dadocbt/deploy/update.sh >> /var/www/dadocbt/storage/logs/cron-update.log 2>&1
```

---

## 🛠️ PERINTAH PENGELOLAAN VPS CEPAT

| Perintah Terminal | Fungsi & Penjelasan |
| :--- | :--- |
| `sudo dadocbt-update` | Memperbarui kode dari GitHub, backup pra-update, migrasi DB & restart worker. |
| `sudo dadocbt-backup` | Membuat backup basis data terkompresi `.sql.gz` dan membersihkan arsip >30 hari. |
| `sudo dadocbt-status` | Menampilkan kesehatan server (Nginx, PHP, MariaDB, RAM, disk, & jumlah siswa CBT). |
| `php scripts/migrate.php` | Memeriksa dan memperbarui 42 tabel database tanpa menghapus data yang ada. |

---

## 📁 LOKASI FILE PENTING

- **Aplikasi Web**: `/var/www/dadocbt`
- **File Konfigurasi**: `/var/www/dadocbt/.env`
- **Konfigurasi Nginx**: `/etc/nginx/sites-available/dadocbt`
- **Konfigurasi PHP-FPM Pool**: `/etc/php/8.3/fpm/pool.d/dadocbt.conf`
- **Konfigurasi MariaDB**: `/etc/mysql/mariadb.conf.d/99-dadocbt.cnf`
- **Arsip Backup Database**: `/var/www/dadocbt/storage/backups/`
- **Log Pembaruan**: `/var/www/dadocbt/storage/logs/update.log`
- **Log Webhook GitHub**: `/var/www/dadocbt/storage/logs/webhook.log`

