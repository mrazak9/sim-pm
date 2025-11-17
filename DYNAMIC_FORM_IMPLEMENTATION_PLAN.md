# 📋 Dynamic Form Implementation Plan
## Template-Based Dynamic Forms for Pengisian Butir Akreditasi

> **Project:** SIM Penjaminan Mutu
> **Feature:** Dynamic Form System for Accreditation Items
> **Approach:** Template-Based with JSON Schema
> **Status:** 📝 Planning Phase
> **Created:** 2025-11-17
> **Estimated Duration:** 3-5 Days

---

## 🎯 GOALS & OBJECTIVES

### Business Goals
1. **Flexibility** - Support berbagai jenis input untuk butir akreditasi yang berbeda
2. **Structure** - Data terstruktur untuk analytics & reporting
3. **Compliance** - Sesuai format BAN-PT untuk export
4. **Validation** - Validasi otomatis sesuai requirement tiap butir
5. **User Experience** - Form yang intuitif dan mudah digunakan

### Technical Goals
1. **Scalability** - Mudah menambah form type baru tanpa coding
2. **Maintainability** - Single source of truth untuk form configuration
3. **Reusability** - Dynamic form components yang reusable
4. **Performance** - Efficient data storage & retrieval
5. **Backward Compatibility** - Existing data (rich text) tetap berfungsi

---

## 📊 CURRENT STATE ANALYSIS

### Existing Schema

**Table: `butir_akreditasis`**
```sql
- id (bigint)
- kode (varchar) UNIQUE
- nama (varchar)
- deskripsi (text)
- kategori (varchar)
- parent_id (foreign key, nullable)
- instrumen (varchar) - '4.0', '3.0', etc.
- bobot (integer)
- is_mandatory (boolean)
- metadata (json) ← ALREADY EXISTS! 🎉
- urutan (integer)
- timestamps
```

**Table: `pengisian_butirs`**
```sql
- id (bigint)
- periode_akreditasi_id (foreign key)
- butir_akreditasi_id (foreign key)
- pic_user_id (foreign key)
- konten (longtext) - Rich text HTML
- konten_plain (longtext) - Plain text for search
- files (json) - Array of uploaded files
- status (enum)
- version (integer)
- notes (text)
- reviewed_by (foreign key)
- reviewed_at (timestamp)
- review_notes (text)
- completion_percentage (decimal)
- is_complete (boolean)
- timestamps
- soft_deletes
```

### Current Workflow
1. User select periode → select butir
2. User fill rich text editor (konten)
3. User upload files
4. Submit for review

### Limitations
- ❌ All butir use same input type (rich text)
- ❌ No structured data (susah analytics)
- ❌ No automatic validation
- ❌ Cannot export to BAN-PT format
- ❌ No table input for data-heavy butir (PKM, publikasi, dll)

---

## 🏗️ PROPOSED ARCHITECTURE

### Overview Diagram

```
┌─────────────────────────────────────────────────────┐
│  BUTIR AKREDITASI (Master Data)                     │
│  ┌───────────────────────────────────────────────┐  │
│  │ metadata: {                                   │  │
│  │   "form_config": {                            │  │
│  │     "type": "table",                          │  │
│  │     "columns": [...],                         │  │
│  │     "validations": {...}                      │  │
│  │   }                                           │  │
│  │ }                                             │  │
│  └───────────────────────────────────────────────┘  │
└─────────────────────────────────────────────────────┘
                         │
                         ▼
┌─────────────────────────────────────────────────────┐
│  DYNAMIC FORM RENDERER (Frontend)                   │
│  ┌───────────────────────────────────────────────┐  │
│  │ <component :is="getFormType(config.type)">   │  │
│  │   - TableFormRenderer                         │  │
│  │   - NarrativeFormRenderer                     │  │
│  │   - ChecklistFormRenderer                     │  │
│  │   - MixedFormRenderer                         │  │
│  │ </component>                                  │  │
│  └───────────────────────────────────────────────┘  │
└─────────────────────────────────────────────────────┘
                         │
                         ▼
┌─────────────────────────────────────────────────────┐
│  PENGISIAN BUTIR (User Data)                        │
│  ┌───────────────────────────────────────────────┐  │
│  │ form_data: {                                  │  │
│  │   "rows": [                                   │  │
│  │     {"judul": "...", "tahun": 2024, ...},    │  │
│  │     {"judul": "...", "tahun": 2024, ...}     │  │
│  │   ]                                           │  │
│  │ }                                             │  │
│  │ konten: "..." (backward compat)               │  │
│  └───────────────────────────────────────────────┘  │
└─────────────────────────────────────────────────────┘
```

---

## 🗄️ DATABASE CHANGES

### Migration 1: Add `form_data` column

**File:** `database/migrations/2025_11_17_000001_add_form_data_to_pengisian_butirs.php`

