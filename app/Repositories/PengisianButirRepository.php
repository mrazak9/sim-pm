<?php

namespace App\Repositories;

use App\Models\PengisianButir;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Support\Collection;

class PengisianButirRepository
{
    protected PengisianButir $model;

    public function __construct(PengisianButir $model)
    {
        $this->model = $model;
    }

    /**
     * Get all pengisian butir
     */
    public function all(): Collection
    {
        return $this->model->with(['periodeAkreditasi', 'butirAkreditasi', 'picUser', 'reviewer'])
            ->orderBy('created_at', 'desc')
            ->get();
    }

    /**
     * Get all pengisian butir with filters and pagination
     */
    public function paginate(array $filters = [], int|string $perPage = 15): mixed
    {
        $query = $this->model->with(['periodeAkreditasi', 'butirAkreditasi', 'picUser', 'reviewer']);

        // Filter by periode_akreditasi_id
        if (isset($filters['periode_akreditasi_id'])) {
            $query->where('periode_akreditasi_id', $filters['periode_akreditasi_id']);
        }

        // Filter by butir_akreditasi_id
        if (isset($filters['butir_akreditasi_id'])) {
            $query->where('butir_akreditasi_id', $filters['butir_akreditasi_id']);
        }

        // Filter by status
        if (isset($filters['status'])) {
            $query->where('status', $filters['status']);
        }

        // Filter by is_complete
        if (isset($filters['is_complete'])) {
            $query->where('is_complete', $filters['is_complete']);
        }

        // Filter by pic_user_id
        if (isset($filters['pic_user_id'])) {
            $query->where('pic_user_id', $filters['pic_user_id']);
        }

        // Filter by reviewed_by
        if (isset($filters['reviewed_by'])) {
            $query->where('reviewed_by', $filters['reviewed_by']);
        }

        // Search
        if (isset($filters['search'])) {
            $search = $filters['search'];
            $query->where(function($q) use ($search) {
                $q->where('konten', 'LIKE', "%{$search}%")
                  ->orWhere('konten_plain', 'LIKE', "%{$search}%")
                  ->orWhere('notes', 'LIKE', "%{$search}%")
                  ->orWhere('review_notes', 'LIKE', "%{$search}%");
            });
        }

        // Sorting
        $sortBy = $filters['sort_by'] ?? 'created_at';
        $sortOrder = $filters['sort_order'] ?? 'desc';

        $query->orderBy($sortBy, $sortOrder);

        // Handle 'all' for non-paginated results
        if ($perPage === 'all') {
            return $query->get();
        }

        return $query->paginate((int) $perPage);
    }

    /**
     * Find pengisian butir by ID
     */
    public function findById(int $id, array $with = []): ?PengisianButir
    {
        $query = $this->model->query();

        $defaultWith = ['periodeAkreditasi', 'butirAkreditasi', 'picUser', 'reviewer'];

        if (!empty($with)) {
            $query->with($with);
        } else {
            $query->with($defaultWith);
        }

        return $query->find($id);
    }

    /**
     * Create new pengisian butir
     */
    public function create(array $data): PengisianButir
    {
        return $this->model->create($data);
    }

    /**
     * Update pengisian butir
     */
    public function update(PengisianButir $pengisianButir, array $data): PengisianButir
    {
        $pengisianButir->update($data);
        return $pengisianButir->fresh(['periodeAkreditasi', 'butirAkreditasi', 'picUser', 'reviewer']);
    }

    /**
     * Delete pengisian butir
     */
    public function delete(PengisianButir $pengisianButir): bool
    {
        return $pengisianButir->delete();
    }

    /**
     * Get pengisian butir by periode
     */
    public function getByPeriode(int $periodeId): Collection
    {
        return $this->model->byPeriode($periodeId)
            ->with(['butirAkreditasi', 'picUser', 'reviewer'])
            ->orderBy('created_at', 'desc')
            ->get();
    }

    /**
     * Get pengisian butir by status
     */
    public function getByStatus(string $status): Collection
    {
        return $this->model->byStatus($status)
            ->with(['periodeAkreditasi', 'butirAkreditasi', 'picUser', 'reviewer'])
            ->orderBy('created_at', 'desc')
            ->get();
    }

