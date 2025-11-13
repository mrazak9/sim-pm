<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\UnitKerja;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

class UnitKerjaController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {
        $query = UnitKerja::with(['parent', 'children']);

        // Filter by active status
        if ($request->has('is_active')) {
            $query->where('is_active', $request->is_active);
        }

        // Filter by jenis unit
        if ($request->has('jenis_unit')) {
            $query->where('jenis_unit', $request->jenis_unit);
        }

        // Search
        if ($request->has('search')) {
            $search = $request->search;
            $query->where(function($q) use ($search) {
                $q->where('nama_unit', 'LIKE', "%{$search}%")
                  ->orWhere('kode_unit', 'LIKE', "%{$search}%");
            });
        }

        $unitKerjas = $query->orderBy('nama_unit')->paginate($request->get('per_page', 15));

        return response()->json([
            'success' => true,
            'data' => $unitKerjas,
        ]);
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'kode_unit' => 'required|string|max:20|unique:unit_kerjas',
            'nama_unit' => 'required|string|max:255',
            'deskripsi' => 'nullable|string',
            'jenis_unit' => 'required|in:fakultas,program_studi,lembaga,unit_pendukung',
            'parent_id' => 'nullable|exists:unit_kerjas,id',
            'is_active' => 'boolean',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => 'Validation error',
                'errors' => $validator->errors(),
            ], 422);
        }

        $unitKerja = UnitKerja::create($request->all());

        return response()->json([
            'success' => true,
            'message' => 'Unit Kerja created successfully',
            'data' => $unitKerja->load(['parent', 'children']),
        ], 201);
    }

    /**
     * Display the specified resource.
     */
    public function show(string $id)
    {
        $unitKerja = UnitKerja::with(['parent', 'children', 'programStudis'])->find($id);

        if (!$unitKerja) {
            return response()->json([
                'success' => false,
                'message' => 'Unit Kerja not found',
            ], 404);
        }

        return response()->json([
            'success' => true,
            'data' => $unitKerja,
        ]);
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, string $id)
    {
        $unitKerja = UnitKerja::find($id);

        if (!$unitKerja) {
            return response()->json([
                'success' => false,
                'message' => 'Unit Kerja not found',
            ], 404);
        }

        $validator = Validator::make($request->all(), [
            'kode_unit' => 'required|string|max:20|unique:unit_kerjas,kode_unit,' . $id,
            'nama_unit' => 'required|string|max:255',
            'deskripsi' => 'nullable|string',
            'jenis_unit' => 'required|in:fakultas,program_studi,lembaga,unit_pendukung',
            'parent_id' => 'nullable|exists:unit_kerjas,id',
            'is_active' => 'boolean',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => 'Validation error',
                'errors' => $validator->errors(),
            ], 422);
        }

        $unitKerja->update($request->all());

        return response()->json([
            'success' => true,
            'message' => 'Unit Kerja updated successfully',
            'data' => $unitKerja->load(['parent', 'children']),
        ]);
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(string $id)
    {
        $unitKerja = UnitKerja::find($id);

        if (!$unitKerja) {
            return response()->json([
                'success' => false,
                'message' => 'Unit Kerja not found',
            ], 404);
        }

        $unitKerja->delete();

        return response()->json([
            'success' => true,
            'message' => 'Unit Kerja deleted successfully',
        ]);
    }
}
