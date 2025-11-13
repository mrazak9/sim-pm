<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\ProgramStudi;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

class ProgramStudiController extends Controller
{
    public function index(Request $request)
    {
        $query = ProgramStudi::with(['unitKerja']);

        if ($request->has('is_active')) {
            $query->where('is_active', $request->is_active);
        }

        if ($request->has('jenjang')) {
            $query->where('jenjang', $request->jenjang);
        }

        if ($request->has('unit_kerja_id')) {
            $query->where('unit_kerja_id', $request->unit_kerja_id);
        }

        if ($request->has('search')) {
            $search = $request->search;
            $query->where(function($q) use ($search) {
                $q->where('nama_prodi', 'LIKE', "%{$search}%")
                  ->orWhere('kode_prodi', 'LIKE', "%{$search}%");
            });
        }

        $programStudis = $query->orderBy('nama_prodi')->paginate($request->get('per_page', 15));

        return response()->json([
            'success' => true,
            'data' => $programStudis,
        ]);
    }

    public function store(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'kode_prodi' => 'required|string|max:20|unique:program_studis',
            'nama_prodi' => 'required|string|max:255',
            'unit_kerja_id' => 'required|exists:unit_kerjas,id',
            'jenjang' => 'required|in:D3,D4,S1,S2,S3',
            'akreditasi' => 'nullable|string|max:5',
            'tanggal_akreditasi' => 'nullable|date',
            'kuota_mahasiswa' => 'nullable|integer|min:0',
            'is_active' => 'boolean',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => 'Validation error',
                'errors' => $validator->errors(),
            ], 422);
        }

        $programStudi = ProgramStudi::create($request->all());

        return response()->json([
            'success' => true,
            'message' => 'Program Studi created successfully',
            'data' => $programStudi->load('unitKerja'),
        ], 201);
    }

    public function show(string $id)
    {
        $programStudi = ProgramStudi::with('unitKerja')->find($id);

        if (!$programStudi) {
            return response()->json([
                'success' => false,
                'message' => 'Program Studi not found',
            ], 404);
        }

        return response()->json([
            'success' => true,
            'data' => $programStudi,
        ]);
    }

    public function update(Request $request, string $id)
    {
        $programStudi = ProgramStudi::find($id);

        if (!$programStudi) {
            return response()->json([
                'success' => false,
                'message' => 'Program Studi not found',
            ], 404);
        }

        $validator = Validator::make($request->all(), [
            'kode_prodi' => 'required|string|max:20|unique:program_studis,kode_prodi,' . $id,
            'nama_prodi' => 'required|string|max:255',
            'unit_kerja_id' => 'required|exists:unit_kerjas,id',
            'jenjang' => 'required|in:D3,D4,S1,S2,S3',
            'akreditasi' => 'nullable|string|max:5',
            'tanggal_akreditasi' => 'nullable|date',
            'kuota_mahasiswa' => 'nullable|integer|min:0',
            'is_active' => 'boolean',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => 'Validation error',
                'errors' => $validator->errors(),
            ], 422);
        }

        $programStudi->update($request->all());

        return response()->json([
            'success' => true,
            'message' => 'Program Studi updated successfully',
            'data' => $programStudi->load('unitKerja'),
        ]);
    }

    public function destroy(string $id)
    {
        $programStudi = ProgramStudi::find($id);

        if (!$programStudi) {
            return response()->json([
                'success' => false,
                'message' => 'Program Studi not found',
            ], 404);
        }

        $programStudi->delete();

        return response()->json([
            'success' => true,
            'message' => 'Program Studi deleted successfully',
        ]);
    }
}
