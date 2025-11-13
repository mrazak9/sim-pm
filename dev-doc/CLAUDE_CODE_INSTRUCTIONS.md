# Claude Code Instructions - SIM Penjaminan Mutu

> **Project**: Sistem Informasi Manajemen Penjaminan Mutu  
> **Tech Stack**: Laravel 10+ | Vue.js 3 | PostgreSQL 14+ | Tailwind CSS  
> **Version**: 1.0  
> **Last Updated**: November 2024

---

## 📋 Project Overview

Building a comprehensive **Quality Assurance Management Information System** for Higher Education institutions in Indonesia. The system manages:

- **Akreditasi** (BAN-PT/LAM Accreditation)
- **Audit** (Internal/External/ISO)
- **Dokumen** (Document Management with Versioning)
- **Kuesioner** (Survey Builder & Analytics)
- **SPMI** (Internal Quality Assurance - PPEPP Cycle)
- **IKU** (Key Performance Indicators Tracking)
- **Dashboard** (Executive Dashboard with Visualizations)

---

## 🎯 Project Goals

1. **Efficiency**: Reduce accreditation preparation time by 50%
2. **Automation**: Automate audit processes and reporting
3. **Data-Driven**: Real-time dashboard for decision making
4. **Compliance**: Ensure compliance with SN Dikti standards
5. **Integration**: Seamless integration with SIAKAD and PDDIKTI

---

## 🏗️ Architecture & Tech Stack

### Backend
- **Framework**: Laravel 10+
- **PHP Version**: 8.1+
- **API Authentication**: Laravel Sanctum
- **Queue System**: Redis with Laravel Horizon
- **File Storage**: Local / AWS S3 / Google Cloud Storage

### Frontend
- **Framework**: Vue.js 3 (Composition API)
- **State Management**: Pinia
- **Styling**: Tailwind CSS
- **Build Tool**: Vite
- **HTTP Client**: Axios

### Database
- **Primary**: PostgreSQL 14+ (recommended) or MySQL 8+
- **Cache & Queue**: Redis
- **Search**: Elasticsearch (optional for full-text search)

### Development Tools
- **Version Control**: Git
- **Code Quality**: Laravel Pint (PSR-12)
- **Testing**: PHPUnit (backend), Vitest (frontend)
- **API Documentation**: OpenAPI/Swagger
- **Debugging**: Laravel Debugbar, Ray

---

## 📂 Project Structure

```
sim-penjaminan-mutu/
├── app/
│   ├── Http/
│   │   ├── Controllers/
│   │   │   └── Api/              # RESTful API controllers
│   │   │       ├── AkreditasiController.php
│   │   │       ├── AuditController.php
│   │   │       ├── DokumenController.php
│   │   │       ├── KuesionerController.php
│   │   │       ├── SPMIController.php
│   │   │       └── IKUController.php
│   │   ├── Requests/              # Form validation requests
│   │   │   ├── StoreAkreditasiRequest.php
│   │   │   └── UpdateAkreditasiRequest.php
│   │   ├── Resources/             # API response resources
│   │   │   ├── AkreditasiResource.php
│   │   │   └── AkreditasiCollection.php
│   │   └── Middleware/
│   ├── Models/                    # Eloquent models
│   │   ├── Akreditasi/
│   │   ├── Audit/
│   │   ├── Dokumen/
│   │   └── User.php
│   ├── Services/                  # Business logic layer
│   │   ├── AkreditasiService.php
│   │   ├── ScoringService.php
│   │   └── NotificationService.php
│   ├── Repositories/              # Data access layer
│   │   ├── AkreditasiRepository.php
│   │   └── BaseRepository.php
│   ├── Jobs/                      # Queue jobs
│   │   ├── ProcessDocumentJob.php
│   │   └── SendNotificationJob.php
│   ├── Events/                    # Application events
│   ├── Listeners/                 # Event listeners
│   └── Traits/                    # Reusable traits
├── database/
│   ├── migrations/
│   ├── seeders/
│   └── factories/
├── resources/
│   ├── js/
│   │   ├── components/
│   │   │   ├── akreditasi/
│   │   │   ├── audit/
│   │   │   ├── dokumen/
│   │   │   └── shared/
│   │   ├── views/
│   │   ├── composables/
│   │   ├── stores/
│   │   ├── router/
│   │   ├── services/
│   │   └── utils/
│   └── css/
├── routes/
│   ├── api.php                    # API routes
│   └── web.php                    # Web routes
├── tests/
│   ├── Feature/                   # Feature tests
│   └── Unit/                      # Unit tests
├── storage/
│   ├── app/
│   │   ├── public/
│   │   └── documents/
│   └── logs/
└── docs/
    ├── api/
    ├── database/
    └── architecture/
```

---

## 🔐 Security Standards (CRITICAL)

### Authentication & Authorization
- **Always** use Laravel Sanctum for API authentication
- Implement **Role-Based Access Control (RBAC)** using Spatie Laravel Permission
- Use **2FA** for admin users
- Implement **rate limiting** on all auth endpoints
- **Session timeout**: 60 minutes of inactivity
- **JWT tokens**: 24-hour expiry with refresh token

