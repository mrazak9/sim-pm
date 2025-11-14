<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\Akreditasi\StoreButirAkreditasiRequest;
use App\Http\Requests\Akreditasi\UpdateButirAkreditasiRequest;
use App\Http\Resources\Akreditasi\ButirAkreditasiResource;
use App\Services\ButirAkreditasiService;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;

class ButirAkreditasiController extends Controller
{
    protected ButirAkreditasiService $butirAkreditasiService;

    public function __construct(ButirAkreditasiService $butirAkreditasiService)
    {
        $this->butirAkreditasiService = $butirAkreditasiService;
    }

    /**
     * Display a listing of butir akreditasi.
     */
    public function index(Request $request): JsonResponse
    {
        try {
            $filters = $request->only([
                'instrumen',
                'kategori',
                'parent_id',
                'parent_only',
                'is_mandatory',
                'search'
            ]);
            $perPage = $request->get('per_page', 15);
            $sortBy = $request->get('sort_by', 'urutan');
            $sortOrder = $request->get('sort_order', 'asc');

            $butirs = $this->butirAkreditasiService->getAllButirAkreditasi(
                $filters,
                $perPage,
                $sortBy,
                $sortOrder
            );

            // Handle pagination or all
            if ($perPage !== 'all' && method_exists($butirs, 'currentPage')) {
                return response()->json([
                    'success' => true,
                    'message' => 'Data butir akreditasi berhasil diambil',
                    'data' => ButirAkreditasiResource::collection($butirs)->response()->getData(true)['data'],
                    'meta' => [
                        'current_page' => $butirs->currentPage(),
                        'from' => $butirs->firstItem(),
                        'last_page' => $butirs->lastPage(),
                        'per_page' => $butirs->perPage(),
                        'to' => $butirs->lastItem(),
                        'total' => $butirs->total(),
                    ],
                ]);
            }

            return response()->json([
                'success' => true,
                'message' => 'Data butir akreditasi berhasil diambil',
                'data' => ButirAkreditasiResource::collection($butirs),
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Gagal mengambil data butir akreditasi',
                'error' => $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Store a newly created butir akreditasi.
     */
    public function store(StoreButirAkreditasiRequest $request): JsonResponse
    {
        try {
            $butir = $this->butirAkreditasiService->createButirAkreditasi($request->validated());

            return response()->json([
                'success' => true,
                'message' => 'Butir akreditasi berhasil dibuat',
                'data' => new ButirAkreditasiResource($butir),
            ], 201);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Gagal membuat butir akreditasi',
                'error' => $e->getMessage(),
            ], 422);
        }
    }

    /**
     * Display the specified butir akreditasi.
     */
    public function show(string $id): JsonResponse
    {
        try {
            $butir = $this->butirAkreditasiService->getButirAkreditasiById($id);

            if (!$butir) {
                return response()->json([
                    'success' => false,
                    'message' => 'Butir akreditasi tidak ditemukan',
                ], 404);
            }

            return response()->json([
                'success' => true,
                'message' => 'Detail butir akreditasi berhasil diambil',
                'data' => new ButirAkreditasiResource($butir),
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Butir akreditasi tidak ditemukan',
                'error' => $e->getMessage(),
            ], 404);
        }
    }

    /**
     * Update the specified butir akreditasi.
     */
    public function update(UpdateButirAkreditasiRequest $request, string $id): JsonResponse
    {
        try {
            $butir = $this->butirAkreditasiService->updateButirAkreditasi($id, $request->validated());

            return response()->json([
                'success' => true,
                'message' => 'Butir akreditasi berhasil diupdate',
                'data' => new ButirAkreditasiResource($butir),
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Gagal mengupdate butir akreditasi',
                'error' => $e->getMessage(),
            ], 422);
        }
    }

    /**
     * Remove the specified butir akreditasi.
     */
    public function destroy(string $id): JsonResponse
    {
        try {
            $this->butirAkreditasiService->deleteButirAkreditasi($id);

            return response()->json([
                'success' => true,
                'message' => 'Butir akreditasi berhasil dihapus',
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Gagal menghapus butir akreditasi',
                'error' => $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Get butir akreditasi grouped by kategori.
     */
    public function byKategori(Request $request): JsonResponse
    {
        try {
            $instrumen = $request->get('instrumen', '4.0');
            $data = $this->butirAkreditasiService->getButirByKategori($instrumen);

            return response()->json([
                'success' => true,
                'message' => 'Data butir akreditasi per kategori berhasil diambil',
                'data' => $data,
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Gagal mengambil data',
                'error' => $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Get butir akreditasi by instrumen.
     */
    public function byInstrumen(Request $request): JsonResponse
    {
        try {
            $instrumen = $request->get('instrumen', '4.0');
            $butirs = $this->butirAkreditasiService->getButirByInstrumen($instrumen);

            return response()->json([
                'success' => true,
                'message' => 'Data butir akreditasi berhasil diambil',
                'data' => ButirAkreditasiResource::collection($butirs),
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Gagal mengambil data',
                'error' => $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Get list of kategori for specific instrumen.
     */
    public function kategori(Request $request): JsonResponse
    {
        try {
            $instrumen = $request->get('instrumen', '4.0');
            $kategoris = $this->butirAkreditasiService->getKategoriList($instrumen);

            return response()->json([
                'success' => true,
                'message' => 'Daftar kategori berhasil diambil',
                'data' => $kategoris,
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Gagal mengambil daftar kategori',
                'error' => $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Get list of instrumen available.
     */
    public function instrumen(): JsonResponse
    {
        try {
            $instrumens = $this->butirAkreditasiService->getInstrumenList();

            return response()->json([
                'success' => true,
                'message' => 'Daftar instrumen berhasil diambil',
                'data' => $instrumens,
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Gagal mengambil daftar instrumen',
                'error' => $e->getMessage(),
            ], 500);
        }
    }
}
