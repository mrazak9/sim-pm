<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\IKU\StoreIKUTargetRequest;
use App\Http\Requests\IKU\UpdateIKUTargetRequest;
use App\Http\Resources\IKUTargetResource;
use App\Services\IKUTargetService;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;

class IKUTargetController extends Controller
{
    protected IKUTargetService $targetService;

    public function __construct(IKUTargetService $targetService)
    {
        $this->targetService = $targetService;
    }

    /**
     * Display a listing of the resource.
     */
    public function index(Request $request): JsonResponse
    {
        try {
            $filters = $request->only([
                'iku_id',
                'tahun_akademik_id',
                'unit_kerja_id',
                'program_studi_id',
                'periode',
                'search'
            ]);
            $perPage = $request->get('per_page', 15);

            $targets = $this->targetService->getAllTargets($filters, $perPage);

            return response()->json([
                'success' => true,
                'data' => IKUTargetResource::collection($targets)->response()->getData(true)['data'],
                'meta' => [
                    'current_page' => $targets->currentPage(),
                    'from' => $targets->firstItem(),
                    'last_page' => $targets->lastPage(),
                    'per_page' => $targets->perPage(),
                    'to' => $targets->lastItem(),
                    'total' => $targets->total(),
                ],
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to fetch IKU Targets',
                'error' => $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(StoreIKUTargetRequest $request): JsonResponse
    {
        try {
            $target = $this->targetService->createTarget($request->validated());

            return response()->json([
                'success' => true,
                'message' => 'IKU Target created successfully',
                'data' => new IKUTargetResource($target),
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
            $target = $this->targetService->getTargetById($id);

            if (!$target) {
                return response()->json([
                    'success' => false,
                    'message' => 'IKU Target not found',
                ], 404);
            }

            return response()->json([
                'success' => true,
                'data' => new IKUTargetResource($target),
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to fetch IKU Target',
                'error' => $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(UpdateIKUTargetRequest $request, string $id): JsonResponse
    {
        try {
            $target = $this->targetService->updateTarget($id, $request->validated());

            return response()->json([
                'success' => true,
                'message' => 'IKU Target updated successfully',
                'data' => new IKUTargetResource($target),
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
            $this->targetService->deleteTarget($id);

            return response()->json([
                'success' => true,
                'message' => 'IKU Target deleted successfully',
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => $e->getMessage(),
            ], 422);
        }
    }

    /**
     * Get dashboard statistics for a specific target
     */
    public function statistics(string $id): JsonResponse
    {
        try {
            $statistics = $this->targetService->getTargetStatistics($id);

            return response()->json([
                'success' => true,
                'data' => $statistics,
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => $e->getMessage(),
            ], 404);
        }
    }

    /**
     * Get all targets dashboard statistics
     */
    public function dashboardStatistics(): JsonResponse
    {
        try {
            $statistics = $this->targetService->getDashboardStatistics();

            return response()->json([
                'success' => true,
                'data' => $statistics,
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to fetch dashboard statistics',
                'error' => $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Get targets that need attention
     */
    public function needAttention(): JsonResponse
    {
        try {
            $targets = $this->targetService->getTargetsNeedAttention();

            return response()->json([
                'success' => true,
                'data' => IKUTargetResource::collection($targets),
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to fetch targets',
                'error' => $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Get targets by status
     */
    public function byStatus(Request $request): JsonResponse
    {
        try {
            $status = $request->get('status');

            if (!$status) {
                return response()->json([
                    'success' => false,
                    'message' => 'Status parameter is required',
                ], 400);
            }

            $targets = $this->targetService->getTargetsByStatus($status);

            return response()->json([
                'success' => true,
                'data' => IKUTargetResource::collection($targets),
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => $e->getMessage(),
            ], 422);
        }
    }

    /**
     * Check if target is at risk
     */
    public function checkRisk(string $id): JsonResponse
    {
        try {
            $riskData = $this->targetService->isTargetAtRisk($id);

            return response()->json([
                'success' => true,
                'data' => $riskData,
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => $e->getMessage(),
            ], 404);
        }
    }
}
