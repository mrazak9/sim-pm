<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ProgramStudi extends Model
{
    use HasFactory;

    protected $fillable = [
        'kode_prodi',
        'nama_prodi',
        'unit_kerja_id',
        'jenjang',
        'akreditasi',
        'tanggal_akreditasi',
        'kuota_mahasiswa',
        'is_active',
    ];

    protected $casts = [
        'is_active' => 'boolean',
        'tanggal_akreditasi' => 'date',
    ];

    // Relationships
    public function unitKerja()
    {
        return $this->belongsTo(UnitKerja::class);
    }

    // Scopes
    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }

    public function scopeByJenjang($query, $jenjang)
    {
        return $query->where('jenjang', $jenjang);
    }

    public function scopeByUnitKerja($query, $unitKerjaId)
    {
        return $query->where('unit_kerja_id', $unitKerjaId);
    }
}
