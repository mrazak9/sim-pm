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

    /**
     * Scopes
     */
    public function scopeByInstrumen($query, $instrumen)
    {
        return $query->where('instrumen', $instrumen);
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
