<?php

namespace App\Services;

use App\Models\InstrumenAkreditasi;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Support\Collection;

class InstrumenAkreditasiService
{
    /**
     * Get all instrumen with filters and pagination
     */
    public function getAllInstrumen(array $filters = [], int $perPage = 15): LengthAwarePaginator
    {
        $query = InstrumenAkreditasi::query();

        // Filter by jenis
        if (!empty($filters['jenis'])) {
            $query->byJenis($filters['jenis']);
        }

        // Filter by lembaga
        if (!empty($filters['lembaga'])) {
            $query->byLembaga($filters['lembaga']);
        }

        // Filter by active status
        if (isset($filters['is_active'])) {
            $query->where('is_active', $filters['is_active']);
        }

        // Search
        if (!empty($filters['search'])) {
            $search = $filters['search'];
            $query->where(function ($q) use ($search) {
                $q->where('kode', 'ilike', "%{$search}%")
                  ->orWhere('nama', 'ilike', "%{$search}%")
                  ->orWhere('deskripsi', 'ilike', "%{$search}%");
            });
        }

        return $query->orderBy('tahun_berlaku', 'desc')
                     ->orderBy('kode', 'asc')
                     ->paginate($perPage);
    }

    /**
     * Get active instrumen
     */
    public function getActiveInstrumen(): Collection
    {
        return InstrumenAkreditasi::active()
            ->orderBy('tahun_berlaku', 'desc')
            ->orderBy('kode', 'asc')
            ->get();
    }

    /**
     * Get instrumen by ID
     */
    public function getInstrumenById(int $id): ?InstrumenAkreditasi
    {
        return InstrumenAkreditasi::find($id);
    }

    /**
     * Create new instrumen
     */
    public function createInstrumen(array $data): InstrumenAkreditasi
    {
        return InstrumenAkreditasi::create($data);
    }

    /**
     * Update instrumen
     */
    public function updateInstrumen(int $id, array $data): InstrumenAkreditasi
    {
        $instrumen = $this->getInstrumenById($id);

        if (!$instrumen) {
            throw new \Exception('Instrumen not found');
        }

        $instrumen->update($data);
        return $instrumen->fresh();
    }

    /**
     * Delete instrumen
     */
    public function deleteInstrumen(int $id): bool
    {
        $instrumen = $this->getInstrumenById($id);

        if (!$instrumen) {
            throw new \Exception('Instrumen not found');
        }

        return $instrumen->delete();
    }

    /**
     * Toggle active status
     */
    public function toggleActive(int $id): InstrumenAkreditasi
    {
        $instrumen = $this->getInstrumenById($id);

        if (!$instrumen) {
            throw new \Exception('Instrumen not found');
        }

        $instrumen->update([
            'is_active' => !$instrumen->is_active
        ]);

        return $instrumen->fresh();
    }

    /**
     * Get statistics
     */
    public function getStatistics(): array
    {
        return [
            'total' => InstrumenAkreditasi::count(),
            'active' => InstrumenAkreditasi::where('is_active', true)->count(),
            'inactive' => InstrumenAkreditasi::where('is_active', false)->count(),
            'by_jenis' => [
                'program_studi' => InstrumenAkreditasi::where('jenis', 'program_studi')->count(),
                'institusi' => InstrumenAkreditasi::where('jenis', 'institusi')->count(),
            ],
            'by_lembaga' => [
                'BAN-PT' => InstrumenAkreditasi::where('lembaga', 'BAN-PT')->count(),
                'LAM' => InstrumenAkreditasi::where('lembaga', 'LAM')->count(),
                'Internasional' => InstrumenAkreditasi::where('lembaga', 'Internasional')->count(),
            ],
        ];
    }
}
