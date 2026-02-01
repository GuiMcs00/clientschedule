<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

/**
 * Form request for updating an existing customer.
 * 
 * Validates customer data including name, email, and phone number for updates.
 * Ensures email uniqueness among non-deleted customers, excluding the current customer.
 */
class UpdateCustomerRequest extends FormRequest
{
    /**
     * Get the validation rules for updating a customer.
     * 
     * Uses 'sometimes' to allow partial updates (only validate fields that are present).
     * Ignores the current customer when checking email uniqueness.
     *
     * @return array<string, array<int, mixed>> The validation rules
     */
    public function rules(): array
    {
        $id = $this->route('customer')?->id ?? $this->route('customer');

        return [
            'name' => ['sometimes', 'required', 'string', 'max:150'],
            'email' => [
                'sometimes',
                'required',
                'email',
                'max:255',
                Rule::unique('customers', 'email')
                    ->whereNull('deleted_at')
                    ->ignore($id),
            ],
            'phone' => ['nullable', 'string', 'max:30'],
        ];
    }
}
