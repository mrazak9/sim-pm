<?php

namespace App\Repositories;

use App\Models\AuditPlan;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Pagination\LengthAwarePaginator;

class AuditPlanRepository
{
    /**
     * Get all audit plans with pagination.
     */
    public function all(array $filters = [], int $perPage = 15): LengthAwarePaginator
    {
        $query = AuditPlan::with(['tahunAkademik', 'creator', 'approver']);

        // Apply filters
        if (isset($filters['tahun_akademik_id'])) {
            $query->where('tahun_akademik_id', $filters['tahun_akademik_id']);
        }

        if (isset($filters['status'])) {
            $query->where('status', $filters['status']);
        }

        if (isset($filters['periode'])) {
            $query->where('periode', $filters['periode']);
        }

        if (isset($filters['search'])) {
            $query->where(function ($q) use ($filters) {
                $q->where('title', 'like', '%' . $filters['search'] . '%')
                  ->orWhere('description', 'like', '%' . $filters['search'] . '%');
            });
        }

        if (isset($filters['year'])) {
            $query->whereYear('start_date', $filters['year']);
        }

        // Order by
        $orderBy = $filters['order_by'] ?? 'start_date';
        $orderDir = $filters['order_dir'] ?? 'desc';
        $query->orderBy($orderBy, $orderDir);

        return $query->paginate($perPage);
    }

    /**
     * Find audit plan by ID with relationships.
     */
    public function findById(int $id): ?AuditPlan
    {
        return AuditPlan::with([
            'tahunAkademik',
            'creator',
            'approver',
            'schedules.unitKerja',
            'schedules.auditorLead'
        ])->find($id);
    }

    /**
     * Create new audit plan.
     */
    public function create(array $data): AuditPlan
    {
        return AuditPlan::create($data);
    }

    /**
     * Update audit plan.
     */
    public function update(AuditPlan $auditPlan, array $data): bool
    {
        return $auditPlan->update($data);
    }

    /**
     * Delete audit plan.
     */
    public function delete(AuditPlan $auditPlan): bool
    {
        return $auditPlan->delete();
    }

    /**
     * Get active audit plans.
     */
    public function getActive(): Collection
    {
        return AuditPlan::active()
            ->with(['tahunAkademik'])
            ->orderBy('start_date', 'desc')
            ->get();
    }

    /**
     * Get approved audit plans.
     */
    public function getApproved(): Collection
    {
        return AuditPlan::approved()
            ->with(['tahunAkademik'])
            ->orderBy('start_date', 'desc')
            ->get();
    }

    /**
     * Get ongoing audit plans.
     */
    public function getOngoing(): Collection
    {
        return AuditPlan::ongoing()
            ->with(['tahunAkademik'])
            ->orderBy('start_date', 'desc')
            ->get();
    }

    /**
     * Get audit plans by tahun akademik.
     */
    public function getByTahunAkademik(int $tahunAkademikId): Collection
    {
        return AuditPlan::where('tahun_akademik_id', $tahunAkademikId)
            ->with(['creator', 'approver'])
            ->orderBy('start_date', 'desc')
            ->get();
    }

    /**
     * Get audit plans by status.
     */
    public function getByStatus(string $status): Collection
    {
        return AuditPlan::where('status', $status)
            ->with(['tahunAkademik', 'creator'])
            ->orderBy('start_date', 'desc')
            ->get();
    }

    /**
     * Get statistics.
     */
    public function getStatistics(): array
    {
        return [
            'total' => AuditPlan::count(),
            'draft' => AuditPlan::where('status', 'draft')->count(),
            'approved' => AuditPlan::where('status', 'approved')->count(),
            'ongoing' => AuditPlan::where('status', 'ongoing')->count(),
            'completed' => AuditPlan::where('status', 'completed')->count(),
            'cancelled' => AuditPlan::where('status', 'cancelled')->count(),
        ];
    }

    /**
     * Toggle active status.
     */
    public function toggleActive(AuditPlan $auditPlan): bool
    {
        $newStatus = $auditPlan->status === 'cancelled' ? 'draft' : 'cancelled';
        return $auditPlan->update(['status' => $newStatus]);
    }

    /**
     * Check if plan code exists.
     */
    public function codeExists(string $code, ?int $exceptId = null): bool
    {
        $query = AuditPlan::where('title', $code);

        if ($exceptId) {
            $query->where('id', '!=', $exceptId);
        }

        return $query->exists();
    }

    /**
     * Get audit plans with schedule counts.
     */
    public function getWithScheduleCounts(): Collection
    {
        return AuditPlan::withCount('schedules')
            ->with(['tahunAkademik'])
            ->orderBy('start_date', 'desc')
            ->get();
    }
}
