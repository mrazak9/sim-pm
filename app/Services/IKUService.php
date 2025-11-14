<?php

namespace App\Services;

use App\Models\IKU;
use App\Repositories\IKURepository;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class IKUService
{
    protected IKURepository $repository;

    public function __construct(IKURepository $repository)
    {
        $this->repository = $repository;
    }

    /**
     * Get all IKUs with filters
     */
    public function getAllIKUs(array $filters, int $perPage = 15): LengthAwarePaginator
    {
        return $this->repository->getAll($filters, $perPage);
    }

    /**
     * Get IKU by ID
     */
    public function getIKUById(int $id): ?IKU
    {
        return $this->repository->findById($id);
    }

    /**
     * Create new IKU
     */
    public function createIKU(array $data): IKU
    {
        DB::beginTransaction();

        try {
            // Check if kode_iku already exists
            if ($this->repository->codeExists($data['kode_iku'])) {
                throw new \Exception('Kode IKU sudah digunakan');
            }

            $iku = $this->repository->create($data);

            DB::commit();
            Log::info('IKU created successfully', ['iku_id' => $iku->id, 'kode' => $iku->kode_iku]);

            return $iku;
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Failed to create IKU', ['error' => $e->getMessage()]);
            throw $e;
        }
    }

    /**
     * Update existing IKU
     */
    public function updateIKU(int $id, array $data): IKU
    {
        DB::beginTransaction();

        try {
            $iku = $this->repository->findById($id);

            if (!$iku) {
                throw new \Exception('IKU tidak ditemukan');
            }

            // Check if kode_iku already exists (excluding current IKU)
            if (isset($data['kode_iku']) && $this->repository->codeExists($data['kode_iku'], $id)) {
                throw new \Exception('Kode IKU sudah digunakan');
            }

            $iku = $this->repository->update($iku, $data);

            DB::commit();
            Log::info('IKU updated successfully', ['iku_id' => $iku->id]);

            return $iku;
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Failed to update IKU', ['iku_id' => $id, 'error' => $e->getMessage()]);
            throw $e;
        }
    }

    /**
     * Delete IKU
     */
    public function deleteIKU(int $id): bool
    {
        DB::beginTransaction();

        try {
            $iku = $this->repository->findById($id);

            if (!$iku) {
                throw new \Exception('IKU tidak ditemukan');
            }

            // Check if IKU has associated targets
            if ($iku->targets()->count() > 0) {
                throw new \Exception('IKU tidak dapat dihapus karena memiliki target yang terkait');
            }

            $result = $this->repository->delete($iku);

            DB::commit();
            Log::info('IKU deleted successfully', ['iku_id' => $id]);

            return $result;
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Failed to delete IKU', ['iku_id' => $id, 'error' => $e->getMessage()]);
            throw $e;
        }
    }

    /**
     * Get all IKU categories
     */
    public function getCategories(): Collection
    {
        return $this->repository->getCategories();
    }

    /**
     * Get active IKUs only
     */
    public function getActiveIKUs(): Collection
    {
        return $this->repository->getActive();
    }

    /**
     * Get IKU statistics for dashboard
     */
    public function getStatistics(): array
    {
        return $this->repository->getStatistics();
    }

    /**
     * Get IKUs by category
     */
    public function getIKUsByCategory(string $kategori): Collection
    {
        return $this->repository->getByCategory($kategori);
    }

    /**
     * Toggle IKU active status
     */
    public function toggleActiveStatus(int $id): IKU
    {
        DB::beginTransaction();

        try {
            $iku = $this->repository->findById($id);

            if (!$iku) {
                throw new \Exception('IKU tidak ditemukan');
            }

            $iku = $this->repository->update($iku, [
                'is_active' => !$iku->is_active
            ]);

            DB::commit();
            Log::info('IKU active status toggled', ['iku_id' => $id, 'is_active' => $iku->is_active]);

            return $iku;
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Failed to toggle IKU active status', ['iku_id' => $id, 'error' => $e->getMessage()]);
            throw $e;
        }
    }
}
