<?php

namespace App\Http\Resources\MasterData;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class UnitKerjaResource extends JsonResource
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
            'kode_unit' => $this->kode_unit,
            'nama_unit' => $this->nama_unit,
            'nama_singkat' => $this->nama_singkat,
            'email' => $this->email,
            'telepon' => $this->telepon,
            'alamat' => $this->alamat,
            'website' => $this->website,
            'deskripsi' => $this->deskripsi,
            'jenis_unit' => $this->jenis_unit,
            'jenis_unit_label' => $this->getJenisUnitLabel(),
            'parent_id' => $this->parent_id,
            'parent' => $this->whenLoaded('parent', function() {
                return [
                    'id' => $this->parent->id,
                    'kode_unit' => $this->parent->kode_unit,
                    'nama_unit' => $this->parent->nama_unit,
                    'jenis_unit' => $this->parent->jenis_unit,
                ];
            }),
            'children' => UnitKerjaResource::collection($this->whenLoaded('children')),
            'program_studis' => $this->whenLoaded('programStudis', function() {
                return $this->programStudis->map(function($prodi) {
                    return [
                        'id' => $prodi->id,
                        'kode_prodi' => $prodi->kode_prodi,
                        'nama_prodi' => $prodi->nama_prodi,
                        'jenjang' => $prodi->jenjang,
                    ];
                });
            }),
            'children_count' => $this->when(isset($this->children), function() {
                return $this->children->count();
            }),
            'is_active' => $this->is_active,
            'created_at' => $this->created_at?->format('Y-m-d H:i:s'),
            'updated_at' => $this->updated_at?->format('Y-m-d H:i:s'),
        ];
    }

    /**
     * Get jenis unit label in Indonesian
     */
    private function getJenisUnitLabel(): string
    {
        $labels = [
            'fakultas' => 'Fakultas',
            'program_studi' => 'Program Studi',
            'lembaga' => 'Lembaga',
            'unit_pendukung' => 'Unit Pendukung',
        ];

        return $labels[$this->jenis_unit] ?? $this->jenis_unit;
    }
}
