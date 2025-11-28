<?php

namespace App\Http\Resources\SPMI;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class SpmiIndicatorResource extends JsonResource
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
            'spmi_standard_id' => $this->spmi_standard_id,
            'code' => $this->code,
            'name' => $this->name,
            'description' => $this->description,
            'measurement_unit' => $this->measurement_unit,
            'measurement_type' => $this->measurement_type,
            'measurement_type_label' => $this->getMeasurementTypeLabel(),
            'formula' => $this->formula,
            'data_source' => $this->data_source,
            'frequency' => $this->frequency,
            'frequency_label' => $this->getFrequencyLabel(),
            'pic_id' => $this->pic_id,
            'is_active' => (bool) $this->is_active,
            'spmi_standard' => $this->whenLoaded('spmiStandard', function () {
                return [
                    'id' => $this->spmiStandard->id,
                    'code' => $this->spmiStandard->code,
                    'name' => $this->spmiStandard->name,
                ];
            }),
            'pic' => $this->whenLoaded('pic', function () {
                return [
                    'id' => $this->pic->id,
                    'name' => $this->pic->name,
                    'email' => $this->pic->email,
                ];
            }),
            'targets_count' => $this->whenCounted('targets'),
            'created_at' => $this->created_at?->format('Y-m-d H:i:s'),
            'updated_at' => $this->updated_at?->format('Y-m-d H:i:s'),
        ];
    }

    /**
     * Get measurement type label.
     */
    private function getMeasurementTypeLabel(): string
    {
        return match($this->measurement_type) {
            'kuantitatif' => 'Kuantitatif',
            'kualitatif' => 'Kualitatif',
            default => $this->measurement_type,
        };
    }

    /**
     * Get frequency label.
     */
    private function getFrequencyLabel(): string
    {
        return match($this->frequency) {
            'harian' => 'Harian',
            'mingguan' => 'Mingguan',
            'bulanan' => 'Bulanan',
            'semesteran' => 'Semesteran',
            'tahunan' => 'Tahunan',
            default => $this->frequency,
        };
    }
}
