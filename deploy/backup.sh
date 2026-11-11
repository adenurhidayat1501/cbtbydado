#!/usr/bin/env bash

# ==============================================================================
# DADO CBT v1.5.3 — DATABASE BACKUP UTILITY SCRIPT
# ==============================================================================
# Membuat arsip SQL terkompresi (.sql.gz) dan otomatis menghapus cadangan >30 hari.
#
# Cara pakai:
#   sudo /var/www/dadocbt/deploy/backup.sh
# Atau menggunakan shortcut:
#   sudo dadocbt-backup
# ==============================================================================

set -e

APP_DIR="/var/www/dadocbt"
ENV_FILE="$APP_DIR/.env"
BACKUP_DIR="$APP_DIR/storage/backups"

mkdir -p "$BACKUP_DIR"

if [ ! -f "$ENV_FILE" ]; then
    echo "[ERROR] File .env tidak ditemukan di $APP_DIR"
    exit 1
fi

DB_NAME=$(grep -E '^DB_NAME=' "$ENV_FILE" | cut -d '=' -f2- | tr -d '"' | tr -d "'" | tr -d '\r')
DB_USER=$(grep -E '^DB_USER=' "$ENV_FILE" | cut -d '=' -f2- | tr -d '"' | tr -d "'" | tr -d '\r')
DB_PASS=$(grep -E '^DB_PASS=' "$ENV_FILE" | cut -d '=' -f2- | tr -d '"' | tr -d "'" | tr -d '\r')
DB_HOST=$(grep -E '^DB_HOST=' "$ENV_FILE" | cut -d '=' -f2- | tr -d '"' | tr -d "'" | tr -d '\r')
DB_PORT=$(grep -E '^DB_PORT=' "$ENV_FILE" | cut -d '=' -f2- | tr -d '"' | tr -d "'" | tr -d '\r')

DB_NAME=${DB_NAME:-dadocbt}
DB_USER=${DB_USER:-root}
DB_HOST=${DB_HOST:-127.0.0.1}
DB_PORT=${DB_PORT:-3306}

TIMESTAMP=$(date '+%Y%m%d_%H%M%S')
BACKUP_FILE="${BACKUP_DIR}/backup_${DB_NAME}_${TIMESTAMP}.sql"

echo ">> Membuat backup database '${DB_NAME}'..."

if [ -n "$DB_PASS" ]; then
    mysqldump -h "$DB_HOST" -P "$DB_PORT" -u "$DB_USER" -p"$DB_PASS" \
        --single-transaction --quick --routines --triggers "$DB_NAME" > "$BACKUP_FILE"
else
    mysqldump -h "$DB_HOST" -P "$DB_PORT" -u "$DB_USER" \
        --single-transaction --quick --routines --triggers "$DB_NAME" > "$BACKUP_FILE"
fi

# Kompresi gzip
gzip -f "$BACKUP_FILE"
GZ_FILE="${BACKUP_FILE}.gz"

echo "✓ Backup berhasil disimpan di: ${GZ_FILE} ($(du -h "$GZ_FILE" | cut -f1))"

# Hapus backup yang lebih tua dari 30 hari untuk hemat ruang disk
find "$BACKUP_DIR" -type f -name "backup_*.sql.gz" -mtime +30 -exec rm -f {} +
echo "✓ Rotasi backup: file lama >30 hari dibersihkan."

