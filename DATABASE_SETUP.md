# Setup Database untuk SIM Penjaminan Mutu

## Prerequisites
Pastikan Anda sudah menginstall dan menjalankan salah satu dari:
- MySQL Server (recommended)
- MariaDB Server
- XAMPP/WAMP/LARAGON (yang sudah include MySQL)

## Langkah-langkah Setup Database

### 1. Pastikan MySQL Server Running
```bash
# Windows (jika pakai XAMPP)
- Buka XAMPP Control Panel
- Start Apache dan MySQL

# Linux/Mac
sudo service mysql start
# atau
sudo systemctl start mysql
```

### 2. Buat Database
Pilih salah satu cara:

#### Cara 1: Menggunakan SQL File
```bash
mysql -u root -p < database/setup_database.sql
```

#### Cara 2: Menggunakan MySQL Command Line
```bash
mysql -u root -p
```
Lalu jalankan:
```sql
CREATE DATABASE sim_pm CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
exit;
```

#### Cara 3: Menggunakan phpMyAdmin
1. Buka phpMyAdmin (biasanya di http://localhost/phpmyadmin)
2. Klik "New" atau "Baru"
3. Nama database: `sim_pm`
4. Collation: `utf8mb4_unicode_ci`
5. Klik "Create"

### 3. Konfigurasi .env
File `.env` sudah dikonfigurasi dengan:
```
DB_CONNECTION=mysql
DB_HOST=127.0.0.1
DB_PORT=3306
DB_DATABASE=sim_pm
DB_USERNAME=root
DB_PASSWORD=
```

**Sesuaikan `DB_USERNAME` dan `DB_PASSWORD` jika berbeda di sistem Anda!**

### 4. Jalankan Migrations
```bash
php artisan migrate
```

### 5. Seed Database (Opsional, untuk data dummy)
```bash
php artisan db:seed
```

Atau jalankan sekaligus:
```bash
php artisan migrate:fresh --seed
```

### 6. Test Koneksi
```bash
php artisan tinker
```
Lalu coba:
```php
DB::connection()->getPdo();
```

Jika tidak ada error, database sudah terhubung!

## Troubleshooting

### Error: Access denied for user
Ubah `DB_USERNAME` dan `DB_PASSWORD` di file `.env` sesuai kredensial MySQL Anda.

### Error: Unknown database 'sim_pm'
Pastikan database `sim_pm` sudah dibuat (lihat langkah 2).

### Error: SQLSTATE[HY000] [2002] Connection refused
Pastikan MySQL server sedang running (lihat langkah 1).

## Setelah Setup Berhasil
Aplikasi siap digunakan! Akses halaman IKU dan coba tambah data baru.

Data login default (setelah seed):
- Email: admin@sim-pm.test
- Password: password
