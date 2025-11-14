<?php

namespace App\Services;

use App\Models\ButirAkreditasi;
use App\Repositories\ButirAkreditasiRepository;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class ButirAkreditasiService
{
    protected ButirAkreditasiRepository $repository;

    public function __construct(ButirAkreditasiRepository $repository)
    {
        $this->repository = $repository;
    }

    /**
     * Get all butir akreditasi
     */
    public function all(): Collection
    {
        return $this->repository->all();
    }

    /**
     * Get all butir akreditasi with filters and pagination
     */
    public function paginate(array $filters = [], int $perPage = 15): LengthAwarePaginator
    {
        return $this->repository->paginate($filters, $perPage);
    }

    /**
     * Get all butir akreditasi with filters, sorting, and optional pagination
     * This is an alias for paginate with additional sorting support
     */
    public function getAllButirAkreditasi(
        array $filters = [],
        $perPage = 15,
        string $sortBy = 'urutan',
        string $sortOrder = 'asc'
    ) {
        // Add sorting to filters
        $filters['sort_by'] = $sortBy;
        $filters['sort_order'] = $sortOrder;

        // Handle 'all' case for per_page - return Collection instead of pagination
        if ($perPage === 'all') {
            $query = ButirAkreditasi::with(['parent', 'children']);

            // Apply same filters as paginate
            if (isset($filters['instrumen'])) {
                $query->where('instrumen', $filters['instrumen']);
            }
            if (isset($filters['kategori'])) {
                $query->where('kategori', $filters['kategori']);
            }
            if (isset($filters['is_mandatory'])) {
                $query->where('is_mandatory', $filters['is_mandatory']);
            }
            if (isset($filters['periode_akreditasi_id'])) {
                $query->where('periode_akreditasi_id', $filters['periode_akreditasi_id']);
            }
            if (isset($filters['template_only']) && $filters['template_only']) {
                $query->whereNull('periode_akreditasi_id');
            }
            if (isset($filters['parent_id'])) {
                if ($filters['parent_id'] === 'null') {
                    $query->whereNull('parent_id');
                } else {
                    $query->where('parent_id', $filters['parent_id']);
                }
            }
            if (isset($filters['search'])) {
                $search = $filters['search'];
                $query->where(function($q) use ($search) {
                    $q->where('nama', 'LIKE', "%{$search}%")
                      ->orWhere('kode', 'LIKE', "%{$search}%")
                      ->orWhere('deskripsi', 'LIKE', "%{$search}%");
                });
            }

            return $query->orderBy($sortBy, $sortOrder)->get();
        }

        return $this->repository->paginate($filters, $perPage);
    }

    /**
     * Get butir akreditasi by ID
     */
    public function findById(int $id): ?ButirAkreditasi
    {
        return $this->repository->findById($id);
    }

    /**
     * Alias for findById - used by controller
     */
    public function getButirAkreditasiById(int $id): ?ButirAkreditasi
    {
        return $this->findById($id);
    }

    /**
     * Create new butir akreditasi
     */
    public function create(array $data): ButirAkreditasi
    {
        DB::beginTransaction();

        try {
            // Validate kode uniqueness
            if ($this->repository->kodeExists($data['kode'])) {
                throw new \Exception('Kode Butir Akreditasi sudah digunakan');
            }

            // Validate parent if provided
            if (isset($data['parent_id'])) {
                $parent = $this->repository->findById($data['parent_id']);
                if (!$parent) {
                    throw new \Exception('Parent Butir tidak ditemukan');
                }

                // Check if parent is in same instrumen
                if (isset($data['instrumen']) && $parent->instrumen !== $data['instrumen']) {
                    throw new \Exception('Parent Butir harus memiliki instrumen yang sama');
                }
            }

            // Auto-generate urutan if not provided
            if (!isset($data['urutan'])) {
                $data['urutan'] = $this->repository->getNextUrutan($data['parent_id'] ?? null);
            }

            // Set default is_mandatory if not provided
            if (!isset($data['is_mandatory'])) {
                $data['is_mandatory'] = true;
            }

            $butirAkreditasi = $this->repository->create($data);

            DB::commit();
            Log::info('Butir Akreditasi created successfully', [
                'butir_id' => $butirAkreditasi->id,
                'kode' => $butirAkreditasi->kode
            ]);

            return $butirAkreditasi;
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Failed to create Butir Akreditasi', [
                'error' => $e->getMessage(),
                'data' => $data
            ]);
            throw $e;
        }
    }

    /**
     * Update butir akreditasi
     */
    public function update(int $id, array $data): ButirAkreditasi
    {
        DB::beginTransaction();

        try {
            $butirAkreditasi = $this->repository->findById($id);

            if (!$butirAkreditasi) {
                throw new \Exception('Butir Akreditasi tidak ditemukan');
            }

            // Check if kode already exists (excluding current butir)
            if (isset($data['kode']) && $this->repository->kodeExists($data['kode'], $id)) {
                throw new \Exception('Kode Butir Akreditasi sudah digunakan');
            }

            // Validate parent if provided
            if (isset($data['parent_id'])) {
                // Cannot set itself as parent
                if ($data['parent_id'] == $id) {
                    throw new \Exception('Butir tidak dapat menjadi parent dari dirinya sendiri');
                }

                // Check for circular reference
                if ($this->wouldCreateCircularReference($id, $data['parent_id'])) {
                    throw new \Exception('Tidak dapat membuat referensi circular pada hierarki butir');
                }

                $parent = $this->repository->findById($data['parent_id']);
                if (!$parent) {
                    throw new \Exception('Parent Butir tidak ditemukan');
                }

                // Check if parent is in same instrumen
                if (isset($data['instrumen']) && $parent->instrumen !== $data['instrumen']) {
                    throw new \Exception('Parent Butir harus memiliki instrumen yang sama');
                }
            }

            $butirAkreditasi = $this->repository->update($butirAkreditasi, $data);

            DB::commit();
            Log::info('Butir Akreditasi updated successfully', [
                'butir_id' => $id,
                'kode' => $butirAkreditasi->kode
            ]);

            return $butirAkreditasi;
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Failed to update Butir Akreditasi', [
                'error' => $e->getMessage(),
                'butir_id' => $id
            ]);
            throw $e;
        }
    }

    /**
     * Delete butir akreditasi
     */
    public function delete(int $id): bool
    {
        DB::beginTransaction();

        try {
            $butirAkreditasi = $this->repository->findById($id);

            if (!$butirAkreditasi) {
                throw new \Exception('Butir Akreditasi tidak ditemukan');
            }

            // Check if has children
            if ($this->repository->hasChildren($id)) {
                throw new \Exception('Butir Akreditasi tidak dapat dihapus karena memiliki sub butir');
            }

            // Check if has associated pengisian butir
            if ($butirAkreditasi->pengisianButirs()->count() > 0) {
                throw new \Exception('Butir Akreditasi tidak dapat dihapus karena memiliki pengisian butir');
            }

            // Check if has associated dokumen
            if ($butirAkreditasi->dokumenAkreditasis()->count() > 0) {
                throw new \Exception('Butir Akreditasi tidak dapat dihapus karena memiliki dokumen terkait');
            }

            $result = $this->repository->delete($butirAkreditasi);

            DB::commit();
            Log::info('Butir Akreditasi deleted successfully', ['butir_id' => $id]);

            return $result;
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Failed to delete Butir Akreditasi', [
                'error' => $e->getMessage(),
                'butir_id' => $id
            ]);
            throw $e;
        }
    }

    /**
     * Get butir by kategori
     */
    public function getByKategori(string $kategori): Collection
    {
        return $this->repository->getByKategori($kategori);
    }

    /**
     * Get butir by instrumen
     */
    public function getByInstrumen(string $instrumen): Collection
    {
        return $this->repository->getByInstrumen($instrumen);
    }

    /**
     * Get active/mandatory butir
     */
    public function getActive(): Collection
    {
        return $this->repository->getActive();
    }

    /**
     * Get parent butir only
     */
    public function getParentOnly(): Collection
    {
        return $this->repository->getParentOnly();
    }

    /**
     * Get children of a specific parent
     */
    public function getChildren(int $parentId): Collection
    {
        return $this->repository->getChildren($parentId);
    }

    /**
     * Get statistics
     */
    public function getStatistics(): array
    {
        return $this->repository->getStatistics();
    }

    /**
     * Get unique categories
     */
    public function getCategories(): Collection
    {
        return $this->repository->getCategories();
    }

    /**
     * Get unique instruments
     */
    public function getInstruments(): Collection
    {
        return $this->repository->getInstruments();
    }

    /**
     * Validate kode uniqueness
     */
    public function validateKode(string $kode, ?int $exceptId = null): bool
    {
        return !$this->repository->kodeExists($kode, $exceptId);
    }

    /**
     * Check if setting a parent would create circular reference
     */
    protected function wouldCreateCircularReference(int $childId, int $newParentId): bool
    {
        // Get all descendants of the child
        $descendants = $this->getAllDescendants($childId);

        // If new parent is in descendants, it would create circular reference
        return in_array($newParentId, $descendants);
    }

    /**
     * Get all descendant IDs of a butir
     */
    protected function getAllDescendants(int $butirId): array
    {
        $descendants = [];
        $children = $this->repository->getChildren($butirId);

        foreach ($children as $child) {
            $descendants[] = $child->id;
            $descendants = array_merge($descendants, $this->getAllDescendants($child->id));
        }

        return $descendants;
    }

    /**
     * Toggle mandatory status
     */
    public function toggleMandatoryStatus(int $id): ButirAkreditasi
    {
        DB::beginTransaction();

        try {
            $butirAkreditasi = $this->repository->findById($id);

            if (!$butirAkreditasi) {
                throw new \Exception('Butir Akreditasi tidak ditemukan');
            }

            $butirAkreditasi = $this->repository->update($butirAkreditasi, [
                'is_mandatory' => !$butirAkreditasi->is_mandatory
            ]);

            DB::commit();
            Log::info('Butir Akreditasi mandatory status toggled', [
                'butir_id' => $id,
                'new_status' => $butirAkreditasi->is_mandatory
            ]);

            return $butirAkreditasi;
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Failed to toggle Butir Akreditasi mandatory status', [
                'error' => $e->getMessage(),
                'butir_id' => $id
            ]);
            throw $e;
        }
    }

    /**
     * Copy butir from template to periode
     * This will copy all template butir with matching instrumen to a specific periode
     *
     * @param int $periodeId Target periode ID
     * @param string $instrumen Instrumen to filter templates
     * @return array ['copied_count' => int, 'butirs' => Collection]
     */
    public function copyButirFromTemplate(int $periodeId, string $instrumen): array
    {
        DB::beginTransaction();

        try {
            // Get all template butir with matching instrumen
            $templateButirs = ButirAkreditasi::templatesOnly()
                ->byInstrumen($instrumen)
                ->orderBy('parent_id')
                ->orderBy('urutan')
                ->get();

            if ($templateButirs->isEmpty()) {
                throw new \Exception('Tidak ada template butir untuk instrumen ' . $instrumen);
            }

            // Check if periode already has butir
            $existingCount = ButirAkreditasi::byPeriode($periodeId)->count();
            if ($existingCount > 0) {
                throw new \Exception('Periode ini sudah memiliki butir. Hapus butir existing terlebih dahulu atau gunakan opsi copy dari periode lain.');
            }

            $copiedButirs = collect();
            $parentMap = []; // Map old parent_id to new parent_id

            foreach ($templateButirs as $template) {
                // Prepare data for new butir
                $newButirData = [
                    'kode' => $template->kode,
                    'nama' => $template->nama,
                    'deskripsi' => $template->deskripsi,
                    'instrumen' => $template->instrumen,
                    'periode_akreditasi_id' => $periodeId,
                    'template_id' => $template->id,
                    'kategori' => $template->kategori,
                    'bobot' => $template->bobot,
                    'urutan' => $template->urutan,
                    'is_mandatory' => $template->is_mandatory,
                    'metadata' => $template->metadata,
                    'parent_id' => null, // Will be set later if needed
                ];

                // Map parent_id if exists
                if ($template->parent_id && isset($parentMap[$template->parent_id])) {
                    $newButirData['parent_id'] = $parentMap[$template->parent_id];
                }

                // Create new butir (skip kode uniqueness check for copies)
                $newButir = ButirAkreditasi::create($newButirData);
                $copiedButirs->push($newButir);

                // Store mapping for children
                $parentMap[$template->id] = $newButir->id;
            }

            DB::commit();
            Log::info('Butir copied from template successfully', [
                'periode_id' => $periodeId,
                'instrumen' => $instrumen,
                'copied_count' => $copiedButirs->count()
            ]);

            return [
                'copied_count' => $copiedButirs->count(),
                'butirs' => $copiedButirs,
            ];
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Failed to copy butir from template', [
                'error' => $e->getMessage(),
                'periode_id' => $periodeId,
                'instrumen' => $instrumen
            ]);
            throw $e;
        }
    }

    /**
     * Copy butir from another periode
     *
     * @param int $sourcePeriodeId Source periode ID
     * @param int $targetPeriodeId Target periode ID
     * @return array ['copied_count' => int, 'butirs' => Collection]
     */
    public function copyButirFromPeriode(int $sourcePeriodeId, int $targetPeriodeId): array
    {
        DB::beginTransaction();

        try {
            // Get all butir from source periode
            $sourceButirs = ButirAkreditasi::byPeriode($sourcePeriodeId)
                ->orderBy('parent_id')
                ->orderBy('urutan')
                ->get();

            if ($sourceButirs->isEmpty()) {
                throw new \Exception('Periode sumber tidak memiliki butir');
            }

            // Check if target periode already has butir
            $existingCount = ButirAkreditasi::byPeriode($targetPeriodeId)->count();
            if ($existingCount > 0) {
                throw new \Exception('Periode target sudah memiliki butir. Hapus butir existing terlebih dahulu.');
            }

            $copiedButirs = collect();
            $parentMap = []; // Map old parent_id to new parent_id

            foreach ($sourceButirs as $source) {
                // Prepare data for new butir
                $newButirData = [
                    'kode' => $source->kode,
                    'nama' => $source->nama,
                    'deskripsi' => $source->deskripsi,
                    'instrumen' => $source->instrumen,
                    'periode_akreditasi_id' => $targetPeriodeId,
                    'template_id' => $source->template_id, // Keep reference to original template
                    'kategori' => $source->kategori,
                    'bobot' => $source->bobot,
                    'urutan' => $source->urutan,
                    'is_mandatory' => $source->is_mandatory,
                    'metadata' => $source->metadata,
                    'parent_id' => null, // Will be set later if needed
                ];

                // Map parent_id if exists
                if ($source->parent_id && isset($parentMap[$source->parent_id])) {
                    $newButirData['parent_id'] = $parentMap[$source->parent_id];
                }

                // Create new butir
                $newButir = ButirAkreditasi::create($newButirData);
                $copiedButirs->push($newButir);

                // Store mapping for children
                $parentMap[$source->id] = $newButir->id;
            }

            DB::commit();
            Log::info('Butir copied from periode successfully', [
                'source_periode_id' => $sourcePeriodeId,
                'target_periode_id' => $targetPeriodeId,
                'copied_count' => $copiedButirs->count()
            ]);

            return [
                'copied_count' => $copiedButirs->count(),
                'butirs' => $copiedButirs,
            ];
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Failed to copy butir from periode', [
                'error' => $e->getMessage(),
                'source_periode_id' => $sourcePeriodeId,
                'target_periode_id' => $targetPeriodeId
            ]);
            throw $e;
        }
    }

    /**
     * Get template butir by instrumen
     */
    public function getTemplatesByInstrumen(string $instrumen): Collection
    {
        return ButirAkreditasi::templatesOnly()
            ->byInstrumen($instrumen)
            ->orderBy('urutan')
            ->get();
    }

    /**
     * Get butir by periode
     */
    public function getByPeriode(int $periodeId): Collection
    {
        return ButirAkreditasi::byPeriode($periodeId)
            ->orderBy('urutan')
            ->get();
    }

    /**
     * Count template butir by instrumen
     */
    public function countTemplatesByInstrumen(string $instrumen): int
    {
        return ButirAkreditasi::templatesOnly()
            ->byInstrumen($instrumen)
            ->count();
    }

    /**
     * Count butir by periode
     */
    public function countByPeriode(int $periodeId): int
    {
        return ButirAkreditasi::byPeriode($periodeId)->count();
    }

    // ==================== Controller Alias Methods ====================

    /**
     * Alias for create - used by controller
     */
    public function createButirAkreditasi(array $data): ButirAkreditasi
    {
        return $this->create($data);
    }

    /**
     * Alias for update - used by controller
     */
    public function updateButirAkreditasi(int $id, array $data): ButirAkreditasi
    {
        return $this->update($id, $data);
    }

    /**
     * Alias for delete - used by controller
     */
    public function deleteButirAkreditasi(int $id): bool
    {
        return $this->delete($id);
    }

    /**
     * Get butir by kategori - used by controller
     * Returns grouped data by kategori
     */
    public function getButirByKategori(string $instrumen): array
    {
        $butirs = $this->repository->getByInstrumen($instrumen);

        // Group by kategori
        $grouped = $butirs->groupBy('kategori');

        $result = [];
        foreach ($grouped as $kategori => $items) {
            $result[] = [
                'kategori' => $kategori,
                'butirs' => $items->values()->all(),
                'count' => $items->count()
            ];
        }

        return $result;
    }

    /**
     * Alias for getByInstrumen - used by controller
     */
    public function getButirByInstrumen(string $instrumen): Collection
    {
        return $this->getByInstrumen($instrumen);
    }

    /**
     * Get list of kategori for specific instrumen
     */
    public function getKategoriList(string $instrumen): Collection
    {
        return ButirAkreditasi::where('instrumen', $instrumen)
            ->whereNotNull('kategori')
            ->distinct()
            ->orderBy('kategori')
            ->pluck('kategori');
    }

    /**
     * Get list of available instrumen
     */
    public function getInstrumenList(): Collection
    {
        return $this->repository->getInstruments();
    }
}