### Data Protection
- **Hash all passwords** using bcrypt (Laravel default)
- **Encrypt sensitive data** at rest using AES-256
- Use **HTTPS** everywhere (enforce in production)
- Implement **CSRF protection** (Laravel default)
- **Sanitize all user inputs** to prevent XSS
- Use **parameterized queries** (Eloquent handles this)
- **Never expose** .env file or sensitive config

### File Upload Security
- **Validate file types** (whitelist approach)
- **Scan for malware** before storage
- **Limit file size**: 50MB default, configurable
- **Store outside** public directory
- Generate **unique filenames** to prevent overwrites
- Implement **access control** for file downloads

### API Security
- **Validate all inputs** in Form Requests
- Return **appropriate HTTP status codes**
- **Never expose** sensitive data in error messages
- Implement **API versioning**: /api/v1/
- Use **rate limiting**: 60 requests/minute per user
- Log **security events** (failed logins, unauthorized access)

---

## 📝 Coding Standards

### PHP/Laravel Best Practices

#### PSR-12 Compliance
```php
// ✅ GOOD
class AkreditasiController extends Controller
{
    public function __construct(
        private AkreditasiService $service
    ) {}

    public function index(Request $request): JsonResponse
    {
        try {
            $data = $this->service->paginate($request->all());
            return response()->json([
                'success' => true,
                'data' => AkreditasiResource::collection($data),
            ]);
        } catch (\Exception $e) {
            Log::error('Akreditasi index error', [
                'error' => $e->getMessage(),
                'user_id' => auth()->id(),
            ]);
            
            return response()->json([
                'success' => false,
                'message' => 'Gagal mengambil data akreditasi',
            ], 500);
        }
    }
}

// ❌ BAD - Missing type hints, no error handling
class AkreditasiController extends Controller
{
    public function index($request)
    {
        $data = Akreditasi::all();
        return $data;
    }
}
```

#### Repository Pattern
```php
// Repository Interface
interface AkreditasiRepositoryInterface
{
    public function paginate(array $filters, int $perPage = 15);
    public function findById(int $id);
    public function create(array $data);
    public function update(int $id, array $data);
    public function delete(int $id): bool;
}

// Repository Implementation
class AkreditasiRepository implements AkreditasiRepositoryInterface
{
    public function paginate(array $filters, int $perPage = 15)
    {
        return AkreditasiPeriod::query()
            ->when($filters['search'] ?? null, function ($query, $search) {
                $query->where('nama', 'like', "%{$search}%");
            })
            ->when($filters['prodi_id'] ?? null, function ($query, $prodiId) {
                $query->where('prodi_id', $prodiId);
            })
            ->with(['prodi', 'butirAkreditasi'])
            ->latest()
            ->paginate($perPage);
    }
}
```

#### Service Layer for Business Logic
```php
class AkreditasiService
{
    public function __construct(
        private AkreditasiRepository $repository,
        private ScoringService $scoringService,
        private NotificationService $notificationService
    ) {}

    public function create(array $data): AkreditasiPeriod
    {
        DB::beginTransaction();
        
        try {
            $akreditasi = $this->repository->create($data);
            
            // Business logic
            $this->createDefaultButir($akreditasi);
            $this->notificationService->sendCreatedNotification($akreditasi);
            
            DB::commit();
            return $akreditasi;
        } catch (\Exception $e) {
            DB::rollBack();
            throw $e;
        }
    }

    public function calculateScore(int $akreditasiId): array
    {
        $akreditasi = $this->repository->findById($akreditasiId);
        return $this->scoringService->calculate($akreditasi);
    }
}
```

#### Form Request Validation
```php
class StoreAkreditasiRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()->can('create', AkreditasiPeriod::class);
    }

    public function rules(): array
    {
        return [
            'nama' => 'required|string|max:255',
            'jenis_akreditasi' => 'required|in:ban-pt,lam,internasional',
            'lembaga_akreditasi' => 'required|string|max:100',
            'tanggal_mulai' => 'required|date',
            'deadline_dokumen' => 'required|date|after:tanggal_mulai',
            'tanggal_visitasi' => 'nullable|date|after:deadline_dokumen',
            'tanggal_berakhir' => 'required|date|after:tanggal_visitasi',
            'prodi_id' => 'required|exists:prodis,id',
        ];
    }

    public function messages(): array
    {
        return [
            'nama.required' => 'Nama akreditasi wajib diisi',
            'jenis_akreditasi.in' => 'Jenis akreditasi tidak valid',
            'prodi_id.exists' => 'Program studi tidak ditemukan',
        ];
    }
}
```

#### API Resources
```php
class AkreditasiResource extends JsonResource
{
    public function toArray($request): array
    {
        return [
            'id' => $this->id,
            'nama' => $this->nama,
            'jenis_akreditasi' => $this->jenis_akreditasi,
            'lembaga_akreditasi' => $this->lembaga_akreditasi,
            'periode' => [
                'mulai' => $this->tanggal_mulai?->format('d/m/Y'),
                'deadline_dokumen' => $this->deadline_dokumen?->format('d/m/Y'),
                'visitasi' => $this->tanggal_visitasi?->format('d/m/Y'),
                'berakhir' => $this->tanggal_berakhir?->format('d/m/Y'),
            ],
            'status' => $this->status,
            'progress' => $this->calculateProgress(),
            'prodi' => new ProdiResource($this->whenLoaded('prodi')),
            'butir_count' => $this->butir_akreditasi_count,
            'created_at' => $this->created_at?->toIso8601String(),
            'updated_at' => $this->updated_at?->toIso8601String(),
        ];
    }
}
```

