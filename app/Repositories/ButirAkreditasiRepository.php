<?php

namespace App\Repositories;

use App\Models\ButirAkreditasi;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Support\Collection;

class ButirAkreditasiRepository
{
    protected ButirAkreditasi $model;

    public function __construct(ButirAkreditasi $model)
    {
        $this->model = $model;
    }

    /**
     * Get all butir akreditasi
     */
    public function all(): Collection
    {
        return $this->model->with(['parent', 'children'])
            ->orderBy('urutan')
            ->get();
    }

    /**
     * Get all butir akreditasi with filters and pagination
     */
    public function paginate(array $filters = [], int $perPage = 15): LengthAwarePaginator
    {
        $query = $this->model->with(['parent', 'children']);

        // Filter by instrumen
        if (isset($filters['instrumen'])) {
            $query->where('instrumen', $filters['instrumen']);
        }

        // Filter by kategori
        if (isset($filters['kategori'])) {
            $query->where('kategori', $filters['kategori']);
        }

        // Filter by is_mandatory
        if (isset($filters['is_mandatory'])) {
            $query->where('is_mandatory', $filters['is_mandatory']);
        }

        // Filter by periode_akreditasi_id
        if (isset($filters['periode_akreditasi_id'])) {
            $query->where('periode_akreditasi_id', $filters['periode_akreditasi_id']);
        }

        // Filter template only (butir without periode)
        if (isset($filters['template_only']) && $filters['template_only']) {
            $query->whereNull('periode_akreditasi_id');
        }

        // Filter by parent_id (get children of specific parent)
        if (isset($filters['parent_id'])) {
            if ($filters['parent_id'] === 'null') {
                $query->whereNull('parent_id');
            } else {
                $query->where('parent_id', $filters['parent_id']);
            }
        }

        // Search
        if (isset($filters['search'])) {
            $search = $filters['search'];
            $query->where(function($q) use ($search) {
                $q->where('nama', 'LIKE', "%{$search}%")
                  ->orWhere('kode', 'LIKE', "%{$search}%")
                  ->orWhere('deskripsi', 'LIKE', "%{$search}%");
            });
        }

        return $query->orderBy('urutan')->paginate($perPage);
    }

    /**
     * Find butir akreditasi by ID
     */
    public function findById(int $id, array $with = []): ?ButirAkreditasi
    {
        $query = $this->model->query();

        $defaultWith = ['parent', 'children', 'pengisianButirs', 'dokumenAkreditasis'];

        if (!empty($with)) {
            $query->with($with);
        } else {
            $query->with($defaultWith);
        }

        return $query->find($id);
    }

    /**
     * Create new butir akreditasi
     */
    public function create(array $data): ButirAkreditasi
    {
        return $this->model->create($data);
    }

    /**
     * Update butir akreditasi
     */
    public function update(ButirAkreditasi $butirAkreditasi, array $data): ButirAkreditasi
    {
        $butirAkreditasi->update($data);
        return $butirAkreditasi->fresh(['parent', 'children', 'pengisianButirs', 'dokumenAkreditasis']);
    }

    /**
     * Delete butir akreditasi
     */
    public function delete(ButirAkreditasi $butirAkreditasi): bool
    {
        return $butirAkreditasi->delete();
    }

    /**
     * Get butir akreditasi by kategori
     */
    public function getByKategori(string $kategori): Collection
    {
        return $this->model->byKategori($kategori)
            ->with(['parent', 'children'])
            ->orderBy('urutan')
            ->get();
    }

    /**
     * Get butir akreditasi by instrumen
     */
    public function getByInstrumen(string $instrumen): Collection
    {
        return $this->model->byInstrumen($instrumen)
            ->with(['parent', 'children'])
            ->orderBy('urutan')
            ->get();
    }

    /**
     * Get active/mandatory butir akreditasi
     */
    public function getActive(): Collection
    {
        return $this->model->mandatory()
            ->with(['parent', 'children'])
            ->orderBy('urutan')
            ->get();
    }

    /**
     * Get parent butir only (no children)
     */
    public function getParentOnly(): Collection
    {
        return $this->model->parentOnly()
            ->with(['children'])
            ->orderBy('urutan')
            ->get();
    }

    /**
     * Get children of a specific parent
     */
    public function getChildren(int $parentId): Collection
    {
        return $this->model->where('parent_id', $parentId)
            ->orderBy('urutan')
            ->get();
    }

    /**
     * Check if butir has children
     */
    public function hasChildren(int $id): bool
    {
        return $this->model->where('parent_id', $id)->exists();
    }

    /**
     * Get butir statistics
     */
    public function getStatistics(): array
    {
        return [
            'total' => $this->model->count(),
            'mandatory' => $this->model->where('is_mandatory', true)->count(),
            'optional' => $this->model->where('is_mandatory', false)->count(),
            'parent_only' => $this->model->whereNull('parent_id')->count(),
            'by_instrumen' => $this->model->selectRaw('instrumen, COUNT(*) as count')
                ->groupBy('instrumen')
                ->pluck('count', 'instrumen')
                ->toArray(),
            'by_kategori' => $this->model->selectRaw('kategori, COUNT(*) as count')
                ->whereNotNull('kategori')
                ->groupBy('kategori')
                ->pluck('count', 'kategori')
                ->toArray(),
        ];
    }

    /**
     * Get unique categories
     */
    public function getCategories(): Collection
    {
        return $this->model->whereNotNull('kategori')
            ->distinct()
            ->orderBy('kategori')
            ->pluck('kategori');
    }

    /**
     * Get unique instruments
     */
    public function getInstruments(): Collection
    {
        return $this->model->whereNotNull('instrumen')
            ->distinct()
            ->orderBy('instrumen')
            ->pluck('instrumen');
    }

    /**
     * Check if kode already exists
     */
    public function kodeExists(string $kode, ?int $exceptId = null): bool
    {
        $query = $this->model->where('kode', $kode);

        if ($exceptId) {
            $query->where('id', '!=', $exceptId);
        }

        return $query->exists();
    }

    /**
     * Get next urutan number for a specific parent or root level
     */
    public function getNextUrutan(?int $parentId = null): int
    {
        $query = $this->model->query();

        if ($parentId) {
            $query->where('parent_id', $parentId);
        } else {
            $query->whereNull('parent_id');
        }

        $maxUrutan = $query->max('urutan');
        return ($maxUrutan ?? 0) + 1;
    }
}
