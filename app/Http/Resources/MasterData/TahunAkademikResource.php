<?php

namespace App\Http\Resources\MasterData;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;
use Carbon\Carbon;

class TahunAkademikResource extends JsonResource
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
            'kode_tahun' => $this->kode_tahun,
            'nama_tahun' => $this->nama_tahun,
            'semester' => $this->semester,
            'semester_label' => $this->getSemesterLabel(),
            'tanggal_mulai' => $this->tanggal_mulai?->format('Y-m-d'),
            'tanggal_mulai_formatted' => $this->tanggal_mulai?->format('d F Y'),
            'tanggal_selesai' => $this->tanggal_selesai?->format('Y-m-d'),
            'tanggal_selesai_formatted' => $this->tanggal_selesai?->format('d F Y'),
            'periode' => $this->getPeriode(),
            'is_current' => $this->getIsCurrent(),
            'is_upcoming' => $this->getIsUpcoming(),
            'is_past' => $this->getIsPast(),
            'status_label' => $this->getStatusLabel(),
            'is_active' => $this->is_active,
            'created_at' => $this->created_at?->format('Y-m-d H:i:s'),
            'updated_at' => $this->updated_at?->format('Y-m-d H:i:s'),
        ];
    }

    /**
     * Get semester label
     */
    private function getSemesterLabel(): string
    {
        $labels = [
            'ganjil' => 'Semester Ganjil',
            'genap' => 'Semester Genap',
        ];

        return $labels[$this->semester] ?? $this->semester;
    }

    /**
     * Get formatted periode
     */
    private function getPeriode(): string
    {
        if (!$this->tanggal_mulai || !$this->tanggal_selesai) {
            return '-';
        }

        return $this->tanggal_mulai->format('d M Y') . ' - ' . $this->tanggal_selesai->format('d M Y');
    }

    /**
     * Check if current period
     */
    private function getIsCurrent(): bool
    {
        if (!$this->tanggal_mulai || !$this->tanggal_selesai) {
            return false;
        }

        $now = Carbon::now();
        return $now->between($this->tanggal_mulai, $this->tanggal_selesai);
    }

    /**
     * Check if upcoming period
     */
    private function getIsUpcoming(): bool
    {
        if (!$this->tanggal_mulai) {
            return false;
        }

        $now = Carbon::now();
        return $this->tanggal_mulai->isFuture();
    }

    /**
     * Check if past period
     */
    private function getIsPast(): bool
    {
        if (!$this->tanggal_selesai) {
            return false;
        }

        $now = Carbon::now();
        return $this->tanggal_selesai->isPast();
    }

    /**
     * Get status label
     */
    private function getStatusLabel(): string
    {
        if ($this->getIsCurrent()) {
            return 'Sedang Berlangsung';
        } elseif ($this->getIsUpcoming()) {
            return 'Akan Datang';
        } elseif ($this->getIsPast()) {
            return 'Selesai';
        }

        return '-';
    }
}
