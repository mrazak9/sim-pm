<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class ButirAkreditasi extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'kode',
        'nama',
        'deskripsi',
        'instrumen',
        'periode_akreditasi_id',
        'template_id',
        'kategori',
        'bobot',
        'parent_id',
        'urutan',
        'is_mandatory',
        'metadata',
    ];

    protected $casts = [
        'metadata' => 'array',
        'bobot' => 'decimal:2',
        'is_mandatory' => 'boolean',
        'urutan' => 'integer',
    ];

    /**
     * Relationships
     */
    public function parent()
    {
        return $this->belongsTo(ButirAkreditasi::class, 'parent_id');
    }

    public function children()
    {
        return $this->hasMany(ButirAkreditasi::class, 'parent_id')->orderBy('urutan');
    }

    public function periodeAkreditasi()
    {
        return $this->belongsTo(PeriodeAkreditasi::class, 'periode_akreditasi_id');
    }

    public function template()
    {
        return $this->belongsTo(ButirAkreditasi::class, 'template_id');
    }

    public function copiedButirs()
    {
        return $this->hasMany(ButirAkreditasi::class, 'template_id');
    }

    public function pengisianButirs()
    {
        return $this->hasMany(PengisianButir::class);
    }

    public function dokumenAkreditasis()
    {
        return $this->belongsToMany(DokumenAkreditasi::class, 'butir_dokumen')
            ->withPivot('keterangan', 'urutan')
            ->withTimestamps()
            ->orderBy('urutan');
    }

    public function columnMappings()
    {
        return $this->hasMany(ButirColumnMapping::class)->orderBy('display_order');
    }

    public function butirData()
    {
        return $this->hasManyThrough(
            ButirData::class,
            PengisianButir::class,
            'butir_akreditasi_id',
            'pengisian_butir_id'
        );
    }

    /**
     * Scopes
     */
    public function scopeByInstrumen($query, $instrumen)
    {
        return $query->where('instrumen', $instrumen);
    }

    public function scopeTemplatesOnly($query)
    {
        return $query->whereNull('periode_akreditasi_id');
    }

    public function scopeByPeriode($query, $periodeId)
    {
        return $query->where('periode_akreditasi_id', $periodeId);
    }

    public function scopeParentOnly($query)
    {
        return $query->whereNull('parent_id');
    }

    public function scopeMandatory($query)
    {
        return $query->where('is_mandatory', true);
    }

    public function scopeByKategori($query, $kategori)
    {
        return $query->where('kategori', $kategori);
    }

    /**
     * Accessors
     */
    public function getHasChildrenAttribute()
    {
        return $this->children()->exists();
    }

    public function getFullKodeAttribute()
    {
        if ($this->parent) {
            return $this->parent->kode . '.' . $this->kode;
        }
        return $this->kode;
    }
}
