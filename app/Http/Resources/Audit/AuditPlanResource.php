<?php

namespace App\Http\Resources\Audit;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class AuditPlanResource extends JsonResource
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
            'title' => $this->title,
            'description' => $this->description,
            'tahun_akademik_id' => $this->tahun_akademik_id,
            'tahun_akademik' => $this->whenLoaded('tahunAkademik', function () {
                return [
                    'id' => $this->tahunAkademik->id,
                    'tahun' => $this->tahunAkademik->tahun,
                    'semester' => $this->tahunAkademik->semester,
                ];
            }),
            'periode' => $this->periode,
            'periode_label' => $this->getPeriodeLabel(),
            'start_date' => $this->start_date?->format('Y-m-d'),
            'end_date' => $this->end_date?->format('Y-m-d'),
            'status' => $this->status,
            'status_label' => $this->getStatusLabel(),
            'objectives' => $this->objectives,
            'scope' => $this->scope,
            'created_by' => $this->created_by,
            'creator' => $this->whenLoaded('creator', function () {
                return [
                    'id' => $this->creator->id,
                    'name' => $this->creator->name,
                    'email' => $this->creator->email,
                ];
            }),
            'approved_by' => $this->approved_by,
            'approver' => $this->whenLoaded('approver', function () {
                return $this->approver ? [
                    'id' => $this->approver->id,
                    'name' => $this->approver->name,
                    'email' => $this->approver->email,
                ] : null;
            }),
            'approved_at' => $this->approved_at?->format('Y-m-d H:i:s'),
            'schedules_count' => $this->whenCounted('schedules'),
            'schedules' => AuditScheduleResource::collection($this->whenLoaded('schedules')),
            'is_editable' => $this->isEditable(),
            'is_approved' => $this->isApproved(),
            'created_at' => $this->created_at?->format('Y-m-d H:i:s'),
            'updated_at' => $this->updated_at?->format('Y-m-d H:i:s'),
        ];
    }

    /**
     * Get periode label.
     */
    private function getPeriodeLabel(): string
    {
        return match($this->periode) {
            'semester_1' => 'Semester 1',
            'semester_2' => 'Semester 2',
            'tahunan' => 'Tahunan',
            default => $this->periode,
        };
    }

    /**
     * Get status label.
     */
    private function getStatusLabel(): string
    {
        return match($this->status) {
            'draft' => 'Draft',
            'approved' => 'Disetujui',
            'ongoing' => 'Sedang Berjalan',
            'completed' => 'Selesai',
            'cancelled' => 'Dibatalkan',
            default => $this->status,
        };
    }
}
