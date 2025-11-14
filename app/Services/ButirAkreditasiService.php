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
     * Get butir akreditasi by ID
     */
    public function findById(int $id): ?ButirAkreditasi
    {
        return $this->repository->findById($id);
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
}