```php
<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('pengisian_butirs', function (Blueprint $table) {
            // Add structured form data column
            $table->json('form_data')->nullable()->after('konten_plain');

            // Add index for better performance
            $table->index('butir_akreditasi_id');
        });
    }

    public function down(): void
    {
        Schema::table('pengisian_butirs', function (Blueprint $table) {
            $table->dropColumn('form_data');
        });
    }
};
```

### Migration 2: Add sample templates to existing butir

**File:** `database/migrations/2025_11_17_000002_add_form_templates_to_butir_akreditasis.php`

```php
<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        // Example: Update PKM butir with table template
        DB::table('butir_akreditasis')
            ->where('kode', 'LIKE', '%C.2.a%') // PKM butir code
            ->update([
                'metadata' => json_encode([
                    'form_config' => [
                        'type' => 'table',
                        'label' => 'Data Pengabdian kepada Masyarakat',
                        'columns' => [
                            [
                                'name' => 'judul',
                                'label' => 'Judul Kegiatan PKM',
                                'type' => 'text',
                                'required' => true,
                                'width' => '30%'
                            ],
                            [
                                'name' => 'tahun',
                                'label' => 'Tahun',
                                'type' => 'number',
                                'required' => true,
                                'width' => '10%',
                                'min' => 2020,
                                'max' => 2030
                            ],
                            [
                                'name' => 'mitra',
                                'label' => 'Nama Mitra',
                                'type' => 'text',
                                'required' => true,
                                'width' => '25%'
                            ],
                            [
                                'name' => 'dana',
                                'label' => 'Dana (Rp)',
                                'type' => 'currency',
                                'required' => false,
                                'width' => '15%'
                            ],
                            [
                                'name' => 'status',
                                'label' => 'Status',
                                'type' => 'select',
                                'required' => true,
                                'options' => [
                                    'selesai' => 'Selesai',
                                    'berjalan' => 'Sedang Berjalan',
                                    'rencana' => 'Rencana'
                                ],
                                'width' => '20%'
                            ]
                        ],
                        'validations' => [
                            'min_rows' => 1,
                            'max_rows' => 100
                        ],
                        'options' => [
                            'allow_add' => true,
                            'allow_delete' => true,
                            'allow_import' => true,
                            'export_format' => 'excel'
                        ]
                    ],
                    'help_text' => 'Isikan seluruh kegiatan PKM yang dilakukan dalam periode akreditasi',
                    'example_data' => [
                        [
                            'judul' => 'Pelatihan Digital Marketing UMKM',
                            'tahun' => 2024,
                            'mitra' => 'Kelompok UMKM Desa ABC',
                            'dana' => 15000000,
                            'status' => 'selesai'
                        ]
                    ]
                ])
            ]);
    }

    public function down(): void
    {
        // Reset metadata
        DB::table('butir_akreditasis')
            ->where('kode', 'LIKE', '%C.2.a%')
            ->update(['metadata' => null]);
    }
};
```

---

## 📐 FORM TYPE SPECIFICATIONS

### 1. TABLE FORM

**Use Cases:** PKM, Publikasi, Luaran, Mahasiswa, Dosen

**Schema:**
```json
{
  "type": "table",
  "label": "Data Pengabdian kepada Masyarakat",
  "columns": [
    {
      "name": "judul",
      "label": "Judul Kegiatan",
      "type": "text",
      "required": true,
      "width": "30%",
      "placeholder": "Masukkan judul kegiatan...",
      "max_length": 500
    },
    {
      "name": "tahun",
      "label": "Tahun",
      "type": "number",
      "required": true,
      "width": "10%",
      "min": 2020,
      "max": 2030
    },
    {
      "name": "dana",
      "label": "Dana (Rp)",
      "type": "currency",
      "required": false,
      "width": "15%"
    },
    {
      "name": "status",
      "label": "Status",
      "type": "select",
      "required": true,
      "options": {
        "selesai": "Selesai",
        "berjalan": "Sedang Berjalan"
      }
    },
    {
      "name": "file",
      "label": "Dokumen Bukti",
      "type": "file",
      "required": false,
      "accept": ".pdf,.doc,.docx",
      "max_size": "5MB"
    }
  ],
  "validations": {
    "min_rows": 1,
    "max_rows": 100,
    "unique_fields": ["judul"]
  },
  "options": {
    "allow_add": true,
    "allow_delete": true,
    "allow_import": true,
    "allow_export": true,
    "show_summary": true
  }
}
```

**Stored Data:**
```json
{
  "rows": [
    {
      "judul": "Pelatihan Digital Marketing",
      "tahun": 2024,
      "dana": 15000000,
      "status": "selesai",
      "file": "uploads/bukti_pkm_001.pdf"
    },
    {
      "judul": "Pendampingan Budidaya Lele",
      "tahun": 2024,
      "dana": 12000000,
      "status": "berjalan",
      "file": null
    }
  ]
}
```

### 2. NARRATIVE FORM

**Use Cases:** Visi-Misi, Tata Pamong, Deskripsi Program

