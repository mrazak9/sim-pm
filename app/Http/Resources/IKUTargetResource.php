<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class IKUTargetResource extends JsonResource
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
            'iku_id' => $this->iku_id,
            'iku' => new IKUResource($this->whenLoaded('iku')),
            'tahun_akademik_id' => $this->tahun_akademik_id,
            'tahun_akademik' => $this->whenLoaded('tahunAkademik', function() {
                return [
                    'id' => $this->tahunAkademik->id,
                    'nama' => $this->tahunAkademik->nama,
                    'tahun_mulai' => $this->tahunAkademik->tahun_mulai,
                    'tahun_selesai' => $this->tahunAkademik->tahun_selesai,
                ];
            }),
            'unit_kerja_id' => $this->unit_kerja_id,
            'unit_kerja' => $this->whenLoaded('unitKerja', function() {
                return [
                    'id' => $this->unitKerja->id,
                    'nama' => $this->unitKerja->nama,
                    'kode' => $this->unitKerja->kode ?? null,
                ];
            }),
            'program_studi_id' => $this->program_studi_id,
            'program_studi' => $this->whenLoaded('programStudi', function() {
                return [
                    'id' => $this->programStudi->id,
                    'nama' => $this->programStudi->nama,
                    'kode' => $this->programStudi->kode ?? null,
                ];
            }),
            'target_value' => (float) $this->target_value,
            'periode' => $this->periode,
            'periode_label' => $this->getPeriodeLabel(),
            'keterangan' => $this->keterangan,
            'total_capaian' => (float) $this->total_capaian,
            'persentase_capaian' => round((float) $this->persentase_capaian, 2),
            'status' => $this->getStatus(),
            'status_label' => $this->getStatusLabel(),
            'status_color' => $this->getStatusColor(),
            'progress_count' => $this->when(isset($this->progress), function() {
                return $this->progress->count();
            }),
            'latest_progress' => new IKUProgressResource($this->whenLoaded('latestProgress')),
            'progress' => IKUProgressResource::collection($this->whenLoaded('progress')),
            'created_at' => $this->created_at?->format('Y-m-d H:i:s'),
            'updated_at' => $this->updated_at?->format('Y-m-d H:i:s'),
        ];
    }

    /**
     * Get periode label in Indonesian
     */
    private function getPeriodeLabel(): string
    {
        $labels = [
            'tahunan' => 'Tahunan',
            'semester_1' => 'Semester 1',
            'semester_2' => 'Semester 2',
            'triwulan_1' => 'Triwulan 1',
            'triwulan_2' => 'Triwulan 2',
            'triwulan_3' => 'Triwulan 3',
            'triwulan_4' => 'Triwulan 4',
        ];

        return $labels[$this->periode] ?? $this->periode;
    }

    /**
     * Get target status
     */
    private function getStatus(): string
    {
        $persentase = $this->persentase_capaian;

        if ($persentase >= 100) {
            return 'achieved';
        } elseif ($persentase >= 75) {
            return 'on_track';
        } elseif ($persentase >= 50) {
            return 'warning';
        } else {
            return 'critical';
        }
    }

    /**
     * Get status label
     */
    private function getStatusLabel(): string
    {
        $labels = [
            'achieved' => 'Tercapai',
            'on_track' => 'Sesuai Target',
            'warning' => 'Perlu Perhatian',
            'critical' => 'Kritis',
        ];

        return $labels[$this->getStatus()] ?? '-';
    }

    /**
     * Get status color for UI
     */
    private function getStatusColor(): string
    {
        $colors = [
            'achieved' => 'blue',
            'on_track' => 'green',
            'warning' => 'yellow',
            'critical' => 'red',
        ];

        return $colors[$this->getStatus()] ?? 'gray';
    }

    /**
     * Wrap the resource in a standard API response format
     */
    public function withResponse($request, $response)
    {
        $response->setData([
            'success' => true,
            'data' => $response->getData(),
        ]);
    }
}
