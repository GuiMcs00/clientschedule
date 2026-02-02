<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

/**
 * Form request for updating an existing appointment series.
 * 
 * Validates recurring appointment series data for updates with all fields optional.
 * Uses 'sometimes' to allow partial updates (only validate fields that are present).
 * Supports updating title, date range, timezone, active status, and weekday slots.
 */
class UpdateAppointmentSeriesRequest extends FormRequest
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
     * Get the validation rules for updating an appointment series.
     * 
     * All fields are optional for partial updates.
     * Validates series info, date range, and weekday slots when provided.
     *
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array<mixed>|string> The validation rules
     */
    public function rules(): array
    {
        return [
            'title' => ['sometimes', 'required', 'string', 'max:150'],
            'notes' => ['sometimes', 'nullable', 'string'],
            'timezone' => ['sometimes', 'required', 'string', 'max:64', 'timezone'],
            'starts_on' => ['sometimes', 'required', 'date'],
            'is_active' => ['sometimes', 'required', 'boolean'],

            'weekdays' => ['sometimes', 'required', 'array', 'min:1'],
            'weekdays.*.weekday' => ['required_with:weekdays', 'integer', 'between:0,6'],
            'weekdays.*.start_time' => ['required_with:weekdays', 'date_format:H:i'],
            'weekdays.*.end_time' => ['required_with:weekdays', 'date_format:H:i'],
        ];
    }

    /**
     * Configure the validator instance with additional validation logic.
     * 
     * Adds custom validation rules to ensure:
     * - end_time is greater than start_time for each weekday slot
     * - No duplicate weekday slots in the request payload
     *
     * @param \Illuminate\Validation\Validator $validator The validator instance
     * @return void
     */
    public function withValidator($validator): void
    {
        $validator->after(function ($validator) {
            $weekdays = $this->input('weekdays');

            if (!is_array($weekdays)) {
                return;
            }

            $seen = [];

            foreach ($weekdays as $i => $slot) {
                $start = $slot['start_time'] ?? null;
                $end = $slot['end_time'] ?? null;
                $weekday = $slot['weekday'] ?? null;

                if ($start && $end && $start >= $end) {
                    $validator->errors()->add("weekdays.$i.end_time", 'end_time deve ser maior que start_time.');
                }

                if ($weekday !== null && $start && $end) {
                    $key = $weekday . '|' . $start . '|' . $end;
                    if (isset($seen[$key])) {
                        $validator->errors()->add("weekdays.$i.weekday", 'Slot duplicado no payload.');
                    }
                    $seen[$key] = true;
                }
            }
        });
    }

}
