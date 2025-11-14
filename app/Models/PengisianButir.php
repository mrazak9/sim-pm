<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class PengisianButir extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'periode_akreditasi_id',
        'butir_akreditasi_id',
        'pic_user_id',
        'konten',
        'konten_plain',
        'files',
        'status',
        'version',
        'notes',
        'reviewed_by',
        'reviewed_at',
        'review_notes',
        'completion_percentage',
        'is_complete',
    ];

    protected $casts = [
        'files' => 'array',
        'reviewed_at' => 'datetime',
        'completion_percentage' => 'decimal:2',
        'is_complete' => 'boolean',
        'version' => 'integer',
    ];

    /**
     * Relationships
     */
    public function periodeAkreditasi()
    {
        return $this->belongsTo(PeriodeAkreditasi::class);
    }

    public function butirAkreditasi()
    {
        return $this->belongsTo(ButirAkreditasi::class);
    }

    public function picUser()
    {
        return $this->belongsTo(User::class, 'pic_user_id');
    }

    public function reviewer()
    {
        return $this->belongsTo(User::class, 'reviewed_by');
    }

    /**
     * Scopes
     */
    public function scopeByPeriode($query, $periodeId)
    {
        return $query->where('periode_akreditasi_id', $periodeId);
    }

    public function scopeByStatus($query, $status)
    {
        return $query->where('status', $status);
    }

    public function scopeDraft($query)
    {
        return $query->where('status', 'draft');
    }

    public function scopeApproved($query)
    {
        return $query->where('status', 'approved');
    }

    public function scopeComplete($query)
    {
        return $query->where('is_complete', true);
    }

    public function scopeIncomplete($query)
    {
        return $query->where('is_complete', false);
    }

    /**
     * Accessors
     */
    public function getStatusColorAttribute()
    {
        return match($this->status) {
            'draft' => 'gray',
            'submitted' => 'blue',
            'review' => 'yellow',
            'revision' => 'orange',
            'approved' => 'green',
            default => 'gray',
        };
    }

    public function getStatusLabelAttribute()
    {
        return match($this->status) {
            'draft' => 'Draft',
            'submitted' => 'Diajukan',
            'review' => 'Dalam Review',
            'revision' => 'Perlu Revisi',
            'approved' => 'Disetujui',
            default => 'Unknown',
        };
    }

    public function getWordCountAttribute()
    {
        return str_word_count(strip_tags($this->konten ?? ''));
    }

    public function getCharCountAttribute()
    {
        return strlen(strip_tags($this->konten ?? ''));
    }
}
