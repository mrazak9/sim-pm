<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class ButirData extends Model
{
    use HasFactory, SoftDeletes;

    protected $table = 'butir_data';

    protected $fillable = [
        'pengisian_butir_id',
        'row_number',
        'c1', 'c2', 'c3', 'c4', 'c5',
        'c6', 'c7', 'c8', 'c9', 'c10',
        'c11', 'c12', 'c13', 'c14', 'c15',
        'c16', 'c17', 'c18', 'c19', 'c20',
        'c21', 'c22', 'c23', 'c24', 'c25',
        'c26', 'c27', 'c28', 'c29', 'c30',
        'metadata',
    ];

    protected $casts = [
        'metadata' => 'array',
        'row_number' => 'integer',
    ];

    /**
     * Relationships
     */
    public function pengisianButir()
    {
        return $this->belongsTo(PengisianButir::class);
    }

    /**
     * Get column mappings for this butir
     */
    public function getMappings()
    {
        static $cache = [];

        $butirId = $this->pengisianButir->butir_akreditasi_id;

        if (!isset($cache[$butirId])) {
            $cache[$butirId] = ButirColumnMapping::where('butir_akreditasi_id', $butirId)
                ->orderBy('display_order')
                ->get();
        }

        return $cache[$butirId];
    }

    /**
     * Transform c1-c30 to named fields
     *
     * @return array
     */
    public function toNamedFields(): array
    {
        $mappings = $this->getMappings();

        $result = [
            'id' => $this->id,
            'row_number' => $this->row_number,
        ];

        foreach ($mappings as $mapping) {
            $columnName = $mapping->column_name;
            $fieldName = $mapping->field_name;

            $result[$fieldName] = $this->$columnName;
        }

        // Add metadata
        if ($this->metadata) {
            $result['dokumen'] = $this->metadata['dokumen'] ?? [];
            $result['notes'] = $this->metadata['notes'] ?? null;
            $result['custom_data'] = $this->metadata['custom_data'] ?? [];
        }

        return $result;
    }

    /**
     * Create from named fields
     *
     * @param array $fields
     * @param int $pengisianButirId
     * @return self
     */
    public static function fromNamedFields(array $fields, int $pengisianButirId): self
    {
        $pengisian = PengisianButir::findOrFail($pengisianButirId);

        $mappings = ButirColumnMapping::where('butir_akreditasi_id', $pengisian->butir_akreditasi_id)
            ->get()
            ->keyBy('field_name');

        $data = [
            'pengisian_butir_id' => $pengisianButirId,
            'row_number' => $fields['row_number'] ?? 1,
        ];

        // Map field names to column names
        foreach ($fields as $fieldName => $value) {
            if (isset($mappings[$fieldName])) {
                $columnName = $mappings[$fieldName]->column_name;
                $data[$columnName] = $value;
            }
        }

        // Handle metadata
        $metadata = [];
        if (isset($fields['dokumen'])) {
            $metadata['dokumen'] = $fields['dokumen'];
        }
        if (isset($fields['notes'])) {
            $metadata['notes'] = $fields['notes'];
        }
        if (isset($fields['custom_data'])) {
            $metadata['custom_data'] = $fields['custom_data'];
        }

        if (!empty($metadata)) {
            $data['metadata'] = $metadata;
        }

        return self::create($data);
    }

    /**
     * Update from named fields
     *
     * @param array $fields
     * @return bool
     */
    public function updateFromNamedFields(array $fields): bool
    {
        $mappings = $this->getMappings()->keyBy('field_name');

        $data = [];

        // Map field names to column names
        foreach ($fields as $fieldName => $value) {
            if (isset($mappings[$fieldName])) {
                $columnName = $mappings[$fieldName]->column_name;
                $data[$columnName] = $value;
            }
        }

        // Handle metadata updates
        if (isset($fields['dokumen']) || isset($fields['notes']) || isset($fields['custom_data'])) {
            $currentMetadata = $this->metadata ?? [];

            if (isset($fields['dokumen'])) {
                $currentMetadata['dokumen'] = $fields['dokumen'];
            }
            if (isset($fields['notes'])) {
                $currentMetadata['notes'] = $fields['notes'];
            }
            if (isset($fields['custom_data'])) {
                $currentMetadata['custom_data'] = $fields['custom_data'];
            }

            $data['metadata'] = $currentMetadata;
        }

        return $this->update($data);
    }

    /**
     * Scopes
     */
    public function scopeByPengisian($query, int $pengisianId)
    {
        return $query->where('pengisian_butir_id', $pengisianId);
    }
}
