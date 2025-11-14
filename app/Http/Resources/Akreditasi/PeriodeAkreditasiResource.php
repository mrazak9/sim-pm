<?php

namespace App\Http\Resources\Akreditasi;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class PeriodeAkreditasiResource extends JsonResource
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
            'nama' => $this->nama,
            'jenis_akreditasi' => $this->jenis_akreditasi,
            'jenis_akreditasi_label' => $this->getJenisAkreditasiLabel(),
            'lembaga' => $this->lembaga,
            'lembaga_label' => $this->getLembagaLabel(),
            'instrumen' => $this->instrumen,
            'instrumen_label' => $this->getInstrumenLabel(),
            'jenjang' => $this->jenjang,
            'jenjang_label' => $this->getJenjangLabel(),
            'unit_kerja_id' => $this->unit_kerja_id,
            'unit_kerja' => $this->whenLoaded('unitKerja', function() {
                return [
                    'id' => $this->unitKerja->id,
                    'kode_unit' => $this->unitKerja->kode_unit,
                    'nama_unit' => $this->unitKerja->nama_unit,
                    'jenis_unit' => $this->unitKerja->jenis_unit,
                ];
            }),
            'program_studi_id' => $this->program_studi_id,
            'program_studi' => $this->whenLoaded('programStudi', function() {
                return [
                    'id' => $this->programStudi->id,
                    'kode_prodi' => $this->programStudi->kode_prodi,
                    'nama_prodi' => $this->programStudi->nama_prodi,
                    'jenjang' => $this->programStudi->jenjang,
                ];
            }),
            'tanggal_mulai' => $this->tanggal_mulai?->format('Y-m-d'),
            'tanggal_mulai_formatted' => $this->tanggal_mulai?->format('d F Y'),
            'deadline_pengumpulan' => $this->deadline_pengumpulan?->format('Y-m-d'),
            'deadline_pengumpulan_formatted' => $this->deadline_pengumpulan?->format('d F Y'),
            'jadwal_visitasi' => $this->jadwal_visitasi?->format('Y-m-d'),
            'jadwal_visitasi_formatted' => $this->jadwal_visitasi?->format('d F Y'),
            'tanggal_berakhir' => $this->tanggal_berakhir?->format('Y-m-d'),
            'tanggal_berakhir_formatted' => $this->tanggal_berakhir?->format('d F Y'),
            'status' => $this->status,
            'status_label' => $this->getStatusLabel(),
            'status_color' => $this->getStatusColor(),
            'keterangan' => $this->keterangan,
            'metadata' => $this->metadata,

            // Computed fields
            'sisa_hari' => $this->getSisaHari(),
            'is_expired' => $this->getIsExpired(),
            'is_active' => $this->getIsActive(),
            'progress_persentase' => $this->when(
                $this->relationLoaded('pengisianButirs'),
                fn() => $this->progress_persentase ?? 0
            ),

            // Relationship counts
            'butir_count' => $this->when(
                $this->relationLoaded('pengisianButirs'),
                fn() => $this->pengisianButirs->count()
            ),
            'dokumen_count' => $this->when(
                $this->relationLoaded('dokumenAkreditasis'),
                fn() => $this->dokumenAkreditasis->count()
            ),
            'pengisian_count' => $this->when(
                $this->relationLoaded('pengisianButirs'),
                fn() => $this->pengisianButirs->where('is_complete', true)->count()
            ),

            'created_at' => $this->created_at?->format('Y-m-d H:i:s'),
            'updated_at' => $this->updated_at?->format('Y-m-d H:i:s'),
            'deleted_at' => $this->deleted_at?->format('Y-m-d H:i:s'),
        ];
    }

    /**
     * Get jenis akreditasi label
     */
    private function getJenisAkreditasiLabel(): string
    {
        $labels = [
            'institusi' => 'Institusi',
            'program_studi' => 'Program Studi',
        ];

        return $labels[$this->jenis_akreditasi] ?? $this->jenis_akreditasi;
    }

    /**
     * Get lembaga label
     */
    private function getLembagaLabel(): string
    {
        $labels = [
            'ban_pt' => 'BAN-PT',
            'lam_ptkes' => 'LAM-PTKes',
            'lam_teknik' => 'LAM-Teknik',
            'lam_infokom' => 'LAM-Infokom',
            'lam_ptkesos' => 'LAM-PTKesos',
            'lam_emba' => 'LAM-EMBA',
        ];

        return $labels[$this->lembaga] ?? $this->lembaga;
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
     * Get jenjang label
     */
    private function getJenjangLabel(): ?string
    {
        if (!$this->jenjang) return null;

        $labels = [
            's1' => 'S1',
            's2' => 'S2',
            's3' => 'S3',
            'd3' => 'D3',
            'd4' => 'D4',
            'profesi' => 'Profesi',
            'spesialis' => 'Spesialis',
        ];

        return $labels[$this->jenjang] ?? $this->jenjang;
    }

    /**
     * Get status label
     */
    private function getStatusLabel(): string
    {
        $labels = [
            'persiapan' => 'Persiapan',
            'pengisian' => 'Pengisian',
            'review' => 'Review',
            'visitasi' => 'Visitasi',
            'selesai' => 'Selesai',
            'dibatalkan' => 'Dibatalkan',
        ];

        return $labels[$this->status] ?? $this->status;
    }

    /**
     * Get status color for UI
     */
    private function getStatusColor(): string
    {
        $colors = [
            'persiapan' => 'gray',
            'pengisian' => 'blue',
            'review' => 'yellow',
            'visitasi' => 'purple',
            'selesai' => 'green',
            'dibatalkan' => 'red',
        ];

        return $colors[$this->status] ?? 'gray';
    }

    /**
     * Get sisa hari until deadline
     */
    private function getSisaHari(): ?int
    {
        if (!$this->deadline_pengumpulan) return null;
        return (int) now()->diffInDays($this->deadline_pengumpulan, false);
    }

    /**
     * Check if periode is expired
     */
    private function getIsExpired(): bool
    {
        if (!$this->deadline_pengumpulan) return false;
        return $this->deadline_pengumpulan->isPast();
    }

    /**
     * Check if periode is active
     */
    private function getIsActive(): bool
    {
        return in_array($this->status, ['persiapan', 'pengisian', 'review', 'visitasi']);
    }
}
