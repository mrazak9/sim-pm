<?php

namespace App\Services;

use App\Models\TahunAkademik;
use App\Repositories\TahunAkademikRepository;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class TahunAkademikService
{
    protected TahunAkademikRepository $repository;

    public function __construct(TahunAkademikRepository $repository)
    {
        $this->repository = $repository;
    }

    /**
     * Get all tahun akademik with filters and pagination
     */
    public function getAllTahunAkademik(array $filters = [], int $perPage = 15): LengthAwarePaginator
    {
        return $this->repository->getAll($filters, $perPage);
    }

    /**
     * Get tahun akademik by ID
     */
    public function getTahunAkademikById(string $id): ?TahunAkademik
    {
        return $this->repository->findById($id);
    }

    /**
     * Create new tahun akademik
     */
    public function createTahunAkademik(array $data): TahunAkademik
    {
        DB::beginTransaction();
        try {
            // Check if kode already exists
            if ($this->repository->kodeExists($data['kode_tahun'])) {
                throw new \Exception('Kode Tahun Akademik sudah digunakan');
            }

            // Check for date overlap
            if ($this->repository->hasOverlap($data['tanggal_mulai'], $data['tanggal_selesai'])) {
                throw new \Exception('Periode tahun akademik tumpang tindih dengan periode yang sudah ada');
            }

            $tahunAkademik = $this->repository->create($data);

            DB::commit();
            Log::info('Tahun Akademik created successfully', ['tahun_akademik_id' => $tahunAkademik->id]);

            return $tahunAkademik;
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Failed to create Tahun Akademik', [
                'error' => $e->getMessage(),
                'data' => $data
            ]);
            throw $e;
        }
    }

    /**
     * Update tahun akademik
     */
    public function updateTahunAkademik(string $id, array $data): TahunAkademik
    {
        DB::beginTransaction();
        try {
            $tahunAkademik = $this->repository->findById($id);

            if (!$tahunAkademik) {
                throw new \Exception('Tahun Akademik tidak ditemukan');
            }

            // Check if kode already exists (except current record)
            if ($this->repository->kodeExists($data['kode_tahun'], $id)) {
                throw new \Exception('Kode Tahun Akademik sudah digunakan');
            }

            // Check for date overlap (except current record)
            if ($this->repository->hasOverlap($data['tanggal_mulai'], $data['tanggal_selesai'], $id)) {
                throw new \Exception('Periode tahun akademik tumpang tindih dengan periode yang sudah ada');
            }

            $tahunAkademik = $this->repository->update($tahunAkademik, $data);

            DB::commit();
            Log::info('Tahun Akademik updated successfully', ['tahun_akademik_id' => $id]);

            return $tahunAkademik;
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Failed to update Tahun Akademik', [
                'error' => $e->getMessage(),
                'tahun_akademik_id' => $id
            ]);
            throw $e;
        }
    }

    /**
     * Delete tahun akademik
     */
    public function deleteTahunAkademik(string $id): void
    {
        DB::beginTransaction();
        try {
            $tahunAkademik = $this->repository->findById($id);

            if (!$tahunAkademik) {
                throw new \Exception('Tahun Akademik tidak ditemukan');
            }

            $this->repository->delete($tahunAkademik);

            DB::commit();
            Log::info('Tahun Akademik deleted successfully', ['tahun_akademik_id' => $id]);
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Failed to delete Tahun Akademik', [
                'error' => $e->getMessage(),
                'tahun_akademik_id' => $id
            ]);
            throw $e;
        }
    }

    /**
     * Get active tahun akademik
     */
    public function getActiveTahunAkademik(): Collection
    {
        return $this->repository->getActive();
    }

    /**
     * Get current tahun akademik
     */
    public function getCurrentTahunAkademik(): ?TahunAkademik
    {
        return $this->repository->getCurrent();
    }

    /**
     * Get upcoming tahun akademik
     */
    public function getUpcomingTahunAkademik(): ?TahunAkademik
    {
        return $this->repository->getUpcoming();
    }

    /**
     * Get tahun akademik by semester
     */
    public function getTahunAkademikBySemester(string $semester): Collection
    {
        return $this->repository->getBySemester($semester);
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
    public function toggleActiveStatus(string $id): TahunAkademik
    {
        DB::beginTransaction();
        try {
            $tahunAkademik = $this->repository->findById($id);

            if (!$tahunAkademik) {
                throw new \Exception('Tahun Akademik tidak ditemukan');
            }

            $tahunAkademik = $this->repository->update($tahunAkademik, [
                'is_active' => !$tahunAkademik->is_active
            ]);

            DB::commit();
            Log::info('Tahun Akademik status toggled', [
                'tahun_akademik_id' => $id,
                'new_status' => $tahunAkademik->is_active
            ]);

            return $tahunAkademik;
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Failed to toggle Tahun Akademik status', [
                'error' => $e->getMessage(),
                'tahun_akademik_id' => $id
            ]);
            throw $e;
        }
    }
}
