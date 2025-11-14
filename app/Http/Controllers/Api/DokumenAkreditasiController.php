<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\DokumenAkreditasi;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Validator;

class DokumenAkreditasiController extends Controller
{
    /**
     * Display a listing of dokumen akreditasi.
     */
    public function index(Request $request)
    {
        try {
            $query = DokumenAkreditasi::with(['periodeAkreditasi', 'uploader']);

            // Filter by periode
            if ($request->has('periode_akreditasi_id')) {
                $query->where('periode_akreditasi_id', $request->periode_akreditasi_id);
            }

            // Filter by jenis_dokumen
            if ($request->has('jenis_dokumen')) {
                $query->where('jenis_dokumen', $request->jenis_dokumen);
            }

            // Search
            if ($request->has('search')) {
                $query->where(function ($q) use ($request) {
                    $q->where('nama_dokumen', 'like', '%' . $request->search . '%')
                      ->orWhere('nomor_dokumen', 'like', '%' . $request->search . '%')
                      ->orWhere('deskripsi', 'like', '%' . $request->search . '%');
                });
            }

            // Sorting
            $sortBy = $request->get('sort_by', 'created_at');
            $sortOrder = $request->get('sort_order', 'desc');
            $query->orderBy($sortBy, $sortOrder);

            // Pagination
            $perPage = $request->get('per_page', 15);
            $dokumens = $query->paginate($perPage);

            return response()->json([
                'success' => true,
                'message' => 'Data dokumen akreditasi berhasil diambil',
                'data' => $dokumens,
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
    public function store(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'periode_akreditasi_id' => 'required|exists:periode_akreditasis,id',
            'nama_dokumen' => 'required|string|max:255',
            'nomor_dokumen' => 'nullable|string|max:100',
            'jenis_dokumen' => 'required|in:kebijakan,pedoman,manual,sop,formulir,instruksi_kerja,laporan,sertifikat,sk,lainnya',
            'deskripsi' => 'nullable|string',
            'file' => 'required|file|mimes:pdf,doc,docx,xls,xlsx,ppt,pptx,jpg,jpeg,png|max:10240', // Max 10MB
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => 'Validasi gagal',
                'errors' => $validator->errors(),
            ], 422);
        }

        try {
            // Upload file
            $file = $request->file('file');
            $path = $file->store('dokumen-akreditasi', 'public');

            $dokumen = DokumenAkreditasi::create([
                'periode_akreditasi_id' => $request->periode_akreditasi_id,
                'nama_dokumen' => $request->nama_dokumen,
                'nomor_dokumen' => $request->nomor_dokumen,
                'jenis_dokumen' => $request->jenis_dokumen,
                'deskripsi' => $request->deskripsi,
                'file_path' => $path,
                'file_type' => $file->getClientOriginalExtension(),
                'file_size' => $file->getSize(),
                'uploaded_by' => Auth::id(),
            ]);

            return response()->json([
                'success' => true,
                'message' => 'Dokumen berhasil diupload',
                'data' => $dokumen->load(['uploader']),
            ], 201);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Gagal upload dokumen',
                'error' => $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Display the specified dokumen.
     */
    public function show($id)
    {
        try {
            $dokumen = DokumenAkreditasi::with(['periodeAkreditasi', 'uploader', 'butirAkreditasis'])
                ->findOrFail($id);

            return response()->json([
                'success' => true,
                'message' => 'Detail dokumen berhasil diambil',
                'data' => $dokumen,
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
     * Download dokumen.
     */
    public function download($id)
    {
        try {
            $dokumen = DokumenAkreditasi::findOrFail($id);

            if (!Storage::disk('public')->exists($dokumen->file_path)) {
                return response()->json([
                    'success' => false,
                    'message' => 'File tidak ditemukan',
                ], 404);
            }

            return Storage::disk('public')->download($dokumen->file_path, $dokumen->nama_dokumen);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Gagal download dokumen',
                'error' => $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Update dokumen metadata.
     */
    public function update(Request $request, $id)
    {
        $validator = Validator::make($request->all(), [
            'nama_dokumen' => 'sometimes|required|string|max:255',
            'nomor_dokumen' => 'nullable|string|max:100',
            'jenis_dokumen' => 'sometimes|required|in:kebijakan,pedoman,manual,sop,formulir,instruksi_kerja,laporan,sertifikat,sk,lainnya',
            'deskripsi' => 'nullable|string',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => 'Validasi gagal',
                'errors' => $validator->errors(),
            ], 422);
        }

        try {
            $dokumen = DokumenAkreditasi::findOrFail($id);
            $dokumen->update($request->only([
                'nama_dokumen',
                'nomor_dokumen',
                'jenis_dokumen',
                'deskripsi',
            ]));

            return response()->json([
                'success' => true,
                'message' => 'Dokumen berhasil diupdate',
                'data' => $dokumen,
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Gagal update dokumen',
                'error' => $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Delete dokumen.
     */
    public function destroy($id)
    {
        try {
            $dokumen = DokumenAkreditasi::findOrFail($id);
            $dokumen->delete(); // Auto delete file via model boot

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
     * Attach dokumen to butir akreditasi.
     */
    public function attachToButir(Request $request, $id)
    {
        $validator = Validator::make($request->all(), [
            'butir_akreditasi_ids' => 'required|array',
            'butir_akreditasi_ids.*' => 'exists:butir_akreditasis,id',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => 'Validasi gagal',
                'errors' => $validator->errors(),
            ], 422);
        }

        try {
            $dokumen = DokumenAkreditasi::findOrFail($id);
            $dokumen->butirAkreditasis()->sync($request->butir_akreditasi_ids);

            return response()->json([
                'success' => true,
                'message' => 'Dokumen berhasil dipetakan ke butir akreditasi',
                'data' => $dokumen->load(['butirAkreditasis']),
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Gagal memetakan dokumen',
                'error' => $e->getMessage(),
            ], 500);
        }
    }
}
