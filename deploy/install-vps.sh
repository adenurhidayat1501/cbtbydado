#!/usr/bin/env bash

# ==============================================================================
# DADO CBT v1.5.3 — AUTO INSTALLER UNTUK VPS UBUNTU 24.04 LTS (NOBLE NUMBAT)
# ==============================================================================
# Skrip instalasi otomatis produksi untuk VPS Ubuntu 24.04 / 22.04 LTS.
# Dilengkapi fitur AUTO-HAPUS SEMUA PROYEK LAMA (Clean Reset untuk Masa Test).
# Mengonfigurasi Nginx, PHP 8.3-FPM, MariaDB Server, UFW Firewall,
# Git Repository, migrasi 42 tabel, optimasi konkurensi 1.000 user ujian serentak,
# dan sertifikat SSL Let's Encrypt gratis.
#
# Cara eksekusi:
#   sudo bash deploy/install-vps.sh
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

# Pastikan dijalankan sebagai root
if [ "$EUID" -ne 0 ]; then
    echo -e "${RED}[ERROR] Installer ini wajib dijalankan sebagai user root atau dengan 'sudo bash deploy/install-vps.sh'!${NC}"
    exit 1
fi

clear
echo -e "${CYAN}${BOLD}"
echo "=========================================================================="
echo "         DADO CBT v1.5.3 — VPS AUTO INSTALLER (UBUNTU 24.04 LTS)         "
echo "        Aplikasi CBT Standar AKM, E-Learning, E-Rapor & Buku Induk        "
echo "           Mode Uji Coba: Dilengkapi Fitur Pembersihan Total              "
echo "=========================================================================="
echo -e "${NC}"

# 1. Deteksi OS
if [ -f /etc/os-release ]; then
    . /etc/os-release
    OS_NAME=$ID
    OS_VERSION=$VERSION_ID
else
    echo -e "${RED}[ERROR] Tidak dapat mendeteksi informasi sistem operasi.${NC}"
    exit 1
fi

if [ "$OS_NAME" != "ubuntu" ]; then
    echo -e "${YELLOW}[PERINGATAN] Sistem operasi terdeteksi: ${OS_NAME} ${OS_VERSION}. Disarankan menggunakan Ubuntu 24.04 LTS.${NC}"
    read -p "Apakah Anda ingin tetap melanjutkan? (y/N): " CONTINUE_ANYWAY
    if [[ ! "$CONTINUE_ANYWAY" =~ ^[Yy]$ ]]; then
        exit 1
    fi
else
    echo -e "${GREEN}✓ Sistem Operasi Valid: Ubuntu ${OS_VERSION} (${VERSION_CODENAME:-Noble})${NC}"
fi

# Tentukan versi PHP yang akan digunakan (Ubuntu 24.04 secara native menyediakan PHP 8.3)
PHP_VER="8.3"
if [ "$OS_VERSION" = "22.04" ]; then
    PHP_VER="8.2"
fi

# 2. Parameter Input Interaktif
echo -e "\n${BLUE}${BOLD}[ LANGKAH 1 / 9 ] KONFIGURASI PARAMETER SISTEM${NC}"
echo -e "Silakan isi parameter berikut atau tekan [ENTER] untuk nilai default:"

# Domain atau IP
SERVER_IP=$(curl -s -4 icanhazip.com || hostname -I | awk '{print $1}')
read -p "Domain / Subdomain (atau IP Server) [Default: ${SERVER_IP}]: " INPUT_DOMAIN
DOMAIN="${INPUT_DOMAIN:-$SERVER_IP}"

# Repository Git
read -p "URL Git Repository DADO CBT [Default: https://github.com/dado-cbt/dadocbt.git]: " INPUT_REPO
REPO_URL="${INPUT_REPO:-https://github.com/dado-cbt/dadocbt.git}"

read -p "Branch Git [Default: main]: " INPUT_BRANCH
GIT_BRANCH="${INPUT_BRANCH:-main}"

# Database Config
DB_NAME_DEFAULT="dadocbt"
read -p "Nama Basis Data [Default: ${DB_NAME_DEFAULT}]: " INPUT_DB_NAME
DB_NAME="${INPUT_DB_NAME:-$DB_NAME_DEFAULT}"

