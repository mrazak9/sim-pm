<?php

namespace App\Services;

use App\Models\Document;
use App\Models\DocumentVersion;
use App\Models\DocumentShare;
use App\Models\DocumentCategory;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Auth;
use Illuminate\Http\UploadedFile;
use Carbon\Carbon;

class DocumentService
{
    /**
     * Get all documents with filters and pagination
     */
    public function paginate(array $filters = [], int $perPage = 15): LengthAwarePaginator
    {
        $query = Document::with(['category', 'uploader', 'approver'])
            ->where('is_latest', true);

        // Filter by category
        if (!empty($filters['category_id'])) {
            $query->where('category_id', $filters['category_id']);
        }

        // Filter by status
        if (!empty($filters['status'])) {
            $query->where('status', $filters['status']);
        }

        // Filter by visibility
        if (!empty($filters['visibility'])) {
            $query->where('visibility', $filters['visibility']);
        }

        // Filter by uploaded by
        if (!empty($filters['uploaded_by'])) {
            $query->where('uploaded_by', $filters['uploaded_by']);
        }

        // Search in title and description
        if (!empty($filters['search'])) {
            $query->whereFullText(['title', 'description'], $filters['search']);
        }

        // Filter archived
        if (isset($filters['is_archived'])) {
            $query->where('is_archived', $filters['is_archived']);
        } else {
            // By default, don't show archived documents
            $query->where('is_archived', false);
        }

        // Apply access control - users can only see:
        // 1. Their own documents
        // 2. Public documents
        // 3. Documents shared with them
        if (!Auth::user()->hasRole('admin')) {
            $userId = Auth::id();
            $query->where(function ($q) use ($userId) {
                $q->where('uploaded_by', $userId)
                    ->orWhere('visibility', 'public')
                    ->orWhereHas('shares', function ($shareQuery) use ($userId) {
                        $shareQuery->where('shared_with', $userId)
                            ->where(function ($expiryQuery) {
                                $expiryQuery->whereNull('expires_at')
                                    ->orWhere('expires_at', '>', Carbon::now());
                            });
                    });
            });
        }

        return $query->latest()->paginate($perPage);
    }

    /**
     * Get document by ID with access control
     */
    public function findById(int $id): ?Document
    {
        $document = Document::with([
            'category',
            'uploader',
            'approver',
            'versions.uploader',
            'shares.recipient'
        ])->find($id);

        if (!$document) {
            return null;
        }

        // Check access
        if (!$this->canAccess($document)) {
            throw new \Exception('Anda tidak memiliki akses ke dokumen ini.');
        }

        return $document;
    }

    /**
     * Upload new document
     */
    public function upload(array $data, UploadedFile $file): Document
    {
        DB::beginTransaction();

        try {
            // Store file
            $path = $file->store('documents/' . date('Y/m'), 'public');

            // Create document
            $document = Document::create([
                'category_id' => $data['category_id'],
                'uploaded_by' => Auth::id(),
                'title' => $data['title'],
                'document_number' => $data['document_number'] ?? null,
                'description' => $data['description'] ?? null,
                'file_name' => $file->getClientOriginalName(),
                'file_path' => $path,
                'file_type' => $file->getClientOriginalExtension(),
                'mime_type' => $file->getMimeType(),
                'file_size' => $file->getSize(),
                'status' => $data['status'] ?? 'draft',
                'visibility' => $data['visibility'] ?? 'private',
                'current_version' => 1,
                'is_latest' => true,
                'retention_until' => $data['retention_until'] ?? null,
            ]);

            // Create first version
            DocumentVersion::create([
                'document_id' => $document->id,
                'uploaded_by' => Auth::id(),
                'version_number' => 1,
                'file_name' => $file->getClientOriginalName(),
                'file_path' => $path,
                'file_type' => $file->getClientOriginalExtension(),
                'mime_type' => $file->getMimeType(),
                'file_size' => $file->getSize(),
                'change_notes' => 'Initial upload',
            ]);

            DB::commit();

            return $document->load(['category', 'uploader']);
        } catch (\Exception $e) {
            DB::rollBack();
            // Delete uploaded file if transaction fails
            if (isset($path)) {
                Storage::disk('public')->delete($path);
            }
            throw $e;
        }
    }

    /**
     * Update document metadata
     */
    public function update(int $id, array $data): Document
    {
        $document = $this->findById($id);

        // Only owner or admin can update
        if (!$this->canEdit($document)) {
            throw new \Exception('Anda tidak memiliki izin untuk mengubah dokumen ini.');
        }

        $document->update([
            'title' => $data['title'] ?? $document->title,
            'document_number' => $data['document_number'] ?? $document->document_number,
            'description' => $data['description'] ?? $document->description,
            'category_id' => $data['category_id'] ?? $document->category_id,
            'visibility' => $data['visibility'] ?? $document->visibility,
            'retention_until' => $data['retention_until'] ?? $document->retention_until,
        ]);

        return $document->fresh(['category', 'uploader']);
    }