**Schema:**
```json
{
  "type": "narrative",
  "label": "Visi dan Misi Program Studi",
  "fields": [
    {
      "name": "visi",
      "label": "Visi",
      "type": "richtext",
      "required": true,
      "min_length": 100,
      "max_length": 5000,
      "help_text": "Tuliskan visi program studi yang jelas dan terukur"
    },
    {
      "name": "misi",
      "label": "Misi",
      "type": "richtext",
      "required": true,
      "min_length": 100,
      "max_length": 5000
    },
    {
      "name": "strategi",
      "label": "Strategi Pencapaian",
      "type": "richtext",
      "required": false
    }
  ],
  "validations": {
    "require_all": true
  }
}
```

**Stored Data:**
```json
{
  "visi": "<p>Menjadi program studi...</p>",
  "misi": "<ul><li>Menyelenggarakan...</li></ul>",
  "strategi": "<p>Strategi yang dilakukan...</p>"
}
```

### 3. CHECKLIST FORM

**Use Cases:** Kelengkapan Dokumen, Compliance Check

**Schema:**
```json
{
  "type": "checklist",
  "label": "Kelengkapan Dokumen Akreditasi",
  "items": [
    {
      "id": "sk_pendirian",
      "label": "SK Pendirian Program Studi",
      "required": true,
      "allow_file": true,
      "file_required": true
    },
    {
      "id": "kurikulum",
      "label": "Dokumen Kurikulum",
      "required": true,
      "allow_file": true
    },
    {
      "id": "rps",
      "label": "RPS Lengkap Semua Mata Kuliah",
      "required": true,
      "allow_file": true
    }
  ]
}
```

**Stored Data:**
```json
{
  "items": [
    {
      "id": "sk_pendirian",
      "checked": true,
      "file": "uploads/sk_pendirian.pdf",
      "notes": "SK No. 123/2020"
    },
    {
      "id": "kurikulum",
      "checked": true,
      "file": "uploads/kurikulum_2024.pdf",
      "notes": null
    }
  ]
}
```

### 4. MIXED FORM

**Use Cases:** Kombinasi berbagai input

**Schema:**
```json
{
  "type": "mixed",
  "label": "Data Mahasiswa dan Lulusan",
  "sections": [
    {
      "title": "Statistik Mahasiswa",
      "type": "table",
      "columns": [...]
    },
    {
      "title": "Analisis IPK",
      "type": "narrative",
      "fields": [...]
    },
    {
      "title": "Dokumen Pendukung",
      "type": "checklist",
      "items": [...]
    }
  ]
}
```

### 5. METRIC FORM

**Use Cases:** Single value metrics (IPK rata-rata, Rasio dosen-mahasiswa)

**Schema:**
```json
{
  "type": "metric",
  "label": "IPK Rata-rata Lulusan",
  "metrics": [
    {
      "name": "ipk_avg",
      "label": "IPK Rata-rata",
      "type": "decimal",
      "required": true,
      "min": 0.00,
      "max": 4.00,
      "decimals": 2
    },
    {
      "name": "lulus_tepat_waktu",
      "label": "Persentase Lulus Tepat Waktu",
      "type": "percentage",
      "required": true
    }
  ]
}
```

---

## 💻 BACKEND IMPLEMENTATION

### 1. Update Model

**File:** `app/Models/PengisianButir.php`

```php
<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class PengisianButir extends Model
{
    protected $fillable = [
        // ... existing fields
        'form_data',
    ];

    protected $casts = [
        'files' => 'array',
        'form_data' => 'array', // ← NEW
        // ... other casts
    ];

    /**
     * Get form type from related butir
     */
    public function getFormTypeAttribute(): ?string
    {
        return $this->butirAkreditasi?->metadata['form_config']['type'] ?? null;
    }

    /**
     * Check if this pengisian uses dynamic form
     */
    public function hasDynamicForm(): bool
    {
        return !empty($this->butirAkreditasi?->metadata['form_config']);
    }

    /**
     * Get validation rules from butir template
     */
    public function getValidationRules(): array
    {
        if (!$this->hasDynamicForm()) {
            return [];
        }

        $config = $this->butirAkreditasi->metadata['form_config'];
        // Generate validation rules from config
        // Implementation in service layer
    }
}
```

### 2. Create Form Validator Service

**File:** `app/Services/DynamicFormValidatorService.php`

