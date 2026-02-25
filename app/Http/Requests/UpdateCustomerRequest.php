<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class UpdateCustomerRequest extends FormRequest
{
    /**
     * All users are authorized to update a customer.
     */
    public function authorize(): bool
    {
        return true;
    }

    /**
     * Validation rules for updating a customer.
     */
    public function rules(): array
    {
        return [
            'name'    => 'required|string|max:255',
            'email'   => 'nullable|email|max:255',
            'phone'   => 'nullable|string|max:50',
            'company' => 'nullable|string|max:255',
            'status'  => 'required|in:active,inactive',
        ];
    }
}
