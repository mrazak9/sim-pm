<?php

namespace App\Services;

use App\Models\SurveyQuestion;
use App\Repositories\SurveyQuestionRepository;
use App\Repositories\SurveyRepository;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Exception;

class SurveyQuestionService
{
    public function __construct(
        private SurveyQuestionRepository $repository,
        private SurveyRepository $surveyRepository
    ) {}

    /**
     * Get questions by survey ID.
     */
    public function getBySurvey(int $surveyId): Collection
    {
        return $this->repository->findBySurvey($surveyId);
    }

    /**
     * Get question by ID.
     */
    public function getById(int $id): ?SurveyQuestion
    {
        return $this->repository->findById($id);
    }

    /**
     * Create new question with order calculation.
     */
    public function create(array $data): SurveyQuestion
    {
        DB::beginTransaction();
        try {
            // Check if survey exists
            $survey = $this->surveyRepository->findById($data['survey_id']);
            if (!$survey) {
                throw new Exception('Survey not found');
            }

            // Check if survey can be edited
            if (!$survey->canBeEdited()) {
                throw new Exception('Cannot add questions to published or closed survey');
            }

            // Calculate next order if not provided
            if (!isset($data['order'])) {
                $data['order'] = $this->repository->getNextOrder($data['survey_id']);
            }

            $question = $this->repository->create($data);

            DB::commit();
            Log::info('Survey question created', ['id' => $question->id, 'survey_id' => $data['survey_id']]);

            return $question;
        } catch (Exception $e) {
            DB::rollBack();
            Log::error('Failed to create survey question', ['error' => $e->getMessage()]);
            throw $e;
        }
    }

    /**
     * Update question.
     */
    public function update(int $id, array $data): SurveyQuestion
    {
        DB::beginTransaction();
        try {
            $question = $this->repository->findById($id);

            if (!$question) {
                throw new Exception('Question not found');
            }

            // Check if survey can be edited
            if (!$question->survey->canBeEdited()) {
                throw new Exception('Cannot edit questions of published or closed survey');
            }

            $question = $this->repository->update($question, $data);

            DB::commit();
            Log::info('Survey question updated', ['id' => $id]);

            return $question;
        } catch (Exception $e) {
            DB::rollBack();
            Log::error('Failed to update survey question', ['id' => $id, 'error' => $e->getMessage()]);
            throw $e;
        }
    }

    /**
     * Delete question.
     */
    public function delete(int $id): bool
    {
        DB::beginTransaction();
        try {
            $question = $this->repository->findById($id);

            if (!$question) {
                throw new Exception('Question not found');
            }

            // Check if survey can be edited
            if (!$question->survey->canBeEdited()) {
                throw new Exception('Cannot delete questions from published or closed survey');
            }

            // Check if question has answers
            if ($question->answers()->exists()) {
                throw new Exception('Cannot delete question that has answers');
            }

            $this->repository->delete($question);

            DB::commit();
            Log::info('Survey question deleted', ['id' => $id]);

            return true;
        } catch (Exception $e) {
            DB::rollBack();
            Log::error('Failed to delete survey question', ['id' => $id, 'error' => $e->getMessage()]);
            throw $e;
        }
    }

    /**
     * Reorder questions within a survey.
     */
    public function reorder(int $surveyId, array $orderData): bool
    {
        DB::beginTransaction();
        try {
            // Check if survey exists
            $survey = $this->surveyRepository->findById($surveyId);
            if (!$survey) {
                throw new Exception('Survey not found');
            }

            // Check if survey can be edited
            if (!$survey->canBeEdited()) {
                throw new Exception('Cannot reorder questions of published or closed survey');
            }

            $this->repository->reorder($surveyId, $orderData);

            DB::commit();
            Log::info('Survey questions reordered', ['survey_id' => $surveyId]);

            return true;
        } catch (Exception $e) {
            DB::rollBack();
            Log::error('Failed to reorder survey questions', ['survey_id' => $surveyId, 'error' => $e->getMessage()]);
            throw $e;
        }
    }

    /**
     * Duplicate a question.
     */
    public function duplicate(int $id): SurveyQuestion
    {
        DB::beginTransaction();
        try {
            $question = $this->repository->findById($id);

            if (!$question) {
                throw new Exception('Question not found');
            }

            // Check if survey can be edited
            if (!$question->survey->canBeEdited()) {
                throw new Exception('Cannot duplicate questions of published or closed survey');
            }

            $newQuestion = $this->repository->duplicate($id);

            DB::commit();
            Log::info('Survey question duplicated', ['original_id' => $id, 'new_id' => $newQuestion->id]);

            return $newQuestion;
        } catch (Exception $e) {
            DB::rollBack();
            Log::error('Failed to duplicate survey question', ['id' => $id, 'error' => $e->getMessage()]);
            throw $e;
        }
    }
}
