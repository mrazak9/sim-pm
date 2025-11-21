# Implementasi Column Mapping System (C1-C30)

## 📋 Overview

Dokumen ini berisi perencanaan lengkap untuk implementasi sistem column mapping dengan pendekatan **Pure Hybrid**: menggunakan kolom fisik `c1-c30` untuk performa query optimal, dengan dynamic mapping table untuk maintainability.

### Tujuan

- ✅ Query performance optimal (indexable columns)
- ✅ Maintainability tinggi (dynamic mapping, tidak hardcore)
- ✅ Mendukung hingga 30 field per butir
- ✅ Support dokumen pendukung per row
- ✅ Mudah untuk filter, aggregate, dan reporting

### Teknologi

- Database: PostgreSQL (sudah support JSONB untuk metadata)
- Framework: Laravel 10+
- Pattern: Repository + Service Layer

---

## 🏗️ Struktur Database

### 1. Tabel `butir_data` (Data Utama)

Menyimpan data pengisian butir dengan kolom c1-c30 untuk performa query optimal.

```sql
CREATE TABLE butir_data (
    id BIGSERIAL PRIMARY KEY,
    pengisian_butir_id BIGINT NOT NULL,
    row_number INT DEFAULT 1,

    -- 30 kolom untuk data (TEXT untuk fleksibilitas)
    c1 TEXT,
    c2 TEXT,
    c3 TEXT,
    c4 TEXT,
    c5 TEXT,
    c6 TEXT,
    c7 TEXT,
    c8 TEXT,
    c9 TEXT,
    c10 TEXT,
    c11 TEXT,
    c12 TEXT,
    c13 TEXT,
    c14 TEXT,
    c15 TEXT,
    c16 TEXT,
    c17 TEXT,
    c18 TEXT,
    c19 TEXT,
    c20 TEXT,
    c21 TEXT,
    c22 TEXT,
    c23 TEXT,
    c24 TEXT,
    c25 TEXT,
    c26 TEXT,
    c27 TEXT,
    c28 TEXT,
    c29 TEXT,
    c30 TEXT,

    -- Metadata untuk dokumen pendukung, notes, dll (JSONB)
    metadata JSONB,

    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    deleted_at TIMESTAMP NULL,

    FOREIGN KEY (pengisian_butir_id) REFERENCES pengisian_butirs(id) ON DELETE CASCADE
);

-- Indexes untuk performa query
CREATE INDEX idx_butir_data_pengisian_id ON butir_data(pengisian_butir_id);
CREATE INDEX idx_butir_data_row_number ON butir_data(row_number);

-- Index untuk 10 kolom pertama (yang paling sering di-query)
CREATE INDEX idx_butir_data_c1 ON butir_data(c1);
CREATE INDEX idx_butir_data_c2 ON butir_data(c2);
CREATE INDEX idx_butir_data_c3 ON butir_data(c3);
CREATE INDEX idx_butir_data_c4 ON butir_data(c4);
CREATE INDEX idx_butir_data_c5 ON butir_data(c5);
CREATE INDEX idx_butir_data_c6 ON butir_data(c6);
CREATE INDEX idx_butir_data_c7 ON butir_data(c7);
CREATE INDEX idx_butir_data_c8 ON butir_data(c8);
CREATE INDEX idx_butir_data_c9 ON butir_data(c9);
CREATE INDEX idx_butir_data_c10 ON butir_data(c10);

-- GIN index untuk metadata JSONB
CREATE INDEX idx_butir_data_metadata ON butir_data USING GIN (metadata);

-- Soft deletes index
CREATE INDEX idx_butir_data_deleted_at ON butir_data(deleted_at);
```

**Struktur metadata JSONB:**
```json
{
  "dokumen": [
    {
      "id": 1,
      "nama": "Sertifikat.pdf",
      "url": "/uploads/sertifikat.pdf",
      "ukuran": 1024000,
      "keterangan": "Sertifikat dosen"
    }
  ],
  "notes": "Catatan tambahan untuk row ini",
  "custom_data": {
    "field_tambahan_1": "value",
    "field_tambahan_2": "value"
  }
}
```

---

### 2. Tabel `butir_column_mappings` (Mapping Table)

Menyimpan mapping antara field_name (logical) dengan column_name (physical c1-c30).

```sql
CREATE TABLE butir_column_mappings (
    id BIGSERIAL PRIMARY KEY,
    butir_akreditasi_id BIGINT NOT NULL,

    -- Field information
    field_name VARCHAR(100) NOT NULL,          -- 'nama_dosen', 'nip', dst
    field_label VARCHAR(200) NOT NULL,         -- 'Nama Dosen', 'NIP', dst
    column_name VARCHAR(10) NOT NULL,          -- 'c1', 'c2', 'c3', dst

    -- Field type & validation
    field_type VARCHAR(50) NOT NULL,           -- 'text', 'number', 'date', 'select', 'currency', 'email', dll
    field_config JSONB,                        -- Configuration: options, min, max, regex, dll

    -- Display & ordering
    display_order INT NOT NULL,                -- Urutan tampilan (1, 2, 3, ...)
    width VARCHAR(20),                         -- Width untuk tabel (20%, 15%, auto, dll)

    -- Validation
    is_required BOOLEAN DEFAULT false,

    -- Metadata
    help_text TEXT,                            -- Help text untuk user
    placeholder VARCHAR(255),                  -- Placeholder input

    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,

    FOREIGN KEY (butir_akreditasi_id) REFERENCES butir_akreditasis(id) ON DELETE CASCADE,

    -- Constraints untuk mencegah duplikasi
    UNIQUE(butir_akreditasi_id, field_name),
    UNIQUE(butir_akreditasi_id, column_name),
    UNIQUE(butir_akreditasi_id, display_order)
);

-- Indexes
CREATE INDEX idx_mappings_butir_id ON butir_column_mappings(butir_akreditasi_id);
CREATE INDEX idx_mappings_field_name ON butir_column_mappings(field_name);
CREATE INDEX idx_mappings_column_name ON butir_column_mappings(column_name);
```

**Struktur field_config JSONB:**
```json
{
  "type": "select",
  "options": {
    "S1": "Sarjana (S1)",
    "S2": "Magister (S2)",
    "S3": "Doktor (S3)"
  },
  "validation": {
    "min": 0,
    "max": 100,
    "regex": "^[0-9]+$",
    "min_length": 5,
    "max_length": 500
  },
  "display": {
    "prefix": "Rp ",
    "suffix": " tahun",
    "format": "number"
  }
}
```

