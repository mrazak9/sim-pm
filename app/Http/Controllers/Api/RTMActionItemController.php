<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\SPMI\StoreRTMActionItemRequest;
use App\Http\Requests\SPMI\UpdateRTMActionItemRequest;
use App\Http\Resources\SPMI\RTMActionItemResource;
use App\Services\RTMActionItemService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;
use Exception;

class RTMActionItemController extends Controller
{
    public function __construct(
        private RTMActionItemService $service
    ) {}

    /**
     * Display a listing of RTM action items.
     */
    public function index(Request $request): AnonymousResourceCollection
    {
        $filters = $request->only([
            'search',
            'rtm_id',
            'status',
            'priority',
            'pic_id',
            'order_by',
            'order_dir'
        ]);

        $perPage = $request->input('per_page', 15);
        $actionItems = $this->service->getAllPaginated($filters, $perPage);

        return RTMActionItemResource::collection($actionItems);
    }

    /**
     * Store a newly created RTM action item.
     */
    public function store(StoreRTMActionItemRequest $request): JsonResponse
    {
        try {
            $actionItem = $this->service->create($request->validated());

            return response()->json([
                'success' => true,
                'message' => 'Item tindakan RTM berhasil dibuat',
                'data' => new RTMActionItemResource($actionItem),
            ], 201);
        } catch (Exception $e) {
            return response()->json([
                'success' => false,
                'message' => $e->getMessage(),
            ], 400);
        }
    }

    /**
     * Display the specified RTM action item.
     */
    public function show(int $id): JsonResponse
    {
        try {
            $actionItem = $this->service->getById($id);

            if (!$actionItem) {
                return response()->json([
                    'success' => false,
                    'message' => 'Item tindakan RTM tidak ditemukan',
                ], 404);
            }

            return response()->json([
                'success' => true,
                'data' => new RTMActionItemResource($actionItem),
            ]);
        } catch (Exception $e) {
            return response()->json([
                'success' => false,
                'message' => $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Update the specified RTM action item.
     */
    public function update(UpdateRTMActionItemRequest $request, int $id): JsonResponse
    {
        try {
            $actionItem = $this->service->update($id, $request->validated());

            return response()->json([
                'success' => true,
                'message' => 'Item tindakan RTM berhasil diperbarui',
                'data' => new RTMActionItemResource($actionItem),
            ]);
        } catch (Exception $e) {
            return response()->json([
                'success' => false,
                'message' => $e->getMessage(),
            ], 400);
        }
    }

    /**
     * Remove the specified RTM action item.
     */
    public function destroy(int $id): JsonResponse
    {
        try {
            $this->service->delete($id);

            return response()->json([
                'success' => true,
                'message' => 'Item tindakan RTM berhasil dihapus',
            ]);
        } catch (Exception $e) {
            return response()->json([
                'success' => false,
                'message' => $e->getMessage(),
            ], 400);
        }
    }

    /**
     * Start RTM action item.
     */
    public function start(int $id): JsonResponse
    {
        try {
            $actionItem = $this->service->start($id);

            return response()->json([
                'success' => true,
                'message' => 'Item tindakan RTM berhasil dimulai',
                'data' => new RTMActionItemResource($actionItem),
            ]);
        } catch (Exception $e) {
            return response()->json([
                'success' => false,
                'message' => $e->getMessage(),
            ], 400);
        }
    }

    /**
     * Complete RTM action item with remarks.
     */
    public function complete(int $id, Request $request): JsonResponse
    {
        try {
            $actionItem = $this->service->complete($id, $request->all());

            return response()->json([
                'success' => true,
                'message' => 'Item tindakan RTM berhasil diselesaikan',
                'data' => new RTMActionItemResource($actionItem),
            ]);
        } catch (Exception $e) {
            return response()->json([
                'success' => false,
                'message' => $e->getMessage(),
            ], 400);
        }
    }

    /**
     * Cancel RTM action item with reason.
     */
    public function cancel(int $id, Request $request): JsonResponse
    {
        try {
            $actionItem = $this->service->cancel($id, $request->input('reason'));

            return response()->json([
                'success' => true,
                'message' => 'Item tindakan RTM berhasil dibatalkan',
                'data' => new RTMActionItemResource($actionItem),
            ]);
        } catch (Exception $e) {
            return response()->json([
                'success' => false,
                'message' => $e->getMessage(),
            ], 400);
        }
    }

    /**
     * Get RTM action item statistics.
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
     * Get dashboard statistics for RTM action items.
     */
    public function dashboardStatistics(): JsonResponse
    {
        try {
            $statistics = $this->service->getDashboardStatistics();

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
     * Get overdue RTM action items.
     */
    public function overdue(): AnonymousResourceCollection
    {
        $actionItems = $this->service->getOverdue();
        return RTMActionItemResource::collection($actionItems);
    }

    /**
     * Get RTM action items due soon (default 7 days).
     */
    public function dueSoon(Request $request): AnonymousResourceCollection
    {
        $days = $request->input('days', 7);
        $actionItems = $this->service->getDueSoon($days);

        return RTMActionItemResource::collection($actionItems);
    }

    /**
     * Add progress to RTM action item.
     */
    public function addProgress(int $id, Request $request): JsonResponse
    {
        try {
            $request->validate([
                'progress_date' => 'required|date',
                'progress_percentage' => 'required|integer|min:0|max:100',
                'description' => 'required|string',
                'evidence_file' => 'nullable|file|mimes:pdf,doc,docx,jpg,jpeg,png|max:10240',
            ]);

            $progress = $this->service->addProgress(
                $id,
                $request->input('progress_date'),
                $request->input('progress_percentage'),
                $request->input('description'),
                $request->file('evidence_file')
            );

            return response()->json([
                'success' => true,
                'message' => 'Progress item tindakan berhasil dicatat',
                'data' => $progress,
            ], 201);
        } catch (Exception $e) {
            return response()->json([
                'success' => false,
                'message' => $e->getMessage(),
            ], 400);
        }
    }

    /**
     * Extend due date for RTM action item.
     */
    public function extend(int $id, Request $request): JsonResponse
    {
        try {
            $request->validate([
                'new_due_date' => 'required|date|after:today',
                'reason' => 'required|string',
            ]);

            $actionItem = $this->service->extendDueDate(
                $id,
                $request->input('new_due_date'),
                $request->input('reason')
            );

            return response()->json([
                'success' => true,
                'message' => 'Tanggal jatuh tempo item tindakan berhasil diperpanjang',
                'data' => new RTMActionItemResource($actionItem),
            ]);
        } catch (Exception $e) {
            return response()->json([
                'success' => false,
                'message' => $e->getMessage(),
            ], 400);
        }
    }
}
