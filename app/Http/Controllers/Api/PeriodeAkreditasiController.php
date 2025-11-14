<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\PeriodeAkreditasi;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

class PeriodeAkreditasiController extends Controller
{
    /**
     * Display a listing of periode akreditasi.
     */
    public function index(Request $request)
    {
        try {
            $query = PeriodeAkreditasi::with(['unitKerja', 'programStudi']);

            // Filter by jenis_akreditasi
            if ($request->has('jenis_akreditasi')) {
                $query->where('jenis_akreditasi', $request->jenis_akreditasi);
            }

            // Filter by lembaga
            if ($request->has('lembaga')) {
                $query->where('lembaga', $request->lembaga);
            }

            // Filter by status
            if ($request->has('status')) {
                $query->where('status', $request->status);
            }

            // Filter by program_studi_id
            if ($request->has('program_studi_id')) {
                $query->where('program_studi_id', $request->program_studi_id);
            }

            // Search by nama
            if ($request->has('search')) {
                $query->where('nama', 'like', '%' . $request->search . '%');
            }

            // Sorting
            $sortBy = $request->get('sort_by', 'created_at');
            $sortOrder = $request->get('sort_order', 'desc');
            $query->orderBy($sortBy, $sortOrder);

            // Pagination
            $perPage = $request->get('per_page', 15);
            $periodes = $query->paginate($perPage);

            return response()->json([
                'success' => true,
                'message' => 'Data periode akreditasi berhasil diambil',
                'data' => $periodes,
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
    public function store(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'nama' => 'required|string|max:255',
            'jenis_akreditasi' => 'required|in:institusi,program_studi',
            'lembaga' => 'required|in:BAN-PT,LAM,Internasional',
            'instrumen' => 'nullable|string|max:50',
            'jenjang' => 'nullable|string|max:10',
            'unit_kerja_id' => 'nullable|exists:unit_kerjas,id',
            'program_studi_id' => 'nullable|exists:program_studis,id',
            'tanggal_mulai' => 'nullable|date',
            'deadline_pengumpulan' => 'nullable|date|after:tanggal_mulai',
            'jadwal_visitasi' => 'nullable|date|after:deadline_pengumpulan',
            'tanggal_berakhir' => 'nullable|date|after:jadwal_visitasi',
            'status' => 'nullable|in:persiapan,pengisian,review,submit,visitasi,selesai',
            'keterangan' => 'nullable|string',
            'metadata' => 'nullable|array',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => 'Validasi gagal',
                'errors' => $validator->errors(),
            ], 422);
        }

        try {
            // Clean up empty date fields
            $data = $request->all();
            $dateFields = ['tanggal_mulai', 'deadline_pengumpulan', 'jadwal_visitasi', 'tanggal_berakhir'];

            foreach ($dateFields as $field) {
                if (isset($data[$field]) && empty($data[$field])) {
                    $data[$field] = null;
                }
            }

            $periode = PeriodeAkreditasi::create($data);

            return response()->json([
                'success' => true,
                'message' => 'Periode akreditasi berhasil dibuat',
                'data' => $periode->load(['unitKerja', 'programStudi']),
            ], 201);
        } catch (\Exception $e) {
            \Log::error('Error creating periode akreditasi: ' . $e->getMessage(), [
                'request' => $request->all(),
                'trace' => $e->getTraceAsString()
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Gagal membuat periode akreditasi',
                'error' => $e->getMessage(),
                'details' => config('app.debug') ? $e->getTraceAsString() : null,
            ], 500);
        }
    }

    /**
     * Display the specified periode akreditasi.
     */
    public function show($id)
    {
        try {
            $periode = PeriodeAkreditasi::with([
                'unitKerja',
                'programStudi',
                'pengisianButirs.butirAkreditasi',
                'dokumenAkreditasis',
            ])->findOrFail($id);

            // Add computed attributes
            $periode->progress_persentase = $periode->progress_persentase;
            $periode->is_expired = $periode->is_expired;
            $periode->sisa_hari = $periode->sisa_hari;

            return response()->json([
                'success' => true,
                'message' => 'Detail periode akreditasi berhasil diambil',
                'data' => $periode,
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
    public function update(Request $request, $id)
    {
        $validator = Validator::make($request->all(), [
            'nama' => 'sometimes|required|string|max:255',
            'jenis_akreditasi' => 'sometimes|required|in:institusi,program_studi',
            'lembaga' => 'sometimes|required|in:BAN-PT,LAM,Internasional',
            'instrumen' => 'nullable|string|max:50',
            'jenjang' => 'nullable|string|max:10',
            'unit_kerja_id' => 'nullable|exists:unit_kerjas,id',
            'program_studi_id' => 'nullable|exists:program_studis,id',
            'tanggal_mulai' => 'nullable|date',
            'deadline_pengumpulan' => 'nullable|date',
            'jadwal_visitasi' => 'nullable|date',
            'tanggal_berakhir' => 'nullable|date',
            'status' => 'nullable|in:persiapan,pengisian,review,submit,visitasi,selesai',
            'keterangan' => 'nullable|string',
            'metadata' => 'nullable|array',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => 'Validasi gagal',
                'errors' => $validator->errors(),
            ], 422);
        }

        try {
            $periode = PeriodeAkreditasi::findOrFail($id);

            // Clean up empty date fields
            $data = $request->all();
            $dateFields = ['tanggal_mulai', 'deadline_pengumpulan', 'jadwal_visitasi', 'tanggal_berakhir'];

            foreach ($dateFields as $field) {
                if (isset($data[$field]) && empty($data[$field])) {
                    $data[$field] = null;
                }
            }

            $periode->update($data);

            return response()->json([
                'success' => true,
                'message' => 'Periode akreditasi berhasil diupdate',
                'data' => $periode->load(['unitKerja', 'programStudi']),
            ]);
        } catch (\Exception $e) {
            \Log::error('Error updating periode akreditasi: ' . $e->getMessage(), [
                'id' => $id,
                'request' => $request->all(),
                'trace' => $e->getTraceAsString()
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Gagal mengupdate periode akreditasi',
                'error' => $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Remove the specified periode akreditasi.
     */
    public function destroy($id)
    {
        try {
            $periode = PeriodeAkreditasi::findOrFail($id);
            $periode->delete();

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
    public function statistics($id)
    {
        try {
            $periode = PeriodeAkreditasi::with(['pengisianButirs'])->findOrFail($id);

            $totalButir = $periode->pengisianButirs->count();
            $butirSelesai = $periode->pengisianButirs->where('is_complete', true)->count();
            $butirDraft = $periode->pengisianButirs->where('status', 'draft')->count();
            $butirReview = $periode->pengisianButirs->where('status', 'review')->count();
            $butirApproved = $periode->pengisianButirs->where('status', 'approved')->count();

            $statistics = [
                'total_butir' => $totalButir,
                'butir_selesai' => $butirSelesai,
                'butir_belum_selesai' => $totalButir - $butirSelesai,
                'progress_persentase' => $totalButir > 0 ? round(($butirSelesai / $totalButir) * 100, 2) : 0,
                'status_breakdown' => [
                    'draft' => $butirDraft,
                    'submitted' => $periode->pengisianButirs->where('status', 'submitted')->count(),
                    'review' => $butirReview,
                    'revision' => $periode->pengisianButirs->where('status', 'revision')->count(),
                    'approved' => $butirApproved,
                ],
                'dokumen_count' => $periode->dokumenAkreditasis()->count(),
                'sisa_hari' => $periode->sisa_hari,
                'is_expired' => $periode->is_expired,
            ];

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
