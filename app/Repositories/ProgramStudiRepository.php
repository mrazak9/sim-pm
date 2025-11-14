<?php

namespace App\Repositories;

use App\Models\ProgramStudi;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Support\Collection;

class ProgramStudiRepository
{
    protected ProgramStudi $model;

    public function __construct(ProgramStudi $model)
    {
        $this->model = $model;
    }

    /**
     * Get all program studi with filters and pagination
     */
    public function getAll(array $filters = [], int $perPage = 15): LengthAwarePaginator
    {
        $query = $this->model->with(['unitKerja']);

        if (isset($filters['is_active'])) {
            $query->where('is_active', $filters['is_active']);
        }

        if (isset($filters['jenjang'])) {
            $query->where('jenjang', $filters['jenjang']);
        }

        if (isset($filters['unit_kerja_id'])) {
            $query->where('unit_kerja_id', $filters['unit_kerja_id']);
        }

        if (isset($filters['akreditasi'])) {
            $query->where('akreditasi', $filters['akreditasi']);
        }

        if (isset($filters['search'])) {
            $search = $filters['search'];
            $query->where(function($q) use ($search) {
                $q->where('nama_prodi', 'LIKE', "%{$search}%")
                  ->orWhere('kode_prodi', 'LIKE', "%{$search}%");
            });
        }

        return $query->orderBy('nama_prodi')->paginate($perPage);
    }

    /**
     * Find program studi by ID
     */
    public function findById(string $id, array $with = []): ?ProgramStudi
    {
        $query = $this->model->query();

        if (!empty($with)) {
            $query->with($with);
        }

        return $query->find($id);
    }

    /**
     * Create new program studi
     */
    public function create(array $data): ProgramStudi
    {
        return $this->model->create($data);
    }

    /**
     * Update program studi
     */
    public function update(ProgramStudi $programStudi, array $data): ProgramStudi
    {
        $programStudi->update($data);
        return $programStudi->fresh(['unitKerja']);
    }

    /**
     * Delete program studi
     */
    public function delete(ProgramStudi $programStudi): bool
    {
        return $programStudi->delete();
    }

    /**
     * Check if kode_prodi already exists
     */
    public function kodeExists(string $kode, ?string $exceptId = null): bool
    {
        $query = $this->model->where('kode_prodi', $kode);

        if ($exceptId) {
            $query->where('id', '!=', $exceptId);
        }

        return $query->exists();
    }

    /**
     * Get all active program studi
     */
    public function getActive(): Collection
    {
        return $this->model->where('is_active', true)
            ->with('unitKerja')
            ->orderBy('nama_prodi')
            ->get();
    }

    /**
     * Get program studi by jenjang
     */
    public function getByJenjang(string $jenjang): Collection
    {
        return $this->model->where('jenjang', $jenjang)
            ->where('is_active', true)
            ->with('unitKerja')
            ->orderBy('nama_prodi')
            ->get();
    }

    /**
     * Get program studi by unit kerja
     */
    public function getByUnitKerja(string $unitKerjaId): Collection
    {
        return $this->model->where('unit_kerja_id', $unitKerjaId)
            ->where('is_active', true)
            ->orderBy('nama_prodi')
            ->get();
    }

    /**
     * Get program studi by akreditasi
     */
    public function getByAkreditasi(string $akreditasi): Collection
    {
        return $this->model->where('akreditasi', $akreditasi)
            ->where('is_active', true)
            ->with('unitKerja')
            ->orderBy('nama_prodi')
            ->get();
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
            'by_jenjang' => $this->model->selectRaw('jenjang, COUNT(*) as count')
                ->groupBy('jenjang')
                ->pluck('count', 'jenjang')
                ->toArray(),
            'by_akreditasi' => $this->model->selectRaw('akreditasi, COUNT(*) as count')
                ->whereNotNull('akreditasi')
                ->groupBy('akreditasi')
                ->pluck('count', 'akreditasi')
                ->toArray(),
        ];
    }
}
