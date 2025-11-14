<?php

namespace App\Repositories;

use App\Models\Jabatan;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Support\Collection;

class JabatanRepository
{
    protected Jabatan $model;

    public function __construct(Jabatan $model)
    {
        $this->model = $model;
    }

    /**
     * Get all jabatan with filters and pagination
     */
    public function getAll(array $filters = [], int $perPage = 15): LengthAwarePaginator
    {
        $query = $this->model->query();

        if (isset($filters['is_active'])) {
            $query->where('is_active', $filters['is_active']);
        }

        if (isset($filters['kategori'])) {
            $query->where('kategori', $filters['kategori']);
        }

        if (isset($filters['level'])) {
            $query->where('level', $filters['level']);
        }

        if (isset($filters['search'])) {
            $search = $filters['search'];
            $query->where(function($q) use ($search) {
                $q->where('nama_jabatan', 'LIKE', "%{$search}%")
                  ->orWhere('kode_jabatan', 'LIKE', "%{$search}%");
            });
        }

        return $query->orderBy('level')->orderBy('nama_jabatan')->paginate($perPage);
    }

    /**
     * Find jabatan by ID
     */
    public function findById(string $id): ?Jabatan
    {
        return $this->model->find($id);
    }

    /**
     * Create new jabatan
     */
    public function create(array $data): Jabatan
    {
        return $this->model->create($data);
    }

    /**
     * Update jabatan
     */
    public function update(Jabatan $jabatan, array $data): Jabatan
    {
        $jabatan->update($data);
        return $jabatan->fresh();
    }

    /**
     * Delete jabatan
     */
    public function delete(Jabatan $jabatan): bool
    {
        return $jabatan->delete();
    }

    /**
     * Check if kode_jabatan already exists
     */
    public function kodeExists(string $kode, ?string $exceptId = null): bool
    {
        $query = $this->model->where('kode_jabatan', $kode);

        if ($exceptId) {
            $query->where('id', '!=', $exceptId);
        }

        return $query->exists();
    }

    /**
     * Get all active jabatan
     */
    public function getActive(): Collection
    {
        return $this->model->where('is_active', true)
            ->orderBy('level')
            ->orderBy('nama_jabatan')
            ->get();
    }

    /**
     * Get jabatan by kategori
     */
    public function getByKategori(string $kategori): Collection
    {
        return $this->model->where('kategori', $kategori)
            ->where('is_active', true)
            ->orderBy('level')
            ->orderBy('nama_jabatan')
            ->get();
    }

    /**
     * Get jabatan by level
     */
    public function getByLevel(int $level): Collection
    {
        return $this->model->where('level', $level)
            ->where('is_active', true)
            ->orderBy('nama_jabatan')
            ->get();
    }

    /**
     * Get unique categories
     */
    public function getCategories(): array
    {
        return ['struktural', 'fungsional', 'dosen', 'tendik'];
    }

    /**
     * Get statistics
     */
    public function getStatistics(): array
    {
        return [
            'total' => $this->model->count(),
            'active' => $this->model->where('is_active', true)->count(),
            'inactive' => $this->model->where('is_active', false)->count(),
            'by_kategori' => $this->model->selectRaw('kategori, COUNT(*) as count')
                ->groupBy('kategori')
                ->pluck('count', 'kategori')
                ->toArray(),
            'by_level' => $this->model->selectRaw('level, COUNT(*) as count')
                ->whereNotNull('level')
                ->groupBy('level')
                ->orderBy('level')
                ->pluck('count', 'level')
                ->toArray(),
        ];
    }
}
