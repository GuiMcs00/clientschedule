<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

/**
 * Form request for creating a new appointment series.
 * 
 * Validates recurring appointment series data including title, date range,
 * timezone, and weekday time slots. Ensures weekday slots have valid times
 * and end time is after start time.
 */
class StoreAppointmentSeriesRequest extends FormRequest
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
     * Get the validation rules for creating an appointment series.
     * 
     * Validates:
     * - Series info (title, notes, timezone)
     * - Date range (starts_on, optional ends_on)
     * - Weekday slots (array of weekday/time combinations)
     * - Each weekday must be 0-6, times in H:i format
     *
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array<mixed>|string> The validation rules
     */
    public function rules(): array
    {
        return [
            'title' => ['required', 'string', 'max:150'],
            'notes' => ['nullable', 'string'],
            'timezone' => ['sometimes', 'string', 'max:64', 'timezone'],
            'starts_on' => ['required', 'date'],
            'ends_on' => ['nullable', 'date', 'after_or_equal:starts_on'],
            'is_active' => ['sometimes', 'boolean'],

            'weekdays' => ['required', 'array', 'min:1'],
            'weekdays.*.weekday' => ['required', 'integer', 'between:0,6'],
            'weekdays.*.start_time' => ['required', 'date_format:H:i'],
            'weekdays.*.end_time' => ['required', 'date_format:H:i'],
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
