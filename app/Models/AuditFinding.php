<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Database\Eloquent\SoftDeletes;

class AuditFinding extends Model
{
    use HasFactory, SoftDeletes;

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'audit_schedule_id',
        'finding_code',
        'category',
        'standar_reference',
        'clause',
        'description',
        'evidence_description',
        'root_cause',
        'recommendation',
        'impact',
        'pic_id',
        'unit_kerja_id',
        'due_date',
        'status',
        'resolution_notes',
        'resolved_at',
        'verified_by',
        'verified_at',
        'priority',
        'severity',
    ];

    /**
     * The attributes that should be cast.
     *
     * @var array<string, string>
     */
    protected $casts = [
        'due_date' => 'date',
        'resolved_at' => 'datetime',
        'verified_at' => 'datetime',
    ];

    /**
     * Get the audit schedule that owns the finding.
     */
    public function auditSchedule(): BelongsTo
    {
        return $this->belongsTo(AuditSchedule::class);
    }

    /**
     * Get the person in charge of this finding.
     */
    public function pic(): BelongsTo
    {
        return $this->belongsTo(User::class, 'pic_id');
    }

    /**
     * Get the responsible unit for this finding.
     */
    public function unitKerja(): BelongsTo
    {
        return $this->belongsTo(UnitKerja::class);
    }

    /**
     * Get the verifier of this finding.
     */
    public function verifier(): BelongsTo
    {
        return $this->belongsTo(User::class, 'verified_by');
    }

    /**
     * Get the evidence for this finding.
     */
    public function evidence(): HasMany
    {
        return $this->hasMany(AuditEvidence::class);
    }

    /**
     * Get the RTL (follow-up action plan) for this finding.
     */
    public function rtl(): HasOne
    {
        return $this->hasOne(RTL::class);
    }

    /**
     * Scope a query to only include open findings.
     */
    public function scopeOpen($query)
    {
        return $query->where('status', 'open');
    }

    /**
     * Scope a query to only include major findings.
     */
    public function scopeMajor($query)
    {
        return $query->where('category', 'major');
    }

    /**
     * Scope a query to only include minor findings.
     */
    public function scopeMinor($query)
    {
        return $query->where('category', 'minor');
    }

    /**
     * Scope a query to only include OFI (Opportunity for Improvement).
     */
    public function scopeOfi($query)
    {
        return $query->where('category', 'ofi');
    }

    /**
     * Scope a query to only include overdue findings.
     */
    public function scopeOverdue($query)
    {
        return $query->where('status', '!=', 'closed')
            ->where('due_date', '<', now());
    }

    /**
     * Scope a query by category.
     */
    public function scopeByCategory($query, $category)
    {
        return $query->where('category', $category);
    }

    /**
     * Scope a query by priority.
     */
    public function scopeByPriority($query, $priority)
    {
        return $query->where('priority', $priority);
    }

    /**
     * Check if finding is overdue.
     */
    public function isOverdue(): bool
    {
        return $this->due_date &&
               $this->due_date->isPast() &&
               !in_array($this->status, ['resolved', 'verified', 'closed']);
    }

    /**
     * Check if finding is resolved.
     */
    public function isResolved(): bool
    {
        return in_array($this->status, ['resolved', 'verified', 'closed']);
    }

    /**
     * Get category label in Indonesian.
     */
    public function getCategoryLabel(): string
    {
        return match($this->category) {
            'major' => 'Major NC',
            'minor' => 'Minor NC',
            'ofi' => 'OFI',
            default => $this->category,
        };
    }

    /**
     * Get status label in Indonesian.
     */
    public function getStatusLabel(): string
    {
        return match($this->status) {
            'open' => 'Terbuka',
            'in_progress' => 'Dalam Proses',
            'resolved' => 'Diselesaikan',
            'verified' => 'Terverifikasi',
            'closed' => 'Ditutup',
            default => $this->status,
        };
    }
}
