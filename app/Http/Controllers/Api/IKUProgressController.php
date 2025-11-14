<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\IKU\StoreIKUProgressRequest;
use App\Http\Requests\IKU\UpdateIKUProgressRequest;
use App\Http\Resources\IKUProgressResource;
use App\Services\IKUProgressService;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Storage;

class IKUProgressController extends Controller
{
    protected IKUProgressService $progressService;

    public function __construct(IKUProgressService $progressService)
    {
        $this->progressService = $progressService;
    }

    /**
     * Display a listing of the resource.
     */
    public function index(Request $request): JsonResponse
    {
        try {
            $filters = $request->only([
                'iku_target_id',
                'start_date',
                'end_date',
                'recent_days',
                'search'
            ]);
            $perPage = $request->get('per_page', 15);

            $progress = $this->progressService->getAllProgress($filters, $perPage);

            return response()->json([
                'success' => true,
                'data' => IKUProgressResource::collection($progress)->response()->getData(true)['data'],
                'meta' => [
                    'current_page' => $progress->currentPage(),
                    'from' => $progress->firstItem(),
                    'last_page' => $progress->lastPage(),
                    'per_page' => $progress->perPage(),
                    'to' => $progress->lastItem(),
                    'total' => $progress->total(),
                ],
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to fetch IKU Progress',
                'error' => $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(StoreIKUProgressRequest $request): JsonResponse
    {
        try {
            $file = $request->hasFile('bukti_dokumen') ? $request->file('bukti_dokumen') : null;
            $progress = $this->progressService->createProgress($request->validated(), $file);

            return response()->json([
                'success' => true,
                'message' => 'IKU Progress created successfully',
                'data' => new IKUProgressResource($progress),
            ], 201);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => $e->getMessage(),
            ], 422);
        }
    }

    /**
     * Display the specified resource.
     */
    public function show(string $id): JsonResponse
    {
        try {
            $progress = $this->progressService->getProgressById($id);

            if (!$progress) {
                return response()->json([
                    'success' => false,
                    'message' => 'IKU Progress not found',
                ], 404);
            }

            return response()->json([
                'success' => true,
                'data' => new IKUProgressResource($progress),
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to fetch IKU Progress',
                'error' => $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(UpdateIKUProgressRequest $request, string $id): JsonResponse
    {
        try {
            $file = $request->hasFile('bukti_dokumen') ? $request->file('bukti_dokumen') : null;
            $progress = $this->progressService->updateProgress($id, $request->validated(), $file);

            return response()->json([
                'success' => true,
                'message' => 'IKU Progress updated successfully',
                'data' => new IKUProgressResource($progress),
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => $e->getMessage(),
            ], 422);
        }
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(string $id): JsonResponse
    {
        try {
            $this->progressService->deleteProgress($id);

            return response()->json([
                'success' => true,
                'message' => 'IKU Progress deleted successfully',
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => $e->getMessage(),
            ], 422);
        }
    }

    /**
     * Download progress document
     */
    public function downloadDocument(string $id)
    {
        try {
            $document = $this->progressService->downloadDocument($id);

            return Storage::disk('public')->download($document['path'], $document['name']);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => $e->getMessage(),
            ], 404);
        }
    }

    /**
     * Get progress summary by target
     */
    public function summaryByTarget(string $targetId): JsonResponse
    {
        try {
            $summary = $this->progressService->getProgressSummaryByTarget($targetId);

            return response()->json([
                'success' => true,
                'data' => $summary,
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to fetch progress summary',
                'error' => $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Get recent progress entries
     */
    public function recent(Request $request): JsonResponse
    {
        try {
            $days = $request->get('days', 30);
            $limit = $request->get('limit', 10);

            $progress = $this->progressService->getRecentProgress($days, $limit);

            return response()->json([
                'success' => true,
                'data' => IKUProgressResource::collection($progress),
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to fetch recent progress',
                'error' => $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Get progress statistics for dashboard
     */
    public function statistics(): JsonResponse
    {
        try {
            $statistics = $this->progressService->getStatistics();

            return response()->json([
                'success' => true,
                'data' => $statistics,
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to fetch statistics',
                'error' => $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Get progress trend for charts
     */
    public function trend(string $targetId): JsonResponse
    {
        try {
            $trend = $this->progressService->getProgressTrend($targetId);

            return response()->json([
                'success' => true,
                'data' => $trend,
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to fetch progress trend',
                'error' => $e->getMessage(),
            ], 500);
        }
    }
}
