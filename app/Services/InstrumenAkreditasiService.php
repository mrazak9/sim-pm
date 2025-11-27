<?php

namespace App\Services;

use App\Models\InstrumenAkreditasi;
use App\Repositories\InstrumenAkreditasiRepository;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class InstrumenAkreditasiService
{
    protected InstrumenAkreditasiRepository $repository;

    public function __construct(InstrumenAkreditasiRepository $repository)
    {
        $this->repository = $repository;
    }

    /**
     * Get all instrumen with filters and pagination
     */
    public function getAllInstrumen(
        array $filters = [],
        int $perPage = 15,
        string $sortBy = 'tahun_berlaku',
        string $sortOrder = 'desc'
    ): LengthAwarePaginator {
        return $this->repository->paginate($filters, $perPage, $sortBy, $sortOrder);
    }

    /**
     * Get active instrumen
     */
    public function getActiveInstrumen(): Collection
    {
        return $this->repository->getActive();
    }

    /**
     * Get instrumen by jenis
     */
    public function getByJenis(string $jenis): Collection
    {
        return $this->repository->getByJenis($jenis);
    }

    /**
     * Get instrumen by lembaga
     */
    public function getByLembaga(string $lembaga): Collection
    {
        return $this->repository->getByLembaga($lembaga);
    }

    /**
     * Get instrumen by ID
     */
    public function getInstrumenById(int $id): ?InstrumenAkreditasi
    {
        return $this->repository->findById($id);
    }

    /**
     * Create new instrumen
     */
    public function createInstrumen(array $data): InstrumenAkreditasi
    {
        DB::beginTransaction();
        try {
            // Validate unique kode
            if ($this->repository->kodeExists($data['kode'])) {
                throw new \Exception('Kode instrumen sudah digunakan');
            }

            $instrumen = $this->repository->create($data);

            DB::commit();

            Log::info('Instrumen akreditasi created', [
                'instrumen_id' => $instrumen->id,
                'kode' => $instrumen->kode,
                'nama' => $instrumen->nama,
            ]);

            return $instrumen;
        } catch (\Exception $e) {
            DB::rollBack();

            Log::error('Failed to create instrumen akreditasi', [
                'error' => $e->getMessage(),
                'data' => $data,
            ]);

            throw $e;
        }
    }

    /**
     * Update instrumen
     */
    public function updateInstrumen(int $id, array $data): InstrumenAkreditasi
    {
        DB::beginTransaction();
        try {
            // Validate unique kode (excluding current record)
            if (isset($data['kode']) && $this->repository->kodeExists($data['kode'], $id)) {
                throw new \Exception('Kode instrumen sudah digunakan');
            }

            $instrumen = $this->repository->update($id, $data);

            DB::commit();

            Log::info('Instrumen akreditasi updated', [
                'instrumen_id' => $instrumen->id,
                'kode' => $instrumen->kode,
                'changes' => $data,
            ]);

            return $instrumen;
        } catch (\Exception $e) {
            DB::rollBack();

            Log::error('Failed to update instrumen akreditasi', [
                'instrumen_id' => $id,
                'error' => $e->getMessage(),
                'data' => $data,
            ]);

            throw $e;
        }
    }

    /**
     * Delete instrumen
     */
    public function deleteInstrumen(int $id): bool
    {
        DB::beginTransaction();
        try {
            $instrumen = $this->repository->findById($id);

            if (!$instrumen) {
                throw new \Exception('Instrumen akreditasi tidak ditemukan');
            }

            // Check if instrumen is used in any periode akreditasi
            if ($instrumen->periodeAkreditasi()->exists()) {
                throw new \Exception('Instrumen tidak dapat dihapus karena sudah digunakan di periode akreditasi');
            }

            $deleted = $this->repository->delete($id);

            DB::commit();

            Log::info('Instrumen akreditasi deleted', [
                'instrumen_id' => $id,
                'kode' => $instrumen->kode,
            ]);

            return $deleted;
        } catch (\Exception $e) {
            DB::rollBack();

            Log::error('Failed to delete instrumen akreditasi', [
                'instrumen_id' => $id,
                'error' => $e->getMessage(),
            ]);

            throw $e;
        }
    }

    /**
     * Toggle active status
     */
    public function toggleActive(int $id): InstrumenAkreditasi
    {
        DB::beginTransaction();
        try {
            $instrumen = $this->repository->toggleActive($id);

            DB::commit();

            Log::info('Instrumen akreditasi active status toggled', [
                'instrumen_id' => $instrumen->id,
                'is_active' => $instrumen->is_active,
            ]);

            return $instrumen;
        } catch (\Exception $e) {
            DB::rollBack();

            Log::error('Failed to toggle instrumen akreditasi active status', [
                'instrumen_id' => $id,
                'error' => $e->getMessage(),
            ]);

            throw $e;
        }
    }

    /**
     * Get statistics
     */
    public function getStatistics(): array
    {
        try {
            return $this->repository->getStatistics();
        } catch (\Exception $e) {
            Log::error('Failed to get instrumen akreditasi statistics', [
                'error' => $e->getMessage(),
            ]);

            throw $e;
        }
    }
}
