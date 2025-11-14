<?php

namespace App\Services;

use App\Models\PeriodeAkreditasi;
use App\Repositories\PeriodeAkreditasiRepository;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Carbon\Carbon;

class PeriodeAkreditasiService
{
    protected PeriodeAkreditasiRepository $repository;

    public function __construct(PeriodeAkreditasiRepository $repository)
    {
        $this->repository = $repository;
    }

    /**
     * Get all periode akreditasi
     */
    public function all(): Collection
    {
        return $this->repository->all();
    }

    /**
     * Get all periode akreditasi with filters and pagination
     */
    public function paginate(array $filters = [], int $perPage = 15): LengthAwarePaginator
    {
        return $this->repository->paginate($filters, $perPage);
    }

    /**
     * Get periode akreditasi by ID
     */
    public function findById(int $id): ?PeriodeAkreditasi
    {
        return $this->repository->findById($id);
    }

    /**
     * Create new periode akreditasi
     */
    public function create(array $data): PeriodeAkreditasi
    {
        DB::beginTransaction();

        try {
            // Validate dates
            $this->validateDates($data);

            // Check for overlapping periods
            if ($this->checkOverlap($data)) {
                throw new \Exception('Periode akreditasi bertumpang tindih dengan periode lain');
            }

            // Validate program_studi_id for prodi akreditasi
            if ($data['jenis_akreditasi'] === 'prodi' && !isset($data['program_studi_id'])) {
                throw new \Exception('Program Studi harus dipilih untuk akreditasi prodi');
            }

            // Set default status if not provided
            if (!isset($data['status'])) {
                $data['status'] = 'persiapan';
            }

            $periodeAkreditasi = $this->repository->create($data);

            DB::commit();
            Log::info('Periode Akreditasi created successfully', [
                'periode_id' => $periodeAkreditasi->id,
                'nama' => $periodeAkreditasi->nama
            ]);

            return $periodeAkreditasi;
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Failed to create Periode Akreditasi', [
                'error' => $e->getMessage(),
                'data' => $data
            ]);
            throw $e;
        }
    }

    /**
     * Update periode akreditasi
     */
    public function update(int $id, array $data): PeriodeAkreditasi
    {
        DB::beginTransaction();

        try {
            $periodeAkreditasi = $this->repository->findById($id);

            if (!$periodeAkreditasi) {
                throw new \Exception('Periode Akreditasi tidak ditemukan');
            }

            // Validate dates if provided
            if (isset($data['tanggal_mulai']) || isset($data['tanggal_selesai'])) {
                $this->validateDates($data, $periodeAkreditasi);
            }

            // Check for overlapping periods (excluding current)
            if ($this->checkOverlap($data, $id)) {
                throw new \Exception('Periode akreditasi bertumpang tindih dengan periode lain');
            }

            // Validate status transitions
            if (isset($data['status'])) {
                $this->validateStatusTransition($periodeAkreditasi->status, $data['status']);
            }

            $periodeAkreditasi = $this->repository->update($periodeAkreditasi, $data);

            DB::commit();
            Log::info('Periode Akreditasi updated successfully', [
                'periode_id' => $id,
                'nama' => $periodeAkreditasi->nama
            ]);

            return $periodeAkreditasi;
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Failed to update Periode Akreditasi', [
                'error' => $e->getMessage(),
                'periode_id' => $id
            ]);
            throw $e;
        }
    }

    /**
     * Delete periode akreditasi
     */
    public function delete(int $id): bool
    {
        DB::beginTransaction();

        try {
            $periodeAkreditasi = $this->repository->findById($id);

            if (!$periodeAkreditasi) {
                throw new \Exception('Periode Akreditasi tidak ditemukan');
            }

            // Check if has associated pengisian butir
            if ($periodeAkreditasi->pengisianButirs()->count() > 0) {
                throw new \Exception('Periode Akreditasi tidak dapat dihapus karena memiliki pengisian butir');
            }

            // Check if has associated dokumen
            if ($periodeAkreditasi->dokumenAkreditasis()->count() > 0) {
                throw new \Exception('Periode Akreditasi tidak dapat dihapus karena memiliki dokumen');
            }

            $result = $this->repository->delete($periodeAkreditasi);

            DB::commit();
            Log::info('Periode Akreditasi deleted successfully', ['periode_id' => $id]);

            return $result;
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Failed to delete Periode Akreditasi', [
                'error' => $e->getMessage(),
                'periode_id' => $id
            ]);
            throw $e;
        }
    }

    /**
     * Get active periode akreditasi
     */
    public function getActive(): Collection
    {
        return $this->repository->getActive();
    }

    /**
     * Get periode akreditasi statistics
     */
    public function getStatistics(): array
    {
        return $this->repository->getStatistics();
    }

    /**
     * Get periode by program studi
     */
    public function getByProgram(int $programStudiId): Collection
    {
        return $this->repository->getByProgram($programStudiId);
    }

    /**
     * Get periode by lembaga
     */
    public function getByLembaga(string $lembaga): Collection
    {
        return $this->repository->getByLembaga($lembaga);
    }

    /**
     * Get periode institusi
     */
    public function getInstitusi(): Collection
    {
        return $this->repository->getInstitusi();
    }

    /**
     * Get periode prodi
     */
    public function getProdi(): Collection
    {
        return $this->repository->getProdi();
    }

    /**
     * Get expired periode
     */
    public function getExpired(): Collection
    {
        return $this->repository->getExpired();
    }

    /**
     * Get upcoming periode
     */
    public function getUpcoming(int $days = 30): Collection
    {
        return $this->repository->getUpcoming($days);
    }

    /**
     * Validate dates
     */
    protected function validateDates(array $data, ?PeriodeAkreditasi $existing = null): void
    {
        $tanggalMulai = isset($data['tanggal_mulai'])
            ? Carbon::parse($data['tanggal_mulai'])
            : ($existing ? Carbon::parse($existing->tanggal_mulai) : null);

        $tanggalSelesai = isset($data['tanggal_selesai'])
            ? Carbon::parse($data['tanggal_selesai'])
            : ($existing ? Carbon::parse($existing->tanggal_selesai) : null);

        $deadlinePengumpulan = isset($data['deadline_pengumpulan'])
            ? Carbon::parse($data['deadline_pengumpulan'])
            : ($existing && $existing->deadline_pengumpulan ? Carbon::parse($existing->deadline_pengumpulan) : null);

        if ($tanggalMulai && $tanggalSelesai && $tanggalSelesai->lt($tanggalMulai)) {
            throw new \Exception('Tanggal selesai harus lebih besar dari tanggal mulai');
        }

        if ($deadlinePengumpulan && $tanggalSelesai && $deadlinePengumpulan->gt($tanggalSelesai)) {
            throw new \Exception('Deadline pengumpulan tidak boleh melewati tanggal selesai');
        }

        if ($deadlinePengumpulan && $tanggalMulai && $deadlinePengumpulan->lt($tanggalMulai)) {
            throw new \Exception('Deadline pengumpulan harus setelah tanggal mulai');
        }
    }

    /**
     * Check for overlapping periods
     */
    protected function checkOverlap(array $data, ?int $exceptId = null): bool
    {
        if (!isset($data['tanggal_mulai']) || !isset($data['tanggal_selesai'])) {
            return false;
        }

        $query = PeriodeAkreditasi::query()
            ->where(function($q) use ($data) {
                $q->whereBetween('tanggal_mulai', [$data['tanggal_mulai'], $data['tanggal_selesai']])
                  ->orWhereBetween('tanggal_selesai', [$data['tanggal_mulai'], $data['tanggal_selesai']])
                  ->orWhere(function($q2) use ($data) {
                      $q2->where('tanggal_mulai', '<=', $data['tanggal_mulai'])
                         ->where('tanggal_selesai', '>=', $data['tanggal_selesai']);
                  });
            });

        // Filter by same jenis_akreditasi
        if (isset($data['jenis_akreditasi'])) {
            $query->where('jenis_akreditasi', $data['jenis_akreditasi']);
        }

        // For prodi, check overlap only within same program_studi_id
        if (isset($data['program_studi_id'])) {
            $query->where('program_studi_id', $data['program_studi_id']);
        }

        // Exclude current record when updating
        if ($exceptId) {
            $query->where('id', '!=', $exceptId);
        }

        return $query->exists();
    }

    /**
     * Validate status transitions
     */
    protected function validateStatusTransition(string $currentStatus, string $newStatus): void
    {
        $validTransitions = [
            'persiapan' => ['pengisian', 'ditunda'],
            'pengisian' => ['review', 'ditunda'],
            'review' => ['selesai', 'pengisian'],
            'selesai' => [],
            'ditunda' => ['persiapan', 'pengisian'],
        ];

        if (!isset($validTransitions[$currentStatus])) {
            throw new \Exception('Status saat ini tidak valid');
        }

        if ($currentStatus === $newStatus) {
            return; // Allow same status
        }

        if (!in_array($newStatus, $validTransitions[$currentStatus])) {
            throw new \Exception("Transisi status dari '{$currentStatus}' ke '{$newStatus}' tidak diizinkan");
        }
    }
}
