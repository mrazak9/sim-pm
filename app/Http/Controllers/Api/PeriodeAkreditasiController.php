<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\Akreditasi\StorePeriodeAkreditasiRequest;
use App\Http\Requests\Akreditasi\UpdatePeriodeAkreditasiRequest;
use App\Http\Resources\Akreditasi\PeriodeAkreditasiResource;
use App\Services\PeriodeAkreditasiService;
use App\Services\ButirAkreditasiService;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;

class PeriodeAkreditasiController extends Controller
{
    protected PeriodeAkreditasiService $periodeAkreditasiService;
    protected ButirAkreditasiService $butirAkreditasiService;

    public function __construct(
        PeriodeAkreditasiService $periodeAkreditasiService,
        ButirAkreditasiService $butirAkreditasiService
    ) {
        $this->periodeAkreditasiService = $periodeAkreditasiService;
        $this->butirAkreditasiService = $butirAkreditasiService;
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

    /**
     * Get comprehensive dashboard data for periode akreditasi.
     */
    public function dashboard(string $id): JsonResponse
    {
        try {
            $dashboardData = $this->periodeAkreditasiService->getDashboardData($id);

            return response()->json([
                'success' => true,
                'message' => 'Data dashboard berhasil diambil',
                'data' => $dashboardData,
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Gagal mengambil data dashboard',
                'error' => $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Get gap analysis for periode akreditasi.
     */
    public function gapAnalysis(string $id): JsonResponse
    {
        try {
            $gapAnalysis = $this->periodeAkreditasiService->getGapAnalysis($id);

            return response()->json([
                'success' => true,
                'message' => 'Gap analysis berhasil diambil',
                'data' => $gapAnalysis,
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Gagal mengambil gap analysis',
                'error' => $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Get scoring simulation for periode akreditasi.
     */
    public function scoringSimulation(string $id): JsonResponse
    {
        try {
            $scoringData = $this->periodeAkreditasiService->calculatePredictedScore($id);

            return response()->json([
                'success' => true,
                'message' => 'Scoring simulation berhasil diambil',
                'data' => $scoringData,
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Gagal mengambil scoring simulation',
                'error' => $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Export periode akreditasi to PDF.
     */
    public function exportPDF(string $id)
    {
        try {
            $exporter = new \App\Exports\PeriodeAkreditasiPDF($id);
            return $exporter->download();
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Gagal export PDF',
                'error' => $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Export periode akreditasi to Excel.
     */
    public function exportExcel(string $id)
    {
        try {
            $periode = $this->periodeAkreditasiService->getPeriodeAkreditasiById($id);
            $filename = 'Laporan_Akreditasi_' . str_replace(' ', '_', $periode->nama) . '_' . date('Y-m-d') . '.xlsx';

            return \Excel::download(
                new \App\Exports\PeriodeAkreditasiExcel($id),
                $filename
            );
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Gagal export Excel',
                'error' => $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Copy butir from template to periode.
     */
    public function copyButirFromTemplate(Request $request, string $id): JsonResponse
    {
        try {
            $periode = $this->periodeAkreditasiService->getPeriodeAkreditasiById($id);

            if (!$periode) {
                return response()->json([
                    'success' => false,
                    'message' => 'Periode akreditasi tidak ditemukan',
                ], 404);
            }

            // Use instrumen from periode
            $instrumen = $periode->instrumen;

            if (!$instrumen) {
                return response()->json([
                    'success' => false,
                    'message' => 'Periode belum memiliki instrumen yang ditentukan',
                ], 422);
            }

            $result = $this->butirAkreditasiService->copyButirFromTemplate($id, $instrumen);

            return response()->json([
                'success' => true,
                'message' => 'Butir berhasil di-copy dari template',
                'data' => [
                    'copied_count' => $result['copied_count'],
                ],
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Gagal copy butir dari template',
                'error' => $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Copy butir from another periode.
     */
    public function copyButirFromPeriode(Request $request, string $id): JsonResponse
    {
        $request->validate([
            'source_periode_id' => 'required|integer|exists:periode_akreditasis,id',
        ]);

        try {
            $sourcePeriodeId = $request->input('source_periode_id');

            // Validate that source and target are different
            if ($sourcePeriodeId == $id) {
                return response()->json([
                    'success' => false,
                    'message' => 'Periode sumber dan target tidak boleh sama',
                ], 422);
            }

            $result = $this->butirAkreditasiService->copyButirFromPeriode($sourcePeriodeId, $id);

            return response()->json([
                'success' => true,
                'message' => 'Butir berhasil di-copy dari periode lain',
                'data' => [
                    'copied_count' => $result['copied_count'],
                ],
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Gagal copy butir dari periode lain',
                'error' => $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Get butir count for periode.
     */
    public function getButirCount(string $id): JsonResponse
    {
        try {
            $count = $this->butirAkreditasiService->countByPeriode($id);

            return response()->json([
                'success' => true,
                'message' => 'Jumlah butir berhasil diambil',
                'data' => [
                    'count' => $count,
                ],
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Gagal mengambil jumlah butir',
                'error' => $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Get template count by instrumen.
     */
    public function getTemplateCount(Request $request): JsonResponse
    {
        $request->validate([
            'instrumen' => 'required|string',
        ]);

        try {
            $instrumen = $request->input('instrumen');
            $count = $this->butirAkreditasiService->countTemplatesByInstrumen($instrumen);

            return response()->json([
                'success' => true,
                'message' => 'Jumlah template butir berhasil diambil',
                'data' => [
                    'count' => $count,
                    'instrumen' => $instrumen,
                ],
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Gagal mengambil jumlah template butir',
                'error' => $e->getMessage(),
            ], 500);
        }
    }
}
