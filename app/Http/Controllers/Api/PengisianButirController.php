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

            // Handle pagination or all
            if ($perPage !== 'all' && method_exists($pengisians, 'currentPage')) {
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
            }

            return response()->json([
                'success' => true,
                'message' => 'Data pengisian butir berhasil diambil',
                'data' => PengisianButirResource::collection($pengisians),
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

            // Load butir akreditasi relation to get template metadata
            $pengisian->load('butirAkreditasi', 'periodeAkreditasi', 'picUser', 'reviewer');

            // Debug logging
            \Log::info('=== PengisianButirController::show ===', [
                'id' => $id,
                'has_form_data' => !empty($pengisian->form_data),
                'form_data_type' => gettype($pengisian->form_data),
                'form_data_sample' => is_string($pengisian->form_data) ? substr($pengisian->form_data, 0, 100) : $pengisian->form_data,
                'has_template' => !empty($pengisian->butirAkreditasi->metadata['form_config']),
                'konten_length' => strlen($pengisian->konten ?? ''),
            ]);

            return response()->json([
                'success' => true,
                'message' => 'Detail pengisian butir berhasil diambil',
                'data' => new PengisianButirResource($pengisian),
            ]);
        } catch (\Exception $e) {
            \Log::error('Error in show PengisianButir', [
                'id' => $id,
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);
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
            $validatedData = $request->validated();

            // Debug logging
            \Log::info('=== PengisianButirController::update ===', [
                'id' => $id,
                'has_form_data_in_request' => isset($validatedData['form_data']),
                'form_data_type' => isset($validatedData['form_data']) ? gettype($validatedData['form_data']) : 'not set',
                'form_data_is_null' => isset($validatedData['form_data']) && $validatedData['form_data'] === null,
                'konten_length' => strlen($validatedData['konten'] ?? ''),
                'completion_percentage' => $validatedData['completion_percentage'] ?? 'not set',
                'is_complete' => $validatedData['is_complete'] ?? 'not set',
            ]);

            $pengisian = $this->pengisianButirService->updatePengisianButir($id, $validatedData);

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
            $pengisian = $this->pengisianButirService->approve(
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

            $pengisian = $this->pengisianButirService->reject($id, $reviewNotes);

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

    /**
     * Check lock status of pengisian butir.
     */
    public function checkLockStatus(string $id): JsonResponse
    {
        try {
            $lockStatus = $this->pengisianButirService->checkLockStatus($id);

            return response()->json([
                'success' => true,
                'message' => 'Lock status berhasil diambil',
                'data' => $lockStatus,
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Gagal mengambil lock status',
                'error' => $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Acquire edit lock for pengisian butir
     *
     * @param string $id
     * @return JsonResponse
     */
    public function acquireLock(string $id): JsonResponse
    {
        try {
            $lock = $this->pengisianButirService->acquireLock((int) $id);

            return response()->json([
                'success' => true,
                'message' => 'Edit lock berhasil diambil',
                'data' => $lock,
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Gagal mengambil edit lock',
                'error' => $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Release edit lock
     *
     * @param string $id
     * @return JsonResponse
     */
    public function releaseLock(string $id): JsonResponse
    {
        try {
            $this->pengisianButirService->releaseLock((int) $id);

            return response()->json([
                'success' => true,
                'message' => 'Edit lock berhasil dilepas',
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Gagal melepas edit lock',
                'error' => $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Extend edit lock duration
     *
     * @param string $id
     * @return JsonResponse
     */
    public function extendLock(string $id): JsonResponse
    {
        try {
            $lock = $this->pengisianButirService->extendLock((int) $id);

            return response()->json([
                'success' => true,
                'message' => 'Edit lock berhasil diperpanjang',
                'data' => $lock,
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Gagal memperpanjang edit lock',
                'error' => $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Check edit lock status
     *
     * @param string $id
     * @return JsonResponse
     */
    public function checkEditLock(string $id): JsonResponse
    {
        try {
            $lockStatus = $this->pengisianButirService->checkEditLock((int) $id);

            return response()->json([
                'success' => true,
                'message' => 'Edit lock status berhasil diambil',
                'data' => $lockStatus,
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Gagal mengambil edit lock status',
                'error' => $e->getMessage(),
            ], 500);
        }
    }
}
