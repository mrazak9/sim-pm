<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class SurveyAnswer extends Model
{
    use HasFactory;

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'survey_response_id',
        'survey_question_id',
        'answer_text',
        'answer_option',
        'answer_number',
        'answer_date',
    ];

    /**
     * The attributes that should be cast.
     *
     * @var array<string, string>
     */
    protected $casts = [
        'answer_option' => 'array',
    ];

    /**
     * Get the response that owns the answer.
     */
    public function response(): BelongsTo
    {
        return $this->belongsTo(SurveyResponse::class, 'survey_response_id');
    }

    /**
     * Get the question that owns the answer.
     */
    public function question(): BelongsTo
    {
        return $this->belongsTo(SurveyQuestion::class, 'survey_question_id');
    }

    /**
     * Get the answer value based on question type.
     */
    public function getAnswerValue(): mixed
    {
        $questionType = $this->question->question_type ?? null;

        return match ($questionType) {
            'text', 'textarea', 'email' => $this->answer_text,
            'number', 'rating' => $this->answer_number,
            'date', 'datetime' => $this->answer_date,
            'radio', 'select' => $this->answer_text,
            'checkbox', 'multiple_choice' => $this->answer_option,
            default => $this->answer_text,
        };
    }

    /**
     * Get the formatted answer for display.
     */
    public function getFormattedAnswer(): string
    {
        $value = $this->getAnswerValue();

        if (is_array($value)) {
            return implode(', ', $value);
        }

        if (is_null($value)) {
            return '';
        }

        return (string) $value;
    }
}