### Vue.js/Frontend Standards

#### Composition API with Script Setup
```vue
<script setup lang="ts">
import { ref, computed, onMounted } from 'vue'
import { useAkreditasiStore } from '@/stores/akreditasi'
import { useRouter } from 'vue-router'

// Props
interface Props {
  akreditasiId?: number
}

const props = withDefaults(defineProps<Props>(), {
  akreditasiId: undefined
})

// Emits
const emit = defineEmits<{
  (e: 'saved', data: any): void
  (e: 'cancelled'): void
}>()

// Store & Router
const store = useAkreditasiStore()
const router = useRouter()

// Reactive State
const loading = ref(false)
const error = ref<string | null>(null)
const formData = ref({
  nama: '',
  jenis_akreditasi: 'ban-pt',
  // ... other fields
})

// Computed
const isValid = computed(() => {
  return formData.value.nama.length > 0 && 
         formData.value.prodi_id !== null
})

// Methods
const handleSubmit = async () => {
  if (!isValid.value) return
  
  loading.value = true
  error.value = null
  
  try {
    const result = await store.create(formData.value)
    emit('saved', result)
    router.push(`/akreditasi/${result.id}`)
  } catch (e: any) {
    error.value = e.response?.data?.message || 'Terjadi kesalahan'
  } finally {
    loading.value = false
  }
}

// Lifecycle
onMounted(async () => {
  if (props.akreditasiId) {
    await loadData()
  }
})
</script>

<template>
  <div class="max-w-4xl mx-auto p-6">
    <!-- Error Alert -->
    <div v-if="error" class="mb-4 p-4 bg-red-50 border border-red-200 rounded-lg">
      <p class="text-red-800">{{ error }}</p>
    </div>

    <!-- Form -->
    <form @submit.prevent="handleSubmit" class="space-y-6">
      <!-- Form fields with Tailwind CSS -->
      <div>
        <label class="block text-sm font-medium text-gray-700 mb-2">
          Nama Akreditasi
        </label>
        <input
          v-model="formData.nama"
          type="text"
          class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent"
          required
        />
      </div>

      <!-- Submit Button -->
      <button
        type="submit"
        :disabled="!isValid || loading"
        class="w-full px-6 py-3 bg-blue-600 text-white rounded-lg hover:bg-blue-700 disabled:opacity-50 disabled:cursor-not-allowed transition-colors"
      >
        <span v-if="loading">Loading...</span>
        <span v-else>Simpan</span>
      </button>
    </form>
  </div>
</template>
```

#### Pinia Store Pattern
```typescript
import { defineStore } from 'pinia'
import axios from '@/services/axios'

interface AkreditasiPeriod {
  id: number
  nama: string
  // ... other fields
}

export const useAkreditasiStore = defineStore('akreditasi', {
  state: () => ({
    periods: [] as AkreditasiPeriod[],
    currentPeriod: null as AkreditasiPeriod | null,
    loading: false,
    error: null as string | null,
  }),

  getters: {
    activePeriods: (state) => {
      return state.periods.filter(p => p.status === 'active')
    },
  },

  actions: {
    async fetchAll() {
      this.loading = true
      this.error = null
      
      try {
        const { data } = await axios.get('/api/v1/akreditasi')
        this.periods = data.data
      } catch (error: any) {
        this.error = error.response?.data?.message || 'Failed to fetch'
        throw error
      } finally {
        this.loading = false
      }
    },

    async create(payload: Partial<AkreditasiPeriod>) {
      const { data } = await axios.post('/api/v1/akreditasi', payload)
      this.periods.push(data.data)
      return data.data
    },

    async update(id: number, payload: Partial<AkreditasiPeriod>) {
      const { data } = await axios.put(`/api/v1/akreditasi/${id}`, payload)
      const index = this.periods.findIndex(p => p.id === id)
      if (index !== -1) {
        this.periods[index] = data.data
      }
      return data.data
    },

    async delete(id: number) {
      await axios.delete(`/api/v1/akreditasi/${id}`)
      this.periods = this.periods.filter(p => p.id !== id)
    },
  },
})
```

---

## 🗄️ Database Design Standards

### Naming Conventions
- **Tables**: plural, snake_case (e.g., `akreditasi_periods`, `audit_findings`)
- **Columns**: snake_case (e.g., `created_at`, `prodi_id`)
- **Primary Key**: always `id` (bigIncrements)
- **Foreign Keys**: `{table_singular}_id` (e.g., `user_id`, `prodi_id`)
- **Pivot Tables**: alphabetical order (e.g., `prodi_user`, not `user_prodi`)
- **Boolean columns**: prefix with `is_` or `has_` (e.g., `is_active`, `has_submitted`)

