<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\Akreditasi\StoreDokumenAkreditasiRequest;
use App\Http\Requests\Akreditasi\UpdateDokumenAkreditasiRequest;
use App\Http\Resources\Akreditasi\DokumenAkreditasiResource;
use App\Services\DokumenAkreditasiService;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Symfony\Component\HttpFoundation\BinaryFileResponse;

class DokumenAkreditasiController extends Controller
{
    protected DokumenAkreditasiService $dokumenAkreditasiService;

    public function __construct(DokumenAkreditasiService $dokumenAkreditasiService)
    {
        $this->dokumenAkreditasiService = $dokumenAkreditasiService;
    }

    /**
     * Display a listing of dokumen akreditasi.
     */
    public function index(Request $request): JsonResponse
    {
        try {
            $filters = $request->only([
                'periode_akreditasi_id',
                'jenis_dokumen',
                'search'
            ]);
            $perPage = $request->get('per_page', 15);
            $sortBy = $request->get('sort_by', 'created_at');
            $sortOrder = $request->get('sort_order', 'desc');

            $dokumens = $this->dokumenAkreditasiService->getAllDokumenAkreditasi(
                $filters,
                $perPage,
                $sortBy,
                $sortOrder
            );

            return response()->json([
                'success' => true,
                'message' => 'Data dokumen akreditasi berhasil diambil',
                'data' => DokumenAkreditasiResource::collection($dokumens)->response()->getData(true)['data'],
                'meta' => [
                    'current_page' => $dokumens->currentPage(),
                    'from' => $dokumens->firstItem(),
                    'last_page' => $dokumens->lastPage(),
                    'per_page' => $dokumens->perPage(),
                    'to' => $dokumens->lastItem(),
                    'total' => $dokumens->total(),
                ],
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Gagal mengambil data dokumen',
                'error' => $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Upload dokumen akreditasi.
     */
    public function store(StoreDokumenAkreditasiRequest $request): JsonResponse
    {
        try {
            $dokumen = $this->dokumenAkreditasiService->createDokumenAkreditasi(
                $request->validated(),
                $request->file('file')
            );

            return response()->json([
                'success' => true,
                'message' => 'Dokumen berhasil diupload',
                'data' => new DokumenAkreditasiResource($dokumen),
            ], 201);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Gagal upload dokumen',
                'error' => $e->getMessage(),
            ], 422);
        }
    }

    /**
     * Display the specified dokumen.
     */
    public function show(string $id): JsonResponse
    {
        try {
            $dokumen = $this->dokumenAkreditasiService->getDokumenAkreditasiById($id);

            if (!$dokumen) {
                return response()->json([
                    'success' => false,
                    'message' => 'Dokumen tidak ditemukan',
                ], 404);
            }

            return response()->json([
                'success' => true,
                'message' => 'Detail dokumen berhasil diambil',
                'data' => new DokumenAkreditasiResource($dokumen),
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Dokumen tidak ditemukan',
                'error' => $e->getMessage(),
            ], 404);
        }
    }

    /**
     * Update dokumen metadata.
     */
    public function update(UpdateDokumenAkreditasiRequest $request, string $id): JsonResponse
    {
        try {
            $dokumen = $this->dokumenAkreditasiService->updateDokumenAkreditasi($id, $request->validated());

            return response()->json([
                'success' => true,
                'message' => 'Dokumen berhasil diupdate',
                'data' => new DokumenAkreditasiResource($dokumen),
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Gagal update dokumen',
                'error' => $e->getMessage(),
            ], 422);
        }
    }

    /**
     * Delete dokumen.
     */
    public function destroy(string $id): JsonResponse
    {
        try {
            $this->dokumenAkreditasiService->deleteDokumenAkreditasi($id);

            return response()->json([
                'success' => true,
                'message' => 'Dokumen berhasil dihapus',
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Gagal menghapus dokumen',
                'error' => $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Download dokumen.
     */
    public function download(string $id): BinaryFileResponse|JsonResponse
    {
        try {
            $result = $this->dokumenAkreditasiService->downloadDokumen($id);

            if (!$result) {
                return response()->json([
                    'success' => false,
                    'message' => 'File tidak ditemukan',
                ], 404);
            }

            return $result;
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Gagal download dokumen',
                'error' => $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Attach dokumen to butir akreditasi.
     */
    public function attachToButir(Request $request, string $id): JsonResponse
    {
        try {
            $butirIds = $request->get('butir_akreditasi_ids', []);

            if (!is_array($butirIds)) {
                return response()->json([
                    'success' => false,
                    'message' => 'butir_akreditasi_ids harus berupa array',
                ], 400);
            }

            $dokumen = $this->dokumenAkreditasiService->attachToButir($id, $butirIds);

            return response()->json([
                'success' => true,
                'message' => 'Dokumen berhasil dipetakan ke butir akreditasi',
                'data' => new DokumenAkreditasiResource($dokumen),
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Gagal memetakan dokumen',
                'error' => $e->getMessage(),
            ], 422);
        }
    }
}
