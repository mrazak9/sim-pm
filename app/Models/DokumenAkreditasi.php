<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Facades\Storage;

class DokumenAkreditasi extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'periode_akreditasi_id',
        'nama_dokumen',
        'nomor_dokumen',
        'jenis_dokumen',
        'deskripsi',
        'file_path',
        'file_type',
        'file_size',
        'uploaded_by',
        'metadata',
    ];

    protected $casts = [
        'metadata' => 'array',
        'file_size' => 'integer',
    ];

    /**
     * Relationships
     */
    public function periodeAkreditasi()
    {
        return $this->belongsTo(PeriodeAkreditasi::class);
    }

    public function uploader()
    {
        return $this->belongsTo(User::class, 'uploaded_by');
    }

    public function butirAkreditasis()
    {
        return $this->belongsToMany(ButirAkreditasi::class, 'butir_dokumen')
            ->withPivot('keterangan', 'urutan')
            ->withTimestamps()
            ->orderBy('urutan');
    }

    /**
     * Scopes
     */
    public function scopeByPeriode($query, $periodeId)
    {
        return $query->where('periode_akreditasi_id', $periodeId);
    }

    public function scopeByJenisDokumen($query, $jenis)
    {
        return $query->where('jenis_dokumen', $jenis);
    }

    public function scopeByFileType($query, $fileType)
    {
        return $query->where('file_type', $fileType);
    }

    /**
     * Accessors
     */
    public function getFileSizeFormattedAttribute()
    {
        $bytes = $this->file_size;
        if ($bytes >= 1073741824) {
            return number_format($bytes / 1073741824, 2) . ' GB';
        } elseif ($bytes >= 1048576) {
            return number_format($bytes / 1048576, 2) . ' MB';
        } elseif ($bytes >= 1024) {
            return number_format($bytes / 1024, 2) . ' KB';
        } elseif ($bytes > 1) {
            return $bytes . ' bytes';
        } elseif ($bytes == 1) {
            return $bytes . ' byte';
        } else {
            return '0 bytes';
        }
    }

    public function getFileUrlAttribute()
    {
        if ($this->file_path) {
            return Storage::url($this->file_path);
        }
        return null;
    }

    public function getFileExistsAttribute()
    {
        if ($this->file_path) {
            return Storage::exists($this->file_path);
        }
        return false;
    }

    /**
     * Methods
     */
    public function deleteFile()
    {
        if ($this->file_path && Storage::exists($this->file_path)) {
            Storage::delete($this->file_path);
        }
    }

    /**
     * Boot method
     */
    protected static function boot()
    {
        parent::boot();

        // Auto delete file when model is deleted
        static::deleting(function ($dokumen) {
            $dokumen->deleteFile();
        });
    }
}
