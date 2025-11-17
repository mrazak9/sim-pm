<?php

namespace App\Services;

use App\Models\SurveyResponse;
use App\Models\SurveyAnswer;
use App\Repositories\SurveyResponseRepository;
use App\Repositories\SurveyRepository;
use App\Repositories\SurveyQuestionRepository;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Exception;

class SurveyResponseService
{
    public function __construct(
        private SurveyResponseRepository $repository,
        private SurveyRepository $surveyRepository,
        private SurveyQuestionRepository $questionRepository
    ) {}

    /**
     * Get responses by survey ID.
     */
    public function getBySurvey(int $surveyId, array $filters = [], int $perPage = 15)
    {
        return $this->repository->findBySurvey($surveyId, $filters, $perPage);
    }

    /**
     * Get response by ID.
     */
    public function getById(int $id): ?SurveyResponse
    {
        return $this->repository->findById($id);
    }

    /**
     * Start new response.
     */
    public function startResponse(int $surveyId, ?int $userId = null): SurveyResponse
    {
        DB::beginTransaction();
        try {
            // Check if survey exists
            $survey = $this->surveyRepository->findById($surveyId);
            if (!$survey) {
                throw new Exception('Survey not found');
            }

            // Check if survey can be responded to
            if (!$survey->canBeResponded()) {
                throw new Exception('Survey is not available for responses');
            }

            // Check if user already responded (if not allowing multiple responses)
            if ($userId && !$survey->allow_multiple_responses) {
                if ($this->repository->hasUserResponded($surveyId, $userId)) {
                    throw new Exception('You have already responded to this survey');
                }
            }

            // Generate response code
            $responseCode = $this->repository->generateResponseCode();

            $data = [
                'survey_id' => $surveyId,
                'user_id' => $userId,
                'response_code' => $responseCode,
                'started_at' => now(),
                'is_completed' => false,
                'ip_address' => request()->ip(),
                'user_agent' => request()->userAgent(),
            ];

            $response = $this->repository->create($data);

            DB::commit();
            Log::info('Survey response started', ['id' => $response->id, 'survey_id' => $surveyId]);

            return $response;
        } catch (Exception $e) {
            DB::rollBack();
            Log::error('Failed to start survey response', ['survey_id' => $surveyId, 'error' => $e->getMessage()]);
            throw $e;
        }
    }

    /**
     * Submit response with answers.
     */
    public function submitResponse(int $responseId, array $answers): SurveyResponse
    {
        DB::beginTransaction();
        try {
            $response = $this->repository->findById($responseId);

            if (!$response) {
                throw new Exception('Response not found');
            }

            // Check if response is already completed
            if ($response->is_completed) {
                throw new Exception('Response has already been completed');
            }

            // Validate required questions
            $this->validateRequiredQuestions($response->survey_id, $answers);

            // Save answers
            foreach ($answers as $answerData) {
                SurveyAnswer::create([
                    'survey_response_id' => $responseId,
                    'survey_question_id' => $answerData['question_id'],
                    'answer_text' => $answerData['answer'] ?? null,
                    'answer_option' => $answerData['option'] ?? null,
                ]);
            }

            // Update response as completed
            $response = $this->repository->update($response, [
                'is_completed' => true,
                'completed_at' => now(),
            ]);

            DB::commit();
            Log::info('Survey response submitted', ['id' => $responseId]);

            return $response;
        } catch (Exception $e) {
            DB::rollBack();
            Log::error('Failed to submit survey response', ['id' => $responseId, 'error' => $e->getMessage()]);
            throw $e;
        }
    }

    /**
     * Get analytics for a survey.
     */
    public function getAnalytics(int $surveyId): array
    {
        DB::beginTransaction();
        try {
            // Get basic statistics
            $statistics = $this->repository->getStatistics($surveyId);

            // Get question-level analytics
            $questions = $this->questionRepository->findBySurvey($surveyId);
            $questionAnalytics = [];

            foreach ($questions as $question) {
                $answers = SurveyAnswer::where('survey_question_id', $question->id)
                    ->get();

                $analytics = [
                    'question_id' => $question->id,
                    'question_text' => $question->question_text,
                    'question_type' => $question->question_type,
                    'total_answers' => $answers->count(),
                ];

                // For radio, checkbox, dropdown - get option distribution
                if (in_array($question->question_type, ['radio', 'checkbox', 'dropdown'])) {
                    $optionDistribution = $answers
                        ->groupBy('answer_option')
                        ->map(fn($group) => $group->count())
                        ->toArray();

                    $analytics['option_distribution'] = $optionDistribution;
                }

                // For rating - get average and distribution
                if ($question->question_type === 'rating') {
                    $ratings = $answers->pluck('answer_text')->filter()->map(fn($r) => (int)$r);
                    $analytics['average_rating'] = $ratings->count() > 0 ? round($ratings->average(), 2) : 0;
                    $analytics['rating_distribution'] = $ratings->countBy()->toArray();
                }

                // For text/textarea - just count
                if (in_array($question->question_type, ['text', 'textarea'])) {
                    $analytics['text_responses'] = $answers->pluck('answer_text')->filter()->values()->toArray();
                }

                $questionAnalytics[] = $analytics;
            }

            DB::commit();

            return [
                'statistics' => $statistics,
                'question_analytics' => $questionAnalytics,
            ];
        } catch (Exception $e) {
            DB::rollBack();
            Log::error('Failed to get survey analytics', ['survey_id' => $surveyId, 'error' => $e->getMessage()]);
            throw $e;
        }
    }

    /**
     * Validate that all required questions are answered.
     */
    private function validateRequiredQuestions(int $surveyId, array $answers): void
    {
        $requiredQuestions = $this->questionRepository->getRequiredBySurvey($surveyId);
        $answeredQuestionIds = collect($answers)->pluck('question_id')->toArray();

        foreach ($requiredQuestions as $question) {
            if (!in_array($question->id, $answeredQuestionIds)) {
                throw new Exception("Question '{$question->question_text}' is required");
            }
        }
    }

    /**
     * Get responses by user.
     */
    public function getByUser(int $userId): Collection
    {
        return $this->repository->findByUser($userId);
    }
}
