#!/bin/bash

# Setup Database PostgreSQL untuk SIM Penjaminan Mutu
# Jalankan dengan: bash setup_postgres.sh

echo "========================================="
echo "Setup PostgreSQL untuk SIM Penjaminan Mutu"
echo "========================================="
echo ""

# Warna
RED='\033[0;31m'
GREEN='\033[0;32m'
YELLOW='\033[1;33m'
NC='\033[0m' # No Color

# Cek apakah PostgreSQL terinstall
if ! command -v psql &> /dev/null; then
    echo -e "${RED}PostgreSQL tidak ditemukan!${NC}"
    echo "Silakan install PostgreSQL terlebih dahulu."
    exit 1
fi

echo -e "${GREEN}PostgreSQL ditemukan!${NC}"
echo ""

# Ambil kredensial dari .env atau gunakan default
DB_DATABASE=${DB_DATABASE:-sim_pm}
DB_USERNAME=${DB_USERNAME:-postgres}

echo "Database yang akan dibuat: $DB_DATABASE"
echo "Username PostgreSQL: $DB_USERNAME"
echo ""

# Cek apakah database sudah ada
if psql -U $DB_USERNAME -lqt | cut -d \| -f 1 | grep -qw $DB_DATABASE; then
    echo -e "${YELLOW}Database '$DB_DATABASE' sudah ada.${NC}"
    read -p "Hapus dan buat ulang? (y/N): " -n 1 -r
    echo
    if [[ $REPLY =~ ^[Yy]$ ]]; then
        echo "Menghapus database lama..."
        dropdb -U $DB_USERNAME $DB_DATABASE
        echo "Membuat database baru..."
        createdb -U $DB_USERNAME $DB_DATABASE
        echo -e "${GREEN}Database berhasil dibuat ulang!${NC}"
    else
        echo "Menggunakan database yang ada."
    fi
else
    echo "Membuat database '$DB_DATABASE'..."
    createdb -U $DB_USERNAME $DB_DATABASE
    if [ $? -eq 0 ]; then
        echo -e "${GREEN}Database berhasil dibuat!${NC}"
    else
        echo -e "${RED}Gagal membuat database!${NC}"
        exit 1
    fi
fi

echo ""
echo "Membersihkan cache Laravel..."
php artisan config:clear
php artisan cache:clear

echo ""
echo "Menjalankan migrations..."
php artisan migrate:fresh --seed

if [ $? -eq 0 ]; then
    echo ""
    echo -e "${GREEN}=========================================${NC}"
    echo -e "${GREEN}Setup berhasil!${NC}"
    echo -e "${GREEN}=========================================${NC}"
    echo ""
    echo "Database: $DB_DATABASE"
    echo "Tabel yang dibuat:"
    psql -U $DB_USERNAME -d $DB_DATABASE -c "\dt" | grep -E "ikus|iku_targets|iku_progress"
    echo ""
    echo "Login default:"
    echo "  Email: admin@sim-pm.test"
    echo "  Password: password"
    echo ""
    echo "Aplikasi siap digunakan!"
else
    echo -e "${RED}Gagal menjalankan migrations!${NC}"
    echo "Periksa kredensial database di file .env"
    exit 1
fi
