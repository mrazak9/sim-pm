<?php

namespace App\Services;

use App\Models\RTL;
use App\Models\RTLProgress;
use App\Repositories\RTLRepository;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;
use Exception;

class RTLService
{
    public function __construct(
        private RTLRepository $repository
    ) {}

    /**
     * Get all RTLs with pagination.
     */
    public function getAllPaginated(array $filters = [], int $perPage = 15): LengthAwarePaginator
    {
        return $this->repository->all($filters, $perPage);
    }

    /**
     * Get RTL by ID.
     */
    public function getById(int $id): ?RTL
    {
        return $this->repository->findById($id);
    }

    /**
     * Create new RTL.
     */
    public function create(array $data): RTL
    {
        DB::beginTransaction();
        try {
            // Generate RTL code if not provided
            if (!isset($data['rtl_code'])) {
                $data['rtl_code'] = $this->repository->generateRTLCode();
            }

            // Validate RTL code uniqueness
            if ($this->repository->codeExists($data['rtl_code'])) {
                throw new Exception('RTL code already exists');
            }

            // Set default values
            $data['status'] = $data['status'] ?? 'not_started';
            $data['completion_percentage'] = $data['completion_percentage'] ?? 0;
            $data['risk_level'] = $data['risk_level'] ?? 'medium';

            $rtl = $this->repository->create($data);

            DB::commit();
            Log::info('RTL created', [
                'id' => $rtl->id,
                'code' => $rtl->rtl_code,
                'finding_id' => $rtl->audit_finding_id
            ]);

            return $rtl;
        } catch (Exception $e) {
            DB::rollBack();
            Log::error('Failed to create RTL', ['error' => $e->getMessage()]);
            throw $e;
        }
    }

    /**
     * Update RTL.
     */
    public function update(int $id, array $data): RTL
    {
        DB::beginTransaction();
        try {
            $rtl = $this->repository->findById($id);

            if (!$rtl) {
                throw new Exception('RTL not found');
            }

            // Validate RTL code if changed
            if (isset($data['rtl_code']) && $data['rtl_code'] !== $rtl->rtl_code) {
                if ($this->repository->codeExists($data['rtl_code'], $id)) {
                    throw new Exception('RTL code already exists');
                }
            }

            // Don't allow updates to completed RTLs unless admin
            if ($rtl->status === 'completed' && !auth()->user()->hasRole('admin')) {
                throw new Exception('Cannot update completed RTL');
            }

            $this->repository->update($rtl, $data);

            DB::commit();
            Log::info('RTL updated', ['id' => $id]);

            return $rtl->fresh();
        } catch (Exception $e) {
            DB::rollBack();
            Log::error('Failed to update RTL', ['id' => $id, 'error' => $e->getMessage()]);
            throw $e;
        }
    }

    /**
     * Delete RTL.
     */
    public function delete(int $id): bool
    {
        DB::beginTransaction();
        try {
            $rtl = $this->repository->findById($id);

            if (!$rtl) {
                throw new Exception('RTL not found');
            }

            // Check if RTL can be deleted
            if ($rtl->status === 'completed' || $rtl->verification_status === 'approved') {
                throw new Exception('Cannot delete completed or approved RTL');
            }

            $this->repository->delete($rtl);

            DB::commit();
            Log::info('RTL deleted', ['id' => $id]);

            return true;
        } catch (Exception $e) {
            DB::rollBack();
            Log::error('Failed to delete RTL', ['id' => $id, 'error' => $e->getMessage()]);
            throw $e;
        }
    }

    /**
     * Start RTL execution.
     */
    public function start(int $id): RTL
    {
        DB::beginTransaction();
        try {
            $rtl = $this->repository->findById($id);

            if (!$rtl) {
                throw new Exception('RTL not found');
            }

            if ($rtl->status !== 'not_started') {
                throw new Exception('Only not started RTLs can be started');
            }

            $data = [
                'status' => 'in_progress',
                'started_at' => now(),
            ];

            $this->repository->update($rtl, $data);

            DB::commit();
            Log::info('RTL started', ['id' => $id]);

            return $rtl->fresh();
        } catch (Exception $e) {
            DB::rollBack();
            Log::error('Failed to start RTL', ['id' => $id, 'error' => $e->getMessage()]);
            throw $e;
        }
    }

