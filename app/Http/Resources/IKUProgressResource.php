<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;
use Illuminate\Support\Facades\Storage;

class IKUProgressResource extends JsonResource
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
            'iku_target_id' => $this->iku_target_id,
            'target' => new IKUTargetResource($this->whenLoaded('target')),
            'tanggal_capaian' => $this->tanggal_capaian?->format('Y-m-d'),
            'tanggal_capaian_formatted' => $this->tanggal_capaian?->format('d F Y'),
            'nilai_capaian' => (float) $this->nilai_capaian,
            'persentase_capaian' => $this->persentase_capaian ? round((float) $this->persentase_capaian, 2) : null,
            'keterangan' => $this->keterangan,
            'bukti_dokumen' => $this->bukti_dokumen,
            'bukti_dokumen_url' => $this->bukti_dokumen ? Storage::disk('public')->url($this->bukti_dokumen) : null,
            'bukti_dokumen_name' => $this->bukti_dokumen ? basename($this->bukti_dokumen) : null,
            'has_document' => !is_null($this->bukti_dokumen),
            'created_by' => $this->created_by,
            'creator' => $this->whenLoaded('creator', function() {
                return [
                    'id' => $this->creator->id,
                    'name' => $this->creator->name,
                    'email' => $this->creator->email,
                ];
            }),
            'created_at' => $this->created_at?->format('Y-m-d H:i:s'),
            'updated_at' => $this->updated_at?->format('Y-m-d H:i:s'),
        ];
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
