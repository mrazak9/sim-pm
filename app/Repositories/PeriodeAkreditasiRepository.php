<?php

namespace App\Repositories;

use App\Models\PeriodeAkreditasi;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Support\Collection;

class PeriodeAkreditasiRepository
{
    protected PeriodeAkreditasi $model;

    public function __construct(PeriodeAkreditasi $model)
    {
        $this->model = $model;
    }

    /**
     * Get all periode akreditasi
     */
    public function all(): Collection
    {
        return $this->model->with(['unitKerja', 'programStudi'])
            ->orderBy('tanggal_mulai', 'desc')
            ->get();
    }

    /**
     * Get all periode akreditasi with filters and pagination
     */
    public function paginate(array $filters = [], int $perPage = 15): LengthAwarePaginator
    {
        $query = $this->model->with(['unitKerja', 'programStudi']);

        // Filter by jenis_akreditasi
        if (isset($filters['jenis_akreditasi'])) {
            $query->where('jenis_akreditasi', $filters['jenis_akreditasi']);
        }

        // Filter by lembaga
        if (isset($filters['lembaga'])) {
            $query->where('lembaga', $filters['lembaga']);
        }

        // Filter by instrumen
        if (isset($filters['instrumen'])) {
            $query->where('instrumen', $filters['instrumen']);
        }

        // Filter by status
        if (isset($filters['status'])) {
            $query->where('status', $filters['status']);
        }

        // Filter by unit_kerja_id
        if (isset($filters['unit_kerja_id'])) {
            $query->where('unit_kerja_id', $filters['unit_kerja_id']);
        }

        // Filter by program_studi_id
        if (isset($filters['program_studi_id'])) {
            $query->where('program_studi_id', $filters['program_studi_id']);
        }

        // Search
        if (isset($filters['search'])) {
            $search = $filters['search'];
            $query->where(function($q) use ($search) {
                $q->where('nama', 'LIKE', "%{$search}%")
                  ->orWhere('jenis_akreditasi', 'LIKE', "%{$search}%")
                  ->orWhere('lembaga', 'LIKE', "%{$search}%")
                  ->orWhere('keterangan', 'LIKE', "%{$search}%");
            });
        }

        // Apply sorting
        $sortBy = $filters['sort_by'] ?? 'tanggal_mulai';
        $sortOrder = $filters['sort_order'] ?? 'desc';

        // Validate sort column to prevent SQL injection
        $allowedSortColumns = [
            'id', 'nama', 'jenis_akreditasi', 'lembaga', 'instrumen',
            'status', 'tanggal_mulai', 'tanggal_selesai', 'deadline_pengumpulan',
            'created_at', 'updated_at'
        ];

        if (in_array($sortBy, $allowedSortColumns)) {
            $query->orderBy($sortBy, $sortOrder);
        } else {
            $query->orderBy('tanggal_mulai', 'desc');
        }

        return $query->paginate($perPage);
    }

    /**
     * Find periode akreditasi by ID
     */
    public function findById(int $id, array $with = []): ?PeriodeAkreditasi
    {
        $query = $this->model->query();

        $defaultWith = ['unitKerja', 'programStudi', 'pengisianButirs', 'dokumenAkreditasis'];

        if (!empty($with)) {
            $query->with($with);
        } else {
            $query->with($defaultWith);
        }

        return $query->find($id);
    }

    /**
     * Create new periode akreditasi
     */
    public function create(array $data): PeriodeAkreditasi
    {
        return $this->model->create($data);
    }

    /**
     * Update periode akreditasi
     */
    public function update(PeriodeAkreditasi $periodeAkreditasi, array $data): PeriodeAkreditasi
    {
        $periodeAkreditasi->update($data);
        return $periodeAkreditasi->fresh(['unitKerja', 'programStudi', 'pengisianButirs', 'dokumenAkreditasis']);
    }

    /**
     * Delete periode akreditasi
     */
    public function delete(PeriodeAkreditasi $periodeAkreditasi): bool
    {
        return $periodeAkreditasi->delete();
    }

