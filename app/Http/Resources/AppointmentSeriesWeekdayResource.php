<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

/**
 * API resource for transforming appointment series weekday slot data.
 * 
 * Transforms an AppointmentSeriesWeekday model into a JSON response format.
 * Includes weekday number, time slot information, and timestamps.
 */
class AppointmentSeriesWeekdayResource extends JsonResource
{
    /**
     * Transform the weekday slot resource into an array.
     * 
     * Formats time values as strings and timestamps as ISO 8601 strings
     * for consistent API responses across different timezones.
     *
     * @param Request $request The HTTP request instance
     * @return array<string, mixed> The weekday slot data as an array
     */
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'series_id' => $this->series_id,

            'weekday' => (int) $this->weekday,
            'start_time' => $this->start_time,
            'end_time' => $this->end_time,

            'created_at' => $this->created_at?->toISOString(),
            'updated_at' => $this->updated_at?->toISOString(),
        ];
    }
}
