<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

/**
 * API resource for transforming customer data.
 * 
 * Transforms a Customer model into a JSON response format.
 * Includes customer attributes and timestamps.
 */
class CustomerResource extends JsonResource
{
    /**
     * Transform the customer resource into an array.
     *
     * @param Request $request The HTTP request instance
     * @return array<string, mixed> The customer data as an array
     */
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'name' => $this->name,
            'email' => $this->email,
            'phone' => $this->phone,
            'deleted_at' => $this->deleted_at,
            'created_at' => $this->created_at,
            'updated_at' => $this->updated_at,
        ];
    }
}
