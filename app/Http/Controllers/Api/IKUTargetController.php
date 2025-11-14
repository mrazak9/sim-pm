<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\IKU\StoreIKUTargetRequest;
use App\Http\Requests\IKU\UpdateIKUTargetRequest;
use App\Http\Resources\IKUTargetResource;
use App\Services\IKUTargetService;
use App\Exports\IKUTargetExport;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Maatwebsite\Excel\Facades\Excel;
use Barryvdh\DomPDF\Facade\Pdf;

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

    /**
     * Export IKU Targets to Excel
     */
    public function exportExcel(Request $request)
    {
        try {
            $filters = $request->only(['iku_id', 'tahun_akademik_id', 'unit_kerja_id', 'status']);
            $filename = 'iku_targets_' . date('Y-m-d_His') . '.xlsx';

            return Excel::download(new IKUTargetExport($filters), $filename);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to export IKU targets',
                'error' => $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Export IKU Targets to PDF
     */
    public function exportPDF(Request $request)
    {
        try {
            $filters = $request->only(['iku_id', 'tahun_akademik_id', 'unit_kerja_id', 'status']);

            $query = \App\Models\IKUTarget::with(['iku', 'tahunAkademik', 'unitKerja']);

            // Apply filters
            if (!empty($filters['iku_id'])) {
                $query->where('iku_id', $filters['iku_id']);
            }
            if (!empty($filters['tahun_akademik_id'])) {
                $query->where('tahun_akademik_id', $filters['tahun_akademik_id']);
            }
            if (!empty($filters['unit_kerja_id'])) {
                $query->where('unit_kerja_id', $filters['unit_kerja_id']);
            }
            if (isset($filters['status'])) {
                $query->where('status', $filters['status']);
            }

            $targets = $query->orderBy('periode')->get();

            $pdf = Pdf::loadView('exports.iku-target-pdf', compact('targets'));
            $filename = 'iku_targets_' . date('Y-m-d_His') . '.pdf';

            return $pdf->download($filename);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to export IKU targets to PDF',
                'error' => $e->getMessage(),
            ], 500);
        }
    }
}
