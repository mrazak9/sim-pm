<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\IKU;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

class IKUController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {
        $query = IKU::with(['targets']);

        // Filter by active status
        if ($request->has('is_active')) {
            $query->where('is_active', $request->is_active);
        }

        // Filter by kategori
        if ($request->has('kategori')) {
            $query->where('kategori', $request->kategori);
        }

        // Filter by target_type
        if ($request->has('target_type')) {
            $query->where('target_type', $request->target_type);
        }

        // Search
        if ($request->has('search')) {
            $search = $request->search;
            $query->where(function($q) use ($search) {
                $q->where('nama_iku', 'LIKE', "%{$search}%")
                  ->orWhere('kode_iku', 'LIKE', "%{$search}%")
                  ->orWhere('deskripsi', 'LIKE', "%{$search}%");
            });
        }

        $ikus = $query->orderBy('kode_iku')->paginate($request->get('per_page', 15));

        return response()->json([
            'success' => true,
            'data' => $ikus,
        ]);
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'kode_iku' => 'required|string|max:20|unique:ikus',
            'nama_iku' => 'required|string|max:255',
            'deskripsi' => 'nullable|string',
            'satuan' => 'required|in:persen,jumlah,skor,nilai',
            'target_type' => 'required|in:increase,decrease',
            'kategori' => 'nullable|string|max:100',
            'bobot' => 'nullable|integer|min:0|max:100',
            'is_active' => 'boolean',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => 'Validation error',
                'errors' => $validator->errors(),
            ], 422);
        }

        $iku = IKU::create($request->all());

        return response()->json([
            'success' => true,
            'message' => 'IKU created successfully',
            'data' => $iku->load(['targets']),
        ], 201);
    }

    /**
     * Display the specified resource.
     */
    public function show(string $id)
    {
        $iku = IKU::with(['targets.tahunAkademik', 'targets.unitKerja', 'targets.programStudi', 'targets.progress'])->find($id);

        if (!$iku) {
            return response()->json([
                'success' => false,
                'message' => 'IKU not found',
            ], 404);
        }

        return response()->json([
            'success' => true,
            'data' => $iku,
        ]);
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, string $id)
    {
        $iku = IKU::find($id);

        if (!$iku) {
            return response()->json([
                'success' => false,
                'message' => 'IKU not found',
            ], 404);
        }

        $validator = Validator::make($request->all(), [
            'kode_iku' => 'required|string|max:20|unique:ikus,kode_iku,' . $id,
            'nama_iku' => 'required|string|max:255',
            'deskripsi' => 'nullable|string',
            'satuan' => 'required|in:persen,jumlah,skor,nilai',
            'target_type' => 'required|in:increase,decrease',
            'kategori' => 'nullable|string|max:100',
            'bobot' => 'nullable|integer|min:0|max:100',
            'is_active' => 'boolean',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => 'Validation error',
                'errors' => $validator->errors(),
            ], 422);
        }

        $iku->update($request->all());

        return response()->json([
            'success' => true,
            'message' => 'IKU updated successfully',
            'data' => $iku->load(['targets']),
        ]);
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(string $id)
    {
        $iku = IKU::find($id);

        if (!$iku) {
            return response()->json([
                'success' => false,
                'message' => 'IKU not found',
            ], 404);
        }

        $iku->delete();

        return response()->json([
            'success' => true,
            'message' => 'IKU deleted successfully',
        ]);
    }

    /**
     * Get list of unique categories
     */
    public function categories()
    {
        $categories = IKU::whereNotNull('kategori')
            ->distinct()
            ->pluck('kategori');

        return response()->json([
            'success' => true,
            'data' => $categories,
        ]);
    }
}
