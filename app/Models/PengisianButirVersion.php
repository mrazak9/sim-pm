<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class PengisianButirVersion extends Model
{
    use HasFactory;

    protected $fillable = [
        'pengisian_butir_id',
        'version_number',
        'user_id',
        'konten',
        'konten_plain',
        'files',
        'status',
        'notes',
        'completion_percentage',
        'is_complete',
        'change_type',
        'change_summary',
        'metadata',
    ];

    protected $casts = [
        'files' => 'array',
        'metadata' => 'array',
        'completion_percentage' => 'decimal:2',
        'is_complete' => 'boolean',
        'version_number' => 'integer',
    ];

    /**
     * Relationships
     */
    public function pengisianButir()
    {
        return $this->belongsTo(PengisianButir::class);
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Scopes
     */
    public function scopeByPengisianButir($query, int $pengisianButirId)
    {
        return $query->where('pengisian_butir_id', $pengisianButirId);
    }

    public function scopeLatestVersion($query)
    {
        return $query->orderBy('version_number', 'desc');
    }

    /**
     * Accessors
     */
    public function getChangeTypeLabel()
    {
        return match($this->change_type) {
            'created' => 'Dibuat',
            'updated' => 'Diperbarui',
            'submitted' => 'Diajukan',
            'reviewed' => 'Direview',
            'approved' => 'Disetujui',
            'revision' => 'Revisi',
            default => 'Perubahan',
        };
    }
}
