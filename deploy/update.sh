#!/usr/bin/env bash

# ==============================================================================
# DADO CBT v1.5.3 — AUTO UPDATE SCRIPT FOR UBUNTU 24.04 LTS
# ==============================================================================
# Digunakan untuk memperbarui aplikasi DADO CBT langsung dari repository GitHub
# Memastikan backup otomatis dibuat, izin file diperbaiki, dan migrasi skema berjalan.
#
# Cara pakai manual:
#   sudo /var/www/dadocbt/deploy/update.sh
# Atau menggunakan shortcut:
#   sudo dadocbt-update
# ==============================================================================

set -e

# Warna Terminal
RED='\033[0;31m'
GREEN='\033[0;32m'
YELLOW='\033[1;33m'
BLUE='\033[0;34m'
CYAN='\033[0;36m'
NC='\033[0m' # No Color

APP_DIR="/var/www/dadocbt"
BRANCH="${1:-main}"

echo -e "${CYAN}========================================================${NC}"
echo -e "${CYAN}   DADO CBT v1.5.3 — GITHUB AUTO UPDATE ENGINE          ${NC}"
echo -e "${CYAN}========================================================${NC}"
echo -e "Waktu Mulai : $(date '+%Y-%m-%d %H:%M:%S')"
echo -e "Target Dir  : ${APP_DIR}"
echo -e "Branch      : ${BRANCH}"
echo -e "--------------------------------------------------------"

# 1. Pastikan dieksekusi dengan izin root / sudo
if [ "$EUID" -ne 0 ]; then
    echo -e "${RED}[ERROR] Script ini harus dijalankan dengan hak akses root / sudo!${NC}"
    exit 1
fi

# 2. Pastikan direktori aplikasi ada
if [ ! -d "$APP_DIR" ]; then
    echo -e "${RED}[ERROR] Direktori ${APP_DIR} tidak ditemukan!${NC}"
    exit 1
fi

cd "$APP_DIR"

# 3. Buat folder storage dan backups jika belum ada
mkdir -p "$APP_DIR/storage/backups"
mkdir -p "$APP_DIR/storage/logs"
mkdir -p "$APP_DIR/public/uploads"

# 4. Baca konfigurasi database dari file .env untuk backup otomatis
ENV_FILE="$APP_DIR/.env"
if [ -f "$ENV_FILE" ]; then
    echo -e "${BLUE}[1/6] Membaca konfigurasi database dari .env...${NC}"
    DB_NAME=$(grep -E '^DB_NAME=' "$ENV_FILE" | cut -d '=' -f2- | tr -d '"' | tr -d "'" | tr -d '\r')
    DB_USER=$(grep -E '^DB_USER=' "$ENV_FILE" | cut -d '=' -f2- | tr -d '"' | tr -d "'" | tr -d '\r')
    DB_PASS=$(grep -E '^DB_PASS=' "$ENV_FILE" | cut -d '=' -f2- | tr -d '"' | tr -d "'" | tr -d '\r')
    DB_HOST=$(grep -E '^DB_HOST=' "$ENV_FILE" | cut -d '=' -f2- | tr -d '"' | tr -d "'" | tr -d '\r')
    DB_PORT=$(grep -E '^DB_PORT=' "$ENV_FILE" | cut -d '=' -f2- | tr -d '"' | tr -d "'" | tr -d '\r')

    DB_NAME=${DB_NAME:-dadocbt}
    DB_USER=${DB_USER:-root}
    DB_HOST=${DB_HOST:-127.0.0.1}
    DB_PORT=${DB_PORT:-3306}

    # Backup otomatis sebelum update
    BACKUP_FILE="$APP_DIR/storage/backups/auto_pre_update_$(date +%Y%m%d_%H%M%S).sql"
    echo -e "${BLUE}[2/6] Membuat backup database pengaman ke ${BACKUP_FILE}...${NC}"
    if [ -n "$DB_PASS" ]; then
        mysqldump -h "$DB_HOST" -P "$DB_PORT" -u "$DB_USER" -p"$DB_PASS" "$DB_NAME" > "$BACKUP_FILE" 2>/dev/null || true
    else
        mysqldump -h "$DB_HOST" -P "$DB_PORT" -u "$DB_USER" "$DB_NAME" > "$BACKUP_FILE" 2>/dev/null || true
    fi

    if [ -s "$BACKUP_FILE" ]; then
        echo -e "${GREEN}      ✓ Backup database pra-update berhasil disimpan ($(du -h "$BACKUP_FILE" | cut -f1))${NC}"
    else
        echo -e "${YELLOW}      ! Peringatan: mysqldump tidak menghasilkan file atau database kosong. Melanjutkan update...${NC}"
    fi
