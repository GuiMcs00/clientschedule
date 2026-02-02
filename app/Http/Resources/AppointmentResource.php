<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

/**
 * API resource for transforming appointment data.
 * 
 * Transforms an Appointment model into a JSON response format.
 * Includes appointment details, timing information, and optionally the parent series data.
 */
class AppointmentResource extends JsonResource
{
    /**
     * Transform the appointment resource into an array.
     * 
     * Formats timestamps as ISO 8601 strings for consistent API responses.
     * Conditionally includes series data if it has been eager loaded.
     *
     * @param Request $request The HTTP request instance
     * @return array<string, mixed> The appointment data as an array
     */
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'customer_id' => $this->customer_id,

            'title' => $this->title,
            'notes' => $this->notes,

            'starts_at' => $this->starts_at?->toISOString(),
            'ends_at' => $this->ends_at?->toISOString(),

            'series_id' => $this->series_id,

            // Include series data if loaded
            'series' => new AppointmentSeriesResource($this->whenLoaded('series')),

            'deleted_at' => $this->deleted_at?->toISOString(),
            'created_at' => $this->created_at?->toISOString(),
            'updated_at' => $this->updated_at?->toISOString(),
        ];
    }
}
