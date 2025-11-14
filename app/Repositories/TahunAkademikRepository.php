<?php

namespace App\Repositories;

use App\Models\TahunAkademik;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Support\Collection;
use Carbon\Carbon;

class TahunAkademikRepository
{
    protected TahunAkademik $model;

    public function __construct(TahunAkademik $model)
    {
        $this->model = $model;
    }

    /**
     * Get all tahun akademik with filters and pagination
     */
    public function getAll(array $filters = [], int $perPage = 15): LengthAwarePaginator
    {
        $query = $this->model->query();

        if (isset($filters['is_active'])) {
            $query->where('is_active', $filters['is_active']);
        }

        if (isset($filters['semester'])) {
            $query->where('semester', $filters['semester']);
        }

        if (isset($filters['search'])) {
            $search = $filters['search'];
            $query->where(function($q) use ($search) {
                $q->where('nama_tahun', 'LIKE', "%{$search}%")
                  ->orWhere('kode_tahun', 'LIKE', "%{$search}%");
            });
        }

        return $query->orderByDesc('tanggal_mulai')->paginate($perPage);
    }

    /**
     * Find tahun akademik by ID
     */
    public function findById(string $id): ?TahunAkademik
    {
        return $this->model->find($id);
    }

    /**
     * Create new tahun akademik
     */
    public function create(array $data): TahunAkademik
    {
        return $this->model->create($data);
    }

    /**
     * Update tahun akademik
     */
    public function update(TahunAkademik $tahunAkademik, array $data): TahunAkademik
    {
        $tahunAkademik->update($data);
        return $tahunAkademik->fresh();
    }

    /**
     * Delete tahun akademik
     */
    public function delete(TahunAkademik $tahunAkademik): bool
    {
        return $tahunAkademik->delete();
    }

    /**
     * Check if kode_tahun already exists
     */
    public function kodeExists(string $kode, ?string $exceptId = null): bool
    {
        $query = $this->model->where('kode_tahun', $kode);

        if ($exceptId) {
            $query->where('id', '!=', $exceptId);
        }

        return $query->exists();
    }

    /**
     * Get all active tahun akademik
     */
    public function getActive(): Collection
    {
        return $this->model->where('is_active', true)
            ->orderByDesc('tanggal_mulai')
            ->get();
    }

    /**
     * Get current tahun akademik
     */
    public function getCurrent(): ?TahunAkademik
    {
        $now = Carbon::now();

        return $this->model->where('is_active', true)
            ->where('tanggal_mulai', '<=', $now)
            ->where('tanggal_selesai', '>=', $now)
            ->first();
    }

    /**
     * Get upcoming tahun akademik
     */
    public function getUpcoming(): ?TahunAkademik
    {
        $now = Carbon::now();

        return $this->model->where('is_active', true)
            ->where('tanggal_mulai', '>', $now)
            ->orderBy('tanggal_mulai')
            ->first();
    }

    /**
     * Get tahun akademik by semester
     */
    public function getBySemester(string $semester): Collection
    {
        return $this->model->where('semester', $semester)
            ->where('is_active', true)
            ->orderByDesc('tanggal_mulai')
            ->get();
    }

    /**
     * Check if dates overlap with existing tahun akademik
     */
    public function hasOverlap(string $tanggalMulai, string $tanggalSelesai, ?string $exceptId = null): bool
    {
        $query = $this->model->where(function($q) use ($tanggalMulai, $tanggalSelesai) {
            $q->whereBetween('tanggal_mulai', [$tanggalMulai, $tanggalSelesai])
              ->orWhereBetween('tanggal_selesai', [$tanggalMulai, $tanggalSelesai])
              ->orWhere(function($q2) use ($tanggalMulai, $tanggalSelesai) {
                  $q2->where('tanggal_mulai', '<=', $tanggalMulai)
                     ->where('tanggal_selesai', '>=', $tanggalSelesai);
              });
        });

        if ($exceptId) {
            $query->where('id', '!=', $exceptId);
        }

        return $query->exists();
    }

    /**
     * Get statistics
     */
    public function getStatistics(): array
    {
        $now = Carbon::now();

        return [
            'total' => $this->model->count(),
            'active' => $this->model->where('is_active', true)->count(),
            'inactive' => $this->model->where('is_active', false)->count(),
            'current' => $this->model->where('is_active', true)
                ->where('tanggal_mulai', '<=', $now)
                ->where('tanggal_selesai', '>=', $now)
                ->count(),
            'by_semester' => $this->model->selectRaw('semester, COUNT(*) as count')
                ->groupBy('semester')
                ->pluck('count', 'semester')
                ->toArray(),
        ];
    }
}
