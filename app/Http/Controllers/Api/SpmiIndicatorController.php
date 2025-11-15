<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\SPMI\StoreSpmiIndicatorRequest;
use App\Http\Requests\SPMI\UpdateSpmiIndicatorRequest;
use App\Http\Resources\SPMI\SpmiIndicatorResource;
use App\Services\SpmiIndicatorService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;
use Exception;

class SpmiIndicatorController extends Controller
{
    public function __construct(
        private SpmiIndicatorService $service
    ) {}

    /**
     * Display a listing of SPMI indicators.
     */
    public function index(Request $request): AnonymousResourceCollection
    {
        $filters = $request->only([
            'search',
            'spmi_standard_id',
            'is_active',
            'order_by',
            'order_dir'
        ]);

        $perPage = $request->input('per_page', 15);
        $indicators = $this->service->getAllPaginated($filters, $perPage);

        return SpmiIndicatorResource::collection($indicators);
    }

    /**
     * Store a newly created SPMI indicator.
     */
    public function store(StoreSpmiIndicatorRequest $request): JsonResponse
    {
        try {
            $indicator = $this->service->create($request->validated());

            return response()->json([
                'success' => true,
                'message' => 'Indikator SPMI berhasil dibuat',
                'data' => new SpmiIndicatorResource($indicator),
            ], 201);
        } catch (Exception $e) {
            return response()->json([
                'success' => false,
                'message' => $e->getMessage(),
            ], 400);
        }
    }

    /**
     * Display the specified SPMI indicator.
     */
    public function show(int $id): JsonResponse
    {
        try {
            $indicator = $this->service->getById($id);

            if (!$indicator) {
                return response()->json([
                    'success' => false,
                    'message' => 'Indikator SPMI tidak ditemukan',
                ], 404);
            }

            return response()->json([
                'success' => true,
                'data' => new SpmiIndicatorResource($indicator),
            ]);
        } catch (Exception $e) {
            return response()->json([
                'success' => false,
                'message' => $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Update the specified SPMI indicator.
     */
    public function update(UpdateSpmiIndicatorRequest $request, int $id): JsonResponse
    {
        try {
            $indicator = $this->service->update($id, $request->validated());

            return response()->json([
                'success' => true,
                'message' => 'Indikator SPMI berhasil diperbarui',
                'data' => new SpmiIndicatorResource($indicator),
            ]);
        } catch (Exception $e) {
            return response()->json([
                'success' => false,
                'message' => $e->getMessage(),
            ], 400);
        }
    }

    /**
     * Remove the specified SPMI indicator.
     */
    public function destroy(int $id): JsonResponse
    {
        try {
            $this->service->delete($id);

            return response()->json([
                'success' => true,
                'message' => 'Indikator SPMI berhasil dihapus',
            ]);
        } catch (Exception $e) {
            return response()->json([
                'success' => false,
                'message' => $e->getMessage(),
            ], 400);
        }
    }

    /**
     * Activate SPMI indicator.
     */
    public function activate(int $id): JsonResponse
    {
        try {
            $indicator = $this->service->activate($id);

            return response()->json([
                'success' => true,
                'message' => 'Indikator SPMI berhasil diaktifkan',
                'data' => new SpmiIndicatorResource($indicator),
            ]);
        } catch (Exception $e) {
            return response()->json([
                'success' => false,
                'message' => $e->getMessage(),
            ], 400);
        }
    }

    /**
     * Deactivate SPMI indicator.
     */
    public function deactivate(int $id): JsonResponse
    {
        try {
            $indicator = $this->service->deactivate($id);

            return response()->json([
                'success' => true,
                'message' => 'Indikator SPMI berhasil dinonaktifkan',
                'data' => new SpmiIndicatorResource($indicator),
            ]);
        } catch (Exception $e) {
            return response()->json([
                'success' => false,
                'message' => $e->getMessage(),
            ], 400);
        }
    }

    /**
     * Get SPMI indicators statistics.
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
     * Create target for SPMI indicator.
     */
    public function createTarget(int $id, Request $request): JsonResponse
    {
        try {
            $target = $this->service->createTarget($id, $request->all());

            return response()->json([
                'success' => true,
                'message' => 'Target indikator berhasil dibuat',
                'data' => $target,
            ], 201);
        } catch (Exception $e) {
            return response()->json([
                'success' => false,
                'message' => $e->getMessage(),
            ], 400);
        }
    }

    /**
     * Update target for SPMI indicator.
     */
    public function updateTarget(int $id, int $targetId, Request $request): JsonResponse
    {
        try {
            $target = $this->service->updateTarget($id, $targetId, $request->all());

            return response()->json([
                'success' => true,
                'message' => 'Target indikator berhasil diperbarui',
                'data' => $target,
            ]);
        } catch (Exception $e) {
            return response()->json([
                'success' => false,
                'message' => $e->getMessage(),
            ], 400);
        }
    }

    /**
     * Record achievement for SPMI indicator.
     */
    public function recordAchievement(int $id, Request $request): JsonResponse
    {
        try {
            $achievement = $this->service->recordAchievement($id, $request->all());

            return response()->json([
                'success' => true,
                'message' => 'Pencapaian indikator berhasil dicatat',
                'data' => $achievement,
            ], 201);
        } catch (Exception $e) {
            return response()->json([
                'success' => false,
                'message' => $e->getMessage(),
            ], 400);
        }
    }
}
