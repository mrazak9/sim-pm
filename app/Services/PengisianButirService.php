<?php

namespace App\Services;

use App\Models\PengisianButir;
use App\Repositories\PengisianButirRepository;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Auth;

class PengisianButirService
{
    protected PengisianButirRepository $repository;

    public function __construct(PengisianButirRepository $repository)
    {
        $this->repository = $repository;
    }

    /**
     * Get all pengisian butir
     */
    public function all(): Collection
    {
        return $this->repository->all();
    }

    /**
     * Get all pengisian butir with filters and pagination
     */
    public function paginate(array $filters = [], int $perPage = 15): LengthAwarePaginator
    {
        return $this->repository->paginate($filters, $perPage);
    }

    /**
     * Get pengisian butir by ID
     */
    public function findById(int $id): ?PengisianButir
    {
        return $this->repository->findById($id);
    }

    /**
     * Create new pengisian butir
     */
    public function create(array $data): PengisianButir
    {
        DB::beginTransaction();

        try {
            // Check if pengisian already exists for this periode and butir
            if (isset($data['periode_akreditasi_id']) && isset($data['butir_akreditasi_id'])) {
                if ($this->repository->existsForPeriodeAndButir(
                    $data['periode_akreditasi_id'],
                    $data['butir_akreditasi_id']
                )) {
                    throw new \Exception('Pengisian butir untuk periode dan butir ini sudah ada');
                }
            }

            // Set default status
            if (!isset($data['status'])) {
                $data['status'] = 'draft';
            }

            // Set default is_complete
            if (!isset($data['is_complete'])) {
                $data['is_complete'] = false;
            }

            // Calculate initial completion percentage
            $data['completion_percentage'] = $this->calculateCompletionPercentage($data);

            $pengisianButir = $this->repository->create($data);

            DB::commit();
            Log::info('Pengisian Butir created successfully', [
                'pengisian_id' => $pengisianButir->id,
                'periode_id' => $pengisianButir->periode_akreditasi_id,
                'butir_id' => $pengisianButir->butir_akreditasi_id
            ]);

            return $pengisianButir;
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Failed to create Pengisian Butir', [
                'error' => $e->getMessage(),
                'data' => $data
            ]);
            throw $e;
        }
    }

    /**
     * Update pengisian butir
     */
    public function update(int $id, array $data): PengisianButir
    {
        DB::beginTransaction();

        try {
            $pengisianButir = $this->repository->findById($id);

            if (!$pengisianButir) {
                throw new \Exception('Pengisian Butir tidak ditemukan');
            }

            // Only allow updates if status is draft or revision
            if (!in_array($pengisianButir->status, ['draft', 'revision'])) {
                throw new \Exception('Pengisian butir hanya dapat diubah jika statusnya draft atau revision');
            }

            // Recalculate completion percentage
            $mergedData = array_merge($pengisianButir->toArray(), $data);
            $data['completion_percentage'] = $this->calculateCompletionPercentage($mergedData);

            // Auto-mark as complete if completion is 100%
            if ($data['completion_percentage'] >= 100) {
                $data['is_complete'] = true;
            }

            $pengisianButir = $this->repository->update($pengisianButir, $data);

            DB::commit();
            Log::info('Pengisian Butir updated successfully', [
                'pengisian_id' => $id,
                'completion' => $pengisianButir->completion_percentage
            ]);

            return $pengisianButir;
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Failed to update Pengisian Butir', [
                'error' => $e->getMessage(),
                'pengisian_id' => $id
            ]);
            throw $e;
        }
    }

    /**
     * Delete pengisian butir
     */
    public function delete(int $id): bool
    {
        DB::beginTransaction();

        try {
            $pengisianButir = $this->repository->findById($id);

            if (!$pengisianButir) {
                throw new \Exception('Pengisian Butir tidak ditemukan');
            }

            // Only allow deletion if status is draft
            if ($pengisianButir->status !== 'draft') {
                throw new \Exception('Hanya pengisian butir dengan status draft yang dapat dihapus');
            }

            $result = $this->repository->delete($pengisianButir);

            DB::commit();
            Log::info('Pengisian Butir deleted successfully', ['pengisian_id' => $id]);

            return $result;
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Failed to delete Pengisian Butir', [
                'error' => $e->getMessage(),
                'pengisian_id' => $id
            ]);
            throw $e;
        }
    }

    /**
     * Submit pengisian butir for review
     */
    public function submit(int $id): PengisianButir
    {
        DB::beginTransaction();

        try {
            $pengisianButir = $this->repository->findById($id);

            if (!$pengisianButir) {
                throw new \Exception('Pengisian Butir tidak ditemukan');
            }

            // Validate current status
            if (!in_array($pengisianButir->status, ['draft', 'revision'])) {
                throw new \Exception('Hanya pengisian dengan status draft atau revision yang dapat disubmit');
            }

            // Validate completion
            if (!$pengisianButir->is_complete) {
                throw new \Exception('Pengisian butir belum lengkap. Harap lengkapi semua field yang diperlukan');
            }

            // Validate required fields
            if (empty($pengisianButir->konten)) {
                throw new \Exception('Konten pengisian tidak boleh kosong');
            }

            $pengisianButir = $this->repository->update($pengisianButir, [
                'status' => 'submitted',
                'submitted_at' => now()
            ]);

            DB::commit();
            Log::info('Pengisian Butir submitted successfully', [
                'pengisian_id' => $id,
                'submitted_by' => $pengisianButir->pic_user_id
            ]);

            return $pengisianButir;
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Failed to submit Pengisian Butir', [
                'error' => $e->getMessage(),
                'pengisian_id' => $id
            ]);
            throw $e;
        }
    }

    /**
     * Approve pengisian butir
     */
    public function approve(int $id, ?string $notes = null): PengisianButir
    {
        DB::beginTransaction();

        try {
            $pengisianButir = $this->repository->findById($id);

            if (!$pengisianButir) {
                throw new \Exception('Pengisian Butir tidak ditemukan');
            }

            // Validate current status
            if (!in_array($pengisianButir->status, ['submitted', 'review'])) {
                throw new \Exception('Hanya pengisian dengan status submitted atau review yang dapat diapprove');
            }

            $pengisianButir = $this->repository->update($pengisianButir, [
                'status' => 'approved',
                'reviewed_by' => Auth::id(),
                'reviewed_at' => now(),
                'review_notes' => $notes
            ]);

            DB::commit();
            Log::info('Pengisian Butir approved successfully', [
                'pengisian_id' => $id,
                'reviewed_by' => Auth::id()
            ]);

            return $pengisianButir;
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Failed to approve Pengisian Butir', [
                'error' => $e->getMessage(),
                'pengisian_id' => $id
            ]);
            throw $e;
        }
    }

    /**
     * Reject pengisian butir
     */
    public function reject(int $id, string $notes): PengisianButir
    {
        DB::beginTransaction();

        try {
            $pengisianButir = $this->repository->findById($id);

            if (!$pengisianButir) {
                throw new \Exception('Pengisian Butir tidak ditemukan');
            }

            // Validate current status
            if (!in_array($pengisianButir->status, ['submitted', 'review'])) {
                throw new \Exception('Hanya pengisian dengan status submitted atau review yang dapat direject');
            }

            // Notes are required for rejection
            if (empty($notes)) {
                throw new \Exception('Catatan review harus diisi saat melakukan reject');
            }

            $pengisianButir = $this->repository->update($pengisianButir, [
                'status' => 'revision',
                'reviewed_by' => Auth::id(),
                'reviewed_at' => now(),
                'review_notes' => $notes
            ]);

            DB::commit();
            Log::info('Pengisian Butir rejected successfully', [
                'pengisian_id' => $id,
                'reviewed_by' => Auth::id()
            ]);

            return $pengisianButir;
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Failed to reject Pengisian Butir', [
                'error' => $e->getMessage(),
                'pengisian_id' => $id
            ]);
            throw $e;
        }
    }

    /**
     * Mark pengisian for review
     */
    public function revise(int $id): PengisianButir
    {
        DB::beginTransaction();

        try {
            $pengisianButir = $this->repository->findById($id);

            if (!$pengisianButir) {
                throw new \Exception('Pengisian Butir tidak ditemukan');
            }

            // Only submitted pengisian can be moved to review
            if ($pengisianButir->status !== 'submitted') {
                throw new \Exception('Hanya pengisian dengan status submitted yang dapat masuk tahap review');
            }

            $pengisianButir = $this->repository->update($pengisianButir, [
                'status' => 'review',
                'reviewed_by' => Auth::id(),
                'reviewed_at' => now()
            ]);

            DB::commit();
            Log::info('Pengisian Butir moved to review', [
                'pengisian_id' => $id,
                'reviewed_by' => Auth::id()
            ]);

            return $pengisianButir;
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Failed to move Pengisian Butir to review', [
                'error' => $e->getMessage(),
                'pengisian_id' => $id
            ]);
            throw $e;
        }
    }

    /**
     * Get summary for a periode
     */
    public function getSummary(int $periodeId): array
    {
        return $this->repository->getSummary($periodeId);
    }

    /**
     * Get pengisian by periode
     */
    public function getByPeriode(int $periodeId): Collection
    {
        return $this->repository->getByPeriode($periodeId);
    }

    /**
     * Get pengisian by status
     */
    public function getByStatus(string $status): Collection
    {
        return $this->repository->getByStatus($status);
    }

    /**
     * Get pengisian for review
     */
    public function getForReview(): Collection
    {
        return $this->repository->getForReview();
    }

    /**
     * Get statistics
     */
    public function getStatistics(): array
    {
        return $this->repository->getStatistics();
    }

    /**
     * Calculate completion percentage
     */
    protected function calculateCompletionPercentage(array $data): float
    {
        $requiredFields = ['konten', 'konten_plain'];
        $filledFields = 0;
        $totalFields = count($requiredFields);

        foreach ($requiredFields as $field) {
            if (isset($data[$field]) && !empty($data[$field])) {
                $filledFields++;
            }
        }

        // If is_complete is explicitly set to true, return 100%
        if (isset($data['is_complete']) && $data['is_complete'] === true) {
            return 100.0;
        }

        return $totalFields > 0 ? round(($filledFields / $totalFields) * 100, 2) : 0.0;
    }
}
