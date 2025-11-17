<?php

namespace App\Services;

use App\Models\SpmiStandard;
use App\Repositories\SpmiStandardRepository;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Exception;

class SpmiStandardService
{
    public function __construct(
        private SpmiStandardRepository $repository
    ) {}

    /**
     * Get all SPMI standards with pagination.
     */
    public function getAllPaginated(array $filters = [], int $perPage = 15): LengthAwarePaginator
    {
        return $this->repository->all($filters, $perPage);
    }

    /**
     * Get SPMI standard by ID.
     */
    public function getById(int $id): ?SpmiStandard
    {
        return $this->repository->findById($id);
    }

    /**
     * Create new SPMI standard with auto-code generation and transaction.
     */
    public function create(array $data): SpmiStandard
    {
        DB::beginTransaction();
        try {
            // Validate review date
            if (isset($data['review_date']) && isset($data['effective_date'])) {
                $this->validateReviewDate($data['effective_date'], $data['review_date']);
            }

            // Generate code if not provided
            if (empty($data['code'])) {
                $data['code'] = $this->generateStandardCode();
            } else {
                // Check if code already exists
                if ($this->repository->codeExists($data['code'])) {
                    throw new Exception('Standard code already exists');
                }
            }

            // Set status and creator
            $data['status'] = $data['status'] ?? 'draft';
            $data['created_by'] = auth()->id();
            $data['version'] = 1;

            $standard = $this->repository->create($data);

            DB::commit();
            Log::info('SPMI standard created', ['id' => $standard->id, 'code' => $standard->code]);

            return $standard;
        } catch (Exception $e) {
            DB::rollBack();
            Log::error('Failed to create SPMI standard', ['error' => $e->getMessage()]);
            throw $e;
        }
    }

    /**
     * Update SPMI standard with validation.
     */
    public function update(int $id, array $data): SpmiStandard
    {
        DB::beginTransaction();
        try {
            $standard = $this->repository->findById($id);

            if (!$standard) {
                throw new Exception('SPMI standard not found');
            }

            // Check if standard can be updated (only draft or revision status)
            if (!in_array($standard->status, ['draft', 'revision'])) {
                throw new Exception('Only draft or revision standards can be updated');
            }

            // Validate review date if being updated
            if (isset($data['review_date']) || isset($data['effective_date'])) {
                $effectiveDate = $data['effective_date'] ?? $standard->effective_date;
                $reviewDate = $data['review_date'] ?? $standard->review_date;
                if ($effectiveDate && $reviewDate) {
                    $this->validateReviewDate($effectiveDate, $reviewDate);
                }
            }

            // Validate code uniqueness if code is being changed
            if (isset($data['code']) && $data['code'] !== $standard->code) {
                if ($this->repository->codeExists($data['code'], $id)) {
                    throw new Exception('Standard code already exists');
                }
            }

            $this->repository->update($standard, $data);

            DB::commit();
            Log::info('SPMI standard updated', ['id' => $standard->id]);

            return $standard->fresh();
        } catch (Exception $e) {
            DB::rollBack();
            Log::error('Failed to update SPMI standard', ['id' => $id, 'error' => $e->getMessage()]);
            throw $e;
        }
    }

    /**
     * Delete SPMI standard with validation.
     * Cannot delete if has related indicators or monitoring records.
     */
    public function delete(int $id): bool
    {
        DB::beginTransaction();
        try {
            $standard = $this->repository->findById($id);

            if (!$standard) {
                throw new Exception('SPMI standard not found');
            }

            // Check if standard has indicators
            if ($standard->indicators()->exists()) {
                throw new Exception('Cannot delete standard that has related indicators');
            }

            // Check if standard has monitoring records
            if ($standard->monitorings()->exists()) {
                throw new Exception('Cannot delete standard that has related monitoring records');
            }

            $this->repository->delete($standard);

            DB::commit();
            Log::info('SPMI standard deleted', ['id' => $id]);

            return true;
        } catch (Exception $e) {
            DB::rollBack();
            Log::error('Failed to delete SPMI standard', ['id' => $id, 'error' => $e->getMessage()]);
            throw $e;
        }
    }