---

## 🔄 Migration Plan

### Phase 1: Create Tables

**File:** `database/migrations/YYYY_MM_DD_HHMMSS_create_butir_data_and_mappings_tables.php`

```php
<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // 1. Create butir_column_mappings table
        Schema::create('butir_column_mappings', function (Blueprint $table) {
            $table->id();
            $table->foreignId('butir_akreditasi_id')
                ->constrained('butir_akreditasis')
                ->onDelete('cascade');

            // Field information
            $table->string('field_name', 100);
            $table->string('field_label', 200);
            $table->string('column_name', 10);

            // Type & config
            $table->string('field_type', 50);
            $table->jsonb('field_config')->nullable();

            // Display
            $table->integer('display_order');
            $table->string('width', 20)->nullable();

            // Validation
            $table->boolean('is_required')->default(false);

            // Metadata
            $table->text('help_text')->nullable();
            $table->string('placeholder')->nullable();

            $table->timestamps();

            // Unique constraints
            $table->unique(['butir_akreditasi_id', 'field_name']);
            $table->unique(['butir_akreditasi_id', 'column_name']);
            $table->unique(['butir_akreditasi_id', 'display_order']);

            // Indexes
            $table->index('field_name');
            $table->index('column_name');
        });

        // 2. Create butir_data table
        Schema::create('butir_data', function (Blueprint $table) {
            $table->id();
            $table->foreignId('pengisian_butir_id')
                ->constrained('pengisian_butirs')
                ->onDelete('cascade');
            $table->integer('row_number')->default(1);

            // 30 columns for data
            for ($i = 1; $i <= 30; $i++) {
                $table->text("c{$i}")->nullable();
            }

            // Metadata for documents, notes, etc
            $table->jsonb('metadata')->nullable();

            $table->timestamps();
            $table->softDeletes();

            // Indexes
            $table->index('pengisian_butir_id');
            $table->index('row_number');

            // Index first 10 columns (most frequently queried)
            for ($i = 1; $i <= 10; $i++) {
                $table->index("c{$i}");
            }
        });

        // GIN index for metadata
        DB::statement('CREATE INDEX idx_butir_data_metadata ON butir_data USING GIN (metadata)');
    }

    public function down(): void
    {
        Schema::dropIfExists('butir_data');
        Schema::dropIfExists('butir_column_mappings');
    }
};
```

### Phase 2: Migrate Existing Data (Optional)

Jika sudah ada data di `pengisian_butirs.form_data`, migrate ke struktur baru:

**File:** `database/migrations/YYYY_MM_DD_HHMMSS_migrate_form_data_to_butir_data.php`

```php
<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        // Get all pengisian_butirs with form_data
        $pengisians = DB::table('pengisian_butirs')
            ->whereNotNull('form_data')
            ->get();

        foreach ($pengisians as $pengisian) {
            $formData = json_decode($pengisian->form_data, true);

            if (empty($formData)) continue;

            // Get butir
            $butir = DB::table('butir_akreditasis')
                ->find($pengisian->butir_akreditasi_id);

            if (!$butir) continue;

            // Get form config
            $metadata = json_decode($butir->metadata, true);
            $formConfig = $metadata['form_config'] ?? null;

            if (!$formConfig) continue;

            // Process based on form type
            if ($formConfig['type'] === 'table') {
                $this->migrateTableForm($pengisian, $formConfig, $formData);
            } elseif ($formConfig['type'] === 'narrative') {
                $this->migrateNarrativeForm($pengisian, $formConfig, $formData);
            }
        }
    }

    protected function migrateTableForm($pengisian, $formConfig, $formData)
    {
        // Create mappings from form_config
        $columns = $formConfig['columns'] ?? [];
        $mappings = [];

        foreach ($columns as $index => $column) {
            $columnNumber = $index + 1;
            if ($columnNumber > 30) break; // Max 30 columns

            DB::table('butir_column_mappings')->insert([
                'butir_akreditasi_id' => $pengisian->butir_akreditasi_id,
                'field_name' => $column['name'],
                'field_label' => $column['label'],
                'column_name' => "c{$columnNumber}",
                'field_type' => $column['type'] ?? 'text',
                'field_config' => json_encode($column),
                'display_order' => $columnNumber,
                'is_required' => $column['required'] ?? false,
                'width' => $column['width'] ?? null,
                'created_at' => now(),
                'updated_at' => now(),
            ]);

            $mappings[$column['name']] = "c{$columnNumber}";
        }

        // Migrate data rows
        foreach ($formData as $rowIndex => $row) {
            $dataRow = [
                'pengisian_butir_id' => $pengisian->id,
                'row_number' => $rowIndex + 1,
                'created_at' => now(),
                'updated_at' => now(),
            ];

            // Map fields to columns
            foreach ($row as $fieldName => $value) {
                if (isset($mappings[$fieldName])) {
                    $dataRow[$mappings[$fieldName]] = $value;
                }
            }

            DB::table('butir_data')->insert($dataRow);
        }
    }

    protected function migrateNarrativeForm($pengisian, $formConfig, $formData)
    {
        // Similar to table form but single row
        // ... implementation
    }

    public function down(): void
    {
        // Cannot reverse this migration safely
    }
};
```

---

## 📦 Model Implementation

### 1. Model `ButirData`

**File:** `app/Models/ButirData.php`

