<?php

namespace App\Repositories;

use App\Models\RTMActionItem;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Pagination\LengthAwarePaginator;

class RTMActionItemRepository
{
    /**
     * Get all RTM action items with pagination and filters.
     */
    public function all(array $filters = [], int $perPage = 15): LengthAwarePaginator
    {
        $query = RTMActionItem::with([
            'rtm.tahunAkademik',
            'pic',
            'unitKerja',
            'progress'
        ]);

        // Apply filters
        if (isset($filters['rtm_id'])) {
            $query->where('rtm_id', $filters['rtm_id']);
        }

        if (isset($filters['status'])) {
            $query->where('status', $filters['status']);
        }

        if (isset($filters['priority'])) {
            $query->where('priority', $filters['priority']);
        }

        if (isset($filters['pic_id'])) {
            $query->where('pic_id', $filters['pic_id']);
        }

        if (isset($filters['unit_kerja_id'])) {
            $query->where('unit_kerja_id', $filters['unit_kerja_id']);
        }

        if (isset($filters['overdue'])) {
            $query->where('status', '!=', 'completed')
                ->where('due_date', '<', now()->toDateString());
        }

        if (isset($filters['due_soon'])) {
            $days = $filters['due_soon_days'] ?? 7;
            $query->where('status', '!=', 'completed')
                ->where('due_date', '<=', now()->addDays($days)->toDateString())
                ->where('due_date', '>=', now()->toDateString());
        }

        if (isset($filters['due_date_from'])) {
            $query->where('due_date', '>=', $filters['due_date_from']);
        }

        if (isset($filters['due_date_to'])) {
            $query->where('due_date', '<=', $filters['due_date_to']);
        }

        if (isset($filters['search'])) {
            $query->where(function ($q) use ($filters) {
                $q->where('action_code', 'like', '%' . $filters['search'] . '%')
                  ->orWhere('title', 'like', '%' . $filters['search'] . '%')
                  ->orWhere('description', 'like', '%' . $filters['search'] . '%');
            });
        }

        // Order by
        $orderBy = $filters['order_by'] ?? 'due_date';
        $orderDir = $filters['order_dir'] ?? 'asc';
        $query->orderBy($orderBy, $orderDir);

        return $query->paginate($perPage);
    }

    /**
     * Find RTM action item by ID with relationships.
     */
    public function findById(int $id, array $with = []): ?RTMActionItem
    {
        $defaultWith = [
            'rtm.tahunAkademik',
            'pic',
            'unitKerja',
            'progress'
        ];

        $relations = !empty($with) ? $with : $defaultWith;

        return RTMActionItem::with($relations)->find($id);
    }

    /**
     * Create new RTM action item.
     */
    public function create(array $data): RTMActionItem
    {
        return RTMActionItem::create($data);
    }

    /**
     * Update RTM action item.
     */
    public function update(RTMActionItem $actionItem, array $data): bool
    {
        return $actionItem->update($data);
    }

    /**
     * Delete RTM action item.
     */
    public function delete(RTMActionItem $actionItem): bool
    {
        return $actionItem->delete();
    }

    /**
     * Get overdue action items.
     */
    public function getOverdue(): Collection
    {
        return RTMActionItem::overdue()
            ->with(['rtm', 'pic', 'unitKerja'])
            ->orderBy('due_date', 'asc')
            ->get();
    }

    /**
     * Get action items due soon within X days.
     */
    public function getDueSoon(int $days = 7): Collection
    {
        return RTMActionItem::dueSoon($days)
            ->with(['rtm', 'pic', 'unitKerja'])
            ->orderBy('due_date', 'asc')
            ->get();
    }

    /**
     * Get completed action items.
     */
    public function getCompleted(): Collection
    {
        return RTMActionItem::completed()
            ->with(['rtm', 'pic', 'unitKerja'])
            ->orderBy('completed_at', 'desc')
            ->get();
    }

    /**
     * Get action items by RTM.
     */
    public function getByRTM(int $rtmId): Collection
    {
        return RTMActionItem::where('rtm_id', $rtmId)
            ->with(['pic', 'unitKerja', 'progress'])
            ->orderBy('priority', 'desc')
            ->orderBy('due_date', 'asc')
            ->get();
    }

    /**
     * Get action items assigned to a PIC (Person In Charge).
     */
    public function getByPIC(int $userId): Collection
    {
        return RTMActionItem::where('pic_id', $userId)
            ->with(['rtm.tahunAkademik', 'unitKerja', 'progress'])
            ->orderBy('due_date', 'asc')
            ->get();
    }

    /**
     * Get action items by unit kerja.
     */
    public function getByUnitKerja(int $unitKerjaId): Collection
    {
        return RTMActionItem::where('unit_kerja_id', $unitKerjaId)
            ->with(['rtm', 'pic', 'progress'])
            ->orderBy('due_date', 'asc')
            ->get();
    }

