<?php

namespace App\Repositories;

use App\Models\SpmiMonitoring;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Pagination\LengthAwarePaginator;

class SpmiMonitoringRepository
{
    /**
     * Get all SPMI monitorings with pagination and filters.
     */
    public function all(array $filters = [], int $perPage = 15): LengthAwarePaginator
    {
        $query = SpmiMonitoring::with([
            'spmiStandard',
            'tahunAkademik',
            'monitoredBy',
            'unitKerja'
        ]);

        // Apply filters
        if (isset($filters['spmi_standard_id'])) {
            $query->where('spmi_standard_id', $filters['spmi_standard_id']);
        }

        if (isset($filters['tahun_akademik_id'])) {
            $query->where('tahun_akademik_id', $filters['tahun_akademik_id']);
        }

        if (isset($filters['status'])) {
            $query->where('status', $filters['status']);
        }

        if (isset($filters['monitoring_type'])) {
            $query->where('monitoring_type', $filters['monitoring_type']);
        }

        if (isset($filters['unit_kerja_id'])) {
            $query->where('unit_kerja_id', $filters['unit_kerja_id']);
        }

        if (isset($filters['compliance_level'])) {
            $query->where('compliance_level', $filters['compliance_level']);
        }

        if (isset($filters['monitoring_date_from'])) {
            $query->where('monitoring_date', '>=', $filters['monitoring_date_from']);
        }

        if (isset($filters['monitoring_date_to'])) {
            $query->where('monitoring_date', '<=', $filters['monitoring_date_to']);
        }

        if (isset($filters['search'])) {
            $query->where(function ($q) use ($filters) {
                $q->where('monitoring_code', 'like', '%' . $filters['search'] . '%')
                  ->orWhere('title', 'like', '%' . $filters['search'] . '%')
                  ->orWhere('description', 'like', '%' . $filters['search'] . '%');
            });
        }

        // Order by
        $orderBy = $filters['order_by'] ?? 'monitoring_date';
        $orderDir = $filters['order_dir'] ?? 'desc';
        $query->orderBy($orderBy, $orderDir);

        return $query->paginate($perPage);
    }

    /**
     * Find SPMI monitoring by ID with relationships.
     */
    public function findById(int $id, array $with = []): ?SpmiMonitoring
    {
        $defaultWith = [
            'spmiStandard',
            'tahunAkademik',
            'monitoredBy',
            'unitKerja'
        ];

        $relations = !empty($with) ? $with : $defaultWith;

        return SpmiMonitoring::with($relations)->find($id);
    }

    /**
     * Create new SPMI monitoring.
     */
    public function create(array $data): SpmiMonitoring
    {
        return SpmiMonitoring::create($data);
    }

    /**
     * Update SPMI monitoring.
     */
    public function update(SpmiMonitoring $monitoring, array $data): bool
    {
        return $monitoring->update($data);
    }

    /**
     * Delete SPMI monitoring.
     */
    public function delete(SpmiMonitoring $monitoring): bool
    {
        return $monitoring->delete();
    }

    /**
     * Get completed monitorings.
     */
    public function getCompleted(): Collection
    {
        return SpmiMonitoring::completed()
            ->with(['spmiStandard', 'tahunAkademik', 'unitKerja'])
            ->orderBy('monitoring_date', 'desc')
            ->get();
    }

    /**
     * Get pending monitorings.
     */
    public function getPending(): Collection
    {
        return SpmiMonitoring::where('status', 'pending')
            ->with(['spmiStandard', 'tahunAkademik', 'unitKerja'])
            ->orderBy('monitoring_date', 'asc')
            ->get();
    }

    /**
     * Get monitorings by SPMI standard.
     */
    public function getByStandard(int $standardId): Collection
    {
        return SpmiMonitoring::byStandard($standardId)
            ->with(['tahunAkademik', 'monitoredBy', 'unitKerja'])
            ->orderBy('monitoring_date', 'desc')
            ->get();
    }

    /**
     * Get monitorings by tahun akademik.
     */
    public function getByTahunAkademik(int $tahunAkademikId): Collection
    {
        return SpmiMonitoring::where('tahun_akademik_id', $tahunAkademikId)
            ->with(['spmiStandard', 'monitoredBy', 'unitKerja'])
            ->orderBy('monitoring_date', 'desc')
            ->get();
    }

    /**
     * Get monitorings by unit kerja.
     */
    public function getByUnitKerja(int $unitKerjaId): Collection
    {
        return SpmiMonitoring::where('unit_kerja_id', $unitKerjaId)
            ->with(['spmiStandard', 'tahunAkademik', 'monitoredBy'])
            ->orderBy('monitoring_date', 'desc')
            ->get();
    }

