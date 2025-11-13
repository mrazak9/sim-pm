<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\IKUProgress;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Storage;

class IKUProgressController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {
        $query = IKUProgress::with(['target.iku', 'target.tahunAkademik', 'target.unitKerja', 'target.programStudi', 'creator']);

        // Filter by IKU Target
        if ($request->has('iku_target_id')) {
            $query->where('iku_target_id', $request->iku_target_id);
        }

        // Filter by date range
        if ($request->has('start_date') && $request->has('end_date')) {
            $query->byDateRange($request->start_date, $request->end_date);
        }

        // Filter by recent days
        if ($request->has('recent_days')) {
            $query->recent($request->recent_days);
        }

        // Search
        if ($request->has('search')) {
            $search = $request->search;
            $query->where(function($q) use ($search) {
                $q->where('keterangan', 'LIKE', "%{$search}%")
                  ->orWhereHas('target.iku', function($subQ) use ($search) {
                      $subQ->where('nama_iku', 'LIKE', "%{$search}%")
                           ->orWhere('kode_iku', 'LIKE', "%{$search}%");
                  });
            });
        }

        $progress = $query->orderBy('tanggal_capaian', 'desc')
                         ->paginate($request->get('per_page', 15));

        return response()->json([
            'success' => true,
            'data' => $progress,
        ]);
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'iku_target_id' => 'required|exists:iku_targets,id',
            'tanggal_capaian' => 'required|date',
            'nilai_capaian' => 'required|numeric|min:0',
            'persentase_capaian' => 'nullable|numeric|min:0|max:100',
            'keterangan' => 'nullable|string',
            'bukti_dokumen' => 'nullable|file|mimes:pdf,doc,docx,xls,xlsx,jpg,jpeg,png|max:5120',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => 'Validation error',
                'errors' => $validator->errors(),
            ], 422);
        }

        $data = $request->except('bukti_dokumen');
        $data['created_by'] = auth()->id() ?? 1; // Default to user ID 1 if not authenticated

        // Handle file upload
        if ($request->hasFile('bukti_dokumen')) {
            $file = $request->file('bukti_dokumen');
            $filename = time() . '_' . $file->getClientOriginalName();
            $path = $file->storeAs('iku_progress_documents', $filename, 'public');
            $data['bukti_dokumen'] = $path;
        }

        $progress = IKUProgress::create($data);

        return response()->json([
            'success' => true,
            'message' => 'IKU Progress created successfully',
            'data' => $progress->load(['target.iku', 'creator']),
        ], 201);
    }

    /**
     * Display the specified resource.
     */
    public function show(string $id)
    {
        $progress = IKUProgress::with([
            'target.iku',
            'target.tahunAkademik',
            'target.unitKerja',
            'target.programStudi',
            'creator'
        ])->find($id);

        if (!$progress) {
            return response()->json([
                'success' => false,
                'message' => 'IKU Progress not found',
            ], 404);
        }

        return response()->json([
            'success' => true,
            'data' => $progress,
        ]);
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, string $id)
    {
        $progress = IKUProgress::find($id);

        if (!$progress) {
            return response()->json([
                'success' => false,
                'message' => 'IKU Progress not found',
            ], 404);
        }

        $validator = Validator::make($request->all(), [
            'iku_target_id' => 'required|exists:iku_targets,id',
            'tanggal_capaian' => 'required|date',
            'nilai_capaian' => 'required|numeric|min:0',
            'persentase_capaian' => 'nullable|numeric|min:0|max:100',
            'keterangan' => 'nullable|string',
            'bukti_dokumen' => 'nullable|file|mimes:pdf,doc,docx,xls,xlsx,jpg,jpeg,png|max:5120',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => 'Validation error',
                'errors' => $validator->errors(),
            ], 422);
        }

        $data = $request->except('bukti_dokumen');

        // Handle file upload
        if ($request->hasFile('bukti_dokumen')) {
            // Delete old file if exists
            if ($progress->bukti_dokumen && Storage::disk('public')->exists($progress->bukti_dokumen)) {
                Storage::disk('public')->delete($progress->bukti_dokumen);
            }

            $file = $request->file('bukti_dokumen');
            $filename = time() . '_' . $file->getClientOriginalName();
            $path = $file->storeAs('iku_progress_documents', $filename, 'public');
            $data['bukti_dokumen'] = $path;
        }

        $progress->update($data);

        return response()->json([
            'success' => true,
            'message' => 'IKU Progress updated successfully',
            'data' => $progress->load(['target.iku', 'creator']),
        ]);
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(string $id)
    {
        $progress = IKUProgress::find($id);

        if (!$progress) {
            return response()->json([
                'success' => false,
                'message' => 'IKU Progress not found',
            ], 404);
        }

        // Delete associated file if exists
        if ($progress->bukti_dokumen && Storage::disk('public')->exists($progress->bukti_dokumen)) {
            Storage::disk('public')->delete($progress->bukti_dokumen);
        }

        $progress->delete();

        return response()->json([
            'success' => true,
            'message' => 'IKU Progress deleted successfully',
        ]);
    }

    /**
     * Download progress document
     */
    public function downloadDocument(string $id)
    {
        $progress = IKUProgress::find($id);

        if (!$progress) {
            return response()->json([
                'success' => false,
                'message' => 'IKU Progress not found',
            ], 404);
        }

        if (!$progress->bukti_dokumen) {
            return response()->json([
                'success' => false,
                'message' => 'No document available',
            ], 404);
        }

        if (!Storage::disk('public')->exists($progress->bukti_dokumen)) {
            return response()->json([
                'success' => false,
                'message' => 'Document file not found',
            ], 404);
        }

        return Storage::disk('public')->download($progress->bukti_dokumen);
    }

    /**
     * Get progress summary by target
     */
    public function summaryByTarget(string $targetId)
    {
        $progress = IKUProgress::where('iku_target_id', $targetId)
            ->with(['creator'])
            ->orderBy('tanggal_capaian', 'desc')
            ->get();

        $summary = [
            'total_entries' => $progress->count(),
            'total_nilai_capaian' => $progress->sum('nilai_capaian'),
            'avg_persentase_capaian' => $progress->avg('persentase_capaian'),
            'latest_progress' => $progress->first(),
            'progress_list' => $progress,
        ];

        return response()->json([
            'success' => true,
            'data' => $summary,
        ]);
    }
}
