<?php

namespace App\Http\Resources\Audit;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class AuditScheduleResource extends JsonResource
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
            'audit_plan_id' => $this->audit_plan_id,
            'audit_plan' => $this->whenLoaded('auditPlan', function () {
                return [
                    'id' => $this->auditPlan->id,
                    'title' => $this->auditPlan->title,
                    'periode' => $this->auditPlan->periode,
                ];
            }),
            'unit_kerja_id' => $this->unit_kerja_id,
            'unit_kerja' => $this->whenLoaded('unitKerja', function () {
                return [
                    'id' => $this->unitKerja->id,
                    'nama' => $this->unitKerja->nama,
                    'kode' => $this->unitKerja->kode,
                ];
            }),
            'auditor_lead_id' => $this->auditor_lead_id,
            'auditor_lead' => $this->whenLoaded('auditorLead', function () {
                return [
                    'id' => $this->auditorLead->id,
                    'name' => $this->auditorLead->name,
                    'email' => $this->auditorLead->email,
                ];
            }),
            'auditors' => $this->whenLoaded('auditors', function () {
                return $this->auditors->map(function ($auditor) {
                    return [
                        'id' => $auditor->id,
                        'name' => $auditor->name,
                        'email' => $auditor->email,
                        'role' => $auditor->pivot->role,
                    ];
                });
            }),
            'scheduled_date' => $this->scheduled_date?->format('Y-m-d H:i:s'),
            'estimated_duration' => $this->estimated_duration,
            'estimated_duration_formatted' => $this->getEstimatedDurationFormatted(),
            'location' => $this->location,
            'status' => $this->status,
            'status_label' => $this->getStatusLabel(),
            'agenda' => $this->agenda,
            'notes' => $this->notes,
            'preparation_notes' => $this->preparation_notes,
            'actual_start' => $this->actual_start?->format('Y-m-d H:i:s'),
            'actual_end' => $this->actual_end?->format('Y-m-d H:i:s'),
            'actual_duration' => $this->getActualDuration(),
            'actual_duration_formatted' => $this->getActualDurationFormatted(),
            'summary' => $this->summary,
            'findings_count' => $this->whenCounted('findings'),
            'evidence_count' => $this->whenCounted('evidence'),
            'is_editable' => $this->isEditable(),
            'created_at' => $this->created_at?->format('Y-m-d H:i:s'),
            'updated_at' => $this->updated_at?->format('Y-m-d H:i:s'),
        ];
    }

    /**
     * Get estimated duration formatted.
     */
    private function getEstimatedDurationFormatted(): string
    {
        $hours = floor($this->estimated_duration / 60);
        $minutes = $this->estimated_duration % 60;

        if ($hours > 0 && $minutes > 0) {
            return "{$hours} jam {$minutes} menit";
        } elseif ($hours > 0) {
            return "{$hours} jam";
        } else {
            return "{$minutes} menit";
        }
    }

    /**
     * Get actual duration formatted.
     */
    private function getActualDurationFormatted(): ?string
    {
        $duration = $this->getActualDuration();
        if (!$duration) return null;

        $hours = floor($duration / 60);
        $minutes = $duration % 60;

        if ($hours > 0 && $minutes > 0) {
            return "{$hours} jam {$minutes} menit";
        } elseif ($hours > 0) {
            return "{$hours} jam";
        } else {
            return "{$minutes} menit";
        }
    }

    /**
     * Get status label.
     */
    private function getStatusLabel(): string
    {
        return match($this->status) {
            'scheduled' => 'Terjadwal',
            'ongoing' => 'Sedang Berjalan',
            'completed' => 'Selesai',
            'cancelled' => 'Dibatalkan',
            'rescheduled' => 'Dijadwal Ulang',
            default => $this->status,
        };
    }
}
