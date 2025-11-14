<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\IKU\StoreIKURequest;
use App\Http\Requests\IKU\UpdateIKURequest;
use App\Http\Resources\IKUResource;
use App\Services\IKUService;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;

class IKUController extends Controller
{
    protected IKUService $ikuService;

    public function __construct(IKUService $ikuService)
    {
        $this->ikuService = $ikuService;
    }

    /**
     * Display a listing of the resource.
     */
    public function index(Request $request): JsonResponse
    {
        try {
            $filters = $request->only(['is_active', 'kategori', 'target_type', 'search']);
            $perPage = $request->get('per_page', 15);

            $ikus = $this->ikuService->getAllIKUs($filters, $perPage);

            return response()->json([
                'success' => true,
                'data' => IKUResource::collection($ikus)->response()->getData(true)['data'],
                'meta' => [
                    'current_page' => $ikus->currentPage(),
                    'from' => $ikus->firstItem(),
                    'last_page' => $ikus->lastPage(),
                    'per_page' => $ikus->perPage(),
                    'to' => $ikus->lastItem(),
                    'total' => $ikus->total(),
                ],
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to fetch IKUs',
                'error' => $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(StoreIKURequest $request): JsonResponse
    {
        try {
            $iku = $this->ikuService->createIKU($request->validated());

            return response()->json([
                'success' => true,
                'message' => 'IKU created successfully',
                'data' => new IKUResource($iku->load(['targets'])),
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
            $iku = $this->ikuService->getIKUById($id);

            if (!$iku) {
                return response()->json([
                    'success' => false,
                    'message' => 'IKU not found',
                ], 404);
            }

            return response()->json([
                'success' => true,
                'data' => new IKUResource($iku),
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to fetch IKU',
                'error' => $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(UpdateIKURequest $request, string $id): JsonResponse
    {
        try {
            $iku = $this->ikuService->updateIKU($id, $request->validated());

            return response()->json([
                'success' => true,
                'message' => 'IKU updated successfully',
                'data' => new IKUResource($iku),
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
            $this->ikuService->deleteIKU($id);

            return response()->json([
                'success' => true,
                'message' => 'IKU deleted successfully',
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => $e->getMessage(),
            ], 422);
        }
    }

    /**
     * Get list of unique categories
     */
    public function categories(): JsonResponse
    {
        try {
            $categories = $this->ikuService->getCategories();

            return response()->json([
                'success' => true,
                'data' => $categories,
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to fetch categories',
                'error' => $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Get IKU statistics for dashboard
     */
    public function statistics(): JsonResponse
    {
        try {
            $statistics = $this->ikuService->getStatistics();

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
     * Toggle IKU active status
     */
    public function toggleActive(string $id): JsonResponse
    {
        try {
            $iku = $this->ikuService->toggleActiveStatus($id);

            return response()->json([
                'success' => true,
                'message' => 'IKU status updated successfully',
                'data' => new IKUResource($iku),
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => $e->getMessage(),
            ], 422);
        }
    }
}
