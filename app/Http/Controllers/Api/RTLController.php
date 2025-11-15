<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\Audit\StoreRTLRequest;
use App\Http\Requests\Audit\UpdateRTLRequest;
use App\Http\Resources\Audit\RTLResource;
use App\Services\RTLService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;
use Exception;

class RTLController extends Controller
{
    public function __construct(
        private RTLService $service
    ) {}

    /**
     * Display a listing of RTLs.
     */
    public function index(Request $request): AnonymousResourceCollection
    {
        $filters = $request->only([
            'audit_finding_id',
            'status',
            'pic_id',
            'unit_kerja_id',
            'verification_status',
            'risk_level',
            'overdue',
            'due_soon',
            'due_soon_days',
            'completion_min',
            'completion_max',
            'search',
            'order_by',
            'order_dir'
        ]);

        $perPage = $request->input('per_page', 15);
        $rtls = $this->service->getAllPaginated($filters, $perPage);

        return RTLResource::collection($rtls);
    }

    /**
     * Store a newly created RTL.
     */
    public function store(StoreRTLRequest $request): JsonResponse
    {
        try {
            $rtl = $this->service->create($request->validated());

            return response()->json([
                'success' => true,
                'message' => 'RTL berhasil dibuat',
                'data' => new RTLResource($rtl),
            ], 201);
        } catch (Exception $e) {
            return response()->json([
                'success' => false,
                'message' => $e->getMessage(),
            ], 400);
        }
    }

    /**
     * Display the specified RTL.
     */
    public function show(int $id): JsonResponse
    {
        try {
            $rtl = $this->service->getById($id);

            if (!$rtl) {
                return response()->json([
                    'success' => false,
                    'message' => 'RTL tidak ditemukan',
                ], 404);
            }

            return response()->json([
                'success' => true,
                'data' => new RTLResource($rtl),
            ]);
        } catch (Exception $e) {
            return response()->json([
                'success' => false,
                'message' => $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Update the specified RTL.
     */
    public function update(UpdateRTLRequest $request, int $id): JsonResponse
    {
        try {
            $rtl = $this->service->update($id, $request->validated());

            return response()->json([
                'success' => true,
                'message' => 'RTL berhasil diperbarui',
                'data' => new RTLResource($rtl),
            ]);
        } catch (Exception $e) {
            return response()->json([
                'success' => false,
                'message' => $e->getMessage(),
            ], 400);
        }
    }

    /**
     * Remove the specified RTL.
     */
    public function destroy(int $id): JsonResponse
    {
        try {
            $this->service->delete($id);

            return response()->json([
                'success' => true,
                'message' => 'RTL berhasil dihapus',
            ]);
        } catch (Exception $e) {
            return response()->json([
                'success' => false,
                'message' => $e->getMessage(),
            ], 400);
        }
    }

    /**
     * Start RTL execution.
     */
    public function start(int $id): JsonResponse
    {
        try {
            $rtl = $this->service->start($id);

            return response()->json([
                'success' => true,
                'message' => 'RTL dimulai',
                'data' => new RTLResource($rtl),
            ]);
        } catch (Exception $e) {
            return response()->json([
                'success' => false,
                'message' => $e->getMessage(),
            ], 400);
        }
    }

    /**
     * Complete RTL.
     */
    public function complete(Request $request, int $id): JsonResponse
    {
        try {
            $rtl = $this->service->complete($id, $request->all());

            return response()->json([
                'success' => true,
                'message' => 'RTL selesai',
                'data' => new RTLResource($rtl),
            ]);
        } catch (Exception $e) {
            return response()->json([
                'success' => false,
                'message' => $e->getMessage(),
            ], 400);
        }
    }

    /**
     * Add progress update to RTL.
     */
    public function addProgress(Request $request, int $id): JsonResponse
    {
        try {
            $progress = $this->service->addProgress($id, $request->all());

            return response()->json([
                'success' => true,
                'message' => 'Progress RTL berhasil ditambahkan',
                'data' => $progress,
            ]);
        } catch (Exception $e) {
            return response()->json([
                'success' => false,
                'message' => $e->getMessage(),
            ], 400);
        }
    }

    /**
     * Verify RTL.
     */
    public function verify(Request $request, int $id): JsonResponse
    {
        try {
            $rtl = $this->service->verify(
                $id,
                $request->input('verification_status'),
                $request->input('verification_notes')
            );

            return response()->json([
                'success' => true,
                'message' => 'RTL berhasil diverifikasi',
                'data' => new RTLResource($rtl),
            ]);
        } catch (Exception $e) {
            return response()->json([
                'success' => false,
                'message' => $e->getMessage(),
            ], 400);
        }
    }

    /**
     * Get overdue RTLs.
     */
    public function overdue(): AnonymousResourceCollection
    {
        $rtls = $this->service->getOverdue();
        return RTLResource::collection($rtls);
    }

    /**
     * Get RTLs due soon.
     */
    public function dueSoon(Request $request): AnonymousResourceCollection
    {
        $days = $request->input('days', 7);
        $rtls = $this->service->getDueSoon($days);
        return RTLResource::collection($rtls);
    }

    /**
     * Get RTLs pending verification.
     */
    public function pendingVerification(): AnonymousResourceCollection
    {
        $rtls = $this->service->getPendingVerification();
        return RTLResource::collection($rtls);
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
     * Get dashboard statistics.
     */
    public function dashboardStatistics(): JsonResponse
    {
        $statistics = $this->service->getDashboardStatistics();

        return response()->json([
            'success' => true,
            'data' => $statistics,
        ]);
    }

    /**
     * Get statistics by unit kerja.
     */
    public function statisticsByUnitKerja(): JsonResponse
    {
        $statistics = $this->service->getStatisticsByUnitKerja();

        return response()->json([
            'success' => true,
            'data' => $statistics,
        ]);
    }
}
