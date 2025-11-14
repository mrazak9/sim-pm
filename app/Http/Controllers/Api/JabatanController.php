<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\MasterData\StoreJabatanRequest;
use App\Http\Requests\MasterData\UpdateJabatanRequest;
use App\Http\Resources\MasterData\JabatanResource;
use App\Services\JabatanService;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;

class JabatanController extends Controller
{
    protected JabatanService $jabatanService;

    public function __construct(JabatanService $jabatanService)
    {
        $this->jabatanService = $jabatanService;
    }

    public function index(Request $request): JsonResponse
    {
        try {
            $filters = $request->only(['is_active', 'kategori', 'level', 'search']);
            $perPage = $request->get('per_page', 15);

            $jabatans = $this->jabatanService->getAllJabatan($filters, $perPage);

            return response()->json([
                'success' => true,
                'data' => JabatanResource::collection($jabatans)->response()->getData(true)['data'],
                'meta' => [
                    'current_page' => $jabatans->currentPage(),
                    'from' => $jabatans->firstItem(),
                    'last_page' => $jabatans->lastPage(),
                    'per_page' => $jabatans->perPage(),
                    'to' => $jabatans->lastItem(),
                    'total' => $jabatans->total(),
                ],
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to fetch Jabatan',
                'error' => $e->getMessage(),
            ], 500);
        }
    }

    public function store(StoreJabatanRequest $request): JsonResponse
    {
        try {
            $jabatan = $this->jabatanService->createJabatan($request->validated());

            return response()->json([
                'success' => true,
                'message' => 'Jabatan created successfully',
                'data' => new JabatanResource($jabatan),
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
            $jabatan = $this->jabatanService->getJabatanById($id);

            if (!$jabatan) {
                return response()->json([
                    'success' => false,
                    'message' => 'Jabatan not found',
                ], 404);
            }

            return response()->json([
                'success' => true,
                'data' => new JabatanResource($jabatan),
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to fetch Jabatan',
                'error' => $e->getMessage(),
            ], 500);
        }
    }

    public function update(UpdateJabatanRequest $request, string $id): JsonResponse
    {
        try {
            $jabatan = $this->jabatanService->updateJabatan($id, $request->validated());

            return response()->json([
                'success' => true,
                'message' => 'Jabatan updated successfully',
                'data' => new JabatanResource($jabatan),
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
            $this->jabatanService->deleteJabatan($id);

            return response()->json([
                'success' => true,
                'message' => 'Jabatan deleted successfully',
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
            $jabatans = $this->jabatanService->getActiveJabatan();

            return response()->json([
                'success' => true,
                'data' => JabatanResource::collection($jabatans),
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to fetch active Jabatan',
                'error' => $e->getMessage(),
            ], 500);
        }
    }

    public function byKategori(Request $request): JsonResponse
    {
        try {
            $kategori = $request->get('kategori');

            if (!$kategori) {
                return response()->json([
                    'success' => false,
                    'message' => 'Kategori parameter is required',
                ], 400);
            }

            $jabatans = $this->jabatanService->getJabatanByKategori($kategori);

            return response()->json([
                'success' => true,
                'data' => JabatanResource::collection($jabatans),
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => $e->getMessage(),
            ], 422);
        }
    }

    public function byLevel(Request $request): JsonResponse
    {
        try {
            $level = $request->get('level');

            if (!$level) {
                return response()->json([
                    'success' => false,
                    'message' => 'Level parameter is required',
                ], 400);
            }

            $jabatans = $this->jabatanService->getJabatanByLevel((int) $level);

            return response()->json([
                'success' => true,
                'data' => JabatanResource::collection($jabatans),
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => $e->getMessage(),
            ], 422);
        }
    }

    public function categories(): JsonResponse
    {
        try {
            $categories = $this->jabatanService->getCategories();

            return response()->json([
                'success' => true,
                'data' => $categories,
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to fetch categories',
                'error' => $e->getMessage(),
            ], 500);
        }
    }

    public function statistics(): JsonResponse
    {
        try {
            $statistics = $this->jabatanService->getStatistics();

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
            $jabatan = $this->jabatanService->toggleActiveStatus($id);

            return response()->json([
                'success' => true,
                'message' => 'Jabatan status updated successfully',
                'data' => new JabatanResource($jabatan),
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => $e->getMessage(),
            ], 422);
        }
    }
}
