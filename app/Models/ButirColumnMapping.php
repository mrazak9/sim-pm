<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ButirColumnMapping extends Model
{
    use HasFactory;

    protected $fillable = [
        'butir_akreditasi_id',
        'field_name',
        'field_label',
        'column_name',
        'field_type',
        'field_config',
        'display_order',
        'width',
        'is_required',
        'help_text',
        'placeholder',
    ];

    protected $casts = [
        'field_config' => 'array',
        'is_required' => 'boolean',
        'display_order' => 'integer',
    ];

    /**
     * Relationships
     */
    public function butirAkreditasi()
    {
        return $this->belongsTo(ButirAkreditasi::class);
    }

    /**
     * Get mappings by butir ID
     */
    public static function getByButirId(int $butirId)
    {
        return static::where('butir_akreditasi_id', $butirId)
            ->orderBy('display_order')
            ->get();
    }

    /**
     * Get mapping by field name
     */
    public static function getByFieldName(int $butirId, string $fieldName)
    {
        return static::where('butir_akreditasi_id', $butirId)
            ->where('field_name', $fieldName)
            ->first();
    }

    /**
     * Get next available column
     */
    public static function getNextAvailableColumn(int $butirId): ?string
    {
        $usedColumns = static::where('butir_akreditasi_id', $butirId)
            ->pluck('column_name')
            ->toArray();

        for ($i = 1; $i <= 30; $i++) {
            $column = "c{$i}";
            if (!in_array($column, $usedColumns)) {
                return $column;
            }
        }

        return null; // All columns used
    }

    /**
     * Scopes
     */
    public function scopeByButir($query, int $butirId)
    {
        return $query->where('butir_akreditasi_id', $butirId);
    }

    public function scopeOrdered($query)
    {
        return $query->orderBy('display_order');
    }
}