```php
<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class ButirData extends Model
{
    use HasFactory, SoftDeletes;

    protected $table = 'butir_data';

    protected $fillable = [
        'pengisian_butir_id',
        'row_number',
        'c1', 'c2', 'c3', 'c4', 'c5',
        'c6', 'c7', 'c8', 'c9', 'c10',
        'c11', 'c12', 'c13', 'c14', 'c15',
        'c16', 'c17', 'c18', 'c19', 'c20',
        'c21', 'c22', 'c23', 'c24', 'c25',
        'c26', 'c27', 'c28', 'c29', 'c30',
        'metadata',
    ];

    protected $casts = [
        'metadata' => 'array',
        'row_number' => 'integer',
    ];

    /**
     * Relationships
     */
    public function pengisianButir()
    {
        return $this->belongsTo(PengisianButir::class);
    }

    /**
     * Get column mappings for this butir
     */
    public function getMappings()
    {
        static $cache = [];

        $butirId = $this->pengisianButir->butir_akreditasi_id;

        if (!isset($cache[$butirId])) {
            $cache[$butirId] = ButirColumnMapping::where('butir_akreditasi_id', $butirId)
                ->orderBy('display_order')
                ->get();
        }

        return $cache[$butirId];
    }

    /**
     * Transform c1-c30 to named fields
     *
     * @return array
     */
    public function toNamedFields(): array
    {
        $mappings = $this->getMappings();

        $result = [
            'id' => $this->id,
            'row_number' => $this->row_number,
        ];

        foreach ($mappings as $mapping) {
            $columnName = $mapping->column_name;
            $fieldName = $mapping->field_name;

            $result[$fieldName] = $this->$columnName;
        }

        // Add metadata
        if ($this->metadata) {
            $result['dokumen'] = $this->metadata['dokumen'] ?? [];
            $result['notes'] = $this->metadata['notes'] ?? null;
            $result['custom_data'] = $this->metadata['custom_data'] ?? [];
        }

        return $result;
    }

    /**
     * Create from named fields
     *
     * @param array $fields
     * @param int $pengisianButirId
     * @return self
     */
    public static function fromNamedFields(array $fields, int $pengisianButirId): self
    {
        $pengisian = PengisianButir::findOrFail($pengisianButirId);

        $mappings = ButirColumnMapping::where('butir_akreditasi_id', $pengisian->butir_akreditasi_id)
            ->get()
            ->keyBy('field_name');

        $data = [
            'pengisian_butir_id' => $pengisianButirId,
            'row_number' => $fields['row_number'] ?? 1,
        ];

        // Map field names to column names
        foreach ($fields as $fieldName => $value) {
            if (isset($mappings[$fieldName])) {
                $columnName = $mappings[$fieldName]->column_name;
                $data[$columnName] = $value;
            }
        }

        // Handle metadata
        $metadata = [];
        if (isset($fields['dokumen'])) {
            $metadata['dokumen'] = $fields['dokumen'];
        }
        if (isset($fields['notes'])) {
            $metadata['notes'] = $fields['notes'];
        }
        if (isset($fields['custom_data'])) {
            $metadata['custom_data'] = $fields['custom_data'];
        }

        if (!empty($metadata)) {
            $data['metadata'] = $metadata;
        }

        return self::create($data);
    }

    /**
     * Update from named fields
     *
     * @param array $fields
     * @return bool
     */
    public function updateFromNamedFields(array $fields): bool
    {
        $mappings = $this->getMappings()->keyBy('field_name');

        $data = [];

        // Map field names to column names
        foreach ($fields as $fieldName => $value) {
            if (isset($mappings[$fieldName])) {
                $columnName = $mappings[$fieldName]->column_name;
                $data[$columnName] = $value;
            }
        }

        // Handle metadata updates
        if (isset($fields['dokumen']) || isset($fields['notes']) || isset($fields['custom_data'])) {
            $currentMetadata = $this->metadata ?? [];

            if (isset($fields['dokumen'])) {
                $currentMetadata['dokumen'] = $fields['dokumen'];
            }
            if (isset($fields['notes'])) {
                $currentMetadata['notes'] = $fields['notes'];
            }
            if (isset($fields['custom_data'])) {
                $currentMetadata['custom_data'] = $fields['custom_data'];
            }

            $data['metadata'] = $currentMetadata;
        }

        return $this->update($data);
    }

    /**
     * Scopes
     */
    public function scopeByPengisian($query, int $pengisianId)
    {
        return $query->where('pengisian_butir_id', $pengisianId);
    }
}
```

### 2. Model `ButirColumnMapping`

**File:** `app/Models/ButirColumnMapping.php`

```php
<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ButirColumnMapping extends Model
{
    use HasFactory;

    protected $fillable = [
        'butir_akreditasi_id',
        'field_name',
        'field_label',
        'column_name',
        'field_type',
        'field_config',
        'display_order',
        'width',
        'is_required',
        'help_text',
        'placeholder',
    ];

    protected $casts = [
        'field_config' => 'array',
        'is_required' => 'boolean',
        'display_order' => 'integer',
    ];

    /**
     * Relationships
     */
    public function butirAkreditasi()
    {
        return $this->belongsTo(ButirAkreditasi::class);
    }

    /**
     * Get mappings by butir ID
     */
    public static function getByButirId(int $butirId)
    {
        return static::where('butir_akreditasi_id', $butirId)
            ->orderBy('display_order')
            ->get();
    }

    /**
     * Get mapping by field name
     */
    public static function getByFieldName(int $butirId, string $fieldName)
    {
        return static::where('butir_akreditasi_id', $butirId)
            ->where('field_name', $fieldName)
            ->first();
    }

    /**
     * Get next available column
     */
    public static function getNextAvailableColumn(int $butirId): ?string
    {
        $usedColumns = static::where('butir_akreditasi_id', $butirId)
            ->pluck('column_name')
            ->toArray();

        for ($i = 1; $i <= 30; $i++) {
            $column = "c{$i}";
            if (!in_array($column, $usedColumns)) {
                return $column;
            }
        }

        return null; // All columns used
    }

    /**
     * Scopes
     */
    public function scopeByButir($query, int $butirId)
    {
        return $query->where('butir_akreditasi_id', $butirId);
    }

    public function scopeOrdered($query)
    {
        return $query->orderBy('display_order');
    }
}
```

### 3. Update Model `ButirAkreditasi`

Tambahkan relationship ke mapping:

```php
// app/Models/ButirAkreditasi.php

public function columnMappings()
{
    return $this->hasMany(ButirColumnMapping::class)->orderBy('display_order');
}

public function butirData()
{
    return $this->hasManyThrough(
        ButirData::class,
        PengisianButir::class,
        'butir_akreditasi_id',
        'pengisian_butir_id'
    );
}
```

### 4. Update Model `PengisianButir`

Tambahkan relationship:

```php
// app/Models/PengisianButir.php

public function butirData()
{
    return $this->hasMany(ButirData::class, 'pengisian_butir_id');
}
```

---

## 🔧 Service Layer Implementation

### 1. Service `ButirMappingService`

**File:** `app/Services/ButirMappingService.php`

