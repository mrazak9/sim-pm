<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;

class SpmiIndicatorTarget extends Model
{
    use HasFactory, SoftDeletes;

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'spmi_indicator_id',
        'tahun_akademik_id',
        'periode',
        'target_value',
        'achievement_value',
        'achievement_percentage',
        'notes',
        'status',
        'measurement_date',
        'measured_by',
    ];

    /**
     * The attributes that should be cast.
     *
     * @var array<string, string>
     */
    protected $casts = [
        'target_value' => 'decimal:2',
        'achievement_value' => 'decimal:2',
        'achievement_percentage' => 'integer',
        'measurement_date' => 'date',
    ];

    /**
     * Get the SPMI indicator that owns the target.
     */
    public function spmiIndicator(): BelongsTo
    {
        return $this->belongsTo(SpmiIndicator::class, 'spmi_indicator_id');
    }

    /**
     * Get the tahun akademik that owns the target.
     */
    public function tahunAkademik(): BelongsTo
    {
        return $this->belongsTo(TahunAkademik::class, 'tahun_akademik_id');
    }

    /**
     * Get the user who measured this target.
     */
    public function measuredBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'measured_by');
    }

    /**
     * Calculate the achievement percentage.
     */
    public function calculateAchievementPercentage(): float
    {
        if ($this->target_value <= 0) {
            return 0;
        }

        return ($this->achievement_value / $this->target_value) * 100;
    }

    /**
     * Check if target is achieved (achievement percentage >= 100).
     */
    public function isAchieved(): bool
    {
        return $this->achievement_percentage >= 100;
    }

    /**
     * Check if target is at risk (achievement percentage between 70-99).
     */
    public function isAtRisk(): bool
    {
        return $this->achievement_percentage >= 70 && $this->achievement_percentage < 100;
    }

    /**
     * Get the status label in Indonesian.
     */
    public function getStatusLabelAttribute(): string
    {
        return match ($this->status) {
            'achieved' => 'Tercapai',
            'at_risk' => 'Berisiko',
            'not_achieved' => 'Tidak Tercapai',
            'pending' => 'Menunggu',
            default => 'Tidak Diketahui',
        };
    }
}
