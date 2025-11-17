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
        'form_data', // ← NEW: Structured form data
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
        'form_data' => 'array', // ← NEW: JSON cast for dynamic form data
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

    public function versions()
    {
        return $this->hasMany(PengisianButirVersion::class)->orderBy('version_number', 'desc');
    }

    public function comments()
    {
        return $this->hasMany(ButirComment::class)->orderBy('created_at', 'desc');
    }

    public function lock()
    {
        return $this->hasOne(PengisianButirLock::class);
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

    /**
     * Dynamic Form Helper Methods
     */

    /**
     * Check if this pengisian uses dynamic form (has form_data)
     */
    public function hasDynamicForm(): bool
    {
        return !empty($this->form_data) || $this->hasFormConfig();
    }

    /**
     * Check if butir has form configuration in metadata
     */
    public function hasFormConfig(): bool
    {
        return !empty($this->butirAkreditasi?->metadata['form_config']);
    }

    /**
     * Get form type from butir configuration
     */
    public function getFormType(): ?string
    {
        return $this->butirAkreditasi?->metadata['form_config']['type'] ?? null;
    }

    /**
     * Get form configuration from butir
     */
    public function getFormConfig(): ?array
    {
        return $this->butirAkreditasi?->metadata['form_config'] ?? null;
    }

    /**
     * Check if uses legacy rich text input (konten field)
     */
    public function usesLegacyInput(): bool
    {
        return !$this->hasDynamicForm() && !empty($this->konten);
    }
}
