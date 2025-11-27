# Setup PostgreSQL untuk SIM Penjaminan Mutu

## Error yang Anda Alami

```
SQLSTATE[42P01]: Undefined table: 7 ERROR: relation "ikus" does not exist
```

**Penyebab:** Tabel `ikus` belum dibuat di database PostgreSQL. Migrations belum dijalankan.

## 🚀 Solusi Cepat (Recommended)

Jalankan script otomatis:

```bash
bash setup_postgres.sh
```

Script ini akan:
1. ✅ Cek PostgreSQL terinstall
2. ✅ Buat database `sim_pm` (atau gunakan yang ada)
3. ✅ Jalankan migrations
4. ✅ Seed data dummy
5. ✅ Verifikasi tabel berhasil dibuat

## 📋 Solusi Manual (Step by Step)

### 1. Pastikan PostgreSQL Running

```bash
# Linux/Mac
sudo service postgresql start
# atau
sudo systemctl start postgresql

# Windows (jika pakai XAMPP/Laragon)
- Start PostgreSQL service dari control panel
```

### 2. Update File `.env`

Pastikan konfigurasi database benar:

```env
DB_CONNECTION=pgsql
DB_HOST=127.0.0.1
DB_PORT=5432
DB_DATABASE=sim_pm
DB_USERNAME=postgres
DB_PASSWORD=your_password_here
```

**PENTING:** Ganti `your_password_here` dengan password PostgreSQL Anda!

### 3. Buat Database

**Via Terminal:**
```bash
# Login ke PostgreSQL
psql -U postgres

# Buat database
CREATE DATABASE sim_pm;

# Verifikasi
\l

# Exit
\q
```

**Via pgAdmin / DBeaver:**
1. Klik kanan "Databases"
2. Create → Database
3. Name: `sim_pm`
4. Encoding: UTF8
5. Save

### 4. Clear Cache Laravel

```bash
php artisan config:clear
php artisan cache:clear
```

### 5. Test Koneksi Database

```bash
php artisan tinker
```

Lalu ketik:
```php
DB::connection()->getPdo();
exit;
```

Jika tidak ada error → koneksi berhasil! ✅

### 6. Jalankan Migrations

**Pilihan A - Fresh Install (RECOMMENDED untuk development):**
```bash
php artisan migrate:fresh --seed
```

Ini akan:
- Drop semua tabel lama
- Buat tabel baru
- Insert data dummy (users, roles, IKU, dll)

**Pilihan B - Migration Biasa:**
```bash
php artisan migrate
```

**Pilihan C - Cek status dulu:**
```bash
php artisan migrate:status
```

### 7. Verifikasi Tabel Sudah Dibuat

```bash
psql -U postgres -d sim_pm

# List semua tabel
\dt

# Harus muncul:
# - ikus
# - iku_targets
# - iku_progress
# - users
# - roles
# - dll

# Cek struktur tabel ikus
\d ikus

# Exit
\q
```

## 🎯 Testing

Setelah migrations berhasil, test dengan:

1. **Cek data di database:**
   ```bash
   psql -U postgres -d sim_pm -c "SELECT * FROM ikus LIMIT 5;"
   ```

2. **Akses aplikasi:**
   - Buka halaman IKU
   - Klik "Tambah IKU"
   - Isi form dan simpan
   - Error "relation ikus does not exist" seharusnya HILANG ✅

3. **Login:**
   - Email: `admin@sim-pm.test`
   - Password: `password`

## ❌ Troubleshooting

### Error: "database sim_pm does not exist"

```bash
createdb -U postgres sim_pm
```

### Error: "password authentication failed"

1. Edit file `pg_hba.conf` (biasanya di `/etc/postgresql/XX/main/pg_hba.conf`)
2. Ganti `md5` jadi `trust` untuk local development
3. Restart PostgreSQL: `sudo service postgresql restart`
4. Atau set password: `ALTER USER postgres PASSWORD 'newpassword';`

### Error: "connection refused"

PostgreSQL tidak running. Start service:
```bash
sudo service postgresql start
```

### Error: "SQLSTATE[08006] [7]"

Port salah atau PostgreSQL tidak listening. Cek:
```bash
sudo netstat -plnt | grep 5432
```

### Migration Failed

Rollback dan coba lagi:
```bash
php artisan migrate:rollback
php artisan migrate:fresh --seed
```

### Tabel masih tidak ada

Cek apakah migrations file ada:
```bash
ls -la database/migrations/*iku*
```

Harus ada 3 files:
- `2025_11_13_095508_create_ikus_table.php`
- `2025_11_13_095541_create_iku_targets_table.php`
- `2025_11_13_095615_create_iku_progress_table.php`

## 📊 Struktur Tabel IKU

### Table: `ikus`
```sql
- id (bigint)
- kode_iku (varchar) UNIQUE
- nama_iku (varchar)
- deskripsi (text)
- satuan (enum: persen, jumlah, skor, nilai)
- target_type (enum: increase, decrease)
- kategori (varchar)
- bobot (integer)
- is_active (boolean)
- created_at, updated_at
```

### Table: `iku_targets`
```sql
- id (bigint)
- iku_id (foreign key → ikus)
- tahun_akademik_id (foreign key)
- unit_kerja_id (foreign key, nullable)
- program_studi_id (foreign key, nullable)
- target_value (decimal)
- periode (enum)
- keterangan (text)
- created_at, updated_at
```

### Table: `iku_progress`
```sql
- id (bigint)
- iku_target_id (foreign key → iku_targets)
- tanggal_capaian (date)
- nilai_capaian (decimal)
- persentase_capaian (decimal)
- keterangan (text)
- bukti_dokumen (varchar)
- created_by (foreign key → users)
- created_at, updated_at
```

## ✅ Checklist Setup

- [ ] PostgreSQL terinstall dan running
- [ ] File `.env` sudah dikonfigurasi dengan benar
- [ ] Database `sim_pm` sudah dibuat
- [ ] `php artisan config:clear` sudah dijalankan
- [ ] `php artisan migrate:fresh --seed` berhasil
- [ ] Tabel `ikus`, `iku_targets`, `iku_progress` ada di database
- [ ] Form tambah IKU tidak error lagi

## 🆘 Butuh Bantuan?

Jika masih error, jalankan dan kirim output:

```bash
# Cek status migrations
php artisan migrate:status

# Cek koneksi database
php artisan tinker
>>> DB::connection()->getPdo();
>>> DB::select('SELECT version()');

# Cek tabel di database
psql -U postgres -d sim_pm -c "\dt"
```
