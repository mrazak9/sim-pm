<?php

namespace App\Repositories;

use App\Models\RTL;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Pagination\LengthAwarePaginator;

class RTLRepository
{
    /**
     * Get all RTLs with pagination.
     */
    public function all(array $filters = [], int $perPage = 15): LengthAwarePaginator
    {
        $query = RTL::with([
            'auditFinding.auditSchedule.auditPlan',
            'auditFinding.auditSchedule.unitKerja',
            'pic',
            'unitKerja',
            'verifier',
            'latestProgress'
        ]);

        // Apply filters
        if (isset($filters['audit_finding_id'])) {
            $query->where('audit_finding_id', $filters['audit_finding_id']);
        }

        if (isset($filters['status'])) {
            $query->where('status', $filters['status']);
        }

        if (isset($filters['pic_id'])) {
            $query->where('pic_id', $filters['pic_id']);
        }

        if (isset($filters['unit_kerja_id'])) {
            $query->where('unit_kerja_id', $filters['unit_kerja_id']);
        }

        if (isset($filters['verification_status'])) {
            $query->where('verification_status', $filters['verification_status']);
        }

        if (isset($filters['risk_level'])) {
            $query->where('risk_level', $filters['risk_level']);
        }

        if (isset($filters['overdue'])) {
            $query->where('status', '!=', 'completed')
                ->where('target_date', '<', now());
        }

        if (isset($filters['due_soon'])) {
            $days = $filters['due_soon_days'] ?? 7;
            $query->where('status', '!=', 'completed')
                ->where('target_date', '<=', now()->addDays($days))
                ->where('target_date', '>=', now());
        }

        if (isset($filters['completion_min'])) {
            $query->where('completion_percentage', '>=', $filters['completion_min']);
        }

        if (isset($filters['completion_max'])) {
            $query->where('completion_percentage', '<=', $filters['completion_max']);
        }

        if (isset($filters['search'])) {
            $query->where(function ($q) use ($filters) {
                $q->where('rtl_code', 'like', '%' . $filters['search'] . '%')
                  ->orWhere('action_plan', 'like', '%' . $filters['search'] . '%');
            });
        }

        // Order by
        $orderBy = $filters['order_by'] ?? 'target_date';
        $orderDir = $filters['order_dir'] ?? 'asc';
        $query->orderBy($orderBy, $orderDir);

        return $query->paginate($perPage);
    }

    /**
     * Find RTL by ID.
     */
    public function findById(int $id): ?RTL
    {
        return RTL::with([
            'auditFinding.auditSchedule.auditPlan.tahunAkademik',
            'auditFinding.auditSchedule.unitKerja',
            'pic',
            'unitKerja',
            'verifier',
            'progressUpdates.reporter'
        ])->find($id);
    }

    /**
     * Find by RTL code.
     */
    public function findByCode(string $code): ?RTL
    {
        return RTL::where('rtl_code', $code)
            ->with(['auditFinding', 'pic', 'unitKerja'])
            ->first();
    }

    /**
     * Create new RTL.
     */
    public function create(array $data): RTL
    {
        return RTL::create($data);
    }

    /**
     * Update RTL.
     */
    public function update(RTL $rtl, array $data): bool
    {
        return $rtl->update($data);
    }

    /**
     * Delete RTL.
     */
    public function delete(RTL $rtl): bool
    {
        return $rtl->delete();
    }

    /**
     * Get not started RTLs.
     */
    public function getNotStarted(): Collection
    {
        return RTL::notStarted()
            ->with(['auditFinding', 'pic', 'unitKerja'])
            ->orderBy('target_date', 'asc')
            ->get();
    }

    /**
     * Get in progress RTLs.
     */
    public function getInProgress(): Collection
    {
        return RTL::inProgress()
            ->with(['auditFinding', 'pic', 'unitKerja', 'latestProgress'])
            ->orderBy('completion_percentage', 'asc')
            ->orderBy('target_date', 'asc')
            ->get();
    }

    /**
     * Get completed RTLs.
     */
    public function getCompleted(): Collection
    {
        return RTL::completed()
            ->with(['auditFinding', 'pic', 'unitKerja'])
            ->orderBy('completed_at', 'desc')
            ->get();
    }

    /**
     * Get overdue RTLs.
     */
    public function getOverdue(): Collection
    {
        return RTL::overdue()
            ->with(['auditFinding', 'pic', 'unitKerja'])
            ->orderBy('target_date', 'asc')
            ->get();
    }

