<?php

namespace App\Http\Resources\SPMI;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class SpmiStandardResource extends JsonResource
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
            'code' => $this->code,
            'name' => $this->name,
            'category' => $this->category,
            'category_label' => $this->getCategoryLabel(),
            'description' => $this->description,
            'objective' => $this->objective,
            'scope' => $this->scope,
            'reference' => $this->reference,
            'status' => $this->status,
            'status_label' => $this->getStatusLabel(),
            'effective_date' => $this->effective_date?->format('Y-m-d'),
            'review_date' => $this->review_date?->format('Y-m-d'),
            'version' => $this->version,
            'unit_kerja' => $this->whenLoaded('unitKerja', function () {
                return [
                    'id' => $this->unitKerja->id,
                    'name' => $this->unitKerja->name,
                    'code' => $this->unitKerja->code,
                ];
            }),
            'creator' => $this->whenLoaded('creator', function () {
                return [
                    'id' => $this->creator->id,
                    'name' => $this->creator->name,
                    'email' => $this->creator->email,
                ];
            }),
            'approver' => $this->whenLoaded('approver', function () {
                return $this->approver ? [
                    'id' => $this->approver->id,
                    'name' => $this->approver->name,
                    'email' => $this->approver->email,
                ] : null;
            }),
            'approved_at' => $this->approved_at?->format('Y-m-d H:i:s'),
            'is_active' => (bool) $this->is_active,
            'is_draft' => $this->status === 'draft',
            'is_approved' => (bool) $this->is_approved,
            'indicators_count' => $this->whenCounted('indicators'),
            'created_at' => $this->created_at?->format('Y-m-d H:i:s'),
            'updated_at' => $this->updated_at?->format('Y-m-d H:i:s'),
        ];
    }

    /**
     * Get category label.
     */
    private function getCategoryLabel(): string
    {
        return match($this->category) {
            'pendidikan' => 'Pendidikan',
            'penelitian' => 'Penelitian',
            'pengabdian' => 'Pengabdian',
            'pengelolaan' => 'Pengelolaan',
            default => $this->category,
        };
    }

    /**
     * Get status label.
     */
    private function getStatusLabel(): string
    {
        return match($this->status) {
            'draft' => 'Draft',
            'active' => 'Aktif',
            'inactive' => 'Tidak Aktif',
            default => $this->status,
        };
    }
}