    /**
     * Upload new version of document
     */
    public function uploadNewVersion(int $id, UploadedFile $file, ?string $changeNotes = null): Document
    {
        $document = $this->findById($id);

        // Only owner or admin can upload new version
        if (!$this->canEdit($document)) {
            throw new \Exception('Anda tidak memiliki izin untuk mengubah dokumen ini.');
        }

        DB::beginTransaction();

        try {
            // Store new file
            $path = $file->store('documents/' . date('Y/m'), 'public');

            // Update document
            $newVersion = $document->current_version + 1;
            $document->update([
                'file_name' => $file->getClientOriginalName(),
                'file_path' => $path,
                'file_type' => $file->getClientOriginalExtension(),
                'mime_type' => $file->getMimeType(),
                'file_size' => $file->getSize(),
                'current_version' => $newVersion,
            ]);

            // Create version record
            DocumentVersion::create([
                'document_id' => $document->id,
                'uploaded_by' => Auth::id(),
                'version_number' => $newVersion,
                'file_name' => $file->getClientOriginalName(),
                'file_path' => $path,
                'file_type' => $file->getClientOriginalExtension(),
                'mime_type' => $file->getMimeType(),
                'file_size' => $file->getSize(),
                'change_notes' => $changeNotes ?? "Version $newVersion",
            ]);

            DB::commit();

            return $document->fresh(['category', 'uploader', 'versions']);
        } catch (\Exception $e) {
            DB::rollBack();
            if (isset($path)) {
                Storage::disk('public')->delete($path);
            }
            throw $e;
        }
    }

    /**
     * Restore previous version
     */
    public function restoreVersion(int $documentId, int $versionNumber): Document
    {
        $document = $this->findById($documentId);

        // Only owner or admin can restore
        if (!$this->canEdit($document)) {
            throw new \Exception('Anda tidak memiliki izin untuk mengubah dokumen ini.');
        }

        $version = DocumentVersion::where('document_id', $documentId)
            ->where('version_number', $versionNumber)
            ->firstOrFail();

        DB::beginTransaction();

        try {
            // Copy the old version file to new location
            $oldPath = $version->file_path;
            $newPath = 'documents/' . date('Y/m') . '/' . uniqid() . '_' . $version->file_name;
            Storage::disk('public')->copy($oldPath, $newPath);

            // Update document
            $newVersion = $document->current_version + 1;
            $document->update([
                'file_name' => $version->file_name,
                'file_path' => $newPath,
                'current_version' => $newVersion,
            ]);

            // Create new version record
            DocumentVersion::create([
                'document_id' => $document->id,
                'uploaded_by' => Auth::id(),
                'version_number' => $newVersion,
                'file_name' => $version->file_name,
                'file_path' => $newPath,
                'file_type' => $version->file_type,
                'mime_type' => $version->mime_type,
                'file_size' => $version->file_size,
                'change_notes' => "Restored from version $versionNumber",
            ]);

            DB::commit();

            return $document->fresh(['category', 'uploader', 'versions']);
        } catch (\Exception $e) {
            DB::rollBack();
            if (isset($newPath)) {
                Storage::disk('public')->delete($newPath);
            }
            throw $e;
        }
    }

    /**
     * Delete document (soft delete)
     */
    public function delete(int $id): bool
    {
        $document = $this->findById($id);

        // Only owner or admin can delete
        if (!$this->canEdit($document)) {
            throw new \Exception('Anda tidak memiliki izin untuk menghapus dokumen ini.');
        }

        return $document->delete();
    }

    /**
     * Permanently delete document and all files
     */
    public function forceDelete(int $id): bool
    {
        $document = Document::withTrashed()->findOrFail($id);

        // Only admin can force delete
        if (!Auth::user()->hasRole('admin')) {
            throw new \Exception('Hanya admin yang dapat menghapus permanen dokumen.');
        }

        DB::beginTransaction();

        try {
            // Delete all version files
            foreach ($document->versions as $version) {
                Storage::disk('public')->delete($version->file_path);
            }

            // Delete main file
            Storage::disk('public')->delete($document->file_path);

            // Force delete document (cascade will delete versions and shares)
            $document->forceDelete();

            DB::commit();

            return true;
        } catch (\Exception $e) {
            DB::rollBack();
            throw $e;
        }
    }

