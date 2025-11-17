<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\Survey\SubmitSurveyResponseRequest;
use App\Http\Resources\Survey\SurveyResponseResource;
use App\Services\SurveyResponseService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;
use Exception;

class SurveyResponseController extends Controller
{
    public function __construct(
        private SurveyResponseService $service
    ) {}

    /**
     * Display a listing of responses for a survey.
     *
     * @param Request $request
     * @param int $surveyId
     * @return AnonymousResourceCollection
     */
    public function index(Request $request, int $surveyId): AnonymousResourceCollection
    {
        $filters = $request->only([
            'is_completed',
            'user_id',
            'date_from',
            'date_to',
            'order_by',
            'order_dir'
        ]);

        $perPage = $request->input('per_page', 15);
        $responses = $this->service->getBySurvey($surveyId, $filters, $perPage);

        return SurveyResponseResource::collection($responses);
    }

    /**
     * Start a new response for a survey.
     *
     * @param Request $request
     * @param int $surveyId
     * @return JsonResponse
     */
    public function store(Request $request, int $surveyId): JsonResponse
    {
        try {
            $userId = auth()->id();

            // For anonymous surveys, don't require authentication
            $response = $this->service->startResponse($surveyId, $userId);

            return response()->json([
                'success' => true,
                'message' => 'Respons kuesioner berhasil dimulai',
                'data' => new SurveyResponseResource($response),
            ], 201);
        } catch (Exception $e) {
            return response()->json([
                'success' => false,
                'message' => $e->getMessage(),
            ], 400);
        }
    }

    /**
     * Display the specified response.
     *
     * @param int $id
     * @return JsonResponse
     */
    public function show(int $id): JsonResponse
    {
        try {
            $response = $this->service->getById($id);

            if (!$response) {
                return response()->json([
                    'success' => false,
                    'message' => 'Respons tidak ditemukan',
                ], 404);
            }

            return response()->json([
                'success' => true,
                'data' => new SurveyResponseResource($response),
            ]);
        } catch (Exception $e) {
            return response()->json([
                'success' => false,
                'message' => $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Submit response with answers.
     *
     * @param SubmitSurveyResponseRequest $request
     * @param int $id
     * @return JsonResponse
     */
    public function submit(SubmitSurveyResponseRequest $request, int $id): JsonResponse
    {
        try {
            $response = $this->service->submitResponse($id, $request->validated()['answers']);

            return response()->json([
                'success' => true,
                'message' => 'Respons kuesioner berhasil dikirim',
                'data' => new SurveyResponseResource($response),
            ]);
        } catch (Exception $e) {
            return response()->json([
                'success' => false,
                'message' => $e->getMessage(),
            ], 400);
        }
    }

    /**
     * Get analytics for a survey.
     *
     * @param int $surveyId
     * @return JsonResponse
     */
    public function analytics(int $surveyId): JsonResponse
    {
        try {
            $analytics = $this->service->getAnalytics($surveyId);

            return response()->json([
                'success' => true,
                'data' => $analytics,
            ]);
        } catch (Exception $e) {
            return response()->json([
                'success' => false,
                'message' => $e->getMessage(),
            ], 400);
        }
    }

    /**
     * Get responses by user.
     *
     * @param int $userId
     * @return JsonResponse
     */
    public function byUser(int $userId): JsonResponse
    {
        try {
            $responses = $this->service->getByUser($userId);

            return response()->json([
                'success' => true,
                'data' => SurveyResponseResource::collection($responses),
            ]);
        } catch (Exception $e) {
            return response()->json([
                'success' => false,
                'message' => $e->getMessage(),
            ], 400);
        }
    }

    /**
     * Get current user's responses.
     *
     * @return JsonResponse
     */
    public function myResponses(): JsonResponse
    {
        try {
            $userId = auth()->id();

            if (!$userId) {
                return response()->json([
                    'success' => false,
                    'message' => 'Unauthorized',
                ], 401);
            }

            $responses = $this->service->getByUser($userId);

            return response()->json([
                'success' => true,
                'data' => SurveyResponseResource::collection($responses),
            ]);
        } catch (Exception $e) {
            return response()->json([
                'success' => false,
                'message' => $e->getMessage(),
            ], 400);
        }
    }
}
