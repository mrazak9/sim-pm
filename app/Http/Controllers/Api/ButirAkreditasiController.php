<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\ButirAkreditasi;
use Illuminate\Http\Request;

class ButirAkreditasiController extends Controller
{
    /**
     * Display a listing of butir akreditasi.
     */
    public function index(Request $request)
    {
        try {
            $query = ButirAkreditasi::with(['parent', 'children']);

            // Filter by instrumen
            if ($request->has('instrumen')) {
                $query->where('instrumen', $request->instrumen);
            }

            // Filter by kategori
            if ($request->has('kategori')) {
                $query->where('kategori', $request->kategori);
            }

            // Filter parent only
            if ($request->has('parent_only') && $request->parent_only == 'true') {
                $query->whereNull('parent_id');
            }

            // Filter children only
            if ($request->has('parent_id')) {
                $query->where('parent_id', $request->parent_id);
            }

            // Filter mandatory
            if ($request->has('is_mandatory')) {
                $query->where('is_mandatory', $request->is_mandatory === 'true');
            }

            // Search
            if ($request->has('search')) {
                $query->where(function ($q) use ($request) {
                    $q->where('kode', 'like', '%' . $request->search . '%')
                      ->orWhere('nama', 'like', '%' . $request->search . '%')
                      ->orWhere('deskripsi', 'like', '%' . $request->search . '%');
                });
            }

            // Sorting
            $sortBy = $request->get('sort_by', 'urutan');
            $sortOrder = $request->get('sort_order', 'asc');
            $query->orderBy($sortBy, $sortOrder);

            // Pagination or all
            if ($request->has('per_page') && $request->per_page !== 'all') {
                $perPage = $request->get('per_page', 15);
                $butirs = $query->paginate($perPage);
            } else {
                $butirs = $query->get();
            }

            return response()->json([
                'success' => true,
                'message' => 'Data butir akreditasi berhasil diambil',
                'data' => $butirs,
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Gagal mengambil data butir akreditasi',
                'error' => $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Get butir akreditasi grouped by kategori.
     */
    public function byKategori(Request $request)
    {
        try {
            $instrumen = $request->get('instrumen', '4.0');

            $butirs = ButirAkreditasi::with(['children'])
                ->where('instrumen', $instrumen)
                ->whereNull('parent_id')
                ->orderBy('kategori')
                ->orderBy('urutan')
                ->get()
                ->groupBy('kategori');

            $formatted = [];
            foreach ($butirs as $kategori => $items) {
                $formatted[] = [
                    'kategori' => $kategori,
                    'total_butir' => $items->sum(function ($item) {
                        return 1 + $item->children->count();
                    }),
                    'total_bobot' => $items->sum('bobot'),
                    'butir' => $items->values(),
                ];
            }

            return response()->json([
                'success' => true,
                'message' => 'Data butir akreditasi per kategori berhasil diambil',
                'data' => $formatted,
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Gagal mengambil data',
                'error' => $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Display the specified butir akreditasi.
     */
    public function show($id)
    {
        try {
            $butir = ButirAkreditasi::with(['parent', 'children', 'dokumenAkreditasis'])
                ->findOrFail($id);

            return response()->json([
                'success' => true,
                'message' => 'Detail butir akreditasi berhasil diambil',
                'data' => $butir,
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Butir akreditasi tidak ditemukan',
                'error' => $e->getMessage(),
            ], 404);
        }
    }

    /**
     * Get list of instrumen available.
     */
    public function instrumen()
    {
        try {
            $instrumens = ButirAkreditasi::select('instrumen')
                ->distinct()
                ->orderBy('instrumen')
                ->pluck('instrumen');

            return response()->json([
                'success' => true,
                'message' => 'Daftar instrumen berhasil diambil',
                'data' => $instrumens,
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Gagal mengambil daftar instrumen',
                'error' => $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Get list of kategori for specific instrumen.
     */
    public function kategori(Request $request)
    {
        try {
            $instrumen = $request->get('instrumen', '4.0');

            $kategoris = ButirAkreditasi::where('instrumen', $instrumen)
                ->select('kategori')
                ->distinct()
                ->whereNotNull('kategori')
                ->orderBy('kategori')
                ->pluck('kategori');

            return response()->json([
                'success' => true,
                'message' => 'Daftar kategori berhasil diambil',
                'data' => $kategoris,
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Gagal mengambil daftar kategori',
                'error' => $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Store (optional, for admin to add custom butir)
     */
    public function store(Request $request)
    {
        // Implementation untuk admin add custom butir (optional)
        return response()->json([
            'success' => false,
            'message' => 'Feature not implemented yet',
        ], 501);
    }

    /**
     * Update (optional, for admin to edit butir)
     */
    public function update(Request $request, $id)
    {
        // Implementation untuk admin edit butir (optional)
        return response()->json([
            'success' => false,
            'message' => 'Feature not implemented yet',
        ], 501);
    }

    /**
     * Delete (optional, for admin)
     */
    public function destroy($id)
    {
        // Implementation untuk admin delete butir (optional)
        return response()->json([
            'success' => false,
            'message' => 'Feature not implemented yet',
        ], 501);
    }
}