### Migration Best Practices
```php
Schema::create('akreditasi_periods', function (Blueprint $table) {
    $table->id();
    $table->string('nama');
    $table->enum('jenis_akreditasi', ['ban-pt', 'lam', 'internasional']);
    $table->string('lembaga_akreditasi', 100);
    $table->date('tanggal_mulai');
    $table->date('deadline_dokumen');
    $table->date('tanggal_visitasi')->nullable();
    $table->date('tanggal_berakhir');
    $table->enum('status', ['persiapan', 'pengisian', 'review', 'submit', 'visitasi', 'selesai'])
          ->default('persiapan');
    $table->foreignId('prodi_id')->constrained('prodis')->onDelete('cascade');
    $table->timestamps();
    $table->softDeletes();
    
    // Indexes
    $table->index('status');
    $table->index('prodi_id');
    $table->index('tanggal_mulai');
});
```

### Eloquent Model Conventions
```php
class AkreditasiPeriod extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'nama',
        'jenis_akreditasi',
        'lembaga_akreditasi',
        'tanggal_mulai',
        'deadline_dokumen',
        'tanggal_visitasi',
        'tanggal_berakhir',
        'status',
        'prodi_id',
    ];

    protected $casts = [
        'tanggal_mulai' => 'date',
        'deadline_dokumen' => 'date',
        'tanggal_visitasi' => 'date',
        'tanggal_berakhir' => 'date',
    ];

    // Relationships
    public function prodi(): BelongsTo
    {
        return $this->belongsTo(Prodi::class);
    }

    public function butirAkreditasi(): HasMany
    {
        return $this->hasMany(ButirAkreditasi::class, 'akreditasi_period_id');
    }

    public function dokumen(): MorphMany
    {
        return $this->morphMany(Dokumen::class, 'dokumentable');
    }

    // Scopes
    public function scopeActive($query)
    {
        return $query->whereIn('status', ['pengisian', 'review', 'submit']);
    }

    public function scopeByProdi($query, int $prodiId)
    {
        return $query->where('prodi_id', $prodiId);
    }

    // Accessors
    public function getProgressAttribute(): float
    {
        $total = $this->butirAkreditasi()->count();
        if ($total === 0) return 0;
        
        $filled = $this->butirAkreditasi()->whereNotNull('konten')->count();
        return round(($filled / $total) * 100, 2);
    }
}
```

---

## 🧪 Testing Standards

### Feature Test Example
```php
class AkreditasiTest extends TestCase
{
    use RefreshDatabase;

    private User $user;
    private Prodi $prodi;

    protected function setUp(): void
    {
        parent::setUp();
        
        $this->user = User::factory()->create();
        $this->prodi = Prodi::factory()->create();
        
        Sanctum::actingAs($this->user);
    }

    /** @test */
    public function it_can_create_akreditasi_period()
    {
        $data = [
            'nama' => 'Akreditasi 2024',
            'jenis_akreditasi' => 'ban-pt',
            'lembaga_akreditasi' => 'BAN-PT',
            'tanggal_mulai' => '2024-01-01',
            'deadline_dokumen' => '2024-06-01',
            'tanggal_berakhir' => '2024-12-31',
            'prodi_id' => $this->prodi->id,
        ];

        $response = $this->postJson('/api/v1/akreditasi', $data);

        $response->assertStatus(201)
                 ->assertJsonStructure([
                     'success',
                     'message',
                     'data' => ['id', 'nama', 'status']
                 ]);

        $this->assertDatabaseHas('akreditasi_periods', [
            'nama' => 'Akreditasi 2024',
            'prodi_id' => $this->prodi->id,
        ]);
    }

    /** @test */
    public function it_validates_required_fields()
    {
        $response = $this->postJson('/api/v1/akreditasi', []);

        $response->assertStatus(422)
                 ->assertJsonValidationErrors(['nama', 'jenis_akreditasi']);
    }

    /** @test */
    public function it_prevents_unauthorized_access()
    {
        Sanctum::actingAs(User::factory()->create(['role' => 'mahasiswa']));

        $response = $this->postJson('/api/v1/akreditasi', [
            'nama' => 'Test',
        ]);

        $response->assertStatus(403);
    }
}
```

---

## 📡 API Design Standards

### Endpoint Structure
```
BASE_URL/api/v1/

Authentication:
POST   /auth/register
POST   /auth/login
POST   /auth/logout
GET    /auth/user
POST   /auth/refresh

Akreditasi:
GET    /akreditasi              # List with pagination
POST   /akreditasi              # Create
GET    /akreditasi/{id}         # Show single
PUT    /akreditasi/{id}         # Update
DELETE /akreditasi/{id}         # Delete (soft)
GET    /akreditasi/{id}/butir   # Get butir akreditasi
POST   /akreditasi/{id}/score   # Calculate score

Audit:
GET    /audit
POST   /audit
GET    /audit/{id}
PUT    /audit/{id}
DELETE /audit/{id}
GET    /audit/{id}/findings     # Temuan audit
POST   /audit/{id}/rtl          # Rencana tindak lanjut

Dokumen:
GET    /dokumen
POST   /dokumen
GET    /dokumen/{id}
PUT    /dokumen/{id}
DELETE /dokumen/{id}
POST   /dokumen/{id}/approve    # Workflow approval
GET    /dokumen/{id}/versions   # Version history

Kuesioner:
GET    /kuesioner
POST   /kuesioner
GET    /kuesioner/{id}
PUT    /kuesioner/{id}
DELETE /kuesioner/{id}
POST   /kuesioner/{id}/publish
GET    /kuesioner/{id}/responses
POST   /kuesioner/{id}/submit   # Submit response

IKU:
GET    /iku
POST   /iku
GET    /iku/{id}
PUT    /iku/{id}
DELETE /iku/{id}
POST   /iku/{id}/capaian        # Update capaian
GET    /iku/dashboard           # Dashboard data

Master Data:
GET    /prodis
GET    /fakultas
GET    /users
GET    /tahun-akademik
```