```php
<?php

namespace App\Services;

use App\Models\ButirAkreditasi;
use Illuminate\Support\Facades\Validator;

class DynamicFormValidatorService
{
    /**
     * Validate form data against butir template
     */
    public function validate(array $formData, ButirAkreditasi $butir): array
    {
        if (!isset($butir->metadata['form_config'])) {
            return []; // No dynamic validation needed
        }

        $config = $butir->metadata['form_config'];
        $type = $config['type'];

        return match($type) {
            'table' => $this->validateTableForm($formData, $config),
            'narrative' => $this->validateNarrativeForm($formData, $config),
            'checklist' => $this->validateChecklistForm($formData, $config),
            'metric' => $this->validateMetricForm($formData, $config),
            'mixed' => $this->validateMixedForm($formData, $config),
            default => []
        };
    }

    protected function validateTableForm(array $data, array $config): array
    {
        $rules = [];

        // Validate min/max rows
        if (isset($config['validations']['min_rows'])) {
            $rules['rows'] = 'required|array|min:' . $config['validations']['min_rows'];
        }
        if (isset($config['validations']['max_rows'])) {
            $rules['rows'] .= '|max:' . $config['validations']['max_rows'];
        }

        // Validate each column
        foreach ($config['columns'] as $column) {
            $fieldRules = [];

            if ($column['required'] ?? false) {
                $fieldRules[] = 'required';
            }

            switch ($column['type']) {
                case 'text':
                    $fieldRules[] = 'string';
                    if (isset($column['max_length'])) {
                        $fieldRules[] = 'max:' . $column['max_length'];
                    }
                    break;
                case 'number':
                    $fieldRules[] = 'numeric';
                    if (isset($column['min'])) {
                        $fieldRules[] = 'min:' . $column['min'];
                    }
                    if (isset($column['max'])) {
                        $fieldRules[] = 'max:' . $column['max'];
                    }
                    break;
                case 'currency':
                    $fieldRules[] = 'numeric|min:0';
                    break;
                case 'select':
                    $options = array_keys($column['options'] ?? []);
                    $fieldRules[] = 'in:' . implode(',', $options);
                    break;
            }

            $rules['rows.*.' . $column['name']] = implode('|', $fieldRules);
        }

        return $rules;
    }

    protected function validateNarrativeForm(array $data, array $config): array
    {
        $rules = [];

        foreach ($config['fields'] as $field) {
            $fieldRules = [];

            if ($field['required'] ?? false) {
                $fieldRules[] = 'required';
            }

            if ($field['type'] === 'richtext') {
                $fieldRules[] = 'string';
                if (isset($field['min_length'])) {
                    $fieldRules[] = 'min:' . $field['min_length'];
                }
                if (isset($field['max_length'])) {
                    $fieldRules[] = 'max:' . $field['max_length'];
                }
            }

            $rules[$field['name']] = implode('|', $fieldRules);
        }

        return $rules;
    }

    // ... other validation methods
}
```

### 3. Update Service Layer

**File:** `app/Services/PengisianButirService.php`

```php
// Add to existing service

protected DynamicFormValidatorService $formValidator;

public function __construct(
    PengisianButirRepository $repository,
    DynamicFormValidatorService $formValidator
) {
    $this->repository = $repository;
    $this->formValidator = $formValidator;
}

public function create(array $data): PengisianButir
{
    DB::beginTransaction();

    try {
        // Load butir to get form config
        $butir = ButirAkreditasi::find($data['butir_akreditasi_id']);

        // Validate form_data against template if exists
        if ($butir && isset($data['form_data'])) {
            $rules = $this->formValidator->validate($data['form_data'], $butir);

            if (!empty($rules)) {
                $validator = Validator::make($data['form_data'], $rules);
                if ($validator->fails()) {
                    throw new \Exception('Validasi form gagal: ' . $validator->errors()->first());
                }
            }
        }

        // Calculate completion based on form_data or konten
        $data['completion_percentage'] = $this->calculateCompletion($data, $butir);

        $pengisianButir = $this->repository->create($data);

        DB::commit();
        return $pengisianButir;
    } catch (\Exception $e) {
        DB::rollBack();
        throw $e;
    }
}

protected function calculateCompletion(array $data, ?ButirAkreditasi $butir): float
{
    // If has form_data and template, calculate based on template
    if (isset($data['form_data']) && $butir && isset($butir->metadata['form_config'])) {
        return $this->calculateDynamicFormCompletion($data['form_data'], $butir->metadata['form_config']);
    }

    // Otherwise use existing logic (konten-based)
    return $this->calculateCompletionPercentage($data);
}

protected function calculateDynamicFormCompletion(array $formData, array $config): float
{
    $type = $config['type'];

    return match($type) {
        'table' => $this->calculateTableCompletion($formData, $config),
        'narrative' => $this->calculateNarrativeCompletion($formData, $config),
        'checklist' => $this->calculateChecklistCompletion($formData, $config),
        default => 0.0
    };
}

protected function calculateTableCompletion(array $data, array $config): float
{
    $rows = $data['rows'] ?? [];
    $minRows = $config['validations']['min_rows'] ?? 1;

    if (empty($rows)) {
        return 0.0;
    }

    $totalFields = count($config['columns']);
    $requiredFields = array_filter($config['columns'], fn($col) => $col['required'] ?? false);
    $requiredCount = count($requiredFields);

    $filledRows = 0;
    foreach ($rows as $row) {
        $filledFields = 0;
        foreach ($requiredFields as $field) {
            if (!empty($row[$field['name']])) {
                $filledFields++;
            }
        }
        if ($filledFields === $requiredCount) {
            $filledRows++;
        }
    }

    $rowCompletion = min(100, (count($rows) / max($minRows, 1)) * 100);
    $dataCompletion = ($filledRows / max(count($rows), 1)) * 100;

    return ($rowCompletion + $dataCompletion) / 2;
}

// ... other completion calculators
```