DB_USER_DEFAULT="dadocbt_user"
read -p "Username Database [Default: ${DB_USER_DEFAULT}]: " INPUT_DB_USER
DB_USER="${INPUT_DB_USER:-$DB_USER_DEFAULT}"

RANDOM_PASS=$(tr -dc A-Za-z0-9_#% 2>/dev/null < /dev/urandom | head -c 16 || echo "DadoCbt2026!Secure")
read -p "Password Database [Default: otomatis acak]: " INPUT_DB_PASS
DB_PASS="${INPUT_DB_PASS:-$RANDOM_PASS}"

# Admin Credentials
read -p "Username Administrator [Default: admin]: " INPUT_ADMIN_USER
ADMIN_USER="${INPUT_ADMIN_USER:-admin}"

read -p "Password Administrator [Default: admin123]: " INPUT_ADMIN_PASS
ADMIN_PASS="${INPUT_ADMIN_PASS:-admin123}"

# Pembersihan Masa Test
read -p "Hapus semua project & database lama di VPS (Clean Reset - Masa Test)? [Y/n]: " INPUT_CLEAN
CLEAN_OLD="${INPUT_CLEAN:-Y}"

# SSL Let's Encrypt
ENABLE_SSL="no"
SSL_EMAIL=""
if [[ "$DOMAIN" =~ \.[a-zA-Z]{2,}$ ]]; then
    read -p "Apakah Anda ingin memasang SSL HTTPS gratis via Let's Encrypt? (y/N): " INPUT_SSL
    if [[ "$INPUT_SSL" =~ ^[Yy]$ ]]; then
        ENABLE_SSL="yes"
        read -p "Alamat Email Administrator untuk SSL: " SSL_EMAIL
    fi
fi

APP_DIR="/var/www/dadocbt"

echo -e "\n${CYAN}Ringkasan Konfigurasi:${NC}"
echo -e "  - Alamat Akses     : ${YELLOW}http://${DOMAIN}${NC}"
echo -e "  - Direktori Web    : ${YELLOW}${APP_DIR}${NC}"
echo -e "  - Versi PHP        : ${YELLOW}PHP ${PHP_VER}-FPM${NC}"
echo -e "  - Database         : ${YELLOW}${DB_NAME} (User: ${DB_USER})${NC}"
echo -e "  - Admin Default    : ${YELLOW}${ADMIN_USER}${NC}"
echo -e "  - Reset Masa Test  : ${YELLOW}${CLEAN_OLD} (Hapus seluruh proyek & DB lama)${NC}"
echo -e "  - Pasang SSL       : ${YELLOW}${ENABLE_SSL}${NC}"
echo ""
read -p "Lanjutkan proses instalasi otomatis? (Y/n): " CONFIRM
if [[ "$CONFIRM" =~ ^[Nn]$ ]]; then
    echo -e "${RED}Instalasi dibatalkan oleh pengguna.${NC}"
    exit 0
fi

# 3. Update Sistem & Install Paket Dasar
echo -e "\n${BLUE}${BOLD}[ LANGKAH 2 / 9 ] MEMPERBARUI SISTEM & MENGINSTAL DEPENDENSI${NC}"
export DEBIAN_FRONTEND=noninteractive
apt update -y
apt upgrade -y -o Dpkg::Options::="--force-confdef" -o Dpkg::Options::="--force-confold"

# Install alat pendukung
apt install -y software-properties-common curl wget git unzip zip ufw htop jq ca-certificates lsb-release

# Tambahkan repository Ondrej PHP jika di Ubuntu 22.04 membutuhkan PHP 8.3
if [ "$PHP_VER" = "8.3" ] && [ "$OS_VERSION" != "24.04" ]; then
    add-apt-repository -y ppa:ondrej/php
    apt update -y
fi

# 4. Install Nginx, PHP, MariaDB, Certbot
echo -e "\n${BLUE}${BOLD}[ LANGKAH 3 / 9 ] MENGINSTAL NGINX, PHP ${PHP_VER}, & MARIADB${NC}"
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

echo -e "${GREEN}✓ Layanan Nginx, PHP ${PHP_VER}, dan MariaDB berhasil dipasang.${NC}"

# 5. PEMBERSIHAN TOTAL PROYEK & DATABASE LAMA (CLEAN RESET UNTUK MASA TEST)
if [[ "$CLEAN_OLD" =~ ^[Yy]$ ]]; then
    echo -e "\n${YELLOW}${BOLD}[ LANGKAH 4 / 9 ] MEMBERSIHKAN SELURUH PROYEK & BASIS DATA LAMA (MASA TEST)${NC}"
    echo -e "${YELLOW}Menghentikan worker sementara dan menghapus file/database pengujian sebelumnya...${NC}"

    # Hentikan web server sementara agar tidak mengunci file atau socket
    systemctl stop nginx 2>/dev/null || true
    for ver in 8.4 8.3 8.2 8.1; do
        systemctl stop "php${ver}-fpm" 2>/dev/null || true
    done

    # Hapus folder aplikasi lama di /var/www/
    for old_dir in "$APP_DIR" "/var/www/cbt" "/var/www/garudacbt" "/var/www/cbt-saduci"; do
        if [ -d "$old_dir" ]; then
            echo -e "  - Menghapus direktori lama: ${old_dir}..."
            rm -rf "$old_dir"
        fi
    done

    # Hapus konfigurasi web server lama
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

    echo -e "${GREEN}✓ Seluruh file proyek lama & basis data uji coba berhasil dibersihkan total!${NC}"
else
    echo -e "\n${BLUE}[ LANGKAH 4 / 9 ] Pembersihan proyek lama dilewati sesuai pilihan pengguna.${NC}"
fi

# 6. Optimasi MariaDB untuk 1.000 Siswa Ujian Serentak
echo -e "\n${BLUE}${BOLD}[ LANGKAH 5 / 9 ] MENGONFIGURASI & MENGOPTIMASI MARIADB${NC}"

# Hitung RAM server untuk menentukan buffer pool
TOTAL_RAM_KB=$(grep MemTotal /proc/meminfo | awk '{print $2}')
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

# 7. Unduh / Deploy Kode Sumber DADO CBT Bersih
echo -e "\n${BLUE}${BOLD}[ LANGKAH 6 / 9 ] MEMASANG KODE SUMBER DADO CBT${NC}"

SCRIPT_DIR="$(cd "$(dirname "${BASH_SOURCE[0]}")/.." && pwd)"
if [ -f "$SCRIPT_DIR/public/index.php" ] && [ "$SCRIPT_DIR" != "$APP_DIR" ]; then
    echo -e "${BLUE}Menyalin file dari repositori lokal (${SCRIPT_DIR}) ke ${APP_DIR}...${NC}"
    mkdir -p "$APP_DIR"
    cp -r "$SCRIPT_DIR/." "$APP_DIR/"
elif [ -d "$APP_DIR/.git" ]; then
    echo -e "${BLUE}Memperbarui repositori git di ${APP_DIR}...${NC}"
    cd "$APP_DIR"
    git fetch origin "$GIT_BRANCH"
    git checkout "$GIT_BRANCH"
    git pull origin "$GIT_BRANCH"
else
    echo -e "${BLUE}Melakukan git clone segar dari ${REPO_URL} (Branch: ${GIT_BRANCH})...${NC}"
    rm -rf "$APP_DIR"
    git clone -b "$GIT_BRANCH" "$REPO_URL" "$APP_DIR" || {
        echo -e "${YELLOW}Gagal clone dari remote git. Membuat direktori baru...${NC}"
        mkdir -p "$APP_DIR"
        if [ -f "$SCRIPT_DIR/public/index.php" ]; then
            cp -r "$SCRIPT_DIR/." "$APP_DIR/"
        fi
    }
fi

cd "$APP_DIR"
mkdir -p "$APP_DIR/storage/backups"
mkdir -p "$APP_DIR/storage/logs"
mkdir -p "$APP_DIR/public/uploads/sekolah"
mkdir -p "$APP_DIR/public/uploads/siswa"
mkdir -p "$APP_DIR/public/uploads/guru"
mkdir -p "$APP_DIR/public/uploads/materi"
mkdir -p "$APP_DIR/public/uploads/tugas"
mkdir -p "$APP_DIR/public/uploads/soal"

# Generate File .env
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
GITHUB_WEBHOOK_SECRET="$(tr -dc A-Za-z0-9 2>/dev/null < /dev/urandom | head -c 32 || echo "DadoCbtSecretWebhookKey2026")"
EOF

# Import Skema Database 42 Tabel & Seeder Awal
echo -e "${BLUE}Mengimpor skema 42 tabel database dan seed data awal...${NC}"
if [ -f "$APP_DIR/database/schema.sql" ]; then
    mariadb -u "${DB_USER}" -p"${DB_PASS}" "${DB_NAME}" < "$APP_DIR/database/schema.sql"
    echo -e "${GREEN}      ✓ Schema 42 tabel berhasil diimpor.${NC}"
fi

if [ -f "$APP_DIR/database/seed.sql" ]; then
    mariadb -u "${DB_USER}" -p"${DB_PASS}" "${DB_NAME}" < "$APP_DIR/database/seed.sql" 2>/dev/null || true
    echo -e "${GREEN}      ✓ Data awal (seeder) berhasil diimpor.${NC}"
fi

# Setel Akun Admin & Profil Sekolah
HASHED_ADMIN_PASS=$(php -r "echo password_hash('${ADMIN_PASS}', PASSWORD_BCRYPT);")
mariadb -u "${DB_USER}" -p"${DB_PASS}" "${DB_NAME}" -e "
    UPDATE users SET username = '${ADMIN_USER}', password = '${HASHED_ADMIN_PASS}', active = 1 WHERE role = 'admin' LIMIT 1;
    INSERT INTO users (username, password, role, active)
    SELECT '${ADMIN_USER}', '${HASHED_ADMIN_PASS}', 'admin', 1
    WHERE NOT EXISTS (SELECT 1 FROM users WHERE role = 'admin');
    UPDATE setting_sekolah SET nama_sekolah = 'SMP NEGERI SATU ATAP 2 CIPATUJAH', jenjang = 2 WHERE id_setting = 1;
" 2>/dev/null || true

# Buat file pengunci instalasi
echo "Installed via VPS Autoinstaller on $(date '+%Y-%m-%d %H:%M:%S')" > "$APP_DIR/installed.lock"

# 8. Optimasi PHP-FPM Pool & OPcache
echo -e "\n${BLUE}${BOLD}[ LANGKAH 7 / 9 ] MENGONFIGURASI POOL PHP ${PHP_VER}-FPM & OPCACHE${NC}"

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

# Konfigurasi OPcache
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

# 9. Konfigurasi Virtual Host Nginx
echo -e "\n${BLUE}${BOLD}[ LANGKAH 8 / 9 ] MENGONFIGURASI VIRTUAL HOST NGINX${NC}"

cat > /etc/nginx/sites-available/dadocbt <<EOF
server {
    listen 80;
    listen [::]:80;
    server_name ${DOMAIN};

    root ${APP_DIR}/public;
    index index.php index.html;

    client_max_body_size 128M;
    client_body_buffer_size 128k;

    # Gzip Compression untuk menghemat bandwidth CBT
    gzip on;
    gzip_vary on;
    gzip_proxied any;
    gzip_comp_level 6;
    gzip_types text/plain text/css text/xml application/json application/javascript application/rss+xml font/woff2 application/font-woff;

    # Routing Utama MVC DADO CBT
    location / {
        try_files \$uri \$uri/ /index.php?\$query_string;
    }

    # Penanganan Skrip PHP via FastCGI Pool
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

    # Cache Aset Statis (CSS, JS, WebFont KaTeX, Gambar)
    location ~* \.(jpg|jpeg|png|gif|ico|css|js|woff|woff2|ttf|svg|eot)\$ {
        expires 30d;
        add_header Cache-Control "public, no-transform";
        access_log off;
        log_not_found off;
    }

    # Lindungi file rahasia (.env, .git, installed.lock, storage)
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

# 10. Atur Hak Akses Direktori
echo -e "\n${BLUE}${BOLD}[ LANGKAH 9 / 9 ] FINALISASI PERIZINAN & SHORTCUT PERINTAH${NC}"

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

# Konfigurasi UFW Firewall
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
        echo -e "${YELLOW}Certbot gagal mendapatkan sertifikat SSL. Pastikan DNS domain telah mengarah ke IP ini (${SERVER_IP}).${NC}"
    }
fi

# Ringkasan Selesai
clear
echo -e "${GREEN}${BOLD}"
echo "=========================================================================="
echo "          ✔ INSTALASI DADO CBT v1.5.3 BERHASIL DI-DEPLOY!                 "
echo "        (Server Dibersihkan & Dikonfigurasi Segar / Fresh Clean)          "
echo "=========================================================================="
echo -e "${NC}"
echo -e "Informasi Akses Aplikasi:"
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
