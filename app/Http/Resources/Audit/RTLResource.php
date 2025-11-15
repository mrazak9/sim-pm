<?php

namespace App\Http\Resources\Audit;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class RTLResource extends JsonResource
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
            'audit_finding_id' => $this->audit_finding_id,
            'audit_finding' => $this->whenLoaded('auditFinding', function () {
                return [
                    'id' => $this->auditFinding->id,
                    'finding_code' => $this->auditFinding->finding_code,
                    'category' => $this->auditFinding->category,
                    'category_label' => $this->auditFinding->getCategoryLabel(),
                    'description' => $this->auditFinding->description,
                ];
            }),
            'rtl_code' => $this->rtl_code,
            'action_plan' => $this->action_plan,
            'success_indicator' => $this->success_indicator,
            'implementation_steps' => $this->implementation_steps,
            'pic_id' => $this->pic_id,
            'pic' => $this->whenLoaded('pic', function () {
                return [
                    'id' => $this->pic->id,
                    'name' => $this->pic->name,
                    'email' => $this->pic->email,
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
            'target_date' => $this->target_date?->format('Y-m-d'),
            'days_until_target' => $this->getDaysUntilTarget(),
            'budget' => $this->budget ? (float) $this->budget : null,
            'budget_formatted' => $this->budget ? 'Rp ' . number_format($this->budget, 0, ',', '.') : null,
            'resources_needed' => $this->resources_needed,
            'status' => $this->status,
            'status_label' => $this->getStatusLabel(),
            'completion_percentage' => $this->completion_percentage,
            'current_status_notes' => $this->current_status_notes,
            'started_at' => $this->started_at?->format('Y-m-d H:i:s'),
            'completed_at' => $this->completed_at?->format('Y-m-d H:i:s'),
            'completion_notes' => $this->completion_notes,
            'verified_by' => $this->verified_by,
            'verifier' => $this->whenLoaded('verifier', function () {
                return $this->verifier ? [
                    'id' => $this->verifier->id,
                    'name' => $this->verifier->name,
                    'email' => $this->verifier->email,
                ] : null;
            }),
            'verified_at' => $this->verified_at?->format('Y-m-d H:i:s'),
            'verification_status' => $this->verification_status,
            'verification_status_label' => $this->getVerificationStatusLabel(),
            'verification_notes' => $this->verification_notes,
            'risk_level' => $this->risk_level,
            'risk_level_label' => $this->getRiskLevelLabel(),
            'risk_description' => $this->risk_description,
            'progress_updates_count' => $this->whenCounted('progressUpdates'),
            'latest_progress' => $this->whenLoaded('latestProgress', function () {
                return $this->latestProgress ? [
                    'id' => $this->latestProgress->id,
                    'progress_date' => $this->latestProgress->progress_date?->format('Y-m-d'),
                    'progress_percentage' => $this->latestProgress->progress_percentage,
                    'description' => $this->latestProgress->description,
                ] : null;
            }),
            'is_overdue' => $this->isOverdue(),
            'is_completed' => $this->isCompleted(),
            'created_at' => $this->created_at?->format('Y-m-d H:i:s'),
            'updated_at' => $this->updated_at?->format('Y-m-d H:i:s'),
        ];
    }

    /**
     * Get risk level label.
     */
    private function getRiskLevelLabel(): string
    {
        return match($this->risk_level) {
            'low' => 'Rendah',
            'medium' => 'Sedang',
            'high' => 'Tinggi',
            default => $this->risk_level,
        };
    }

    /**
     * Get verification status label.
     */
    private function getVerificationStatusLabel(): ?string
    {
        if (!$this->verification_status) return null;

        return match($this->verification_status) {
            'pending' => 'Menunggu Verifikasi',
            'approved' => 'Disetujui',
            'rejected' => 'Ditolak',
            'revision' => 'Perlu Revisi',
            default => $this->verification_status,
        };
    }
}