    /**
     * Complete RTL.
     */
    public function complete(int $id, array $data): RTL
    {
        DB::beginTransaction();
        try {
            $rtl = $this->repository->findById($id);

            if (!$rtl) {
                throw new Exception('RTL not found');
            }

            if ($rtl->status === 'completed') {
                throw new Exception('RTL is already completed');
            }

            if ($rtl->completion_percentage < 100) {
                throw new Exception('RTL completion percentage must be 100%');
            }

            $updateData = [
                'status' => 'completed',
                'completed_at' => now(),
                'completion_notes' => $data['completion_notes'] ?? null,
                'verification_status' => 'pending',
            ];

            $this->repository->update($rtl, $updateData);

            DB::commit();
            Log::info('RTL completed', ['id' => $id]);

            return $rtl->fresh();
        } catch (Exception $e) {
            DB::rollBack();
            Log::error('Failed to complete RTL', ['id' => $id, 'error' => $e->getMessage()]);
            throw $e;
        }
    }

    /**
     * Add progress update to RTL.
     */
    public function addProgress(int $rtlId, array $data): RTLProgress
    {
        DB::beginTransaction();
        try {
            $rtl = $this->repository->findById($rtlId);

            if (!$rtl) {
                throw new Exception('RTL not found');
            }

            if ($rtl->status === 'completed') {
                throw new Exception('Cannot add progress to completed RTL');
            }

            // Handle file upload
            if (isset($data['evidence_file'])) {
                $file = $data['evidence_file'];
                $path = $file->store('rtl-evidence', 'public');

                $data['evidence_file'] = $path;
                $data['evidence_type'] = $file->getClientMimeType();
                $data['evidence_size'] = $file->getSize();
            }

            $data['rtl_id'] = $rtlId;
            $data['reported_by'] = auth()->id();
            $data['progress_date'] = $data['progress_date'] ?? now();

            $progress = RTLProgress::create($data);

            // Auto-update RTL status if started
            if ($rtl->status === 'not_started' && $data['progress_percentage'] > 0) {
                $rtl->update([
                    'status' => 'in_progress',
                    'started_at' => now(),
                ]);
            }

            DB::commit();
            Log::info('RTL progress added', [
                'rtl_id' => $rtlId,
                'progress_id' => $progress->id,
                'percentage' => $progress->progress_percentage
            ]);

            return $progress;
        } catch (Exception $e) {
            DB::rollBack();
            Log::error('Failed to add RTL progress', ['rtl_id' => $rtlId, 'error' => $e->getMessage()]);
            throw $e;
        }
    }

    /**
     * Verify RTL.
     */
    public function verify(int $id, string $verificationStatus, ?string $notes = null): RTL
    {
        DB::beginTransaction();
        try {
            $rtl = $this->repository->findById($id);

            if (!$rtl) {
                throw new Exception('RTL not found');
            }

            if ($rtl->status !== 'completed') {
                throw new Exception('Only completed RTLs can be verified');
            }

            if (!in_array($verificationStatus, ['approved', 'rejected', 'revision'])) {
                throw new Exception('Invalid verification status');
            }

            $data = [
                'verification_status' => $verificationStatus,
                'verified_by' => auth()->id(),
                'verified_at' => now(),
                'verification_notes' => $notes,
            ];

            // If rejected or needs revision, set status back to in_progress
            if (in_array($verificationStatus, ['rejected', 'revision'])) {
                $data['status'] = 'in_progress';
                $data['completed_at'] = null;
            }

            $this->repository->update($rtl, $data);

            DB::commit();
            Log::info('RTL verified', [
                'id' => $id,
                'status' => $verificationStatus,
                'verified_by' => auth()->id()
            ]);

            return $rtl->fresh();
        } catch (Exception $e) {
            DB::rollBack();
            Log::error('Failed to verify RTL', ['id' => $id, 'error' => $e->getMessage()]);
            throw $e;
        }
    }

    /**
     * Get overdue RTLs.
     */
    public function getOverdue(): Collection
    {
        return $this->repository->getOverdue();
    }

    /**
     * Get RTLs due soon.
     */
    public function getDueSoon(int $days = 7): Collection
    {
        return $this->repository->getDueSoon($days);
    }

    /**
     * Get pending verification RTLs.
     */
    public function getPendingVerification(): Collection
    {
        return $this->repository->getPendingVerification();
    }

    /**
     * Get statistics.
     */
    public function getStatistics(): array
    {
        return $this->repository->getStatistics();
    }

    /**
     * Get dashboard statistics.
     */
    public function getDashboardStatistics(): array
    {
        return $this->repository->getDashboardStatistics();
    }

    /**
     * Get statistics by unit kerja.
     */
    public function getStatisticsByUnitKerja(): array
    {
        return $this->repository->getStatisticsByUnitKerja();
    }
}
