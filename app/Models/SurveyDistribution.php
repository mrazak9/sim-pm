<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class SurveyDistribution extends Model
{
    use HasFactory;

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'survey_id',
        'distribution_type',
        'distribution_target',
        'sent_count',
        'response_count',
        'message',
        'scheduled_at',
        'sent_at',
        'created_by',
    ];

    /**
     * The attributes that should be cast.
     *
     * @var array<string, string>
     */
    protected $casts = [
        'sent_count' => 'integer',
        'response_count' => 'integer',
        'scheduled_at' => 'datetime',
        'sent_at' => 'datetime',
    ];

    /**
     * Get the survey that owns the distribution.
     */
    public function survey(): BelongsTo
    {
        return $this->belongsTo(Survey::class);
    }

    /**
     * Get the user who created the distribution.
     */
    public function creator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    /**
     * Get the response rate percentage.
     */
    public function getResponseRate(): float
    {
        if (!$this->sent_count || $this->sent_count === 0) {
            return 0.0;
        }

        return round(($this->response_count / $this->sent_count) * 100, 2);
    }

    /**
     * Check if distribution is active.
     */
    public function isActive(): bool
    {
        return !is_null($this->sent_at);
    }

    /**
     * Scope a query to only include distributions by type.
     */
    public function scopeByType($query, string $type)
    {
        return $query->where('distribution_type', $type);
    }

    /**
     * Scope a query to only include distributions by survey.
     */
    public function scopeBySurvey($query, int $surveyId)
    {
        return $query->where('survey_id', $surveyId);
    }
}