### Response Format
```json
// Success Response
{
    "success": true,
    "message": "Data berhasil disimpan",
    "data": {
        "id": 1,
        "nama": "Akreditasi 2024",
        // ... other fields
    }
}

// Success with Pagination
{
    "success": true,
    "data": [
        { "id": 1, "nama": "..." },
        { "id": 2, "nama": "..." }
    ],
    "meta": {
        "current_page": 1,
        "last_page": 10,
        "per_page": 15,
        "total": 150,
        "from": 1,
        "to": 15
    },
    "links": {
        "first": "http://api.example.com/akreditasi?page=1",
        "last": "http://api.example.com/akreditasi?page=10",
        "prev": null,
        "next": "http://api.example.com/akreditasi?page=2"
    }
}

// Error Response
{
    "success": false,
    "message": "Gagal menyimpan data",
    "errors": {
        "nama": ["Nama wajib diisi"],
        "prodi_id": ["Prodi tidak ditemukan"]
    }
}
```

---

## 🚀 Performance Optimization

### Database Query Optimization
```php
// ❌ BAD - N+1 Query Problem
$akreditasi = AkreditasiPeriod::all();
foreach ($akreditasi as $item) {
    echo $item->prodi->nama; // Each triggers a query
    echo $item->butirAkreditasi->count(); // Another query per item
}

// ✅ GOOD - Eager Loading
$akreditasi = AkreditasiPeriod::with(['prodi', 'butirAkreditasi'])
    ->withCount('butirAkreditasi')
    ->get();

foreach ($akreditasi as $item) {
    echo $item->prodi->nama; // No additional query
    echo $item->butir_akreditasi_count; // No additional query
}
```

### Caching Strategy
```php
use Illuminate\Support\Facades\Cache;

class AkreditasiService
{
    public function getDashboardStats(int $prodiId): array
    {
        return Cache::remember("akreditasi.stats.{$prodiId}", 3600, function () use ($prodiId) {
            return [
                'total' => AkreditasiPeriod::where('prodi_id', $prodiId)->count(),
                'active' => AkreditasiPeriod::active()->where('prodi_id', $prodiId)->count(),
                'progress' => $this->calculateAverageProgress($prodiId),
            ];
        });
    }

    public function clearCache(int $prodiId): void
    {
        Cache::forget("akreditasi.stats.{$prodiId}");
    }
}
```

### Queue Jobs for Heavy Tasks
```php
// Job Class
class ProcessAkreditasiDocumentJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public function __construct(
        private int $documentId
    ) {}

    public function handle(): void
    {
        $document = Dokumen::find($this->documentId);
        
        // Heavy processing
        $this->extractText($document);
        $this->generateThumbnail($document);
        $this->scanForMalware($document);
        
        $document->update(['processed' => true]);
    }
}

// Dispatch in Controller
ProcessAkreditasiDocumentJob::dispatch($document->id);
```

---

## 🎨 UI/UX Guidelines

### Tailwind CSS Component Patterns

#### Card Component
```vue
<template>
  <div class="bg-white rounded-lg shadow-sm border border-gray-200 p-6">
    <div class="flex items-center justify-between mb-4">
      <h3 class="text-lg font-semibold text-gray-900">
        {{ title }}
      </h3>
      <slot name="actions" />
    </div>
    <div class="text-gray-600">
      <slot />
    </div>
  </div>
</template>
```

#### Button Variants
```vue
<!-- Primary Button -->
<button class="px-4 py-2 bg-blue-600 text-white rounded-lg hover:bg-blue-700 focus:ring-4 focus:ring-blue-200 transition-colors">
  Primary Action
</button>

<!-- Secondary Button -->
<button class="px-4 py-2 bg-gray-100 text-gray-700 rounded-lg hover:bg-gray-200 focus:ring-4 focus:ring-gray-200 transition-colors">
  Secondary Action
</button>

<!-- Danger Button -->
<button class="px-4 py-2 bg-red-600 text-white rounded-lg hover:bg-red-700 focus:ring-4 focus:ring-red-200 transition-colors">
  Delete
</button>
```

#### Form Input
```vue
<div class="space-y-2">
  <label class="block text-sm font-medium text-gray-700">
    Nama Akreditasi
    <span class="text-red-500">*</span>
  </label>
  <input
    type="text"
    class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent transition-shadow"
    placeholder="Masukkan nama akreditasi"
  />
  <p class="text-sm text-red-600">Error message here</p>
</div>
```

