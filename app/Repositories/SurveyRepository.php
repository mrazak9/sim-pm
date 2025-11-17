<?php

namespace App\Repositories;

use App\Models\Survey;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Pagination\LengthAwarePaginator;

class SurveyRepository
{
    /**
     * Get all surveys with pagination and filters.
     */
    public function findAll(array $filters = [], int $perPage = 15): LengthAwarePaginator
    {
        $query = Survey::with([
            'unitKerja',
            'creator',
            'questions',
            'responses'
        ]);

        // Apply filters
        if (isset($filters['unit_kerja_id'])) {
            $query->where('unit_kerja_id', $filters['unit_kerja_id']);
        }

        if (isset($filters['status'])) {
            $query->where('status', $filters['status']);
        }

        if (isset($filters['type'])) {
            $query->where('type', $filters['type']);
        }

        if (isset($filters['created_by'])) {
            $query->where('created_by', $filters['created_by']);
        }

        if (isset($filters['search'])) {
            $query->where(function ($q) use ($filters) {
                $q->where('title', 'like', '%' . $filters['search'] . '%')
                  ->orWhere('description', 'like', '%' . $filters['search'] . '%');
            });
        }

        // Order by
        $orderBy = $filters['order_by'] ?? 'created_at';
        $orderDir = $filters['order_dir'] ?? 'desc';
        $query->orderBy($orderBy, $orderDir);

        return $query->paginate($perPage);
    }

    /**
     * Find survey by ID with relationships.
     */
    public function findById(int $id, array $with = []): ?Survey
    {
        $defaultWith = [
            'unitKerja',
            'creator',
            'questions',
            'responses'
        ];

        $relations = !empty($with) ? $with : $defaultWith;

        return Survey::with($relations)->find($id);
    }

    /**
     * Create new survey.
     */
    public function create(array $data): Survey
    {
        return Survey::create($data);
    }

    /**
     * Update survey.
     */
    public function update(Survey $survey, array $data): Survey
    {
        $survey->update($data);
        return $survey->fresh();
    }

    /**
     * Delete survey.
     */
    public function delete(Survey $survey): bool
    {
        return $survey->delete();
    }

    /**
     * Generate unique survey code.
     * Format: SURV-YYYY-### (e.g., SURV-2025-001)
     */
    public function generateSurveyCode(): string
    {
        $year = now()->year;
        $prefix = "SURV-{$year}-";

        $lastSurvey = Survey::where('id', 'like', $prefix . '%')
            ->orderBy('id', 'desc')
            ->first();

        if (!$lastSurvey) {
            return $prefix . '001';
        }

        // Extract the last sequence number from the ID or created_at
        $lastNumber = Survey::whereYear('created_at', $year)
            ->count();

        $newNumber = str_pad($lastNumber + 1, 3, '0', STR_PAD_LEFT);

        return $prefix . $newNumber;
    }

    /**
     * Get published surveys.
     */
    public function getPublishedSurveys(): Collection
    {
        return Survey::published()
            ->with(['unitKerja', 'creator'])
            ->orderBy('created_at', 'desc')
            ->get();
    }

    /**
     * Get active surveys.
     */
    public function getActiveSurveys(): Collection
    {
        return Survey::active()
            ->with(['unitKerja', 'creator'])
            ->orderBy('created_at', 'desc')
            ->get();
    }

    /**
     * Get surveys by creator.
     */
    public function getByCreator(int $userId): Collection
    {
        return Survey::byCreator($userId)
            ->with(['unitKerja', 'questions', 'responses'])
            ->orderBy('created_at', 'desc')
            ->get();
    }

    /**
     * Get statistics for surveys.
     * Returns total, published, active, and responses count.
     */
    public function getStatistics(): array
    {
        $total = Survey::count();
        $published = Survey::where('status', 'published')->count();
        $draft = Survey::where('status', 'draft')->count();
        $closed = Survey::where('status', 'closed')->count();

        $active = Survey::active()->count();

        $totalResponses = Survey::withCount('responses')
            ->get()
            ->sum('responses_count');

        $completedResponses = Survey::withCount(['responses' => function ($query) {
            $query->where('is_completed', true);
        }])
            ->get()
            ->sum('responses_count');

        $responseRate = $totalResponses > 0
            ? round(($completedResponses / $totalResponses) * 100, 2)
            : 0;

        return [
            'total' => $total,
            'published' => $published,
            'draft' => $draft,
            'closed' => $closed,
            'active' => $active,
            'total_responses' => $totalResponses,
            'completed_responses' => $completedResponses,
            'response_rate' => $responseRate,
        ];
    }

    /**
     * Get surveys by unit kerja.
     */
    public function getByUnitKerja(int $unitKerjaId): Collection
    {
        return Survey::where('unit_kerja_id', $unitKerjaId)
            ->with(['creator', 'questions', 'responses'])
            ->orderBy('created_at', 'desc')
            ->get();
    }

    /**
     * Get surveys by type.
     */
    public function getByType(string $type): Collection
    {
        return Survey::byType($type)
            ->with(['unitKerja', 'creator'])
            ->orderBy('created_at', 'desc')
            ->get();
    }
}
