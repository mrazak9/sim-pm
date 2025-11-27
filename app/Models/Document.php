<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Facades\Storage;

class Document extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'category_id',
        'uploaded_by',
        'title',
        'document_number',
        'description',
        'file_name',
        'file_path',
        'file_type',
        'mime_type',
        'file_size',
        'status',
        'approved_by',
        'approved_at',
        'approval_notes',
        'current_version',
        'is_latest',
        'tags',
        'metadata',
        'visibility',
        'retention_until',
        'is_archived',
        'archived_at',
    ];

    protected $casts = [
        'tags' => 'array',
        'metadata' => 'array',
        'current_version' => 'integer',
        'is_latest' => 'boolean',
        'is_archived' => 'boolean',
        'file_size' => 'integer',
        'approved_at' => 'datetime',
        'archived_at' => 'datetime',
        'retention_until' => 'date',
    ];

    /**
     * Get the category
     */
    public function category(): BelongsTo
    {
        return $this->belongsTo(DocumentCategory::class);
    }

    /**
     * Get the uploader
     */
    public function uploader(): BelongsTo
    {
        return $this->belongsTo(User::class, 'uploaded_by');
    }

    /**
     * Get the approver
     */
    public function approver(): BelongsTo
    {
        return $this->belongsTo(User::class, 'approved_by');
    }

    /**
     * Get versions
     */
    public function versions(): HasMany
    {
        return $this->hasMany(DocumentVersion::class)->orderBy('version_number', 'desc');
    }

    /**
     * Get shares
     */
    public function shares(): HasMany
    {
        return $this->hasMany(DocumentShare::class);
    }

    /**
     * Get download URL
     */
    public function getDownloadUrlAttribute(): string
    {
        return url("/api/documents/{$this->id}/download");
    }

    /**
     * Get view/preview URL
     */
    public function getViewUrlAttribute(): string
    {
        return url("/api/documents/{$this->id}/view");
    }

    /**
     * Get formatted file size
     */
    public function getFormattedSizeAttribute(): string
    {
        $bytes = $this->file_size;
        $units = ['B', 'KB', 'MB', 'GB'];

        for ($i = 0; $bytes > 1024 && $i < count($units) - 1; $i++) {
            $bytes /= 1024;
        }

        return round($bytes, 2) . ' ' . $units[$i];
    }

    /**
     * Scopes
     */
    public function scopeByStatus($query, $status)
    {
        return $query->where('status', $status);
    }

    public function scopeApproved($query)
    {
        return $query->where('status', 'approved');
    }

    public function scopeLatest($query)
    {
        return $query->where('is_latest', true);
    }

    public function scopeNotArchived($query)
    {
        return $query->where('is_archived', false);
    }

    public function scopeByVisibility($query, $visibility)
    {
        return $query->where('visibility', $visibility);
    }
}
