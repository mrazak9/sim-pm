<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

class RTL extends Model
{
    use HasFactory, SoftDeletes;

    /**
     * The table associated with the model.
     *
     * @var string
     */
    protected $table = 'rtls';

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'audit_finding_id',
        'rtl_code',
        'action_plan',
        'success_indicator',
        'implementation_steps',
        'pic_id',
        'unit_kerja_id',
        'target_date',
        'budget',
        'resources_needed',
        'status',
        'completion_percentage',
        'current_status_notes',
        'started_at',
        'completed_at',
        'completion_notes',
        'verified_by',
        'verified_at',
        'verification_notes',
        'verification_status',
        'risk_level',
        'risk_description',
    ];

    /**
     * The attributes that should be cast.
     *
     * @var array<string, string>
     */
    protected $casts = [
        'target_date' => 'date',
        'budget' => 'decimal:2',
        'completion_percentage' => 'integer',
        'started_at' => 'datetime',
        'completed_at' => 'datetime',
        'verified_at' => 'datetime',
    ];

    /**
     * Get the audit finding that owns this RTL.
     */
    public function auditFinding(): BelongsTo
    {
        return $this->belongsTo(AuditFinding::class);
    }

    /**
     * Get the person in charge of this RTL.
     */
    public function pic(): BelongsTo
    {
        return $this->belongsTo(User::class, 'pic_id');
    }

    /**
     * Get the responsible unit for this RTL.
     */
    public function unitKerja(): BelongsTo
    {
        return $this->belongsTo(UnitKerja::class);
    }

    /**
     * Get the verifier of this RTL.
     */
    public function verifier(): BelongsTo
    {
        return $this->belongsTo(User::class, 'verified_by');
    }

    /**
     * Get the progress updates for this RTL.
     */
    public function progressUpdates(): HasMany
    {
        return $this->hasMany(RTLProgress::class);
    }

    /**
     * Get the latest progress update.
     */
    public function latestProgress()
    {
        return $this->hasOne(RTLProgress::class)->latestOfMany('progress_date');
    }

    /**
     * Scope a query to only include not started RTLs.
     */
    public function scopeNotStarted($query)
    {
        return $query->where('status', 'not_started');
    }

    /**
     * Scope a query to only include in progress RTLs.
     */
    public function scopeInProgress($query)
    {
        return $query->where('status', 'in_progress');
    }

    /**
     * Scope a query to only include completed RTLs.
     */
    public function scopeCompleted($query)
    {
        return $query->where('status', 'completed');
    }

    /**
     * Scope a query to only include overdue RTLs.
     */
    public function scopeOverdue($query)
    {
        return $query->where('status', '!=', 'completed')
            ->where('target_date', '<', now());
    }

    /**
     * Scope for RTLs due soon (within specified days).
     */
    public function scopeDueSoon($query, int $days = 7)
    {
        return $query->where('status', '!=', 'completed')
            ->where('target_date', '<=', now()->addDays($days))
            ->where('target_date', '>=', now());
    }

    /**
     * Scope by verification status.
     */
    public function scopeByVerificationStatus($query, $status)
    {
        return $query->where('verification_status', $status);
    }

    /**
     * Check if RTL is overdue.
     */
    public function isOverdue(): bool
    {
        return $this->target_date &&
               $this->target_date->isPast() &&
               $this->status !== 'completed';
    }

    /**
     * Check if RTL is completed.
     */
    public function isCompleted(): bool
    {
        return $this->status === 'completed';
    }

    /**
     * Get status label in Indonesian.
     */
    public function getStatusLabel(): string
    {
        return match($this->status) {
            'not_started' => 'Belum Dimulai',
            'in_progress' => 'Sedang Berjalan',
            'completed' => 'Selesai',
            'overdue' => 'Terlambat',
            'cancelled' => 'Dibatalkan',
            default => $this->status,
        };
    }

    /**
     * Get days until target date.
     */
    public function getDaysUntilTarget(): ?int
    {
        if (!$this->target_date) {
            return null;
        }

        return now()->startOfDay()->diffInDays($this->target_date->startOfDay(), false);
    }

    /**
     * Update completion percentage based on latest progress.
     */
    public function updateCompletionPercentage(): void
    {
        $latestProgress = $this->latestProgress;
        if ($latestProgress) {
            $this->completion_percentage = $latestProgress->progress_percentage;
            $this->save();
        }
    }
}
