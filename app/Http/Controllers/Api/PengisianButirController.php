<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\Akreditasi\StorePengisianButirRequest;
use App\Http\Requests\Akreditasi\UpdatePengisianButirRequest;
use App\Http\Resources\Akreditasi\PengisianButirResource;
use App\Services\PengisianButirService;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;

class PengisianButirController extends Controller
{
    protected PengisianButirService $pengisianButirService;

    public function __construct(PengisianButirService $pengisianButirService)
    {
        $this->pengisianButirService = $pengisianButirService;
    }

    /**
     * Display a listing of pengisian butir.
     */
    public function index(Request $request): JsonResponse
    {
        try {
            $filters = $request->only([
                'periode_akreditasi_id',
                'status',
                'pic_user_id',
                'is_complete'
            ]);
            $perPage = $request->get('per_page', 15);
            $sortBy = $request->get('sort_by', 'created_at');
            $sortOrder = $request->get('sort_order', 'desc');

            $pengisians = $this->pengisianButirService->getAllPengisianButir(
                $filters,
                $perPage,
                $sortBy,
                $sortOrder
            );

            return response()->json([
                'success' => true,
                'message' => 'Data pengisian butir berhasil diambil',
                'data' => PengisianButirResource::collection($pengisians)->response()->getData(true)['data'],
                'meta' => [
                    'current_page' => $pengisians->currentPage(),
                    'from' => $pengisians->firstItem(),
                    'last_page' => $pengisians->lastPage(),
                    'per_page' => $pengisians->perPage(),
                    'to' => $pengisians->lastItem(),
                    'total' => $pengisians->total(),
                ],
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
     * Store or update pengisian butir (upsert).
     */
    public function store(StorePengisianButirRequest $request): JsonResponse
    {
        try {
            $pengisian = $this->pengisianButirService->createPengisianButir($request->validated());

            return response()->json([
                'success' => true,
                'message' => 'Pengisian butir berhasil disimpan',
                'data' => new PengisianButirResource($pengisian),
            ], 201);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Gagal menyimpan pengisian butir',
                'error' => $e->getMessage(),
            ], 422);
        }
    }

    /**
     * Display the specified pengisian butir.
     */
    public function show(string $id): JsonResponse
    {
        try {
            $pengisian = $this->pengisianButirService->getPengisianButirById($id);

            if (!$pengisian) {
                return response()->json([
                    'success' => false,
                    'message' => 'Pengisian butir tidak ditemukan',
                ], 404);
            }

            return response()->json([
                'success' => true,
                'message' => 'Detail pengisian butir berhasil diambil',
                'data' => new PengisianButirResource($pengisian),
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Pengisian butir tidak ditemukan',
                'error' => $e->getMessage(),
            ], 404);
        }
    }

    /**
     * Update the specified pengisian butir.
     */
    public function update(UpdatePengisianButirRequest $request, string $id): JsonResponse
    {
        try {
            $pengisian = $this->pengisianButirService->updatePengisianButir($id, $request->validated());

            return response()->json([
                'success' => true,
                'message' => 'Pengisian butir berhasil diupdate',
                'data' => new PengisianButirResource($pengisian),
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Gagal mengupdate pengisian butir',
                'error' => $e->getMessage(),
            ], 422);
        }
    }

    /**
     * Remove the specified pengisian butir.
     */
    public function destroy(string $id): JsonResponse
    {
        try {
            $this->pengisianButirService->deletePengisianButir($id);

            return response()->json([
                'success' => true,
                'message' => 'Pengisian butir berhasil dihapus',
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Gagal menghapus pengisian butir',
                'error' => $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Submit pengisian butir for review.
     */
    public function submit(string $id): JsonResponse
    {
        try {
            $pengisian = $this->pengisianButirService->submitPengisianButir($id);

            return response()->json([
                'success' => true,
                'message' => 'Pengisian butir berhasil disubmit untuk review',
                'data' => new PengisianButirResource($pengisian),
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Gagal submit pengisian butir',
                'error' => $e->getMessage(),
            ], 422);
        }
    }

    /**
     * Approve pengisian butir.
     */
    public function approve(Request $request, string $id): JsonResponse
    {
        try {
            $pengisian = $this->pengisianButirService->approvePengisianButir(
                $id,
                $request->get('review_notes')
            );

            return response()->json([
                'success' => true,
                'message' => 'Pengisian butir berhasil diapprove',
                'data' => new PengisianButirResource($pengisian),
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Gagal approve pengisian butir',
                'error' => $e->getMessage(),
            ], 422);
        }
    }

    /**
     * Request revision for pengisian butir.
     */
    public function revision(Request $request, string $id): JsonResponse
    {
        try {
            $reviewNotes = $request->get('review_notes');

            if (!$reviewNotes) {
                return response()->json([
                    'success' => false,
                    'message' => 'Review notes wajib diisi untuk request revisi',
                ], 400);
            }

            $pengisian = $this->pengisianButirService->requestRevisionPengisianButir($id, $reviewNotes);

            return response()->json([
                'success' => true,
                'message' => 'Request revisi berhasil dikirim',
                'data' => new PengisianButirResource($pengisian),
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Gagal request revisi',
                'error' => $e->getMessage(),
            ], 422);
        }
    }

    /**
     * Get summary untuk periode tertentu.
     */
    public function summary(string $periodeId): JsonResponse
    {
        try {
            $summary = $this->pengisianButirService->getSummaryByPeriode($periodeId);

            return response()->json([
                'success' => true,
                'message' => 'Summary berhasil diambil',
                'data' => $summary,
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Gagal mengambil summary',
                'error' => $e->getMessage(),
            ], 500);
        }
    }
}
