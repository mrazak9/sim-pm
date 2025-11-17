# UI Template Builder - Panduan Penggunaan

## Ringkasan

Sistem UI Template Builder memungkinkan pengguna untuk membuat dan mengelola template form butir akreditasi melalui antarmuka web, tanpa perlu menulis kode atau migration.

## Fitur yang Telah Diimplementasikan

### 1. Template Management Page (`/akreditasi/butir-templates`)

Halaman ini menampilkan daftar semua butir akreditasi dengan status template:

**Fitur:**
- ✅ **Statistics Cards**: Menampilkan total butir, butir dengan template, tanpa template, dan progress persentase
- ✅ **Search**: Cari butir berdasarkan kode atau nama
- ✅ **Filter**: Filter berdasarkan status template (Semua / Dengan Template / Tanpa Template)
- ✅ **Table View**: Menampilkan daftar butir dengan informasi:
  - Kode butir
  - Nama butir
  - Status template (badge hijau/abu-abu)
  - Tipe template (Table/Narasi/Checklist/Metric)
- ✅ **Actions**:
  - **+ Tambah**: Untuk butir tanpa template (navigasi ke builder)
  - **Edit**: Untuk butir dengan template (navigasi ke builder)
  - **Copy**: Copy template dari butir lain
  - **Hapus**: Hapus template yang ada

**Copy Template Modal:**
- Memilih butir sumber yang sudah memiliki template
- Copy form configuration ke butir target
- Otomatis refresh data setelah copy

### 2. Template Builder Page (`/akreditasi/butir-templates/:id`)

Halaman form builder untuk membuat atau mengedit template butir:

**Fitur Utama:**

#### A. Template Type Selection
5 tipe template yang tersedia dengan icon dan deskripsi:
- 📊 **Table**: Data berbentuk tabel dengan kolom
- 📝 **Narrative**: Teks panjang/uraian
- ✓ **Checklist**: Daftar item yang bisa dicentang
- 📈 **Metric**: Nilai/angka indikator
- 🔀 **Mixed**: Kombinasi berbagai tipe (coming soon)

#### B. Basic Information
- **Label Template**: Judul form yang akan ditampilkan (required)
- **Help Text**: Teks bantuan untuk user yang mengisi

#### C. Template Configuration Per Type

**1. Table Template:**
- Tambah/hapus kolom secara dinamis
- Konfigurasi per kolom:
  - Nama field (name)
  - Label kolom (label)
  - Tipe input: Text, Textarea, Number, Currency, Date, Select
  - Lebar kolom (width %)
  - Placeholder text
  - Required/optional
  - Opsi dropdown (untuk tipe Select)
- Validasi:
  - Min rows (minimum baris yang harus diisi)
  - Max rows (maksimum baris)
- Options:
  - Show summary (tampilkan ringkasan)

**2. Narrative Template:**
- Rich text editor untuk uraian panjang
- Konfigurasi:
  - Min length (minimum karakter)
  - Max length (maksimum karakter)
  - Required/optional

**3. Checklist Template:**
- Tambah/hapus item checklist
- Konfigurasi per item:
  - Label item
  - Deskripsi item
  - Required/optional

**4. Metric Template:**
- Tambah/hapus metrik
- Konfigurasi per metrik:
  - Nama field
  - Label
  - Tipe: Number, Percentage, Currency
  - Help text
  - Required/optional

**5. Mixed Template:**
- Akan ditambahkan di versi berikutnya
- Untuk sementara gunakan tipe lain atau edit manual

#### D. Actions
- **Simpan Template**: Validasi dan simpan ke database
- **Batal**: Kembali ke halaman management

### 3. Backend API

**Endpoints yang tersedia:**

```
GET    /api/butir-templates              - List semua butir dengan status template
GET    /api/butir-templates/{id}         - Get template config untuk butir tertentu
POST   /api/butir-templates/{id}         - Create/update template
DELETE /api/butir-templates/{id}         - Hapus template
POST   /api/butir-templates/{id}/copy    - Copy template dari butir lain
```

**Query Parameters untuk List:**
- `search`: Cari berdasarkan kode/nama
- `has_template`: Filter (true/false)

