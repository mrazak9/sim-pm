# 📋 Panduan Menambahkan Template ke Butir Akreditasi

Dokumen ini menjelaskan cara menambahkan dynamic form template ke butir-butir akreditasi lainnya.

---

## 🎯 **3 Cara Menambahkan Template**

### **Cara 1: Via Artisan Command (Paling Mudah) ⭐**

Command ini memudahkan Anda menambahkan template secara interaktif.

#### **a. Lihat butir yang belum punya template:**
```bash
php artisan butir:add-template --list
```

Output:
```
Butir WITHOUT templates (25):
┌────┬──────────┬─────────────────────────────────────────┐
│ ID │ Kode     │ Nama                                    │
├────┼──────────┼─────────────────────────────────────────┤
│ 5  │ A.2      │ Tata Pamong                             │
│ 8  │ B.1      │ Kerjasama Tri Dharma PT                 │
│ 12 │ C.4      │ Karya Ilmiah Mahasiswa                  │
└────┴──────────┴─────────────────────────────────────────┘

Progress: 8/33 butir have templates (24.24%)
```

#### **b. Tambahkan template ke butir tertentu:**
```bash
php artisan butir:add-template 5
```

Akan muncul prompt interaktif:
```
Butir: [A.2] Tata Pamong
Select template type:
  [0] table
  [1] narrative
  [2] checklist
  [3] metric
  [4] mixed
> 0

Use predefined template? (yes/no) [yes]:
> yes

✓ Template successfully added to butir A.2
  Type: table
  Label: Data Tata Pamong
```

#### **c. Tambahkan dengan template spesifik (skip prompt):**
```bash
php artisan butir:add-template 12 --template=table
```

---

### **Cara 2: Via Migration (Untuk Production & Team)**

Gunakan ini jika Anda ingin template di-apply secara konsisten di semua environment.

#### **Langkah 1: Buat migration baru**
```bash
php artisan make:migration add_nama_butir_template
```

#### **Langkah 2: Edit migration**

Contoh untuk **Mahasiswa** butir:

```php
<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        $formConfig = [
            'type' => 'table',
            'label' => 'Data Mahasiswa',
            'help_text' => 'Isikan data mahasiswa aktif dan lulusan',
            'columns' => [
                [
                    'name' => 'nim',
                    'label' => 'NIM',
                    'type' => 'text',
                    'required' => true,
                    'width' => '15%',
                ],
                [
                    'name' => 'nama',
                    'label' => 'Nama Mahasiswa',
                    'type' => 'text',
                    'required' => true,
                    'width' => '30%',
                ],
                [
                    'name' => 'tahun_masuk',
                    'label' => 'Tahun Masuk',
                    'type' => 'number',
                    'required' => true,
                    'width' => '10%',
                    'min' => 2015,
                    'max' => 2030
                ],
                [
                    'name' => 'status',
                    'label' => 'Status',
                    'type' => 'select',
                    'required' => true,
                    'width' => '15%',
                    'options' => [
                        'aktif' => 'Aktif',
                        'lulus' => 'Lulus',
                        'cuti' => 'Cuti',
                        'keluar' => 'Keluar/DO'
                    ]
                ],
                [
                    'name' => 'ipk',
                    'label' => 'IPK',
                    'type' => 'decimal',
                    'required' => false,
                    'width' => '10%',
                    'min' => 0,
                    'max' => 4
                ]
            ],
            'validations' => [
                'min_rows' => 1,
                'max_rows' => 500
            ],
            'options' => [
                'allow_add' => true,
                'allow_delete' => true,
                'show_summary' => true
            ]
        ];

        // Apply to butir that match
        $butirs = DB::table('butir_akreditasis')
            ->where(function($query) {
                $query->where('nama', 'ILIKE', '%Mahasiswa%')
                      ->orWhere('kode', 'ILIKE', '%B.2%');
            })
            ->get();

        foreach ($butirs as $butir) {
            $metadata = $butir->metadata ? json_decode($butir->metadata, true) : [];
            $metadata['form_config'] = $formConfig;

            DB::table('butir_akreditasis')
                ->where('id', $butir->id)
                ->update(['metadata' => json_encode($metadata)]);
        }
    }

    public function down(): void
    {
        $butirs = DB::table('butir_akreditasis')
            ->where(function($query) {
                $query->where('nama', 'ILIKE', '%Mahasiswa%');
            })
            ->get();

        foreach ($butirs as $butir) {
            $metadata = $butir->metadata ? json_decode($butir->metadata, true) : [];
            unset($metadata['form_config']);

            DB::table('butir_akreditasis')
                ->where('id', $butir->id)
                ->update(['metadata' => json_encode($metadata)]);
        }
    }
};
```

#### **Langkah 3: Jalankan migration**
```bash
php artisan migrate
```

---

