<?php

namespace App\Services;

use App\Models\Jabatan;
use App\Repositories\JabatanRepository;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class JabatanService
{
    protected JabatanRepository $repository;

    public function __construct(JabatanRepository $repository)
    {
        $this->repository = $repository;
    }

    /**
     * Get all jabatan with filters and pagination
     */
    public function getAllJabatan(array $filters = [], int $perPage = 15): LengthAwarePaginator
    {
        return $this->repository->getAll($filters, $perPage);
    }

    /**
     * Get jabatan by ID
     */
    public function getJabatanById(string $id): ?Jabatan
    {
        return $this->repository->findById($id);
    }

    /**
     * Create new jabatan
     */
    public function createJabatan(array $data): Jabatan
    {
        DB::beginTransaction();
        try {
            // Check if kode already exists
            if ($this->repository->kodeExists($data['kode_jabatan'])) {
                throw new \Exception('Kode Jabatan sudah digunakan');
            }

            $jabatan = $this->repository->create($data);

            DB::commit();
            Log::info('Jabatan created successfully', ['jabatan_id' => $jabatan->id]);

            return $jabatan;
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Failed to create Jabatan', [
                'error' => $e->getMessage(),
                'data' => $data
            ]);
            throw $e;
        }
    }

    /**
     * Update jabatan
     */
    public function updateJabatan(string $id, array $data): Jabatan
    {
        DB::beginTransaction();
        try {
            $jabatan = $this->repository->findById($id);

            if (!$jabatan) {
                throw new \Exception('Jabatan tidak ditemukan');
            }

            // Check if kode already exists (except current record)
            if ($this->repository->kodeExists($data['kode_jabatan'], $id)) {
                throw new \Exception('Kode Jabatan sudah digunakan');
            }

            $jabatan = $this->repository->update($jabatan, $data);

            DB::commit();
            Log::info('Jabatan updated successfully', ['jabatan_id' => $id]);

            return $jabatan;
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Failed to update Jabatan', [
                'error' => $e->getMessage(),
                'jabatan_id' => $id
            ]);
            throw $e;
        }
    }

    /**
     * Delete jabatan
     */
    public function deleteJabatan(string $id): void
    {
        DB::beginTransaction();
        try {
            $jabatan = $this->repository->findById($id);

            if (!$jabatan) {
                throw new \Exception('Jabatan tidak ditemukan');
            }

            $this->repository->delete($jabatan);

            DB::commit();
            Log::info('Jabatan deleted successfully', ['jabatan_id' => $id]);
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Failed to delete Jabatan', [
                'error' => $e->getMessage(),
                'jabatan_id' => $id
            ]);
            throw $e;
        }
    }

    /**
     * Get active jabatan
     */
    public function getActiveJabatan(): Collection
    {
        return $this->repository->getActive();
    }

    /**
     * Get jabatan by kategori
     */
    public function getJabatanByKategori(string $kategori): Collection
    {
        return $this->repository->getByKategori($kategori);
    }

    /**
     * Get jabatan by level
     */
    public function getJabatanByLevel(int $level): Collection
    {
        return $this->repository->getByLevel($level);
    }

    /**
     * Get unique categories
     */
    public function getCategories(): array
    {
        return $this->repository->getCategories();
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
    public function toggleActiveStatus(string $id): Jabatan
    {
        DB::beginTransaction();
        try {
            $jabatan = $this->repository->findById($id);

            if (!$jabatan) {
                throw new \Exception('Jabatan tidak ditemukan');
            }

            $jabatan = $this->repository->update($jabatan, [
                'is_active' => !$jabatan->is_active
            ]);

            DB::commit();
            Log::info('Jabatan status toggled', [
                'jabatan_id' => $id,
                'new_status' => $jabatan->is_active
            ]);

            return $jabatan;
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Failed to toggle Jabatan status', [
                'error' => $e->getMessage(),
                'jabatan_id' => $id
            ]);
            throw $e;
        }
    }
}
