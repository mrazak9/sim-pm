<?php

namespace App\Services;

use App\Models\DokumenAkreditasi;
use App\Repositories\DokumenAkreditasiRepository;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Auth;
use Illuminate\Http\UploadedFile;

class DokumenAkreditasiService
{
    protected DokumenAkreditasiRepository $repository;

    public function __construct(DokumenAkreditasiRepository $repository)
    {
        $this->repository = $repository;
    }

    /**
     * Get all dokumen akreditasi
     */
    public function all(): Collection
    {
        return $this->repository->all();
    }

    /**
     * Get all dokumen akreditasi with filters and pagination
     */
    public function paginate(array $filters = [], int $perPage = 15): LengthAwarePaginator
    {
        return $this->repository->paginate($filters, $perPage);
    }

    /**
     * Get dokumen akreditasi by ID
     */
    public function findById(int $id): ?DokumenAkreditasi
    {
        return $this->repository->findById($id);
    }

    /**
     * Create new dokumen akreditasi
     */
    public function create(array $data, ?UploadedFile $file = null): DokumenAkreditasi
    {
        DB::beginTransaction();

        try {
            // Validate nomor dokumen if provided
            if (isset($data['nomor_dokumen']) && !empty($data['nomor_dokumen'])) {
                if ($this->repository->nomorDokumenExists($data['nomor_dokumen'])) {
                    throw new \Exception('Nomor dokumen sudah digunakan');
                }
            }

            // Handle file upload
            if ($file) {
                $fileData = $this->handleFileUpload($file, $data['periode_akreditasi_id'] ?? null);
                $data = array_merge($data, $fileData);
            }

            // Set uploaded_by
            if (!isset($data['uploaded_by'])) {
                $data['uploaded_by'] = Auth::id();
            }

            $dokumenAkreditasi = $this->repository->create($data);

            DB::commit();
            Log::info('Dokumen Akreditasi created successfully', [
                'dokumen_id' => $dokumenAkreditasi->id,
                'nama_dokumen' => $dokumenAkreditasi->nama_dokumen
            ]);

            return $dokumenAkreditasi;
        } catch (\Exception $e) {
            DB::rollBack();

            // Delete uploaded file if exists and there was an error
            if (isset($data['file_path']) && Storage::exists($data['file_path'])) {
                Storage::delete($data['file_path']);
            }

            Log::error('Failed to create Dokumen Akreditasi', [
                'error' => $e->getMessage(),
                'data' => $data
            ]);
            throw $e;
        }
    }

    /**
     * Update dokumen akreditasi
     */
    public function update(int $id, array $data, ?UploadedFile $file = null): DokumenAkreditasi
    {
        DB::beginTransaction();

        try {
            $dokumenAkreditasi = $this->repository->findById($id);

            if (!$dokumenAkreditasi) {
                throw new \Exception('Dokumen Akreditasi tidak ditemukan');
            }

            // Validate nomor dokumen if changed
            if (isset($data['nomor_dokumen']) &&
                $data['nomor_dokumen'] !== $dokumenAkreditasi->nomor_dokumen) {
                if ($this->repository->nomorDokumenExists($data['nomor_dokumen'], $id)) {
                    throw new \Exception('Nomor dokumen sudah digunakan');
                }
            }

            // Handle file upload if new file provided
            if ($file) {
                $oldFilePath = $dokumenAkreditasi->file_path;

                $fileData = $this->handleFileUpload($file, $dokumenAkreditasi->periode_akreditasi_id);
                $data = array_merge($data, $fileData);

                // Delete old file after successful upload
                if ($oldFilePath && Storage::exists($oldFilePath)) {
                    Storage::delete($oldFilePath);
                }
            }

            $dokumenAkreditasi = $this->repository->update($dokumenAkreditasi, $data);

            DB::commit();
            Log::info('Dokumen Akreditasi updated successfully', [
                'dokumen_id' => $id,
                'nama_dokumen' => $dokumenAkreditasi->nama_dokumen
            ]);

            return $dokumenAkreditasi;
        } catch (\Exception $e) {
            DB::rollBack();

            // Delete newly uploaded file if exists and there was an error
            if (isset($data['file_path']) &&
                $data['file_path'] !== $dokumenAkreditasi->file_path &&
                Storage::exists($data['file_path'])) {
                Storage::delete($data['file_path']);
            }

            Log::error('Failed to update Dokumen Akreditasi', [
                'error' => $e->getMessage(),
                'dokumen_id' => $id
            ]);
            throw $e;
        }
    }