```php
<?php

namespace App\Services;

use App\Models\ButirAkreditasi;
use App\Models\ButirColumnMapping;
use Illuminate\Support\Facades\DB;

class ButirMappingService
{
    /**
     * Setup column mappings for a butir from form config
     *
     * @param int $butirId
     * @return array Created mappings
     */
    public function setupFromFormConfig(int $butirId): array
    {
        $butir = ButirAkreditasi::findOrFail($butirId);
        $formConfig = $butir->metadata['form_config'] ?? null;

        if (!$formConfig) {
            throw new \Exception("Butir tidak memiliki form_config");
        }

        // Get fields from config
        $fields = $formConfig['columns'] ?? $formConfig['fields'] ?? [];

        return $this->setupMappings($butirId, $fields);
    }

    /**
     * Setup column mappings manually
     *
     * @param int $butirId
     * @param array $fields
     * @return array Created mappings
     */
    public function setupMappings(int $butirId, array $fields): array
    {
        DB::beginTransaction();

        try {
            $mappings = [];
            $columnIndex = 1;

            foreach ($fields as $field) {
                if ($columnIndex > 30) {
                    throw new \Exception("Maksimal 30 field per butir");
                }

                $mapping = ButirColumnMapping::create([
                    'butir_akreditasi_id' => $butirId,
                    'field_name' => $field['name'],
                    'field_label' => $field['label'] ?? ucfirst($field['name']),
                    'column_name' => "c{$columnIndex}",
                    'field_type' => $field['type'] ?? 'text',
                    'field_config' => $this->buildFieldConfig($field),
                    'display_order' => $columnIndex,
                    'width' => $field['width'] ?? null,
                    'is_required' => $field['required'] ?? false,
                    'help_text' => $field['help_text'] ?? null,
                    'placeholder' => $field['placeholder'] ?? null,
                ]);

                $mappings[] = $mapping;
                $columnIndex++;
            }

            DB::commit();

            return $mappings;

        } catch (\Exception $e) {
            DB::rollBack();
            throw $e;
        }
    }

    /**
     * Update existing mappings
     *
     * @param int $butirId
     * @param array $fields
     * @return array
     */
    public function updateMappings(int $butirId, array $fields): array
    {
        DB::beginTransaction();

        try {
            // Delete existing mappings
            ButirColumnMapping::where('butir_akreditasi_id', $butirId)->delete();

            // Create new mappings
            $mappings = $this->setupMappings($butirId, $fields);

            DB::commit();

            return $mappings;

        } catch (\Exception $e) {
            DB::rollBack();
            throw $e;
        }
    }

    /**
     * Build field config from field definition
     *
     * @param array $field
     * @return array
     */
    protected function buildFieldConfig(array $field): array
    {
        $config = [];

        // Add validation rules
        if (isset($field['min'])) $config['validation']['min'] = $field['min'];
        if (isset($field['max'])) $config['validation']['max'] = $field['max'];
        if (isset($field['min_length'])) $config['validation']['min_length'] = $field['min_length'];
        if (isset($field['max_length'])) $config['validation']['max_length'] = $field['max_length'];
        if (isset($field['regex'])) $config['validation']['regex'] = $field['regex'];

        // Add options for select/radio
        if (isset($field['options'])) {
            $config['options'] = $field['options'];
        }

        // Add display config
        if (isset($field['prefix'])) $config['display']['prefix'] = $field['prefix'];
        if (isset($field['suffix'])) $config['display']['suffix'] = $field['suffix'];
        if (isset($field['format'])) $config['display']['format'] = $field['format'];

        return $config;
    }

    /**
     * Get mappings as associative array (field_name => column_name)
     *
     * @param int $butirId
     * @return array
     */
    public function getMappingsDictionary(int $butirId): array
    {
        return ButirColumnMapping::where('butir_akreditasi_id', $butirId)
            ->pluck('column_name', 'field_name')
            ->toArray();
    }

    /**
     * Get reverse mappings (column_name => field_name)
     *
     * @param int $butirId
     * @return array
     */
    public function getReverseMappingsDictionary(int $butirId): array
    {
        return ButirColumnMapping::where('butir_akreditasi_id', $butirId)
            ->pluck('field_name', 'column_name')
            ->toArray();
    }
}
```

### 2. Service `ButirDataService`

**File:** `app/Services/ButirDataService.php`

```php
<?php

namespace App\Services;

use App\Models\ButirData;
use App\Models\ButirColumnMapping;
use App\Models\PengisianButir;
use Illuminate\Support\Facades\DB;

class ButirDataService
{
    /**
     * Create single row data
     *
     * @param int $pengisianButirId
     * @param array $data Named fields
     * @return ButirData
     */
    public function create(int $pengisianButirId, array $data): ButirData
    {
        return ButirData::fromNamedFields($data, $pengisianButirId);
    }

    /**
     * Update single row data
     *
     * @param int $id
     * @param array $data Named fields
     * @return ButirData
     */
    public function update(int $id, array $data): ButirData
    {
        $butirData = ButirData::findOrFail($id);
        $butirData->updateFromNamedFields($data);

        return $butirData->fresh();
    }

    /**
     * Delete row
     *
     * @param int $id
     * @return bool
     */
    public function delete(int $id): bool
    {
        return ButirData::findOrFail($id)->delete();
    }

    /**
     * Bulk create rows
     *
     * @param int $pengisianButirId
     * @param array $rows Array of named fields
     * @return array Created ButirData instances
     */
    public function bulkCreate(int $pengisianButirId, array $rows): array
    {
        DB::beginTransaction();

        try {
            $created = [];

            foreach ($rows as $index => $row) {
                $row['row_number'] = $row['row_number'] ?? ($index + 1);
                $created[] = $this->create($pengisianButirId, $row);
            }

            DB::commit();

            return $created;

        } catch (\Exception $e) {
            DB::rollBack();
            throw $e;
        }
    }

    /**
     * Get all data for a pengisian butir
     *
     * @param int $pengisianButirId
     * @param bool $asNamedFields Transform to named fields
     * @return \Illuminate\Database\Eloquent\Collection
     */
    public function getByPengisian(int $pengisianButirId, bool $asNamedFields = true)
    {
        $data = ButirData::byPengisian($pengisianButirId)
            ->orderBy('row_number')
            ->get();

        if ($asNamedFields) {
            return $data->map->toNamedFields();
        }

        return $data;
    }

    /**
     * Query by field name
     *
     * @param int $butirId
     * @param string $fieldName
     * @param mixed $operator
     * @param mixed $value
     * @return \Illuminate\Database\Eloquent\Collection
     */
    public function queryByField(int $butirId, string $fieldName, $operator, $value = null)
    {
        // Get column mapping
        $mapping = ButirColumnMapping::getByFieldName($butirId, $fieldName);

        if (!$mapping) {
            throw new \Exception("Field '{$fieldName}' tidak ditemukan dalam mapping");
        }

        // Support both 3 and 4 parameters
        if (func_num_args() === 3) {
            $value = $operator;
            $operator = '=';
        }

        // Query using column name
        return ButirData::whereHas('pengisianButir', function($q) use ($butirId) {
            $q->where('butir_akreditasi_id', $butirId);
        })
        ->where($mapping->column_name, $operator, $value)
        ->get()
        ->map->toNamedFields();
    }

    /**
     * Get query builder for complex queries
     *
     * @param int $butirId
     * @return ButirDataQueryBuilder
     */
    public function query(int $butirId): ButirDataQueryBuilder
    {
        return new ButirDataQueryBuilder($butirId);
    }

    /**
     * Import data from array
     *
     * @param int $pengisianButirId
     * @param array $rows
     * @return array
     */
    public function import(int $pengisianButirId, array $rows): array
    {
        return $this->bulkCreate($pengisianButirId, $rows);
    }

    /**
     * Export data to array
     *
     * @param int $pengisianButirId
     * @return array
     */
    public function export(int $pengisianButirId): array
    {
        return $this->getByPengisian($pengisianButirId, true)->toArray();
    }
}
```

