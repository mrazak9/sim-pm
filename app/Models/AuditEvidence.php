<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Facades\Storage;

class AuditEvidence extends Model
{
    use HasFactory, SoftDeletes;

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'audit_finding_id',
        'audit_schedule_id',
        'file_name',
        'file_path',
        'file_type',
        'file_size',
        'mime_type',
        'title',
        'description',
        'type',
        'captured_at',
        'uploaded_by',
    ];

    /**
     * The attributes that should be cast.
     *
     * @var array<string, string>
     */
    protected $casts = [
        'file_size' => 'integer',
        'captured_at' => 'datetime',
    ];

    /**
     * Get the audit finding that owns the evidence.
     */
    public function auditFinding(): BelongsTo
    {
        return $this->belongsTo(AuditFinding::class);
    }

    /**
     * Get the audit schedule that owns the evidence.
     */
    public function auditSchedule(): BelongsTo
    {
        return $this->belongsTo(AuditSchedule::class);
    }

    /**
     * Get the user who uploaded the evidence.
     */
    public function uploader(): BelongsTo
    {
        return $this->belongsTo(User::class, 'uploaded_by');
    }

    /**
     * Scope a query to only include evidence for findings.
     */
    public function scopeForFinding($query)
    {
        return $query->whereNotNull('audit_finding_id');
    }

    /**
     * Scope a query to only include evidence for schedules.
     */
    public function scopeForSchedule($query)
    {
        return $query->whereNotNull('audit_schedule_id')
            ->whereNull('audit_finding_id');
    }

    /**
     * Scope a query by evidence type.
     */
    public function scopeByType($query, $type)
    {
        return $query->where('type', $type);
    }

    /**
     * Get file size in human readable format.
     */
    public function getFileSizeFormatted(): string
    {
        $bytes = $this->file_size;
        if ($bytes === 0) return '0 Bytes';

        $k = 1024;
        $sizes = ['Bytes', 'KB', 'MB', 'GB'];
        $i = floor(log($bytes) / log($k));

        return round($bytes / pow($k, $i), 2) . ' ' . $sizes[$i];
    }

    /**
     * Get file URL for download.
     */
    public function getFileUrl(): string
    {
        return Storage::url($this->file_path);
    }

    /**
     * Delete file from storage when evidence is deleted.
     */
    protected static function boot()
    {
        parent::boot();

        static::deleting(function ($evidence) {
            if ($evidence->file_path && Storage::exists($evidence->file_path)) {
                Storage::delete($evidence->file_path);
            }
        });
    }

    /**
     * Check if evidence is an image.
     */
    public function isImage(): bool
    {
        return str_starts_with($this->mime_type ?? '', 'image/');
    }

    /**
     * Check if evidence is a document.
     */
    public function isDocument(): bool
    {
        $documentMimes = [
            'application/pdf',
            'application/msword',
            'application/vnd.openxmlformats-officedocument.wordprocessingml.document',
            'application/vnd.ms-excel',
            'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet',
        ];

        return in_array($this->mime_type, $documentMimes);
    }
}
