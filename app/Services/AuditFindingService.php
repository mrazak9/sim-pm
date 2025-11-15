<?php

namespace App\Services;

use App\Models\AuditFinding;
use App\Repositories\AuditFindingRepository;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Exception;

class AuditFindingService
{
    public function __construct(
        private AuditFindingRepository $repository
    ) {}

    /**
     * Get all audit findings with pagination.
     */
    public function getAllPaginated(array $filters = [], int $perPage = 15): LengthAwarePaginator
    {
        return $this->repository->all($filters, $perPage);
    }

    /**
     * Get audit finding by ID.
     */
    public function getById(int $id): ?AuditFinding
    {
        return $this->repository->findById($id);
    }

    /**
     * Create new audit finding.
     */
    public function create(array $data): AuditFinding
    {
        DB::beginTransaction();
        try {
            // Generate finding code if not provided
            if (!isset($data['finding_code'])) {
                $data['finding_code'] = $this->repository->generateFindingCode();
            }

            // Validate finding code uniqueness
            if ($this->repository->codeExists($data['finding_code'])) {
                throw new Exception('Finding code already exists');
            }

            // Set default values
            $data['status'] = $data['status'] ?? 'open';
            $data['priority'] = $data['priority'] ?? 'medium';
            $data['severity'] = $data['severity'] ?? 'medium';

            // Calculate due date if not provided (default: 30 days for major, 60 days for minor/ofi)
            if (!isset($data['due_date'])) {
                $days = match($data['category']) {
                    'major' => 30,
                    'minor' => 60,
                    'ofi' => 90,
                    default => 60,
                };
                $data['due_date'] = now()->addDays($days)->format('Y-m-d');
            }

            $auditFinding = $this->repository->create($data);

            DB::commit();
            Log::info('Audit finding created', [
                'id' => $auditFinding->id,
                'code' => $auditFinding->finding_code,
                'category' => $auditFinding->category
            ]);

            return $auditFinding;
        } catch (Exception $e) {
            DB::rollBack();
            Log::error('Failed to create audit finding', ['error' => $e->getMessage()]);
            throw $e;
        }
    }

    /**
     * Update audit finding.
     */
    public function update(int $id, array $data): AuditFinding
    {
        DB::beginTransaction();
        try {
            $auditFinding = $this->repository->findById($id);

            if (!$auditFinding) {
                throw new Exception('Audit finding not found');
            }

            // Validate finding code if changed
            if (isset($data['finding_code']) && $data['finding_code'] !== $auditFinding->finding_code) {
                if ($this->repository->codeExists($data['finding_code'], $id)) {
                    throw new Exception('Finding code already exists');
                }
            }

            $this->repository->update($auditFinding, $data);

            DB::commit();
            Log::info('Audit finding updated', ['id' => $id]);

            return $auditFinding->fresh();
        } catch (Exception $e) {
            DB::rollBack();
            Log::error('Failed to update audit finding', ['id' => $id, 'error' => $e->getMessage()]);
            throw $e;
        }
    }

    /**
     * Delete audit finding.
     */
    public function delete(int $id): bool
    {
        DB::beginTransaction();
        try {
            $auditFinding = $this->repository->findById($id);

            if (!$auditFinding) {
                throw new Exception('Audit finding not found');
            }

            // Check if finding can be deleted (no RTL or not yet verified)
            if ($auditFinding->status === 'verified' || $auditFinding->status === 'closed') {
                throw new Exception('Cannot delete verified or closed findings');
            }

            if ($auditFinding->rtl()->exists()) {
                throw new Exception('Cannot delete finding with existing RTL');
            }

            $this->repository->delete($auditFinding);

            DB::commit();
            Log::info('Audit finding deleted', ['id' => $id]);

            return true;
        } catch (Exception $e) {
            DB::rollBack();
            Log::error('Failed to delete audit finding', ['id' => $id, 'error' => $e->getMessage()]);
            throw $e;
        }
    }

