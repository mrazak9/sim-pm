<?php

namespace App\Repositories;

use App\Models\AuditSchedule;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Pagination\LengthAwarePaginator;

class AuditScheduleRepository
{
    /**
     * Get all audit schedules with pagination.
     */
    public function all(array $filters = [], int $perPage = 15): LengthAwarePaginator
    {
        $query = AuditSchedule::with([
            'auditPlan',
            'unitKerja',
            'auditorLead',
            'auditors'
        ]);

        // Apply filters
        if (isset($filters['audit_plan_id'])) {
            $query->where('audit_plan_id', $filters['audit_plan_id']);
        }

        if (isset($filters['unit_kerja_id'])) {
            $query->where('unit_kerja_id', $filters['unit_kerja_id']);
        }

        if (isset($filters['auditor_lead_id'])) {
            $query->where('auditor_lead_id', $filters['auditor_lead_id']);
        }

        if (isset($filters['status'])) {
            $query->where('status', $filters['status']);
        }

        if (isset($filters['scheduled_from'])) {
            $query->where('scheduled_date', '>=', $filters['scheduled_from']);
        }

        if (isset($filters['scheduled_to'])) {
            $query->where('scheduled_date', '<=', $filters['scheduled_to']);
        }

        if (isset($filters['search'])) {
            $query->where(function ($q) use ($filters) {
                $q->where('location', 'like', '%' . $filters['search'] . '%')
                  ->orWhere('agenda', 'like', '%' . $filters['search'] . '%')
                  ->orWhereHas('unitKerja', function ($q2) use ($filters) {
                      $q2->where('nama', 'like', '%' . $filters['search'] . '%');
                  });
            });
        }

        // Order by
        $orderBy = $filters['order_by'] ?? 'scheduled_date';
        $orderDir = $filters['order_dir'] ?? 'asc';
        $query->orderBy($orderBy, $orderDir);

        return $query->paginate($perPage);
    }

    /**
     * Find audit schedule by ID.
     */
    public function findById(int $id): ?AuditSchedule
    {
        return AuditSchedule::with([
            'auditPlan.tahunAkademik',
            'unitKerja',
            'auditorLead',
            'auditors',
            'findings',
            'evidence'
        ])->find($id);
    }

    /**
     * Create new audit schedule.
     */
    public function create(array $data): AuditSchedule
    {
        return AuditSchedule::create($data);
    }

    /**
     * Update audit schedule.
     */
    public function update(AuditSchedule $auditSchedule, array $data): bool
    {
        return $auditSchedule->update($data);
    }

    /**
     * Delete audit schedule.
     */
    public function delete(AuditSchedule $auditSchedule): bool
    {
        return $auditSchedule->delete();
    }

    /**
     * Get scheduled audits.
     */
    public function getScheduled(): Collection
    {
        return AuditSchedule::scheduled()
            ->with(['auditPlan', 'unitKerja', 'auditorLead'])
            ->orderBy('scheduled_date', 'asc')
            ->get();
    }

    /**
     * Get upcoming audits.
     */
    public function getUpcoming(int $days = 7): Collection
    {
        return AuditSchedule::upcoming()
            ->where('scheduled_date', '<=', now()->addDays($days))
            ->with(['auditPlan', 'unitKerja', 'auditorLead'])
            ->orderBy('scheduled_date', 'asc')
            ->get();
    }

    /**
     * Get completed audits.
     */
    public function getCompleted(): Collection
    {
        return AuditSchedule::completed()
            ->with(['auditPlan', 'unitKerja', 'auditorLead'])
            ->orderBy('scheduled_date', 'desc')
            ->get();
    }

    /**
     * Get ongoing audits.
     */
    public function getOngoing(): Collection
    {
        return AuditSchedule::ongoing()
            ->with(['auditPlan', 'unitKerja', 'auditorLead'])
            ->orderBy('scheduled_date', 'desc')
            ->get();
    }

    /**
     * Get schedules by audit plan.
     */
    public function getByAuditPlan(int $auditPlanId): Collection
    {
        return AuditSchedule::where('audit_plan_id', $auditPlanId)
            ->with(['unitKerja', 'auditorLead'])
            ->orderBy('scheduled_date', 'asc')
            ->get();
    }

    /**
     * Get schedules by unit kerja.
     */
    public function getByUnitKerja(int $unitKerjaId): Collection
    {
        return AuditSchedule::where('unit_kerja_id', $unitKerjaId)
            ->with(['auditPlan', 'auditorLead'])
            ->orderBy('scheduled_date', 'desc')
            ->get();
    }

    /**
     * Get schedules by auditor.
     */
    public function getByAuditor(int $userId): Collection
    {
        return AuditSchedule::where('auditor_lead_id', $userId)
            ->orWhereHas('auditors', function ($query) use ($userId) {
                $query->where('user_id', $userId);
            })
            ->with(['auditPlan', 'unitKerja'])
            ->orderBy('scheduled_date', 'desc')
            ->get();
    }

    /**
     * Get calendar events (for calendar view).
     */
    public function getCalendarEvents(?string $start = null, ?string $end = null): array
    {
        $query = AuditSchedule::with(['unitKerja', 'auditPlan']);

        if ($start) {
            $query->where('scheduled_date', '>=', $start);
        }

        if ($end) {
            $query->where('scheduled_date', '<=', $end);
        }

        $schedules = $query->get();

        return $schedules->map(function ($schedule) {
            return [
                'id' => $schedule->id,
                'title' => $schedule->unitKerja->nama ?? 'Audit',
                'start' => $schedule->scheduled_date->toIso8601String(),
                'end' => $schedule->scheduled_date->addMinutes($schedule->estimated_duration)->toIso8601String(),
                'color' => $this->getStatusColor($schedule->status),
                'description' => $schedule->agenda,
            ];
        })->toArray();
    }

    /**
     * Get statistics.
     */
    public function getStatistics(): array
    {
        return [
            'total' => AuditSchedule::count(),
            'scheduled' => AuditSchedule::scheduled()->count(),
            'ongoing' => AuditSchedule::ongoing()->count(),
            'completed' => AuditSchedule::completed()->count(),
            'cancelled' => AuditSchedule::where('status', 'cancelled')->count(),
            'upcoming_7_days' => AuditSchedule::upcoming()->where('scheduled_date', '<=', now()->addDays(7))->count(),
        ];
    }

    /**
     * Assign auditors to schedule.
     */
    public function assignAuditors(AuditSchedule $schedule, array $auditors): void
    {
        $schedule->auditors()->sync($auditors);
    }

    /**
     * Get status color for calendar.
     */
    private function getStatusColor(string $status): string
    {
        return match($status) {
            'scheduled' => '#3b82f6',  // blue
            'ongoing' => '#f59e0b',    // orange
            'completed' => '#10b981',  // green
            'cancelled' => '#ef4444',  // red
            'rescheduled' => '#8b5cf6', // purple
            default => '#6b7280',      // gray
        };
    }
}
