<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class SurveyQuestion extends Model
{
    use HasFactory;

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'survey_id',
        'question_text',
        'question_type',
        'options',
        'is_required',
        'order',
        'validation_rules',
        'conditional_logic',
        'help_text',
    ];

    /**
     * The attributes that should be cast.
     *
     * @var array<string, string>
     */
    protected $casts = [
        'options' => 'array',
        'validation_rules' => 'array',
        'conditional_logic' => 'array',
        'is_required' => 'boolean',
    ];

    /**
     * Get the survey that owns the question.
     */
    public function survey(): BelongsTo
    {
        return $this->belongsTo(Survey::class);
    }

    /**
     * Get the answers for this question.
     */
    public function answers(): HasMany
    {
        return $this->hasMany(SurveyAnswer::class);
    }

    /**
     * Check if question is required.
     */
    public function isRequired(): bool
    {
        return $this->is_required === true;
    }

    /**
     * Get the question options as an array.
     */
    public function getOptions(): array
    {
        return $this->options ?? [];
    }

    /**
     * Get the validation rules as an array.
     */
    public function getValidationRules(): array
    {
        return $this->validation_rules ?? [];
    }
}
