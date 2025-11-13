<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class IKU extends Model
{
    use HasFactory;

    protected $table = 'ikus';

    protected $fillable = [
        'kode_iku',
        'nama_iku',
        'deskripsi',
        'satuan',
        'target_type',
        'kategori',
        'bobot',
        'is_active',
    ];

    protected $casts = [
        'is_active' => 'boolean',
        'bobot' => 'integer',
    ];

    /**
     * Get all targets for this IKU
     */
    public function targets()
    {
        return $this->hasMany(IKUTarget::class, 'iku_id');
    }

    /**
     * Get active targets only
     */
    public function activeTargets()
    {
        return $this->hasMany(IKUTarget::class, 'iku_id');
    }

    /**
     * Scope to get only active IKUs
     */
    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }

    /**
     * Scope to filter by category
     */
    public function scopeByKategori($query, $kategori)
    {
        return $query->where('kategori', $kategori);
    }
}
