<?php

namespace App\Http\Resources\Akreditasi;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class InstrumenAkreditasiResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     */
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'kode' => $this->kode,
            'nama' => $this->nama,
            'deskripsi' => $this->deskripsi,
            'jenis' => $this->jenis,
            'jenis_label' => $this->getJenisLabel(),
            'lembaga' => $this->lembaga,
            'tahun_berlaku' => $this->tahun_berlaku,
            'is_active' => $this->is_active,
            'created_at' => $this->created_at?->format('Y-m-d H:i:s'),
            'updated_at' => $this->updated_at?->format('Y-m-d H:i:s'),
        ];
    }

    /**
     * Get jenis label
     */
    private function getJenisLabel(): string
    {
        $labels = [
            'program_studi' => 'Program Studi',
            'institusi' => 'Institusi',
            'both' => 'Program Studi & Institusi',
        ];

        return $labels[$this->jenis] ?? $this->jenis;
    }
}
