<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\IKUTarget;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

class IKUTargetController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {
        $query = IKUTarget::with(['iku', 'tahunAkademik', 'unitKerja', 'programStudi', 'latestProgress']);

        // Filter by IKU
        if ($request->has('iku_id')) {
            $query->where('iku_id', $request->iku_id);
        }

        // Filter by tahun akademik
        if ($request->has('tahun_akademik_id')) {
            $query->where('tahun_akademik_id', $request->tahun_akademik_id);
        }

        // Filter by unit kerja
        if ($request->has('unit_kerja_id')) {
            $query->where('unit_kerja_id', $request->unit_kerja_id);
        }

        // Filter by program studi
        if ($request->has('program_studi_id')) {
            $query->where('program_studi_id', $request->program_studi_id);
        }

        // Filter by periode
        if ($request->has('periode')) {
            $query->where('periode', $request->periode);
        }

        // Search
        if ($request->has('search')) {
            $search = $request->search;
            $query->whereHas('iku', function($q) use ($search) {
                $q->where('nama_iku', 'LIKE', "%{$search}%")
                  ->orWhere('kode_iku', 'LIKE', "%{$search}%");
            });
        }

        $targets = $query->orderBy('tahun_akademik_id', 'desc')
                        ->orderBy('created_at', 'desc')
                        ->paginate($request->get('per_page', 15));

        return response()->json([
            'success' => true,
            'data' => $targets,
        ]);
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'iku_id' => 'required|exists:ikus,id',
            'tahun_akademik_id' => 'required|exists:tahun_akademiks,id',
            'unit_kerja_id' => 'nullable|exists:unit_kerjas,id',
            'program_studi_id' => 'nullable|exists:program_studis,id',
            'target_value' => 'required|numeric|min:0',
            'periode' => 'required|in:tahunan,semester_1,semester_2,triwulan_1,triwulan_2,triwulan_3,triwulan_4',
            'keterangan' => 'nullable|string',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => 'Validation error',
                'errors' => $validator->errors(),
            ], 422);
        }

        $target = IKUTarget::create($request->all());

        return response()->json([
            'success' => true,
            'message' => 'IKU Target created successfully',
            'data' => $target->load(['iku', 'tahunAkademik', 'unitKerja', 'programStudi']),
        ], 201);
    }

    /**
     * Display the specified resource.
     */
    public function show(string $id)
    {
        $target = IKUTarget::with([
            'iku',
            'tahunAkademik',
            'unitKerja',
            'programStudi',
            'progress.creator'
        ])->find($id);

        if (!$target) {
            return response()->json([
                'success' => false,
                'message' => 'IKU Target not found',
            ], 404);
        }

        // Add calculated attributes
        $targetData = $target->toArray();
        $targetData['total_capaian'] = $target->total_capaian;
        $targetData['persentase_capaian'] = $target->persentase_capaian;

        return response()->json([
            'success' => true,
            'data' => $targetData,
        ]);
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, string $id)
    {
        $target = IKUTarget::find($id);

        if (!$target) {
            return response()->json([
                'success' => false,
                'message' => 'IKU Target not found',
            ], 404);
        }

        $validator = Validator::make($request->all(), [
            'iku_id' => 'required|exists:ikus,id',
            'tahun_akademik_id' => 'required|exists:tahun_akademiks,id',
            'unit_kerja_id' => 'nullable|exists:unit_kerjas,id',
            'program_studi_id' => 'nullable|exists:program_studis,id',
            'target_value' => 'required|numeric|min:0',
            'periode' => 'required|in:tahunan,semester_1,semester_2,triwulan_1,triwulan_2,triwulan_3,triwulan_4',
            'keterangan' => 'nullable|string',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => 'Validation error',
                'errors' => $validator->errors(),
            ], 422);
        }

        $target->update($request->all());

        return response()->json([
            'success' => true,
            'message' => 'IKU Target updated successfully',
            'data' => $target->load(['iku', 'tahunAkademik', 'unitKerja', 'programStudi']),
        ]);
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(string $id)
    {
        $target = IKUTarget::find($id);

        if (!$target) {
            return response()->json([
                'success' => false,
                'message' => 'IKU Target not found',
            ], 404);
        }

        $target->delete();

        return response()->json([
            'success' => true,
            'message' => 'IKU Target deleted successfully',
        ]);
    }

    /**
     * Get dashboard statistics for a specific target
     */
    public function statistics(string $id)
    {
        $target = IKUTarget::with(['iku', 'progress'])->find($id);

        if (!$target) {
            return response()->json([
                'success' => false,
                'message' => 'IKU Target not found',
            ], 404);
        }

        $statistics = [
            'target_value' => $target->target_value,
            'total_capaian' => $target->total_capaian,
            'persentase_capaian' => $target->persentase_capaian,
            'jumlah_progress' => $target->progress()->count(),
            'progress_terakhir' => $target->latestProgress,
            'iku' => $target->iku,
        ];

        return response()->json([
            'success' => true,
            'data' => $statistics,
        ]);
    }
}
