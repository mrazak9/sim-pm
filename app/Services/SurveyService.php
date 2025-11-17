<?php

namespace App\Services;

use App\Models\Survey;
use App\Repositories\SurveyRepository;
use App\Repositories\SurveyQuestionRepository;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Exception;

class SurveyService
{
    public function __construct(
        private SurveyRepository $repository,
        private SurveyQuestionRepository $questionRepository
    ) {}

    /**
     * Get all surveys with pagination.
     */
    public function getAllPaginated(array $filters = [], int $perPage = 15): LengthAwarePaginator
    {
        return $this->repository->findAll($filters, $perPage);
    }

    /**
     * Get survey by ID.
     */
    public function getById(int $id): ?Survey
    {
        return $this->repository->findById($id);
    }

    /**
     * Create new survey with auto-code generation and transaction.
     */
    public function create(array $data): Survey
    {
        DB::beginTransaction();
        try {
            // Generate survey code
            $data['survey_code'] = $this->repository->generateSurveyCode();

            // Set status and creator
            $data['status'] = $data['status'] ?? 'draft';
            $data['created_by'] = auth()->id();

            // Validate dates
            if (isset($data['start_date']) && isset($data['end_date'])) {
                $this->validateDates($data['start_date'], $data['end_date']);
            }

            $survey = $this->repository->create($data);

            DB::commit();
            Log::info('Survey created', ['id' => $survey->id, 'code' => $survey->survey_code]);

            return $survey;
        } catch (Exception $e) {
            DB::rollBack();
            Log::error('Failed to create survey', ['error' => $e->getMessage()]);
            throw $e;
        }
    }

    /**
     * Update survey with validation.
     */
    public function update(int $id, array $data): Survey
    {
        DB::beginTransaction();
        try {
            $survey = $this->repository->findById($id);

            if (!$survey) {
                throw new Exception('Survey not found');
            }

            // Check if survey can be updated
            if ($survey->status === 'closed') {
                throw new Exception('Cannot update closed survey');
            }

            // Validate dates if being updated
            if (isset($data['start_date']) || isset($data['end_date'])) {
                $startDate = $data['start_date'] ?? $survey->start_date;
                $endDate = $data['end_date'] ?? $survey->end_date;
                if ($startDate && $endDate) {
                    $this->validateDates($startDate, $endDate);
                }
            }

            $survey = $this->repository->update($survey, $data);

            DB::commit();
            Log::info('Survey updated', ['id' => $survey->id]);

            return $survey;
        } catch (Exception $e) {
            DB::rollBack();
            Log::error('Failed to update survey', ['id' => $id, 'error' => $e->getMessage()]);
            throw $e;
        }
    }

    /**
     * Delete survey with validation.
     * Cannot delete if has responses.
     */
    public function delete(int $id): bool
    {
        DB::beginTransaction();
        try {
            $survey = $this->repository->findById($id);

            if (!$survey) {
                throw new Exception('Survey not found');
            }

            // Check if survey has responses
            if ($survey->responses()->exists()) {
                throw new Exception('Cannot delete survey that has responses');
            }

            // Delete all questions first
            $survey->questions()->delete();

            $this->repository->delete($survey);

            DB::commit();
            Log::info('Survey deleted', ['id' => $id]);

            return true;
        } catch (Exception $e) {
            DB::rollBack();
            Log::error('Failed to delete survey', ['id' => $id, 'error' => $e->getMessage()]);
            throw $e;
        }
    }

    /**
     * Publish survey (change status to 'published').
     */
    public function publish(int $id): Survey
    {
        DB::beginTransaction();
        try {
            $survey = $this->repository->findById($id);

            if (!$survey) {
                throw new Exception('Survey not found');
            }

            // Only draft surveys can be published
            if ($survey->status !== 'draft') {
                throw new Exception('Only draft surveys can be published');
            }

            // Check if survey has questions
            if (!$survey->questions()->exists()) {
                throw new Exception('Cannot publish survey without questions');
            }

            $data = [
                'status' => 'published',
            ];

            $survey = $this->repository->update($survey, $data);

            DB::commit();
            Log::info('Survey published', ['id' => $id]);

            return $survey;
        } catch (Exception $e) {
            DB::rollBack();
            Log::error('Failed to publish survey', ['id' => $id, 'error' => $e->getMessage()]);
            throw $e;
        }
    }

    /**
     * Close survey (change status to 'closed').
     */
    public function close(int $id): Survey
    {
        DB::beginTransaction();
        try {
            $survey = $this->repository->findById($id);

            if (!$survey) {
                throw new Exception('Survey not found');
            }

            // Only published surveys can be closed
            if ($survey->status !== 'published') {
                throw new Exception('Only published surveys can be closed');
            }

            $data = [
                'status' => 'closed',
            ];

            $survey = $this->repository->update($survey, $data);

            DB::commit();
            Log::info('Survey closed', ['id' => $id]);

            return $survey;
        } catch (Exception $e) {
            DB::rollBack();
            Log::error('Failed to close survey', ['id' => $id, 'error' => $e->getMessage()]);
            throw $e;
        }
    }

    /**
     * Duplicate survey (copy survey with questions).
     */
    public function duplicate(int $id): Survey
    {
        DB::beginTransaction();
        try {
            $survey = $this->repository->findById($id);

            if (!$survey) {
                throw new Exception('Survey not found');
            }

            // Create new survey
            $newSurveyData = $survey->toArray();
            unset($newSurveyData['id'], $newSurveyData['created_at'], $newSurveyData['updated_at'], $newSurveyData['deleted_at']);

            $newSurveyData['title'] = $survey->title . ' (Copy)';
            $newSurveyData['status'] = 'draft';
            $newSurveyData['survey_code'] = $this->repository->generateSurveyCode();
            $newSurveyData['created_by'] = auth()->id();

            $newSurvey = $this->repository->create($newSurveyData);

            // Copy questions
            $questions = $survey->questions;
            foreach ($questions as $question) {
                $questionData = $question->toArray();
                unset($questionData['id'], $questionData['created_at'], $questionData['updated_at']);
                $questionData['survey_id'] = $newSurvey->id;

                $this->questionRepository->create($questionData);
            }

            DB::commit();
            Log::info('Survey duplicated', ['original_id' => $id, 'new_id' => $newSurvey->id]);

            return $newSurvey;
        } catch (Exception $e) {
            DB::rollBack();
            Log::error('Failed to duplicate survey', ['id' => $id, 'error' => $e->getMessage()]);
            throw $e;
        }
    }

    /**
     * Get published surveys.
     */
    public function getPublished(): Collection
    {
        return $this->repository->getPublishedSurveys();
    }

    /**
     * Get active surveys.
     */
    public function getActive(): Collection
    {
        return $this->repository->getActiveSurveys();
    }

    /**
     * Get surveys by creator.
     */
    public function getByCreator(int $userId): Collection
    {
        return $this->repository->getByCreator($userId);
    }

    /**
     * Get statistics.
     */
    public function getStatistics(): array
    {
        return $this->repository->getStatistics();
    }

    /**
     * Validate dates (end date must be after start date).
     */
    private function validateDates(string $startDate, string $endDate): void
    {
        if ($endDate <= $startDate) {
            throw new Exception('End date must be after start date');
        }
    }
}