### 3. Query Builder `ButirDataQueryBuilder`

**File:** `app/Services/ButirDataQueryBuilder.php`

```php
<?php

namespace App\Services;

use App\Models\ButirData;
use App\Models\ButirColumnMapping;
use Illuminate\Database\Eloquent\Builder;

class ButirDataQueryBuilder
{
    protected int $butirId;
    protected Builder $query;
    protected $mappings;
    protected bool $returnNamedFields = true;

    public function __construct(int $butirId)
    {
        $this->butirId = $butirId;
        $this->query = ButirData::query();
        $this->loadMappings();
    }

    protected function loadMappings()
    {
        $this->mappings = ButirColumnMapping::where('butir_akreditasi_id', $this->butirId)
            ->get()
            ->keyBy('field_name');
    }

    /**
     * Add WHERE clause by field name
     */
    public function whereField(string $fieldName, $operator, $value = null)
    {
        $mapping = $this->mappings[$fieldName] ?? null;

        if (!$mapping) {
            throw new \Exception("Field '{$fieldName}' tidak ditemukan dalam mapping");
        }

        if (func_num_args() === 2) {
            $value = $operator;
            $operator = '=';
        }

        $this->query->where($mapping->column_name, $operator, $value);

        return $this;
    }

    /**
     * Add OR WHERE clause by field name
     */
    public function orWhereField(string $fieldName, $operator, $value = null)
    {
        $mapping = $this->mappings[$fieldName] ?? null;

        if (!$mapping) {
            throw new \Exception("Field '{$fieldName}' tidak ditemukan dalam mapping");
        }

        if (func_num_args() === 2) {
            $value = $operator;
            $operator = '=';
        }

        $this->query->orWhere($mapping->column_name, $operator, $value);

        return $this;
    }

    /**
     * Add WHERE IN clause by field name
     */
    public function whereFieldIn(string $fieldName, array $values)
    {
        $mapping = $this->mappings[$fieldName] ?? null;

        if (!$mapping) {
            throw new \Exception("Field '{$fieldName}' tidak ditemukan dalam mapping");
        }

        $this->query->whereIn($mapping->column_name, $values);

        return $this;
    }

    /**
     * Add WHERE NULL clause by field name
     */
    public function whereFieldNull(string $fieldName)
    {
        $mapping = $this->mappings[$fieldName] ?? null;

        if (!$mapping) {
            throw new \Exception("Field '{$fieldName}' tidak ditemukan dalam mapping");
        }

        $this->query->whereNull($mapping->column_name);

        return $this;
    }

    /**
     * Add ORDER BY clause by field name
     */
    public function orderByField(string $fieldName, string $direction = 'asc')
    {
        $mapping = $this->mappings[$fieldName] ?? null;

        if (!$mapping) {
            throw new \Exception("Field '{$fieldName}' tidak ditemukan dalam mapping");
        }

        $this->query->orderBy($mapping->column_name, $direction);

        return $this;
    }

    /**
     * Filter by pengisian butir
     */
    public function byPengisian(int $pengisianButirId)
    {
        $this->query->where('pengisian_butir_id', $pengisianButirId);

        return $this;
    }

    /**
     * Set whether to return named fields (default: true)
     */
    public function asRawColumns()
    {
        $this->returnNamedFields = false;

        return $this;
    }

    /**
     * Get results
     */
    public function get()
    {
        $results = $this->query->get();

        if ($this->returnNamedFields) {
            return $results->map->toNamedFields();
        }

        return $results;
    }

    /**
     * Get first result
     */
    public function first()
    {
        $result = $this->query->first();

        if ($result && $this->returnNamedFields) {
            return $result->toNamedFields();
        }

        return $result;
    }

    /**
     * Get paginated results
     */
    public function paginate($perPage = 15)
    {
        $paginator = $this->query->paginate($perPage);

        if ($this->returnNamedFields) {
            $paginator->through(fn($item) => $item->toNamedFields());
        }

        return $paginator;
    }

    /**
     * Count results
     */
    public function count()
    {
        return $this->query->count();
    }

    /**
     * Get underlying query builder
     */
    public function getQuery(): Builder
    {
        return $this->query;
    }
}
```

---

## 📡 API Endpoints

### 1. Controller `ButirDataController`

**File:** `app/Http/Controllers/Api/ButirDataController.php`