### **Cara 3: Via SQL Manual (Testing & Quick Fix)**

Gunakan ini untuk testing cepat atau fix butir spesifik.

#### **Langkah 1: Cek ID butir**
```sql
SELECT id, kode, nama FROM butir_akreditasis
WHERE nama ILIKE '%kata_kunci%';
```

#### **Langkah 2: Update metadata**

**Template Table:**
```sql
UPDATE butir_akreditasis
SET metadata = jsonb_set(
    COALESCE(metadata, '{}'::jsonb),
    '{form_config}',
    '{
      "type": "table",
      "label": "Data Dosen",
      "help_text": "Isikan data dosen tetap program studi",
      "columns": [
        {
          "name": "nidn",
          "label": "NIDN",
          "type": "text",
          "required": true,
          "width": "15%"
        },
        {
          "name": "nama",
          "label": "Nama Dosen",
          "type": "text",
          "required": true,
          "width": "30%"
        },
        {
          "name": "pendidikan",
          "label": "Pendidikan",
          "type": "select",
          "required": true,
          "width": "15%",
          "options": {
            "s1": "S1",
            "s2": "S2",
            "s3": "S3"
          }
        }
      ],
      "validations": {
        "min_rows": 1,
        "max_rows": 100
      },
      "options": {
        "allow_add": true,
        "allow_delete": true,
        "show_summary": true
      }
    }'::jsonb
)
WHERE id = 42; -- GANTI DENGAN ID YANG BENAR
```

**Template Narrative:**
```sql
UPDATE butir_akreditasis
SET metadata = jsonb_set(
    COALESCE(metadata, '{}'::jsonb),
    '{form_config}',
    '{
      "type": "narrative",
      "label": "Profil Program Studi",
      "help_text": "Tuliskan profil program studi secara lengkap",
      "fields": [
        {
          "name": "sejarah",
          "label": "Sejarah",
          "type": "richtext",
          "required": true,
          "min_length": 100,
          "max_length": 5000
        },
        {
          "name": "keunggulan",
          "label": "Keunggulan",
          "type": "richtext",
          "required": true,
          "min_length": 50,
          "max_length": 3000
        }
      ],
      "validations": {
        "require_all": true
      }
    }'::jsonb
)
WHERE id = 42;
```

---

## 📊 **Tipe Template yang Tersedia**

### 1. **Table** - Multi-row data entry
**Cocok untuk:**
- Data dosen, mahasiswa, pegawai
- Penelitian, PKM, publikasi
- Kerjasama, fasilitas
- Prestasi, penghargaan

**Field types:**
- `text` - Input teks
- `number` - Angka
- `currency` - Rupiah (auto format)
- `decimal` - Angka desimal
- `percentage` - Persentase (0-100)
- `select` - Dropdown
- `date` - Tanggal

### 2. **Narrative** - Rich text sections
**Cocok untuk:**
- Visi, Misi, Tujuan
- Profil, Sejarah
- Analisis, Evaluasi
- Rencana strategis

### 3. **Checklist** - Item dengan centang
**Cocok untuk:**
- Kelengkapan dokumen
- Compliance/kepatuhan
- Standar operasional

### 4. **Metric** - Single value metrics
**Cocok untuk:**
- Rasio/angka tunggal
- Indikator kinerja
- Target dan capaian

### 5. **Mixed** - Kombinasi
**Cocok untuk:**
- Butir kompleks
- Gabungan tabel + narasi

---

## 🔧 **Customisasi Template**

### **Menambahkan Kolom Baru**

Tambahkan object baru di array `columns`:

```php
[
    'name' => 'email',           // Unique field name
    'label' => 'Email',          // Label yang ditampilkan
    'type' => 'text',            // Tipe field
    'required' => true,          // Wajib diisi?
    'width' => '20%',            // Lebar kolom
    'placeholder' => 'user@domain.com',  // Placeholder
    'max_length' => 100,         // Max karakter (optional)
    'min' => 0,                  // Min value untuk number (optional)
    'max' => 999,                // Max value untuk number (optional)
]
```

### **Menambahkan Validasi**

```php
'validations' => [
    'min_rows' => 5,          // Minimal 5 baris
    'max_rows' => 200,        // Maksimal 200 baris
]
```

### **Opsi Tambahan**

```php
'options' => [
    'allow_add' => true,      // Boleh tambah baris?
    'allow_delete' => true,   // Boleh hapus baris?
    'show_summary' => true,   // Tampilkan ringkasan?
    'allow_import' => false,  // Import dari Excel? (future)
    'allow_export' => false,  // Export ke Excel? (future)
]
```

---

## 📝 **Contoh Template Spesifik BAN-PT**