### 4. Update Controller

**File:** `app/Http/Controllers/Api/PengisianButirController.php`

```php
// Add validation for form_data in store/update

public function store(StorePengisianButirRequest $request): JsonResponse
{
    try {
        $data = $request->validated();

        // If has form_data, validate against butir template
        if (isset($data['form_data'])) {
            $butir = ButirAkreditasi::find($data['butir_akreditasi_id']);
            if ($butir && isset($butir->metadata['form_config'])) {
                $formValidator = app(DynamicFormValidatorService::class);
                $rules = $formValidator->validate($data['form_data'], $butir);

                if (!empty($rules)) {
                    $validator = Validator::make($data['form_data'], $rules);
                    if ($validator->fails()) {
                        return response()->json([
                            'success' => false,
                            'message' => 'Validasi form gagal',
                            'errors' => $validator->errors()
                        ], 422);
                    }
                }
            }
        }

        $pengisian = $this->pengisianButirService->createPengisianButir($data);

        return response()->json([
            'success' => true,
            'message' => 'Pengisian butir berhasil disimpan',
            'data' => new PengisianButirResource($pengisian),
        ], 201);
    } catch (\Exception $e) {
        return response()->json([
            'success' => false,
            'message' => 'Gagal menyimpan pengisian butir',
            'error' => $e->getMessage(),
        ], 422);
    }
}
```

---

## 🎨 FRONTEND IMPLEMENTATION

### 1. Create Form Renderer Components

**Directory Structure:**
```
resources/js/components/dynamic-forms/
├── DynamicFormRenderer.vue          (Main router component)
├── renderers/
│   ├── TableFormRenderer.vue
│   ├── NarrativeFormRenderer.vue
│   ├── ChecklistFormRenderer.vue
│   ├── MetricFormRenderer.vue
│   └── MixedFormRenderer.vue
└── fields/
    ├── TextField.vue
    ├── NumberField.vue
    ├── CurrencyField.vue
    ├── SelectField.vue
    ├── DateField.vue
    ├── FileField.vue
    └── RichTextField.vue
```

### 2. Main Dynamic Form Renderer

**File:** `resources/js/components/dynamic-forms/DynamicFormRenderer.vue`

```vue
<template>
  <div class="dynamic-form-renderer">
    <!-- Show legacy rich text if no form config -->
    <RichTextEditor
      v-if="!hasFormConfig"
      v-model="konten"
      :error="errors.konten"
    />

    <!-- Show dynamic form based on type -->
    <component
      v-else
      :is="formComponent"
      v-model="formData"
      :config="formConfig"
      :errors="errors"
      @update:modelValue="handleFormChange"
    />

    <!-- File upload (always available) -->
    <div class="mt-6">
      <label class="block text-sm font-medium mb-2">Dokumen Pendukung</label>
      <FileUpload
        v-model="files"
        multiple
        accept=".pdf,.doc,.docx,.xls,.xlsx"
        :max-size="10485760"
      />
    </div>
  </div>
</template>

<script setup>
import { computed, ref, watch } from 'vue'
import RichTextEditor from '@/components/RichTextEditor.vue'
import FileUpload from '@/components/FileUpload.vue'

// Dynamic form renderers
import TableFormRenderer from './renderers/TableFormRenderer.vue'
import NarrativeFormRenderer from './renderers/NarrativeFormRenderer.vue'
import ChecklistFormRenderer from './renderers/ChecklistFormRenderer.vue'
import MetricFormRenderer from './renderers/MetricFormRenderer.vue'
import MixedFormRenderer from './renderers/MixedFormRenderer.vue'

const props = defineProps({
  butir: {
    type: Object,
    required: true
  },
  modelValue: {
    type: Object,
    default: () => ({
      konten: '',
      form_data: null,
      files: []
    })
  },
  errors: {
    type: Object,
    default: () => ({})
  }
})

const emit = defineEmits(['update:modelValue'])

const konten = ref(props.modelValue.konten || '')
const formData = ref(props.modelValue.form_data || null)
const files = ref(props.modelValue.files || [])

const hasFormConfig = computed(() => {
  return props.butir?.metadata?.form_config !== undefined
})

const formConfig = computed(() => {
  return props.butir?.metadata?.form_config || null
})

const formComponent = computed(() => {
  if (!hasFormConfig.value) return null

  const type = formConfig.value?.type
  const componentMap = {
    'table': TableFormRenderer,
    'narrative': NarrativeFormRenderer,
    'checklist': ChecklistFormRenderer,
    'metric': MetricFormRenderer,
    'mixed': MixedFormRenderer
  }

  return componentMap[type] || null
})

const handleFormChange = (newValue) => {
  formData.value = newValue
  emitUpdate()
}

const emitUpdate = () => {
  emit('update:modelValue', {
    konten: konten.value,
    form_data: formData.value,
    files: files.value
  })
}

watch([konten, files], emitUpdate, { deep: true })
</script>
```

