<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\SPMI\StoreSpmiMonitoringRequest;
use App\Http\Requests\SPMI\UpdateSpmiMonitoringRequest;
use App\Http\Resources\SPMI\SpmiMonitoringResource;
use App\Services\SpmiMonitoringService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;
use Exception;

class SpmiMonitoringController extends Controller
{
    public function __construct(
        private SpmiMonitoringService $service
    ) {}

    /**
     * Display a listing of SPMI monitoring records.
     */
    public function index(Request $request): AnonymousResourceCollection
    {
        $filters = $request->only([
            'search',
            'spmi_standard_id',
            'tahun_akademik_id',
            'status',
            'order_by',
            'order_dir'
        ]);

        $perPage = $request->input('per_page', 15);
        $monitorings = $this->service->getAllPaginated($filters, $perPage);

        return SpmiMonitoringResource::collection($monitorings);
    }

    /**
     * Store a newly created SPMI monitoring record.
     */
    public function store(StoreSpmiMonitoringRequest $request): JsonResponse
    {
        try {
            $monitoring = $this->service->create($request->validated());

            return response()->json([
                'success' => true,
                'message' => 'Monitoring SPMI berhasil dibuat',
                'data' => new SpmiMonitoringResource($monitoring),
            ], 201);
        } catch (Exception $e) {
            return response()->json([
                'success' => false,
                'message' => $e->getMessage(),
            ], 400);
        }
    }

    /**
     * Display the specified SPMI monitoring record.
     */
    public function show(int $id): JsonResponse
    {
        try {
            $monitoring = $this->service->getById($id);

            if (!$monitoring) {
                return response()->json([
                    'success' => false,
                    'message' => 'Monitoring SPMI tidak ditemukan',
                ], 404);
            }

            return response()->json([
                'success' => true,
                'data' => new SpmiMonitoringResource($monitoring),
            ]);
        } catch (Exception $e) {
            return response()->json([
                'success' => false,
                'message' => $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Update the specified SPMI monitoring record.
     */
    public function update(UpdateSpmiMonitoringRequest $request, int $id): JsonResponse
    {
        try {
            $monitoring = $this->service->update($id, $request->validated());

            return response()->json([
                'success' => true,
                'message' => 'Monitoring SPMI berhasil diperbarui',
                'data' => new SpmiMonitoringResource($monitoring),
            ]);
        } catch (Exception $e) {
            return response()->json([
                'success' => false,
                'message' => $e->getMessage(),
            ], 400);
        }
    }

    /**
     * Remove the specified SPMI monitoring record.
     */
    public function destroy(int $id): JsonResponse
    {
        try {
            $this->service->delete($id);

            return response()->json([
                'success' => true,
                'message' => 'Monitoring SPMI berhasil dihapus',
            ]);
        } catch (Exception $e) {
            return response()->json([
                'success' => false,
                'message' => $e->getMessage(),
            ], 400);
        }
    }

    /**
     * Start SPMI monitoring.
     */
    public function start(int $id): JsonResponse
    {
        try {
            $monitoring = $this->service->start($id);

            return response()->json([
                'success' => true,
                'message' => 'Monitoring SPMI berhasil dimulai',
                'data' => new SpmiMonitoringResource($monitoring),
            ]);
        } catch (Exception $e) {
            return response()->json([
                'success' => false,
                'message' => $e->getMessage(),
            ], 400);
        }
    }

    /**
     * Complete SPMI monitoring with findings and recommendations.
     */
    public function complete(int $id, Request $request): JsonResponse
    {
        try {
            $monitoring = $this->service->complete($id, $request->all());

            return response()->json([
                'success' => true,
                'message' => 'Monitoring SPMI berhasil diselesaikan',
                'data' => new SpmiMonitoringResource($monitoring),
            ]);
        } catch (Exception $e) {
            return response()->json([
                'success' => false,
                'message' => $e->getMessage(),
            ], 400);
        }
    }

    /**
     * Get SPMI monitoring statistics.
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
     * Get dashboard data for SPMI monitoring.
     */
    public function dashboardData(): JsonResponse
    {
        try {
            $dashboardData = $this->service->getDashboardData();

            return response()->json([
                'success' => true,
                'data' => $dashboardData,
            ]);
        } catch (Exception $e) {
            return response()->json([
                'success' => false,
                'message' => $e->getMessage(),
            ], 400);
        }
    }

    /**
     * Upload report file for SPMI monitoring.
     */
    public function uploadReport(int $id, Request $request): JsonResponse
    {
        try {
            $request->validate([
                'report_file' => 'required|file|mimes:pdf,doc,docx|max:10240',
            ]);

            $monitoring = $this->service->uploadReport($id, $request->file('report_file'));

            return response()->json([
                'success' => true,
                'message' => 'Laporan monitoring berhasil diunggah',
                'data' => new SpmiMonitoringResource($monitoring),
            ]);
        } catch (Exception $e) {
            return response()->json([
                'success' => false,
                'message' => $e->getMessage(),
            ], 400);
        }
    }
}
