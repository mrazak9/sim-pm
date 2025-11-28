<?php

namespace App\Repositories;

use App\Models\RTM;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Pagination\LengthAwarePaginator;

class RTMRepository
{
    /**
     * Get all RTMs with pagination and filters.
     */
    public function all(array $filters = [], int $perPage = 15): LengthAwarePaginator
    {
        $query = RTM::with([
            'tahunAkademik',
            'chairman',
            'secretary'
        ])
        ->withCount(['participants', 'actionItems']);

        // Apply filters
        if (isset($filters['tahun_akademik_id'])) {
            $query->where('tahun_akademik_id', $filters['tahun_akademik_id']);
        }

        if (isset($filters['status'])) {
            $query->where('status', $filters['status']);
        }

        if (isset($filters['year'])) {
            $query->whereYear('meeting_date', $filters['year']);
        }

        if (isset($filters['month'])) {
            $query->whereMonth('meeting_date', $filters['month']);
        }

        if (isset($filters['meeting_date_from'])) {
            $query->where('meeting_date', '>=', $filters['meeting_date_from']);
        }

        if (isset($filters['meeting_date_to'])) {
            $query->where('meeting_date', '<=', $filters['meeting_date_to']);
        }

        if (isset($filters['chairman_id'])) {
            $query->where('chairman_id', $filters['chairman_id']);
        }

        if (isset($filters['search'])) {
            $query->where(function ($q) use ($filters) {
                $q->where('rtm_code', 'like', '%' . $filters['search'] . '%')
                  ->orWhere('title', 'like', '%' . $filters['search'] . '%')
                  ->orWhere('agenda', 'like', '%' . $filters['search'] . '%');
            });
        }

        // Order by
        $orderBy = $filters['order_by'] ?? 'meeting_date';
        $orderDir = $filters['order_dir'] ?? 'desc';
        $query->orderBy($orderBy, $orderDir);

        return $query->paginate($perPage);
    }

    /**
     * Find RTM by ID with relationships.
     */
    public function findById(int $id, array $with = []): ?RTM
    {
        $defaultWith = [
            'tahunAkademik',
            'chairman',
            'secretary',
            'participants',
            'actionItems.pic',
            'actionItems.unitKerja'
        ];

        $relations = !empty($with) ? $with : $defaultWith;

        return RTM::with($relations)->find($id);
    }

    /**
     * Create new RTM.
     */
    public function create(array $data): RTM
    {
        return RTM::create($data);
    }

    /**
     * Update RTM.
     */
    public function update(RTM $rtm, array $data): bool
    {
        return $rtm->update($data);
    }

    /**
     * Delete RTM.
     */
    public function delete(RTM $rtm): bool
    {
        return $rtm->delete();
    }

    /**
     * Get completed RTMs.
     */
    public function getCompleted(): Collection
    {
        return RTM::completed()
            ->with(['tahunAkademik', 'chairman', 'secretary'])
            ->orderBy('meeting_date', 'desc')
            ->get();
    }

    /**
     * Get draft RTMs.
     */
    public function getDraft(): Collection
    {
        return RTM::where('status', 'draft')
            ->with(['tahunAkademik', 'chairman'])
            ->orderBy('meeting_date', 'asc')
            ->get();
    }

    /**
     * Get upcoming RTMs within X days.
     */
    public function getUpcoming(int $days = 30): Collection
    {
        $upcomingDate = now()->addDays($days)->toDateString();

        return RTM::where('status', '!=', 'completed')
            ->where('meeting_date', '>=', now()->toDateString())
            ->where('meeting_date', '<=', $upcomingDate)
            ->with(['tahunAkademik', 'chairman', 'secretary'])
            ->orderBy('meeting_date', 'asc')
            ->get();
    }

    /**
     * Get RTMs by tahun akademik.
     */
    public function getByTahunAkademik(int $tahunAkademikId): Collection
    {
        return RTM::where('tahun_akademik_id', $tahunAkademikId)
            ->with(['chairman', 'secretary', 'actionItems'])
            ->orderBy('meeting_date', 'desc')
            ->get();
    }

    /**
     * Generate unique RTM code.
     * Format: RTM-YYYY-### (e.g., RTM-2025-001)
     */
    public function generateRTMCode(): string
    {
        $year = now()->year;
        $prefix = "RTM-{$year}-";

        $lastRTM = RTM::where('rtm_code', 'like', $prefix . '%')
            ->orderBy('rtm_code', 'desc')
            ->first();

        if (!$lastRTM) {
            return $prefix . '001';
        }

        $lastNumber = (int) substr($lastRTM->rtm_code, -3);
        $newNumber = str_pad($lastNumber + 1, 3, '0', STR_PAD_LEFT);

        return $prefix . $newNumber;
    }

