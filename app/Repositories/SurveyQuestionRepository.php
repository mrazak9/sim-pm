<?php

namespace App\Repositories;

use App\Models\SurveyQuestion;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Support\Facades\DB;

class SurveyQuestionRepository
{
    /**
     * Find questions by survey ID.
     */
    public function findBySurvey(int $surveyId): Collection
    {
        return SurveyQuestion::where('survey_id', $surveyId)
            ->orderBy('order', 'asc')
            ->get();
    }

    /**
     * Find question by ID.
     */
    public function findById(int $id): ?SurveyQuestion
    {
        return SurveyQuestion::with('survey')->find($id);
    }

    /**
     * Create new question.
     */
    public function create(array $data): SurveyQuestion
    {
        return SurveyQuestion::create($data);
    }

    /**
     * Update question.
     */
    public function update(SurveyQuestion $question, array $data): SurveyQuestion
    {
        $question->update($data);
        return $question->fresh();
    }

    /**
     * Delete question.
     */
    public function delete(SurveyQuestion $question): bool
    {
        return $question->delete();
    }

    /**
     * Reorder questions within a survey.
     */
    public function reorder(int $surveyId, array $orderData): bool
    {
        DB::beginTransaction();
        try {
            foreach ($orderData as $item) {
                SurveyQuestion::where('id', $item['id'])
                    ->where('survey_id', $surveyId)
                    ->update(['order' => $item['order']]);
            }

            DB::commit();
            return true;
        } catch (\Exception $e) {
            DB::rollBack();
            throw $e;
        }
    }

    /**
     * Duplicate a question.
     */
    public function duplicate(int $questionId): SurveyQuestion
    {
        $question = $this->findById($questionId);

        if (!$question) {
            throw new \Exception('Question not found');
        }

        // Get the next order number
        $maxOrder = SurveyQuestion::where('survey_id', $question->survey_id)
            ->max('order');

        $newQuestion = $question->replicate();
        $newQuestion->order = $maxOrder + 1;
        $newQuestion->question_text = $question->question_text . ' (Copy)';
        $newQuestion->save();

        return $newQuestion;
    }

    /**
     * Get next order number for a survey.
     */
    public function getNextOrder(int $surveyId): int
    {
        $maxOrder = SurveyQuestion::where('survey_id', $surveyId)
            ->max('order');

        return $maxOrder ? $maxOrder + 1 : 1;
    }

    /**
     * Get questions count by survey.
     */
    public function getCountBySurvey(int $surveyId): int
    {
        return SurveyQuestion::where('survey_id', $surveyId)->count();
    }

    /**
     * Get required questions by survey.
     */
    public function getRequiredBySurvey(int $surveyId): Collection
    {
        return SurveyQuestion::where('survey_id', $surveyId)
            ->where('is_required', true)
            ->orderBy('order', 'asc')
            ->get();
    }
}
