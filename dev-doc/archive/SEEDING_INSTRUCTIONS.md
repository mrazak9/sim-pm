# Instruksi Seeding Data Template Butir Akreditasi

## Mengisi Data Template Butir Akreditasi

Untuk mengisi dropdown **Instrumen** dan **Kategori** di form Butir Akreditasi, Anda perlu menjalankan seeder yang sudah disediakan.

### Instrumen yang Tersedia

Seeder akan mengisi template butir untuk instrumen berikut:

1. **BANPT 4.0** - Akreditasi Program Studi BAN-PT versi 4.0
   - 9 Kriteria
   - ~50+ butir akreditasi template

2. **LAMEMBA** - LAM Ekonomi, Manajemen, Bisnis, dan Akuntansi
   - 9 Standar
   - ~20 butir akreditasi template

3. **LAMINFOKOM** - LAM Infokom (Informatika dan Komputer)
   - 9 Standar
   - ~15 butir akreditasi template

### Cara Menjalankan Seeder

#### 1. Pastikan Database Sudah Ter-migrate

```bash
php artisan migrate
```

#### 2. Jalankan Semua Seeder

```bash
php artisan db:seed
```

**Atau** jalankan seeder spesifik:

```bash
# Hanya BANPT 4.0
php artisan db:seed --class=ButirAkreditasiSeeder

# Hanya LAM (LAMEMBA & LAMINFOKOM)
php artisan db:seed --class=ButirAkreditasiLAMSeeder

# Keduanya
php artisan db:seed --class=ButirAkreditasiSeeder
php artisan db:seed --class=ButirAkreditasiLAMSeeder
```

### Hasil Setelah Seeding

Setelah menjalankan seeder, Anda akan memiliki:

#### Dropdown Instrumen akan berisi:
- BANPT 4.0
- LAMEMBA
- LAMINFOKOM

#### Dropdown Kategori (tergantung instrumen yang dipilih):

**BANPT 4.0:**
- Kriteria 1
- Kriteria 2
- Kriteria 3
- ... (9 kriteria)

**LAMEMBA:**
- Standar 1: Visi, Misi, Tujuan, dan Sasaran
- Standar 2: Tata Pamong, Kepemimpinan, Sistem Pengelolaan, dan Penjaminan Mutu
- Standar 3: Mahasiswa
- ... (9 standar)

**LAMINFOKOM:**
- Standar 1: Visi, Misi, Tujuan, dan Strategi
- Standar 2: Tata Kelola, Kepemimpinan, dan Sistem Pengelolaan
- Standar 3: Mahasiswa
- ... (9 standar)

### Cara Kerja Template Butir

1. **Template Butir** (`periode_akreditasi_id = NULL`)
   - Data master yang bisa di-copy ke berbagai periode
   - Akses melalui menu "Butir Akreditasi" → Mode "Template"

2. **Butir Per-Periode** (`periode_akreditasi_id != NULL`)
   - Copy dari template untuk periode tertentu
   - Bisa di-edit tanpa mempengaruhi template
   - Akses melalui "Import Butir" di detail periode

### Workflow Penggunaan

```
1. Jalankan Seeder → Template Butir ter-isi
                   ↓
2. Buat Periode Akreditasi (pilih instrumen: BANPT 4.0 / LAMEMBA / LAMINFOKOM)
                   ↓
3. Import Butir dari Template → Copy semua butir ke periode
                   ↓
4. Edit/Isi butir per periode sesuai kebutuhan
```

### Menambah Instrumen Baru

Jika Anda ingin menambah instrumen lain (misalnya LAM-PTKes, LAMTEKFAR, dll):

1. Buat seeder baru mirip `ButirAkreditasiLAMSeeder.php`
2. Definisikan butir-butir akreditasi untuk instrumen tersebut
3. Daftarkan di `DatabaseSeeder.php`
4. Jalankan seeder

### Troubleshooting

#### Dropdown masih kosong setelah seeding?

1. Cek apakah seeder berhasil:
   ```bash
   php artisan tinker
   >>> App\Models\ButirAkreditasi::templatesOnly()->count()
   ```

2. Clear cache:
   ```bash
   php artisan cache:clear
   php artisan config:clear
   ```

3. Reload halaman browser

#### Error saat menjalankan seeder?

- Pastikan database connection sudah benar di `.env`
- Pastikan migration sudah dijalankan
- Check log: `storage/logs/laravel.log`

---

**Note:** Template butir yang sudah di-seed dapat diedit atau ditambah melalui menu "Butir Akreditasi" dengan memilih mode "Template".
