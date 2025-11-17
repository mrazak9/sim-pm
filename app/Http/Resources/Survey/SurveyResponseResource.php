<?php

namespace App\Http\Resources\Survey;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class SurveyResponseResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'response_code' => $this->response_code ?? null,
            'survey' => $this->whenLoaded('survey', function () {
                return [
                    'id' => $this->survey->id,
                    'title' => $this->survey->title,
                    'type' => $this->survey->type,
                    'status' => $this->survey->status,
                ];
            }),
            'user' => $this->whenLoaded('user', function () {
                return $this->user ? [
                    'id' => $this->user->id,
                    'name' => $this->user->name,
                    'email' => $this->user->email,
                ] : null;
            }),
            'respondent_name' => $this->respondent_name,
            'respondent_email' => $this->respondent_email,
            'is_completed' => (bool) $this->is_completed,
            'is_anonymous' => $this->isAnonymous(),
            'started_at' => $this->started_at?->format('Y-m-d H:i:s'),
            'completed_at' => $this->completed_at?->format('Y-m-d H:i:s'),
            'completion_time_minutes' => $this->when(
                $this->started_at && $this->completed_at,
                fn() => round($this->started_at->diffInMinutes($this->completed_at), 2)
            ),
            'completion_percentage' => $this->when(
                $this->relationLoaded('answers') && $this->relationLoaded('survey'),
                fn() => $this->getCompletionPercentage()
            ),
            'answers' => $this->whenLoaded('answers', function () {
                return $this->answers->map(function ($answer) {
                    return [
                        'id' => $answer->id,
                        'question_id' => $answer->survey_question_id,
                        'question_text' => $answer->question->question_text ?? null,
                        'answer_text' => $answer->answer_text,
                        'answer_option' => $answer->answer_option,
                    ];
                });
            }),
            'ip_address' => $this->when(
                $request->user()?->hasRole('admin'),
                $this->ip_address
            ),
            'user_agent' => $this->when(
                $request->user()?->hasRole('admin'),
                $this->user_agent
            ),
            'created_at' => $this->created_at?->format('Y-m-d H:i:s'),
            'updated_at' => $this->updated_at?->format('Y-m-d H:i:s'),
        ];
    }
}
