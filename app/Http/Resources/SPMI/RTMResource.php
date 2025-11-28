<?php

namespace App\Http\Resources\SPMI;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class RTMResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        $duration = null;
        if ($this->start_time && $this->end_time) {
            $start = \DateTime::createFromFormat('H:i', $this->start_time);
            $end = \DateTime::createFromFormat('H:i', $this->end_time);
            if ($start && $end) {
                $duration = $start->diff($end)->format('%H:%I');
            }
        }

        return [
            'id' => $this->id,
            'rtm_code' => $this->rtm_code,
            'code' => $this->rtm_code, // Alias for frontend compatibility
            'title' => $this->title,
            'description' => $this->description,
            'tahun_akademik_id' => $this->tahun_akademik_id,
            'meeting_date' => $this->meeting_date?->format('Y-m-d'),
            'start_time' => $this->start_time ? substr($this->start_time, 0, 5) : null, // Format to HH:MM
            'end_time' => $this->end_time ? substr($this->end_time, 0, 5) : null, // Format to HH:MM
            'duration' => $duration,
            'location' => $this->location,
            'agenda' => $this->agenda,
            'discussion_points' => $this->discussion_points,
            'decisions' => $this->decisions,
            'minutes' => $this->minutes,
            'follow_up_plan' => $this->follow_up_plan,
            'status' => $this->status,
            'status_label' => $this->getStatusLabel(),
            'tahun_akademik' => $this->whenLoaded('tahunAkademik', function () {
                return [
                    'id' => $this->tahunAkademik->id,
                    'tahun' => $this->tahunAkademik->tahun,
                    'semester' => $this->tahunAkademik->semester,
                ];
            }),
            'chairman_id' => $this->chairman_id,
            'chairman' => $this->whenLoaded('chairman', function () {
                return [
                    'id' => $this->chairman->id,
                    'name' => $this->chairman->name,
                    'email' => $this->chairman->email,
                ];
            }),
            'secretary_id' => $this->secretary_id,
            'secretary' => $this->whenLoaded('secretary', function () {
                return [
                    'id' => $this->secretary->id,
                    'name' => $this->secretary->name,
                    'email' => $this->secretary->email,
                ];
            }),
            'participants' => $this->whenLoaded('participants', function () {
                return $this->participants->map(function ($participant) {
                    return [
                        'user_id' => $participant->id,
                        'user_name' => $participant->name,
                        'role' => $participant->pivot->role ?? 'Peserta',
                        'attended' => $participant->pivot->is_present ?? false,
                    ];
                });
            }),
            'participants_count' => $this->whenCounted('participants'),
            'action_items_count' => $this->whenCounted('actionItems'),
            'minutes_file' => $this->minutes_file,
            'attendance_file' => $this->attendance_file,
            'is_completed' => (bool) $this->is_completed,
            'created_at' => $this->created_at?->format('Y-m-d H:i:s'),
            'updated_at' => $this->updated_at?->format('Y-m-d H:i:s'),
        ];
    }

    /**
     * Get status label.
     */
    private function getStatusLabel(): string
    {
        return match($this->status) {
            'planned' => 'Direncanakan',
            'ongoing' => 'Berlangsung',
            'completed' => 'Selesai',
            'cancelled' => 'Dibatalkan',
            'draft' => 'Draft',
            'published' => 'Dipublikasikan',
            default => $this->status,
        };
    }
}
