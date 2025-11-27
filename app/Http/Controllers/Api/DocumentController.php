<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Services\DocumentService;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Storage;
use Symfony\Component\HttpFoundation\StreamedResponse;

class DocumentController extends Controller
{
    protected DocumentService $documentService;

    public function __construct(DocumentService $documentService)
    {
        $this->documentService = $documentService;
    }

    /**
     * Display a listing of documents
     */
    public function index(Request $request): JsonResponse
    {
        try {
            $filters = $request->only([
                'category_id',
                'status',
                'visibility',
                'uploaded_by',
                'search',
                'is_archived'
            ]);
            $perPage = $request->get('per_page', 15);

            $documents = $this->documentService->paginate($filters, $perPage);

            return response()->json([
                'success' => true,
                'message' => 'Data dokumen berhasil diambil',
                'data' => $documents->items(),
                'meta' => [
                    'current_page' => $documents->currentPage(),
                    'from' => $documents->firstItem(),
                    'last_page' => $documents->lastPage(),
                    'per_page' => $documents->perPage(),
                    'to' => $documents->lastItem(),
                    'total' => $documents->total(),
                ],
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Gagal mengambil data dokumen',
                'error' => $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Get my documents
     */
    public function myDocuments(Request $request): JsonResponse
    {
        try {
            $perPage = $request->get('per_page', 15);
            $documents = $this->documentService->getMyDocuments($perPage);

            return response()->json([
                'success' => true,
                'message' => 'Data dokumen saya berhasil diambil',
                'data' => $documents->items(),
                'meta' => [
                    'current_page' => $documents->currentPage(),
                    'from' => $documents->firstItem(),
                    'last_page' => $documents->lastPage(),
                    'per_page' => $documents->perPage(),
                    'to' => $documents->lastItem(),
                    'total' => $documents->total(),
                ],
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Gagal mengambil data dokumen saya',
                'error' => $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Get documents shared with me
     */
    public function sharedWithMe(Request $request): JsonResponse
    {
        try {
            $perPage = $request->get('per_page', 15);
            $documents = $this->documentService->getSharedWithMe($perPage);

            return response()->json([
                'success' => true,
                'message' => 'Data dokumen yang dibagikan berhasil diambil',
                'data' => $documents->items(),
                'meta' => [
                    'current_page' => $documents->currentPage(),
                    'from' => $documents->firstItem(),
                    'last_page' => $documents->lastPage(),
                    'per_page' => $documents->perPage(),
                    'to' => $documents->lastItem(),
                    'total' => $documents->total(),
                ],
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Gagal mengambil data dokumen yang dibagikan',
                'error' => $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Display the specified document
     */
    public function show(int $id): JsonResponse
    {
        try {
            $document = $this->documentService->findById($id);

            if (!$document) {
                return response()->json([
                    'success' => false,
                    'message' => 'Dokumen tidak ditemukan',
                ], 404);
            }

            // Get user permission
            $permission = $this->documentService->getPermission($document);

            return response()->json([
                'success' => true,
                'message' => 'Detail dokumen berhasil diambil',
                'data' => array_merge($document->toArray(), [
                    'user_permission' => $permission,
                ]),
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Gagal mengambil detail dokumen',
                'error' => $e->getMessage(),
            ], $e->getMessage() === 'Anda tidak memiliki akses ke dokumen ini.' ? 403 : 500);
        }
    }

    /**
     * Upload new document
     */
    public function store(Request $request): JsonResponse
    {
        $validator = Validator::make($request->all(), [
            'category_id' => 'required|exists:document_categories,id',
            'title' => 'required|string|max:255',
            'document_number' => 'nullable|string|max:100|unique:documents,document_number',
            'description' => 'nullable|string',
            'file' => 'required|file|max:51200', // 50MB max
            'status' => 'nullable|in:draft,review,approved,archived',
            'visibility' => 'nullable|in:public,private,restricted',
            'retention_until' => 'nullable|date',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => 'Validasi gagal',
                'errors' => $validator->errors(),
            ], 422);
        }

        try {
            $document = $this->documentService->upload(
                $request->all(),
                $request->file('file')
            );

            return response()->json([
                'success' => true,
                'message' => 'Dokumen berhasil diunggah',
                'data' => $document,
            ], 201);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Gagal mengunggah dokumen',
                'error' => $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Update document metadata
     */
    public function update(Request $request, int $id): JsonResponse
    {
        $validator = Validator::make($request->all(), [
            'category_id' => 'sometimes|exists:document_categories,id',
            'title' => 'sometimes|string|max:255',
            'document_number' => 'nullable|string|max:100|unique:documents,document_number,' . $id,
            'description' => 'nullable|string',
            'visibility' => 'sometimes|in:public,private,restricted',
            'retention_until' => 'nullable|date',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => 'Validasi gagal',
                'errors' => $validator->errors(),
            ], 422);
        }

        try {
            $document = $this->documentService->update($id, $request->all());

            return response()->json([
                'success' => true,
                'message' => 'Dokumen berhasil diperbarui',
                'data' => $document,
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Gagal memperbarui dokumen',
                'error' => $e->getMessage(),
            ], $e->getMessage() === 'Anda tidak memiliki izin untuk mengubah dokumen ini.' ? 403 : 500);
        }
    }

    /**
     * Upload new version
     */
    public function uploadVersion(Request $request, int $id): JsonResponse
    {
        $validator = Validator::make($request->all(), [
            'file' => 'required|file|max:51200', // 50MB max
            'change_notes' => 'nullable|string',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => 'Validasi gagal',
                'errors' => $validator->errors(),
            ], 422);
        }

        try {
            $document = $this->documentService->uploadNewVersion(
                $id,
                $request->file('file'),
                $request->input('change_notes')
            );

            return response()->json([
                'success' => true,
                'message' => 'Versi baru berhasil diunggah',
                'data' => $document,
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Gagal mengunggah versi baru',
                'error' => $e->getMessage(),
            ], $e->getMessage() === 'Anda tidak memiliki izin untuk mengubah dokumen ini.' ? 403 : 500);
        }
    }

    /**
     * Restore previous version
     */
    public function restoreVersion(int $documentId, int $versionNumber): JsonResponse
    {
        try {
            $document = $this->documentService->restoreVersion($documentId, $versionNumber);

            return response()->json([
                'success' => true,
                'message' => "Versi $versionNumber berhasil dipulihkan",
                'data' => $document,
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Gagal memulihkan versi',
                'error' => $e->getMessage(),
            ], $e->getMessage() === 'Anda tidak memiliki izin untuk mengubah dokumen ini.' ? 403 : 500);
        }
    }

    /**
     * Update document status (workflow)
     */
    public function updateStatus(Request $request, int $id): JsonResponse
    {
        $validator = Validator::make($request->all(), [
            'status' => 'required|in:draft,review,approved,archived',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => 'Validasi gagal',
                'errors' => $validator->errors(),
            ], 422);
        }

        try {
            $document = $this->documentService->updateStatus($id, $request->input('status'));

            return response()->json([
                'success' => true,
                'message' => 'Status dokumen berhasil diperbarui',
                'data' => $document,
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Gagal memperbarui status dokumen',
                'error' => $e->getMessage(),
            ], 400);
        }
    }

    /**
     * Share document with user
     */
    public function share(Request $request, int $id): JsonResponse
    {
        $validator = Validator::make($request->all(), [
            'user_id' => 'required|exists:users,id',
            'permission' => 'required|in:view,download,edit',
            'expires_at' => 'nullable|date|after:now',
            'notes' => 'nullable|string',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => 'Validasi gagal',
                'errors' => $validator->errors(),
            ], 422);
        }

        try {
            $share = $this->documentService->share(
                $id,
                $request->input('user_id'),
                $request->input('permission'),
                $request->input('expires_at') ? \Carbon\Carbon::parse($request->input('expires_at')) : null,
                $request->input('notes')
            );

            return response()->json([
                'success' => true,
                'message' => 'Dokumen berhasil dibagikan',
                'data' => $share,
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Gagal membagikan dokumen',
                'error' => $e->getMessage(),
            ], $e->getMessage() === 'Anda tidak memiliki izin untuk membagikan dokumen ini.' ? 403 : 500);
        }
    }

    /**
     * Revoke document share
     */
    public function revokeShare(int $shareId): JsonResponse
    {
        try {
            $this->documentService->revokeShare($shareId);

            return response()->json([
                'success' => true,
                'message' => 'Pembagian dokumen berhasil dicabut',
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Gagal mencabut pembagian dokumen',
                'error' => $e->getMessage(),
            ], $e->getMessage() === 'Anda tidak memiliki izin untuk mencabut pembagian dokumen ini.' ? 403 : 500);
        }
    }

    /**
     * Delete document (soft delete)
     */
    public function destroy(int $id): JsonResponse
    {
        try {
            $this->documentService->delete($id);

            return response()->json([
                'success' => true,
                'message' => 'Dokumen berhasil dihapus',
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Gagal menghapus dokumen',
                'error' => $e->getMessage(),
            ], $e->getMessage() === 'Anda tidak memiliki izin untuk menghapus dokumen ini.' ? 403 : 500);
        }
    }

    /**
     * Permanently delete document
     */
    public function forceDestroy(int $id): JsonResponse
    {
        try {
            $this->documentService->forceDelete($id);

            return response()->json([
                'success' => true,
                'message' => 'Dokumen berhasil dihapus permanen',
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Gagal menghapus permanen dokumen',
                'error' => $e->getMessage(),
            ], $e->getMessage() === 'Hanya admin yang dapat menghapus permanen dokumen.' ? 403 : 500);
        }
    }

    /**
     * Download document file
     */
    public function download(int $id): StreamedResponse|JsonResponse
    {
        try {
            $document = $this->documentService->findById($id);

            if (!$document) {
                return response()->json([
                    'success' => false,
                    'message' => 'Dokumen tidak ditemukan',
                ], 404);
            }

            // Check permission
            $permission = $this->documentService->getPermission($document);
            if (!in_array($permission, ['download', 'edit'])) {
                return response()->json([
                    'success' => false,
                    'message' => 'Anda tidak memiliki izin untuk mengunduh dokumen ini',
                ], 403);
            }

            // Check if file exists
            if (!Storage::exists($document->file_path)) {
                return response()->json([
                    'success' => false,
                    'message' => 'File tidak ditemukan di storage',
                ], 404);
            }

            // Log activity
            $this->documentService->logActivity($document, 'downloaded');

            // Return file download
            return Storage::download($document->file_path, $document->file_name);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Gagal mengunduh dokumen',
                'error' => $e->getMessage(),
            ], 500);
        }
    }

    /**
     * View/preview document file
     */
    public function view(int $id): StreamedResponse|JsonResponse
    {
        try {
            $document = $this->documentService->findById($id);

            if (!$document) {
                return response()->json([
                    'success' => false,
                    'message' => 'Dokumen tidak ditemukan',
                ], 404);
            }

            // Check permission - view requires at least view permission
            $permission = $this->documentService->getPermission($document);
            if (!$permission) {
                return response()->json([
                    'success' => false,
                    'message' => 'Anda tidak memiliki izin untuk melihat dokumen ini',
                ], 403);
            }

            // Check if file exists
            if (!Storage::exists($document->file_path)) {
                return response()->json([
                    'success' => false,
                    'message' => 'File tidak ditemukan di storage',
                ], 404);
            }

            // Log activity
            $this->documentService->logActivity($document, 'viewed');

            // Return file for inline viewing
            return response()->file(Storage::path($document->file_path), [
                'Content-Type' => $document->mime_type,
                'Content-Disposition' => 'inline; filename="' . $document->file_name . '"',
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Gagal menampilkan dokumen',
                'error' => $e->getMessage(),
            ], 500);
        }
    }
}