    /**
     * Update finding status.
     */
    public function updateStatus(int $id, string $status, ?string $notes = null): AuditFinding
    {
        DB::beginTransaction();
        try {
            $auditFinding = $this->repository->findById($id);

            if (!$auditFinding) {
                throw new Exception('Audit finding not found');
            }

            // Validate status transition
            $this->validateStatusTransition($auditFinding->status, $status);

            $data = ['status' => $status];

            // Handle status-specific logic
            if ($status === 'resolved') {
                $data['resolved_at'] = now();
                $data['resolution_notes'] = $notes;
            }

            if ($status === 'verified') {
                if ($auditFinding->status !== 'resolved') {
                    throw new Exception('Only resolved findings can be verified');
                }
                $data['verified_by'] = auth()->id();
                $data['verified_at'] = now();
            }

            $this->repository->update($auditFinding, $data);

            DB::commit();
            Log::info('Audit finding status updated', [
                'id' => $id,
                'old_status' => $auditFinding->status,
                'new_status' => $status
            ]);

            return $auditFinding->fresh();
        } catch (Exception $e) {
            DB::rollBack();
            Log::error('Failed to update finding status', [
                'id' => $id,
                'status' => $status,
                'error' => $e->getMessage()
            ]);
            throw $e;
        }
    }

    /**
     * Resolve finding.
     */
    public function resolve(int $id, string $resolutionNotes): AuditFinding
    {
        return $this->updateStatus($id, 'resolved', $resolutionNotes);
    }

    /**
     * Verify finding.
     */
    public function verify(int $id): AuditFinding
    {
        return $this->updateStatus($id, 'verified');
    }

    /**
     * Close finding.
     */
    public function close(int $id): AuditFinding
    {
        DB::beginTransaction();
        try {
            $auditFinding = $this->repository->findById($id);

            if (!$auditFinding) {
                throw new Exception('Audit finding not found');
            }

            if ($auditFinding->status !== 'verified') {
                throw new Exception('Only verified findings can be closed');
            }

            // Check if RTL is completed (if exists)
            if ($auditFinding->rtl && $auditFinding->rtl->status !== 'completed') {
                throw new Exception('RTL must be completed before closing finding');
            }

            $this->repository->update($auditFinding, ['status' => 'closed']);

            DB::commit();
            Log::info('Audit finding closed', ['id' => $id]);

            return $auditFinding->fresh();
        } catch (Exception $e) {
            DB::rollBack();
            Log::error('Failed to close finding', ['id' => $id, 'error' => $e->getMessage()]);
            throw $e;
        }
    }

    /**
     * Reopen finding.
     */
    public function reopen(int $id, string $reason): AuditFinding
    {
        DB::beginTransaction();
        try {
            $auditFinding = $this->repository->findById($id);

            if (!$auditFinding) {
                throw new Exception('Audit finding not found');
            }

            if (!in_array($auditFinding->status, ['resolved', 'verified'])) {
                throw new Exception('Only resolved or verified findings can be reopened');
            }

            $data = [
                'status' => 'in_progress',
                'resolution_notes' => 'Reopened: ' . $reason,
            ];

            $this->repository->update($auditFinding, $data);

            DB::commit();
            Log::info('Audit finding reopened', ['id' => $id, 'reason' => $reason]);

            return $auditFinding->fresh();
        } catch (Exception $e) {
            DB::rollBack();
            Log::error('Failed to reopen finding', ['id' => $id, 'error' => $e->getMessage()]);
            throw $e;
        }
    }

    /**
     * Get overdue findings.
     */
    public function getOverdue(): Collection
    {
        return $this->repository->getOverdue();
    }

    /**
     * Get findings needing attention.
     */
    public function getNeedingAttention(): Collection
    {
        return $this->repository->getNeedingAttention();
    }

    /**
     * Get statistics.
     */
    public function getStatistics(): array
    {
        return $this->repository->getStatistics();
    }

    /**
     * Get statistics by category.
     */
    public function getStatisticsByCategory(): array
    {
        return $this->repository->getStatisticsByCategory();
    }

    /**
     * Validate status transition.
     */
    private function validateStatusTransition(string $currentStatus, string $newStatus): void
    {
        $validTransitions = [
            'open' => ['in_progress', 'resolved'],
            'in_progress' => ['resolved', 'open'],
            'resolved' => ['verified', 'in_progress'],
            'verified' => ['closed', 'in_progress'],
            'closed' => [],
        ];

        if (!isset($validTransitions[$currentStatus])) {
            throw new Exception('Invalid current status');
        }

        if (!in_array($newStatus, $validTransitions[$currentStatus])) {
            throw new Exception("Cannot transition from {$currentStatus} to {$newStatus}");
        }
    }
}