    /**
     * Get action items by status.
     */
    public function getByStatus(string $status): Collection
    {
        return RTMActionItem::where('status', $status)
            ->with(['rtm', 'pic', 'unitKerja'])
            ->orderBy('due_date', 'asc')
            ->get();
    }

    /**
     * Get action items by priority.
     */
    public function getByPriority(string $priority): Collection
    {
        return RTMActionItem::where('priority', $priority)
            ->with(['rtm', 'pic', 'unitKerja'])
            ->orderBy('due_date', 'asc')
            ->get();
    }

    /**
     * Generate unique action item code.
     * Format: RTM-ACT-YYYY-### (e.g., RTM-ACT-2025-001)
     */
    public function generateActionCode(): string
    {
        $year = now()->year;
        $prefix = "RTM-ACT-{$year}-";

        $lastAction = RTMActionItem::where('action_code', 'like', $prefix . '%')
            ->orderBy('action_code', 'desc')
            ->first();

        if (!$lastAction) {
            return $prefix . '001';
        }

        $lastNumber = (int) substr($lastAction->action_code, -3);
        $newNumber = str_pad($lastNumber + 1, 3, '0', STR_PAD_LEFT);

        return $prefix . $newNumber;
    }

    /**
     * Check if code already exists.
     */
    public function codeExists(string $code, ?int $exceptId = null): bool
    {
        $query = RTMActionItem::where('action_code', $code);

        if ($exceptId) {
            $query->where('id', '!=', $exceptId);
        }

        return $query->exists();
    }

    /**
     * Get statistics for action items.
     * Returns counts by status, by priority, and completion rates.
     */
    public function getStatistics(): array
    {
        $total = RTMActionItem::count();
        $completed = RTMActionItem::where('status', 'completed')->count();
        $in_progress = RTMActionItem::where('status', 'in_progress')->count();
        $not_started = RTMActionItem::where('status', 'not_started')->count();
        $overdue = RTMActionItem::overdue()->count();

        $priorities = RTMActionItem::selectRaw('priority, COUNT(*) as count')
            ->whereNotNull('priority')
            ->groupBy('priority')
            ->get()
            ->pluck('count', 'priority')
            ->toArray();

        return [
            'total' => $total,
            'completed' => $completed,
            'in_progress' => $in_progress,
            'not_started' => $not_started,
            'overdue' => $overdue,
            'completion_rate' => $total > 0
                ? round(($completed / $total) * 100, 2)
                : 0,
            'priorities_count' => count($priorities),
            'priorities' => $priorities,
            'average_completion' => RTMActionItem::where('status', '!=', 'completed')
                ->avg('completion_percentage') ?? 0,
        ];
    }

    /**
     * Get comprehensive dashboard statistics for action items.
     */
    public function getDashboardStatistics(): array
    {
        $totalItems = RTMActionItem::count();
        $completedItems = RTMActionItem::completed()->count();
        $overdueItems = RTMActionItem::overdue()->count();
        $dueSoonItems = RTMActionItem::dueSoon(7)->count();
        $inProgressItems = RTMActionItem::where('status', 'in_progress')->count();

        $highPriority = RTMActionItem::where('priority', 'high')
            ->where('status', '!=', 'completed')
            ->count();

        return [
            'total_items' => $totalItems,
            'completed' => $completedItems,
            'in_progress' => $inProgressItems,
            'not_started' => RTMActionItem::where('status', 'not_started')->count(),
            'overdue' => $overdueItems,
            'due_soon_7_days' => $dueSoonItems,
            'high_priority' => $highPriority,
            'completion_rate' => $totalItems > 0
                ? round(($completedItems / $totalItems) * 100, 2)
                : 0,
            'at_risk_count' => $overdueItems + $dueSoonItems,
            'recent_items' => RTMActionItem::with(['rtm', 'pic', 'unitKerja'])
                ->latest('created_at')
                ->limit(5)
                ->get(),
        ];
    }

    /**
     * Get high priority pending action items.
     */
    public function getHighPriorityPending(): Collection
    {
        return RTMActionItem::where('priority', 'high')
            ->where('status', '!=', 'completed')
            ->with(['rtm', 'pic', 'unitKerja'])
            ->orderBy('due_date', 'asc')
            ->get();
    }

    /**
     * Update completion percentage for action item.
     */
    public function updateCompletionPercentage(int $actionItemId, int $percentage): bool
    {
        $actionItem = RTMActionItem::findOrFail($actionItemId);
        return $actionItem->update(['completion_percentage' => $percentage]);
    }

    /**
     * Mark action item as completed.
     */
    public function markAsCompleted(int $actionItemId, string $remarks = ''): bool
    {
        $actionItem = RTMActionItem::findOrFail($actionItemId);

        return $actionItem->update([
            'status' => 'completed',
            'completion_percentage' => 100,
            'completed_at' => now(),
            'completion_remarks' => $remarks,
        ]);
    }
}
