<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class PeriodeAkreditasi extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'nama',
        'jenis_akreditasi',
        'lembaga',
        'instrumen',
        'jenjang',
        'unit_kerja_id',
        'program_studi_id',
        'tanggal_mulai',
        'deadline_pengumpulan',
        'jadwal_visitasi',
        'tanggal_berakhir',
        'status',
        'keterangan',
        'metadata',
    ];

    protected $casts = [
        'metadata' => 'array',
        'tanggal_mulai' => 'date',
        'deadline_pengumpulan' => 'date',
        'jadwal_visitasi' => 'date',
        'tanggal_berakhir' => 'date',
    ];

    /**
     * Relationships
     */
    public function unitKerja()
    {
        return $this->belongsTo(UnitKerja::class);
    }

    public function programStudi()
    {
        return $this->belongsTo(ProgramStudi::class);
    }

    public function pengisianButirs()
    {
        return $this->hasMany(PengisianButir::class);
    }

    public function dokumenAkreditasis()
    {
        return $this->hasMany(DokumenAkreditasi::class);
    }

    /**
     * Get butir akreditasi through pengisian butir (many-to-many through)
     */
    public function butirAkreditasis()
    {
        return $this->hasManyThrough(
            ButirAkreditasi::class,
            PengisianButir::class,
            'periode_akreditasi_id', // Foreign key on pengisian_butirs table
            'id',                     // Foreign key on butir_akreditasis table
            'id',                     // Local key on periode_akreditasis table
            'butir_akreditasi_id'     // Local key on pengisian_butirs table
        );
    }

    /**
     * Scopes
     */
    public function scopeAktif($query)
    {
        return $query->whereIn('status', ['persiapan', 'pengisian', 'review']);
    }

    public function scopeInstitusi($query)
    {
        return $query->where('jenis_akreditasi', 'institusi');
    }

    public function scopeProdi($query)
    {
        return $query->where('jenis_akreditasi', 'program_studi');
    }

    public function scopeByLembaga($query, $lembaga)
    {
        return $query->where('lembaga', $lembaga);
    }

    /**
     * Accessors & Mutators
     */
    public function getProgressPersentaseAttribute()
    {
        // Get total butir based on instrumen
        $totalButir = \App\Models\ButirAkreditasi::where('instrumen', $this->instrumen)->count();
        if ($totalButir == 0) return 0;

        // Count filled butir (distinct)
        $butirFilled = $this->pengisianButirs()->distinct('butir_akreditasi_id')->count('butir_akreditasi_id');
        return round(($butirFilled / $totalButir) * 100, 2);
    }

    public function getIsExpiredAttribute()
    {
        return $this->deadline_pengumpulan && $this->deadline_pengumpulan->isPast();
    }

    public function getSisaHariAttribute()
    {
        if (!$this->deadline_pengumpulan) return null;
        return now()->diffInDays($this->deadline_pengumpulan, false);
    }
}
