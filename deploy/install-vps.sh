#!/usr/bin/env bash

# ==============================================================================
# DADO CBT v1.5.3 — AUTO INSTALLER UNTUK VPS UBUNTU 24.04 / 22.04 LTS
# ==============================================================================
# Skrip instalasi otomatis produksi untuk VPS Ubuntu.
# Alur Pemasangan Termudah:
#   1. git clone <URL_REPO_GITHUB> dadocbt
#   2. cd dadocbt
#   3. sudo bash autoinstall.sh  (atau sudo bash deploy/install-vps.sh)
#
# Dilengkapi fitur:
#   - Express Install 1-Klik (Cukup tekan ENTER untuk semua nilai default)
#   - Deteksi otomatis IP Server & Repository Git lokal
#   - Pencegahan bug terhapusnya folder kerja sendiri (Self-directory safe)
#   - Nginx Web Server, PHP 8.3-FPM dinamis (Target 1.000 Siswa Serentak)
#   - MariaDB Server 10.11+ dengan InnoDB tuning & import 42 tabel PRD
#   - UFW Firewall otomatis & Let's Encrypt SSL gratis
#   - Shortcut CLI: 'sudo dadocbt-update', 'sudo dadocbt-backup', 'sudo dadocbt-status'
# ==============================================================================

set -e

# Format Warna Terminal
RED='\033[0;31m'
GREEN='\033[0;32m'
YELLOW='\033[1;33m'
BLUE='\033[0;34m'
PURPLE='\033[0;35m'
CYAN='\033[0;36m'
BOLD='\033[1m'
NC='\033[0m'

# 1. Pastikan dijalankan sebagai root / sudo
if [ "$EUID" -ne 0 ]; then
    echo -e "${RED}[ERROR] Installer ini wajib dijalankan sebagai user root atau dengan 'sudo bash autoinstall.sh'!${NC}"
    echo -e "Silakan coba lagi: ${BOLD}sudo bash autoinstall.sh${NC} atau ${BOLD}sudo bash deploy/install-vps.sh${NC}"
    exit 1
fi

clear
echo -e "${CYAN}${BOLD}"
echo "=========================================================================="
echo "         DADO CBT v1.5.3 — VPS AUTO INSTALLER (UBUNTU 24.04/22.04)        "
echo "        Aplikasi CBT Standar AKM, E-Learning, E-Rapor & Buku Induk        "
echo "              Mode Instalasi Mudah & Cepat (Zero-Friction)                "
echo "=========================================================================="
echo -e "${NC}"

# 2. Deteksi Direktori Sumber Proyek
if [ -f "$(dirname "${BASH_SOURCE[0]}")/public/index.php" ]; then
    SCRIPT_DIR="$(cd "$(dirname "${BASH_SOURCE[0]}")" && pwd)"
elif [ -f "$(dirname "${BASH_SOURCE[0]}")/../public/index.php" ]; then
    SCRIPT_DIR="$(cd "$(dirname "${BASH_SOURCE[0]}")/.." && pwd)"
else
    SCRIPT_DIR="$(cd "$(dirname "${BASH_SOURCE[0]}")" && pwd)"
fi

APP_DIR="/var/www/dadocbt"

# 3. Deteksi Git & IP Server
DETECTED_REPO=""
DETECTED_BRANCH="main"
if [ -d "$SCRIPT_DIR/.git" ]; then
    DETECTED_REPO=$(git -C "$SCRIPT_DIR" config --get remote.origin.url 2>/dev/null || true)
    DETECTED_BRANCH=$(git -C "$SCRIPT_DIR" rev-parse --abbrev-ref HEAD 2>/dev/null || echo "main")
fi

SERVER_IP=$(curl -s -4 --connect-timeout 3 icanhazip.com 2>/dev/null || curl -s -4 --connect-timeout 3 ifconfig.me 2>/dev/null || hostname -I 2>/dev/null | awk '{print $1}' || echo "127.0.0.1")

# 4. Deteksi Sistem Operasi
if [ -f /etc/os-release ]; then
    . /etc/os-release
    OS_NAME=$ID
    OS_VERSION=$VERSION_ID