    /**
     * Approve SPMI standard (change status to 'active', set approved_by and approved_at).
     */
    public function approve(int $id): SpmiStandard
    {
        DB::beginTransaction();
        try {
            $standard = $this->repository->findById($id);

            if (!$standard) {
                throw new Exception('SPMI standard not found');
            }

            // Only draft standards can be approved
            if ($standard->status !== 'draft') {
                throw new Exception('Only draft standards can be approved');
            }

            $data = [
                'status' => 'active',
                'approved_by' => auth()->id(),
                'approved_at' => now(),
            ];

            $this->repository->update($standard, $data);

            DB::commit();
            Log::info('SPMI standard approved', ['id' => $id, 'approved_by' => auth()->id()]);

            return $standard->fresh();
        } catch (Exception $e) {
            DB::rollBack();
            Log::error('Failed to approve SPMI standard', ['id' => $id, 'error' => $e->getMessage()]);
            throw $e;
        }
    }

    /**
     * Create revision of SPMI standard (increment version, set status to 'revision').
     */
    public function revise(int $id): SpmiStandard
    {
        DB::beginTransaction();
        try {
            $standard = $this->repository->findById($id);

            if (!$standard) {
                throw new Exception('SPMI standard not found');
            }

            // Can only revise approved standards
            if (!$standard->isApproved()) {
                throw new Exception('Only approved standards can be revised');
            }

            $data = [
                'version' => $standard->version + 1,
                'status' => 'revision',
                'approved_by' => null,
                'approved_at' => null,
            ];

            $this->repository->update($standard, $data);

            DB::commit();
            Log::info('SPMI standard revision created', ['id' => $id, 'new_version' => $data['version']]);

            return $standard->fresh();
        } catch (Exception $e) {
            DB::rollBack();
            Log::error('Failed to revise SPMI standard', ['id' => $id, 'error' => $e->getMessage()]);
            throw $e;
        }
    }

    /**
     * Archive SPMI standard (change status to 'inactive').
     */
    public function archive(int $id): SpmiStandard
    {
        DB::beginTransaction();
        try {
            $standard = $this->repository->findById($id);

            if (!$standard) {
                throw new Exception('SPMI standard not found');
            }

            // Cannot archive if status is 'draft' or 'revision'
            if (in_array($standard->status, ['draft', 'revision'])) {
                throw new Exception('Cannot archive draft or revision standards');
            }

            $this->repository->update($standard, ['status' => 'inactive']);

            DB::commit();
            Log::info('SPMI standard archived', ['id' => $id]);

            return $standard->fresh();
        } catch (Exception $e) {
            DB::rollBack();
            Log::error('Failed to archive SPMI standard', ['id' => $id, 'error' => $e->getMessage()]);
            throw $e;
        }
    }

    /**
     * Get active standards.
     */
    public function getActive(): Collection
    {
        return $this->repository->getActive();
    }

    /**
     * Get standards by category.
     */
    public function getByCategory(string $category): Collection
    {
        return $this->repository->getByCategory($category);
    }

    /**
     * Get standards due for review.
     */
    public function getDueForReview(int $days = 30): Collection
    {
        return $this->repository->getDueForReview($days);
    }

    /**
     * Get approved standards.
     */
    public function getApproved(): Collection
    {
        return $this->repository->getApproved();
    }

    /**
     * Get pending approval standards.
     */
    public function getPendingApproval(): Collection
    {
        return $this->repository->getPendingApproval();
    }

    /**
     * Get statistics.
     */
    public function getStatistics(): array
    {
        return $this->repository->getStatistics();
    }

    /**
     * Generate unique standard code.
     * Format: STD-YYYY-### (e.g., STD-2025-001)
     */
    private function generateStandardCode(): string
    {
        $year = now()->year;
        $prefix = "STD-{$year}-";

        $lastStandard = SpmiStandard::where('code', 'like', $prefix . '%')
            ->orderBy('code', 'desc')
            ->first();

        if (!$lastStandard) {
            return $prefix . '001';
        }

        $lastNumber = (int) substr($lastStandard->code, -3);
        $newNumber = str_pad($lastNumber + 1, 3, '0', STR_PAD_LEFT);

        return $prefix . $newNumber;
    }

    /**
     * Validate review date (must be after effective date).
     */
    private function validateReviewDate(string $effectiveDate, string $reviewDate): void
    {
        if ($reviewDate <= $effectiveDate) {
            throw new Exception('Review date must be after effective date');
        }
    }
}
