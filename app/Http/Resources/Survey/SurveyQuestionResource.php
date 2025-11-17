<?php

namespace App\Http\Resources\Survey;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class SurveyQuestionResource extends JsonResource
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
            'survey_id' => $this->survey_id,
            'question_text' => $this->question_text,
            'question_type' => $this->question_type,
            'question_type_label' => $this->getQuestionTypeLabel(),
            'options' => $this->options ?? [],
            'is_required' => (bool) $this->is_required,
            'order' => $this->order,
            'validation_rules' => $this->validation_rules ?? [],
            'conditional_logic' => $this->conditional_logic ?? [],
            'help_text' => $this->help_text,
            'survey' => $this->whenLoaded('survey', function () {
                return [
                    'id' => $this->survey->id,
                    'title' => $this->survey->title,
                    'status' => $this->survey->status,
                ];
            }),
            'created_at' => $this->created_at?->format('Y-m-d H:i:s'),
            'updated_at' => $this->updated_at?->format('Y-m-d H:i:s'),
        ];
    }

    /**
     * Get question type label.
     */
    private function getQuestionTypeLabel(): string
    {
        return match($this->question_type) {
            'text' => 'Teks Pendek',
            'textarea' => 'Teks Panjang',
            'radio' => 'Pilihan Tunggal',
            'checkbox' => 'Pilihan Ganda',
            'dropdown' => 'Dropdown',
            'rating' => 'Rating',
            'matrix' => 'Matrix',
            default => $this->question_type,
        };
    }
}
