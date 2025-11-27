<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\Survey\StoreSurveyRequest;
use App\Http\Requests\Survey\UpdateSurveyRequest;
use App\Http\Resources\Survey\SurveyResource;
use App\Services\SurveyService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Exception;

class SurveyController extends Controller
{
    public function __construct(
        private SurveyService $service
    ) {}

    /**
     * Display a listing of surveys.
     *
     * @param Request $request
     * @return JsonResponse
     */
    public function index(Request $request): JsonResponse
    {
        $filters = $request->only([
            'search',
            'type',
            'status',
            'unit_kerja_id',
            'created_by',
            'order_by',
            'order_dir'
        ]);

        $perPage = $request->input('per_page', 15);
        $surveys = $this->service->getAllPaginated($filters, $perPage);

        return response()->json([
            'success' => true,
            'data' => SurveyResource::collection($surveys)->resolve(),
            'meta' => [
                'current_page' => $surveys->currentPage(),
                'from' => $surveys->firstItem(),
                'last_page' => $surveys->lastPage(),
                'per_page' => $surveys->perPage(),
                'to' => $surveys->lastItem(),
                'total' => $surveys->total(),
            ],
        ]);
    }

    /**
     * Store a newly created survey.
     *
     * @param StoreSurveyRequest $request
     * @return JsonResponse
     */
    public function store(StoreSurveyRequest $request): JsonResponse
    {
        try {
            $survey = $this->service->create($request->validated());

            return response()->json([
                'success' => true,
                'message' => 'Kuesioner berhasil dibuat',
                'data' => new SurveyResource($survey),
            ], 201);
        } catch (Exception $e) {
            return response()->json([
                'success' => false,
                'message' => $e->getMessage(),
            ], 400);
        }
    }

    /**
     * Display the specified survey.
     *
     * @param int $id
     * @return JsonResponse
     */
    public function show(int $id): JsonResponse
    {
        try {
            $survey = $this->service->getById($id);

            if (!$survey) {
                return response()->json([
                    'success' => false,
                    'message' => 'Kuesioner tidak ditemukan',
                ], 404);
            }

            return response()->json([
                'success' => true,
                'data' => new SurveyResource($survey),
            ]);
        } catch (Exception $e) {
            return response()->json([
                'success' => false,
                'message' => $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Update the specified survey.
     *
     * @param UpdateSurveyRequest $request
     * @param int $id
     * @return JsonResponse
     */
    public function update(UpdateSurveyRequest $request, int $id): JsonResponse
    {
        try {
            $survey = $this->service->update($id, $request->validated());

            return response()->json([
                'success' => true,
                'message' => 'Kuesioner berhasil diperbarui',
                'data' => new SurveyResource($survey),
            ]);
        } catch (Exception $e) {
            return response()->json([
                'success' => false,
                'message' => $e->getMessage(),
            ], 400);
        }
    }

    /**
     * Remove the specified survey.
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
                'message' => 'Kuesioner berhasil dihapus',
            ]);
        } catch (Exception $e) {
            return response()->json([
                'success' => false,
                'message' => $e->getMessage(),
            ], 400);
        }
    }

    /**
     * Publish survey.
     *
     * @param int $id
     * @return JsonResponse
     */
    public function publish(int $id): JsonResponse
    {
        try {
            $survey = $this->service->publish($id);

            return response()->json([
                'success' => true,
                'message' => 'Kuesioner berhasil dipublikasikan',
                'data' => new SurveyResource($survey),
            ]);
        } catch (Exception $e) {
            return response()->json([
                'success' => false,
                'message' => $e->getMessage(),
            ], 400);
        }
    }

    /**
     * Close survey.
     *
     * @param int $id
     * @return JsonResponse
     */
    public function close(int $id): JsonResponse
    {
        try {
            $survey = $this->service->close($id);

            return response()->json([
                'success' => true,
                'message' => 'Kuesioner berhasil ditutup',
                'data' => new SurveyResource($survey),
            ]);
        } catch (Exception $e) {
            return response()->json([
                'success' => false,
                'message' => $e->getMessage(),
            ], 400);
        }
    }

    /**
     * Duplicate survey.
     *
     * @param int $id
     * @return JsonResponse
     */
    public function duplicate(int $id): JsonResponse
    {
        try {
            $survey = $this->service->duplicate($id);

            return response()->json([
                'success' => true,
                'message' => 'Kuesioner berhasil diduplikasi',
                'data' => new SurveyResource($survey),
            ]);
        } catch (Exception $e) {
            return response()->json([
                'success' => false,
                'message' => $e->getMessage(),
            ], 400);
        }
    }

    /**
     * Get surveys statistics.
     *
     * @return JsonResponse
     */
    public function statistics(): JsonResponse
    {
        try {
            $statistics = $this->service->getStatistics();

            return response()->json([
                'success' => true,
                'data' => $statistics,
            ]);
        } catch (Exception $e) {
            return response()->json([
                'success' => false,
                'message' => $e->getMessage(),
            ], 400);
        }
    }

    /**
     * Get published surveys.
     *
     * @return JsonResponse
     */
    public function published(): JsonResponse
    {
        try {
            $surveys = $this->service->getPublished();

            return response()->json([
                'success' => true,
                'data' => SurveyResource::collection($surveys),
            ]);
        } catch (Exception $e) {
            return response()->json([
                'success' => false,
                'message' => $e->getMessage(),
            ], 400);
        }
    }

    /**
     * Get active surveys.
     *
     * @return JsonResponse
     */
    public function active(): JsonResponse
    {
        try {
            $surveys = $this->service->getActive();

            return response()->json([
                'success' => true,
                'data' => SurveyResource::collection($surveys),
            ]);
        } catch (Exception $e) {
            return response()->json([
                'success' => false,
                'message' => $e->getMessage(),
            ], 400);
        }
    }

    /**
     * Get surveys by creator.
     *
     * @param int $userId
     * @return JsonResponse
     */
    public function byCreator(int $userId): JsonResponse
    {
        try {
            $surveys = $this->service->getByCreator($userId);

            return response()->json([
                'success' => true,
                'data' => SurveyResource::collection($surveys),
            ]);
        } catch (Exception $e) {
            return response()->json([
                'success' => false,
                'message' => $e->getMessage(),
            ], 400);
        }
    }
}
