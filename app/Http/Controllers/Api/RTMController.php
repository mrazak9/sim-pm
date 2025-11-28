<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\SPMI\StoreRTMRequest;
use App\Http\Requests\SPMI\UpdateRTMRequest;
use App\Http\Resources\SPMI\RTMResource;
use App\Services\RTMService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;
use Exception;

class RTMController extends Controller
{
    public function __construct(
        private RTMService $service
    ) {}

    /**
     * Display a listing of RTMs.
     */
    public function index(Request $request): JsonResponse
    {
        $filters = $request->only([
            'search',
            'tahun_akademik_id',
            'status',
            'year',
            'month',
            'order_by',
            'order_dir'
        ]);

        $perPage = $request->input('per_page', 15);
        $rtms = $this->service->getAllPaginated($filters, $perPage);

        return response()->json([
            'success' => true,
            'data' => RTMResource::collection($rtms)->resolve(),
            'meta' => [
                'current_page' => $rtms->currentPage(),
                'last_page' => $rtms->lastPage(),
                'per_page' => $rtms->perPage(),
                'total' => $rtms->total(),
                'from' => $rtms->firstItem(),
                'to' => $rtms->lastItem(),
            ],
        ]);
    }

    /**
     * Store a newly created RTM.
     */
    public function store(StoreRTMRequest $request): JsonResponse
    {
        try {
            $rtm = $this->service->create($request->validated());

            return response()->json([
                'success' => true,
                'message' => 'RTM berhasil dibuat',
                'data' => new RTMResource($rtm),
            ], 201);
        } catch (Exception $e) {
            return response()->json([
                'success' => false,
                'message' => $e->getMessage(),
            ], 400);
        }
    }

