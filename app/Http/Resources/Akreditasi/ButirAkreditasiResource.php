<?php

namespace App\Http\Resources\Akreditasi;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class ButirAkreditasiResource extends JsonResource
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
            'kode' => $this->kode,
            'nama' => $this->nama,
            'deskripsi' => $this->deskripsi,
            'instrumen' => $this->instrumen,
            'instrumen_label' => $this->getInstrumenLabel(),
            'kategori' => $this->kategori,
            'kategori_label' => $this->getKategoriLabel(),
            'bobot' => (float) $this->bobot,
            'parent_id' => $this->parent_id,
            'parent' => $this->whenLoaded('parent', function() {
                return [
                    'id' => $this->parent->id,
                    'kode' => $this->parent->kode,
                    'nama' => $this->parent->nama,
                    'instrumen' => $this->parent->instrumen,
                ];
            }),
            'children' => ButirAkreditasiResource::collection($this->whenLoaded('children')),
            'urutan' => (int) $this->urutan,
            'is_mandatory' => (bool) $this->is_mandatory,
            'is_mandatory_label' => $this->is_mandatory ? 'Wajib' : 'Opsional',
            'metadata' => $this->metadata,

            // Computed fields
            'has_children' => $this->getHasChildren(),
            'full_kode' => $this->full_kode ?? $this->kode,
            'level' => $this->getLevel(),
            'children_count' => $this->when(
                $this->relationLoaded('children'),
                fn() => $this->children->count()
            ),

            // Relationship data
            'pengisian_count' => $this->when(
                $this->relationLoaded('pengisianButirs'),
                fn() => $this->pengisianButirs->count()
            ),
            'dokumen_count' => $this->when(
                $this->relationLoaded('dokumenAkreditasis'),
                fn() => $this->dokumenAkreditasis->count()
            ),

            'created_at' => $this->created_at?->format('Y-m-d H:i:s'),
            'updated_at' => $this->updated_at?->format('Y-m-d H:i:s'),
            'deleted_at' => $this->deleted_at?->format('Y-m-d H:i:s'),
        ];
    }

    /**
     * Get instrumen label
     */
    private function getInstrumenLabel(): string
    {
        $labels = [
            'iapt_3.0' => 'IAPT 3.0',
            'iaps_4.0' => 'IAPS 4.0',
            'lam_ptkes_5.0' => 'LAM-PTKes 5.0',
        ];

        return $labels[$this->instrumen] ?? $this->instrumen;
    }

    /**
     * Get kategori label
     */
    private function getKategoriLabel(): string
    {
        $labels = [
            'visi_misi_tujuan' => 'Visi, Misi, Tujuan',
            'tata_pamong' => 'Tata Pamong',
            'mahasiswa' => 'Mahasiswa',
            'sumber_daya_manusia' => 'Sumber Daya Manusia',
            'keuangan' => 'Keuangan',
            'sarana_prasarana' => 'Sarana dan Prasarana',
            'pendidikan' => 'Pendidikan',
            'penelitian' => 'Penelitian',
            'pengabdian' => 'Pengabdian Masyarakat',
            'luaran_capaian' => 'Luaran dan Capaian Tridharma',
        ];

        return $labels[$this->kategori] ?? $this->kategori;
    }

    /**
     * Check if butir has children
     */
    private function getHasChildren(): bool
    {
        if ($this->relationLoaded('children')) {
            return $this->children->count() > 0;
        }
        return $this->has_children ?? false;
    }

    /**
     * Calculate level in hierarchy (depth)
     */
    private function getLevel(): int
    {
        $level = 0;
        $current = $this;

        // Walk up the parent chain to calculate depth
        while ($current->parent_id !== null) {
            $level++;
            if ($current->relationLoaded('parent') && $current->parent) {
                $current = $current->parent;
            } else {
                // If parent is not loaded, we can't calculate accurate level
                // Return 1 if has parent_id, 0 if root
                return $this->parent_id ? 1 : 0;
            }

            // Prevent infinite loop (safety check)
            if ($level > 10) break;
        }

        return $level;
    }
}
