<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

/**
 * Form request for creating a new appointment.
 * 
 * Validates appointment data including title, timing, and notes.
 * Ensures start and end times are valid and end time is after start time.
 */
class StoreAppointmentRequest extends FormRequest
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
     * Get the validation rules for creating an appointment.
     * 
     * Validates:
     * - Title (required, max 150 chars)
     * - Notes (optional text)
     * - Timing (starts_at and ends_at as dates)
     *
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array<mixed>|string> The validation rules
     */
    public function rules(): array
    {
        return [
            'title' => ['required', 'string', 'max:150'],
            'notes' => ['nullable', 'string'],
            'starts_at' => ['required', 'date'],
            'ends_at' => ['required', 'date', 'after:starts_at'],
        ];
    }
}
