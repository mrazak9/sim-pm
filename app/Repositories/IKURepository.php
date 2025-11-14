<?php

namespace App\Repositories;

use App\Models\IKU;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Pagination\LengthAwarePaginator;

class IKURepository
{
    /**
     * Get all IKUs with optional filters and pagination
     */
    public function getAll(array $filters = [], int $perPage = 15): LengthAwarePaginator
    {
        $query = IKU::with(['targets']);

        // Apply filters
        if (isset($filters['is_active'])) {
            $query->where('is_active', $filters['is_active']);
        }

        if (isset($filters['kategori'])) {
            $query->where('kategori', $filters['kategori']);
        }

        if (isset($filters['target_type'])) {
            $query->where('target_type', $filters['target_type']);
        }

        if (isset($filters['search'])) {
            $search = $filters['search'];
            $query->where(function($q) use ($search) {
                $q->where('nama_iku', 'LIKE', "%{$search}%")
                  ->orWhere('kode_iku', 'LIKE', "%{$search}%")
                  ->orWhere('deskripsi', 'LIKE', "%{$search}%");
            });
        }

        return $query->orderBy('kode_iku')->paginate($perPage);
    }

    /**
     * Find IKU by ID with relationships
     */
    public function findById(int $id): ?IKU
    {
        return IKU::with([
            'targets.tahunAkademik',
            'targets.unitKerja',
            'targets.programStudi',
            'targets.progress'
        ])->find($id);
    }

    /**
     * Create new IKU
     */
    public function create(array $data): IKU
    {
        return IKU::create($data);
    }

    /**
     * Update existing IKU
     */
    public function update(IKU $iku, array $data): IKU
    {
        $iku->update($data);
        return $iku->fresh(['targets']);
    }

    /**
     * Delete IKU
     */
    public function delete(IKU $iku): bool
    {
        return $iku->delete();
    }

    /**
     * Get unique categories
     */
    public function getCategories(): Collection
    {
        return IKU::whereNotNull('kategori')
            ->distinct()
            ->pluck('kategori');
    }

    /**
     * Get active IKUs only
     */
    public function getActive(): Collection
    {
        return IKU::active()->with(['targets'])->get();
    }

    /**
     * Check if IKU code already exists
     */
    public function codeExists(string $kodeIku, ?int $excludeId = null): bool
    {
        $query = IKU::where('kode_iku', $kodeIku);

        if ($excludeId) {
            $query->where('id', '!=', $excludeId);
        }

        return $query->exists();
    }

    /**
     * Get IKU statistics for dashboard
     */
    public function getStatistics(): array
    {
        return [
            'total' => IKU::count(),
            'active' => IKU::where('is_active', true)->count(),
            'inactive' => IKU::where('is_active', false)->count(),
            'total_categories' => IKU::whereNotNull('kategori')->distinct('kategori')->count(),
        ];
    }

    /**
     * Get IKU by category
     */
    public function getByCategory(string $kategori): Collection
    {
        return IKU::byKategori($kategori)->with(['targets'])->get();
    }
}
