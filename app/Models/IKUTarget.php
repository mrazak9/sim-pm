<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class IKUTarget extends Model
{
    use HasFactory;

    protected $table = 'iku_targets';

    protected $fillable = [
        'iku_id',
        'tahun_akademik_id',
        'unit_kerja_id',
        'program_studi_id',
        'target_value',
        'periode',
        'keterangan',
    ];

    protected $casts = [
        'target_value' => 'decimal:2',
    ];

    /**
     * Get the IKU that owns this target
     */
    public function iku()
    {
        return $this->belongsTo(IKU::class, 'iku_id');
    }

    /**
     * Get the tahun akademik
     */
    public function tahunAkademik()
    {
        return $this->belongsTo(TahunAkademik::class, 'tahun_akademik_id');
    }

    /**
     * Get the unit kerja (if applicable)
     */
    public function unitKerja()
    {
        return $this->belongsTo(UnitKerja::class, 'unit_kerja_id');
    }

    /**
     * Get the program studi (if applicable)
     */
    public function programStudi()
    {
        return $this->belongsTo(ProgramStudi::class, 'program_studi_id');
    }

    /**
     * Get all progress records for this target
     */
    public function progress()
    {
        return $this->hasMany(IKUProgress::class, 'iku_target_id');
    }

    /**
     * Get latest progress
     */
    public function latestProgress()
    {
        return $this->hasOne(IKUProgress::class, 'iku_target_id')->latest('tanggal_capaian');
    }

    /**
     * Calculate total achievement
     */
    public function getTotalCapaianAttribute()
    {
        return $this->progress()->sum('nilai_capaian');
    }

    /**
     * Calculate achievement percentage
     */
    public function getPersentaseCapaianAttribute()
    {
        if ($this->target_value == 0) {
            return 0;
        }
        return ($this->total_capaian / $this->target_value) * 100;
    }

    /**
     * Scope to filter by tahun akademik
     */
    public function scopeByTahunAkademik($query, $tahunAkademikId)
    {
        return $query->where('tahun_akademik_id', $tahunAkademikId);
    }

    /**
     * Scope to filter by unit kerja
     */
    public function scopeByUnitKerja($query, $unitKerjaId)
    {
        return $query->where('unit_kerja_id', $unitKerjaId);
    }

    /**
     * Scope to filter by program studi
     */
    public function scopeByProgramStudi($query, $programStudiId)
    {
        return $query->where('program_studi_id', $programStudiId);
    }
}