    /**
     * Get RTLs due soon.
     */
    public function getDueSoon(int $days = 7): Collection
    {
        return RTL::dueSoon($days)
            ->with(['auditFinding', 'pic', 'unitKerja'])
            ->orderBy('target_date', 'asc')
            ->get();
    }

    /**
     * Get RTLs by PIC.
     */
    public function getByPIC(int $picId): Collection
    {
        return RTL::where('pic_id', $picId)
            ->with(['auditFinding', 'unitKerja', 'latestProgress'])
            ->orderBy('target_date', 'asc')
            ->get();
    }

    /**
     * Get RTLs by unit kerja.
     */
    public function getByUnitKerja(int $unitKerjaId): Collection
    {
        return RTL::where('unit_kerja_id', $unitKerjaId)
            ->with(['auditFinding', 'pic', 'latestProgress'])
            ->orderBy('target_date', 'asc')
            ->get();
    }

    /**
     * Get RTLs by audit finding.
     */
    public function getByAuditFinding(int $findingId): Collection
    {
        return RTL::where('audit_finding_id', $findingId)
            ->with(['pic', 'unitKerja', 'progressUpdates'])
            ->get();
    }

    /**
     * Get RTLs pending verification.
     */
    public function getPendingVerification(): Collection
    {
        return RTL::where('status', 'completed')
            ->where(function ($q) {
                $q->whereNull('verification_status')
                  ->orWhere('verification_status', 'pending');
            })
            ->with(['auditFinding', 'pic', 'unitKerja'])
            ->orderBy('completed_at', 'asc')
            ->get();
    }

    /**
     * Get statistics.
     */
    public function getStatistics(): array
    {
        return [
            'total' => RTL::count(),
            'not_started' => RTL::where('status', 'not_started')->count(),
            'in_progress' => RTL::where('status', 'in_progress')->count(),
            'completed' => RTL::where('status', 'completed')->count(),
            'overdue' => RTL::overdue()->count(),
            'due_soon_7_days' => RTL::dueSoon(7)->count(),
            'pending_verification' => RTL::where('status', 'completed')
                ->whereNull('verified_at')->count(),
            'average_completion' => RTL::where('status', '!=', 'completed')
                ->avg('completion_percentage') ?? 0,
        ];
    }

    /**
     * Get statistics by unit kerja.
     */
    public function getStatisticsByUnitKerja(): array
    {
        return RTL::selectRaw('unit_kerja_id, status, COUNT(*) as count')
            ->groupBy('unit_kerja_id', 'status')
            ->with('unitKerja:id,nama')
            ->get()
            ->groupBy('unit_kerja_id')
            ->map(function ($items) {
                $unitKerja = $items->first()->unitKerja;
                return [
                    'unit_kerja' => $unitKerja ? $unitKerja->nama : 'Unknown',
                    'total' => $items->sum('count'),
                    'not_started' => $items->where('status', 'not_started')->sum('count'),
                    'in_progress' => $items->where('status', 'in_progress')->sum('count'),
                    'completed' => $items->where('status', 'completed')->sum('count'),
                ];
            })
            ->values()
            ->toArray();
    }

    /**
     * Get dashboard statistics.
     */
    public function getDashboardStatistics(): array
    {
        $total = RTL::count();
        $completed = RTL::completed()->count();
        $inProgress = RTL::inProgress()->count();
        $overdue = RTL::overdue()->count();
        $dueSoon = RTL::dueSoon(7)->count();

        return [
            'total' => $total,
            'completed' => $completed,
            'in_progress' => $inProgress,
            'overdue' => $overdue,
            'due_soon' => $dueSoon,
            'completion_rate' => $total > 0 ? round(($completed / $total) * 100, 2) : 0,
            'on_track' => $inProgress - $overdue,
            'at_risk' => $dueSoon + $overdue,
        ];
    }

    /**
     * Generate next RTL code.
     */
    public function generateRTLCode(): string
    {
        $year = now()->year;
        $prefix = "RTL-{$year}-";

        $lastRTL = RTL::where('rtl_code', 'like', $prefix . '%')
            ->orderBy('rtl_code', 'desc')
            ->first();

        if (!$lastRTL) {
            return $prefix . '001';
        }

        $lastNumber = (int) substr($lastRTL->rtl_code, -3);
        $newNumber = str_pad($lastNumber + 1, 3, '0', STR_PAD_LEFT);

        return $prefix . $newNumber;
    }

    /**
     * Check if RTL code exists.
     */
    public function codeExists(string $code, ?int $exceptId = null): bool
    {
        $query = RTL::where('rtl_code', $code);

        if ($exceptId) {
            $query->where('id', '!=', $exceptId);
        }

        return $query->exists();
    }
}
