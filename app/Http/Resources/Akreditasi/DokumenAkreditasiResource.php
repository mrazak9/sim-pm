<?php

namespace App\Http\Resources\Akreditasi;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;
use Illuminate\Support\Facades\Storage;

class DokumenAkreditasiResource extends JsonResource
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
            'periode_akreditasi_id' => $this->periode_akreditasi_id,
            'periode_akreditasi' => new PeriodeAkreditasiResource($this->whenLoaded('periodeAkreditasi')),
            'nama_dokumen' => $this->nama_dokumen,
            'nomor_dokumen' => $this->nomor_dokumen,
            'jenis_dokumen' => $this->jenis_dokumen,
            'jenis_dokumen_label' => $this->getJenisDokumenLabel(),
            'deskripsi' => $this->deskripsi,
            'file_path' => $this->file_path,
            'file_type' => $this->file_type,
            'file_size' => (int) $this->file_size,
            'file_size_formatted' => $this->file_size_formatted ?? $this->getFileSizeFormatted(),
            'file_extension' => $this->getFileExtension(),
            'file_url' => $this->file_url ?? $this->getFileUrl(),
            'download_url' => $this->getDownloadUrl(),
            'file_exists' => $this->file_exists ?? $this->getFileExists(),
            'uploaded_by' => $this->uploaded_by,
            'uploader' => $this->whenLoaded('uploader', function() {
                return [
                    'id' => $this->uploader->id,
                    'name' => $this->uploader->name,
                    'email' => $this->uploader->email,
                    'nip' => $this->uploader->nip ?? null,
                ];
            }),
            'metadata' => $this->metadata,

            // Related butirs
            'butirs' => ButirAkreditasiResource::collection($this->whenLoaded('butirAkreditasis')),
            'butirs_count' => $this->when(
                $this->relationLoaded('butirAkreditasis'),
                fn() => $this->butirAkreditasis->count()
            ),

            'created_at' => $this->created_at?->format('Y-m-d H:i:s'),
            'created_at_formatted' => $this->created_at?->format('d F Y H:i'),
            'updated_at' => $this->updated_at?->format('Y-m-d H:i:s'),
            'updated_at_formatted' => $this->updated_at?->format('d F Y H:i'),
            'deleted_at' => $this->deleted_at?->format('Y-m-d H:i:s'),
        ];
    }

    /**
     * Get jenis dokumen label
     */
    private function getJenisDokumenLabel(): string
    {
        $labels = [
            'surat_tugas' => 'Surat Tugas',
            'sk_rektor' => 'SK Rektor',
            'sk_dekan' => 'SK Dekan',
            'dokumen_pendukung' => 'Dokumen Pendukung',
            'laporan' => 'Laporan',
            'borang' => 'Borang',
            'led' => 'Laporan Evaluasi Diri (LED)',
            'dokumen_mutu' => 'Dokumen Mutu',
            'sop' => 'Standard Operating Procedure (SOP)',
            'panduan' => 'Panduan',
            'bukti_fisik' => 'Bukti Fisik',
            'lainnya' => 'Lainnya',
        ];

        return $labels[$this->jenis_dokumen] ?? $this->jenis_dokumen;
    }

    /**
     * Get file size formatted in human readable format
     */
    private function getFileSizeFormatted(): string
    {
        $bytes = $this->file_size;

        if ($bytes >= 1073741824) {
            return number_format($bytes / 1073741824, 2) . ' GB';
        } elseif ($bytes >= 1048576) {
            return number_format($bytes / 1048576, 2) . ' MB';
        } elseif ($bytes >= 1024) {
            return number_format($bytes / 1024, 2) . ' KB';
        } elseif ($bytes > 1) {
            return $bytes . ' bytes';
        } elseif ($bytes == 1) {
            return $bytes . ' byte';
        } else {
            return '0 bytes';
        }
    }

    /**
     * Get file extension
     */
    private function getFileExtension(): ?string
    {
        if (!$this->file_path) {
            return null;
        }

        return strtolower(pathinfo($this->file_path, PATHINFO_EXTENSION));
    }

    /**
     * Get file URL for display
     */
    private function getFileUrl(): ?string
    {
        if (!$this->file_path) {
            return null;
        }

        return Storage::url($this->file_path);
    }

    /**
     * Get download URL
     */
    private function getDownloadUrl(): ?string
    {
        if (!$this->id) {
            return null;
        }

        // You can customize this URL based on your routing structure
        return route('akreditasi.dokumen.download', ['dokumen' => $this->id]);
    }

    /**
     * Check if file exists in storage
     */
    private function getFileExists(): bool
    {
        if (!$this->file_path) {
            return false;
        }

        return Storage::exists($this->file_path);
    }
}
