<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

class Survey extends Model
{
    use HasFactory, SoftDeletes;

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'title',
        'description',
        'type',
        'status',
        'start_date',
        'end_date',
        'max_responses',
        'is_anonymous',
        'allow_multiple_responses',
        'require_login',
        'show_results',
        'welcome_message',
        'thank_you_message',
        'unit_kerja_id',
        'created_by',
    ];

    /**
     * The attributes that should be cast.
     *
     * @var array<string, string>
     */
    protected $casts = [
        'start_date' => 'date',
        'end_date' => 'date',
        'is_anonymous' => 'boolean',
        'allow_multiple_responses' => 'boolean',
        'require_login' => 'boolean',
        'show_results' => 'boolean',
    ];

    /**
     * Get the user who created the survey.
     */
    public function creator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    /**
     * Get the unit kerja that owns the survey.
     */
    public function unitKerja(): BelongsTo
    {
        return $this->belongsTo(UnitKerja::class);
    }

    /**
     * Get the questions for this survey.
     */
    public function questions(): HasMany
    {
        return $this->hasMany(SurveyQuestion::class)->orderBy('order');
    }

    /**
     * Get the responses for this survey.
     */
    public function responses(): HasMany
    {
        return $this->hasMany(SurveyResponse::class);
    }

    /**
     * Get the distributions for this survey.
     */
    public function distributions(): HasMany
    {
        return $this->hasMany(SurveyDistribution::class);
    }

    /**
     * Check if survey is published.
     */
    public function isPublished(): bool
    {
        return $this->status === 'published';
    }

    /**
     * Check if survey is closed.
     */
    public function isClosed(): bool
    {
        return $this->status === 'closed';
    }

    /**
     * Check if survey can be edited.
     */
    public function canBeEdited(): bool
    {
        return $this->status === 'draft';
    }

    /**
     * Check if survey can be responded to.
     */
    public function canBeResponded(): bool
    {
        if (!$this->isPublished()) {
            return false;
        }

        $now = now();

        if ($this->start_date && $now->lessThan($this->start_date)) {
            return false;
        }

        if ($this->end_date && $now->greaterThan($this->end_date)) {
            return false;
        }

        if ($this->max_responses && $this->responses()->count() >= $this->max_responses) {
            return false;
        }

        return true;
    }

    /**
     * Get the response rate percentage.
     */
    public function getResponseRate(): float
    {
        if (!$this->max_responses || $this->max_responses === 0) {
            return 0.0;
        }

        $responseCount = $this->responses()->where('is_completed', true)->count();

        return round(($responseCount / $this->max_responses) * 100, 2);
    }

    /**
     * Scope a query to only include published surveys.
     */
    public function scopePublished($query)
    {
        return $query->where('status', 'published');
    }

    /**
     * Scope a query to only include active surveys.
     */
    public function scopeActive($query)
    {
        $now = now();

        return $query->where('status', 'published')
                     ->where(function ($q) use ($now) {
                         $q->whereNull('start_date')
                           ->orWhere('start_date', '<=', $now);
                     })
                     ->where(function ($q) use ($now) {
                         $q->whereNull('end_date')
                           ->orWhere('end_date', '>=', $now);
                     });
    }

    /**
     * Scope a query to only include surveys by type.
     */
    public function scopeByType($query, string $type)
    {
        return $query->where('type', $type);
    }

    /**
     * Scope a query to only include surveys by creator.
     */
    public function scopeByCreator($query, int $userId)
    {
        return $query->where('created_by', $userId);
    }
}
