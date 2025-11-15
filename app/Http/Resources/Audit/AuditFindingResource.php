<?php

namespace App\Http\Resources\Audit;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class AuditFindingResource extends JsonResource
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
            'audit_schedule_id' => $this->audit_schedule_id,
            'audit_schedule' => $this->whenLoaded('auditSchedule', function () {
                return [
                    'id' => $this->auditSchedule->id,
                    'scheduled_date' => $this->auditSchedule->scheduled_date?->format('Y-m-d H:i:s'),
                    'unit_kerja' => $this->auditSchedule->unitKerja ? [
                        'id' => $this->auditSchedule->unitKerja->id,
                        'nama' => $this->auditSchedule->unitKerja->nama,
                    ] : null,
                ];
            }),
            'finding_code' => $this->finding_code,
            'category' => $this->category,
            'category_label' => $this->getCategoryLabel(),
            'standar_reference' => $this->standar_reference,
            'clause' => $this->clause,
            'description' => $this->description,
            'evidence_description' => $this->evidence_description,
            'root_cause' => $this->root_cause,
            'recommendation' => $this->recommendation,
            'impact' => $this->impact,
            'pic_id' => $this->pic_id,
            'pic' => $this->whenLoaded('pic', function () {
                return $this->pic ? [
                    'id' => $this->pic->id,
                    'name' => $this->pic->name,
                    'email' => $this->pic->email,
                ] : null;
            }),
            'unit_kerja_id' => $this->unit_kerja_id,
            'unit_kerja' => $this->whenLoaded('unitKerja', function () {
                return [
                    'id' => $this->unitKerja->id,
                    'nama' => $this->unitKerja->nama,
                    'kode' => $this->unitKerja->kode,
                ];
            }),
            'due_date' => $this->due_date?->format('Y-m-d'),
            'status' => $this->status,
            'status_label' => $this->getStatusLabel(),
            'resolution_notes' => $this->resolution_notes,
            'resolved_at' => $this->resolved_at?->format('Y-m-d H:i:s'),
            'verified_by' => $this->verified_by,
            'verifier' => $this->whenLoaded('verifier', function () {
                return $this->verifier ? [
                    'id' => $this->verifier->id,
                    'name' => $this->verifier->name,
                    'email' => $this->verifier->email,
                ] : null;
            }),
            'verified_at' => $this->verified_at?->format('Y-m-d H:i:s'),
            'priority' => $this->priority,
            'priority_label' => $this->getPriorityLabel(),
            'severity' => $this->severity,
            'severity_label' => $this->getSeverityLabel(),
            'evidence_count' => $this->whenCounted('evidence'),
            'has_rtl' => $this->whenLoaded('rtl', fn() => $this->rtl !== null),
            'is_overdue' => $this->isOverdue(),
            'is_resolved' => $this->isResolved(),
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
     * Get severity label.
     */
    private function getSeverityLabel(): string
    {
        return match($this->severity) {
            'low' => 'Rendah',
            'medium' => 'Sedang',
            'high' => 'Tinggi',
            default => $this->severity,
        };
    }
}
