<?php

namespace App\Repositories;

use App\Models\IKUProgress;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Pagination\LengthAwarePaginator;

class IKUProgressRepository
{
    /**
     * Get all IKU progress with optional filters and pagination
     */
    public function getAll(array $filters = [], int $perPage = 15): LengthAwarePaginator
    {
        $query = IKUProgress::with([
            'target.iku',
            'target.tahunAkademik',
            'target.unitKerja',
            'target.programStudi',
            'creator'
        ]);

        // Apply filters
        if (isset($filters['iku_target_id'])) {
            $query->where('iku_target_id', $filters['iku_target_id']);
        }

        if (isset($filters['start_date']) && isset($filters['end_date'])) {
            $query->byDateRange($filters['start_date'], $filters['end_date']);
        }

        if (isset($filters['recent_days'])) {
            $query->recent($filters['recent_days']);
        }

        if (isset($filters['search'])) {
            $search = $filters['search'];
            $query->where(function($q) use ($search) {
                $q->where('keterangan', 'LIKE', "%{$search}%")
                  ->orWhereHas('target.iku', function($subQ) use ($search) {
                      $subQ->where('nama_iku', 'LIKE', "%{$search}%")
                           ->orWhere('kode_iku', 'LIKE', "%{$search}%");
                  });
            });
        }

        return $query->orderBy('tanggal_capaian', 'desc')
                     ->paginate($perPage);
    }

    /**
     * Find IKU progress by ID with relationships
     */
    public function findById(int $id): ?IKUProgress
    {
        return IKUProgress::with([
            'target.iku',
            'target.tahunAkademik',
            'target.unitKerja',
            'target.programStudi',
            'creator'
        ])->find($id);
    }

    /**
     * Create new IKU progress
     */
    public function create(array $data): IKUProgress
    {
        return IKUProgress::create($data);
    }

    /**
     * Update existing IKU progress
     */
    public function update(IKUProgress $progress, array $data): IKUProgress
    {
        $progress->update($data);
        return $progress->fresh(['target.iku', 'creator']);
    }

    /**
     * Delete IKU progress
     */
    public function delete(IKUProgress $progress): bool
    {
        return $progress->delete();
    }

    /**
     * Get progress summary by target
     */
    public function getSummaryByTarget(int $targetId): array
    {
        $progress = IKUProgress::where('iku_target_id', $targetId)
            ->with(['creator'])
            ->orderBy('tanggal_capaian', 'desc')
            ->get();

        return [
            'total_entries' => $progress->count(),
            'total_nilai_capaian' => $progress->sum('nilai_capaian'),
            'avg_persentase_capaian' => $progress->avg('persentase_capaian'),
            'latest_progress' => $progress->first(),
            'progress_list' => $progress,
        ];
    }

    /**
     * Get recent progress entries
     */
    public function getRecent(int $days = 30, int $limit = 10): Collection
    {
        return IKUProgress::with(['target.iku', 'creator'])
            ->recent($days)
            ->limit($limit)
            ->get();
    }

    /**
     * Get progress by date range
     */
    public function getByDateRange(string $startDate, string $endDate): Collection
    {
        return IKUProgress::with(['target.iku', 'creator'])
            ->byDateRange($startDate, $endDate)
            ->get();
    }

    /**
     * Get progress statistics for dashboard
     */
    public function getStatistics(): array
    {
        $totalProgress = IKUProgress::count();
        $recentProgress = IKUProgress::recent(30)->count();
        $withDocuments = IKUProgress::whereNotNull('bukti_dokumen')->count();

        return [
            'total_progress' => $totalProgress,
            'recent_30_days' => $recentProgress,
            'with_documents' => $withDocuments,
            'without_documents' => $totalProgress - $withDocuments,
        ];
    }
}
