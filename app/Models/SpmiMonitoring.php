<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;

class SpmiMonitoring extends Model
{
    use HasFactory, SoftDeletes;

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'monitoring_code',
        'spmi_standard_id',
        'tahun_akademik_id',
        'title',
        'description',
        'monitoring_date',
        'monitoring_type',
        'findings',
        'strengths',
        'weaknesses',
        'opportunities',
        'threats',
        'recommendations',
        'compliance_level',
        'compliance_score',
        'status',
        'monitored_by',
        'unit_kerja_id',
        'report_file',
    ];

    /**
     * The attributes that should be cast.
     *
     * @var array<string, string>
     */
    protected $casts = [
        'monitoring_date' => 'date',
        'compliance_score' => 'integer',
    ];

    /**
     * Get the SPMI standard that owns the monitoring.
     */
    public function spmiStandard(): BelongsTo
    {
        return $this->belongsTo(SpmiStandard::class, 'spmi_standard_id');
    }

    /**
     * Get the tahun akademik that owns the monitoring.
     */
    public function tahunAkademik(): BelongsTo
    {
        return $this->belongsTo(TahunAkademik::class, 'tahun_akademik_id');
    }

    /**
     * Get the user who monitored.
     */
    public function monitoredBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'monitored_by');
    }

    /**
     * Get the unit kerja that owns the monitoring.
     */
    public function unitKerja(): BelongsTo
    {
        return $this->belongsTo(UnitKerja::class, 'unit_kerja_id');
    }

    /**
     * Check if monitoring is completed.
     */
    public function isCompleted(): bool
    {
        return $this->status === 'completed';
    }

    /**
     * Get the compliance level label in Indonesian.
     */
    public function getComplianceLevelLabel(): string
    {
        return match ($this->compliance_level) {
            'excellent' => 'Sangat Baik',
            'good' => 'Baik',
            'fair' => 'Cukup',
            'poor' => 'Kurang',
            default => 'Tidak Diketahui',
        };
    }

    /**
     * Scope a query to only include completed monitorings.
     */
    public function scopeCompleted($query)
    {
        return $query->where('status', 'completed');
    }

    /**
     * Scope a query to only include monitorings by standard.
     */
    public function scopeByStandard($query, $standardId)
    {
        return $query->where('spmi_standard_id', $standardId);
    }
}
