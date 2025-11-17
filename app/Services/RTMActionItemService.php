<?php

namespace App\Services;

use App\Models\RTMActionItem;
use App\Models\RTMActionProgress;
use App\Repositories\RTMActionItemRepository;
use App\Repositories\RTMRepository;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;
use Exception;

class RTMActionItemService
{
    public function __construct(
        private RTMActionItemRepository $repository,
        private RTMRepository $rtmRepository
    ) {}

    /**
     * Get all RTM action items with pagination.
     */
    public function getAllPaginated(array $filters = [], int $perPage = 15): LengthAwarePaginator
    {
        return $this->repository->all($filters, $perPage);
    }

    /**
     * Get RTM action item by ID.
     */
    public function getById(int $id): ?RTMActionItem
    {
        return $this->repository->findById($id);
    }

    /**
     * Create action item with auto-code generation.
     */
    public function create(array $data): RTMActionItem
    {
        DB::beginTransaction();
        try {
            // Validate that RTM exists
            $rtm = $this->rtmRepository->findById($data['rtm_id']);
            if (!$rtm) {
                throw new Exception('RTM not found');
            }

            // Validate due date is in future
            if (isset($data['due_date'])) {
                $this->validateDueDate($data['due_date']);
            }

            // Validate PIC exists (basic check - will be caught by foreign key)
            if (!isset($data['pic_id']) || empty($data['pic_id'])) {
                throw new Exception('PIC (Person In Charge) must be specified');
            }

            // Generate code if not provided
            if (empty($data['action_code'])) {
                $data['action_code'] = $this->repository->generateActionCode();
            } else {
                // Check if code already exists
                if ($this->repository->codeExists($data['action_code'])) {
                    throw new Exception('Action item code already exists');
                }
            }

            // Set default values
            $data['status'] = $data['status'] ?? 'not_started';
            $data['completion_percentage'] = $data['completion_percentage'] ?? 0;

            $actionItem = $this->repository->create($data);

            DB::commit();
            Log::info('RTM action item created', ['id' => $actionItem->id, 'code' => $actionItem->action_code]);

            return $actionItem;
        } catch (Exception $e) {
            DB::rollBack();
            Log::error('Failed to create RTM action item', ['error' => $e->getMessage()]);
            throw $e;
        }
    }

    /**
     * Update action item.
     */
    public function update(int $id, array $data): RTMActionItem
    {
        DB::beginTransaction();
        try {
            $actionItem = $this->repository->findById($id);

            if (!$actionItem) {
                throw new Exception('RTM action item not found');
            }

            // Cannot update if completed
            if ($actionItem->status === 'completed') {
                throw new Exception('Cannot update completed action items');
            }

            // Validate due date if being updated
            if (isset($data['due_date'])) {
                $this->validateDueDate($data['due_date']);
            }

            // Validate code uniqueness if code is being changed
            if (isset($data['action_code']) && $data['action_code'] !== $actionItem->action_code) {
                if ($this->repository->codeExists($data['action_code'], $id)) {
                    throw new Exception('Action item code already exists');
                }
            }

            $this->repository->update($actionItem, $data);

            DB::commit();
            Log::info('RTM action item updated', ['id' => $actionItem->id]);

            return $actionItem->fresh();
        } catch (Exception $e) {
            DB::rollBack();
            Log::error('Failed to update RTM action item', ['id' => $id, 'error' => $e->getMessage()]);
            throw $e;
        }
    }

    /**
     * Delete action item (can't delete if in_progress or completed).
     */
    public function delete(int $id): bool
    {
        DB::beginTransaction();
        try {
            $actionItem = $this->repository->findById($id);

            if (!$actionItem) {
                throw new Exception('RTM action item not found');
            }

            // Cannot delete if in_progress or completed
            if (in_array($actionItem->status, ['in_progress', 'completed'])) {
                throw new Exception('Cannot delete action items that are in progress or completed');
            }

            // Clean up evidence file if exists
            if ($actionItem->evidence_file) {
                Storage::delete($actionItem->evidence_file);
            }

            $this->repository->delete($actionItem);

            DB::commit();
            Log::info('RTM action item deleted', ['id' => $id]);

            return true;
        } catch (Exception $e) {
            DB::rollBack();
            Log::error('Failed to delete RTM action item', ['id' => $id, 'error' => $e->getMessage()]);
            throw $e;
        }
    }

    /**
     * Start action item (change status to 'in_progress').
     */
    public function start(int $id): RTMActionItem
    {
        DB::beginTransaction();
        try {
            $actionItem = $this->repository->findById($id);

            if (!$actionItem) {
                throw new Exception('RTM action item not found');
            }

            // Only 'not_started' can be started
            if ($actionItem->status !== 'not_started') {
                throw new Exception('Only not started action items can be started');
            }

            $this->repository->update($actionItem, ['status' => 'in_progress']);

            DB::commit();
            Log::info('RTM action item started', ['id' => $id]);

            return $actionItem->fresh();
        } catch (Exception $e) {
            DB::rollBack();
            Log::error('Failed to start RTM action item', ['id' => $id, 'error' => $e->getMessage()]);
            throw $e;
        }
    }

