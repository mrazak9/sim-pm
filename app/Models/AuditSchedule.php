<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

class AuditSchedule extends Model
{
    use HasFactory, SoftDeletes;

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'audit_plan_id',
        'unit_kerja_id',
        'auditor_lead_id',
        'scheduled_date',
        'estimated_duration',
        'location',
        'status',
        'agenda',
        'notes',
        'preparation_notes',
        'actual_start',
        'actual_end',
        'summary',
    ];

    /**
     * The attributes that should be cast.
     *
     * @var array<string, string>
     */
    protected $casts = [
        'scheduled_date' => 'datetime',
        'actual_start' => 'datetime',
        'actual_end' => 'datetime',
        'estimated_duration' => 'integer',
    ];

    /**
     * Get the audit plan that owns the schedule.
     */
    public function auditPlan(): BelongsTo
    {
        return $this->belongsTo(AuditPlan::class);
    }

    /**
     * Get the unit kerja (auditee) for this schedule.
     */
    public function unitKerja(): BelongsTo
    {
        return $this->belongsTo(UnitKerja::class);
    }

    /**
     * Get the lead auditor for this schedule.
     */
    public function auditorLead(): BelongsTo
    {
        return $this->belongsTo(User::class, 'auditor_lead_id');
    }

    /**
     * Get the auditors assigned to this schedule.
     */
    public function auditors(): BelongsToMany
    {
        return $this->belongsToMany(User::class, 'audit_schedule_auditors')
            ->withPivot('role')
            ->withTimestamps();
    }

    /**
     * Get the findings for this schedule.
     */
    public function findings(): HasMany
    {
        return $this->hasMany(AuditFinding::class);
    }

    /**
     * Get the evidence for this schedule.
     */
    public function evidence(): HasMany
    {
        return $this->hasMany(AuditEvidence::class);
    }

    /**
     * Scope a query to only include scheduled audits.
     */
    public function scopeScheduled($query)
    {
        return $query->where('status', 'scheduled');
    }

    /**
     * Scope a query to only include completed audits.
     */
    public function scopeCompleted($query)
    {
        return $query->where('status', 'completed');
    }

    /**
     * Scope a query to only include ongoing audits.
     */
    public function scopeOngoing($query)
    {
        return $query->where('status', 'ongoing');
    }

    /**
     * Scope for upcoming audits (scheduled in the future).
     */
    public function scopeUpcoming($query)
    {
        return $query->where('status', 'scheduled')
            ->where('scheduled_date', '>', now());
    }

    /**
     * Scope for past audits.
     */
    public function scopePast($query)
    {
        return $query->where('scheduled_date', '<', now());
    }

    /**
     * Check if schedule is editable.
     */
    public function isEditable(): bool
    {
        return in_array($this->status, ['scheduled']);
    }

    /**
     * Get actual duration in minutes.
     */
    public function getActualDuration(): ?int
    {
        if ($this->actual_start && $this->actual_end) {
            return $this->actual_start->diffInMinutes($this->actual_end);
        }
        return null;
    }
}