    /**
     * Generate unique monitoring code.
     * Format: MON-YYYY-###  (e.g., MON-2025-001)
     */
    public function generateMonitoringCode(): string
    {
        $year = now()->year;
        $prefix = "MON-{$year}-";

        $lastMonitoring = SpmiMonitoring::where('monitoring_code', 'like', $prefix . '%')
            ->orderBy('monitoring_code', 'desc')
            ->first();

        if (!$lastMonitoring) {
            return $prefix . '001';
        }

        $lastNumber = (int) substr($lastMonitoring->monitoring_code, -3);
        $newNumber = str_pad($lastNumber + 1, 3, '0', STR_PAD_LEFT);

        return $prefix . $newNumber;
    }

    /**
     * Check if code already exists.
     */
    public function codeExists(string $code, ?int $exceptId = null): bool
    {
        $query = SpmiMonitoring::where('monitoring_code', $code);

        if ($exceptId) {
            $query->where('id', '!=', $exceptId);
        }

        return $query->exists();
    }

    /**
     * Get statistics for SPMI monitorings.
     * Returns counts by status, by compliance_level, and average compliance_score.
     */
    public function getStatistics(): array
    {
        $total = SpmiMonitoring::count();
        $completed = SpmiMonitoring::where('status', 'completed')->count();
        $pending = SpmiMonitoring::where('status', 'pending')->count();
        $in_progress = SpmiMonitoring::where('status', 'in_progress')->count();

        $complianceLevels = SpmiMonitoring::selectRaw('compliance_level, COUNT(*) as count')
            ->whereNotNull('compliance_level')
            ->groupBy('compliance_level')
            ->get()
            ->pluck('count', 'compliance_level')
            ->toArray();

        $monitoringTypes = SpmiMonitoring::selectRaw('monitoring_type, COUNT(*) as count')
            ->whereNotNull('monitoring_type')
            ->groupBy('monitoring_type')
            ->get()
            ->pluck('count', 'monitoring_type')
            ->toArray();

        $avgComplianceScore = SpmiMonitoring::whereNotNull('compliance_score')
            ->avg('compliance_score') ?? 0;

        return [
            'total' => $total,
            'completed' => $completed,
            'pending' => $pending,
            'in_progress' => $in_progress,
            'average_compliance_score' => round($avgComplianceScore, 2),
            'compliance_levels_count' => count($complianceLevels),
            'compliance_levels' => $complianceLevels,
            'monitoring_types_count' => count($monitoringTypes),
            'monitoring_types' => $monitoringTypes,
        ];
    }

    /**
     * Get dashboard data with comprehensive statistics.
     */
    public function getDashboardData(): array
    {
        $totalMonitorings = SpmiMonitoring::count();
        $completedMonitorings = SpmiMonitoring::completed()->count();
        $pendingMonitorings = SpmiMonitoring::where('status', 'pending')->count();
        $inProgressMonitorings = SpmiMonitoring::where('status', 'in_progress')->count();

        $excellent = SpmiMonitoring::where('compliance_level', 'excellent')->count();
        $good = SpmiMonitoring::where('compliance_level', 'good')->count();
        $fair = SpmiMonitoring::where('compliance_level', 'fair')->count();
        $poor = SpmiMonitoring::where('compliance_level', 'poor')->count();

        $avgScore = SpmiMonitoring::whereNotNull('compliance_score')
            ->avg('compliance_score') ?? 0;

        return [
            'total_monitorings' => $totalMonitorings,
            'completed' => $completedMonitorings,
            'pending' => $pendingMonitorings,
            'in_progress' => $inProgressMonitorings,
            'completion_rate' => $totalMonitorings > 0
                ? round(($completedMonitorings / $totalMonitorings) * 100, 2)
                : 0,
            'compliance_distribution' => [
                'excellent' => $excellent,
                'good' => $good,
                'fair' => $fair,
                'poor' => $poor,
            ],
            'average_compliance_score' => round($avgScore, 2),
            'recent_monitorings' => SpmiMonitoring::with(['spmiStandard', 'tahunAkademik'])
                ->latest('monitoring_date')
                ->limit(5)
                ->get(),
        ];
    }

    /**
     * Get monitorings by monitoring type.
     */
    public function getByMonitoringType(string $type): Collection
    {
        return SpmiMonitoring::where('monitoring_type', $type)
            ->with(['spmiStandard', 'tahunAkademik', 'unitKerja'])
            ->orderBy('monitoring_date', 'desc')
            ->get();
    }

    /**
     * Get monitorings by compliance level.
     */
    public function getByComplianceLevel(string $level): Collection
    {
        return SpmiMonitoring::where('compliance_level', $level)
            ->with(['spmiStandard', 'tahunAkademik', 'unitKerja'])
            ->orderBy('monitoring_date', 'desc')
            ->get();
    }
}
