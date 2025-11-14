<?php

namespace App\Services;

use App\Models\UnitKerja;
use App\Repositories\UnitKerjaRepository;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class UnitKerjaService
{
    protected UnitKerjaRepository $repository;

    public function __construct(UnitKerjaRepository $repository)
    {
        $this->repository = $repository;
    }

    /**
     * Get all unit kerja with filters and pagination
     */
    public function getAllUnitKerja(array $filters = [], int $perPage = 15): LengthAwarePaginator
    {
        return $this->repository->getAll($filters, $perPage);
    }

    /**
     * Get unit kerja by ID
     */
    public function getUnitKerjaById(string $id): ?UnitKerja
    {
        return $this->repository->findById($id, ['parent', 'children', 'programStudis']);
    }

    /**
     * Create new unit kerja
     */
    public function createUnitKerja(array $data): UnitKerja
    {
        DB::beginTransaction();
        try {
            // Check if kode already exists
            if ($this->repository->kodeExists($data['kode_unit'])) {
                throw new \Exception('Kode Unit sudah digunakan');
            }

            // Validate parent if provided
            if (isset($data['parent_id'])) {
                $parent = $this->repository->findById($data['parent_id']);
                if (!$parent) {
                    throw new \Exception('Parent Unit tidak ditemukan');
                }
            }

            $unitKerja = $this->repository->create($data);

            DB::commit();
            Log::info('Unit Kerja created successfully', ['unit_kerja_id' => $unitKerja->id]);

            return $unitKerja->load(['parent', 'children']);
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Failed to create Unit Kerja', [
                'error' => $e->getMessage(),
                'data' => $data
            ]);
            throw $e;
        }
    }

    /**
     * Update unit kerja
     */
    public function updateUnitKerja(string $id, array $data): UnitKerja
    {
        DB::beginTransaction();
        try {
            $unitKerja = $this->repository->findById($id);

            if (!$unitKerja) {
                throw new \Exception('Unit Kerja tidak ditemukan');
            }

            // Check if kode already exists (except current record)
            if ($this->repository->kodeExists($data['kode_unit'], $id)) {
                throw new \Exception('Kode Unit sudah digunakan');
            }

            // Validate parent if provided
            if (isset($data['parent_id'])) {
                // Cannot set itself as parent
                if ($data['parent_id'] === $id) {
                    throw new \Exception('Unit tidak dapat menjadi parent dari dirinya sendiri');
                }

                $parent = $this->repository->findById($data['parent_id']);
                if (!$parent) {
                    throw new \Exception('Parent Unit tidak ditemukan');
                }
            }

            $unitKerja = $this->repository->update($unitKerja, $data);

            DB::commit();
            Log::info('Unit Kerja updated successfully', ['unit_kerja_id' => $id]);

            return $unitKerja;
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Failed to update Unit Kerja', [
                'error' => $e->getMessage(),
                'unit_kerja_id' => $id
            ]);
            throw $e;
        }
    }

    /**
     * Delete unit kerja
     */
    public function deleteUnitKerja(string $id): void
    {
        DB::beginTransaction();
        try {
            $unitKerja = $this->repository->findById($id);

            if (!$unitKerja) {
                throw new \Exception('Unit Kerja tidak ditemukan');
            }

            // Check if has children
            if ($this->repository->hasChildren($id)) {
                throw new \Exception('Unit Kerja tidak dapat dihapus karena memiliki sub unit');
            }

            $this->repository->delete($unitKerja);

            DB::commit();
            Log::info('Unit Kerja deleted successfully', ['unit_kerja_id' => $id]);
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Failed to delete Unit Kerja', [
                'error' => $e->getMessage(),
                'unit_kerja_id' => $id
            ]);
            throw $e;
        }
    }

    /**
     * Get active unit kerja
     */
    public function getActiveUnitKerja(): Collection
    {
        return $this->repository->getActive();
    }

    /**
     * Get unit kerja by jenis
     */
    public function getUnitKerjaByJenis(string $jenis): Collection
    {
        return $this->repository->getByJenis($jenis);
    }

    /**
     * Get root units (no parent)
     */
    public function getRootUnits(): Collection
    {
        return $this->repository->getRootUnits();
    }

    /**
     * Get children of a unit
     */
    public function getChildren(string $parentId): Collection
    {
        return $this->repository->getChildren($parentId);
    }

    /**
     * Get statistics
     */
    public function getStatistics(): array
    {
        return $this->repository->getStatistics();
    }

    /**
     * Toggle active status
     */
    public function toggleActiveStatus(string $id): UnitKerja
    {
        DB::beginTransaction();
        try {
            $unitKerja = $this->repository->findById($id);

            if (!$unitKerja) {
                throw new \Exception('Unit Kerja tidak ditemukan');
            }

            $unitKerja = $this->repository->update($unitKerja, [
                'is_active' => !$unitKerja->is_active
            ]);

            DB::commit();
            Log::info('Unit Kerja status toggled', [
                'unit_kerja_id' => $id,
                'new_status' => $unitKerja->is_active
            ]);

            return $unitKerja;
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Failed to toggle Unit Kerja status', [
                'error' => $e->getMessage(),
                'unit_kerja_id' => $id
            ]);
            throw $e;
        }
    }
}
