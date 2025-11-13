<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Jabatan;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

class JabatanController extends Controller
{
    public function index(Request $request)
    {
        $query = Jabatan::query();

        if ($request->has('is_active')) {
            $query->where('is_active', $request->is_active);
        }

        if ($request->has('kategori')) {
            $query->where('kategori', $request->kategori);
        }

        if ($request->has('search')) {
            $search = $request->search;
            $query->where(function($q) use ($search) {
                $q->where('nama_jabatan', 'LIKE', "%{$search}%")
                  ->orWhere('kode_jabatan', 'LIKE', "%{$search}%");
            });
        }

        $jabatans = $query->orderBy('level')->orderBy('nama_jabatan')->paginate($request->get('per_page', 15));

        return response()->json([
            'success' => true,
            'data' => $jabatans,
        ]);
    }

    public function store(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'kode_jabatan' => 'required|string|max:20|unique:jabatans',
            'nama_jabatan' => 'required|string|max:255',
            'deskripsi' => 'nullable|string',
            'kategori' => 'required|in:struktural,fungsional,dosen,tendik',
            'level' => 'nullable|integer|min:1',
            'is_active' => 'boolean',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => 'Validation error',
                'errors' => $validator->errors(),
            ], 422);
        }

        $jabatan = Jabatan::create($request->all());

        return response()->json([
            'success' => true,
            'message' => 'Jabatan created successfully',
            'data' => $jabatan,
        ], 201);
    }

    public function show(string $id)
    {
        $jabatan = Jabatan::find($id);

        if (!$jabatan) {
            return response()->json([
                'success' => false,
                'message' => 'Jabatan not found',
            ], 404);
        }

        return response()->json([
            'success' => true,
            'data' => $jabatan,
        ]);
    }

    public function update(Request $request, string $id)
    {
        $jabatan = Jabatan::find($id);

        if (!$jabatan) {
            return response()->json([
                'success' => false,
                'message' => 'Jabatan not found',
            ], 404);
        }

        $validator = Validator::make($request->all(), [
            'kode_jabatan' => 'required|string|max:20|unique:jabatans,kode_jabatan,' . $id,
            'nama_jabatan' => 'required|string|max:255',
            'deskripsi' => 'nullable|string',
            'kategori' => 'required|in:struktural,fungsional,dosen,tendik',
            'level' => 'nullable|integer|min:1',
            'is_active' => 'boolean',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => 'Validation error',
                'errors' => $validator->errors(),
            ], 422);
        }

        $jabatan->update($request->all());

        return response()->json([
            'success' => true,
            'message' => 'Jabatan updated successfully',
            'data' => $jabatan,
        ]);
    }

    public function destroy(string $id)
    {
        $jabatan = Jabatan::find($id);

        if (!$jabatan) {
            return response()->json([
                'success' => false,
                'message' => 'Jabatan not found',
            ], 404);
        }

        $jabatan->delete();

        return response()->json([
            'success' => true,
            'message' => 'Jabatan deleted successfully',
        ]);
    }
}
