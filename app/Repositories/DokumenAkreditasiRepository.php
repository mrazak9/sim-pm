<?php

namespace App\Repositories;

use App\Models\DokumenAkreditasi;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Support\Collection;

class DokumenAkreditasiRepository
{
    protected DokumenAkreditasi $model;

    public function __construct(DokumenAkreditasi $model)
    {
        $this->model = $model;
    }

    /**
     * Get all dokumen akreditasi
     */
    public function all(): Collection
    {
        return $this->model->with(['periodeAkreditasi', 'uploader', 'butirAkreditasis'])
            ->orderBy('created_at', 'desc')
            ->get();
    }

    /**
     * Get all dokumen akreditasi with filters and pagination
     */
    public function paginate(array $filters = [], int $perPage = 15): LengthAwarePaginator
    {
        $query = $this->model->with(['periodeAkreditasi', 'uploader', 'butirAkreditasis']);

        // Filter by periode_akreditasi_id
        if (isset($filters['periode_akreditasi_id'])) {
            $query->where('periode_akreditasi_id', $filters['periode_akreditasi_id']);
        }

        // Filter by jenis_dokumen
        if (isset($filters['jenis_dokumen'])) {
            $query->where('jenis_dokumen', $filters['jenis_dokumen']);
        }

        // Filter by file_type
        if (isset($filters['file_type'])) {
            $query->where('file_type', $filters['file_type']);
        }

        // Filter by uploaded_by
        if (isset($filters['uploaded_by'])) {
            $query->where('uploaded_by', $filters['uploaded_by']);
        }

        // Search
        if (isset($filters['search'])) {
            $search = $filters['search'];
            $query->where(function($q) use ($search) {
                $q->where('nama_dokumen', 'LIKE', "%{$search}%")
                  ->orWhere('nomor_dokumen', 'LIKE', "%{$search}%")
                  ->orWhere('deskripsi', 'LIKE', "%{$search}%");
            });
        }

        return $query->orderBy('created_at', 'desc')->paginate($perPage);
    }

    /**
     * Find dokumen akreditasi by ID
     */
    public function findById(int $id, array $with = []): ?DokumenAkreditasi
    {
        $query = $this->model->query();

        $defaultWith = ['periodeAkreditasi', 'uploader', 'butirAkreditasis'];

        if (!empty($with)) {
            $query->with($with);
        } else {
            $query->with($defaultWith);
        }

        return $query->find($id);
    }

    /**
     * Create new dokumen akreditasi
     */
    public function create(array $data): DokumenAkreditasi
    {
        return $this->model->create($data);
    }

    /**
     * Update dokumen akreditasi
     */
    public function update(DokumenAkreditasi $dokumenAkreditasi, array $data): DokumenAkreditasi
    {
        $dokumenAkreditasi->update($data);
        return $dokumenAkreditasi->fresh(['periodeAkreditasi', 'uploader', 'butirAkreditasis']);
    }

    /**
     * Delete dokumen akreditasi
     */
    public function delete(DokumenAkreditasi $dokumenAkreditasi): bool
    {
        return $dokumenAkreditasi->delete();
    }

    /**
     * Get dokumen akreditasi by periode
     */
    public function getByPeriode(int $periodeId): Collection
    {
        return $this->model->byPeriode($periodeId)
            ->with(['uploader', 'butirAkreditasis'])
            ->orderBy('created_at', 'desc')
            ->get();
    }

    /**
     * Get dokumen akreditasi by butir
     */
    public function getByButir(int $butirId): Collection
    {
        return $this->model->whereHas('butirAkreditasis', function($query) use ($butirId) {
                $query->where('butir_akreditasi_id', $butirId);
            })
            ->with(['periodeAkreditasi', 'uploader', 'butirAkreditasis'])
            ->orderBy('created_at', 'desc')
            ->get();
    }

