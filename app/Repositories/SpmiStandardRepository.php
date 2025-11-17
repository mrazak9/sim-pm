<?php

namespace App\Repositories;

use App\Models\SpmiStandard;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Pagination\LengthAwarePaginator;

class SpmiStandardRepository
{
    /**
     * Get all SPMI standards with pagination and filters.
     */
    public function all(array $filters = [], int $perPage = 15): LengthAwarePaginator
    {
        $query = SpmiStandard::with([
            'unitKerja',
            'creator',
            'approver',
            'indicators'
        ]);

        // Apply filters
        if (isset($filters['unit_kerja_id'])) {
            $query->where('unit_kerja_id', $filters['unit_kerja_id']);
        }

        if (isset($filters['status'])) {
            $query->where('status', $filters['status']);
        }

        if (isset($filters['category'])) {
            $query->where('category', $filters['category']);
        }

        if (isset($filters['search'])) {
            $query->where(function ($q) use ($filters) {
                $q->where('code', 'like', '%' . $filters['search'] . '%')
                  ->orWhere('name', 'like', '%' . $filters['search'] . '%')
                  ->orWhere('description', 'like', '%' . $filters['search'] . '%');
            });
        }

        // Order by
        $orderBy = $filters['order_by'] ?? 'code';
        $orderDir = $filters['order_dir'] ?? 'asc';
        $query->orderBy($orderBy, $orderDir);

        return $query->paginate($perPage);
    }

    /**
     * Find SPMI standard by ID with relationships.
     */
    public function findById(int $id, array $with = []): ?SpmiStandard
    {
        $defaultWith = [
            'unitKerja',
            'creator',
            'approver',
            'indicators.pic',
            'monitorings.tahunAkademik'
        ];

        $relations = !empty($with) ? $with : $defaultWith;

        return SpmiStandard::with($relations)->find($id);
    }

    /**
     * Create new SPMI standard.
     */
    public function create(array $data): SpmiStandard
    {
        return SpmiStandard::create($data);
    }

    /**
     * Update SPMI standard.
     */
    public function update(SpmiStandard $standard, array $data): bool
    {
        return $standard->update($data);
    }

    /**
     * Delete SPMI standard.
     */
    public function delete(SpmiStandard $standard): bool
    {
        return $standard->delete();
    }

    /**
     * Get only active SPMI standards.
     */
    public function getActive(): Collection
    {
        return SpmiStandard::active()
            ->with(['unitKerja', 'indicators'])
            ->orderBy('code', 'asc')
            ->get();
    }

    /**
     * Get SPMI standards by category.
     */
    public function getByCategory(string $category): Collection
    {
        return SpmiStandard::byCategory($category)
            ->with(['unitKerja', 'indicators'])
            ->orderBy('code', 'asc')
            ->get();
    }

    /**
     * Get unique categories.
     */
    public function getCategories(): Collection
    {
        return SpmiStandard::whereNotNull('category')
            ->distinct()
            ->pluck('category');
    }

    /**
     * Get SPMI standards due for review.
     * Returns standards where review_date is within X days from now.
     */
    public function getDueForReview(int $days = 30): Collection
    {
        $dueDate = now()->addDays($days)->toDateString();

        return SpmiStandard::whereNotNull('review_date')
            ->where('review_date', '<=', $dueDate)
            ->where('review_date', '>=', now()->toDateString())
            ->with(['unitKerja', 'creator'])
            ->orderBy('review_date', 'asc')
            ->get();
    }

    /**
     * Get statistics for SPMI standards.
     * Returns counts by status, by category, total, and indicators count.
     */
    public function getStatistics(): array
    {
        $total = SpmiStandard::count();
        $active = SpmiStandard::where('status', 'active')->count();
        $draft = SpmiStandard::where('status', 'draft')->count();
        $inactive = SpmiStandard::where('status', 'inactive')->count();

        $categories = SpmiStandard::selectRaw('category, COUNT(*) as count')
            ->whereNotNull('category')
            ->groupBy('category')
            ->get()
            ->pluck('count', 'category')
            ->toArray();

        return [
            'total' => $total,
            'active' => $active,
            'draft' => $draft,
            'inactive' => $inactive,
            'categories_count' => count($categories),
            'categories' => $categories,
            'total_indicators' => SpmiStandard::withCount('indicators')
                ->get()
                ->sum('indicators_count'),
        ];
    }

    /**
     * Get standards by unit kerja.
     */
    public function getByUnitKerja(int $unitKerjaId): Collection
    {
        return SpmiStandard::where('unit_kerja_id', $unitKerjaId)
            ->with(['indicators', 'approver'])
            ->orderBy('code', 'asc')
            ->get();
    }

    /**
     * Check if code already exists.
     */
    public function codeExists(string $code, ?int $exceptId = null): bool
    {
        $query = SpmiStandard::where('code', $code);

        if ($exceptId) {
            $query->where('id', '!=', $exceptId);
        }

        return $query->exists();
    }

    /**
     * Get approved standards.
     */
    public function getApproved(): Collection
    {
        return SpmiStandard::whereNotNull('approved_at')
            ->with(['unitKerja', 'approver'])
            ->orderBy('approved_at', 'desc')
            ->get();
    }

    /**
     * Get pending approval standards.
     */
    public function getPendingApproval(): Collection
    {
        return SpmiStandard::whereNull('approved_at')
            ->with(['unitKerja', 'creator'])
            ->orderBy('created_at', 'asc')
            ->get();
    }
}
