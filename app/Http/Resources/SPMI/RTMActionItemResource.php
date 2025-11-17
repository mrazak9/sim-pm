<?php

namespace App\Http\Resources\SPMI;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class RTMActionItemResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        $daysUntilDue = null;
        if ($this->due_date) {
            $daysUntilDue = now()->diffInDays($this->due_date, false);
        }

        $isOverdue = false;
        if ($this->due_date && $this->status !== 'completed') {
            $isOverdue = $this->due_date->isPast();
        }

        return [
            'id' => $this->id,
            'action_code' => $this->action_code,
            'title' => $this->title,
            'description' => $this->description,
            'priority' => $this->priority,
            'priority_label' => $this->getPriorityLabel(),
            'due_date' => $this->due_date?->format('Y-m-d'),
            'days_until_due' => $daysUntilDue,
            'status' => $this->status,
            'status_label' => $this->getStatusLabel(),
            'completion_percentage' => $this->completion_percentage ?? 0,
            'progress_notes' => $this->progress_notes,
            'completed_at' => $this->completed_at?->format('Y-m-d H:i:s'),
            'completion_remarks' => $this->completion_remarks,
            'evidence_file' => $this->evidence_file,
            'rtm' => $this->whenLoaded('rtm', function () {
                return [
                    'id' => $this->rtm->id,
                    'rtm_code' => $this->rtm->rtm_code,
                    'title' => $this->rtm->title,
                ];
            }),
            'pic' => $this->whenLoaded('pic', function () {
                return [
                    'id' => $this->pic->id,
                    'name' => $this->pic->name,
                    'email' => $this->pic->email,
                ];
            }),
            'unit_kerja' => $this->whenLoaded('unitKerja', function () {
                return [
                    'id' => $this->unitKerja->id,
                    'name' => $this->unitKerja->name,
                    'code' => $this->unitKerja->code,
                ];
            }),
            'is_overdue' => $isOverdue,
            'is_completed' => $this->status === 'completed',
            'created_at' => $this->created_at?->format('Y-m-d H:i:s'),
            'updated_at' => $this->updated_at?->format('Y-m-d H:i:s'),
        ];
    }

    /**
     * Get priority label.
     */
    private function getPriorityLabel(): string
    {
        return match($this->priority) {
            'low' => 'Rendah',
            'medium' => 'Sedang',
            'high' => 'Tinggi',
            'critical' => 'Kritis',
            default => $this->priority,
        };
    }

    /**
     * Get status label.
     */
    private function getStatusLabel(): string
    {
        return match($this->status) {
            'pending' => 'Pending',
            'in_progress' => 'Dalam Proses',
            'completed' => 'Selesai',
            'overdue' => 'Overdue',
            default => $this->status,
        };
    }
}
