<?php

namespace App\Http\Resources\MasterData;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class ProgramStudiResource extends JsonResource
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
            'kode_prodi' => $this->kode_prodi,
            'nama_prodi' => $this->nama_prodi,
            'unit_kerja_id' => $this->unit_kerja_id,
            'unit_kerja' => $this->whenLoaded('unitKerja', function() {
                return [
                    'id' => $this->unitKerja->id,
                    'kode_unit' => $this->unitKerja->kode_unit,
                    'nama_unit' => $this->unitKerja->nama_unit,
                    'jenis_unit' => $this->unitKerja->jenis_unit,
                ];
            }),
            'jenjang' => $this->jenjang,
            'jenjang_label' => $this->getJenjangLabel(),
            'akreditasi' => $this->akreditasi,
            'tanggal_akreditasi' => $this->tanggal_akreditasi?->format('Y-m-d'),
            'tanggal_akreditasi_formatted' => $this->tanggal_akreditasi?->format('d F Y'),
            'kuota_mahasiswa' => $this->kuota_mahasiswa,
            'is_active' => $this->is_active,
            'created_at' => $this->created_at?->format('Y-m-d H:i:s'),
            'updated_at' => $this->updated_at?->format('Y-m-d H:i:s'),
        ];
    }

    /**
     * Get jenjang label
     */
    private function getJenjangLabel(): string
    {
        $labels = [
            'D3' => 'Diploma 3',
            'D4' => 'Diploma 4',
            'S1' => 'Sarjana (S1)',
            'S2' => 'Magister (S2)',
            'S3' => 'Doktor (S3)',
        ];

        return $labels[$this->jenjang] ?? $this->jenjang;
    }
}
