<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class IKUResource extends JsonResource
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
            'kode_iku' => $this->kode_iku,
            'nama_iku' => $this->nama_iku,
            'deskripsi' => $this->deskripsi,
            'satuan' => $this->satuan,
            'satuan_label' => $this->getSatuanLabel(),
            'target_type' => $this->target_type,
            'target_type_label' => $this->getTargetTypeLabel(),
            'kategori' => $this->kategori,
            'bobot' => $this->bobot,
            'is_active' => $this->is_active,
            'is_active_label' => $this->is_active ? 'Aktif' : 'Tidak Aktif',
            'targets' => IKUTargetResource::collection($this->whenLoaded('targets')),
            'targets_count' => $this->when(isset($this->targets), function() {
                return $this->targets->count();
            }),
            'created_at' => $this->created_at?->format('Y-m-d H:i:s'),
            'updated_at' => $this->updated_at?->format('Y-m-d H:i:s'),
        ];
    }

    /**
     * Get satuan label in Indonesian
     */
    private function getSatuanLabel(): string
    {
        $labels = [
            'persen' => 'Persen (%)',
            'jumlah' => 'Jumlah',
            'skor' => 'Skor',
            'nilai' => 'Nilai',
        ];

        return $labels[$this->satuan] ?? $this->satuan;
    }

    /**
     * Get target type label in Indonesian
     */
    private function getTargetTypeLabel(): string
    {
        $labels = [
            'increase' => 'Meningkat',
            'decrease' => 'Menurun',
        ];

        return $labels[$this->target_type] ?? $this->target_type;
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