### 3. Table Form Renderer

**File:** `resources/js/components/dynamic-forms/renderers/TableFormRenderer.vue`

```vue
<template>
  <div class="table-form-renderer">
    <!-- Header with title and add button -->
    <div class="mb-4 flex items-center justify-between">
      <div>
        <h3 class="text-lg font-semibold">{{ config.label }}</h3>
        <p v-if="config.help_text" class="mt-1 text-sm text-gray-600 dark:text-gray-400">
          {{ config.help_text }}
        </p>
      </div>
      <button
        v-if="config.options?.allow_add !== false"
        @click="addRow"
        type="button"
        class="inline-flex items-center rounded-lg bg-blue-600 px-4 py-2 text-sm font-medium text-white hover:bg-blue-700"
      >
        <svg class="mr-2 h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
          <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4" />
        </svg>
        Tambah Baris
      </button>
    </div>

    <!-- Validation errors -->
    <div v-if="validationError" class="mb-4 rounded-lg bg-red-50 p-4 dark:bg-red-900/20">
      <p class="text-sm text-red-800 dark:text-red-400">{{ validationError }}</p>
    </div>

    <!-- Table -->
    <div class="overflow-x-auto rounded-lg border border-gray-200 dark:border-gray-700">
      <table class="w-full text-left text-sm">
        <thead class="bg-gray-50 dark:bg-gray-800">
          <tr>
            <th class="px-4 py-3 text-xs font-medium uppercase">#</th>
            <th
              v-for="column in config.columns"
              :key="column.name"
              class="px-4 py-3 text-xs font-medium uppercase"
              :style="{ width: column.width }"
            >
              {{ column.label }}
              <span v-if="column.required" class="text-red-500">*</span>
            </th>
            <th class="px-4 py-3 text-xs font-medium uppercase">Aksi</th>
          </tr>
        </thead>
        <tbody class="divide-y divide-gray-200 dark:divide-gray-700">
          <tr
            v-for="(row, index) in rows"
            :key="index"
            class="hover:bg-gray-50 dark:hover:bg-gray-800"
          >
            <td class="px-4 py-3 text-gray-500">{{ index + 1 }}</td>
            <td
              v-for="column in config.columns"
              :key="column.name"
              class="px-4 py-3"
            >
              <component
                :is="getFieldComponent(column.type)"
                v-model="row[column.name]"
                :config="column"
                :error="getFieldError(index, column.name)"
              />
            </td>
            <td class="px-4 py-3">
              <button
                v-if="config.options?.allow_delete !== false"
                @click="deleteRow(index)"
                type="button"
                class="text-red-600 hover:text-red-800 dark:text-red-400"
              >
                <svg class="h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                  <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16" />
                </svg>
              </button>
            </td>
          </tr>
          <tr v-if="rows.length === 0">
            <td :colspan="config.columns.length + 2" class="px-4 py-8 text-center text-gray-500">
              <p class="mb-2">Belum ada data</p>
              <button
                @click="addRow"
                type="button"
                class="text-blue-600 hover:text-blue-800"
              >
                Klik untuk menambah baris pertama
              </button>
            </td>
          </tr>
        </tbody>
      </table>
    </div>

    <!-- Summary -->
    <div v-if="config.options?.show_summary" class="mt-4 flex items-center justify-between text-sm">
      <div class="text-gray-600 dark:text-gray-400">
        Total: <span class="font-semibold">{{ rows.length }}</span> baris
        <span v-if="config.validations?.min_rows">
          (minimal {{ config.validations.min_rows }} baris)
        </span>
      </div>
      <div class="space-x-2">
        <button
          v-if="config.options?.allow_import"
          @click="importFromExcel"
          type="button"
          class="text-green-600 hover:text-green-800"
        >
          Import Excel
        </button>
        <button
          v-if="config.options?.allow_export"
          @click="exportToExcel"
          type="button"
          class="text-blue-600 hover:text-blue-800"
        >
          Export Excel
        </button>
      </div>
    </div>
  </div>
</template>

<script setup>
import { ref, computed, watch } from 'vue'
import TextField from '../fields/TextField.vue'
import NumberField from '../fields/NumberField.vue'
import CurrencyField from '../fields/CurrencyField.vue'
import SelectField from '../fields/SelectField.vue'
import DateField from '../fields/DateField.vue'
import FileField from '../fields/FileField.vue'

const props = defineProps({
  modelValue: {
    type: Object,
    default: () => ({ rows: [] })
  },
  config: {
    type: Object,
    required: true
  },
  errors: {
    type: Object,
    default: () => ({})
  }
})

const emit = defineEmits(['update:modelValue'])

const rows = ref(props.modelValue?.rows || [])

const validationError = computed(() => {
  const minRows = props.config.validations?.min_rows
  if (minRows && rows.value.length < minRows) {
    return `Minimal ${minRows} baris data harus diisi`
  }
  return null
})

const getFieldComponent = (type) => {
  const componentMap = {
    'text': TextField,
    'number': NumberField,
    'currency': CurrencyField,
    'select': SelectField,
    'date': DateField,
    'file': FileField
  }
  return componentMap[type] || TextField
}

const getFieldError = (rowIndex, fieldName) => {
  return props.errors?.[`rows.${rowIndex}.${fieldName}`]
}

const addRow = () => {
  const newRow = {}
  props.config.columns.forEach(column => {
    newRow[column.name] = column.default || null
  })
  rows.value.push(newRow)
  emitUpdate()
}

const deleteRow = (index) => {
  if (confirm('Hapus baris ini?')) {
    rows.value.splice(index, 1)
    emitUpdate()
  }
}

const emitUpdate = () => {
  emit('update:modelValue', {
    rows: rows.value
  })
}

const importFromExcel = () => {
  // TODO: Implement Excel import
  alert('Fitur import Excel akan segera tersedia')
}

const exportToExcel = () => {
  // TODO: Implement Excel export
  alert('Fitur export Excel akan segera tersedia')
}

watch(rows, emitUpdate, { deep: true })
</script>
```