    /**
     * Complete action item with completion_percentage must be 100.
     */
    public function complete(int $id, array $completionData): RTMActionItem
    {
        DB::beginTransaction();
        try {
            $actionItem = $this->repository->findById($id);

            if (!$actionItem) {
                throw new Exception('RTM action item not found');
            }

            // Cannot complete if already completed
            if ($actionItem->status === 'completed') {
                throw new Exception('Action item is already completed');
            }

            // completion_percentage must be 100 when completing
            if (!isset($completionData['completion_percentage']) || $completionData['completion_percentage'] !== 100) {
                throw new Exception('Completion percentage must be 100 to mark as completed');
            }

            // Set status and completion date
            $completionData['status'] = 'completed';
            $completionData['completed_at'] = now()->toDateString();

            $this->repository->update($actionItem, $completionData);

            DB::commit();
            Log::info('RTM action item completed', ['id' => $id]);

            return $actionItem->fresh();
        } catch (Exception $e) {
            DB::rollBack();
            Log::error('Failed to complete RTM action item', ['id' => $id, 'error' => $e->getMessage()]);
            throw $e;
        }
    }

    /**
     * Cancel action item with reason.
     */
    public function cancel(int $id, string $reason): RTMActionItem
    {
        DB::beginTransaction();
        try {
            $actionItem = $this->repository->findById($id);

            if (!$actionItem) {
                throw new Exception('RTM action item not found');
            }

            // Cannot cancel if already completed
            if ($actionItem->status === 'completed') {
                throw new Exception('Cannot cancel completed action items');
            }

            // Add reason to progress_notes
            $progressNotes = $actionItem->progress_notes ? "CANCELLED: {$reason}\n\n{$actionItem->progress_notes}" : "CANCELLED: {$reason}";

            $this->repository->update($actionItem, [
                'status' => 'cancelled',
                'progress_notes' => $progressNotes,
            ]);

            DB::commit();
            Log::info('RTM action item cancelled', ['id' => $id, 'reason' => $reason]);

            return $actionItem->fresh();
        } catch (Exception $e) {
            DB::rollBack();
            Log::error('Failed to cancel RTM action item', ['id' => $id, 'error' => $e->getMessage()]);
            throw $e;
        }
    }

    /**
     * Add progress to action item and auto-update completion percentage.
     */
    public function addProgress(int $actionItemId, array $progressData): RTMActionProgress
    {
        DB::beginTransaction();
        try {
            $actionItem = $this->repository->findById($actionItemId);

            if (!$actionItem) {
                throw new Exception('RTM action item not found');
            }

            // Cannot add progress to completed items
            if ($actionItem->status === 'completed') {
                throw new Exception('Cannot add progress to completed action items');
            }

            // Validate completion percentage (0-100)
            if (!isset($progressData['progress_percentage']) || $progressData['progress_percentage'] < 0 || $progressData['progress_percentage'] > 100) {
                throw new Exception('Progress percentage must be between 0 and 100');
            }

            // Set defaults
            $progressData['rtm_action_item_id'] = $actionItemId;
            $progressData['progress_date'] = $progressData['progress_date'] ?? now()->toDateString();
            $progressData['reported_by'] = auth()->id();

            $progress = RTMActionProgress::create($progressData);

            // Auto-update action item status based on progress percentage
            if ($progress->progress_percentage >= 100) {
                $actionItem->update(['status' => 'completed', 'completed_at' => now()->toDateString()]);
            } elseif ($progress->progress_percentage > 0 && $actionItem->status === 'not_started') {
                $actionItem->update(['status' => 'in_progress']);
            }

            DB::commit();
            Log::info('RTM action progress added', ['action_item_id' => $actionItemId, 'percentage' => $progress->progress_percentage]);

            return $progress;
        } catch (Exception $e) {
            DB::rollBack();
            Log::error('Failed to add RTM action progress', ['action_item_id' => $actionItemId, 'error' => $e->getMessage()]);
            throw $e;
        }
    }

