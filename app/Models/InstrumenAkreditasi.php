<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class InstrumenAkreditasi extends Model
{
    use HasFactory;

    protected $fillable = [
        'kode',
        'nama',
        'deskripsi',
        'jenis',
        'lembaga',
        'tahun_berlaku',
        'is_active',
    ];

    protected $casts = [
        'is_active' => 'boolean',
        'tahun_berlaku' => 'integer',
    ];

    /**
     * Scope to get only active instrumen
     */
    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }

    /**
     * Scope by jenis
     */
    public function scopeByJenis($query, string $jenis)
    {
        return $query->where(function ($q) use ($jenis) {
            $q->where('jenis', $jenis)
              ->orWhere('jenis', 'both');
        });
    }

    /**
     * Scope by lembaga
     */
    public function scopeByLembaga($query, string $lembaga)
    {
        return $query->where('lembaga', $lembaga);
    }
}
