<?php

namespace App\Repositories;

use App\Models\SpmiIndicator;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Pagination\LengthAwarePaginator;

class SpmiIndicatorRepository
{
    /**
     * Get all SPMI indicators with pagination and filters.
     */
    public function all(array $filters = [], int $perPage = 15): LengthAwarePaginator
    {
        $query = SpmiIndicator::with([
            'spmiStandard',
            'pic',
            'targets'
        ]);

        // Apply filters
        if (isset($filters['spmi_standard_id'])) {
            $query->where('spmi_standard_id', $filters['spmi_standard_id']);
        }

        if (isset($filters['is_active'])) {
            $query->where('is_active', $filters['is_active']);
        }

        if (isset($filters['measurement_type'])) {
            $query->where('measurement_type', $filters['measurement_type']);
        }

        if (isset($filters['pic_id'])) {
            $query->where('pic_id', $filters['pic_id']);
        }

        if (isset($filters['search'])) {
            $query->where(function ($q) use ($filters) {
                $q->where('code', 'like', '%' . $filters['search'] . '%')
                  ->orWhere('name', 'like', '%' . $filters['search'] . '%')
                  ->orWhere('description', 'like', '%' . $filters['search'] . '%');
            });
        }

        // Order by
        $orderBy = $filters['order_by'] ?? 'code';
        $orderDir = $filters['order_dir'] ?? 'asc';
        $query->orderBy($orderBy, $orderDir);

        return $query->paginate($perPage);
    }

    /**
     * Find SPMI indicator by ID with relationships.
     */
    public function findById(int $id, array $with = []): ?SpmiIndicator
    {
        $defaultWith = [
            'spmiStandard',
            'pic',
            'targets.tahunAkademik'
        ];

        $relations = !empty($with) ? $with : $defaultWith;

        return SpmiIndicator::with($relations)->find($id);
    }

    /**
     * Create new SPMI indicator.
     */
    public function create(array $data): SpmiIndicator
    {
        return SpmiIndicator::create($data);
    }

    /**
     * Update SPMI indicator.
     */
    public function update(SpmiIndicator $indicator, array $data): bool
    {
        return $indicator->update($data);
    }

    /**
     * Delete SPMI indicator.
     */
    public function delete(SpmiIndicator $indicator): bool
    {
        return $indicator->delete();
    }

    /**
     * Get indicators by SPMI standard.
     */
    public function getByStandard(int $standardId): Collection
    {
        return SpmiIndicator::where('spmi_standard_id', $standardId)
            ->with(['pic', 'targets'])
            ->orderBy('code', 'asc')
            ->get();
    }

    /**
     * Get only active indicators.
     */
    public function getActive(): Collection
    {
        return SpmiIndicator::active()
            ->with(['spmiStandard', 'pic'])
            ->orderBy('code', 'asc')
            ->get();
    }

    /**
     * Generate unique indicator code.
     * Format: IND-[CATEGORY]-[NUMBER] (e.g., IND-PEND-001)
     */
    public function generateIndicatorCode(string $category = 'GEN'): string
    {
        $category = strtoupper($category);
        $prefix = "IND-{$category}-";

        $lastIndicator = SpmiIndicator::where('code', 'like', $prefix . '%')
            ->orderBy('code', 'desc')
            ->first();

        if (!$lastIndicator) {
            return $prefix . '001';
        }

        $lastNumber = (int) substr($lastIndicator->code, -3);
        $newNumber = str_pad($lastNumber + 1, 3, '0', STR_PAD_LEFT);

        return $prefix . $newNumber;
    }

    /**
     * Check if code already exists.
     */
    public function codeExists(string $code, ?int $exceptId = null): bool
    {
        $query = SpmiIndicator::where('code', $code);

        if ($exceptId) {
            $query->where('id', '!=', $exceptId);
        }

        return $query->exists();
    }

    /**
     * Get statistics for indicators.
     * Returns counts by measurement_type, frequency, and total.
     */
    public function getStatistics(): array
    {
        $total = SpmiIndicator::count();
        $active = SpmiIndicator::where('is_active', true)->count();
        $inactive = SpmiIndicator::where('is_active', false)->count();

        $measurementTypes = SpmiIndicator::selectRaw('measurement_type, COUNT(*) as count')
            ->whereNotNull('measurement_type')
            ->groupBy('measurement_type')
            ->get()
            ->pluck('count', 'measurement_type')
            ->toArray();

        $frequencies = SpmiIndicator::selectRaw('frequency, COUNT(*) as count')
            ->whereNotNull('frequency')
            ->groupBy('frequency')
            ->get()
            ->pluck('count', 'frequency')
            ->toArray();

        return [
            'total' => $total,
            'active' => $active,
            'inactive' => $inactive,
            'kuantitatif' => $measurementTypes['kuantitatif'] ?? 0,
            'kualitatif' => $measurementTypes['kualitatif'] ?? 0,
            'measurement_types' => $measurementTypes,
            'frequencies' => $frequencies,
        ];
    }

    /**
     * Get indicators by measurement type.
     */
    public function getByMeasurementType(string $type): Collection
    {
        return SpmiIndicator::where('measurement_type', $type)
            ->with(['spmiStandard', 'pic'])
            ->orderBy('code', 'asc')
            ->get();
    }

    /**
     * Get indicators by PIC (Person In Charge).
     */
    public function getByPIC(int $picId): Collection
    {
        return SpmiIndicator::where('pic_id', $picId)
            ->with(['spmiStandard', 'targets'])
            ->orderBy('code', 'asc')
            ->get();
    }

    /**
     * Get indicators by frequency.
     */
    public function getByFrequency(string $frequency): Collection
    {
        return SpmiIndicator::where('frequency', $frequency)
            ->with(['spmiStandard', 'pic'])
            ->orderBy('code', 'asc')
            ->get();
    }

    /**
     * Get indicators with targets count.
     */
    public function getWithTargetsCounts(): Collection
    {
        return SpmiIndicator::withCount('targets')
            ->with(['spmiStandard', 'pic'])
            ->orderBy('code', 'asc')
            ->get();
    }
}
