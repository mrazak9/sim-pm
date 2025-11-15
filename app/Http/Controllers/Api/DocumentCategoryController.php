<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\DocumentCategory;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Validator;

class DocumentCategoryController extends Controller
{
    /**
     * Display a listing of categories
     */
    public function index(Request $request): JsonResponse
    {
        try {
            $query = DocumentCategory::with(['parent', 'children']);

            // Filter active only
            if ($request->boolean('active_only', true)) {
                $query->where('is_active', true);
            }

            // Filter by parent
            if ($request->has('parent_id')) {
                $query->where('parent_id', $request->input('parent_id'));
            }

            // Root categories only
            if ($request->boolean('root_only')) {
                $query->whereNull('parent_id');
            }

            $categories = $query->orderBy('order')->get();

            return response()->json([
                'success' => true,
                'message' => 'Data kategori berhasil diambil',
                'data' => $categories,
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Gagal mengambil data kategori',
                'error' => $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Display the specified category
     */
    public function show(int $id): JsonResponse
    {
        try {
            $category = DocumentCategory::with(['parent', 'children', 'documents'])->findOrFail($id);

            return response()->json([
                'success' => true,
                'message' => 'Detail kategori berhasil diambil',
                'data' => $category,
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Gagal mengambil detail kategori',
                'error' => $e->getMessage(),
            ], 404);
        }
    }

    /**
     * Store a newly created category
     */
    public function store(Request $request): JsonResponse
    {
        $validator = Validator::make($request->all(), [
            'name' => 'required|string|max:255',
            'code' => 'required|string|max:50|unique:document_categories,code',
            'description' => 'nullable|string',
            'parent_id' => 'nullable|exists:document_categories,id',
            'icon' => 'nullable|string|max:50',
            'color' => 'nullable|string|size:7|regex:/^#[0-9A-Fa-f]{6}$/',
            'order' => 'nullable|integer|min:0',
            'is_active' => 'boolean',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => 'Validasi gagal',
                'errors' => $validator->errors(),
            ], 422);
        }

        try {
            $category = DocumentCategory::create($request->all());

            return response()->json([
                'success' => true,
                'message' => 'Kategori berhasil dibuat',
                'data' => $category->load(['parent']),
            ], 201);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Gagal membuat kategori',
                'error' => $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Update the specified category
     */
    public function update(Request $request, int $id): JsonResponse
    {
        $validator = Validator::make($request->all(), [
            'name' => 'sometimes|string|max:255',
            'code' => 'sometimes|string|max:50|unique:document_categories,code,' . $id,
            'description' => 'nullable|string',
            'parent_id' => 'nullable|exists:document_categories,id',
            'icon' => 'nullable|string|max:50',
            'color' => 'nullable|string|size:7|regex:/^#[0-9A-Fa-f]{6}$/',
            'order' => 'nullable|integer|min:0',
            'is_active' => 'boolean',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => 'Validasi gagal',
                'errors' => $validator->errors(),
            ], 422);
        }

        try {
            $category = DocumentCategory::findOrFail($id);

            // Prevent circular parent relationship
            if ($request->has('parent_id') && $request->input('parent_id')) {
                if ($this->wouldCreateCircular($id, $request->input('parent_id'))) {
                    return response()->json([
                        'success' => false,
                        'message' => 'Tidak dapat membuat relasi circular pada kategori',
                    ], 400);
                }
            }

            $category->update($request->all());

            return response()->json([
                'success' => true,
                'message' => 'Kategori berhasil diperbarui',
                'data' => $category->fresh(['parent', 'children']),
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Gagal memperbarui kategori',
                'error' => $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Remove the specified category
     */
    public function destroy(int $id): JsonResponse
    {
        try {
            $category = DocumentCategory::findOrFail($id);

            // Check if category has documents
            if ($category->documents()->count() > 0) {
                return response()->json([
                    'success' => false,
                    'message' => 'Tidak dapat menghapus kategori yang masih memiliki dokumen',
                ], 400);
            }

            // Check if category has children
            if ($category->children()->count() > 0) {
                return response()->json([
                    'success' => false,
                    'message' => 'Tidak dapat menghapus kategori yang masih memiliki sub-kategori',
                ], 400);
            }

            $category->delete();

            return response()->json([
                'success' => true,
                'message' => 'Kategori berhasil dihapus',
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Gagal menghapus kategori',
                'error' => $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Get category tree (hierarchical structure)
     */
    public function tree(): JsonResponse
    {
        try {
            $categories = DocumentCategory::with(['children' => function ($query) {
                $query->where('is_active', true)->orderBy('order');
            }])
                ->whereNull('parent_id')
                ->where('is_active', true)
                ->orderBy('order')
                ->get();

            return response()->json([
                'success' => true,
                'message' => 'Tree kategori berhasil diambil',
                'data' => $categories,
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Gagal mengambil tree kategori',
                'error' => $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Check if setting parent would create circular relationship
     */
    protected function wouldCreateCircular(int $categoryId, int $parentId): bool
    {
        if ($categoryId === $parentId) {
            return true;
        }

        $parent = DocumentCategory::find($parentId);
        while ($parent) {
            if ($parent->parent_id === $categoryId) {
                return true;
            }
            $parent = $parent->parent;
        }

        return false;
    }
}