```php
<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Services\ButirDataService;
use App\Http\Requests\ButirData\StoreButirDataRequest;
use App\Http\Requests\ButirData\UpdateButirDataRequest;
use Illuminate\Http\Request;

class ButirDataController extends Controller
{
    protected ButirDataService $service;

    public function __construct(ButirDataService $service)
    {
        $this->service = $service;
    }

    /**
     * Get all data for a pengisian butir
     * GET /api/pengisian-butirs/{pengisianButirId}/data
     */
    public function index(int $pengisianButirId)
    {
        $data = $this->service->getByPengisian($pengisianButirId);

        return response()->json([
            'success' => true,
            'data' => $data,
        ]);
    }

    /**
     * Create new row
     * POST /api/pengisian-butirs/{pengisianButirId}/data
     */
    public function store(StoreButirDataRequest $request, int $pengisianButirId)
    {
        $data = $this->service->create($pengisianButirId, $request->validated());

        return response()->json([
            'success' => true,
            'message' => 'Data berhasil ditambahkan',
            'data' => $data->toNamedFields(),
        ], 201);
    }

    /**
     * Update row
     * PUT /api/butir-data/{id}
     */
    public function update(UpdateButirDataRequest $request, int $id)
    {
        $data = $this->service->update($id, $request->validated());

        return response()->json([
            'success' => true,
            'message' => 'Data berhasil diupdate',
            'data' => $data->toNamedFields(),
        ]);
    }

    /**
     * Delete row
     * DELETE /api/butir-data/{id}
     */
    public function destroy(int $id)
    {
        $this->service->delete($id);

        return response()->json([
            'success' => true,
            'message' => 'Data berhasil dihapus',
        ]);
    }

    /**
     * Bulk create
     * POST /api/pengisian-butirs/{pengisianButirId}/data/bulk
     */
    public function bulkStore(Request $request, int $pengisianButirId)
    {
        $request->validate([
            'rows' => 'required|array',
            'rows.*' => 'required|array',
        ]);

        $data = $this->service->bulkCreate($pengisianButirId, $request->rows);

        return response()->json([
            'success' => true,
            'message' => count($data) . ' data berhasil ditambahkan',
            'data' => collect($data)->map->toNamedFields(),
        ], 201);
    }

    /**
     * Import from file (CSV/Excel)
     * POST /api/pengisian-butirs/{pengisianButirId}/data/import
     */
    public function import(Request $request, int $pengisianButirId)
    {
        $request->validate([
            'file' => 'required|file|mimes:csv,xlsx',
        ]);

        // TODO: Parse file and import
        // This would use a dedicated import service

        return response()->json([
            'success' => true,
            'message' => 'Import berhasil',
        ]);
    }

    /**
     * Export to file (CSV/Excel)
     * GET /api/pengisian-butirs/{pengisianButirId}/data/export
     */
    public function export(Request $request, int $pengisianButirId)
    {
        $format = $request->get('format', 'xlsx');

        $data = $this->service->export($pengisianButirId);

        // TODO: Generate file using Laravel Excel or similar

        return response()->json([
            'success' => true,
            'data' => $data,
        ]);
    }

    /**
     * Query data with filters
     * POST /api/butir-data/query
     */
    public function query(Request $request)
    {
        $request->validate([
            'butir_id' => 'required|integer',
            'filters' => 'array',
            'filters.*.field' => 'required|string',
            'filters.*.operator' => 'required|string',
            'filters.*.value' => 'required',
        ]);

        $query = $this->service->query($request->butir_id);

        foreach ($request->filters ?? [] as $filter) {
            $query->whereField($filter['field'], $filter['operator'], $filter['value']);
        }

        if ($request->has('order_by')) {
            $query->orderByField($request->order_by, $request->get('order_direction', 'asc'));
        }

        $data = $query->paginate($request->get('per_page', 15));

        return response()->json([
            'success' => true,
            'data' => $data,
        ]);
    }
}
```

### 2. Controller `ButirMappingController`

**File:** `app/Http/Controllers/Api/ButirMappingController.php`

```php
<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Services\ButirMappingService;
use App\Models\ButirColumnMapping;
use Illuminate\Http\Request;

class ButirMappingController extends Controller
{
    protected ButirMappingService $service;

    public function __construct(ButirMappingService $service)
    {
        $this->service = $service;
    }

    /**
     * Get mappings for a butir
     * GET /api/butir-akreditasis/{butirId}/mappings
     */
    public function index(int $butirId)
    {
        $mappings = ButirColumnMapping::getByButirId($butirId);

        return response()->json([
            'success' => true,
            'data' => $mappings,
        ]);
    }

    /**
     * Setup mappings from form config
     * POST /api/butir-akreditasis/{butirId}/mappings/setup
     */
    public function setupFromFormConfig(int $butirId)
    {
        try {
            $mappings = $this->service->setupFromFormConfig($butirId);

            return response()->json([
                'success' => true,
                'message' => 'Mapping berhasil dibuat',
                'data' => $mappings,
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => $e->getMessage(),
            ], 400);
        }
    }

    /**
     * Update mappings manually
     * PUT /api/butir-akreditasis/{butirId}/mappings
     */
    public function update(Request $request, int $butirId)
    {
        $request->validate([
            'fields' => 'required|array',
            'fields.*.name' => 'required|string',
            'fields.*.label' => 'required|string',
            'fields.*.type' => 'required|string',
        ]);

        try {
            $mappings = $this->service->updateMappings($butirId, $request->fields);

            return response()->json([
                'success' => true,
                'message' => 'Mapping berhasil diupdate',
                'data' => $mappings,
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => $e->getMessage(),
            ], 400);
        }
    }
}
```

### 3. Routes

**File:** `routes/api.php`

```php
// Butir Data routes
Route::prefix('pengisian-butirs/{pengisianButirId}')->group(function () {
    Route::get('/data', [ButirDataController::class, 'index']);
    Route::post('/data', [ButirDataController::class, 'store']);
    Route::post('/data/bulk', [ButirDataController::class, 'bulkStore']);
    Route::post('/data/import', [ButirDataController::class, 'import']);
    Route::get('/data/export', [ButirDataController::class, 'export']);
});

Route::prefix('butir-data')->group(function () {
    Route::put('/{id}', [ButirDataController::class, 'update']);
    Route::delete('/{id}', [ButirDataController::class, 'destroy']);
    Route::post('/query', [ButirDataController::class, 'query']);
});

// Butir Mapping routes
Route::prefix('butir-akreditasis/{butirId}')->group(function () {
    Route::get('/mappings', [ButirMappingController::class, 'index']);
    Route::post('/mappings/setup', [ButirMappingController::class, 'setupFromFormConfig']);
    Route::put('/mappings', [ButirMappingController::class, 'update']);
});
```

---

## 🧪 Testing Strategy

### 1. Unit Tests