    /**
     * Share document with user
     */
    public function share(int $documentId, int $userId, string $permission = 'view', ?Carbon $expiresAt = null, ?string $notes = null): DocumentShare
    {
        $document = $this->findById($documentId);

        // Only owner or admin can share
        if (!$this->canEdit($document)) {
            throw new \Exception('Anda tidak memiliki izin untuk membagikan dokumen ini.');
        }

        // Check if already shared
        $existingShare = DocumentShare::where('document_id', $documentId)
            ->where('shared_with', $userId)
            ->first();

        if ($existingShare) {
            // Update existing share
            $existingShare->update([
                'permission' => $permission,
                'expires_at' => $expiresAt,
                'notes' => $notes,
            ]);
            return $existingShare->fresh(['recipient', 'sharer']);
        }

        // Create new share
        $share = DocumentShare::create([
            'document_id' => $documentId,
            'shared_by' => Auth::id(),
            'shared_with' => $userId,
            'permission' => $permission,
            'expires_at' => $expiresAt,
            'notes' => $notes,
        ]);

        return $share->load(['recipient', 'sharer']);
    }

    /**
     * Revoke document share
     */
    public function revokeShare(int $shareId): bool
    {
        $share = DocumentShare::findOrFail($shareId);
        $document = $this->findById($share->document_id);

        // Only owner or admin can revoke
        if (!$this->canEdit($document)) {
            throw new \Exception('Anda tidak memiliki izin untuk mencabut pembagian dokumen ini.');
        }

        return $share->delete();
    }

    /**
     * Update document status (workflow)
     */
    public function updateStatus(int $id, string $status): Document
    {
        $document = $this->findById($id);

        $validTransitions = [
            'draft' => ['review'],
            'review' => ['approved', 'draft'],
            'approved' => ['archived'],
            'archived' => [],
        ];

        // Check if transition is valid
        if (!in_array($status, $validTransitions[$document->status] ?? [])) {
            throw new \Exception("Tidak dapat mengubah status dari {$document->status} ke {$status}.");
        }

        // Update status
        $updateData = ['status' => $status];

        // If approving, record approver and timestamp
        if ($status === 'approved') {
            $updateData['approved_by'] = Auth::id();
            $updateData['approved_at'] = Carbon::now();
        }

        // If archiving, set archived flag
        if ($status === 'archived') {
            $updateData['is_archived'] = true;
        }

        $document->update($updateData);

        return $document->fresh(['category', 'uploader', 'approver']);
    }

    /**
     * Check if user can access document
     */
    protected function canAccess(Document $document): bool
    {
        $user = Auth::user();

        // Admin can access everything
        if ($user->hasRole('admin')) {
            return true;
        }

        // Owner can access
        if ($document->uploaded_by === $user->id) {
            return true;
        }

        // Public documents
        if ($document->visibility === 'public') {
            return true;
        }

        // Check if shared with user
        $share = DocumentShare::where('document_id', $document->id)
            ->where('shared_with', $user->id)
            ->active()
            ->first();

        return $share !== null;
    }

    /**
     * Check if user can edit document
     */
    protected function canEdit(Document $document): bool
    {
        $user = Auth::user();

        // Admin can edit everything
        if ($user->hasRole('admin')) {
            return true;
        }

        // Owner can edit
        if ($document->uploaded_by === $user->id) {
            return true;
        }

        // Check if shared with edit permission
        $share = DocumentShare::where('document_id', $document->id)
            ->where('shared_with', $user->id)
            ->where('permission', 'edit')
            ->active()
            ->first();

        return $share !== null;
    }

    /**
     * Get document permission for current user
     */
    public function getPermission(Document $document): ?string
    {
        $user = Auth::user();

        // Admin and owner have full access
        if ($user->hasRole('admin') || $document->uploaded_by === $user->id) {
            return 'edit';
        }

        // Check share permission
        $share = DocumentShare::where('document_id', $document->id)
            ->where('shared_with', $user->id)
            ->active()
            ->first();

        if ($share) {
            return $share->permission;
        }

        // Public documents can be viewed
        if ($document->visibility === 'public') {
            return 'view';
        }

        return null;
    }

    /**
     * Get documents shared with current user
     */
    public function getSharedWithMe(int $perPage = 15): LengthAwarePaginator
    {
        $userId = Auth::id();

        return Document::whereHas('shares', function ($query) use ($userId) {
            $query->where('shared_with', $userId)
                ->where(function ($q) {
                    $q->whereNull('expires_at')
                        ->orWhere('expires_at', '>', Carbon::now());
                });
        })
            ->where('is_latest', true)
            ->where('is_archived', false)
            ->with(['category', 'uploader', 'shares' => function ($query) use ($userId) {
                $query->where('shared_with', $userId);
            }])
            ->latest()
            ->paginate($perPage);
    }

    /**
     * Get my documents
     */
    public function getMyDocuments(int $perPage = 15): LengthAwarePaginator
    {
        return Document::where('uploaded_by', Auth::id())
            ->where('is_latest', true)
            ->with(['category', 'approver'])
            ->latest()
            ->paginate($perPage);
    }
}
