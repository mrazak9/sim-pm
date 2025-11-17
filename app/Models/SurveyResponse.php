<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

class SurveyResponse extends Model
{
    use HasFactory, SoftDeletes;

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'survey_id',
        'user_id',
        'respondent_name',
        'respondent_email',
        'is_completed',
        'started_at',
        'completed_at',
        'ip_address',
        'user_agent',
    ];

    /**
     * The attributes that should be cast.
     *
     * @var array<string, string>
     */
    protected $casts = [
        'started_at' => 'datetime',
        'completed_at' => 'datetime',
        'is_completed' => 'boolean',
    ];

    /**
     * Get the survey that owns the response.
     */
    public function survey(): BelongsTo
    {
        return $this->belongsTo(Survey::class);
    }

    /**
     * Get the user who submitted the response.
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Get the answers for this response.
     */
    public function answers(): HasMany
    {
        return $this->hasMany(SurveyAnswer::class);
    }

    /**
     * Check if response is completed.
     */
    public function isCompleted(): bool
    {
        return $this->is_completed === true;
    }

    /**
     * Check if response is anonymous.
     */
    public function isAnonymous(): bool
    {
        return is_null($this->user_id);
    }

    /**
     * Get the completion percentage.
     */
    public function getCompletionPercentage(): float
    {
        $totalQuestions = $this->survey->questions()->count();

        if ($totalQuestions === 0) {
            return 0.0;
        }

        $answeredQuestions = $this->answers()->distinct('survey_question_id')->count();

        return round(($answeredQuestions / $totalQuestions) * 100, 2);
    }

    /**
     * Scope a query to only include completed responses.
     */
    public function scopeCompleted($query)
    {
        return $query->where('is_completed', true);
    }

    /**
     * Scope a query to only include responses by user.
     */
    public function scopeByUser($query, int $userId)
    {
        return $query->where('user_id', $userId);
    }

    /**
     * Scope a query to only include responses by survey.
     */
    public function scopeBySurvey($query, int $surveyId)
    {
        return $query->where('survey_id', $surveyId);
    }
}
