<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

/**
 * Form request for storing a new customer.
 * 
 * Validates customer data including name, email, and phone number.
 * Ensures email uniqueness among non-deleted customers.
 */
class StoreCustomerRequest extends FormRequest
{
    /**
     * Get the validation rules for storing a customer.
     *
     * @return array<string, array<int, mixed>> The validation rules
     */
    public function rules(): array
    {
        return [
            'name' => ['required', 'string', 'max:150'],
            'email' => [
                'required',
                'email',
                'max:255',
                // Unique email among non-deleted customers
                Rule::unique('customers', 'email')->whereNull('deleted_at'),
            ],
            'phone' => ['nullable', 'string', 'max:30'],
        ];
    }
}
