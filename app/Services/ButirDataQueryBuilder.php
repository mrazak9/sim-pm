<?php

namespace App\Services;

use App\Models\ButirData;
use App\Models\ButirColumnMapping;
use Illuminate\Database\Eloquent\Builder;

class ButirDataQueryBuilder
{
    protected int $butirId;
    protected Builder $query;
    protected $mappings;
    protected bool $returnNamedFields = true;

    public function __construct(int $butirId)
    {
        $this->butirId = $butirId;
        $this->query = ButirData::query();
        $this->loadMappings();
    }

    protected function loadMappings()
    {
        $this->mappings = ButirColumnMapping::where('butir_akreditasi_id', $this->butirId)
            ->get()
            ->keyBy('field_name');
    }

    /**
     * Add WHERE clause by field name
     */
    public function whereField(string $fieldName, $operator, $value = null)
    {
        $mapping = $this->mappings[$fieldName] ?? null;

        if (!$mapping) {
            throw new \Exception("Field '{$fieldName}' tidak ditemukan dalam mapping");
        }

        if (func_num_args() === 2) {
            $value = $operator;
            $operator = '=';
        }

        $this->query->where($mapping->column_name, $operator, $value);

        return $this;
    }

    /**
     * Add OR WHERE clause by field name
     */
    public function orWhereField(string $fieldName, $operator, $value = null)
    {
        $mapping = $this->mappings[$fieldName] ?? null;

        if (!$mapping) {
            throw new \Exception("Field '{$fieldName}' tidak ditemukan dalam mapping");
        }

        if (func_num_args() === 2) {
            $value = $operator;
            $operator = '=';
        }

        $this->query->orWhere($mapping->column_name, $operator, $value);

        return $this;
    }

    /**
     * Add WHERE IN clause by field name
     */
    public function whereFieldIn(string $fieldName, array $values)
    {
        $mapping = $this->mappings[$fieldName] ?? null;

        if (!$mapping) {
            throw new \Exception("Field '{$fieldName}' tidak ditemukan dalam mapping");
        }

        $this->query->whereIn($mapping->column_name, $values);

        return $this;
    }

    /**
     * Add WHERE NULL clause by field name
     */
    public function whereFieldNull(string $fieldName)
    {
        $mapping = $this->mappings[$fieldName] ?? null;

        if (!$mapping) {
            throw new \Exception("Field '{$fieldName}' tidak ditemukan dalam mapping");
        }

        $this->query->whereNull($mapping->column_name);

        return $this;
    }

    /**
     * Add ORDER BY clause by field name
     */
    public function orderByField(string $fieldName, string $direction = 'asc')
    {
        $mapping = $this->mappings[$fieldName] ?? null;

        if (!$mapping) {
            throw new \Exception("Field '{$fieldName}' tidak ditemukan dalam mapping");
        }

        $this->query->orderBy($mapping->column_name, $direction);

        return $this;
    }

    /**
     * Filter by pengisian butir
     */
    public function byPengisian(int $pengisianButirId)
    {
        $this->query->where('pengisian_butir_id', $pengisianButirId);

        return $this;
    }

    /**
     * Set whether to return named fields (default: true)
     */
    public function asRawColumns()
    {
        $this->returnNamedFields = false;

        return $this;
    }

    /**
     * Get results
     */
    public function get()
    {
        $results = $this->query->get();

        if ($this->returnNamedFields) {
            return $results->map->toNamedFields();
        }

        return $results;
    }

    /**
     * Get first result
     */
    public function first()
    {
        $result = $this->query->first();

        if ($result && $this->returnNamedFields) {
            return $result->toNamedFields();
        }

        return $result;
    }

    /**
     * Get paginated results
     */
    public function paginate($perPage = 15)
    {
        $paginator = $this->query->paginate($perPage);

        if ($this->returnNamedFields) {
            $paginator->through(fn($item) => $item->toNamedFields());
        }

        return $paginator;
    }

    /**
     * Count results
     */
    public function count()
    {
        return $this->query->count();
    }

    /**
     * Get underlying query builder
     */
    public function getQuery(): Builder
    {
        return $this->query;
    }
}