    /**
     * Extend due date with reason.
     */
    public function extend(int $id, string $newDueDate, string $reason): RTMActionItem
    {
        DB::beginTransaction();
        try {
            $actionItem = $this->repository->findById($id);

            if (!$actionItem) {
                throw new Exception('RTM action item not found');
            }

            // Cannot extend if completed
            if ($actionItem->status === 'completed') {
                throw new Exception('Cannot extend due date for completed action items');
            }

            // Validate new due date is in future
            $this->validateDueDate($newDueDate);

            // Update progress notes with extension info
            $extensionNote = "DUE DATE EXTENDED: {$reason} (From {$actionItem->due_date} to {$newDueDate})";
            $progressNotes = $actionItem->progress_notes ? "{$extensionNote}\n\n{$actionItem->progress_notes}" : $extensionNote;

            $this->repository->update($actionItem, [
                'due_date' => $newDueDate,
                'progress_notes' => $progressNotes,
            ]);

            DB::commit();
            Log::info('RTM action item due date extended', ['id' => $id, 'new_due_date' => $newDueDate]);

            return $actionItem->fresh();
        } catch (Exception $e) {
            DB::rollBack();
            Log::error('Failed to extend RTM action item due date', ['id' => $id, 'error' => $e->getMessage()]);
            throw $e;
        }
    }

    /**
     * Get overdue action items.
     */
    public function getOverdue(): Collection
    {
        return $this->repository->getOverdue();
    }

    /**
     * Get action items due soon.
     */
    public function getDueSoon(int $days = 7): Collection
    {
        return $this->repository->getDueSoon($days);
    }

    /**
     * Get completed action items.
     */
    public function getCompleted(): Collection
    {
        return $this->repository->getCompleted();
    }

    /**
     * Get action items by RTM.
     */
    public function getByRTM(int $rtmId): Collection
    {
        return $this->repository->getByRTM($rtmId);
    }

    /**
     * Get action items by PIC.
     */
    public function getByPIC(int $userId): Collection
    {
        return $this->repository->getByPIC($userId);
    }

    /**
     * Get action items by unit kerja.
     */
    public function getByUnitKerja(int $unitKerjaId): Collection
    {
        return $this->repository->getByUnitKerja($unitKerjaId);
    }

    /**
     * Get action items by status.
     */
    public function getByStatus(string $status): Collection
    {
        return $this->repository->getByStatus($status);
    }

    /**
     * Get action items by priority.
     */
    public function getByPriority(string $priority): Collection
    {
        return $this->repository->getByPriority($priority);
    }

    /**
     * Get high priority pending action items.
     */
    public function getHighPriorityPending(): Collection
    {
        return $this->repository->getHighPriorityPending();
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
     * Update completion percentage.
     */
    public function updateCompletionPercentage(int $id, int $percentage): RTMActionItem
    {
        DB::beginTransaction();
        try {
            $actionItem = $this->repository->findById($id);

            if (!$actionItem) {
                throw new Exception('RTM action item not found');
            }

            // Validate percentage (0-100)
            if ($percentage < 0 || $percentage > 100) {
                throw new Exception('Completion percentage must be between 0 and 100');
            }

            $this->repository->update($actionItem, ['completion_percentage' => $percentage]);

            DB::commit();
            Log::info('RTM action item completion percentage updated', ['id' => $id, 'percentage' => $percentage]);

            return $actionItem->fresh();
        } catch (Exception $e) {
            DB::rollBack();
            Log::error('Failed to update RTM action item completion percentage', ['id' => $id, 'error' => $e->getMessage()]);
            throw $e;
        }
    }

    /**
     * Mark as overdue status (based on current date vs due date).
     * This checks and updates status if necessary.
     */
    public function checkAndMarkOverdue(int $id): RTMActionItem
    {
        DB::beginTransaction();
        try {
            $actionItem = $this->repository->findById($id);

            if (!$actionItem) {
                throw new Exception('RTM action item not found');
            }

            // If not completed and due date has passed, mark as overdue
            if ($actionItem->status !== 'completed' && $actionItem->isOverdue()) {
                // Update status to indicate overdue (via progress notes if needed)
                if ($actionItem->status !== 'in_progress') {
                    $progressNotes = "OVERDUE: Due date {$actionItem->due_date} has passed.";
                    $existingNotes = $actionItem->progress_notes ? "{$progressNotes}\n\n{$actionItem->progress_notes}" : $progressNotes;
                    $this->repository->update($actionItem, ['progress_notes' => $existingNotes]);
                }
            }

            DB::commit();
            return $actionItem->fresh();
        } catch (Exception $e) {
            DB::rollBack();
            Log::error('Failed to check overdue status for RTM action item', ['id' => $id, 'error' => $e->getMessage()]);
            throw $e;
        }
    }

    /**
     * Validate due date (must be in future).
     */
    private function validateDueDate(string $dueDate): void
    {
        try {
            \DateTime::createFromFormat('Y-m-d', $dueDate);
        } catch (Exception $e) {
            throw new Exception('Invalid due date format');
        }

        if ($dueDate <= now()->toDateString()) {
            throw new Exception('Due date must be in the future');
        }
    }
}