    /**
     * Display the specified RTM.
     */
    public function show(int $id): JsonResponse
    {
        try {
            $rtm = $this->service->getById($id);

            if (!$rtm) {
                return response()->json([
                    'success' => false,
                    'message' => 'RTM tidak ditemukan',
                ], 404);
            }

            return response()->json([
                'success' => true,
                'data' => new RTMResource($rtm),
            ]);
        } catch (Exception $e) {
            return response()->json([
                'success' => false,
                'message' => $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Update the specified RTM.
     */
    public function update(UpdateRTMRequest $request, int $id): JsonResponse
    {
        try {
            $rtm = $this->service->update($id, $request->validated());

            return response()->json([
                'success' => true,
                'message' => 'RTM berhasil diperbarui',
                'data' => new RTMResource($rtm),
            ]);
        } catch (Exception $e) {
            return response()->json([
                'success' => false,
                'message' => $e->getMessage(),
            ], 400);
        }
    }

    /**
     * Remove the specified RTM.
     */
    public function destroy(int $id): JsonResponse
    {
        try {
            $this->service->delete($id);

            return response()->json([
                'success' => true,
                'message' => 'RTM berhasil dihapus',
            ]);
        } catch (Exception $e) {
            return response()->json([
                'success' => false,
                'message' => $e->getMessage(),
            ], 400);
        }
    }

    /**
     * Start RTM.
     */
    public function start(int $id): JsonResponse
    {
        try {
            $rtm = $this->service->start($id);

            return response()->json([
                'success' => true,
                'message' => 'RTM berhasil dimulai',
                'data' => new RTMResource($rtm),
            ]);
        } catch (Exception $e) {
            return response()->json([
                'success' => false,
                'message' => $e->getMessage(),
            ], 400);
        }
    }

    /**
     * Complete RTM with decisions and minutes.
     */
    public function complete(int $id, Request $request): JsonResponse
    {
        try {
            $rtm = $this->service->complete($id, $request->all());

            return response()->json([
                'success' => true,
                'message' => 'RTM berhasil diselesaikan',
                'data' => new RTMResource($rtm),
            ]);
        } catch (Exception $e) {
            return response()->json([
                'success' => false,
                'message' => $e->getMessage(),
            ], 400);
        }
    }

    /**
     * Cancel RTM with reason.
     */
    public function cancel(int $id, Request $request): JsonResponse
    {
        try {
            $rtm = $this->service->cancel($id, $request->input('reason'));

            return response()->json([
                'success' => true,
                'message' => 'RTM berhasil dibatalkan',
                'data' => new RTMResource($rtm),
            ]);
        } catch (Exception $e) {
            return response()->json([
                'success' => false,
                'message' => $e->getMessage(),
            ], 400);
        }
    }

    /**
     * Get RTM statistics.
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
     * Get upcoming RTMs (default 30 days).
     */
    public function upcoming(Request $request): AnonymousResourceCollection
    {
        $days = $request->input('days', 30);
        $filters = $request->only([
            'order_by',
            'order_dir'
        ]);

        $perPage = $request->input('per_page', 15);
        $rtms = $this->service->getUpcoming($days, $filters, $perPage);

        return RTMResource::collection($rtms);
    }

    /**
     * Add participant to RTM.
     */
    public function addParticipant(int $id, Request $request): JsonResponse
    {
        try {
            $request->validate([
                'user_id' => 'required|integer|exists:users,id',
                'role' => 'required|string',
            ]);

            $participant = $this->service->addParticipant($id, $request->input('user_id'), $request->input('role'));

            return response()->json([
                'success' => true,
                'message' => 'Peserta RTM berhasil ditambahkan',
                'data' => $participant,
            ], 201);
        } catch (Exception $e) {
            return response()->json([
                'success' => false,
                'message' => $e->getMessage(),
            ], 400);
        }
    }

    /**
     * Remove participant from RTM.
     */
    public function removeParticipant(int $id, Request $request): JsonResponse
    {
        try {
            $request->validate([
                'user_id' => 'required|integer|exists:users,id',
            ]);

            $this->service->removeParticipant($id, $request->input('user_id'));

            return response()->json([
                'success' => true,
                'message' => 'Peserta RTM berhasil dihapus',
            ]);
        } catch (Exception $e) {
            return response()->json([
                'success' => false,
                'message' => $e->getMessage(),
            ], 400);
        }
    }

    /**
     * Mark participant attendance.
     */
    public function markAttendance(int $id, Request $request): JsonResponse
    {
        try {
            $request->validate([
                'user_id' => 'required|integer|exists:users,id',
                'is_present' => 'required|boolean',
            ]);

            $participant = $this->service->markAttendance($id, $request->input('user_id'), $request->input('is_present'));

            return response()->json([
                'success' => true,
                'message' => 'Kehadiran peserta berhasil dicatat',
                'data' => $participant,
            ]);
        } catch (Exception $e) {
            return response()->json([
                'success' => false,
                'message' => $e->getMessage(),
            ], 400);
        }
    }

    /**
     * Upload minutes file for RTM.
     */
    public function uploadMinutes(int $id, Request $request): JsonResponse
    {
        try {
            $request->validate([
                'minutes_file' => 'required|file|mimes:pdf,doc,docx|max:10240',
            ]);

            $rtm = $this->service->uploadMinutes($id, $request->file('minutes_file'));

            return response()->json([
                'success' => true,
                'message' => 'Notulen RTM berhasil diunggah',
                'data' => new RTMResource($rtm),
            ]);
        } catch (Exception $e) {
            return response()->json([
                'success' => false,
                'message' => $e->getMessage(),
            ], 400);
        }
    }

    /**
     * Upload attendance file for RTM.
     */
    public function uploadAttendance(int $id, Request $request): JsonResponse
    {
        try {
            $request->validate([
                'attendance_file' => 'required|file|mimes:pdf,doc,docx,xls,xlsx|max:10240',
            ]);

            $rtm = $this->service->uploadAttendance($id, $request->file('attendance_file'));

            return response()->json([
                'success' => true,
                'message' => 'Daftar hadir RTM berhasil diunggah',
                'data' => new RTMResource($rtm),
            ]);
        } catch (Exception $e) {
            return response()->json([
                'success' => false,
                'message' => $e->getMessage(),
            ], 400);
        }
    }
}
