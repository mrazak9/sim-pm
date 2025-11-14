<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\MasterData\StoreUnitKerjaRequest;
use App\Http\Requests\MasterData\UpdateUnitKerjaRequest;
use App\Http\Resources\MasterData\UnitKerjaResource;
use App\Services\UnitKerjaService;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;

class UnitKerjaController extends Controller
{
    protected UnitKerjaService $unitKerjaService;

    public function __construct(UnitKerjaService $unitKerjaService)
    {
        $this->unitKerjaService = $unitKerjaService;
    }

    /**
     * Display a listing of the resource.
     */
    public function index(Request $request): JsonResponse
    {
        try {
            $filters = $request->only(['is_active', 'jenis_unit', 'parent_id', 'search']);
            $perPage = $request->get('per_page', 15);

            $unitKerjas = $this->unitKerjaService->getAllUnitKerja($filters, $perPage);

            return response()->json([
                'success' => true,
                'data' => UnitKerjaResource::collection($unitKerjas)->response()->getData(true)['data'],
                'meta' => [
                    'current_page' => $unitKerjas->currentPage(),
                    'from' => $unitKerjas->firstItem(),
                    'last_page' => $unitKerjas->lastPage(),
                    'per_page' => $unitKerjas->perPage(),
                    'to' => $unitKerjas->lastItem(),
                    'total' => $unitKerjas->total(),
                ],
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to fetch Unit Kerja',
                'error' => $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(StoreUnitKerjaRequest $request): JsonResponse
    {
        try {
            $unitKerja = $this->unitKerjaService->createUnitKerja($request->validated());

            return response()->json([
                'success' => true,
                'message' => 'Unit Kerja created successfully',
                'data' => new UnitKerjaResource($unitKerja),
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
            $unitKerja = $this->unitKerjaService->getUnitKerjaById($id);

            if (!$unitKerja) {
                return response()->json([
                    'success' => false,
                    'message' => 'Unit Kerja not found',
                ], 404);
            }

            return response()->json([
                'success' => true,
                'data' => new UnitKerjaResource($unitKerja),
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to fetch Unit Kerja',
                'error' => $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(UpdateUnitKerjaRequest $request, string $id): JsonResponse
    {
        try {
            $unitKerja = $this->unitKerjaService->updateUnitKerja($id, $request->validated());

            return response()->json([
                'success' => true,
                'message' => 'Unit Kerja updated successfully',
                'data' => new UnitKerjaResource($unitKerja),
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
            $this->unitKerjaService->deleteUnitKerja($id);

            return response()->json([
                'success' => true,
                'message' => 'Unit Kerja deleted successfully',
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => $e->getMessage(),
            ], 422);
        }
    }

    /**
     * Get active unit kerja
     */
    public function active(): JsonResponse
    {
        try {
            $unitKerjas = $this->unitKerjaService->getActiveUnitKerja();

            return response()->json([
                'success' => true,
                'data' => UnitKerjaResource::collection($unitKerjas),
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to fetch active Unit Kerja',
                'error' => $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Get unit kerja by jenis
     */
    public function byJenis(Request $request): JsonResponse
    {
        try {
            $jenis = $request->get('jenis');

            if (!$jenis) {
                return response()->json([
                    'success' => false,
                    'message' => 'Jenis parameter is required',
                ], 400);
            }

            $unitKerjas = $this->unitKerjaService->getUnitKerjaByJenis($jenis);

            return response()->json([
                'success' => true,
                'data' => UnitKerjaResource::collection($unitKerjas),
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => $e->getMessage(),
            ], 422);
        }
    }

    /**
     * Get root units (no parent)
     */
    public function roots(): JsonResponse
    {
        try {
            $unitKerjas = $this->unitKerjaService->getRootUnits();

            return response()->json([
                'success' => true,
                'data' => UnitKerjaResource::collection($unitKerjas),
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to fetch root units',
                'error' => $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Get children of a unit
     */
    public function children(string $parentId): JsonResponse
    {
        try {
            $unitKerjas = $this->unitKerjaService->getChildren($parentId);

            return response()->json([
                'success' => true,
                'data' => UnitKerjaResource::collection($unitKerjas),
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to fetch children units',
                'error' => $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Get statistics
     */
    public function statistics(): JsonResponse
    {
        try {
            $statistics = $this->unitKerjaService->getStatistics();

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
     * Toggle active status
     */
    public function toggleActive(string $id): JsonResponse
    {
        try {
            $unitKerja = $this->unitKerjaService->toggleActiveStatus($id);

            return response()->json([
                'success' => true,
                'message' => 'Unit Kerja status updated successfully',
                'data' => new UnitKerjaResource($unitKerja),
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => $e->getMessage(),
            ], 422);
        }
    }
}