    /**
     * Check if code already exists.
     */
    public function codeExists(string $code, ?int $exceptId = null): bool
    {
        $query = RTM::where('rtm_code', $code);

        if ($exceptId) {
            $query->where('id', '!=', $exceptId);
        }

        return $query->exists();
    }

    /**
     * Get statistics for RTMs.
     * Returns counts by status, total action items, completion rates.
     */
    public function getStatistics(): array
    {
        $total = RTM::count();
        $planned = RTM::where('status', 'planned')->count();
        $ongoing = RTM::where('status', 'ongoing')->count();
        $completed = RTM::where('status', 'completed')->count();

        // RTMs happening this month
        $upcomingThisMonth = RTM::where('status', '!=', 'completed')
            ->where('status', '!=', 'cancelled')
            ->whereYear('meeting_date', now()->year)
            ->whereMonth('meeting_date', now()->month)
            ->count();

        return [
            'total' => $total,
            'planned' => $planned,
            'ongoing' => $ongoing,
            'completed' => $completed,
            'upcoming_this_month' => $upcomingThisMonth,
            'completion_rate' => $total > 0
                ? round(($completed / $total) * 100, 2)
                : 0,
            'total_action_items' => RTM::withCount('actionItems')
                ->get()
                ->sum('action_items_count'),
            'upcoming_7_days' => RTM::where('status', '!=', 'completed')
                ->where('status', '!=', 'cancelled')
                ->where('meeting_date', '<=', now()->addDays(7))
                ->where('meeting_date', '>=', now()->toDateString())
                ->count(),
        ];
    }

    /**
     * Add participant to RTM.
     */
    public function addParticipant(int $rtmId, int $userId, string $role = 'participant'): void
    {
        $rtm = RTM::findOrFail($rtmId);

        // Sync to avoid duplicates
        $existingParticipants = $rtm->participants()->pluck('user_id')->toArray();

        if (!in_array($userId, $existingParticipants)) {
            $rtm->participants()->attach($userId, ['role' => $role]);
        }
    }

    /**
     * Remove participant from RTM.
     */
    public function removeParticipant(int $rtmId, int $userId): void
    {
        $rtm = RTM::findOrFail($rtmId);
        $rtm->participants()->detach($userId);
    }

    /**
     * Mark attendance for a participant.
     */
    public function markAttendance(int $rtmId, int $userId, bool $isPresent = true): void
    {
        $rtm = RTM::findOrFail($rtmId);

        $rtm->participants()
            ->wherePivot('user_id', $userId)
            ->updateExistingPivot($userId, ['is_present' => $isPresent]);
    }

    /**
     * Get RTMs by chairman.
     */
    public function getByChairman(int $chairmanId): Collection
    {
        return RTM::where('chairman_id', $chairmanId)
            ->with(['tahunAkademik', 'secretary', 'actionItems'])
            ->orderBy('meeting_date', 'desc')
            ->get();
    }

    /**
     * Get RTM with participant details.
     */
    public function findByIdWithParticipants(int $id): ?RTM
    {
        return RTM::with([
            'participants',
            'tahunAkademik',
            'chairman',
            'secretary'
        ])->find($id);
    }

    /**
     * Get attendance summary for RTM.
     */
    public function getAttendanceSummary(int $rtmId): array
    {
        $rtm = RTM::findOrFail($rtmId);
        $participants = $rtm->participants()->get();

        $present = $participants->where('pivot.is_present', true)->count();
        $absent = $participants->where('pivot.is_present', false)->count();
        $total = $participants->count();

        return [
            'total_participants' => $total,
            'present' => $present,
            'absent' => $absent,
            'attendance_rate' => $total > 0
                ? round(($present / $total) * 100, 2)
                : 0,
        ];
    }

    /**
     * Get dashboard data for RTMs.
     */
    public function getDashboardData(): array
    {
        $totalRTMs = RTM::count();
        $completedRTMs = RTM::completed()->count();
        $upcomingRTMs = RTM::where('status', '!=', 'completed')
            ->where('meeting_date', '>=', now()->toDateString())
            ->where('meeting_date', '<=', now()->addDays(30)->toDateString())
            ->count();

        return [
            'total_rtms' => $totalRTMs,
            'completed' => $completedRTMs,
            'upcoming_30_days' => $upcomingRTMs,
            'completion_rate' => $totalRTMs > 0
                ? round(($completedRTMs / $totalRTMs) * 100, 2)
                : 0,
            'recent_rtms' => RTM::with(['tahunAkademik', 'chairman'])
                ->latest('meeting_date')
                ->limit(5)
                ->get(),
        ];
    }
}