**File:** `tests/Unit/ButirDataTest.php`

```php
<?php

namespace Tests\Unit;

use Tests\TestCase;
use App\Models\ButirData;
use App\Models\PengisianButir;
use App\Models\ButirColumnMapping;
use Illuminate\Foundation\Testing\RefreshDatabase;

class ButirDataTest extends TestCase
{
    use RefreshDatabase;

    /** @test */
    public function it_can_transform_to_named_fields()
    {
        // Setup mapping
        $butir = $this->createButirWithMapping();
        $pengisian = PengisianButir::factory()->create([
            'butir_akreditasi_id' => $butir->id,
        ]);

        // Create data
        $data = ButirData::create([
            'pengisian_butir_id' => $pengisian->id,
            'c1' => 'Ahmad Santoso',
            'c2' => '123456',
            'c3' => 'S3',
        ]);

        // Transform
        $named = $data->toNamedFields();

        $this->assertEquals('Ahmad Santoso', $named['nama_dosen']);
        $this->assertEquals('123456', $named['nip']);
        $this->assertEquals('S3', $named['pendidikan']);
    }

    /** @test */
    public function it_can_create_from_named_fields()
    {
        $butir = $this->createButirWithMapping();
        $pengisian = PengisianButir::factory()->create([
            'butir_akreditasi_id' => $butir->id,
        ]);

        $data = ButirData::fromNamedFields([
            'nama_dosen' => 'Ahmad Santoso',
            'nip' => '123456',
            'pendidikan' => 'S3',
        ], $pengisian->id);

        $this->assertEquals('Ahmad Santoso', $data->c1);
        $this->assertEquals('123456', $data->c2);
        $this->assertEquals('S3', $data->c3);
    }

    protected function createButirWithMapping()
    {
        $butir = ButirAkreditasi::factory()->create();

        ButirColumnMapping::create([
            'butir_akreditasi_id' => $butir->id,
            'field_name' => 'nama_dosen',
            'field_label' => 'Nama Dosen',
            'column_name' => 'c1',
            'field_type' => 'text',
            'display_order' => 1,
        ]);

        ButirColumnMapping::create([
            'butir_akreditasi_id' => $butir->id,
            'field_name' => 'nip',
            'field_label' => 'NIP',
            'column_name' => 'c2',
            'field_type' => 'text',
            'display_order' => 2,
        ]);

        ButirColumnMapping::create([
            'butir_akreditasi_id' => $butir->id,
            'field_name' => 'pendidikan',
            'field_label' => 'Pendidikan',
            'column_name' => 'c3',
            'field_type' => 'select',
            'display_order' => 3,
        ]);

        return $butir;
    }
}
```

### 2. Feature Tests

**File:** `tests/Feature/ButirDataApiTest.php`

```php
<?php

namespace Tests\Feature;

use Tests\TestCase;
use App\Models\User;
use App\Models\PengisianButir;
use Illuminate\Foundation\Testing\RefreshDatabase;

class ButirDataApiTest extends TestCase
{
    use RefreshDatabase;

    /** @test */
    public function it_can_create_butir_data()
    {
        $user = User::factory()->create();
        $pengisian = PengisianButir::factory()->create();

        $response = $this->actingAs($user)
            ->postJson("/api/pengisian-butirs/{$pengisian->id}/data", [
                'nama_dosen' => 'Ahmad Santoso',
                'nip' => '123456',
                'pendidikan' => 'S3',
            ]);

        $response->assertStatus(201)
            ->assertJson([
                'success' => true,
                'data' => [
                    'nama_dosen' => 'Ahmad Santoso',
                    'nip' => '123456',
                    'pendidikan' => 'S3',
                ],
            ]);
    }

    /** @test */
    public function it_can_query_by_field()
    {
        $user = User::factory()->create();

        // Create test data
        // ...

        $response = $this->actingAs($user)
            ->postJson('/api/butir-data/query', [
                'butir_id' => 1,
                'filters' => [
                    [
                        'field' => 'pendidikan',
                        'operator' => '=',
                        'value' => 'S3',
                    ],
                ],
            ]);

        $response->assertStatus(200)
            ->assertJsonStructure([
                'success',
                'data' => [
                    'data',
                    'current_page',
                    'per_page',
                ],
            ]);
    }
}
```

---

## 📊 Seeder Examples

### 1. Seeder untuk Data Dosen

**File:** `database/seeders/DosenButirDataSeeder.php`

```php
<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\ButirAkreditasi;
use App\Models\PeriodeAkreditasi;
use App\Models\PengisianButir;
use App\Services\ButirMappingService;
use App\Services\ButirDataService;

class DosenButirDataSeeder extends Seeder
{
    public function run(): void
    {
        $mappingService = new ButirMappingService();
        $dataService = new ButirDataService();

        // Create butir for dosen
        $butir = ButirAkreditasi::create([
            'periode_akreditasi_id' => null, // Template
            'kode' => 'C.1',
            'nama' => 'Data Dosen',
            'deskripsi' => 'Data dosen tetap program studi',
            'bobot' => 10,
        ]);

        // Setup mapping
        $mappingService->setupMappings($butir->id, [
            ['name' => 'nama_dosen', 'label' => 'Nama Dosen', 'type' => 'text', 'required' => true],
            ['name' => 'nip', 'label' => 'NIP', 'type' => 'text', 'required' => true],
            ['name' => 'nidn', 'label' => 'NIDN', 'type' => 'text', 'required' => true],
            ['name' => 'pendidikan', 'label' => 'Pendidikan Terakhir', 'type' => 'select', 'required' => true],
            ['name' => 'jafung', 'label' => 'Jabatan Fungsional', 'type' => 'select', 'required' => true],
            ['name' => 'status', 'label' => 'Status', 'type' => 'select', 'required' => true],
            ['name' => 'unit_kerja', 'label' => 'Unit Kerja', 'type' => 'text'],
            ['name' => 'email', 'label' => 'Email', 'type' => 'email'],
            ['name' => 'hp', 'label' => 'No. HP', 'type' => 'text'],
            ['name' => 'tahun_masuk', 'label' => 'Tahun Masuk', 'type' => 'number'],
        ]);

        // Create periode
        $periode = PeriodeAkreditasi::first();

        // Create pengisian
        $pengisian = PengisianButir::create([
            'periode_akreditasi_id' => $periode->id,
            'butir_akreditasi_id' => $butir->id,
            'status' => 'draft',
        ]);

        // Seed sample data
        $dosenData = [
            [
                'nama_dosen' => 'Dr. Ahmad Santoso, M.Kom',
                'nip' => '198501012010121001',
                'nidn' => '0101018501',
                'pendidikan' => 'S3',
                'jafung' => 'Lektor Kepala',
                'status' => 'Aktif',
                'unit_kerja' => 'Teknik Informatika',
                'email' => 'ahmad.santoso@univ.ac.id',
                'hp' => '081234567890',
                'tahun_masuk' => 2010,
            ],
            [
                'nama_dosen' => 'Prof. Dr. Budi Raharjo, M.T',
                'nip' => '197801012005011001',
                'nidn' => '0101017801',
                'pendidikan' => 'S3',
                'jafung' => 'Guru Besar',
                'status' => 'Aktif',
                'unit_kerja' => 'Teknik Informatika',
                'email' => 'budi.raharjo@univ.ac.id',
                'hp' => '081234567891',
                'tahun_masuk' => 2005,
            ],
            [
                'nama_dosen' => 'Citra Dewi, S.Kom, M.Cs',
                'nip' => '199001012015082001',
                'nidn' => '0101019001',
                'pendidikan' => 'S2',
                'jafung' => 'Lektor',
                'status' => 'Aktif',
                'unit_kerja' => 'Teknik Informatika',
                'email' => 'citra.dewi@univ.ac.id',
                'hp' => '081234567892',
                'tahun_masuk' => 2015,
            ],
        ];

        $dataService->bulkCreate($pengisian->id, $dosenData);

        $this->command->info('Seeded ' . count($dosenData) . ' dosen records');
    }
}
```

