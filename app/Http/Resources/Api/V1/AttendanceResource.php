<?php

namespace App\Http\Resources\Api\V1;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class AttendanceResource extends JsonResource
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
            'attendance_date' => $this->attendance_date->toDateString(),
            'work_location' => $this->work_location,
            'is_late' => $this->is_late,
            'check_in_at' => $this->check_in_at?->toISOString(),
            'check_out_at' => $this->check_out_at?->toISOString(),
            'lunch_started_at' => $this->lunch_started_at?->toISOString(),
            'lunch_ended_at' => $this->lunch_ended_at?->toISOString(),
        ];
    }
}
