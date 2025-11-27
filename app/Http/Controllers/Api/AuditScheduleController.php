<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\Audit\StoreAuditScheduleRequest;
use App\Http\Requests\Audit\UpdateAuditScheduleRequest;
use App\Http\Resources\Audit\AuditScheduleResource;
use App\Services\AuditScheduleService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Exception;

class AuditScheduleController extends Controller
{
    public function __construct(
        private AuditScheduleService $service
    ) {}

    /**
     * Display a listing of audit schedules.
     */
    public function index(Request $request): JsonResponse
    {
        $filters = $request->only([
            'audit_plan_id',
            'unit_kerja_id',
            'auditor_lead_id',
            'status',
            'scheduled_from',
            'scheduled_to',
            'search',
            'order_by',
            'order_dir'
        ]);

        $perPage = $request->input('per_page', 15);
        $schedules = $this->service->getAllPaginated($filters, $perPage);

        return response()->json([
            'success' => true,
            'data' => AuditScheduleResource::collection($schedules),
            'meta' => [
                'current_page' => $schedules->currentPage(),
                'last_page' => $schedules->lastPage(),
                'per_page' => $schedules->perPage(),
                'total' => $schedules->total(),
                'from' => $schedules->firstItem(),
                'to' => $schedules->lastItem(),
            ],
        ]);
    }

    /**
     * Store a newly created schedule.
     */
    public function store(StoreAuditScheduleRequest $request): JsonResponse
    {
        try {
            $schedule = $this->service->create($request->validated());

            return response()->json([
                'success' => true,
                'message' => 'Jadwal audit berhasil dibuat',
                'data' => new AuditScheduleResource($schedule),
            ], 201);
        } catch (Exception $e) {
            return response()->json([
                'success' => false,
                'message' => $e->getMessage(),
            ], 400);
        }
    }

    /**
     * Display the specified schedule.
     */
    public function show(int $id): JsonResponse
    {
        try {
            $schedule = $this->service->getById($id);

            if (!$schedule) {
                return response()->json([
                    'success' => false,
                    'message' => 'Jadwal audit tidak ditemukan',
                ], 404);
            }

            return response()->json([
                'success' => true,
                'data' => new AuditScheduleResource($schedule),
            ]);
        } catch (Exception $e) {
            return response()->json([
                'success' => false,
                'message' => $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Update the specified schedule.
     */
    public function update(UpdateAuditScheduleRequest $request, int $id): JsonResponse
    {
        try {
            $schedule = $this->service->update($id, $request->validated());

            return response()->json([
                'success' => true,
                'message' => 'Jadwal audit berhasil diperbarui',
                'data' => new AuditScheduleResource($schedule),
            ]);
        } catch (Exception $e) {
            return response()->json([
                'success' => false,
                'message' => $e->getMessage(),
            ], 400);
        }
    }

    /**
     * Remove the specified schedule.
     */
    public function destroy(int $id): JsonResponse
    {
        try {
            $this->service->delete($id);

            return response()->json([
                'success' => true,
                'message' => 'Jadwal audit berhasil dihapus',
            ]);
        } catch (Exception $e) {
            return response()->json([
                'success' => false,
                'message' => $e->getMessage(),
            ], 400);
        }
    }

    /**
     * Start audit.
     */
    public function start(int $id): JsonResponse
    {
        try {
            $schedule = $this->service->startAudit($id);

            return response()->json([
                'success' => true,
                'message' => 'Audit dimulai',
                'data' => new AuditScheduleResource($schedule),
            ]);
        } catch (Exception $e) {
            return response()->json([
                'success' => false,
                'message' => $e->getMessage(),
            ], 400);
        }
    }

    /**
     * Complete audit.
     */
    public function complete(Request $request, int $id): JsonResponse
    {
        try {
            $schedule = $this->service->completeAudit($id, $request->all());

            return response()->json([
                'success' => true,
                'message' => 'Audit selesai',
                'data' => new AuditScheduleResource($schedule),
            ]);
        } catch (Exception $e) {
            return response()->json([
                'success' => false,
                'message' => $e->getMessage(),
            ], 400);
        }
    }

    /**
     * Reschedule audit.
     */
    public function reschedule(Request $request, int $id): JsonResponse
    {
        try {
            $schedule = $this->service->reschedule($id, $request->all());

            return response()->json([
                'success' => true,
                'message' => 'Jadwal audit berhasil diubah',
                'data' => new AuditScheduleResource($schedule),
            ]);
        } catch (Exception $e) {
            return response()->json([
                'success' => false,
                'message' => $e->getMessage(),
            ], 400);
        }
    }

    /**
     * Get upcoming schedules.
     */
    public function upcoming(Request $request): JsonResponse
    {
        $days = $request->input('days', 7);
        $schedules = $this->service->getUpcoming($days);
        return response()->json([
            'success' => true,
            'data' => AuditScheduleResource::collection($schedules),
        ]);
    }

    /**
     * Get calendar events.
     */
    public function calendar(Request $request): JsonResponse
    {
        $start = $request->input('start');
        $end = $request->input('end');
        $events = $this->service->getCalendarEvents($start, $end);

        return response()->json([
            'success' => true,
            'data' => $events,
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