    /**
     * Get pengisian butir summary for a periode
     */
    public function getSummary(int $periodeId): array
    {
        $query = $this->model->where('periode_akreditasi_id', $periodeId);

        return [
            'total' => $query->count(),
            'complete' => $query->clone()->where('is_complete', true)->count(),
            'incomplete' => $query->clone()->where('is_complete', false)->count(),
            'by_status' => $query->clone()
                ->selectRaw('status, COUNT(*) as count')
                ->groupBy('status')
                ->pluck('count', 'status')
                ->toArray(),
            'draft' => $query->clone()->where('status', 'draft')->count(),
            'submitted' => $query->clone()->where('status', 'submitted')->count(),
            'review' => $query->clone()->where('status', 'review')->count(),
            'revision' => $query->clone()->where('status', 'revision')->count(),
            'approved' => $query->clone()->where('status', 'approved')->count(),
            'avg_completion' => $query->clone()->avg('completion_percentage') ?? 0,
        ];
    }

    /**
     * Get draft pengisian butir
     */
    public function getDraft(): Collection
    {
        return $this->model->draft()
            ->with(['periodeAkreditasi', 'butirAkreditasi', 'picUser'])
            ->orderBy('created_at', 'desc')
            ->get();
    }

    /**
     * Get approved pengisian butir
     */
    public function getApproved(): Collection
    {
        return $this->model->approved()
            ->with(['periodeAkreditasi', 'butirAkreditasi', 'picUser', 'reviewer'])
            ->orderBy('reviewed_at', 'desc')
            ->get();
    }

    /**
     * Get complete pengisian butir
     */
    public function getComplete(): Collection
    {
        return $this->model->complete()
            ->with(['periodeAkreditasi', 'butirAkreditasi', 'picUser', 'reviewer'])
            ->orderBy('created_at', 'desc')
            ->get();
    }

    /**
     * Get incomplete pengisian butir
     */
    public function getIncomplete(): Collection
    {
        return $this->model->incomplete()
            ->with(['periodeAkreditasi', 'butirAkreditasi', 'picUser'])
            ->orderBy('created_at', 'desc')
            ->get();
    }

    /**
     * Get pengisian butir by user (PIC)
     */
    public function getByUser(int $userId): Collection
    {
        return $this->model->where('pic_user_id', $userId)
            ->with(['periodeAkreditasi', 'butirAkreditasi', 'reviewer'])
            ->orderBy('created_at', 'desc')
            ->get();
    }

    /**
     * Get pengisian butir by reviewer
     */
    public function getByReviewer(int $reviewerId): Collection
    {
        return $this->model->where('reviewed_by', $reviewerId)
            ->with(['periodeAkreditasi', 'butirAkreditasi', 'picUser'])
            ->orderBy('reviewed_at', 'desc')
            ->get();
    }

    /**
     * Get pengisian butir for review (submitted or in review)
     */
    public function getForReview(): Collection
    {
        return $this->model->whereIn('status', ['submitted', 'review'])
            ->with(['periodeAkreditasi', 'butirAkreditasi', 'picUser'])
            ->orderBy('created_at', 'asc')
            ->get();
    }

    /**
     * Get pengisian butir needing revision
     */
    public function getNeedingRevision(): Collection
    {
        return $this->model->where('status', 'revision')
            ->with(['periodeAkreditasi', 'butirAkreditasi', 'picUser', 'reviewer'])
            ->orderBy('reviewed_at', 'desc')
            ->get();
    }

    /**
     * Get statistics
     */
    public function getStatistics(): array
    {
        return [
            'total' => $this->model->count(),
            'complete' => $this->model->where('is_complete', true)->count(),
            'incomplete' => $this->model->where('is_complete', false)->count(),
            'by_status' => $this->model->selectRaw('status, COUNT(*) as count')
                ->groupBy('status')
                ->pluck('count', 'status')
                ->toArray(),
            'avg_completion_percentage' => round($this->model->avg('completion_percentage') ?? 0, 2),
            'pending_review' => $this->model->whereIn('status', ['submitted', 'review'])->count(),
            'need_revision' => $this->model->where('status', 'revision')->count(),
        ];
    }

    /**
     * Check if pengisian exists for periode and butir
     */
    public function existsForPeriodeAndButir(int $periodeId, int $butirId): bool
    {
        return $this->model->where('periode_akreditasi_id', $periodeId)
            ->where('butir_akreditasi_id', $butirId)
            ->exists();
    }

    /**
     * Find pengisian by periode and butir
     */
    public function findByPeriodeAndButir(int $periodeId, int $butirId): ?PengisianButir
    {
        return $this->model->where('periode_akreditasi_id', $periodeId)
            ->where('butir_akreditasi_id', $butirId)
            ->with(['periodeAkreditasi', 'butirAkreditasi', 'picUser', 'reviewer'])
            ->first();
    }
}
