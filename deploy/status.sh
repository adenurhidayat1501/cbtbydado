#!/usr/bin/env bash

# ==============================================================================
# DADO CBT v1.5.3 — SYSTEM HEALTH & SERVICE STATUS CHECKER
# ==============================================================================
# Menampilkan kondisi kesehatan server, penggunaan memori/disk, dan layanan CBT.
#
# Cara pakai:
#   sudo dadocbt-status
# ==============================================================================

CYAN='\033[0;36m'
GREEN='\033[0;32m'
RED='\033[0;31m'
BOLD='\033[1m'
NC='\033[0m'

echo -e "${CYAN}${BOLD}"
echo "========================================================"
echo "          DADO CBT v1.5.3 — STATUS SISTEM VPS           "
echo "========================================================"
echo -e "${NC}"

echo -e "${BOLD}[ 1. Status Layanan Server ]${NC}"
for s in nginx mariadb php8.3-fpm php8.2-fpm; do
    if systemctl is-active --quiet "$s" 2>/dev/null; then
        echo -e "  ● $s : ${GREEN}AKTIF (Running)${NC}"
    elif systemctl list-unit-files | grep -q "$s"; then
        echo -e "  ● $s : ${RED}MATI (Inactive)${NC}"
    fi
done

echo -e "\n${BOLD}[ 2. Penggunaan Memori & Swap ]${NC}"
free -h | awk 'NR==1{print "  " $0} NR>1{print "  " $0}'

echo -e "\n${BOLD}[ 3. Penggunaan Kapasitas Disk ]${NC}"
df -h / | awk 'NR==1{print "  " $0} NR>1{print "  " $0}'

echo -e "\n${BOLD}[ 4. Informasi Repositori DADO CBT ]${NC}"
APP_DIR="/var/www/dadocbt"
if [ -d "$APP_DIR/.git" ]; then
    cd "$APP_DIR"
    BRANCH=$(git rev-parse --abbrev-ref HEAD 2>/dev/null || echo "Unknown")
    LAST_COMMIT=$(git log -1 --pretty=format:"%h - %s (%ad)" --date=relative 2>/dev/null || echo "-")
    echo -e "  Branch Aktif : ${CYAN}${BRANCH}${NC}"
    echo -e "  Commit Akhir : ${CYAN}${LAST_COMMIT}${NC}"
fi

echo -e "\n${BOLD}[ 5. Aktivitas CBT Peserta Ujian ]${NC}"
ENV_FILE="$APP_DIR/.env"
if [ -f "$ENV_FILE" ]; then
    DB_NAME=$(grep -E '^DB_NAME=' "$ENV_FILE" | cut -d '=' -f2- | tr -d '"' | tr -d "'" | tr -d '\r')
    DB_USER=$(grep -E '^DB_USER=' "$ENV_FILE" | cut -d '=' -f2- | tr -d '"' | tr -d "'" | tr -d '\r')
    DB_PASS=$(grep -E '^DB_PASS=' "$ENV_FILE" | cut -d '=' -f2- | tr -d '"' | tr -d "'" | tr -d '\r')
    
    PASS_FLAG=""
    if [ -n "$DB_PASS" ]; then
        PASS_FLAG="-p${DB_PASS}"
    fi
    
    TOTAL_SISWA=$(mariadb -u "$DB_USER" $PASS_FLAG "$DB_NAME" -se "SELECT COUNT(*) FROM master_siswa;" 2>/dev/null || echo "-")
    SEDANG_UJIAN=$(mariadb -u "$DB_USER" $PASS_FLAG "$DB_NAME" -se "SELECT COUNT(*) FROM cbt_durasi_siswa WHERE status = 1;" 2>/dev/null || echo "0")
    SELESAI_UJIAN=$(mariadb -u "$DB_USER" $PASS_FLAG "$DB_NAME" -se "SELECT COUNT(*) FROM cbt_durasi_siswa WHERE status = 2;" 2>/dev/null || echo "0")
    
    echo -e "  Total Siswa Terdaftar : ${BOLD}${TOTAL_SISWA}${NC}"
    echo -e "  Siswa Sedang Ujian    : ${GREEN}${BOLD}${SEDANG_UJIAN}${NC} (Online)"
    echo -e "  Siswa Selesai Ujian   : ${BOLD}${SELESAI_UJIAN}${NC}"
fi

echo -e "\n${CYAN}========================================================${NC}"

