<?php

namespace App\Http\Resources\SPMI;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class SpmiMonitoringResource extends JsonResource
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
            'monitoring_code' => $this->monitoring_code,
            'title' => $this->title,
            'description' => $this->description,
            'monitoring_date' => $this->monitoring_date?->format('Y-m-d'),
            'monitoring_type' => $this->monitoring_type,
            'monitoring_type_label' => $this->getMonitoringTypeLabel(),
            'findings' => $this->findings,
            'strengths' => $this->strengths,
            'weaknesses' => $this->weaknesses,
            'opportunities' => $this->opportunities,
            'threats' => $this->threats,
            'recommendations' => $this->recommendations,
            'compliance_level' => $this->compliance_level,
            'compliance_level_label' => $this->getComplianceLevelLabel(),
            'compliance_score' => $this->compliance_score,
            'status' => $this->status,
            'status_label' => $this->getStatusLabel(),
            'spmi_standard' => $this->whenLoaded('spmiStandard', function () {
                return [
                    'id' => $this->spmiStandard->id,
                    'code' => $this->spmiStandard->code,
                    'name' => $this->spmiStandard->name,
                ];
            }),
            'tahun_akademik' => $this->whenLoaded('tahunAkademik', function () {
                return [
                    'id' => $this->tahunAkademik->id,
                    'tahun' => $this->tahunAkademik->tahun,
                    'semester' => $this->tahunAkademik->semester,
                ];
            }),
            'monitored_by' => $this->whenLoaded('monitoredBy', function () {
                return [
                    'id' => $this->monitoredBy->id,
                    'name' => $this->monitoredBy->name,
                    'email' => $this->monitoredBy->email,
                ];
            }),
            'unit_kerja' => $this->whenLoaded('unitKerja', function () {
                return [
                    'id' => $this->unitKerja->id,
                    'name' => $this->unitKerja->name,
                    'code' => $this->unitKerja->code,
                ];
            }),
            'report_file' => $this->report_file,
            'created_at' => $this->created_at?->format('Y-m-d H:i:s'),
            'updated_at' => $this->updated_at?->format('Y-m-d H:i:s'),
        ];
    }

    /**
     * Get monitoring type label.
     */
    private function getMonitoringTypeLabel(): string
    {
        return match($this->monitoring_type) {
            'desk_evaluation' => 'Desk Evaluation',
            'field_visit' => 'Field Visit',
            'interview' => 'Interview',
            'document_review' => 'Document Review',
            'mixed' => 'Mixed',
            default => $this->monitoring_type,
        };
    }

    /**
     * Get compliance level label.
     */
    private function getComplianceLevelLabel(): string
    {
        return match($this->compliance_level) {
            'belum_terpenuhi' => 'Belum Terpenuhi',
            'terpenuhi_sebagian' => 'Terpenuhi Sebagian',
            'terpenuhi' => 'Terpenuhi',
            'terpenuhi_sempurna' => 'Terpenuhi Sempurna',
            default => $this->compliance_level,
        };
    }

    /**
     * Get status label.
     */
    private function getStatusLabel(): string
    {
        return match($this->status) {
            'draft' => 'Draft',
            'completed' => 'Selesai',
            'published' => 'Dipublikasikan',
            default => $this->status,
        };
    }
}
