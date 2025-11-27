<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\Audit\StoreAuditFindingRequest;
use App\Http\Requests\Audit\UpdateAuditFindingRequest;
use App\Http\Resources\Audit\AuditFindingResource;
use App\Services\AuditFindingService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Exception;

class AuditFindingController extends Controller
{
    public function __construct(
        private AuditFindingService $service
    ) {}

    /**
     * Display a listing of findings.
     */
    public function index(Request $request): JsonResponse
    {
        $filters = $request->only([
            'audit_schedule_id',
            'category',
            'status',
            'priority',
            'pic_id',
            'unit_kerja_id',
            'overdue',
            'search',
            'order_by',
            'order_dir'
        ]);

        $perPage = $request->input('per_page', 15);
        $findings = $this->service->getAllPaginated($filters, $perPage);

        return response()->json([
            'success' => true,
            'data' => AuditFindingResource::collection($findings),
            'meta' => [
                'current_page' => $findings->currentPage(),
                'last_page' => $findings->lastPage(),
                'per_page' => $findings->perPage(),
                'total' => $findings->total(),
                'from' => $findings->firstItem(),
                'to' => $findings->lastItem(),
            ],
        ]);
    }

    /**
     * Store a newly created finding.
     */
    public function store(StoreAuditFindingRequest $request): JsonResponse
    {
        try {
            $finding = $this->service->create($request->validated());

            return response()->json([
                'success' => true,
                'message' => 'Temuan audit berhasil dibuat',
                'data' => new AuditFindingResource($finding),
            ], 201);
        } catch (Exception $e) {
            return response()->json([
                'success' => false,
                'message' => $e->getMessage(),
            ], 400);
        }
    }

    /**
     * Display the specified finding.
     */
    public function show(int $id): JsonResponse
    {
        try {
            $finding = $this->service->getById($id);

            if (!$finding) {
                return response()->json([
                    'success' => false,
                    'message' => 'Temuan audit tidak ditemukan',
                ], 404);
            }

            return response()->json([
                'success' => true,
                'data' => new AuditFindingResource($finding),
            ]);
        } catch (Exception $e) {
            return response()->json([
                'success' => false,
                'message' => $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Update the specified finding.
     */
    public function update(UpdateAuditFindingRequest $request, int $id): JsonResponse
    {
        try {
            $finding = $this->service->update($id, $request->validated());

            return response()->json([
                'success' => true,
                'message' => 'Temuan audit berhasil diperbarui',
                'data' => new AuditFindingResource($finding),
            ]);
        } catch (Exception $e) {
            return response()->json([
                'success' => false,
                'message' => $e->getMessage(),
            ], 400);
        }
    }

    /**
     * Remove the specified finding.
     */
    public function destroy(int $id): JsonResponse
    {
        try {
            $this->service->delete($id);

            return response()->json([
                'success' => true,
                'message' => 'Temuan audit berhasil dihapus',
            ]);
        } catch (Exception $e) {
            return response()->json([
                'success' => false,
                'message' => $e->getMessage(),
            ], 400);
        }
    }

    /**
     * Resolve finding.
     */
    public function resolve(Request $request, int $id): JsonResponse
    {
        try {
            $finding = $this->service->resolve($id, $request->input('resolution_notes'));

            return response()->json([
                'success' => true,
                'message' => 'Temuan berhasil diselesaikan',
                'data' => new AuditFindingResource($finding),
            ]);
        } catch (Exception $e) {
            return response()->json([
                'success' => false,
                'message' => $e->getMessage(),
            ], 400);
        }
    }

    /**
     * Verify finding.
     */
    public function verify(int $id): JsonResponse
    {
        try {
            $finding = $this->service->verify($id);

            return response()->json([
                'success' => true,
                'message' => 'Temuan berhasil diverifikasi',
                'data' => new AuditFindingResource($finding),
            ]);
        } catch (Exception $e) {
            return response()->json([
                'success' => false,
                'message' => $e->getMessage(),
            ], 400);
        }
    }

    /**
     * Close finding.
     */
    public function close(int $id): JsonResponse
    {
        try {
            $finding = $this->service->close($id);

            return response()->json([
                'success' => true,
                'message' => 'Temuan berhasil ditutup',
                'data' => new AuditFindingResource($finding),
            ]);
        } catch (Exception $e) {
            return response()->json([
                'success' => false,
                'message' => $e->getMessage(),
            ], 400);
        }
    }

    /**
     * Reopen finding.
     */
    public function reopen(Request $request, int $id): JsonResponse
    {
        try {
            $finding = $this->service->reopen($id, $request->input('reason'));

            return response()->json([
                'success' => true,
                'message' => 'Temuan berhasil dibuka kembali',
                'data' => new AuditFindingResource($finding),
            ]);
        } catch (Exception $e) {
            return response()->json([
                'success' => false,
                'message' => $e->getMessage(),
            ], 400);
        }
    }

    /**
     * Get overdue findings.
     */
    public function overdue(): JsonResponse
    {
        $findings = $this->service->getOverdue();
        return response()->json([
            'success' => true,
            'data' => AuditFindingResource::collection($findings),
        ]);
    }

    /**
     * Get findings needing attention.
     */
    public function needingAttention(): JsonResponse
    {
        $findings = $this->service->getNeedingAttention();
        return response()->json([
            'success' => true,
            'data' => AuditFindingResource::collection($findings),
        ]);
    }

    /**
     * Get statistics.
     */
    public function statistics(): JsonResponse
    {
        $statistics = $this->service->getStatistics();

        return response()->json([
            'success' => true,
            'data' => $statistics,
        ]);
    }

    /**
     * Get statistics by category.
     */
    public function statisticsByCategory(): JsonResponse
    {
        $statistics = $this->service->getStatisticsByCategory();

        return response()->json([
            'success' => true,
            'data' => $statistics,
        ]);
    }
}
