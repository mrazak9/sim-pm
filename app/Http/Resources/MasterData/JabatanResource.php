<?php

namespace App\Http\Resources\MasterData;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class JabatanResource extends JsonResource
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
            'kode_jabatan' => $this->kode_jabatan,
            'nama_jabatan' => $this->nama_jabatan,
            'deskripsi' => $this->deskripsi,
            'kategori' => $this->kategori,
            'kategori_label' => $this->getKategoriLabel(),
            'level' => $this->level,
            'level_label' => $this->getLevelLabel(),
            'is_active' => $this->is_active,
            'created_at' => $this->created_at?->format('Y-m-d H:i:s'),
            'updated_at' => $this->updated_at?->format('Y-m-d H:i:s'),
        ];
    }

    /**
     * Get kategori label in Indonesian
     */
    private function getKategoriLabel(): string
    {
        $labels = [
            'struktural' => 'Struktural',
            'fungsional' => 'Fungsional',
            'dosen' => 'Dosen',
            'tendik' => 'Tenaga Kependidikan',
        ];

        return $labels[$this->kategori] ?? $this->kategori;
    }

    /**
     * Get level label
     */
    private function getLevelLabel(): ?string
    {
        if (!$this->level) {
            return null;
        }

        return 'Level ' . $this->level;
    }
}
