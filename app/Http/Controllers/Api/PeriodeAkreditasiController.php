<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\Akreditasi\StorePeriodeAkreditasiRequest;
use App\Http\Requests\Akreditasi\UpdatePeriodeAkreditasiRequest;
use App\Http\Resources\Akreditasi\PeriodeAkreditasiResource;
use App\Services\PeriodeAkreditasiService;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;

class PeriodeAkreditasiController extends Controller
{
    protected PeriodeAkreditasiService $periodeAkreditasiService;

    public function __construct(PeriodeAkreditasiService $periodeAkreditasiService)
    {
        $this->periodeAkreditasiService = $periodeAkreditasiService;
    }

    /**
     * Display a listing of periode akreditasi.
     */
    public function index(Request $request): JsonResponse
    {
        try {
            $filters = $request->only([
                'jenis_akreditasi',
                'lembaga',
                'status',
                'program_studi_id',
                'search'
            ]);
            $perPage = $request->get('per_page', 15);
            $sortBy = $request->get('sort_by', 'created_at');
            $sortOrder = $request->get('sort_order', 'desc');

            $periodes = $this->periodeAkreditasiService->getAllPeriodeAkreditasi(
                $filters,
                $perPage,
                $sortBy,
                $sortOrder
            );

            return response()->json([
                'success' => true,
                'message' => 'Data periode akreditasi berhasil diambil',
                'data' => PeriodeAkreditasiResource::collection($periodes)->response()->getData(true)['data'],
                'meta' => [
                    'current_page' => $periodes->currentPage(),
                    'from' => $periodes->firstItem(),
                    'last_page' => $periodes->lastPage(),
                    'per_page' => $periodes->perPage(),
                    'to' => $periodes->lastItem(),
                    'total' => $periodes->total(),
                ],
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Gagal mengambil data periode akreditasi',
                'error' => $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Store a newly created periode akreditasi.
     */
    public function store(StorePeriodeAkreditasiRequest $request): JsonResponse
    {
        try {
            $periode = $this->periodeAkreditasiService->createPeriodeAkreditasi($request->validated());

            return response()->json([
                'success' => true,
                'message' => 'Periode akreditasi berhasil dibuat',
                'data' => new PeriodeAkreditasiResource($periode),
            ], 201);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Gagal membuat periode akreditasi',
                'error' => $e->getMessage(),
            ], 422);
        }
    }

    /**
     * Display the specified periode akreditasi.
     */
    public function show(string $id): JsonResponse
    {
        try {
            $periode = $this->periodeAkreditasiService->getPeriodeAkreditasiById($id);

            if (!$periode) {
                return response()->json([
                    'success' => false,
                    'message' => 'Periode akreditasi tidak ditemukan',
                ], 404);
            }

            return response()->json([
                'success' => true,
                'message' => 'Detail periode akreditasi berhasil diambil',
                'data' => new PeriodeAkreditasiResource($periode),
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Periode akreditasi tidak ditemukan',
                'error' => $e->getMessage(),
            ], 404);
        }
    }

    /**
     * Update the specified periode akreditasi.
     */
    public function update(UpdatePeriodeAkreditasiRequest $request, string $id): JsonResponse
    {
        try {
            $periode = $this->periodeAkreditasiService->updatePeriodeAkreditasi($id, $request->validated());

            return response()->json([
                'success' => true,
                'message' => 'Periode akreditasi berhasil diupdate',
                'data' => new PeriodeAkreditasiResource($periode),
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Gagal mengupdate periode akreditasi',
                'error' => $e->getMessage(),
            ], 422);
        }
    }

    /**
     * Remove the specified periode akreditasi.
     */
    public function destroy(string $id): JsonResponse
    {
        try {
            $this->periodeAkreditasiService->deletePeriodeAkreditasi($id);

            return response()->json([
                'success' => true,
                'message' => 'Periode akreditasi berhasil dihapus',
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Gagal menghapus periode akreditasi',
                'error' => $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Get statistics for periode akreditasi.
     */
    public function statistics(string $id): JsonResponse
    {
        try {
            $statistics = $this->periodeAkreditasiService->getStatistics($id);

            return response()->json([
                'success' => true,
                'message' => 'Statistik periode akreditasi berhasil diambil',
                'data' => $statistics,
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Gagal mengambil statistik',
                'error' => $e->getMessage(),
            ], 500);
        }
    }
}