### Responsive Design
- **Mobile First**: Design for mobile, then scale up
- **Breakpoints**: sm (640px), md (768px), lg (1024px), xl (1280px)
- **Touch Targets**: Minimum 44x44px for clickable elements
- **Navigation**: Hamburger menu on mobile, full menu on desktop

---

## 📚 Indonesian Context & Terminology

### Key Terms
- **Akreditasi**: Accreditation (BAN-PT, LAM)
- **Prodi**: Program Studi (Study Program)
- **Fakultas**: Faculty
- **Dosen**: Lecturer/Faculty Member
- **Mahasiswa**: Student
- **Tendik**: Tenaga Kependidikan (Educational Staff)
- **SPMI**: Sistem Penjaminan Mutu Internal (Internal Quality Assurance System)
- **PPEPP**: Penetapan, Pelaksanaan, Evaluasi, Pengendalian, Peningkatan (PDCA Cycle)
- **IKU**: Indikator Kinerja Utama (Key Performance Indicator)
- **RTL**: Rencana Tindak Lanjut (Follow-up Action Plan)
- **SN Dikti**: Standar Nasional Pendidikan Tinggi (National Higher Education Standards)
- **Butir Akreditasi**: Accreditation Item/Criteria
- **Temuan Audit**: Audit Finding
- **OFI**: Opportunity for Improvement

### UI Text Guidelines
- Use **Bahasa Indonesia** for all user-facing text
- Use **formal but friendly** tone
- **Error messages**: Clear and actionable
- **Success messages**: Encouraging and informative
- **Button labels**: Action-oriented (Simpan, Tambah, Hapus, Batal)

---

## 🔄 Git Workflow & Commit Standards

### Branch Naming
```
main                    # Production branch
develop                 # Development branch
feature/akreditasi     # New feature
bugfix/scoring-error   # Bug fix
hotfix/security-patch  # Urgent production fix
```

### Conventional Commits
```bash
feat: Add akreditasi scoring calculation
fix: Fix N+1 query in audit list
refactor: Extract scoring logic to service class
docs: Update API documentation
test: Add feature tests for akreditasi module
chore: Update dependencies
style: Format code with Laravel Pint
perf: Optimize database queries with eager loading
```

### Commit Message Format
```
<type>(<scope>): <subject>

<body>

<footer>
```

Example:
```
feat(akreditasi): Add scoring simulation feature

Implement scoring calculation based on BAN-PT 4.0 instrument.
- Calculate weighted scores for each butir
- Generate gap analysis report
- Return predicted rating (Unggul/Baik Sekali/Baik)

Closes #123
```

---

## 🎯 Module-Specific Guidelines

### Akreditasi Module

#### Features
1. **Period Management**: CRUD akreditasi periods
2. **Butir Akreditasi**: Template-based or custom butir
3. **Document Upload**: Support multiple file types
4. **Collaboration**: Multiple users can edit
5. **Version Control**: Track changes to submissions
6. **Scoring Simulation**: Auto-calculate scores
7. **Gap Analysis**: Identify missing requirements
8. **Timeline Tracking**: Reminders for deadlines

#### Business Rules
- One active period per prodi
- Butir must be filled before scoring
- Documents must be approved before submission
- Scoring formula based on BAN-PT 4.0
- Auto-lock after submission deadline

### Audit Module

#### Features
1. **Audit Planning**: Schedule and assign auditors
2. **Audit Execution**: Digital audit forms (mobile-friendly)
3. **Findings Management**: Classify (Major/Minor/Observation/OFI)
4. **RTL Tracking**: Follow-up action plans
5. **Evidence Management**: Photos and documents
6. **Report Generation**: Auto-generate PDF reports

#### Business Rules
- Findings must have RTL
- RTL must have assigned PIC and deadline
- Evidence required for findings closure
- Auto-escalation for overdue RTL

### Document Management Module

#### Features
1. **Version Control**: Track all document changes
2. **Workflow Approval**: Multi-level approval process
3. **Auto Numbering**: ISO-compliant numbering
4. **Full-Text Search**: Search within documents
5. **Retention Policy**: Auto-archive expired docs
6. **Digital Signature**: Sign approved documents

#### Business Rules
- Cannot delete approved documents
- Version increment on every change
- Approval required before publication
- Access control per document
- Audit trail for all actions

### Kuesioner Module

#### Features
1. **Survey Builder**: Drag-and-drop form builder
2. **Logic Branching**: Conditional questions
3. **Multi-Channel**: Email, WhatsApp, QR code
4. **Real-Time Analytics**: Live results dashboard
5. **Sentiment Analysis**: AI-powered analysis
6. **Export**: Multiple formats (Excel, PDF, SPSS)

#### Business Rules
- Published surveys cannot be edited
- Anonymous option available
- Response limit per user configurable
- Auto-close after end date

### SPMI Module

#### Features
1. **Standards Management**: 24 SN Dikti standards
2. **PPEPP Cycle**: Complete cycle implementation
3. **Compliance Tracking**: Monitor compliance levels
4. **Gap Analysis**: Identify gaps in implementation
5. **Continuous Improvement**: Track improvements

#### Business Rules
- Standards must follow SN Dikti format
- PPEPP cycle must be complete
- Annual review mandatory
- Approval required for standard changes

### IKU Module

