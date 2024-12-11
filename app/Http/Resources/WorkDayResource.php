<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class WorkDayResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray($request): array
    {
        return [
            'id' => $this->id,
            'user_id' => $this->user_id,
            'start_time' => $this->start_time,
            'end_time' => $this->end_time,
            'total_work_time' => $this->total_work_time, // Accessor
            'total_pause_time' => $this->total_pause_time, // Accessor
            'pauses' => PauseResource::collection($this->pauses), // Если есть PauseResource
            'created_at' => $this->created_at,
            'updated_at' => $this->updated_at,
        ];
    }
}
