<?php

namespace App\Repositories;

use App\Models\SurveyResponse;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Pagination\LengthAwarePaginator;

class SurveyResponseRepository
{
    /**
     * Find responses by survey ID with filters.
     */
    public function findBySurvey(int $surveyId, array $filters = [], int $perPage = 15): LengthAwarePaginator
    {
        $query = SurveyResponse::with([
            'user',
            'answers.question'
        ])
        ->where('survey_id', $surveyId);

        // Apply filters
        if (isset($filters['is_completed'])) {
            $query->where('is_completed', $filters['is_completed']);
        }

        if (isset($filters['user_id'])) {
            $query->where('user_id', $filters['user_id']);
        }

        if (isset($filters['date_from'])) {
            $query->whereDate('created_at', '>=', $filters['date_from']);
        }

        if (isset($filters['date_to'])) {
            $query->whereDate('created_at', '<=', $filters['date_to']);
        }

        // Order by
        $orderBy = $filters['order_by'] ?? 'created_at';
        $orderDir = $filters['order_dir'] ?? 'desc';
        $query->orderBy($orderBy, $orderDir);

        return $query->paginate($perPage);
    }

    /**
     * Find responses by user.
     */
    public function findByUser(int $userId): Collection
    {
        return SurveyResponse::byUser($userId)
            ->with(['survey', 'answers.question'])
            ->orderBy('created_at', 'desc')
            ->get();
    }

    /**
     * Find response by ID.
     */
    public function findById(int $id): ?SurveyResponse
    {
        return SurveyResponse::with([
            'survey',
            'user',
            'answers.question'
        ])->find($id);
    }

    /**
     * Create new response.
     */
    public function create(array $data): SurveyResponse
    {
        return SurveyResponse::create($data);
    }

    /**
     * Update response.
     */
    public function update(SurveyResponse $response, array $data): SurveyResponse
    {
        $response->update($data);
        return $response->fresh();
    }

    /**
     * Delete response.
     */
    public function delete(SurveyResponse $response): bool
    {
        return $response->delete();
    }

    /**
     * Generate unique response code.
     * Format: RESP-YYYY-### (e.g., RESP-2025-001)
     */
    public function generateResponseCode(): string
    {
        $year = now()->year;
        $prefix = "RESP-{$year}-";

        $lastResponse = SurveyResponse::whereYear('created_at', $year)
            ->count();

        $newNumber = str_pad($lastResponse + 1, 3, '0', STR_PAD_LEFT);

        return $prefix . $newNumber;
    }

    /**
     * Get statistics for a survey.
     */
    public function getStatistics(int $surveyId): array
    {
        $totalResponses = SurveyResponse::where('survey_id', $surveyId)->count();

        $completedResponses = SurveyResponse::where('survey_id', $surveyId)
            ->where('is_completed', true)
            ->count();

        $inProgressResponses = SurveyResponse::where('survey_id', $surveyId)
            ->where('is_completed', false)
            ->count();

        $completionRate = $totalResponses > 0
            ? round(($completedResponses / $totalResponses) * 100, 2)
            : 0;

        $averageCompletionTime = SurveyResponse::where('survey_id', $surveyId)
            ->where('is_completed', true)
            ->whereNotNull('started_at')
            ->whereNotNull('completed_at')
            ->selectRaw('AVG(TIMESTAMPDIFF(MINUTE, started_at, completed_at)) as avg_time')
            ->value('avg_time');

        $anonymousResponses = SurveyResponse::where('survey_id', $surveyId)
            ->whereNull('user_id')
            ->count();

        return [
            'total_responses' => $totalResponses,
            'completed_responses' => $completedResponses,
            'in_progress_responses' => $inProgressResponses,
            'completion_rate' => $completionRate,
            'average_completion_time_minutes' => round($averageCompletionTime ?? 0, 2),
            'anonymous_responses' => $anonymousResponses,
        ];
    }

    /**
     * Check if user has already responded to survey.
     */
    public function hasUserResponded(int $surveyId, int $userId): bool
    {
        return SurveyResponse::where('survey_id', $surveyId)
            ->where('user_id', $userId)
            ->exists();
    }

    /**
     * Get completed responses by survey.
     */
    public function getCompletedBySurvey(int $surveyId): Collection
    {
        return SurveyResponse::bySurvey($surveyId)
            ->completed()
            ->with(['user', 'answers.question'])
            ->orderBy('completed_at', 'desc')
            ->get();
    }
}
