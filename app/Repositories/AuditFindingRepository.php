<?php

namespace App\Repositories;

use App\Models\AuditFinding;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Pagination\LengthAwarePaginator;

class AuditFindingRepository
{
    /**
     * Get all audit findings with pagination.
     */
    public function all(array $filters = [], int $perPage = 15): LengthAwarePaginator
    {
        $query = AuditFinding::with([
            'auditSchedule.auditPlan',
            'auditSchedule.unitKerja',
            'pic',
            'unitKerja',
            'verifier'
        ]);

        // Apply filters
        if (isset($filters['audit_schedule_id'])) {
            $query->where('audit_schedule_id', $filters['audit_schedule_id']);
        }

        if (isset($filters['category'])) {
            $query->where('category', $filters['category']);
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
            $query->where('status', '!=', 'closed')
                ->where('due_date', '<', now());
        }

        if (isset($filters['search'])) {
            $query->where(function ($q) use ($filters) {
                $q->where('finding_code', 'like', '%' . $filters['search'] . '%')
                  ->orWhere('description', 'like', '%' . $filters['search'] . '%')
                  ->orWhere('recommendation', 'like', '%' . $filters['search'] . '%');
            });
        }

        // Order by
        $orderBy = $filters['order_by'] ?? 'created_at';
        $orderDir = $filters['order_dir'] ?? 'desc';
        $query->orderBy($orderBy, $orderDir);

        return $query->paginate($perPage);
    }

    /**
     * Find audit finding by ID.
     */
    public function findById(int $id): ?AuditFinding
    {
        return AuditFinding::with([
            'auditSchedule.auditPlan.tahunAkademik',
            'auditSchedule.unitKerja',
            'pic',
            'unitKerja',
            'verifier',
            'evidence',
            'rtl.progressUpdates'
        ])->find($id);
    }

    /**
     * Find by finding code.
     */
    public function findByCode(string $code): ?AuditFinding
    {
        return AuditFinding::where('finding_code', $code)
            ->with(['auditSchedule', 'pic', 'unitKerja'])
            ->first();
    }

    /**
     * Create new audit finding.
     */
    public function create(array $data): AuditFinding
    {
        return AuditFinding::create($data);
    }

    /**
     * Update audit finding.
     */
    public function update(AuditFinding $auditFinding, array $data): bool
    {
        return $auditFinding->update($data);
    }

    /**
     * Delete audit finding.
     */
    public function delete(AuditFinding $auditFinding): bool
    {
        return $auditFinding->delete();
    }

    /**
     * Get open findings.
     */
    public function getOpen(): Collection
    {
        return AuditFinding::open()
            ->with(['auditSchedule', 'pic', 'unitKerja'])
            ->orderBy('priority', 'desc')
            ->orderBy('due_date', 'asc')
            ->get();
    }

    /**
     * Get overdue findings.
     */
    public function getOverdue(): Collection
    {
        return AuditFinding::overdue()
            ->with(['auditSchedule', 'pic', 'unitKerja'])
            ->orderBy('due_date', 'asc')
            ->get();
    }

    /**
     * Get findings by category.
     */
    public function getByCategory(string $category): Collection
    {
        return AuditFinding::byCategory($category)
            ->with(['auditSchedule', 'pic', 'unitKerja'])
            ->orderBy('created_at', 'desc')
            ->get();
    }

    /**
     * Get findings by priority.
     */
    public function getByPriority(string $priority): Collection
    {
        return AuditFinding::byPriority($priority)
            ->with(['auditSchedule', 'pic', 'unitKerja'])
            ->orderBy('due_date', 'asc')
            ->get();
    }

    /**
     * Get findings by unit kerja.
     */
    public function getByUnitKerja(int $unitKerjaId): Collection
    {
        return AuditFinding::where('unit_kerja_id', $unitKerjaId)
            ->with(['auditSchedule', 'pic'])
            ->orderBy('created_at', 'desc')
            ->get();
    }

    /**
     * Get findings by PIC.
     */
    public function getByPIC(int $picId): Collection
    {
        return AuditFinding::where('pic_id', $picId)
            ->with(['auditSchedule', 'unitKerja'])
            ->orderBy('due_date', 'asc')
            ->get();
    }

    /**
     * Get findings by audit schedule.
     */
    public function getByAuditSchedule(int $scheduleId): Collection
    {
        return AuditFinding::where('audit_schedule_id', $scheduleId)
            ->with(['pic', 'unitKerja', 'evidence'])
            ->orderBy('category', 'asc')
            ->orderBy('priority', 'desc')
            ->get();
    }

    /**
     * Get statistics.
     */
    public function getStatistics(): array
    {
        return [
            'total' => AuditFinding::count(),
            'open' => AuditFinding::where('status', 'open')->count(),
            'in_progress' => AuditFinding::where('status', 'in_progress')->count(),
            'resolved' => AuditFinding::where('status', 'resolved')->count(),
            'verified' => AuditFinding::where('status', 'verified')->count(),
            'closed' => AuditFinding::where('status', 'closed')->count(),
            'major' => AuditFinding::where('category', 'major')->count(),
            'minor' => AuditFinding::where('category', 'minor')->count(),
            'ofi' => AuditFinding::where('category', 'ofi')->count(),
            'overdue' => AuditFinding::overdue()->count(),
            'high_priority' => AuditFinding::where('priority', 'high')->count(),
            'critical_priority' => AuditFinding::where('priority', 'critical')->count(),
        ];
    }

    /**
     * Get statistics by category.
     */
    public function getStatisticsByCategory(): array
    {
        $categories = ['major', 'minor', 'ofi'];
        $stats = [];

        foreach ($categories as $category) {
            $stats[$category] = [
                'total' => AuditFinding::where('category', $category)->count(),
                'open' => AuditFinding::where('category', $category)->where('status', 'open')->count(),
                'in_progress' => AuditFinding::where('category', $category)->where('status', 'in_progress')->count(),
                'resolved' => AuditFinding::where('category', $category)->where('status', 'resolved')->count(),
                'closed' => AuditFinding::where('category', $category)->where('status', 'closed')->count(),
            ];
        }

        return $stats;
    }

    /**
     * Get findings needing attention (high priority + overdue).
     */
    public function getNeedingAttention(): Collection
    {
        return AuditFinding::where(function ($query) {
                $query->whereIn('priority', ['high', 'critical'])
                    ->orWhere(function ($q) {
                        $q->where('status', '!=', 'closed')
                          ->where('due_date', '<', now());
                    });
            })
            ->with(['auditSchedule', 'pic', 'unitKerja'])
            ->orderBy('priority', 'desc')
            ->orderBy('due_date', 'asc')
            ->get();
    }

    /**
     * Generate next finding code.
     */
    public function generateFindingCode(): string
    {
        $year = now()->year;
        $prefix = "AUD-{$year}-";

        $lastFinding = AuditFinding::where('finding_code', 'like', $prefix . '%')
            ->orderBy('finding_code', 'desc')
            ->first();

        if (!$lastFinding) {
            return $prefix . '001';
        }

        $lastNumber = (int) substr($lastFinding->finding_code, -3);
        $newNumber = str_pad($lastNumber + 1, 3, '0', STR_PAD_LEFT);

        return $prefix . $newNumber;
    }

    /**
     * Check if finding code exists.
     */
    public function codeExists(string $code, ?int $exceptId = null): bool
    {
        $query = AuditFinding::where('finding_code', $code);

        if ($exceptId) {
            $query->where('id', '!=', $exceptId);
        }

        return $query->exists();
    }
}