#### Features
1. **Cascading KPI**: From institution to unit level
2. **Target Setting**: Flexible target types
3. **Progress Tracking**: Real-time monitoring
4. **Traffic Light**: Red/Yellow/Green indicators
5. **Evidence Upload**: Proof of achievement
6. **Dashboard**: Visual KPI dashboard

#### Business Rules
- Targets must be measurable
- Evidence required for achievement
- Monthly update mandatory
- Auto-alert for red zone
- Approval required for target changes

---

## 🛠️ Development Workflow with Claude Code

### Starting New Feature

1. **Plan with Claude**
```
I want to create the Akreditasi Period Management feature.

Requirements:
- CRUD operations for akreditasi periods
- Support BAN-PT, LAM, and International accreditation
- Timeline tracking with reminders
- Link to program studi
- Status workflow: persiapan -> pengisian -> review -> submit -> visitasi -> selesai

Please help me plan:
1. Database schema (migration)
2. Models with relationships
3. Repository pattern
4. Service layer
5. API endpoints
6. Form validation
7. API resources

Follow our project standards in CLAUDE_CODE_INSTRUCTIONS.md
```

2. **Generate Migration**
```
Create migration for akreditasi_periods table with the following fields:
- id (primary key)
- nama (string, 255)
- jenis_akreditasi (enum: ban-pt, lam, internasional)
- lembaga_akreditasi (string, 100)
- tanggal_mulai (date)
- deadline_dokumen (date)
- tanggal_visitasi (date, nullable)
- tanggal_berakhir (date)
- status (enum: persiapan, pengisian, review, submit, visitasi, selesai)
- prodi_id (foreign key to prodis table)
- timestamps
- soft deletes

Add appropriate indexes and foreign key constraints.
Follow our database naming conventions.
```

3. **Generate Model**
```
Create AkreditasiPeriod model with:

Relationships:
- belongsTo Prodi
- hasMany ButirAkreditasi
- morphMany Dokumen

Fillable properties: [all fields except id]

Casts:
- tanggal_mulai: date
- deadline_dokumen: date
- tanggal_visitasi: date
- tanggal_berakhir: date

Scopes:
- active (status in pengisian, review, submit)
- byProdi($prodiId)

Accessors:
- progress (calculate based on filled butir)

Follow Laravel 10 best practices and add PHPDoc comments.
```

4. **Generate Repository**
```
Create AkreditasiRepository with Repository Pattern:

Methods:
- paginate(array $filters, int $perPage = 15)
- findById(int $id)
- create(array $data)
- update(int $id, array $data)
- delete(int $id): bool
- getActiveByProdi(int $prodiId)

Include:
- Search functionality
- Filter by status, prodi, date range
- Eager loading for prodi and butirAkreditasi
- Sort options

Follow our repository pattern standards.
```

5. **Generate Service Layer**
```
Create AkreditasiService with business logic:

Methods:
- create(array $data): AkreditasiPeriod
- update(int $id, array $data): AkreditasiPeriod
- delete(int $id): bool
- calculateProgress(int $id): float
- getTimeline(int $id): array
- sendReminders(int $id): void

Include:
- Database transactions
- Error handling with try-catch
- Event dispatching
- Notification sending
- Validation of business rules

Inject AkreditasiRepository dependency.
```

6. **Generate Form Requests**
```
Create Form Request classes:

StoreAkreditasiRequest:
- Validation rules for all required fields
- Custom error messages in Bahasa Indonesia
- Authorization check (user can create akreditasi)

UpdateAkreditasiRequest:
- Similar to Store but with optional fields
- Prevent status change if submitted
- Authorization check (user can update this akreditasi)

Follow our validation standards.
```

7. **Generate API Resources**
```
Create AkreditasiResource for API responses:

Include:
- All model attributes
- Formatted dates (d/m/Y)
- Related prodi data (when loaded)
- Calculated progress
- Butir count
- Status label (Indonesian)

Create AkreditasiCollection for list responses with meta data.

Follow our API response format standards.
```

8. **Generate Controller**
```
Create AkreditasiController with RESTful methods:

- index: List with pagination, search, filter
- store: Create new akreditasi
- show: Get single akreditasi with relations
- update: Update akreditasi
- destroy: Soft delete akreditasi

Include:
- Form Request validation
- Service layer injection
- API Resource responses
- Try-catch error handling
- Proper HTTP status codes
- Authorization checks

Follow our controller pattern standards.
```

9. **Generate Routes**
```
Add routes to routes/api.php for akreditasi endpoints:

- GET /api/v1/akreditasi
- POST /api/v1/akreditasi
- GET /api/v1/akreditasi/{id}
- PUT /api/v1/akreditasi/{id}
- DELETE /api/v1/akreditasi/{id}

Include:
- Auth middleware (sanctum)
- Rate limiting middleware
- Route model binding
- API versioning (v1)

Follow our API endpoint structure.
```

10. **Generate Tests**
```
Create feature tests for AkreditasiController:

Test cases:
1. Authenticated user can list akreditasi
2. User can create akreditasi with valid data
3. Validation fails with invalid data
4. User can view single akreditasi
5. User can update akreditasi
6. User can delete akreditasi
7. Unauthorized user cannot access endpoints
8. Search and filter work correctly

Use factories for test data.
Assert response structure and status codes.
Mock external services.

Follow our testing standards.
```

