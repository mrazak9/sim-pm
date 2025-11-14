<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\PengisianButir;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;

class PengisianButirController extends Controller
{
    /**
     * Display a listing of pengisian butir.
     */
    public function index(Request $request)
    {
        try {
            $query = PengisianButir::with([
                'periodeAkreditasi',
                'butirAkreditasi',
                'picUser',
                'reviewer',
            ]);

            // Filter by periode
            if ($request->has('periode_akreditasi_id')) {
                $query->where('periode_akreditasi_id', $request->periode_akreditasi_id);
            }

            // Filter by status
            if ($request->has('status')) {
                $query->where('status', $request->status);
            }

            // Filter by PIC
            if ($request->has('pic_user_id')) {
                $query->where('pic_user_id', $request->pic_user_id);
            }

            // Filter by completion
            if ($request->has('is_complete')) {
                $query->where('is_complete', $request->is_complete === 'true');
            }

            // Sorting
            $sortBy = $request->get('sort_by', 'created_at');
            $sortOrder = $request->get('sort_order', 'desc');
            $query->orderBy($sortBy, $sortOrder);

            // Pagination
            $perPage = $request->get('per_page', 15);
            $pengisians = $query->paginate($perPage);

            return response()->json([
                'success' => true,
                'message' => 'Data pengisian butir berhasil diambil',
                'data' => $pengisians,
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
    public function store(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'periode_akreditasi_id' => 'required|exists:periode_akreditasis,id',
            'butir_akreditasi_id' => 'required|exists:butir_akreditasis,id',
            'konten' => 'nullable|string',
            'files' => 'nullable|array',
            'notes' => 'nullable|string',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => 'Validasi gagal',
                'errors' => $validator->errors(),
            ], 422);
        }

        try {
            DB::beginTransaction();

            // Upsert based on periode & butir
            $pengisian = PengisianButir::updateOrCreate(
                [
                    'periode_akreditasi_id' => $request->periode_akreditasi_id,
                    'butir_akreditasi_id' => $request->butir_akreditasi_id,
                ],
                [
                    'pic_user_id' => Auth::id(),
                    'konten' => $request->konten,
                    'konten_plain' => strip_tags($request->konten ?? ''),
                    'files' => $request->files,
                    'notes' => $request->notes,
                    'status' => 'draft',
                ]
            );

            DB::commit();

            return response()->json([
                'success' => true,
                'message' => 'Pengisian butir berhasil disimpan',
                'data' => $pengisian->load(['butirAkreditasi', 'picUser']),
            ]);
        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json([
                'success' => false,
                'message' => 'Gagal menyimpan pengisian butir',
                'error' => $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Display the specified pengisian butir.
     */
    public function show($id)
    {
        try {
            $pengisian = PengisianButir::with([
                'periodeAkreditasi',
                'butirAkreditasi',
                'picUser',
                'reviewer',
            ])->findOrFail($id);

            return response()->json([
                'success' => true,
                'message' => 'Detail pengisian butir berhasil diambil',
                'data' => $pengisian,
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
    public function update(Request $request, $id)
    {
        $validator = Validator::make($request->all(), [
            'konten' => 'nullable|string',
            'files' => 'nullable|array',
            'notes' => 'nullable|string',
            'is_complete' => 'nullable|boolean',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => 'Validasi gagal',
                'errors' => $validator->errors(),
            ], 422);
        }

        try {
            $pengisian = PengisianButir::findOrFail($id);

            $pengisian->update([
                'konten' => $request->konten ?? $pengisian->konten,
                'konten_plain' => strip_tags($request->konten ?? $pengisian->konten ?? ''),
                'files' => $request->files ?? $pengisian->files,
                'notes' => $request->notes ?? $pengisian->notes,
                'is_complete' => $request->is_complete ?? $pengisian->is_complete,
            ]);

            return response()->json([
                'success' => true,
                'message' => 'Pengisian butir berhasil diupdate',
                'data' => $pengisian->load(['butirAkreditasi', 'picUser']),
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Gagal mengupdate pengisian butir',
                'error' => $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Submit pengisian butir for review.
     */
    public function submit($id)
    {
        try {
            $pengisian = PengisianButir::findOrFail($id);

            if ($pengisian->status !== 'draft') {
                return response()->json([
                    'success' => false,
                    'message' => 'Hanya pengisian dengan status draft yang bisa disubmit',
                ], 400);
            }

            $pengisian->update([
                'status' => 'submitted',
                'is_complete' => true,
            ]);

            return response()->json([
                'success' => true,
                'message' => 'Pengisian butir berhasil disubmit untuk review',
                'data' => $pengisian,
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Gagal submit pengisian butir',
                'error' => $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Approve pengisian butir.
     */
    public function approve(Request $request, $id)
    {
        $validator = Validator::make($request->all(), [
            'review_notes' => 'nullable|string',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => 'Validasi gagal',
                'errors' => $validator->errors(),
            ], 422);
        }

        try {
            $pengisian = PengisianButir::findOrFail($id);

            $pengisian->update([
                'status' => 'approved',
                'reviewed_by' => Auth::id(),
                'reviewed_at' => now(),
                'review_notes' => $request->review_notes,
            ]);

            return response()->json([
                'success' => true,
                'message' => 'Pengisian butir berhasil diapprove',
                'data' => $pengisian->load(['reviewer']),
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Gagal approve pengisian butir',
                'error' => $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Request revision for pengisian butir.
     */
    public function revision(Request $request, $id)
    {
        $validator = Validator::make($request->all(), [
            'review_notes' => 'required|string',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => 'Validasi gagal',
                'errors' => $validator->errors(),
            ], 422);
        }

        try {
            $pengisian = PengisianButir::findOrFail($id);

            $pengisian->update([
                'status' => 'revision',
                'reviewed_by' => Auth::id(),
                'reviewed_at' => now(),
                'review_notes' => $request->review_notes,
            ]);

            return response()->json([
                'success' => true,
                'message' => 'Request revisi berhasil dikirim',
                'data' => $pengisian->load(['reviewer']),
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Gagal request revisi',
                'error' => $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Delete pengisian butir.
     */
    public function destroy($id)
    {
        try {
            $pengisian = PengisianButir::findOrFail($id);
            $pengisian->delete();

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
     * Get summary untuk periode tertentu.
     */
    public function summary($periodeId)
    {
        try {
            $pengisians = PengisianButir::where('periode_akreditasi_id', $periodeId)->get();

            $summary = [
                'total' => $pengisians->count(),
                'completed' => $pengisians->where('is_complete', true)->count(),
                'draft' => $pengisians->where('status', 'draft')->count(),
                'submitted' => $pengisians->where('status', 'submitted')->count(),
                'review' => $pengisians->where('status', 'review')->count(),
                'revision' => $pengisians->where('status', 'revision')->count(),
                'approved' => $pengisians->where('status', 'approved')->count(),
            ];

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
