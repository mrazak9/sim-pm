<?php

namespace App\Services;

use App\Models\ButirData;
use App\Models\ButirColumnMapping;
use App\Models\PengisianButir;
use Illuminate\Support\Facades\DB;

class ButirDataService
{
    /**
     * Create single row data
     *
     * @param int $pengisianButirId
     * @param array $data Named fields
     * @return ButirData
     */
    public function create(int $pengisianButirId, array $data): ButirData
    {
        return ButirData::fromNamedFields($data, $pengisianButirId);
    }

    /**
     * Update single row data
     *
     * @param int $id
     * @param array $data Named fields
     * @return ButirData
     */
    public function update(int $id, array $data): ButirData
    {
        $butirData = ButirData::findOrFail($id);
        $butirData->updateFromNamedFields($data);

        return $butirData->fresh();
    }

    /**
     * Delete row
     *
     * @param int $id
     * @return bool
     */
    public function delete(int $id): bool
    {
        return ButirData::findOrFail($id)->delete();
    }

    /**
     * Bulk create rows
     *
     * @param int $pengisianButirId
     * @param array $rows Array of named fields
     * @return array Created ButirData instances
     */
    public function bulkCreate(int $pengisianButirId, array $rows): array
    {
        DB::beginTransaction();

        try {
            $created = [];

            foreach ($rows as $index => $row) {
                $row['row_number'] = $row['row_number'] ?? ($index + 1);
                $created[] = $this->create($pengisianButirId, $row);
            }

            DB::commit();

            return $created;

        } catch (\Exception $e) {
            DB::rollBack();
            throw $e;
        }
    }

    /**
     * Sync data (replace all existing data with new data)
     * This will delete all existing rows and create new ones
     *
     * @param int $pengisianButirId
     * @param array $rows Array of named fields
     * @return array Created ButirData instances
     */
    public function syncData(int $pengisianButirId, array $rows): array
    {
        DB::beginTransaction();

        try {
            // Delete all existing data for this pengisian butir
            ButirData::byPengisian($pengisianButirId)->delete();

            // Create new rows
            $created = [];
            foreach ($rows as $index => $row) {
                $row['row_number'] = $row['row_number'] ?? ($index + 1);
                $created[] = $this->create($pengisianButirId, $row);
            }

            DB::commit();

            return $created;

        } catch (\Exception $e) {
            DB::rollBack();
            throw $e;
        }
    }

    /**
     * Get all data for a pengisian butir
     *
     * @param int $pengisianButirId
     * @param bool $asNamedFields Transform to named fields
     * @return \Illuminate\Database\Eloquent\Collection|array
     */
    public function getByPengisian(int $pengisianButirId, bool $asNamedFields = true)
    {
        $data = ButirData::byPengisian($pengisianButirId)
            ->orderBy('row_number')
            ->get();

        if ($asNamedFields) {
            return $data->map->toNamedFields();
        }

        return $data;
    }

    /**
     * Query by field name
     *
     * @param int $butirId
     * @param string $fieldName
     * @param mixed $operator
     * @param mixed $value
     * @return \Illuminate\Support\Collection
     */
    public function queryByField(int $butirId, string $fieldName, $operator, $value = null)
    {
        // Get column mapping
        $mapping = ButirColumnMapping::getByFieldName($butirId, $fieldName);

        if (!$mapping) {
            throw new \Exception("Field '{$fieldName}' tidak ditemukan dalam mapping");
        }

        // Support both 3 and 4 parameters
        if (func_num_args() === 3) {
            $value = $operator;
            $operator = '=';
        }

        // Query using column name
        return ButirData::whereHas('pengisianButir', function($q) use ($butirId) {
            $q->where('butir_akreditasi_id', $butirId);
        })
        ->where($mapping->column_name, $operator, $value)
        ->get()
        ->map->toNamedFields();
    }

    /**
     * Get query builder for complex queries
     *
     * @param int $butirId
     * @return ButirDataQueryBuilder
     */
    public function query(int $butirId): ButirDataQueryBuilder
    {
        return new ButirDataQueryBuilder($butirId);
    }

    /**
     * Import data from array
     *
     * @param int $pengisianButirId
     * @param array $rows
     * @return array
     */
    public function import(int $pengisianButirId, array $rows): array
    {
        return $this->bulkCreate($pengisianButirId, $rows);
    }

    /**
     * Export data to array
     *
     * @param int $pengisianButirId
     * @return array
     */
    public function export(int $pengisianButirId): array
    {
        return $this->getByPengisian($pengisianButirId, true)->toArray();
    }
}