### 4. Field Components

**File:** `resources/js/components/dynamic-forms/fields/TextField.vue`

```vue
<template>
  <input
    v-model="localValue"
    :type="config.type || 'text'"
    :placeholder="config.placeholder"
    :required="config.required"
    :maxlength="config.max_length"
    class="block w-full rounded border-gray-300 text-sm focus:border-blue-500 focus:ring-blue-500 dark:border-gray-600 dark:bg-gray-700 dark:text-white"
    :class="{ 'border-red-500': error }"
  />
</template>

<script setup>
import { ref, watch } from 'vue'

const props = defineProps({
  modelValue: [String, Number],
  config: Object,
  error: String
})

const emit = defineEmits(['update:modelValue'])

const localValue = ref(props.modelValue)

watch(localValue, (newValue) => {
  emit('update:modelValue', newValue)
})

watch(() => props.modelValue, (newValue) => {
  localValue.value = newValue
})
</script>
```

**Similar for other field types:** NumberField, CurrencyField, SelectField, etc.

---

## 🔄 MIGRATION STRATEGY

### Phase 1: Add Infrastructure (Week 1)

1. ✅ Create migrations
2. ✅ Update models & relationships
3. ✅ Create validator service
4. ✅ Update service layer
5. ✅ Create API endpoints

### Phase 2: Frontend Components (Week 1-2)

1. ✅ Create dynamic form renderer
2. ✅ Create table form renderer
3. ✅ Create field components
4. ✅ Test with sample data

### Phase 3: Template Definition (Week 2)

1. ✅ Define templates for common butir types
2. ✅ Update existing butir with templates
3. ✅ Test validation logic

### Phase 4: Integration & Testing (Week 2-3)

1. ✅ Integrate with existing form
2. ✅ End-to-end testing
3. ✅ User acceptance testing
4. ✅ Fix bugs

### Phase 5: Rollout (Week 3)

1. ✅ Production deployment
2. ✅ User training
3. ✅ Monitor & support

---

## 🧪 TESTING PLAN

### Unit Tests

```php
// tests/Unit/DynamicFormValidatorServiceTest.php
public function test_validates_table_form_correctly()
public function test_rejects_invalid_table_data()
public function test_validates_narrative_form()
public function test_validates_checklist_form()
```

### Feature Tests

```php
// tests/Feature/PengisianButirDynamicFormTest.php
public function test_can_create_pengisian_with_table_form()
public function test_validates_min_rows_requirement()
public function test_calculates_completion_for_table_form()
public function test_backward_compatible_with_rich_text()
```

### Frontend Tests

```javascript
// tests/components/DynamicFormRenderer.spec.js
describe('DynamicFormRenderer', () => {
  it('renders table form when config type is table')
  it('renders legacy rich text when no config')
  it('validates required fields')
  it('emits correct data structure')
})
```

---

## 📊 EXAMPLE TEMPLATES

### PKM Template

```json
{
  "form_config": {
    "type": "table",
    "label": "Data Pengabdian kepada Masyarakat",
    "columns": [
      {
        "name": "judul",
        "label": "Judul Kegiatan PKM",
        "type": "text",
        "required": true,
        "width": "30%",
        "max_length": 500
      },
      {
        "name": "tahun",
        "label": "Tahun",
        "type": "number",
        "required": true,
        "width": "8%",
        "min": 2020,
        "max": 2030
      },
      {
        "name": "nama_dosen",
        "label": "Nama Dosen Pengabdi",
        "type": "text",
        "required": true,
        "width": "20%"
      },
      {
        "name": "mitra",
        "label": "Mitra PKM",
        "type": "text",
        "required": true,
        "width": "20%"
      },
      {
        "name": "dana",
        "label": "Dana (Rp)",
        "type": "currency",
        "required": false,
        "width": "12%"
      },
      {
        "name": "status",
        "label": "Status",
        "type": "select",
        "required": true,
        "width": "10%",
        "options": {
          "selesai": "Selesai",
          "berjalan": "Sedang Berjalan",
          "rencana": "Rencana"
        }
      }
    ],
    "validations": {
      "min_rows": 5,
      "max_rows": 100
    },
    "options": {
      "allow_add": true,
      "allow_delete": true,
      "allow_import": true,
      "allow_export": true,
      "show_summary": true
    }
  },
  "help_text": "Isikan seluruh kegiatan PKM yang dilakukan oleh dosen dalam 3 tahun terakhir"
}
```

