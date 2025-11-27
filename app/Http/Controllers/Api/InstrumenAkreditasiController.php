<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\Akreditasi\StoreInstrumenAkreditasiRequest;
use App\Http\Requests\Akreditasi\UpdateInstrumenAkreditasiRequest;
use App\Http\Resources\Akreditasi\InstrumenAkreditasiResource;
use App\Services\InstrumenAkreditasiService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class InstrumenAkreditasiController extends Controller
{
    protected InstrumenAkreditasiService $instrumenService;

    public function __construct(InstrumenAkreditasiService $instrumenService)
    {
        $this->instrumenService = $instrumenService;
    }

    /**
     * Get list of instrumen with pagination and filters
     */
    public function index(Request $request): JsonResponse
    {
        try {
            $filters = $request->only(['jenis', 'lembaga', 'is_active', 'search']);
            $perPage = $request->get('per_page', 15);

            $instrumens = $this->instrumenService->getAllInstrumen($filters, $perPage);

            return response()->json([
                'success' => true,
                'data' => InstrumenAkreditasiResource::collection($instrumens)->response()->getData(true)['data'],
                'meta' => [
                    'current_page' => $instrumens->currentPage(),
                    'from' => $instrumens->firstItem(),
                    'last_page' => $instrumens->lastPage(),
                    'per_page' => $instrumens->perPage(),
                    'to' => $instrumens->lastItem(),
                    'total' => $instrumens->total(),
                ],
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to fetch instrumen',
                'error' => $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Get active instrumen (for dropdowns)
     */
    public function active(): JsonResponse
    {
        try {
            $instrumens = $this->instrumenService->getActiveInstrumen();

            return response()->json([
                'success' => true,
                'data' => InstrumenAkreditasiResource::collection($instrumens),
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to fetch active instrumen',
                'error' => $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Store new instrumen
     */
    public function store(StoreInstrumenAkreditasiRequest $request): JsonResponse
    {
        try {
            $instrumen = $this->instrumenService->createInstrumen($request->validated());

            return response()->json([
                'success' => true,
                'message' => 'Instrumen created successfully',
                'data' => new InstrumenAkreditasiResource($instrumen),
            ], 201);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to create instrumen',
                'error' => $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Show single instrumen
     */
    public function show(string $id): JsonResponse
    {
        try {
            $instrumen = $this->instrumenService->getInstrumenById($id);

            if (!$instrumen) {
                return response()->json([
                    'success' => false,
                    'message' => 'Instrumen not found',
                ], 404);
            }

            return response()->json([
                'success' => true,
                'data' => new InstrumenAkreditasiResource($instrumen),
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to fetch instrumen',
                'error' => $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Update instrumen
     */
    public function update(UpdateInstrumenAkreditasiRequest $request, string $id): JsonResponse
    {
        try {
            $instrumen = $this->instrumenService->updateInstrumen($id, $request->validated());

            return response()->json([
                'success' => true,
                'message' => 'Instrumen updated successfully',
                'data' => new InstrumenAkreditasiResource($instrumen),
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to update instrumen',
                'error' => $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Delete instrumen
     */
    public function destroy(string $id): JsonResponse
    {
        try {
            $this->instrumenService->deleteInstrumen($id);

            return response()->json([
                'success' => true,
                'message' => 'Instrumen deleted successfully',
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to delete instrumen',
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
            $instrumen = $this->instrumenService->toggleActive($id);

            return response()->json([
                'success' => true,
                'message' => 'Status updated successfully',
                'data' => new InstrumenAkreditasiResource($instrumen),
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to toggle status',
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
            $stats = $this->instrumenService->getStatistics();

            return response()->json([
                'success' => true,
                'data' => $stats,
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to fetch statistics',
                'error' => $e->getMessage(),
            ], 500);
        }
    }
}