**Response Format:**
```json
{
  "data": [
    {
      "id": 1,
      "kode": "C.1",
      "nama": "Penelitian Dosen",
      "has_template": true,
      "template_type": "table",
      "metadata": {
        "form_config": { ... }
      }
    }
  ],
  "stats": {
    "total": 50,
    "with_template": 15,
    "without_template": 35,
    "percentage": 30
  }
}
```

## Cara Menggunakan

### Membuat Template Baru

1. **Akses Template Management**
   - Buka `/akreditasi/butir-templates`
   - Lihat daftar butir dengan status template

2. **Pilih Butir**
   - Cari butir yang ingin ditambahkan template
   - Klik tombol **"+ Tambah"** pada kolom Actions

3. **Pilih Tipe Template**
   - Klik salah satu dari 5 tipe template yang tersedia
   - Tipe akan menentukan struktur form

4. **Isi Informasi Dasar**
   - Masukkan **Label Template** (wajib)
   - Tambahkan **Help Text** untuk panduan user

5. **Konfigurasi Form**

   **Untuk Table:**
   - Klik **"+ Tambah Kolom"**
   - Isi nama field, label, tipe input
   - Untuk tipe Select, masukkan opsi dengan format:
     ```
     key1=Nilai 1
     key2=Nilai 2
     key3=Nilai 3
     ```
   - Atur lebar kolom (contoh: 30%)
   - Centang "Wajib Diisi" jika perlu
   - Ulangi untuk kolom lainnya
   - Atur min/max rows di bagian "Opsi Tabel"

   **Untuk Narrative:**
   - Atur min/max length sesuai kebutuhan
   - Centang "Wajib Diisi" jika perlu

   **Untuk Checklist:**
   - Klik **"+ Tambah Item"**
   - Isi label dan deskripsi item
   - Centang "Wajib" jika item harus di-check
   - Ulangi untuk item lainnya

   **Untuk Metric:**
   - Klik **"+ Tambah Metrik"**
   - Isi nama field, label, tipe
   - Tambahkan help text
   - Ulangi untuk metrik lainnya

6. **Simpan Template**
   - Klik **"Simpan Template"**
   - Template akan tersimpan dan langsung bisa digunakan

### Mengedit Template

1. Buka `/akreditasi/butir-templates`
2. Cari butir yang ingin diedit
3. Klik tombol **"Edit"** pada kolom Actions
4. Ubah konfigurasi sesuai kebutuhan
5. Klik **"Simpan Template"**

### Copy Template

Jika ada butir yang mirip, Anda bisa copy template:

1. Buka `/akreditasi/butir-templates`
2. Cari butir yang **belum** ada template
3. Klik tombol **"Copy"** pada kolom Actions
4. Pilih butir sumber dari dropdown (yang sudah ada template)
5. Klik **"Copy Template"**
6. Template akan tersalin, Anda bisa edit nanti jika perlu penyesuaian

### Menghapus Template

1. Buka `/akreditasi/butir-templates`
2. Cari butir yang ingin dihapus template-nya
3. Klik tombol **"Hapus"** pada kolom Actions
4. Konfirmasi penghapusan
5. Template akan dihapus, butir kembali menggunakan form legacy (rich text)

## Contoh Template

### Contoh 1: Penelitian Dosen (Table)

**Basic Info:**
- Label: "Data Penelitian Dosen"
- Help Text: "Isikan seluruh penelitian yang dilakukan oleh dosen dalam periode akreditasi"

**Columns:**
1. Judul Penelitian (text, 30%, required)
2. Tahun (number, 8%, required, min: 2020, max: 2030)
3. Nama Ketua Peneliti (text, 20%, required)
4. Sumber Dana (select, 15%, required)
   - internal=Internal PT
   - dikti=Kemenristekdikti
   - swasta=Swasta/Industri
5. Jumlah Dana (currency, 15%, optional)
6. Status (select, 12%, required)
   - selesai=Selesai
   - berjalan=Sedang Berjalan

**Table Options:**
- Min rows: 1
- Max rows: 200
- Show summary: Yes