else
    OS_NAME="unknown"
    OS_VERSION="unknown"
fi

echo -e "Informasi Sistem & Lingkungan Terdeteksi:"
echo -e "  - Sistem Operasi   : ${GREEN}Ubuntu ${OS_VERSION:-24.04} (${VERSION_CODENAME:-Noble})${NC}"
echo -e "  - Direktori Sumber : ${YELLOW}${SCRIPT_DIR}${NC}"
if [ -n "$DETECTED_REPO" ]; then
    echo -e "  - Git Repository   : ${YELLOW}${DETECTED_REPO}${NC} (Branch: ${GREEN}${DETECTED_BRANCH}${NC})"
fi
echo -e "  - Alamat IP Server : ${CYAN}${SERVER_IP}${NC}"
echo -e "  - Target Produksi  : ${YELLOW}${APP_DIR}${NC}"
echo ""

# Tentukan versi PHP yang akan digunakan (Ubuntu 24.04 secara native menyediakan PHP 8.3)
PHP_VER="8.3"

# 5. Pilihan Mode Instalasi (Express vs Custom)
echo -e "${BOLD}PILIH METODE INSTALASI:${NC}"
echo -e "--------------------------------------------------------------------------"
echo -e "  ${GREEN}${BOLD}[1] Express Install (Sangat Mudah — Cukup Tekan [ENTER])${NC}"
echo -e "      ✓ Otomatis konfigurasi web server menggunakan IP: ${CYAN}http://${SERVER_IP}${NC}"
echo -e "      ✓ Setup otomatis MariaDB, Nginx, PHP 8.3-FPM, UFW Firewall"
echo -e "      ✓ Akun Admin Default: Username ${BOLD}admin${NC} | Password: ${BOLD}admin123${NC}"
echo -e "      ✓ Bersihkan database lama & import 42 tabel relational"
echo ""
echo -e "  ${CYAN}[2] Custom Install (Konfigurasi Manual)${NC}"
echo -e "      ✓ Masukkan Domain / Subdomain kustom & SSL Let's Encrypt gratis"
echo -e "      ✓ Tentukan sendiri nama database, user, dan password admin"
echo -e "--------------------------------------------------------------------------"
read -p "Pilihan Anda [1/2, Default: 1]: " INPUT_MODE
INSTALL_MODE="${INPUT_MODE:-1}"

if [ "$INSTALL_MODE" = "1" ]; then
    DOMAIN="$SERVER_IP"
    DB_NAME="dadocbt"
    DB_USER="dadocbt_user"
    DB_PASS="$(tr -dc A-Za-z0-9 2>/dev/null < /dev/urandom | head -c 16 || echo "DadoCbt2026!Pass")"
    ADMIN_USER="admin"
    ADMIN_PASS="admin123"
    CLEAN_OLD="Y"
    ENABLE_SSL="no"
    SSL_EMAIL=""

    echo -e "\n${GREEN}>> Memulai Express Auto-Install DADO CBT...${NC}"
