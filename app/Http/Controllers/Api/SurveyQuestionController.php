<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\Survey\StoreSurveyQuestionRequest;
use App\Http\Resources\Survey\SurveyQuestionResource;
use App\Services\SurveyQuestionService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Exception;

class SurveyQuestionController extends Controller
{
    public function __construct(
        private SurveyQuestionService $service
    ) {}

    /**
     * Display a listing of questions for a survey.
     *
     * @param int $surveyId
     * @return JsonResponse
     */
    public function index(int $surveyId): JsonResponse
    {
        try {
            $questions = $this->service->getBySurvey($surveyId);

            return response()->json([
                'success' => true,
                'data' => SurveyQuestionResource::collection($questions),
            ]);
        } catch (Exception $e) {
            return response()->json([
                'success' => false,
                'message' => $e->getMessage(),
            ], 400);
        }
    }

    /**
     * Store a newly created question.
     *
     * @param StoreSurveyQuestionRequest $request
     * @return JsonResponse
     */
    public function store(StoreSurveyQuestionRequest $request): JsonResponse
    {
        try {
            $question = $this->service->create($request->validated());

            return response()->json([
                'success' => true,
                'message' => 'Pertanyaan berhasil dibuat',
                'data' => new SurveyQuestionResource($question),
            ], 201);
        } catch (Exception $e) {
            return response()->json([
                'success' => false,
                'message' => $e->getMessage(),
            ], 400);
        }
    }

    /**
     * Display the specified question.
     *
     * @param int $id
     * @return JsonResponse
     */
    public function show(int $id): JsonResponse
    {
        try {
            $question = $this->service->getById($id);

            if (!$question) {
                return response()->json([
                    'success' => false,
                    'message' => 'Pertanyaan tidak ditemukan',
                ], 404);
            }

            return response()->json([
                'success' => true,
                'data' => new SurveyQuestionResource($question),
            ]);
        } catch (Exception $e) {
            return response()->json([
                'success' => false,
                'message' => $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Update the specified question.
     *
     * @param Request $request
     * @param int $id
     * @return JsonResponse
     */
    public function update(Request $request, int $id): JsonResponse
    {
        try {
            // Validate request
            $validated = $request->validate([
                'question_text' => ['sometimes', 'required', 'string'],
                'question_type' => ['sometimes', 'in:text,textarea,radio,checkbox,dropdown,rating,matrix'],
                'options' => ['nullable', 'array'],
                'options.*' => ['string'],
                'is_required' => ['nullable', 'boolean'],
                'order' => ['nullable', 'integer', 'min:1'],
                'validation_rules' => ['nullable', 'array'],
                'conditional_logic' => ['nullable', 'array'],
                'help_text' => ['nullable', 'string'],
            ]);

            $question = $this->service->update($id, $validated);

            return response()->json([
                'success' => true,
                'message' => 'Pertanyaan berhasil diperbarui',
                'data' => new SurveyQuestionResource($question),
            ]);
        } catch (Exception $e) {
            return response()->json([
                'success' => false,
                'message' => $e->getMessage(),
            ], 400);
        }
    }

    /**
     * Remove the specified question.
     *
     * @param int $id
     * @return JsonResponse
     */
    public function destroy(int $id): JsonResponse
    {
        try {
            $this->service->delete($id);

            return response()->json([
                'success' => true,
                'message' => 'Pertanyaan berhasil dihapus',
            ]);
        } catch (Exception $e) {
            return response()->json([
                'success' => false,
                'message' => $e->getMessage(),
            ], 400);
        }
    }

    /**
     * Reorder questions within a survey.
     *
     * @param Request $request
     * @param int $surveyId
     * @return JsonResponse
     */
    public function reorder(Request $request, int $surveyId): JsonResponse
    {
        try {
            // Validate request
            $validated = $request->validate([
                'order' => ['required', 'array', 'min:1'],
                'order.*.id' => ['required', 'exists:survey_questions,id'],
                'order.*.order' => ['required', 'integer', 'min:1'],
            ], [
                'order.required' => 'Data urutan harus diisi',
                'order.array' => 'Data urutan harus berupa array',
                'order.min' => 'Minimal harus ada 1 data urutan',
                'order.*.id.required' => 'ID pertanyaan harus diisi',
                'order.*.id.exists' => 'Pertanyaan tidak ditemukan',
                'order.*.order.required' => 'Urutan harus diisi',
                'order.*.order.integer' => 'Urutan harus berupa angka',
                'order.*.order.min' => 'Urutan minimal 1',
            ]);

            $this->service->reorder($surveyId, $validated['order']);

            return response()->json([
                'success' => true,
                'message' => 'Urutan pertanyaan berhasil diperbarui',
            ]);
        } catch (Exception $e) {
            return response()->json([
                'success' => false,
                'message' => $e->getMessage(),
            ], 400);
        }
    }

    /**
     * Duplicate a question.
     *
     * @param int $id
     * @return JsonResponse
     */
    public function duplicate(int $id): JsonResponse
    {
        try {
            $question = $this->service->duplicate($id);

            return response()->json([
                'success' => true,
                'message' => 'Pertanyaan berhasil diduplikasi',
                'data' => new SurveyQuestionResource($question),
            ]);
        } catch (Exception $e) {
            return response()->json([
                'success' => false,
                'message' => $e->getMessage(),
            ], 400);
        }
    }
}