### Contoh 2: Visi Misi (Narrative)

**Basic Info:**
- Label: "Visi dan Misi Program Studi"
- Help Text: "Tuliskan visi dan misi program studi secara lengkap dan jelas"

**Configuration:**
- Min length: 200 karakter
- Max length: 5000 karakter
- Required: Yes

### Contoh 3: Kelengkapan Dokumen (Checklist)

**Basic Info:**
- Label: "Kelengkapan Dokumen Akreditasi"
- Help Text: "Centang dokumen yang sudah tersedia"

**Items:**
1. Label: "SK Pendirian Program Studi", Required: Yes
2. Label: "Dokumen Kurikulum", Required: Yes
3. Label: "Data Mahasiswa 3 Tahun Terakhir", Required: Yes
4. Label: "Data Dosen Tetap", Required: Yes

## Tips dan Best Practices

1. **Penamaan Field**
   - Gunakan snake_case untuk nama field (contoh: `judul_penelitian`)
   - Nama field harus unik dalam satu template

2. **Label yang Jelas**
   - Gunakan label yang mudah dipahami user
   - Hindari singkatan yang ambigu

3. **Help Text**
   - Berikan panduan yang jelas di help text
   - Jelaskan format atau contoh jika perlu

4. **Validasi**
   - Set required untuk field yang wajib
   - Atur min/max rows untuk table sesuai kebutuhan
   - Jangan terlalu ketat agar user tidak kesulitan

5. **Tipe Input**
   - Gunakan `currency` untuk input uang (otomatis format Rupiah)
   - Gunakan `number` untuk angka biasa
   - Gunakan `select` untuk pilihan terbatas
   - Gunakan `date` untuk tanggal

6. **Template Reuse**
   - Gunakan fitur Copy untuk butir yang mirip
   - Edit hasil copy sesuai kebutuhan spesifik

## Troubleshooting

### Template tidak muncul saat pengisian butir

1. Pastikan template sudah disimpan dengan benar
2. Refresh halaman pengisian butir
3. Cek console browser untuk error
4. Pastikan butir yang dipilih benar

### Form tidak bisa disimpan

1. Pastikan semua field required terisi
2. Cek format opsi untuk tipe Select (harus `key=value`)
3. Pastikan nama field tidak duplikat
4. Pastikan min_rows tidak lebih besar dari max_rows

### Template hilang setelah disimpan

1. Cek di Template Management apakah status sudah "Dengan Template"
2. Cek backend log untuk error
3. Pastikan database tidak ada constraint error

## Upgrade Plan (Future)

Untuk versi berikutnya, akan ditambahkan:

1. **Drag & Drop Builder**
   - Visual builder dengan drag & drop
   - Live preview real-time
   - Component library

2. **Template Library**
   - Template preset BAN-PT
   - Community templates
   - Import/export template

3. **Advanced Features**
   - Conditional fields
   - Formula/calculated fields
   - File upload fields
   - Lookup/relasi dengan data master

4. **Mixed Template Builder**
   - Kombinasi berbagai section
   - Flexible layout

## Files yang Dimodifikasi/Dibuat

**Backend:**
- `app/Http/Controllers/Api/ButirTemplateController.php` - Controller API
- `routes/api.php` - Route definitions

**Frontend:**
- `resources/js/composables/useButirTemplateApi.js` - API composable
- `resources/js/views/akreditasi/ButirTemplateManagement.vue` - Management page
- `resources/js/views/akreditasi/ButirTemplateBuilder.vue` - Builder page
- `resources/js/router/index.js` - Route configuration

**Documentation:**
- `docs/UI_TEMPLATE_BUILDER.md` - Panduan ini

## Support

Jika mengalami kendala atau ada pertanyaan:
1. Cek dokumentasi ini terlebih dahulu
2. Cek file `docs/MENAMBAHKAN_TEMPLATE_BUTIR.md` untuk metode alternatif
3. Gunakan console browser untuk debugging
4. Report issue dengan detail error message

---

**Version**: 1.0 (Hybrid - Simple Form Builder)
**Last Updated**: 2025-11-17