---

## 🚀 Deployment Plan

### Phase 1: Infrastructure (Hari 1-2)

```bash
# 1. Run migrations
php artisan migrate

# 2. Setup mappings dari form_config yang sudah ada
php artisan butir:setup-mappings

# 3. Migrate existing data (if any)
php artisan butir:migrate-form-data

# 4. Run seeders
php artisan db:seed --class=DosenButirDataSeeder
```

### Phase 2: Testing (Hari 3)

```bash
# Run tests
php artisan test --testsuite=Unit
php artisan test --testsuite=Feature

# Performance testing
php artisan butir:test-query-performance
```

### Phase 3: Rollout (Hari 4-5)

1. Deploy ke staging
2. Test dengan user sample
3. Fix bugs
4. Deploy ke production
5. Monitor performance

---

## 📈 Performance Optimization Tips

### 1. Index Strategy

```sql
-- Tambahkan index untuk kolom yang sering di-query
CREATE INDEX idx_butir_data_c11 ON butir_data(c11);
CREATE INDEX idx_butir_data_c12 ON butir_data(c12);

-- Compound index untuk query kombinasi
CREATE INDEX idx_butir_data_c2_c4 ON butir_data(c2, c4);

-- Partial index untuk filter tertentu
CREATE INDEX idx_butir_data_c6_active
ON butir_data(c6) WHERE c6 = 'Aktif';
```

### 2. Query Optimization

```php
// Eager loading
$data = ButirData::with('pengisianButir.butirAkreditasi.columnMappings')
    ->byPengisian($pengisianId)
    ->get();

// Caching mappings
Cache::remember("mappings.{$butirId}", 3600, function() use ($butirId) {
    return ButirColumnMapping::getByButirId($butirId);
});

// Chunk large datasets
ButirData::byPengisian($pengisianId)
    ->chunk(100, function($rows) {
        // Process in batches
    });
```

### 3. Database Tuning

```sql
-- Analyze query plans
EXPLAIN ANALYZE
SELECT * FROM butir_data WHERE c1 = 'Ahmad' AND c2 = '2024';

-- Vacuum regularly
VACUUM ANALYZE butir_data;

-- Update statistics
ANALYZE butir_data;
```

---

## 🔍 Monitoring & Maintenance

### 1. Query Performance Monitoring

```php
// Add to AppServiceProvider
DB::listen(function($query) {
    if ($query->time > 1000) { // Queries over 1s
        Log::warning('Slow query detected', [
            'sql' => $query->sql,
            'time' => $query->time,
            'bindings' => $query->bindings,
        ]);
    }
});
```

### 2. Regular Maintenance Tasks

```bash
# Weekly: Optimize tables
php artisan butir:optimize-tables

# Monthly: Review unused indexes
php artisan butir:analyze-indexes

# Quarterly: Archive old data
php artisan butir:archive-old-data
```

---

## ❓ FAQ & Troubleshooting

### Q: Bagaimana jika butir membutuhkan lebih dari 30 field?

**A:** Gunakan metadata JSONB untuk field tambahan yang jarang di-query:

```php
$data = ButirData::create([
    'pengisian_butir_id' => $id,
    'c1' => 'value1',
    'c2' => 'value2',
    // ... c3-c30
    'metadata' => [
        'custom_data' => [
            'field_31' => 'value31',
            'field_32' => 'value32',
        ]
    ]
]);
```

### Q: Bagaimana cara update mapping tanpa kehilangan data?

**A:** Buat migration khusus untuk remapping:

```php
// 1. Simpan data ke temporary columns
// 2. Update mappings
// 3. Copy data ke columns baru
// 4. Cleanup temporary columns
```

### Q: Performance turun setelah data mencapai 100k rows?

**A:**
- Pastikan index sudah optimal
- Gunakan partitioning by pengisian_butir_id
- Consider archiving old data
- Use materialized views untuk reporting

---

## 📝 Changelog

### Version 1.0.0 (2025-11-21)

- Initial implementation
- Support untuk 30 columns
- Dynamic mapping system
- Query builder with field names
- Import/export functionality

---

## 🤝 Contributing

Untuk kontribusi atau perubahan:

1. Diskusikan dengan tim terlebih dahulu
2. Update dokumentasi ini jika ada perubahan struktur
3. Tambahkan tests untuk fitur baru
4. Update changelog

---

## 📚 References

- [Laravel Eloquent Documentation](https://laravel.com/docs/eloquent)
- [PostgreSQL JSONB Performance](https://www.postgresql.org/docs/current/datatype-json.html)
- [Database Indexing Best Practices](https://use-the-index-luke.com/)

---

**Dokumen ini adalah living document dan akan di-update seiring development.**
