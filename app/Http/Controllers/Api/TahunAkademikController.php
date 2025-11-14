<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\MasterData\StoreTahunAkademikRequest;
use App\Http\Requests\MasterData\UpdateTahunAkademikRequest;
use App\Http\Resources\MasterData\TahunAkademikResource;
use App\Services\TahunAkademikService;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;

class TahunAkademikController extends Controller
{
    protected TahunAkademikService $tahunAkademikService;

    public function __construct(TahunAkademikService $tahunAkademikService)
    {
        $this->tahunAkademikService = $tahunAkademikService;
    }

    public function index(Request $request): JsonResponse
    {
        try {
            $filters = $request->only(['is_active', 'semester', 'search']);
            $perPage = $request->get('per_page', 15);

            $tahunAkademiks = $this->tahunAkademikService->getAllTahunAkademik($filters, $perPage);

            return response()->json([
                'success' => true,
                'data' => TahunAkademikResource::collection($tahunAkademiks)->response()->getData(true)['data'],
                'meta' => [
                    'current_page' => $tahunAkademiks->currentPage(),
                    'from' => $tahunAkademiks->firstItem(),
                    'last_page' => $tahunAkademiks->lastPage(),
                    'per_page' => $tahunAkademiks->perPage(),
                    'to' => $tahunAkademiks->lastItem(),
                    'total' => $tahunAkademiks->total(),
                ],
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to fetch Tahun Akademik',
                'error' => $e->getMessage(),
            ], 500);
        }
    }

    public function store(StoreTahunAkademikRequest $request): JsonResponse
    {
        try {
            $tahunAkademik = $this->tahunAkademikService->createTahunAkademik($request->validated());

            return response()->json([
                'success' => true,
                'message' => 'Tahun Akademik created successfully',
                'data' => new TahunAkademikResource($tahunAkademik),
            ], 201);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => $e->getMessage(),
            ], 422);
        }
    }

    public function show(string $id): JsonResponse
    {
        try {
            $tahunAkademik = $this->tahunAkademikService->getTahunAkademikById($id);

            if (!$tahunAkademik) {
                return response()->json([
                    'success' => false,
                    'message' => 'Tahun Akademik not found',
                ], 404);
            }

            return response()->json([
                'success' => true,
                'data' => new TahunAkademikResource($tahunAkademik),
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to fetch Tahun Akademik',
                'error' => $e->getMessage(),
            ], 500);
        }
    }

    public function update(UpdateTahunAkademikRequest $request, string $id): JsonResponse
    {
        try {
            $tahunAkademik = $this->tahunAkademikService->updateTahunAkademik($id, $request->validated());

            return response()->json([
                'success' => true,
                'message' => 'Tahun Akademik updated successfully',
                'data' => new TahunAkademikResource($tahunAkademik),
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => $e->getMessage(),
            ], 422);
        }
    }

    public function destroy(string $id): JsonResponse
    {
        try {
            $this->tahunAkademikService->deleteTahunAkademik($id);

            return response()->json([
                'success' => true,
                'message' => 'Tahun Akademik deleted successfully',
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => $e->getMessage(),
            ], 422);
        }
    }

    public function active(): JsonResponse
    {
        try {
            $tahunAkademiks = $this->tahunAkademikService->getActiveTahunAkademik();

            return response()->json([
                'success' => true,
                'data' => TahunAkademikResource::collection($tahunAkademiks),
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to fetch active Tahun Akademik',
                'error' => $e->getMessage(),
            ], 500);
        }
    }

    public function current(): JsonResponse
    {
        try {
            $tahunAkademik = $this->tahunAkademikService->getCurrentTahunAkademik();

            if (!$tahunAkademik) {
                return response()->json([
                    'success' => false,
                    'message' => 'No current Tahun Akademik found',
                ], 404);
            }

            return response()->json([
                'success' => true,
                'data' => new TahunAkademikResource($tahunAkademik),
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to fetch current Tahun Akademik',
                'error' => $e->getMessage(),
            ], 500);
        }
    }

    public function upcoming(): JsonResponse
    {
        try {
            $tahunAkademik = $this->tahunAkademikService->getUpcomingTahunAkademik();

            if (!$tahunAkademik) {
                return response()->json([
                    'success' => false,
                    'message' => 'No upcoming Tahun Akademik found',
                ], 404);
            }

            return response()->json([
                'success' => true,
                'data' => new TahunAkademikResource($tahunAkademik),
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to fetch upcoming Tahun Akademik',
                'error' => $e->getMessage(),
            ], 500);
        }
    }

    public function bySemester(Request $request): JsonResponse
    {
        try {
            $semester = $request->get('semester');

            if (!$semester) {
                return response()->json([
                    'success' => false,
                    'message' => 'Semester parameter is required',
                ], 400);
            }

            $tahunAkademiks = $this->tahunAkademikService->getTahunAkademikBySemester($semester);

            return response()->json([
                'success' => true,
                'data' => TahunAkademikResource::collection($tahunAkademiks),
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => $e->getMessage(),
            ], 422);
        }
    }

    public function statistics(): JsonResponse
    {
        try {
            $statistics = $this->tahunAkademikService->getStatistics();

            return response()->json([
                'success' => true,
                'data' => $statistics,
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to fetch statistics',
                'error' => $e->getMessage(),
            ], 500);
        }
    }

    public function toggleActive(string $id): JsonResponse
    {
        try {
            $tahunAkademik = $this->tahunAkademikService->toggleActiveStatus($id);

            return response()->json([
                'success' => true,
                'message' => 'Tahun Akademik status updated successfully',
                'data' => new TahunAkademikResource($tahunAkademik),
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => $e->getMessage(),
            ], 422);
        }
    }
}
