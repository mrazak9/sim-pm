<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\Audit\StoreAuditPlanRequest;
use App\Http\Requests\Audit\UpdateAuditPlanRequest;
use App\Http\Resources\Audit\AuditPlanResource;
use App\Services\AuditPlanService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Exception;

class AuditPlanController extends Controller
{
    public function __construct(
        private AuditPlanService $service
    ) {}

    /**
     * Display a listing of audit plans.
     */
    public function index(Request $request): JsonResponse
    {
        $filters = $request->only([
            'tahun_akademik_id',
            'status',
            'periode',
            'search',
            'year',
            'order_by',
            'order_dir'
        ]);

        $perPage = $request->input('per_page', 15);
        $auditPlans = $this->service->getAllPaginated($filters, $perPage);

        return response()->json([
            'success' => true,
            'data' => AuditPlanResource::collection($auditPlans),
            'meta' => [
                'current_page' => $auditPlans->currentPage(),
                'last_page' => $auditPlans->lastPage(),
                'per_page' => $auditPlans->perPage(),
                'total' => $auditPlans->total(),
                'from' => $auditPlans->firstItem(),
                'to' => $auditPlans->lastItem(),
            ],
        ]);
    }

    /**
     * Store a newly created audit plan.
     */
    public function store(StoreAuditPlanRequest $request): JsonResponse
    {
        try {
            $auditPlan = $this->service->create($request->validated());

            return response()->json([
                'success' => true,
                'message' => 'Rencana audit berhasil dibuat',
                'data' => new AuditPlanResource($auditPlan),
            ], 201);
        } catch (Exception $e) {
            return response()->json([
                'success' => false,
                'message' => $e->getMessage(),
            ], 400);
        }
    }

    /**
     * Display the specified audit plan.
     */
    public function show(int $id): JsonResponse
    {
        try {
            $auditPlan = $this->service->getById($id);

            if (!$auditPlan) {
                return response()->json([
                    'success' => false,
                    'message' => 'Rencana audit tidak ditemukan',
                ], 404);
            }

            return response()->json([
                'success' => true,
                'data' => new AuditPlanResource($auditPlan),
            ]);
        } catch (Exception $e) {
            return response()->json([
                'success' => false,
                'message' => $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Update the specified audit plan.
     */
    public function update(UpdateAuditPlanRequest $request, int $id): JsonResponse
    {
        try {
            $auditPlan = $this->service->update($id, $request->validated());

            return response()->json([
                'success' => true,
                'message' => 'Rencana audit berhasil diperbarui',
                'data' => new AuditPlanResource($auditPlan),
            ]);
        } catch (Exception $e) {
            return response()->json([
                'success' => false,
                'message' => $e->getMessage(),
            ], 400);
        }
    }

    /**
     * Remove the specified audit plan.
     */
    public function destroy(int $id): JsonResponse
    {
        try {
            $this->service->delete($id);

            return response()->json([
                'success' => true,
                'message' => 'Rencana audit berhasil dihapus',
            ]);
        } catch (Exception $e) {
            return response()->json([
                'success' => false,
                'message' => $e->getMessage(),
            ], 400);
        }
    }

    /**
     * Approve audit plan.
     */
    public function approve(int $id): JsonResponse
    {
        try {
            $auditPlan = $this->service->approve($id);

            return response()->json([
                'success' => true,
                'message' => 'Rencana audit berhasil disetujui',
                'data' => new AuditPlanResource($auditPlan),
            ]);
        } catch (Exception $e) {
            return response()->json([
                'success' => false,
                'message' => $e->getMessage(),
            ], 400);
        }
    }

    /**
     * Start audit plan execution.
     */
    public function start(int $id): JsonResponse
    {
        try {
            $auditPlan = $this->service->startExecution($id);

            return response()->json([
                'success' => true,
                'message' => 'Pelaksanaan audit dimulai',
                'data' => new AuditPlanResource($auditPlan),
            ]);
        } catch (Exception $e) {
            return response()->json([
                'success' => false,
                'message' => $e->getMessage(),
            ], 400);
        }
    }

    /**
     * Complete audit plan.
     */
    public function complete(int $id): JsonResponse
    {
        try {
            $auditPlan = $this->service->complete($id);

            return response()->json([
                'success' => true,
                'message' => 'Rencana audit berhasil diselesaikan',
                'data' => new AuditPlanResource($auditPlan),
            ]);
        } catch (Exception $e) {
            return response()->json([
                'success' => false,
                'message' => $e->getMessage(),
            ], 400);
        }
    }

    /**
     * Get active audit plans.
     */
    public function active(): JsonResponse
    {
        $auditPlans = $this->service->getActive();
        return response()->json([
            'success' => true,
            'data' => AuditPlanResource::collection($auditPlans),
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
}