### Publikasi Template

```json
{
  "form_config": {
    "type": "table",
    "label": "Data Publikasi Ilmiah",
    "columns": [
      {
        "name": "judul_artikel",
        "label": "Judul Artikel",
        "type": "text",
        "required": true,
        "width": "30%"
      },
      {
        "name": "nama_dosen",
        "label": "Nama Dosen",
        "type": "text",
        "required": true,
        "width": "15%"
      },
      {
        "name": "nama_jurnal",
        "label": "Nama Jurnal",
        "type": "text",
        "required": true,
        "width": "20%"
      },
      {
        "name": "tahun",
        "label": "Tahun",
        "type": "number",
        "required": true,
        "width": "8%"
      },
      {
        "name": "tingkat",
        "label": "Tingkat",
        "type": "select",
        "required": true,
        "width": "12%",
        "options": {
          "internasional": "Internasional Bereputasi",
          "nasional_terakreditasi": "Nasional Terakreditasi",
          "nasional": "Nasional"
        }
      },
      {
        "name": "url",
        "label": "Link Artikel",
        "type": "text",
        "required": false,
        "width": "15%"
      }
    ],
    "validations": {
      "min_rows": 1
    }
  }
}
```

### Visi Misi Template

```json
{
  "form_config": {
    "type": "narrative",
    "label": "Visi, Misi, Tujuan, dan Strategi",
    "fields": [
      {
        "name": "visi",
        "label": "Visi Program Studi",
        "type": "richtext",
        "required": true,
        "min_length": 100,
        "max_length": 2000,
        "help_text": "Tuliskan visi program studi yang jelas, realistis, dan terukur"
      },
      {
        "name": "misi",
        "label": "Misi Program Studi",
        "type": "richtext",
        "required": true,
        "min_length": 200,
        "max_length": 3000,
        "help_text": "Tuliskan misi program studi yang mendukung pencapaian visi"
      },
      {
        "name": "tujuan",
        "label": "Tujuan Program Studi",
        "type": "richtext",
        "required": true,
        "min_length": 200,
        "max_length": 3000
      },
      {
        "name": "strategi",
        "label": "Strategi Pencapaian",
        "type": "richtext",
        "required": true,
        "min_length": 200,
        "max_length": 5000
      }
    ],
    "validations": {
      "require_all": true
    }
  }
}
```

---

## 📅 TIMELINE

| Week | Phase | Tasks | Deliverables |
|------|-------|-------|--------------|
| 1 | Setup | - Migrations<br>- Models<br>- Validator Service<br>- API Updates | Working backend API |
| 1-2 | Frontend | - Form Renderer<br>- Table Renderer<br>- Field Components | Working dynamic forms |
| 2 | Templates | - Define templates<br>- Populate metadata<br>- Testing | Template library |
| 2-3 | Integration | - E2E testing<br>- Bug fixes<br>- Documentation | Production-ready |
| 3 | Rollout | - Deployment<br>- Training<br>- Support | Live feature |

**Total Duration:** 3-5 Days (with focus)

---

## ✅ ACCEPTANCE CRITERIA

### Must Have
- ✅ Table form renderer works for tabular data
- ✅ Backward compatible with rich text (konten field)
- ✅ Validation based on template config
- ✅ Data stored as structured JSON
- ✅ CRUD operations work with dynamic forms

### Should Have
- ✅ Narrative form renderer
- ✅ Checklist form renderer
- ✅ Auto-calculation of completion percentage
- ✅ Excel import/export for table forms

### Nice to Have
- ⚪ Metric form renderer
- ⚪ Mixed form renderer
- ⚪ Form template builder UI (admin)
- ⚪ Form analytics & insights

---

## 🚀 NEXT STEPS

1. **Review & Approve** this plan
2. **Create GitHub issue** for tracking
3. **Start implementation** with Phase 1 (Database)
4. **Iterate** based on feedback
5. **Deploy** to production

---

## 📞 QUESTIONS & DISCUSSION

### Open Questions
1. Apakah semua butir perlu di-migrate ke dynamic form, atau gradual?
2. Apakah perlu UI untuk admin membuat form template?
3. Export format apa yang dibutuhkan (Excel, PDF, JSON)?
4. Apakah perlu versioning untuk form template?

### Decisions Needed
- [ ] Approve overall architecture
- [ ] Prioritize form types (which first?)
- [ ] Timeline adjustment if needed
- [ ] Resource allocation

---

**Document Version:** 1.0
**Last Updated:** 2025-11-17
**Author:** Claude AI Assistant
**Status:** Awaiting Review & Approval