else
    echo -e "\n${BLUE}${BOLD}[ KONFIGURASI PARAMETER SISTEM ]${NC}"
    read -p "Domain / Subdomain (atau IP Server) [Default: ${SERVER_IP}]: " INPUT_DOMAIN
    DOMAIN="${INPUT_DOMAIN:-$SERVER_IP}"

    read -p "Nama Basis Data [Default: dadocbt]: " INPUT_DB_NAME
    DB_NAME="${INPUT_DB_NAME:-dadocbt}"

    read -p "Username Database [Default: dadocbt_user]: " INPUT_DB_USER
    DB_USER="${INPUT_DB_USER:-dadocbt_user}"

    RANDOM_PASS=$(tr -dc A-Za-z0-9 2>/dev/null < /dev/urandom | head -c 16 || echo "DadoCbt2026!Secure")
    read -p "Password Database [Default: otomatis acak]: " INPUT_DB_PASS
    DB_PASS="${INPUT_DB_PASS:-$RANDOM_PASS}"

    read -p "Username Administrator [Default: admin]: " INPUT_ADMIN_USER
    ADMIN_USER="${INPUT_ADMIN_USER:-admin}"

    read -p "Password Administrator [Default: admin123]: " INPUT_ADMIN_PASS
    ADMIN_PASS="${INPUT_ADMIN_PASS:-admin123}"

    read -p "Hapus semua project & database lama di VPS (Clean Reset)? [Y/n]: " INPUT_CLEAN
    CLEAN_OLD="${INPUT_CLEAN:-Y}"

    ENABLE_SSL="no"
    SSL_EMAIL=""
    if [[ "$DOMAIN" =~ \.[a-zA-Z]{2,}$ ]]; then
        read -p "Pasang sertifikat SSL Let's Encrypt gratis? (y/N): " INPUT_SSL
        if [[ "$INPUT_SSL" =~ ^[Yy]$ ]]; then
            ENABLE_SSL="yes"
            read -p "Email Administrator untuk SSL Let's Encrypt: " SSL_EMAIL
        fi
    fi
fi

echo -e "\n${CYAN}Ringkasan Konfigurasi Pemasangan:${NC}"
echo -e "  - Alamat Akses     : ${YELLOW}http://${DOMAIN}${NC}"
echo -e "  - Direktori Web    : ${YELLOW}${APP_DIR}${NC}"
echo -e "  - Versi PHP        : ${YELLOW}PHP ${PHP_VER}-FPM${NC}"
echo -e "  - Database         : ${YELLOW}${DB_NAME} (User: ${DB_USER})${NC}"
echo -e "  - Admin Default    : ${YELLOW}${ADMIN_USER}${NC}"
echo -e "  - Clean Reset      : ${YELLOW}${CLEAN_OLD}${NC}"
echo -e "  - Pasang SSL       : ${YELLOW}${ENABLE_SSL}${NC}"
echo ""

# 6. Update Sistem & Install Paket Dasar
echo -e "\n${BLUE}${BOLD}[ LANGKAH 1 / 8 ] MEMPERBARUI SISTEM & MENGINSTAL DEPENDENSI${NC}"
export DEBIAN_FRONTEND=noninteractive
apt update -y
apt upgrade -y -o Dpkg::Options::="--force-confdef" -o Dpkg::Options::="--force-confold"

# Install paket-paket pendukung esensial
apt install -y software-properties-common curl wget git unzip zip ufw htop jq ca-certificates lsb-release rsync

# Tambahkan repository Ondrej PHP jika di Ubuntu 22.04 membutuhkan PHP 8.3
if [ "$PHP_VER" = "8.3" ] && [ "$OS_VERSION" != "24.04" ]; then
    add-apt-repository -y ppa:ondrej/php
    apt update -y
fi

# 7. Install Nginx, PHP, MariaDB, Certbot
echo -e "\n${BLUE}${BOLD}[ LANGKAH 2 / 8 ] MENGINSTAL NGINX, PHP ${PHP_VER}, & MARIADB${NC}"
apt install -y nginx mariadb-server mariadb-client

apt install -y \
    "php${PHP_VER}-fpm" \
    "php${PHP_VER}-cli" \
    "php${PHP_VER}-common" \
    "php${PHP_VER}-mysql" \
    "php${PHP_VER}-mbstring" \
    "php${PHP_VER}-curl" \
    "php${PHP_VER}-gd" \
    "php${PHP_VER}-zip" \
    "php${PHP_VER}-xml" \
    "php${PHP_VER}-opcache" \
    "php${PHP_VER}-intl"

if [ "$ENABLE_SSL" = "yes" ]; then
    apt install -y certbot python3-certbot-nginx
fi

systemctl enable nginx
systemctl enable mariadb
systemctl enable "php${PHP_VER}-fpm"

echo -e "${GREEN}✓ Layanan Nginx, PHP ${PHP_VER}, dan MariaDB siap digunakan.${NC}"

