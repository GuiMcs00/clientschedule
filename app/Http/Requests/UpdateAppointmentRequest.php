<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

/**
 * Form request for updating an existing appointment.
 * 
 * Validates appointment data for updates with all fields optional.
 * Uses 'sometimes' to allow partial updates (only validate fields that are present).
 */
class UpdateAppointmentRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     *
     * @return bool Authorization status
     */
    public function authorize(): bool
    {
        return true;
    }

    /**
     * Get the validation rules for updating an appointment.
     * 
     * All fields are optional for partial updates.
     * Validates title, notes, and timing when provided.
     *
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array<mixed>|string> The validation rules
     */
    public function rules(): array
    {
        return [
            'title' => ['sometimes', 'required', 'string', 'max:150'],
            'notes' => ['sometimes', 'nullable', 'string'],
            'starts_at' => ['sometimes', 'required', 'date'],
            'ends_at' => ['sometimes', 'required', 'date'],
        ];
    }
}
