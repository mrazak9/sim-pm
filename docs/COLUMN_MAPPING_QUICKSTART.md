# Column Mapping System - Quick Start Guide

## 🚀 Quick Start

Panduan cepat untuk menggunakan sistem column mapping c1-c30 yang baru diimplementasikan.

---

## 📋 Setup (Untuk Butir Baru)

### 1. Setup Column Mapping

Untuk butir yang membutuhkan form dinamis, setup mapping terlebih dahulu:

```php
use App\Services\ButirMappingService;

$mappingService = new ButirMappingService();

// Define fields untuk butir Anda
$fields = [
    [
        'name' => 'nama_dosen',
        'label' => 'Nama Dosen',
        'type' => 'text',
        'required' => true,
        'width' => '25%',
    ],
    [
        'name' => 'nip',
        'label' => 'NIP',
        'type' => 'text',
        'required' => true,
        'width' => '15%',
    ],
    [
        'name' => 'pendidikan',
        'label' => 'Pendidikan',
        'type' => 'select',
        'required' => true,
        'options' => [
            'S1' => 'Sarjana',
            'S2' => 'Magister',
            'S3' => 'Doktor',
        ],
    ],
    // ... tambahkan field lain (max 30)
];

// Setup mappings
$mappings = $mappingService->setupMappings($butirId, $fields);
```

### 2. Insert Data

```php
use App\Services\ButirDataService;

$dataService = new ButirDataService();

// Single row
$data = $dataService->create($pengisianButirId, [
    'nama_dosen' => 'Dr. Ahmad Santoso, M.Kom',
    'nip' => '198501012010121001',
    'pendidikan' => 'S3',
]);

// Multiple rows
$rows = [
    [
        'nama_dosen' => 'Dr. Ahmad Santoso',
        'nip' => '198501012010121001',
        'pendidikan' => 'S3',
    ],
    [
        'nama_dosen' => 'Prof. Budi Raharjo',
        'nip' => '197801012005011001',
        'pendidikan' => 'S3',
    ],
];

$created = $dataService->bulkCreate($pengisianButirId, $rows);
```

---

## 🔍 Query Data

### Simple Query

```php
$dataService = new ButirDataService();

// Get all data untuk pengisian butir
$data = $dataService->getByPengisian($pengisianButirId);

// Output: Collection of arrays with named fields
// [
//   ['id' => 1, 'nama_dosen' => 'Dr. Ahmad', 'nip' => '12345', ...],
//   ['id' => 2, 'nama_dosen' => 'Prof. Budi', 'nip' => '67890', ...],
// ]
```

### Query by Field

```php
// Cari dosen dengan pendidikan S3
$dosenS3 = $dataService->queryByField($butirId, 'pendidikan', 'S3');

// Dengan operator
$dosenBaru = $dataService->queryByField($butirId, 'tahun_masuk', '>=', 2020);
```

### Complex Query dengan Query Builder

```php
$query = $dataService->query($butirId);

$results = $query
    ->whereField('pendidikan', 'S3')
    ->whereField('jafung', 'Lektor Kepala')
    ->whereField('status', 'Aktif')
    ->orderByField('nama_dosen', 'asc')
    ->get();

// Dengan pagination
$paginated = $query
    ->whereField('pendidikan', 'S3')
    ->paginate(15);
```

### Advanced Queries

```php
// WHERE IN
$query->whereFieldIn('pendidikan', ['S2', 'S3'])->get();

// WHERE NULL
$query->whereFieldNull('email')->get();

// OR WHERE
$query
    ->whereField('pendidikan', 'S3')
    ->orWhereField('jafung', 'Guru Besar')
    ->get();

// Filter by pengisian butir
$query
    ->byPengisian($pengisianButirId)
    ->whereField('status', 'Aktif')
    ->get();
```

---

## ✏️ Update & Delete

### Update Data

```php
$dataService = new ButirDataService();

$updated = $dataService->update($butirDataId, [
    'nama_dosen' => 'Dr. Ahmad Santoso, M.Kom (Updated)',
    'email' => 'ahmad.updated@univ.ac.id',
]);
```

### Delete Data

```php
$dataService->delete($butirDataId);
```

---

## 📡 API Endpoints

### Get Data

```http
GET /api/pengisian-butirs/{pengisianButirId}/data

Response:
{
  "success": true,
  "data": [
    {
      "id": 1,
      "row_number": 1,
      "nama_dosen": "Dr. Ahmad Santoso",
      "nip": "198501012010121001",
      "pendidikan": "S3"
    }
  ]
}
```

### Create Data

```http
POST /api/pengisian-butirs/{pengisianButirId}/data

Body:
{
  "nama_dosen": "Dr. Ahmad Santoso",
  "nip": "198501012010121001",
  "pendidikan": "S3"
}
```

### Bulk Create

```http
POST /api/pengisian-butirs/{pengisianButirId}/data/bulk

Body:
{
  "rows": [
    {
      "nama_dosen": "Dr. Ahmad",
      "nip": "12345",
      "pendidikan": "S3"
    },
    {
      "nama_dosen": "Prof. Budi",
      "nip": "67890",
      "pendidikan": "S3"
    }
  ]
}
```

### Update Data

```http
PUT /api/butir-data/{id}

Body:
{
  "nama_dosen": "Dr. Ahmad (Updated)",
  "email": "ahmad.updated@univ.ac.id"
}
```

### Delete Data

```http
DELETE /api/butir-data/{id}
```

### Query with Filters

