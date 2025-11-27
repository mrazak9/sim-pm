<?php

namespace App\Repositories;

use App\Models\InstrumenAkreditasi;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Support\Collection;

class InstrumenAkreditasiRepository
{
    /**
     * Get all instrumen with filters and pagination
     */
    public function paginate(array $filters = [], int $perPage = 15, string $sortBy = 'tahun_berlaku', string $sortOrder = 'desc'): LengthAwarePaginator
    {
        $query = InstrumenAkreditasi::query();

        // Apply filters
        $this->applyFilters($query, $filters);

        // Apply sorting
        if ($sortBy === 'tahun_berlaku') {
            $query->orderBy('tahun_berlaku', $sortOrder)
                  ->orderBy('kode', 'asc');
        } else {
            $query->orderBy($sortBy, $sortOrder);
        }

        return $query->paginate($perPage);
    }

    /**
     * Find instrumen by ID
     */
    public function findById(int $id): ?InstrumenAkreditasi
    {
        return InstrumenAkreditasi::find($id);
    }

    /**
     * Get active instrumen
     */
    public function getActive(): Collection
    {
        return InstrumenAkreditasi::active()
            ->orderBy('tahun_berlaku', 'desc')
            ->orderBy('kode', 'asc')
            ->get();
    }

    /**
     * Get instrumen by jenis
     */
    public function getByJenis(string $jenis): Collection
    {
        return InstrumenAkreditasi::byJenis($jenis)
            ->active()
            ->orderBy('tahun_berlaku', 'desc')
            ->get();
    }

    /**
     * Get instrumen by lembaga
     */
    public function getByLembaga(string $lembaga): Collection
    {
        return InstrumenAkreditasi::byLembaga($lembaga)
            ->active()
            ->orderBy('tahun_berlaku', 'desc')
            ->get();
    }

    /**
     * Check if kode exists
     */
    public function kodeExists(string $kode, ?int $excludeId = null): bool
    {
        $query = InstrumenAkreditasi::where('kode', $kode);

        if ($excludeId) {
            $query->where('id', '!=', $excludeId);
        }

        return $query->exists();
    }

    /**
     * Create new instrumen
     */
    public function create(array $data): InstrumenAkreditasi
    {
        return InstrumenAkreditasi::create($data);
    }

    /**
     * Update instrumen
     */
    public function update(int $id, array $data): InstrumenAkreditasi
    {
        $instrumen = $this->findById($id);

        if (!$instrumen) {
            throw new \Exception('Instrumen akreditasi tidak ditemukan');
        }

        $instrumen->update($data);
        $instrumen->refresh();

        return $instrumen;
    }

    /**
     * Delete instrumen
     */
    public function delete(int $id): bool
    {
        $instrumen = $this->findById($id);

        if (!$instrumen) {
            throw new \Exception('Instrumen akreditasi tidak ditemukan');
        }

        return $instrumen->delete();
    }

    /**
     * Toggle active status
     */
    public function toggleActive(int $id): InstrumenAkreditasi
    {
        $instrumen = $this->findById($id);

        if (!$instrumen) {
            throw new \Exception('Instrumen akreditasi tidak ditemukan');
        }

        $instrumen->update(['is_active' => !$instrumen->is_active]);
        $instrumen->refresh();

        return $instrumen;
    }

    /**
     * Get statistics
     */
    public function getStatistics(): array
    {
        return [
            'total' => InstrumenAkreditasi::count(),
            'active' => InstrumenAkreditasi::active()->count(),
            'inactive' => InstrumenAkreditasi::where('is_active', false)->count(),
            'by_jenis' => InstrumenAkreditasi::selectRaw('jenis, COUNT(*) as count')
                ->groupBy('jenis')
                ->pluck('count', 'jenis')
                ->toArray(),
            'by_lembaga' => InstrumenAkreditasi::selectRaw('lembaga, COUNT(*) as count')
                ->groupBy('lembaga')
                ->pluck('count', 'lembaga')
                ->toArray(),
        ];
    }

    /**
     * Apply filters to query
     */
    protected function applyFilters($query, array $filters): void
    {
        // Filter by jenis
        if (!empty($filters['jenis'])) {
            $query->byJenis($filters['jenis']);
        }

        // Filter by lembaga
        if (!empty($filters['lembaga'])) {
            $query->byLembaga($filters['lembaga']);
        }

        // Filter by active status
        if (isset($filters['is_active'])) {
            if ($filters['is_active'] === 'true' || $filters['is_active'] === '1' || $filters['is_active'] === true) {
                $query->where('is_active', true);
            } elseif ($filters['is_active'] === 'false' || $filters['is_active'] === '0' || $filters['is_active'] === false) {
                $query->where('is_active', false);
            }
        }

        // Search
        if (!empty($filters['search'])) {
            $search = $filters['search'];
            $query->where(function ($q) use ($search) {
                $q->where('kode', 'ilike', "%{$search}%")
                  ->orWhere('nama', 'ilike', "%{$search}%")
                  ->orWhere('deskripsi', 'ilike', "%{$search}%");
            });
        }
    }
}