    /**
     * Get active periode akreditasi (persiapan, pengisian, review)
     */
    public function getActive(): Collection
    {
        return $this->model->aktif()
            ->with(['unitKerja', 'programStudi'])
            ->orderBy('tanggal_mulai', 'desc')
            ->get();
    }

    /**
     * Get periode akreditasi by semester (based on tanggal_mulai)
     */
    public function getBySemester(int $year, string $semester): Collection
    {
        $query = $this->model->with(['unitKerja', 'programStudi'])
            ->whereYear('tanggal_mulai', $year);

        // Semester ganjil: Jul-Dec, Semester genap: Jan-Jun
        if ($semester === 'ganjil') {
            $query->whereMonth('tanggal_mulai', '>=', 7);
        } elseif ($semester === 'genap') {
            $query->whereMonth('tanggal_mulai', '<=', 6);
        }

        return $query->orderBy('tanggal_mulai', 'desc')->get();
    }

    /**
     * Get periode akreditasi by program studi
     */
    public function getByProgram(int $programStudiId): Collection
    {
        return $this->model->where('program_studi_id', $programStudiId)
            ->with(['unitKerja', 'programStudi', 'pengisianButirs', 'dokumenAkreditasis'])
            ->orderBy('tanggal_mulai', 'desc')
            ->get();
    }

    /**
     * Get periode akreditasi statistics
     */
    public function getStatistics(): array
    {
        return [
            'total' => $this->model->count(),
            'active' => $this->model->aktif()->count(),
            'by_status' => $this->model->selectRaw('status, COUNT(*) as count')
                ->groupBy('status')
                ->pluck('count', 'status')
                ->toArray(),
            'by_jenis' => $this->model->selectRaw('jenis_akreditasi, COUNT(*) as count')
                ->groupBy('jenis_akreditasi')
                ->pluck('count', 'jenis_akreditasi')
                ->toArray(),
            'by_lembaga' => $this->model->selectRaw('lembaga, COUNT(*) as count')
                ->groupBy('lembaga')
                ->pluck('count', 'lembaga')
                ->toArray(),
            'expired' => $this->model->where('deadline_pengumpulan', '<', now())->count(),
            'upcoming' => $this->model->where('deadline_pengumpulan', '>', now())
                ->where('deadline_pengumpulan', '<=', now()->addDays(30))
                ->count(),
        ];
    }

    /**
     * Get periode akreditasi by lembaga
     */
    public function getByLembaga(string $lembaga): Collection
    {
        return $this->model->byLembaga($lembaga)
            ->with(['unitKerja', 'programStudi'])
            ->orderBy('tanggal_mulai', 'desc')
            ->get();
    }

    /**
     * Get periode akreditasi institusi
     */
    public function getInstitusi(): Collection
    {
        return $this->model->institusi()
            ->with(['unitKerja'])
            ->orderBy('tanggal_mulai', 'desc')
            ->get();
    }

    /**
     * Get periode akreditasi program studi
     */
    public function getProdi(): Collection
    {
        return $this->model->prodi()
            ->with(['programStudi', 'unitKerja'])
            ->orderBy('tanggal_mulai', 'desc')
            ->get();
    }

    /**
     * Get expired periode akreditasi
     */
    public function getExpired(): Collection
    {
        return $this->model->where('deadline_pengumpulan', '<', now())
            ->with(['unitKerja', 'programStudi'])
            ->orderBy('deadline_pengumpulan', 'desc')
            ->get();
    }

    /**
     * Get upcoming periode akreditasi (within next 30 days)
     */
    public function getUpcoming(int $days = 30): Collection
    {
        return $this->model->where('deadline_pengumpulan', '>', now())
            ->where('deadline_pengumpulan', '<=', now()->addDays($days))
            ->with(['unitKerja', 'programStudi'])
            ->orderBy('deadline_pengumpulan', 'asc')
            ->get();
    }
}
