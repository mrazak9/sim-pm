<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\MasterData\StoreProgramStudiRequest;
use App\Http\Requests\MasterData\UpdateProgramStudiRequest;
use App\Http\Resources\MasterData\ProgramStudiResource;
use App\Services\ProgramStudiService;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;

class ProgramStudiController extends Controller
{
    protected ProgramStudiService $programStudiService;

    public function __construct(ProgramStudiService $programStudiService)
    {
        $this->programStudiService = $programStudiService;
    }

    public function index(Request $request): JsonResponse
    {
        try {
            $filters = $request->only(['is_active', 'jenjang', 'unit_kerja_id', 'akreditasi', 'search']);
            $perPage = $request->get('per_page', 15);

            $programStudis = $this->programStudiService->getAllProgramStudi($filters, $perPage);

            return response()->json([
                'success' => true,
                'data' => ProgramStudiResource::collection($programStudis)->response()->getData(true)['data'],
                'meta' => [
                    'current_page' => $programStudis->currentPage(),
                    'from' => $programStudis->firstItem(),
                    'last_page' => $programStudis->lastPage(),
                    'per_page' => $programStudis->perPage(),
                    'to' => $programStudis->lastItem(),
                    'total' => $programStudis->total(),
                ],
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to fetch Program Studi',
                'error' => $e->getMessage(),
            ], 500);
        }
    }

    public function store(StoreProgramStudiRequest $request): JsonResponse
    {
        try {
            $programStudi = $this->programStudiService->createProgramStudi($request->validated());

            return response()->json([
                'success' => true,
                'message' => 'Program Studi created successfully',
                'data' => new ProgramStudiResource($programStudi),
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
            $programStudi = $this->programStudiService->getProgramStudiById($id);

            if (!$programStudi) {
                return response()->json([
                    'success' => false,
                    'message' => 'Program Studi not found',
                ], 404);
            }

            return response()->json([
                'success' => true,
                'data' => new ProgramStudiResource($programStudi),
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to fetch Program Studi',
                'error' => $e->getMessage(),
            ], 500);
        }
    }

    public function update(UpdateProgramStudiRequest $request, string $id): JsonResponse
    {
        try {
            $programStudi = $this->programStudiService->updateProgramStudi($id, $request->validated());

            return response()->json([
                'success' => true,
                'message' => 'Program Studi updated successfully',
                'data' => new ProgramStudiResource($programStudi),
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
            $this->programStudiService->deleteProgramStudi($id);

            return response()->json([
                'success' => true,
                'message' => 'Program Studi deleted successfully',
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
            $programStudis = $this->programStudiService->getActiveProgramStudi();

            return response()->json([
                'success' => true,
                'data' => ProgramStudiResource::collection($programStudis),
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to fetch active Program Studi',
                'error' => $e->getMessage(),
            ], 500);
        }
    }

    public function byJenjang(Request $request): JsonResponse
    {
        try {
            $jenjang = $request->get('jenjang');

            if (!$jenjang) {
                return response()->json([
                    'success' => false,
                    'message' => 'Jenjang parameter is required',
                ], 400);
            }

            $programStudis = $this->programStudiService->getProgramStudiByJenjang($jenjang);

            return response()->json([
                'success' => true,
                'data' => ProgramStudiResource::collection($programStudis),
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => $e->getMessage(),
            ], 422);
        }
    }

    public function byUnitKerja(string $unitKerjaId): JsonResponse
    {
        try {
            $programStudis = $this->programStudiService->getProgramStudiByUnitKerja($unitKerjaId);

            return response()->json([
                'success' => true,
                'data' => ProgramStudiResource::collection($programStudis),
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to fetch Program Studi',
                'error' => $e->getMessage(),
            ], 500);
        }
    }

    public function byAkreditasi(Request $request): JsonResponse
    {
        try {
            $akreditasi = $request->get('akreditasi');

            if (!$akreditasi) {
                return response()->json([
                    'success' => false,
                    'message' => 'Akreditasi parameter is required',
                ], 400);
            }

            $programStudis = $this->programStudiService->getProgramStudiByAkreditasi($akreditasi);

            return response()->json([
                'success' => true,
                'data' => ProgramStudiResource::collection($programStudis),
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
            $statistics = $this->programStudiService->getStatistics();

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
            $programStudi = $this->programStudiService->toggleActiveStatus($id);

            return response()->json([
                'success' => true,
                'message' => 'Program Studi status updated successfully',
                'data' => new ProgramStudiResource($programStudi),
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => $e->getMessage(),
            ], 422);
        }
    }
}
