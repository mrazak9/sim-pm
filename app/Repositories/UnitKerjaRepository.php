<?php

namespace App\Repositories;

use App\Models\UnitKerja;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Support\Collection;

class UnitKerjaRepository
{
    protected UnitKerja $model;

    public function __construct(UnitKerja $model)
    {
        $this->model = $model;
    }

    /**
     * Get all unit kerja with filters and pagination
     */
    public function getAll(array $filters = [], int $perPage = 15): LengthAwarePaginator
    {
        $query = $this->model->with(['parent', 'children']);

        if (isset($filters['is_active'])) {
            $query->where('is_active', $filters['is_active']);
        }

        if (isset($filters['jenis_unit'])) {
            $query->where('jenis_unit', $filters['jenis_unit']);
        }

        if (isset($filters['parent_id'])) {
            $query->where('parent_id', $filters['parent_id']);
        }

        if (isset($filters['search'])) {
            $search = $filters['search'];
            $query->where(function($q) use ($search) {
                $q->where('nama_unit', 'LIKE', "%{$search}%")
                  ->orWhere('kode_unit', 'LIKE', "%{$search}%");
            });
        }

        return $query->orderBy('nama_unit')->paginate($perPage);
    }

    /**
     * Find unit kerja by ID
     */
    public function findById(string $id, array $with = []): ?UnitKerja
    {
        $query = $this->model->query();

        if (!empty($with)) {
            $query->with($with);
        }

        return $query->find($id);
    }

    /**
     * Create new unit kerja
     */
    public function create(array $data): UnitKerja
    {
        return $this->model->create($data);
    }

    /**
     * Update unit kerja
     */
    public function update(UnitKerja $unitKerja, array $data): UnitKerja
    {
        $unitKerja->update($data);
        return $unitKerja->fresh(['parent', 'children']);
    }

    /**
     * Delete unit kerja
     */
    public function delete(UnitKerja $unitKerja): bool
    {
        return $unitKerja->delete();
    }

    /**
     * Check if kode_unit already exists
     */
    public function kodeExists(string $kode, ?string $exceptId = null): bool
    {
        $query = $this->model->where('kode_unit', $kode);

        if ($exceptId) {
            $query->where('id', '!=', $exceptId);
        }

        return $query->exists();
    }

    /**
     * Get all active unit kerja
     */
    public function getActive(): Collection
    {
        return $this->model->where('is_active', true)
            ->orderBy('nama_unit')
            ->get();
    }

    /**
     * Get unit kerja by jenis
     */
    public function getByJenis(string $jenis): Collection
    {
        return $this->model->where('jenis_unit', $jenis)
            ->where('is_active', true)
            ->orderBy('nama_unit')
            ->get();
    }

    /**
     * Get root units (no parent)
     */
    public function getRootUnits(): Collection
    {
        return $this->model->whereNull('parent_id')
            ->where('is_active', true)
            ->with('children')
            ->orderBy('nama_unit')
            ->get();
    }

    /**
     * Get children of a unit
     */
    public function getChildren(string $parentId): Collection
    {
        return $this->model->where('parent_id', $parentId)
            ->where('is_active', true)
            ->orderBy('nama_unit')
            ->get();
    }

    /**
     * Check if unit has children
     */
    public function hasChildren(string $id): bool
    {
        return $this->model->where('parent_id', $id)->exists();
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
            'by_jenis' => $this->model->selectRaw('jenis_unit, COUNT(*) as count')
                ->groupBy('jenis_unit')
                ->pluck('count', 'jenis_unit')
                ->toArray(),
        ];
    }
}