### Debugging with Claude

```
This code is producing an N+1 query problem in the audit list:

[paste problematic code]

Please:
1. Identify the N+1 query issue
2. Explain why it's happening
3. Provide the optimized solution using eager loading
4. Show before and after query counts
```

### Refactoring with Claude

```
Refactor this controller method to follow our standards:

[paste code]

Requirements:
- Extract business logic to service layer
- Use Form Request for validation
- Use API Resource for response
- Add proper error handling
- Add PHPDoc comments
- Follow SOLID principles
```

### Code Review with Claude

```
Review this code for:
- Security vulnerabilities
- Performance issues
- Code quality
- Best practices compliance
- Missing error handling
- Missing tests

[paste code]

Provide specific recommendations for improvements.
```

---

## ⚡ Quick Reference Commands

### Laravel Artisan
```bash
# Create migration
php artisan make:migration create_akreditasi_periods_table

# Create model with migration, factory, seeder
php artisan make:model AkreditasiPeriod -mfs

# Create controller
php artisan make:controller Api/AkreditasiController --api

# Create request
php artisan make:request StoreAkreditasiRequest

# Create resource
php artisan make:resource AkreditasiResource

# Run migrations
php artisan migrate

# Run seeders
php artisan db:seed

# Run tests
php artisan test

# Run specific test
php artisan test --filter=AkreditasiTest

# Clear cache
php artisan cache:clear
php artisan config:clear
php artisan route:clear

# Queue work
php artisan queue:work

# Format code
./vendor/bin/pint
```

### Composer
```bash
# Install dependencies
composer install

# Update dependencies
composer update

# Require package
composer require package/name

# Autoload classes
composer dump-autoload
```

### NPM
```bash
# Install dependencies
npm install

# Run dev server
npm run dev

# Build for production
npm run build

# Run tests
npm run test
```

### Git
```bash
# Create feature branch
git checkout -b feature/akreditasi

# Add changes
git add .

# Commit with conventional format
git commit -m "feat(akreditasi): Add CRUD operations"

# Push to remote
git push origin feature/akreditasi

# Create pull request (via GitHub/GitLab UI)
```

---

## 🎯 Success Metrics

### Code Quality
- PSR-12 compliance: 100%
- Test coverage: >70%
- Code duplication: <3%
- Complexity score: <10 per method

### Performance
- API response time: <500ms (95th percentile)
- Page load time: <2s (95th percentile)
- Database query time: <100ms (95th percentile)
- Concurrent users: >500 without degradation

### Security
- No critical vulnerabilities
- All inputs validated
- All sensitive data encrypted
- All API endpoints authenticated
- Rate limiting implemented

### User Experience
- Mobile responsive: 100% of pages
- Accessibility: WCAG 2.1 Level AA
- Error messages: Clear and actionable
- Success feedback: Immediate and visible

---

## 🆘 Troubleshooting

### Common Issues

#### 1. N+1 Query Problem
```
Problem: Slow query performance
Solution: Use eager loading with with()

// Before
$akreditasi = AkreditasiPeriod::all();
foreach ($akreditasi as $item) {
    echo $item->prodi->nama; // N+1!
}

// After
$akreditasi = AkreditasiPeriod::with('prodi')->get();
```

#### 2. Memory Issues with Large Datasets
```
Problem: Memory exhausted
Solution: Use chunking or lazy collections

// Before
$data = AkreditasiPeriod::all(); // Loads all into memory

// After
AkreditasiPeriod::chunk(100, function ($items) {
    foreach ($items as $item) {
        // Process
    }
});
```

#### 3. CORS Errors
```
Problem: CORS policy blocking frontend requests
Solution: Configure cors.php properly

'paths' => ['api/*'],
'allowed_methods' => ['*'],
'allowed_origins' => [env('FRONTEND_URL', 'http://localhost:5173')],
'allowed_headers' => ['*'],
```

#### 4. 419 CSRF Token Mismatch
```
Problem: CSRF token mismatch in API calls
Solution: Exclude API routes from CSRF verification

// In App\Http\Middleware\VerifyCsrfToken
protected $except = [
    'api/*',
];

// Or use Sanctum for API authentication
```

---

## 📖 Additional Resources

- **Laravel Documentation**: https://laravel.com/docs
- **Vue.js Guide**: https://vuejs.org/guide/
- **Tailwind CSS**: https://tailwindcss.com/docs
- **PostgreSQL Docs**: https://www.postgresql.org/docs/
- **Laravel Best Practices**: https://github.com/alexeymezenin/laravel-best-practices
- **PHP The Right Way**: https://phptherightway.com/

---

## 🎉 Remember

- **Security First**: Always validate and sanitize inputs
- **Test Everything**: Write tests alongside features
- **Keep It Simple**: Don't over-engineer
- **Document Well**: Code is read more than written
- **Review Carefully**: Never blindly accept AI-generated code
- **Learn Continuously**: Study patterns from generated code

---

**Happy Coding with Claude Code!** 🚀

For questions or clarifications, refer to the full specification documents or ask Claude for help!
