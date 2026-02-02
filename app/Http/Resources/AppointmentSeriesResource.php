<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

/**
 * API resource for transforming appointment series data.
 * 
 * Transforms an AppointmentSeries model into a JSON response format.
 * Includes series configuration, date ranges, timezone information, and optionally weekday slots.
 */
class AppointmentSeriesResource extends JsonResource
{
    /**
     * Transform the appointment series resource into an array.
     * 
     * Formats dates as date strings and timestamps as ISO 8601 strings.
     * Conditionally includes weekday slot data if it has been eager loaded.
     *
     * @param Request $request The HTTP request instance
     * @return array<string, mixed> The series data as an array
     */
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'customer_id' => $this->customer_id,

            'title' => $this->title,
            'notes' => $this->notes,

            'timezone' => $this->timezone,
            'start_on' => $this->start_on?->toDateString(),
            'end_on' => $this->end_on?->toDateString(),

            'is_active' => (bool) $this->is_active,

            'weekdays' => AppointmentSeriesWeekdayResource::collection(
                $this->whenLoaded('weekdays')
            ),

            'deleted_at' => $this->deleted_at?->toISOString(),
            'created_at' => $this->created_at?->toISOString(),
            'updated_at' => $this->updated_at?->toISOString(),
        ];
    }
}