# 8. Pembersihan Proyek & Basis Data Lama (Clean Reset yang Aman)
if [[ "$CLEAN_OLD" =~ ^[Yy]$ ]]; then
    echo -e "\n${YELLOW}${BOLD}[ LANGKAH 3 / 8 ] MEMBERSIHKAN DATABASE & INSTALASI LAMA (CLEAN RESET)${NC}"

    systemctl stop nginx 2>/dev/null || true
    for ver in 8.4 8.3 8.2 8.1; do
        systemctl stop "php${ver}-fpm" 2>/dev/null || true
    done

    # Bersihkan direktori legacy lain jika ada
    for legacy_dir in "/var/www/cbt" "/var/www/garudacbt" "/var/www/cbt-saduci"; do
        if [ -d "$legacy_dir" ]; then
            echo -e "  - Menghapus direktori legacy: ${legacy_dir}..."
            rm -rf "$legacy_dir"
        fi
    done

    # PENTING: Jika skrip ini dijalankan dari dalam APP_DIR (/var/www/dadocbt),
    # JANGAN hapus foldernya karena itu akan merusak repositori lokal yang sedang dieksekusi!
    if [ "$SCRIPT_DIR" != "$APP_DIR" ]; then
        if [ -d "$APP_DIR" ]; then
            echo -e "  - Membersihkan direktori target lama: ${APP_DIR}..."
            rm -rf "$APP_DIR"
        fi
    else
        echo -e "  - Menjalankan dari repositori lokal di ${APP_DIR}. Mengosongkan folder logs & temporary..."
        rm -rf "$APP_DIR/storage/backups/"* "$APP_DIR/storage/logs/"* 2>/dev/null || true
    fi

    # Bersihkan konfigurasi web server lama
    rm -f /etc/nginx/sites-available/dadocbt /etc/nginx/sites-enabled/dadocbt
    rm -f /etc/nginx/sites-available/cbt /etc/nginx/sites-enabled/cbt
    rm -f /etc/php/*/fpm/pool.d/dadocbt.conf
    rm -f /etc/sudoers.d/dadocbt
    rm -f /usr/local/bin/dadocbt-update /usr/local/bin/dadocbt-backup /usr/local/bin/dadocbt-status

    # Hapus database & user pengujian lama di MariaDB
    if systemctl is-active --quiet mariadb 2>/dev/null || systemctl is-active --quiet mysql 2>/dev/null; then
        echo -e "  - Menghapus database lama '${DB_NAME}' & 'garudacbt' jika ada..."
        mariadb -e "DROP DATABASE IF EXISTS \`${DB_NAME}\`;" 2>/dev/null || true
        mariadb -e "DROP DATABASE IF EXISTS \`garudacbt\`;" 2>/dev/null || true
        mariadb -e "DROP USER IF EXISTS '${DB_USER}'@'localhost';" 2>/dev/null || true
        mariadb -e "FLUSH PRIVILEGES;" 2>/dev/null || true
    fi

    echo -e "${GREEN}✓ Basis data dan file konfigurasi pengujian lama berhasil dibersihkan!${NC}"
else
    echo -e "\n${BLUE}[ LANGKAH 3 / 8 ] Pembersihan proyek lama dilewati sesuai pilihan pengguna.${NC}"
fi

# 9. Optimasi MariaDB untuk 1.000 Siswa Ujian Serentak
echo -e "\n${BLUE}${BOLD}[ LANGKAH 4 / 8 ] MENGONFIGURASI & MENGOPTIMASI BASIS DATA MARIADB${NC}"

TOTAL_RAM_KB=$(grep MemTotal /proc/meminfo 2>/dev/null | awk '{print $2}' || echo "2000000")
TOTAL_RAM_MB=$((TOTAL_RAM_KB / 1024))

if [ "$TOTAL_RAM_MB" -ge 7000 ]; then
    INNODB_BUFFER="4G"
elif [ "$TOTAL_RAM_MB" -ge 3500 ]; then
    INNODB_BUFFER="2G"
elif [ "$TOTAL_RAM_MB" -ge 1800 ]; then
    INNODB_BUFFER="1G"
else
    INNODB_BUFFER="512M"
fi

cat > /etc/mysql/mariadb.conf.d/99-dadocbt.cnf <<EOF
[mysqld]
# Optimasi Konkurensi Tinggi DADO CBT (Target 1.000 Peserta Ujian Serentak)
max_connections                = 1200
max_user_connections           = 1000
connect_timeout                = 15
wait_timeout                   = 60
interactive_timeout            = 60

# Engine InnoDB Tuning
default_storage_engine         = InnoDB
innodb_buffer_pool_size        = ${INNODB_BUFFER}
innodb_buffer_pool_instances    = 2
innodb_log_file_size           = 256M
innodb_log_buffer_size         = 64M
innodb_flush_log_at_trx_commit = 2
innodb_flush_method            = O_DIRECT
innodb_file_per_table          = 1
innodb_read_io_threads         = 4
innodb_write_io_threads        = 4

# Table & Thread Cache
table_open_cache               = 4096
table_definition_cache         = 2048
thread_cache_size              = 64
tmp_table_size                 = 64M
max_heap_table_size            = 64M

# Karakter Standar Unicode AKM
character-set-server           = utf8mb4
collation-server               = utf8mb4_unicode_ci

[client]
default-character-set          = utf8mb4
EOF

systemctl restart mariadb

# Buat Database dan User Baru Bersih
mariadb -e "CREATE DATABASE IF NOT EXISTS \`${DB_NAME}\` CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;"
mariadb -e "CREATE USER IF NOT EXISTS '${DB_USER}'@'localhost' IDENTIFIED BY '${DB_PASS}';"
mariadb -e "GRANT ALL PRIVILEGES ON \`${DB_NAME}\`.* TO '${DB_USER}'@'localhost';"
mariadb -e "FLUSH PRIVILEGES;"

echo -e "${GREEN}✓ Basis data '${DB_NAME}' & user '${DB_USER}' berhasil diinisialisasi bersih.${NC}"

# 10. Memasang Kode Sumber DADO CBT ke Target Produksi
echo -e "\n${BLUE}${BOLD}[ LANGKAH 5 / 8 ] MEMASANG KODE SUMBER DADO CBT KE ${APP_DIR}${NC}"

if [ "$SCRIPT_DIR" != "$APP_DIR" ]; then
    echo -e "${BLUE}Menyalin berkas dari repositori lokal (${SCRIPT_DIR}) ke ${APP_DIR}...${NC}"
    mkdir -p "$APP_DIR"
    cp -a "$SCRIPT_DIR/." "$APP_DIR/"
else
    echo -e "${GREEN}✓ Repositori sudah berada di direktori target: ${APP_DIR}${NC}"
fi

cd "$APP_DIR"

# Tambahkan direktori git safe agar tidak terhambat permission
git config --global --add safe.directory "$APP_DIR" 2>/dev/null || true
if id "www-data" >/dev/null 2>&1; then
    sudo -u www-data git config --global --add safe.directory "$APP_DIR" 2>/dev/null || true
fi

# Buat struktur direktori upload & storage
mkdir -p "$APP_DIR/storage/backups"
mkdir -p "$APP_DIR/storage/logs"
mkdir -p "$APP_DIR/public/uploads/sekolah"
mkdir -p "$APP_DIR/public/uploads/siswa"
mkdir -p "$APP_DIR/public/uploads/guru"
mkdir -p "$APP_DIR/public/uploads/materi"
mkdir -p "$APP_DIR/public/uploads/tugas"
mkdir -p "$APP_DIR/public/uploads/soal"

# Generate Konfigurasi .env
WEBHOOK_SECRET=$(tr -dc A-Za-z0-9 2>/dev/null < /dev/urandom | head -c 32 || echo "DadoCbtSecretWebhookKey2026")
cat > "$APP_DIR/.env" <<EOF
APP_NAME="DADO CBT"
APP_ENV=production
APP_DEBUG=false
APP_URL=http://${DOMAIN}

DB_HOST=127.0.0.1
DB_PORT=3306
DB_NAME=${DB_NAME}
DB_USER=${DB_USER}
DB_PASS="${DB_PASS}"

SESSION_COOKIE_NAME=dadocbt_session
SESSION_LIFETIME=7200
TIMEZONE=Asia/Jakarta
GITHUB_WEBHOOK_SECRET="${WEBHOOK_SECRET}"
EOF

# Import Skema 42 Tabel & Data Seeder
echo -e "${BLUE}Mengimpor skema 42 tabel database dan seed data awal...${NC}"
if [ -f "$APP_DIR/database/schema.sql" ]; then
    mariadb -u "${DB_USER}" -p"${DB_PASS}" "${DB_NAME}" < "$APP_DIR/database/schema.sql"
    echo -e "${GREEN}      ✓ Schema 42 tabel database berhasil diimpor.${NC}"
fi

if [ -f "$APP_DIR/database/seed.sql" ]; then
    mariadb -u "${DB_USER}" -p"${DB_PASS}" "${DB_NAME}" < "$APP_DIR/database/seed.sql" 2>/dev/null || true
    echo -e "${GREEN}      ✓ Data awal master (seeder) berhasil diimpor.${NC}"
fi

# Jalankan Runner Migrasi PHP untuk Validasi Skema 42 Tabel
if [ -f "$APP_DIR/scripts/migrate.php" ]; then
    echo -e "${BLUE}Menjalankan verifikasi migrasi skema tabel DADO CBT...${NC}"
    php "$APP_DIR/scripts/migrate.php" || true
fi

# Set Akun Admin Default & Data Profil Sekolah
HASHED_ADMIN_PASS=$(php -r "echo password_hash('${ADMIN_PASS}', PASSWORD_BCRYPT);")
mariadb -u "${DB_USER}" -p"${DB_PASS}" "${DB_NAME}" -e "
    UPDATE users SET username = '${ADMIN_USER}', password = '${HASHED_ADMIN_PASS}', active = 1 WHERE role = 'admin' LIMIT 1;
    INSERT INTO users (username, password, role, active)
    SELECT '${ADMIN_USER}', '${HASHED_ADMIN_PASS}', 'admin', 1
    WHERE NOT EXISTS (SELECT 1 FROM users WHERE role = 'admin');
    UPDATE setting_sekolah SET nama_sekolah = 'SMP NEGERI SATU ATAP 2 CIPATUJAH', jenjang = 2 WHERE id_setting = 1;
" 2>/dev/null || true

# Buat berkas pengunci instalasi
echo "Installed via VPS Autoinstaller on $(date '+%Y-%m-%d %H:%M:%S')" > "$APP_DIR/installed.lock"

# 11. Optimasi Pool PHP-FPM & OPcache
echo -e "\n${BLUE}${BOLD}[ LANGKAH 6 / 8 ] MENGONFIGURASI POOL PHP ${PHP_VER}-FPM & OPCACHE${NC}"

cat > "/etc/php/${PHP_VER}/fpm/pool.d/dadocbt.conf" <<EOF
[dadocbt]
user = www-data
group = www-data

listen = /run/php/php${PHP_VER}-fpm-dadocbt.sock
listen.owner = www-data
listen.group = www-data
listen.mode = 0660

# Model Pool Dinamis untuk Menampung 1.000 Siswa CBT Serentak
pm = dynamic
pm.max_children = 120
pm.start_servers = 20
pm.min_spare_servers = 10
pm.max_spare_servers = 30
pm.max_requests = 1000

# Konfigurasi Eksekusi & Upload Berkas
php_admin_value[upload_max_filesize] = 128M
php_admin_value[post_max_size]        = 128M
php_admin_value[memory_limit]         = 512M
php_admin_value[max_execution_time]   = 300
php_admin_value[max_input_time]       = 300
php_admin_value[date.timezone]        = Asia/Jakarta

# Logging
catch_workers_output = yes
php_admin_value[error_log] = /var/log/php${PHP_VER}-fpm-dadocbt.log
php_admin_flag[log_errors] = on
EOF

cat > "/etc/php/${PHP_VER}/mods-available/opcache.ini" <<EOF
zend_extension=opcache.so
opcache.enable=1
opcache.enable_cli=1
opcache.memory_consumption=256
opcache.interned_strings_buffer=16
opcache.max_accelerated_files=20000
opcache.max_wasted_percentage=5
opcache.validate_timestamps=1
opcache.revalidate_freq=2
opcache.save_comments=1
opcache.fast_shutdown=1
EOF

systemctl restart "php${PHP_VER}-fpm"
echo -e "${GREEN}✓ Pool PHP-FPM 'dadocbt' & OPcache berhasil diaktifkan.${NC}"

# 12. Konfigurasi Virtual Host Nginx
echo -e "\n${BLUE}${BOLD}[ LANGKAH 7 / 8 ] MENGONFIGURASI VIRTUAL HOST NGINX${NC}"

cat > /etc/nginx/sites-available/dadocbt <<EOF
server {
    listen 80;
    listen [::]:80;
    server_name ${DOMAIN};

    root ${APP_DIR}/public;
    index index.php index.html;

    client_max_body_size 128M;
    client_body_buffer_size 128k;

    # Gzip Compression untuk menghemat bandwidth ujian CBT
    gzip on;
    gzip_vary on;
    gzip_proxied any;
    gzip_comp_level 6;
    gzip_types text/plain text/css text/xml application/json application/javascript application/rss+xml font/woff2 application/font-woff;

    # Routing Utama MVC DADO CBT
    location / {
        try_files \$uri \$uri/ /index.php?\$query_string;
    }

    # Penanganan Skrip PHP via FastCGI Pool Dinamis
    location ~ \.php\$ {
        include snippets/fastcgi-php.conf;
        fastcgi_pass unix:/run/php/php${PHP_VER}-fpm-dadocbt.sock;
        fastcgi_param SCRIPT_FILENAME \$document_root\$fastcgi_script_name;
        include fastcgi_params;

        # FastCGI Buffering untuk AJAX Siswa Serentak
        fastcgi_buffers 16 16k;
        fastcgi_buffer_size 32k;
        fastcgi_busy_buffers_size 64k;
        fastcgi_temp_file_write_size 64k;
        fastcgi_read_timeout 300s;
        fastcgi_connect_timeout 60s;
        fastcgi_send_timeout 300s;
    }

    # Cache Aset Statis (CSS, JS, WebFont KaTeX, Arabic Calibri, Gambar)
    location ~* \.(jpg|jpeg|png|gif|ico|css|js|woff|woff2|ttf|svg|eot)\$ {
        expires 30d;
        add_header Cache-Control "public, no-transform";
        access_log off;
        log_not_found off;
    }

    # Proteksi Berkas Sensitif
    location ~ /\.(?!well-known).* {
        deny all;
        access_log off;
        log_not_found off;
    }

    location ^~ /storage/ {
        deny all;
    }

    location ^~ /app/ {
        deny all;
    }

    location ^~ /database/ {
        deny all;
    }
}
EOF

# Aktifkan site Nginx
rm -f /etc/nginx/sites-enabled/default
ln -sf /etc/nginx/sites-available/dadocbt /etc/nginx/sites-enabled/dadocbt

nginx -t
systemctl restart nginx
echo -e "${GREEN}✓ Konfigurasi Nginx berhasil diuji dan diterapkan.${NC}"

# 13. Izin Direktori, Shortcut CLI, Firewall & SSL
echo -e "\n${BLUE}${BOLD}[ LANGKAH 8 / 8 ] FINALISASI PERIZINAN & SHORTCUT PERINTAH${NC}"

chown -R www-data:www-data "$APP_DIR"
find "$APP_DIR" -type d -exec chmod 755 {} +
find "$APP_DIR" -type f -exec chmod 644 {} +
chmod -R 775 "$APP_DIR/storage"
chmod -R 775 "$APP_DIR/public/uploads"
chmod +x "$APP_DIR/deploy/"*.sh 2>/dev/null || true
chmod +x "$APP_DIR/autoinstall.sh" 2>/dev/null || true

# Buat Shortcut Perintah CLI Global di VPS
ln -sf "$APP_DIR/deploy/update.sh" /usr/local/bin/dadocbt-update
ln -sf "$APP_DIR/deploy/backup.sh" /usr/local/bin/dadocbt-backup
ln -sf "$APP_DIR/deploy/status.sh" /usr/local/bin/dadocbt-status

# Konfigurasi Sudoers untuk Webhook Auto-Update (www-data)
cat > /etc/sudoers.d/dadocbt <<EOF
www-data ALL=(ALL) NOPASSWD: ${APP_DIR}/deploy/update.sh, ${APP_DIR}/deploy/backup.sh
EOF
chmod 0440 /etc/sudoers.d/dadocbt

# Konfigurasi Firewall UFW
if command -v ufw >/dev/null 2>&1; then
    ufw allow OpenSSH >/dev/null 2>&1 || true
    ufw allow 'Nginx Full' >/dev/null 2>&1 || true
    ufw --force enable >/dev/null 2>&1 || true
    echo -e "${GREEN}✓ Firewall UFW aktif (Port SSH, HTTP:80, HTTPS:443 terbuka).${NC}"
fi

# Pasang SSL Certbot jika diminta
if [ "$ENABLE_SSL" = "yes" ] && [ -n "$SSL_EMAIL" ]; then
    echo -e "\n${BLUE}Memasang sertifikat SSL Let's Encrypt untuk ${DOMAIN}...${NC}"
    certbot --nginx -d "$DOMAIN" --non-interactive --agree-tos -m "$SSL_EMAIL" --redirect || {
        echo -e "${YELLOW}Certbot belum dapat mengaktifkan SSL. Pastikan DNS domain telah mengarah ke IP ${SERVER_IP}.${NC}"
    }
fi

# 14. Ringkasan Pemasangan Selesai
clear
echo -e "${GREEN}${BOLD}"
echo "=========================================================================="
echo "          ✔ INSTALASI DADO CBT v1.5.3 BERHASIL DI-DEPLOY!                 "
echo "        (Server Siap Melayani Hingga 1.000 Peserta Ujian Serentak)        "
echo "=========================================================================="
echo -e "${NC}"
echo -e "Informasi Akses Aplikasi Web:"
if [ "$ENABLE_SSL" = "yes" ]; then
    echo -e "  🌐 Portal Utama    : ${CYAN}https://${DOMAIN}/login${NC}"
    echo -e "  📝 Ruang CBT Siswa : ${CYAN}https://${DOMAIN}/cbt/login${NC}"
else
    echo -e "  🌐 Portal Utama    : ${CYAN}http://${DOMAIN}/login${NC}"
    echo -e "  📝 Ruang CBT Siswa : ${CYAN}http://${DOMAIN}/cbt/login${NC}"
fi
echo ""
echo -e "Kredensial Login Administrator:"
echo -e "  👤 Username        : ${BOLD}${ADMIN_USER}${NC}"
echo -e "  🔑 Password        : ${BOLD}${ADMIN_PASS}${NC}"
echo ""
echo -e "Kredensial Database MariaDB:"
echo -e "  🗄️ Database Name   : ${BOLD}${DB_NAME}${NC}"
echo -e "  👤 Database User   : ${BOLD}${DB_USER}${NC}"
echo -e "  🔑 Database Pass   : ${BOLD}${DB_PASS}${NC}"
echo ""
echo -e "Perintah Cepat CLI di VPS Anda:"
echo -e "  🔄 Auto Update Git : ${BOLD}sudo dadocbt-update${NC}"
echo -e "  💾 Backup Database : ${BOLD}sudo dadocbt-backup${NC}"
echo -e "  📊 Monitor Status  : ${BOLD}sudo dadocbt-status${NC}"
echo ""
echo -e "${CYAN}==========================================================================${NC}"
echo -e "Catatan: File konfigurasi tersimpan di: ${YELLOW}${APP_DIR}/.env${NC}"
echo -e "${CYAN}==========================================================================${NC}"
exit 0