    /**
     * Get active dokumen akreditasi (files that exist)
     */
    public function getActive(): Collection
    {
        return $this->model->whereNotNull('file_path')
            ->with(['periodeAkreditasi', 'uploader', 'butirAkreditasis'])
            ->orderBy('created_at', 'desc')
            ->get();
    }

    /**
     * Get dokumen by jenis dokumen
     */
    public function getByJenisDokumen(string $jenisDokumen): Collection
    {
        return $this->model->byJenisDokumen($jenisDokumen)
            ->with(['periodeAkreditasi', 'uploader', 'butirAkreditasis'])
            ->orderBy('created_at', 'desc')
            ->get();
    }

    /**
     * Get dokumen by file type
     */
    public function getByFileType(string $fileType): Collection
    {
        return $this->model->byFileType($fileType)
            ->with(['periodeAkreditasi', 'uploader', 'butirAkreditasis'])
            ->orderBy('created_at', 'desc')
            ->get();
    }

    /**
     * Get dokumen by uploader
     */
    public function getByUploader(int $uploaderId): Collection
    {
        return $this->model->where('uploaded_by', $uploaderId)
            ->with(['periodeAkreditasi', 'butirAkreditasis'])
            ->orderBy('created_at', 'desc')
            ->get();
    }

    /**
     * Get statistics
     */
    public function getStatistics(): array
    {
        return [
            'total' => $this->model->count(),
            'total_size' => $this->model->sum('file_size'),
            'by_jenis_dokumen' => $this->model->selectRaw('jenis_dokumen, COUNT(*) as count')
                ->whereNotNull('jenis_dokumen')
                ->groupBy('jenis_dokumen')
                ->pluck('count', 'jenis_dokumen')
                ->toArray(),
            'by_file_type' => $this->model->selectRaw('file_type, COUNT(*) as count')
                ->whereNotNull('file_type')
                ->groupBy('file_type')
                ->pluck('count', 'file_type')
                ->toArray(),
            'total_size_formatted' => $this->formatBytes($this->model->sum('file_size')),
        ];
    }

    /**
     * Get unique jenis dokumen
     */
    public function getJenisDokumen(): Collection
    {
        return $this->model->whereNotNull('jenis_dokumen')
            ->distinct()
            ->orderBy('jenis_dokumen')
            ->pluck('jenis_dokumen');
    }

    /**
     * Get unique file types
     */
    public function getFileTypes(): Collection
    {
        return $this->model->whereNotNull('file_type')
            ->distinct()
            ->orderBy('file_type')
            ->pluck('file_type');
    }

    /**
     * Check if nomor dokumen already exists
     */
    public function nomorDokumenExists(string $nomorDokumen, ?int $exceptId = null): bool
    {
        $query = $this->model->where('nomor_dokumen', $nomorDokumen);

        if ($exceptId) {
            $query->where('id', '!=', $exceptId);
        }

        return $query->exists();
    }

    /**
     * Get total storage size for a periode
     */
    public function getTotalSizeByPeriode(int $periodeId): int
    {
        return $this->model->where('periode_akreditasi_id', $periodeId)
            ->sum('file_size');
    }

    /**
     * Get dokumen count by periode
     */
    public function getCountByPeriode(int $periodeId): int
    {
        return $this->model->where('periode_akreditasi_id', $periodeId)->count();
    }

    /**
     * Attach dokumen to butir
     */
    public function attachToButir(DokumenAkreditasi $dokumen, int $butirId, array $pivotData = []): void
    {
        $dokumen->butirAkreditasis()->attach($butirId, $pivotData);
    }

    /**
     * Detach dokumen from butir
     */
    public function detachFromButir(DokumenAkreditasi $dokumen, int $butirId): void
    {
        $dokumen->butirAkreditasis()->detach($butirId);
    }

    /**
     * Sync dokumen with butirs
     */
    public function syncWithButirs(DokumenAkreditasi $dokumen, array $butirIds): void
    {
        $dokumen->butirAkreditasis()->sync($butirIds);
    }

    /**
     * Format bytes to human readable format
     */
    private function formatBytes(int $bytes): string
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
