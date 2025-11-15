<?php

namespace App\Services;

use App\Models\AuditPlan;
use App\Repositories\AuditPlanRepository;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Exception;

class AuditPlanService
{
    public function __construct(
        private AuditPlanRepository $repository
    ) {}

    /**
     * Get all audit plans with pagination.
     */
    public function getAllPaginated(array $filters = [], int $perPage = 15): LengthAwarePaginator
    {
        return $this->repository->all($filters, $perPage);
    }

    /**
     * Get audit plan by ID.
     */
    public function getById(int $id): ?AuditPlan
    {
        return $this->repository->findById($id);
    }

    /**
     * Create new audit plan.
     */
    public function create(array $data): AuditPlan
    {
        DB::beginTransaction();
        try {
            // Validate date range
            $this->validateDateRange($data['start_date'], $data['end_date']);

            // Set created by
            $data['created_by'] = auth()->id();
            $data['status'] = $data['status'] ?? 'draft';

            $auditPlan = $this->repository->create($data);

            DB::commit();
            Log::info('Audit plan created', ['id' => $auditPlan->id, 'title' => $auditPlan->title]);

            return $auditPlan;
        } catch (Exception $e) {
            DB::rollBack();
            Log::error('Failed to create audit plan', ['error' => $e->getMessage()]);
            throw $e;
        }
    }

    /**
     * Update audit plan.
     */
    public function update(int $id, array $data): AuditPlan
    {
        DB::beginTransaction();
        try {
            $auditPlan = $this->repository->findById($id);

            if (!$auditPlan) {
                throw new Exception('Audit plan not found');
            }

            // Check if plan is editable
            if (!$auditPlan->isEditable() && !auth()->user()->hasRole('admin')) {
                throw new Exception('Cannot edit approved or completed audit plan');
            }

            // Validate date range if dates are being updated
            if (isset($data['start_date']) || isset($data['end_date'])) {
                $startDate = $data['start_date'] ?? $auditPlan->start_date;
                $endDate = $data['end_date'] ?? $auditPlan->end_date;
                $this->validateDateRange($startDate, $endDate);
            }

            $this->repository->update($auditPlan, $data);

            DB::commit();
            Log::info('Audit plan updated', ['id' => $auditPlan->id]);

            return $auditPlan->fresh();
        } catch (Exception $e) {
            DB::rollBack();
            Log::error('Failed to update audit plan', ['id' => $id, 'error' => $e->getMessage()]);
            throw $e;
        }
    }

    /**
     * Delete audit plan.
     */
    public function delete(int $id): bool
    {
        DB::beginTransaction();
        try {
            $auditPlan = $this->repository->findById($id);

            if (!$auditPlan) {
                throw new Exception('Audit plan not found');
            }

            // Check if plan can be deleted (no completed schedules)
            if ($auditPlan->schedules()->where('status', 'completed')->exists()) {
                throw new Exception('Cannot delete audit plan with completed schedules');
            }

            $this->repository->delete($auditPlan);

            DB::commit();
            Log::info('Audit plan deleted', ['id' => $id]);

            return true;
        } catch (Exception $e) {
            DB::rollBack();
            Log::error('Failed to delete audit plan', ['id' => $id, 'error' => $e->getMessage()]);
            throw $e;
        }
    }

    /**
     * Approve audit plan.
     */
    public function approve(int $id): AuditPlan
    {
        DB::beginTransaction();
        try {
            $auditPlan = $this->repository->findById($id);

            if (!$auditPlan) {
                throw new Exception('Audit plan not found');
            }

            if ($auditPlan->status !== 'draft') {
                throw new Exception('Only draft plans can be approved');
            }

            $data = [
                'status' => 'approved',
                'approved_by' => auth()->id(),
                'approved_at' => now(),
            ];

            $this->repository->update($auditPlan, $data);

            DB::commit();
            Log::info('Audit plan approved', ['id' => $id, 'approved_by' => auth()->id()]);

            return $auditPlan->fresh();
        } catch (Exception $e) {
            DB::rollBack();
            Log::error('Failed to approve audit plan', ['id' => $id, 'error' => $e->getMessage()]);
            throw $e;
        }
    }

    /**
     * Start audit plan execution.
     */
    public function startExecution(int $id): AuditPlan
    {
        DB::beginTransaction();
        try {
            $auditPlan = $this->repository->findById($id);

            if (!$auditPlan) {
                throw new Exception('Audit plan not found');
            }

            if ($auditPlan->status !== 'approved') {
                throw new Exception('Only approved plans can be started');
            }

            $this->repository->update($auditPlan, ['status' => 'ongoing']);

            DB::commit();
            Log::info('Audit plan execution started', ['id' => $id]);

            return $auditPlan->fresh();
        } catch (Exception $e) {
            DB::rollBack();
            Log::error('Failed to start audit plan', ['id' => $id, 'error' => $e->getMessage()]);
            throw $e;
        }
    }

    /**
     * Complete audit plan.
     */
    public function complete(int $id): AuditPlan
    {
        DB::beginTransaction();
        try {
            $auditPlan = $this->repository->findById($id);

            if (!$auditPlan) {
                throw new Exception('Audit plan not found');
            }

            if ($auditPlan->status !== 'ongoing') {
                throw new Exception('Only ongoing plans can be completed');
            }

            // Check if all schedules are completed
            if ($auditPlan->schedules()->whereNotIn('status', ['completed', 'cancelled'])->exists()) {
                throw new Exception('All schedules must be completed or cancelled first');
            }

            $this->repository->update($auditPlan, ['status' => 'completed']);

            DB::commit();
            Log::info('Audit plan completed', ['id' => $id]);

            return $auditPlan->fresh();
        } catch (Exception $e) {
            DB::rollBack();
            Log::error('Failed to complete audit plan', ['id' => $id, 'error' => $e->getMessage()]);
            throw $e;
        }
    }

    /**
     * Get active plans.
     */
    public function getActive(): Collection
    {
        return $this->repository->getActive();
    }

    /**
     * Get statistics.
     */
    public function getStatistics(): array
    {
        return $this->repository->getStatistics();
    }

    /**
     * Validate date range.
     */
    private function validateDateRange(string $startDate, string $endDate): void
    {
        if ($startDate >= $endDate) {
            throw new Exception('End date must be after start date');
        }
    }
}
