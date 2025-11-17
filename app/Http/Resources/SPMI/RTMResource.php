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
            'title' => $this->title,
            'description' => $this->description,
            'meeting_date' => $this->meeting_date?->format('Y-m-d'),
            'start_time' => $this->start_time,
            'end_time' => $this->end_time,
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
            'chairman' => $this->whenLoaded('chairman', function () {
                return [
                    'id' => $this->chairman->id,
                    'name' => $this->chairman->name,
                    'email' => $this->chairman->email,
                ];
            }),
            'secretary' => $this->whenLoaded('secretary', function () {
                return [
                    'id' => $this->secretary->id,
                    'name' => $this->secretary->name,
                    'email' => $this->secretary->email,
                ];
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
            'draft' => 'Draft',
            'completed' => 'Selesai',
            'published' => 'Dipublikasikan',
            default => $this->status,
        };
    }
}