    /**
     * Delete dokumen akreditasi
     */
    public function delete(int $id): bool
    {
        DB::beginTransaction();

        try {
            $dokumenAkreditasi = $this->repository->findById($id);

            if (!$dokumenAkreditasi) {
                throw new \Exception('Dokumen Akreditasi tidak ditemukan');
            }

            // Store file path before deletion
            $filePath = $dokumenAkreditasi->file_path;

            $result = $this->repository->delete($dokumenAkreditasi);

            // Delete physical file
            if ($filePath && Storage::exists($filePath)) {
                Storage::delete($filePath);
            }

            DB::commit();
            Log::info('Dokumen Akreditasi deleted successfully', [
                'dokumen_id' => $id,
                'file_deleted' => $filePath
            ]);

            return $result;
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Failed to delete Dokumen Akreditasi', [
                'error' => $e->getMessage(),
                'dokumen_id' => $id
            ]);
            throw $e;
        }
    }

    /**
     * Get statistics
     */
    public function getStatistics(): array
    {
        return $this->repository->getStatistics();
    }

    /**
     * Get dokumen by periode
     */
    public function getByPeriode(int $periodeId): Collection
    {
        return $this->repository->getByPeriode($periodeId);
    }

    /**
     * Get dokumen by butir
     */
    public function getByButir(int $butirId): Collection
    {
        return $this->repository->getByButir($butirId);
    }

    /**
     * Get dokumen by jenis dokumen
     */
    public function getByJenisDokumen(string $jenisDokumen): Collection
    {
        return $this->repository->getByJenisDokumen($jenisDokumen);
    }

    /**
     * Get unique jenis dokumen
     */
    public function getJenisDokumen(): Collection
    {
        return $this->repository->getJenisDokumen();
    }

    /**
     * Get unique file types
     */
    public function getFileTypes(): Collection
    {
        return $this->repository->getFileTypes();
    }

    /**
     * Attach dokumen to butir
     */
    public function attachToButir(int $dokumenId, int $butirId, array $pivotData = []): DokumenAkreditasi
    {
        DB::beginTransaction();

        try {
            $dokumen = $this->repository->findById($dokumenId);

            if (!$dokumen) {
                throw new \Exception('Dokumen Akreditasi tidak ditemukan');
            }

            // Check if already attached
            if ($dokumen->butirAkreditasis()->where('butir_akreditasi_id', $butirId)->exists()) {
                throw new \Exception('Dokumen sudah terkait dengan butir ini');
            }

            $this->repository->attachToButir($dokumen, $butirId, $pivotData);

            DB::commit();
            Log::info('Dokumen attached to Butir', [
                'dokumen_id' => $dokumenId,
                'butir_id' => $butirId
            ]);

            return $dokumen->fresh(['butirAkreditasis']);
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Failed to attach Dokumen to Butir', [
                'error' => $e->getMessage(),
                'dokumen_id' => $dokumenId,
                'butir_id' => $butirId
            ]);
            throw $e;
        }
    }

    /**
     * Detach dokumen from butir
     */
    public function detachFromButir(int $dokumenId, int $butirId): DokumenAkreditasi
    {
        DB::beginTransaction();

        try {
            $dokumen = $this->repository->findById($dokumenId);

            if (!$dokumen) {
                throw new \Exception('Dokumen Akreditasi tidak ditemukan');
            }

            $this->repository->detachFromButir($dokumen, $butirId);

            DB::commit();
            Log::info('Dokumen detached from Butir', [
                'dokumen_id' => $dokumenId,
                'butir_id' => $butirId
            ]);

            return $dokumen->fresh(['butirAkreditasis']);
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Failed to detach Dokumen from Butir', [
                'error' => $e->getMessage(),
                'dokumen_id' => $dokumenId,
                'butir_id' => $butirId
            ]);
            throw $e;
        }
    }

    /**
     * Sync dokumen with multiple butirs
     */
    public function syncWithButirs(int $dokumenId, array $butirIds): DokumenAkreditasi
    {
        DB::beginTransaction();

        try {
            $dokumen = $this->repository->findById($dokumenId);

            if (!$dokumen) {
                throw new \Exception('Dokumen Akreditasi tidak ditemukan');
            }

            $this->repository->syncWithButirs($dokumen, $butirIds);

            DB::commit();
            Log::info('Dokumen synced with Butirs', [
                'dokumen_id' => $dokumenId,
                'butir_count' => count($butirIds)
            ]);

            return $dokumen->fresh(['butirAkreditasis']);
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Failed to sync Dokumen with Butirs', [
                'error' => $e->getMessage(),
                'dokumen_id' => $dokumenId
            ]);
            throw $e;
        }
    }

    /**
     * Validate file before upload
     */
    public function validateFile(UploadedFile $file): array
    {
        $errors = [];

        // Maximum file size: 50MB
        $maxSize = 50 * 1024 * 1024; // 50MB in bytes
        if ($file->getSize() > $maxSize) {
            $errors[] = 'Ukuran file tidak boleh lebih dari 50MB';
        }

        // Allowed file types
        $allowedMimeTypes = [
            'application/pdf',
            'application/msword',
            'application/vnd.openxmlformats-officedocument.wordprocessingml.document',
            'application/vnd.ms-excel',
            'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet',
            'image/jpeg',
            'image/png',
            'image/jpg',
        ];

        if (!in_array($file->getMimeType(), $allowedMimeTypes)) {
            $errors[] = 'Tipe file tidak didukung. Hanya file PDF, Word, Excel, dan gambar (JPG, PNG) yang diperbolehkan';
        }

        return $errors;
    }

    /**
     * Handle file upload
     */
    public function handleFileUpload(UploadedFile $file, ?int $periodeId = null): array
    {
        // Validate file
        $errors = $this->validateFile($file);
        if (!empty($errors)) {
            throw new \Exception(implode(', ', $errors));
        }

        // Generate storage path
        $path = 'dokumen-akreditasi';
        if ($periodeId) {
            $path .= '/periode-' . $periodeId;
        }

        // Store file
        $storedPath = $file->store($path, 'public');

        if (!$storedPath) {
            throw new \Exception('Gagal menyimpan file');
        }

        return [
            'file_name' => $file->getClientOriginalName(),
            'file_path' => $storedPath,
            'file_type' => $file->getClientOriginalExtension(),
            'file_size' => $file->getSize(),
            'mime_type' => $file->getMimeType(),
        ];
    }

    /**
     * Get storage statistics for a periode
     */
    public function getStorageStatsByPeriode(int $periodeId): array
    {
        return [
            'total_dokumen' => $this->repository->getCountByPeriode($periodeId),
            'total_size' => $this->repository->getTotalSizeByPeriode($periodeId),
            'total_size_formatted' => $this->formatBytes($this->repository->getTotalSizeByPeriode($periodeId)),
        ];
    }

    /**
     * Format bytes to human readable format
     */
    protected function formatBytes(int $bytes): string
    {
        if ($bytes >= 1073741824) {
            return number_format($bytes / 1073741824, 2) . ' GB';
        } elseif ($bytes >= 1048576) {
            return number_format($bytes / 1048576, 2) . ' MB';
        } elseif ($bytes >= 1024) {
            return number_format($bytes / 1024, 2) . ' KB';
        } elseif ($bytes > 1) {
            return $bytes . ' bytes';
        } elseif ($bytes == 1) {
            return $bytes . ' byte';
        } else {
            return '0 bytes';
        }
    }
}