### **Dosen Tetap**
```php
'columns' => [
    ['name' => 'nidn', 'label' => 'NIDN/NIDK', 'type' => 'text', 'required' => true, 'width' => '12%'],
    ['name' => 'nama', 'label' => 'Nama Lengkap', 'type' => 'text', 'required' => true, 'width' => '25%'],
    ['name' => 'pendidikan', 'label' => 'Pendidikan', 'type' => 'select', 'required' => true, 'width' => '10%',
        'options' => ['s1' => 'S1', 's2' => 'S2', 's3' => 'S3']],
    ['name' => 'jabatan', 'label' => 'Jabatan Akademik', 'type' => 'select', 'required' => true, 'width' => '15%',
        'options' => ['asisten_ahli' => 'Asisten Ahli', 'lektor' => 'Lektor', 'lektor_kepala' => 'Lektor Kepala', 'guru_besar' => 'Guru Besar']],
    ['name' => 'bidang_keahlian', 'label' => 'Bidang Keahlian', 'type' => 'text', 'required' => true, 'width' => '20%'],
    ['name' => 'sertifikat', 'label' => 'Sertifikat Profesi', 'type' => 'text', 'required' => false, 'width' => '18%'],
]
```

### **Kurikulum**
```php
'columns' => [
    ['name' => 'kode_mk', 'label' => 'Kode MK', 'type' => 'text', 'required' => true, 'width' => '10%'],
    ['name' => 'nama_mk', 'label' => 'Nama Mata Kuliah', 'type' => 'text', 'required' => true, 'width' => '30%'],
    ['name' => 'sks', 'label' => 'SKS', 'type' => 'number', 'required' => true, 'width' => '8%', 'min' => 1, 'max' => 10],
    ['name' => 'semester', 'label' => 'Semester', 'type' => 'number', 'required' => true, 'width' => '10%', 'min' => 1, 'max' => 8],
    ['name' => 'jenis', 'label' => 'Jenis', 'type' => 'select', 'required' => true, 'width' => '12%',
        'options' => ['wajib' => 'Wajib', 'pilihan' => 'Pilihan']],
    ['name' => 'capaian_pembelajaran', 'label' => 'Capaian Pembelajaran', 'type' => 'text', 'required' => false, 'width' => '30%'],
]
```

### **Kerjasama Tri Dharma**
```php
'columns' => [
    ['name' => 'mitra', 'label' => 'Nama Lembaga Mitra', 'type' => 'text', 'required' => true, 'width' => '25%'],
    ['name' => 'jenis', 'label' => 'Jenis Kerjasama', 'type' => 'select', 'required' => true, 'width' => '15%',
        'options' => ['pendidikan' => 'Pendidikan', 'penelitian' => 'Penelitian', 'pkm' => 'PKM']],
    ['name' => 'tingkat', 'label' => 'Tingkat', 'type' => 'select', 'required' => true, 'width' => '12%',
        'options' => ['internasional' => 'Internasional', 'nasional' => 'Nasional', 'lokal' => 'Lokal']],
    ['name' => 'tahun_mulai', 'label' => 'Tahun Mulai', 'type' => 'number', 'required' => true, 'width' => '10%', 'min' => 2015, 'max' => 2030],
    ['name' => 'durasi', 'label' => 'Durasi (tahun)', 'type' => 'number', 'required' => true, 'width' => '10%', 'min' => 1, 'max' => 10],
    ['name' => 'manfaat', 'label' => 'Manfaat', 'type' => 'text', 'required' => false, 'width' => '28%'],
]
```

---

## ✅ **Checklist Menambahkan Template**

- [ ] Tentukan butir mana yang perlu template
- [ ] Pilih tipe template yang sesuai (table/narrative/dll)
- [ ] Desain kolom/field yang dibutuhkan
- [ ] Pilih cara implementasi (artisan/migration/SQL)
- [ ] Testing di development environment
- [ ] Jalankan migration di production (jika via migration)
- [ ] Verifikasi template muncul di UI
- [ ] Test input & save data

---

## 🆘 **Troubleshooting**

### **Template tidak muncul di dropdown**
- Cek browser console untuk debug log
- Pastikan `metadata.form_config` ada di database
- Refresh halaman

### **Migration error: "butir not found"**
- Pattern ILIKE tidak match dengan nama butir
- Gunakan SQL manual dengan ID spesifik

### **Form tidak render**
- Cek structure `form_config` sesuai dengan dokumentasi
- Pastikan `type` field valid (table/narrative/dll)
- Lihat error di browser console

---

## 📚 **Referensi**

- [DYNAMIC_FORM_IMPLEMENTATION_PLAN.md](../DYNAMIC_FORM_IMPLEMENTATION_PLAN.md) - Dokumentasi lengkap sistem
- [manual_add_template_to_butir.sql](../database/manual_add_template_to_butir.sql) - SQL manual reference

---

Dibuat: 2025-11-17
Update terakhir: 2025-11-17
