<?php

namespace App\Http\Resources\Akreditasi;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;
use Illuminate\Support\Facades\Storage;

class PengisianButirResource extends JsonResource
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
            'butir_akreditasi_id' => $this->butir_akreditasi_id,
            'butir_akreditasi' => new ButirAkreditasiResource($this->whenLoaded('butirAkreditasi')),
            'pic_user_id' => $this->pic_user_id,
            'pic_user' => $this->whenLoaded('picUser', function() {
                return [
                    'id' => $this->picUser->id,
                    'name' => $this->picUser->name,
                    'email' => $this->picUser->email,
                    'nip' => $this->picUser->nip ?? null,
                ];
            }),
            'konten' => $this->konten,
            'konten_plain' => $this->konten_plain,
            'form_data' => $this->form_data,
            'files' => $this->files,
            'files_info' => $this->getFilesInfo(),
            'has_files' => !empty($this->files),
            'files_count' => is_array($this->files) ? count($this->files) : 0,
            'status' => $this->status,
            'status_label' => $this->getStatusLabel(),
            'status_color' => $this->status_color ?? $this->getStatusColor(),
            'version' => (int) $this->version,
            'notes' => $this->notes,
            'reviewed_by' => $this->reviewed_by,
            'reviewer' => $this->whenLoaded('reviewer', function() {
                return [
                    'id' => $this->reviewer->id,
                    'name' => $this->reviewer->name,
                    'email' => $this->reviewer->email,
                    'nip' => $this->reviewer->nip ?? null,
                ];
            }),
            'reviewed_at' => $this->reviewed_at?->format('Y-m-d H:i:s'),
            'reviewed_at_formatted' => $this->reviewed_at?->format('d F Y H:i'),
            'review_notes' => $this->review_notes,
            'completion_percentage' => $this->completion_percentage ? round((float) $this->completion_percentage, 2) : 0,
            'completion_label' => $this->getCompletionLabel(),
            'is_complete' => (bool) $this->is_complete,
            'is_complete_label' => $this->is_complete ? 'Selesai' : 'Belum Selesai',

            // Computed fields
            'word_count' => $this->word_count ?? $this->getWordCount(),
            'char_count' => $this->char_count ?? $this->getCharCount(),
            'days_until_deadline' => $this->getDaysUntilDeadline(),
            'is_overdue' => $this->getIsOverdue(),

            'created_at' => $this->created_at?->format('Y-m-d H:i:s'),
            'updated_at' => $this->updated_at?->format('Y-m-d H:i:s'),
            'deleted_at' => $this->deleted_at?->format('Y-m-d H:i:s'),
        ];
    }

    /**
     * Get status label
     */
    private function getStatusLabel(): string
    {
        $labels = [
            'draft' => 'Draft',
            'submitted' => 'Diajukan',
            'review' => 'Dalam Review',
            'revision' => 'Perlu Revisi',
            'approved' => 'Disetujui',
        ];

        return $labels[$this->status] ?? $this->status;
    }

    /**
     * Get status color for UI
     */
    private function getStatusColor(): string
    {
        $colors = [
            'draft' => 'gray',
            'submitted' => 'blue',
            'review' => 'yellow',
            'revision' => 'orange',
            'approved' => 'green',
        ];

        return $colors[$this->status] ?? 'gray';
    }

    /**
     * Get completion label based on percentage
     */
    private function getCompletionLabel(): string
    {
        $percentage = (float) $this->completion_percentage;

        if ($percentage >= 100) {
            return 'Selesai';
        } elseif ($percentage >= 75) {
            return 'Hampir Selesai';
        } elseif ($percentage >= 50) {
            return 'Sedang Berlangsung';
        } elseif ($percentage >= 25) {
            return 'Baru Dimulai';
        } else {
            return 'Belum Dimulai';
        }
    }

    /**
     * Get files information with URLs
     */
    private function getFilesInfo(): ?array
    {
        if (empty($this->files) || !is_array($this->files)) {
            return null;
        }

        return array_map(function($file) {
            if (is_string($file)) {
                return [
                    'path' => $file,
                    'name' => basename($file),
                    'url' => Storage::url($file),
                    'exists' => Storage::exists($file),
                ];
            }

            // If file is already an array with metadata
            return [
                'path' => $file['path'] ?? $file['file_path'] ?? null,
                'name' => $file['name'] ?? $file['file_name'] ?? basename($file['path'] ?? ''),
                'url' => isset($file['path']) ? Storage::url($file['path']) : null,
                'size' => $file['size'] ?? null,
                'type' => $file['type'] ?? null,
                'exists' => isset($file['path']) ? Storage::exists($file['path']) : false,
            ];
        }, $this->files);
    }

    /**
     * Get word count
     */
    private function getWordCount(): int
    {
        return str_word_count(strip_tags($this->konten ?? ''));
    }

    /**
     * Get character count
     */
    private function getCharCount(): int
    {
        return strlen(strip_tags($this->konten ?? ''));
    }

    /**
     * Get days until deadline
     */
    private function getDaysUntilDeadline(): ?int
    {
        if (!$this->relationLoaded('periodeAkreditasi') || !$this->periodeAkreditasi) {
            return null;
        }

        $deadline = $this->periodeAkreditasi->deadline_pengumpulan;
        if (!$deadline) {
            return null;
        }

        return (int) now()->diffInDays($deadline, false);
    }

    /**
     * Check if overdue
     */
    private function getIsOverdue(): bool
    {
        if (!$this->relationLoaded('periodeAkreditasi') || !$this->periodeAkreditasi) {
            return false;
        }

        $deadline = $this->periodeAkreditasi->deadline_pengumpulan;
        if (!$deadline) {
            return false;
        }

        return $deadline->isPast() && !$this->is_complete;
    }
}
