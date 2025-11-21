<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\InstrumenAkreditasi;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class InstrumenAkreditasiController extends Controller
{
    /**
     * Get list of active instrumen
     */
    public function index(Request $request): JsonResponse
    {
        $query = InstrumenAkreditasi::active();

        // Filter by jenis if provided
        if ($request->has('jenis')) {
            $query->byJenis($request->get('jenis'));
        }

        // Filter by lembaga if provided
        if ($request->has('lembaga')) {
            $query->byLembaga($request->get('lembaga'));
        }

        $instrumens = $query->orderBy('tahun_berlaku', 'desc')
            ->orderBy('kode', 'asc')
            ->get(['id', 'kode', 'nama', 'deskripsi', 'jenis', 'lembaga', 'tahun_berlaku']);

        return response()->json([
            'success' => true,
            'data' => $instrumens
        ]);
    }
}
