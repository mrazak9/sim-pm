<?php

namespace App\Services;

use App\Models\ProgramStudi;
use App\Repositories\ProgramStudiRepository;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class ProgramStudiService
{
    protected ProgramStudiRepository $repository;

    public function __construct(ProgramStudiRepository $repository)
    {
        $this->repository = $repository;
    }

    /**
     * Get all program studi with filters and pagination
     */
    public function getAllProgramStudi(array $filters = [], int $perPage = 15): LengthAwarePaginator
    {
        return $this->repository->getAll($filters, $perPage);
    }

    /**
     * Get program studi by ID
     */
    public function getProgramStudiById(string $id): ?ProgramStudi
    {
        return $this->repository->findById($id, ['unitKerja']);
    }

    /**
     * Create new program studi
     */
    public function createProgramStudi(array $data): ProgramStudi
    {
        DB::beginTransaction();
        try {
            // Check if kode already exists
            if ($this->repository->kodeExists($data['kode_prodi'])) {
                throw new \Exception('Kode Program Studi sudah digunakan');
            }

            $programStudi = $this->repository->create($data);

            DB::commit();
            Log::info('Program Studi created successfully', ['program_studi_id' => $programStudi->id]);

            return $programStudi->load('unitKerja');
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Failed to create Program Studi', [
                'error' => $e->getMessage(),
                'data' => $data
            ]);
            throw $e;
        }
    }

    /**
     * Update program studi
     */
    public function updateProgramStudi(string $id, array $data): ProgramStudi
    {
        DB::beginTransaction();
        try {
            $programStudi = $this->repository->findById($id);

            if (!$programStudi) {
                throw new \Exception('Program Studi tidak ditemukan');
            }

            // Check if kode already exists (except current record)
            if ($this->repository->kodeExists($data['kode_prodi'], $id)) {
                throw new \Exception('Kode Program Studi sudah digunakan');
            }

            $programStudi = $this->repository->update($programStudi, $data);

            DB::commit();
            Log::info('Program Studi updated successfully', ['program_studi_id' => $id]);

            return $programStudi;
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Failed to update Program Studi', [
                'error' => $e->getMessage(),
                'program_studi_id' => $id
            ]);
            throw $e;
        }
    }

    /**
     * Delete program studi
     */
    public function deleteProgramStudi(string $id): void
    {
        DB::beginTransaction();
        try {
            $programStudi = $this->repository->findById($id);

            if (!$programStudi) {
                throw new \Exception('Program Studi tidak ditemukan');
            }

            $this->repository->delete($programStudi);

            DB::commit();
            Log::info('Program Studi deleted successfully', ['program_studi_id' => $id]);
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Failed to delete Program Studi', [
                'error' => $e->getMessage(),
                'program_studi_id' => $id
            ]);
            throw $e;
        }
    }

    /**
     * Get active program studi
     */
    public function getActiveProgramStudi(): Collection
    {
        return $this->repository->getActive();
    }

    /**
     * Get program studi by jenjang
     */
    public function getProgramStudiByJenjang(string $jenjang): Collection
    {
        return $this->repository->getByJenjang($jenjang);
    }

    /**
     * Get program studi by unit kerja
     */
    public function getProgramStudiByUnitKerja(string $unitKerjaId): Collection
    {
        return $this->repository->getByUnitKerja($unitKerjaId);
    }

    /**
     * Get program studi by akreditasi
     */
    public function getProgramStudiByAkreditasi(string $akreditasi): Collection
    {
        return $this->repository->getByAkreditasi($akreditasi);
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
    public function toggleActiveStatus(string $id): ProgramStudi
    {
        DB::beginTransaction();
        try {
            $programStudi = $this->repository->findById($id);

            if (!$programStudi) {
                throw new \Exception('Program Studi tidak ditemukan');
            }

            $programStudi = $this->repository->update($programStudi, [
                'is_active' => !$programStudi->is_active
            ]);

            DB::commit();
            Log::info('Program Studi status toggled', [
                'program_studi_id' => $id,
                'new_status' => $programStudi->is_active
            ]);

            return $programStudi;
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Failed to toggle Program Studi status', [
                'error' => $e->getMessage(),
                'program_studi_id' => $id
            ]);
            throw $e;
        }
    }
}
