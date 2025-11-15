<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Facades\Storage;

class RTLProgress extends Model
{
    use HasFactory;

    /**
     * The table associated with the model.
     *
     * @var string
     */
    protected $table = 'rtl_progress';

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'rtl_id',
        'progress_date',
        'progress_percentage',
        'description',
        'achievements',
        'challenges',
        'evidence_file',
        'evidence_type',
        'evidence_size',
        'next_steps',
        'next_review_date',
        'reported_by',
        'is_milestone',
    ];

    /**
     * The attributes that should be cast.
     *
     * @var array<string, string>
     */
    protected $casts = [
        'progress_date' => 'date',
        'next_review_date' => 'date',
        'progress_percentage' => 'integer',
        'evidence_size' => 'integer',
        'is_milestone' => 'boolean',
    ];

    /**
     * Get the RTL that owns this progress update.
     */
    public function rtl(): BelongsTo
    {
        return $this->belongsTo(RTL::class);
    }

    /**
     * Get the user who reported this progress.
     */
    public function reporter(): BelongsTo
    {
        return $this->belongsTo(User::class, 'reported_by');
    }

    /**
     * Scope a query to only include milestone updates.
     */
    public function scopeMilestones($query)
    {
        return $query->where('is_milestone', true);
    }

    /**
     * Scope a query to only include recent updates (last N days).
     */
    public function scopeRecent($query, int $days = 30)
    {
        return $query->where('progress_date', '>=', now()->subDays($days));
    }

    /**
     * Scope a query ordered by progress date descending.
     */
    public function scopeLatest($query)
    {
        return $query->orderBy('progress_date', 'desc');
    }

    /**
     * Get evidence file size in human readable format.
     */
    public function getEvidenceFileSizeFormatted(): ?string
    {
        if (!$this->evidence_size) {
            return null;
        }

        $bytes = $this->evidence_size;
        if ($bytes === 0) return '0 Bytes';

        $k = 1024;
        $sizes = ['Bytes', 'KB', 'MB', 'GB'];
        $i = floor(log($bytes) / log($k));

        return round($bytes / pow($k, $i), 2) . ' ' . $sizes[$i];
    }

    /**
     * Get evidence file URL for download.
     */
    public function getEvidenceFileUrl(): ?string
    {
        if (!$this->evidence_file) {
            return null;
        }

        return Storage::url($this->evidence_file);
    }

    /**
     * Delete evidence file from storage when progress is deleted.
     */
    protected static function boot()
    {
        parent::boot();

        static::deleting(function ($progress) {
            if ($progress->evidence_file && Storage::exists($progress->evidence_file)) {
                Storage::delete($progress->evidence_file);
            }
        });

        // Update RTL completion percentage when progress is saved
        static::saved(function ($progress) {
            $progress->rtl->updateCompletionPercentage();

            // Auto-update RTL status based on percentage
            $rtl = $progress->rtl;
            if ($progress->progress_percentage >= 100 && $rtl->status !== 'completed') {
                $rtl->status = 'completed';
                $rtl->completed_at = now();
                $rtl->save();
            } elseif ($progress->progress_percentage > 0 && $rtl->status === 'not_started') {
                $rtl->status = 'in_progress';
                $rtl->started_at = now();
                $rtl->save();
            }
        });
    }

    /**
     * Check if progress has evidence file.
     */
    public function hasEvidence(): bool
    {
        return !empty($this->evidence_file);
    }

    /**
     * Get progress percentage color based on value.
     */
    public function getProgressColor(): string
    {
        return match(true) {
            $this->progress_percentage >= 100 => 'green',
            $this->progress_percentage >= 75 => 'blue',
            $this->progress_percentage >= 50 => 'yellow',
            $this->progress_percentage >= 25 => 'orange',
            default => 'red',
        };
    }
}