```http
POST /api/butir-data/query

Body:
{
  "butir_id": 1,
  "pengisian_butir_id": 5,
  "filters": [
    {
      "field": "pendidikan",
      "operator": "=",
      "value": "S3"
    },
    {
      "field": "tahun_masuk",
      "operator": ">=",
      "value": 2010
    }
  ],
  "order_by": "nama_dosen",
  "order_direction": "asc",
  "per_page": 15
}
```

### Export Data

```http
GET /api/pengisian-butirs/{pengisianButirId}/data/export

Response:
{
  "success": true,
  "data": [
    { "nama_dosen": "...", "nip": "..." },
    ...
  ]
}
```

---

## 🗂️ Manage Mappings

### Get Mappings

```http
GET /api/butir-akreditasis/{butirId}/mappings

Response:
{
  "success": true,
  "data": [
    {
      "id": 1,
      "field_name": "nama_dosen",
      "field_label": "Nama Dosen",
      "column_name": "c1",
      "field_type": "text",
      "display_order": 1
    }
  ]
}
```

### Setup Mappings (from form_config if exists)

```http
POST /api/butir-akreditasis/{butirId}/mappings/setup
```

### Update Mappings

```http
PUT /api/butir-akreditasis/{butirId}/mappings

Body:
{
  "fields": [
    {
      "name": "nama_dosen",
      "label": "Nama Dosen",
      "type": "text",
      "required": true
    },
    ...
  ]
}
```

---

## 💡 Tips & Best Practices

### 1. Setup Mapping Sekali Saja

Mapping hanya perlu di-setup sekali untuk setiap butir. Setelah itu, data bisa diisi berkali-kali tanpa perlu setup ulang.

### 2. Maksimal 30 Fields

Sistem mendukung maksimal 30 field per butir. Jika butuh lebih, gunakan `metadata` untuk field tambahan:

```php
$dataService->create($pengisianButirId, [
    'nama_dosen' => 'Ahmad',  // mapped to c1
    'nip' => '12345',         // mapped to c2
    // ... c3-c30
    'metadata' => [
        'custom_data' => [
            'field_31' => 'value',
            'field_32' => 'value',
        ]
    ]
]);
```

### 3. Dokumen Pendukung Per Row

Gunakan `metadata.dokumen` untuk attach dokumen per row:

```php
$dataService->create($pengisianButirId, [
    'nama_dosen' => 'Ahmad',
    'nip' => '12345',
    'dokumen' => [
        [
            'id' => 1,
            'nama' => 'SK Dosen.pdf',
            'url' => '/uploads/sk-dosen.pdf',
            'keterangan' => 'SK Pengangkatan',
        ]
    ],
]);
```

### 4. Query Performance

Field yang sering di-query (tahun, status, dll) akan otomatis cepat karena ada index pada kolom c1-c10.

Untuk field c11-c30, bisa tambahkan index manual jika perlu:

```sql
CREATE INDEX idx_butir_data_c15 ON butir_data(c15);
```

### 5. Field Types

Supported field types:
- `text` - Text input
- `number` - Number input
- `email` - Email input
- `date` - Date picker
- `select` - Dropdown dengan options
- `currency` - Input rupiah
- `richtext` - Rich text editor (untuk narrative)

---

## 🧪 Testing

### Run Seeder

```bash
php artisan db:seed --class=ButirDataDosenSeeder
```

### Query Test

```php
use App\Services\ButirDataService;

$service = new ButirDataService();

// Test simple query
$data = $service->getByPengisian(1);
dd($data);

// Test filter
$dosenS3 = $service->queryByField(1, 'pendidikan', 'S3');
dd($dosenS3);

// Test complex query
$query = $service->query(1);
$results = $query
    ->whereField('pendidikan', 'S3')
    ->whereField('status', 'Aktif')
    ->orderByField('nama_dosen')
    ->get();
dd($results);
```

---

## 📊 Database Structure

### butir_data Table

| Column | Description |
|--------|-------------|
| id | Primary key |
| pengisian_butir_id | Foreign key to pengisian_butirs |
| row_number | Row number (untuk table form) |
| c1-c30 | Data columns (mapped dynamically) |
| metadata | JSONB untuk dokumen, notes, custom data |
| created_at | Timestamp |
| updated_at | Timestamp |
| deleted_at | Soft delete |

### butir_column_mappings Table

| Column | Description |
|--------|-------------|
| id | Primary key |
| butir_akreditasi_id | Foreign key to butir_akreditasis |
| field_name | Logical field name (nama_dosen) |
| field_label | Display label (Nama Dosen) |
| column_name | Physical column (c1) |
| field_type | Field type (text, select, etc) |
| field_config | JSONB validation & options |
| display_order | Display order |

---

## 🔄 Migration from Old System

Jika Anda memiliki data lama di `form_data`, lihat migration script di:

```
database/migrations/YYYY_MM_DD_HHMMSS_migrate_form_data_to_butir_data.php
```

---

## 🆘 Troubleshooting

### Error: Field 'xxx' tidak ditemukan dalam mapping

**Solution:** Setup mapping terlebih dahulu:
```php
$mappingService->setupMappings($butirId, $fields);
```

### Error: Maksimal 30 field per butir

**Solution:** Gunakan `metadata` untuk field tambahan atau split ke multiple butir.

### Query lambat untuk field tertentu

**Solution:** Tambahkan index untuk kolom tersebut:
```sql
CREATE INDEX idx_butir_data_c15 ON butir_data(c15);
```

---

## 📚 Dokumentasi Lengkap

Untuk dokumentasi lengkap implementation, lihat:
- `docs/IMPLEMENTASI_COLUMN_MAPPING.md` - Full implementation plan
- `app/Services/ButirDataService.php` - Service source code
- `app/Models/ButirData.php` - Model source code

---

**Happy Coding! 🚀**
