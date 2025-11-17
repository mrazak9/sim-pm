<?php

namespace App\Http\Resources\Survey;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class SurveyResource extends JsonResource
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
            'survey_code' => $this->survey_code ?? null,
            'title' => $this->title,
            'description' => $this->description,
            'type' => $this->type,
            'type_label' => $this->getTypeLabel(),
            'status' => $this->status,
            'status_label' => $this->getStatusLabel(),
            'start_date' => $this->start_date?->format('Y-m-d'),
            'end_date' => $this->end_date?->format('Y-m-d'),
            'max_responses' => $this->max_responses,
            'is_anonymous' => (bool) $this->is_anonymous,
            'allow_multiple_responses' => (bool) $this->allow_multiple_responses,
            'require_login' => (bool) $this->require_login,
            'show_results' => (bool) $this->show_results,
            'welcome_message' => $this->welcome_message,
            'thank_you_message' => $this->thank_you_message,
            'unit_kerja' => $this->whenLoaded('unitKerja', function () {
                return [
                    'id' => $this->unitKerja->id,
                    'name' => $this->unitKerja->name,
                    'code' => $this->unitKerja->code ?? null,
                ];
            }),
            'creator' => $this->whenLoaded('creator', function () {
                return [
                    'id' => $this->creator->id,
                    'name' => $this->creator->name,
                    'email' => $this->creator->email,
                ];
            }),
            'questions_count' => $this->whenCounted('questions'),
            'responses_count' => $this->whenCounted('responses'),
            'response_rate' => $this->when(
                $this->max_responses && $this->max_responses > 0,
                fn() => $this->getResponseRate()
            ),
            'can_be_edited' => $this->canBeEdited(),
            'can_be_responded' => $this->canBeResponded(),
            'is_published' => $this->isPublished(),
            'is_closed' => $this->isClosed(),
            'questions' => SurveyQuestionResource::collection($this->whenLoaded('questions')),
            'created_at' => $this->created_at?->format('Y-m-d H:i:s'),
            'updated_at' => $this->updated_at?->format('Y-m-d H:i:s'),
        ];
    }

    /**
     * Get type label.
     */
    private function getTypeLabel(): string
    {
        return match($this->type) {
            'internal' => 'Internal',
            'external' => 'Eksternal',
            'public' => 'Publik',
            default => $this->type,
        };
    }

    /**
     * Get status label.
     */
    private function getStatusLabel(): string
    {
        return match($this->status) {
            'draft' => 'Draft',
            'published' => 'Dipublikasikan',
            'closed' => 'Ditutup',
            default => $this->status,
        };
    }
}
