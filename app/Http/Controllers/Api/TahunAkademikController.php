<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\TahunAkademik;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

class TahunAkademikController extends Controller
{
    public function index(Request $request)
    {
        $query = TahunAkademik::query();

        if ($request->has('is_active')) {
            $query->where('is_active', $request->is_active);
        }

        if ($request->has('semester')) {
            $query->where('semester', $request->semester);
        }

        if ($request->has('search')) {
            $search = $request->search;
            $query->where(function($q) use ($search) {
                $q->where('nama_tahun', 'LIKE', "%{$search}%")
                  ->orWhere('kode_tahun', 'LIKE', "%{$search}%");
            });
        }

        $tahunAkademiks = $query->orderByDesc('tanggal_mulai')->paginate($request->get('per_page', 15));

        return response()->json([
            'success' => true,
            'data' => $tahunAkademiks,
        ]);
    }

    public function store(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'kode_tahun' => 'required|string|max:20|unique:tahun_akademiks',
            'nama_tahun' => 'required|string|max:255',
            'semester' => 'required|in:ganjil,genap',
            'tanggal_mulai' => 'required|date',
            'tanggal_selesai' => 'required|date|after:tanggal_mulai',
            'is_active' => 'boolean',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => 'Validation error',
                'errors' => $validator->errors(),
            ], 422);
        }

        $tahunAkademik = TahunAkademik::create($request->all());

        return response()->json([
            'success' => true,
            'message' => 'Tahun Akademik created successfully',
            'data' => $tahunAkademik,
        ], 201);
    }

    public function show(string $id)
    {
        $tahunAkademik = TahunAkademik::find($id);

        if (!$tahunAkademik) {
            return response()->json([
                'success' => false,
                'message' => 'Tahun Akademik not found',
            ], 404);
        }

        return response()->json([
            'success' => true,
            'data' => $tahunAkademik,
        ]);
    }

    public function update(Request $request, string $id)
    {
        $tahunAkademik = TahunAkademik::find($id);

        if (!$tahunAkademik) {
            return response()->json([
                'success' => false,
                'message' => 'Tahun Akademik not found',
            ], 404);
        }

        $validator = Validator::make($request->all(), [
            'kode_tahun' => 'required|string|max:20|unique:tahun_akademiks,kode_tahun,' . $id,
            'nama_tahun' => 'required|string|max:255',
            'semester' => 'required|in:ganjil,genap',
            'tanggal_mulai' => 'required|date',
            'tanggal_selesai' => 'required|date|after:tanggal_mulai',
            'is_active' => 'boolean',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => 'Validation error',
                'errors' => $validator->errors(),
            ], 422);
        }

        $tahunAkademik->update($request->all());

        return response()->json([
            'success' => true,
            'message' => 'Tahun Akademik updated successfully',
            'data' => $tahunAkademik,
        ]);
    }

    public function destroy(string $id)
    {
        $tahunAkademik = TahunAkademik::find($id);

        if (!$tahunAkademik) {
            return response()->json([
                'success' => false,
                'message' => 'Tahun Akademik not found',
            ], 404);
        }

        $tahunAkademik->delete();

        return response()->json([
            'success' => true,
            'message' => 'Tahun Akademik deleted successfully',
        ]);
    }
}