else
    echo -e "${YELLOW}[!] File .env tidak ditemukan. Melanjutkan update tanpa backup pra-eksekusi.${NC}"
fi

# 5. Tarik pembaruan kode dari GitHub
echo -e "${BLUE}[3/6] Mengambil pembaruan dari repository GitHub (Branch: ${BRANCH})...${NC}"
if [ -d "$APP_DIR/.git" ]; then
    git config --global --add safe.directory "$APP_DIR"
    git fetch origin "$BRANCH"
    git checkout "$BRANCH"
    git reset --hard "origin/$BRANCH"
    echo -e "${GREEN}      ✓ Kode terbaru berhasil di-pull dari GitHub.${NC}"
else
    echo -e "${YELLOW}      ! Direktori bukan git clone aktif. Melewati langkah git pull.${NC}"
fi

# 6. Eksekusi Migrasi Skema Database Otomatis
echo -e "${BLUE}[4/6] Memeriksa dan memperbarui skema tabel database...${NC}"
if [ -f "$APP_DIR/scripts/migrate.php" ]; then
    php "$APP_DIR/scripts/migrate.php"
    echo -e "${GREEN}      ✓ Verifikasi skema database selesai.${NC}"
fi

# 7. Penyesuaian Hak Akses & Kepemilikan File
echo -e "${BLUE}[5/6] Mengatur kepemilikan dan hak akses direktori (www-data)...${NC}"
chown -R www-data:www-data "$APP_DIR"
find "$APP_DIR" -type d -exec chmod 755 {} +
find "$APP_DIR" -type f -exec chmod 644 {} +
chmod +x "$APP_DIR/deploy/"*.sh 2>/dev/null || true

# Direktori yang membutuhkan hak tulis penuh untuk web server
chmod -R 775 "$APP_DIR/storage"
chmod -R 775 "$APP_DIR/public/uploads"
echo -e "${GREEN}      ✓ Hak akses file dan folder www-data berhasil distandarisasi.${NC}"

# 8. Restart PHP-FPM dan Reload Nginx (Bersihkan OPCache)
echo -e "${BLUE}[6/6] Me-restart PHP-FPM & me-reload Nginx untuk membersihkan cache...${NC}"

# Deteksi versi PHP-FPM yang aktif di Ubuntu 24.04 (PHP 8.3) atau 22.04 (PHP 8.2)
PHP_SERVICE=""
for ver in 8.4 8.3 8.2 8.1; do
    if systemctl is-active --quiet "php${ver}-fpm"; then
        PHP_SERVICE="php${ver}-fpm"
        break
    fi
done

if [ -n "$PHP_SERVICE" ]; then
    systemctl restart "$PHP_SERVICE"
    echo -e "${GREEN}      ✓ Layanan ${PHP_SERVICE} berhasil di-restart.${NC}"
else
    echo -e "${YELLOW}      ! Layanan PHP-FPM tidak terdeteksi aktif.${NC}"
fi

if systemctl is-active --quiet nginx; then
    systemctl reload nginx
    echo -e "${GREEN}      ✓ Layanan Nginx berhasil di-reload.${NC}"
fi

# 9. Catat riwayat update ke log
LOG_ENTRY="$(date '+%Y-%m-%d %H:%M:%S') - Update sukses ke branch: ${BRANCH}"
if [ -d "$APP_DIR/.git" ]; then
    LATEST_COMMIT=$(git log -1 --pretty=format:"%h - %s (%an)")
    LOG_ENTRY="${LOG_ENTRY} | Commit: ${LATEST_COMMIT}"
fi
echo "$LOG_ENTRY" >> "$APP_DIR/storage/logs/update.log"

echo -e "${CYAN}--------------------------------------------------------${NC}"
echo -e "${GREEN}✔ PEMBARUAN DADO CBT BERHASIL DISELESAIKAN!${NC}"
if [ -n "$LATEST_COMMIT" ]; then
    echo -e "Commit Aktif : ${CYAN}${LATEST_COMMIT}${NC}"
fi
echo -e "Log File     : ${APP_DIR}/storage/logs/update.log"
echo -e "${CYAN}========================================================${NC}"
exit 0

