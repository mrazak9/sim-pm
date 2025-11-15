<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

class SpmiIndicator extends Model
{
    use HasFactory, SoftDeletes;

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'spmi_standard_id',
        'code',
        'name',
        'description',
        'measurement_unit',
        'measurement_type',
        'formula',
        'data_source',
        'frequency',
        'pic_id',
        'is_active',
    ];

    /**
     * The attributes that should be cast.
     *
     * @var array<string, string>
     */
    protected $casts = [
        'is_active' => 'boolean',
    ];

    /**
     * Get the SPMI standard that owns the indicator.
     */
    public function spmiStandard(): BelongsTo
    {
        return $this->belongsTo(SpmiStandard::class, 'spmi_standard_id');
    }

    /**
     * Get the user who is the PIC (Person In Charge) for this indicator.
     */
    public function pic(): BelongsTo
    {
        return $this->belongsTo(User::class, 'pic_id');
    }

    /**
     * Get the targets for this indicator.
     */
    public function targets(): HasMany
    {
        return $this->hasMany(SpmiIndicatorTarget::class, 'spmi_indicator_id');
    }

    /**
     * Scope a query to only include active indicators.
     */
    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }
}
