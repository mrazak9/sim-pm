<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\SPMI\StoreSpmiStandardRequest;
use App\Http\Requests\SPMI\UpdateSpmiStandardRequest;
use App\Http\Resources\SPMI\SpmiStandardResource;
use App\Services\SpmiStandardService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;
use Exception;

class SpmiStandardController extends Controller
{
    public function __construct(
        private SpmiStandardService $service
    ) {}

    /**
     * Display a listing of SPMI standards.
     */
    public function index(Request $request): AnonymousResourceCollection
    {
        $filters = $request->only([
            'search',
            'category',
            'status',
            'unit_kerja_id',
            'order_by',
            'order_dir'
        ]);

        $perPage = $request->input('per_page', 15);
        $standards = $this->service->getAllPaginated($filters, $perPage);

        return SpmiStandardResource::collection($standards);
    }

    /**
     * Store a newly created SPMI standard.
     */
    public function store(StoreSpmiStandardRequest $request): JsonResponse
    {
        try {
            $standard = $this->service->create($request->validated());

            return response()->json([
                'success' => true,
                'message' => 'Standar SPMI berhasil dibuat',
                'data' => new SpmiStandardResource($standard),
            ], 201);
        } catch (Exception $e) {
            return response()->json([
                'success' => false,
                'message' => $e->getMessage(),
            ], 400);
        }
    }

    /**
     * Display the specified SPMI standard.
     */
    public function show(int $id): JsonResponse
    {
        try {
            $standard = $this->service->getById($id);

            if (!$standard) {
                return response()->json([
                    'success' => false,
                    'message' => 'Standar SPMI tidak ditemukan',
                ], 404);
            }

            return response()->json([
                'success' => true,
                'data' => new SpmiStandardResource($standard),
            ]);
        } catch (Exception $e) {
            return response()->json([
                'success' => false,
                'message' => $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Update the specified SPMI standard.
     */
    public function update(UpdateSpmiStandardRequest $request, int $id): JsonResponse
    {
        try {
            $standard = $this->service->update($id, $request->validated());

            return response()->json([
                'success' => true,
                'message' => 'Standar SPMI berhasil diperbarui',
                'data' => new SpmiStandardResource($standard),
            ]);
        } catch (Exception $e) {
            return response()->json([
                'success' => false,
                'message' => $e->getMessage(),
            ], 400);
        }
    }

    /**
     * Remove the specified SPMI standard.
     */
    public function destroy(int $id): JsonResponse
    {
        try {
            $this->service->delete($id);

            return response()->json([
                'success' => true,
                'message' => 'Standar SPMI berhasil dihapus',
            ]);
        } catch (Exception $e) {
            return response()->json([
                'success' => false,
                'message' => $e->getMessage(),
            ], 400);
        }
    }

    /**
     * Get SPMI standards statistics.
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
     * Approve SPMI standard.
     */
    public function approve(int $id): JsonResponse
    {
        try {
            $standard = $this->service->approve($id);

            return response()->json([
                'success' => true,
                'message' => 'Standar SPMI berhasil disetujui',
                'data' => new SpmiStandardResource($standard),
            ]);
        } catch (Exception $e) {
            return response()->json([
                'success' => false,
                'message' => $e->getMessage(),
            ], 400);
        }
    }

    /**
     * Create revision of SPMI standard.
     */
    public function revise(int $id): JsonResponse
    {
        try {
            $standard = $this->service->createRevision($id);

            return response()->json([
                'success' => true,
                'message' => 'Revisi standar SPMI berhasil dibuat',
                'data' => new SpmiStandardResource($standard),
            ]);
        } catch (Exception $e) {
            return response()->json([
                'success' => false,
                'message' => $e->getMessage(),
            ], 400);
        }
    }

    /**
     * Archive SPMI standard.
     */
    public function archive(int $id): JsonResponse
    {
        try {
            $standard = $this->service->archive($id);

            return response()->json([
                'success' => true,
                'message' => 'Standar SPMI berhasil diarsipkan',
                'data' => new SpmiStandardResource($standard),
            ]);
        } catch (Exception $e) {
            return response()->json([
                'success' => false,
                'message' => $e->getMessage(),
            ], 400);
        }
    }

    /**
     * Get SPMI standards due for review.
     */
    public function getDueForReview(Request $request): AnonymousResourceCollection
    {
        $filters = $request->only([
            'order_by',
            'order_dir'
        ]);

        $perPage = $request->input('per_page', 15);
        $standards = $this->service->getDueForReview($filters, $perPage);

        return SpmiStandardResource::collection($standards);
    }
}
