<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class PaymentInitiateRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'course_id' => 'required|integer|exists:courses,id',
            'buyer_name' => 'required|string|max:255',
            'buyer_email' => 'required|email|max:255',
            'buyer_phone' => 'required|string|max:20',
            'billing_address_line1' => ['required', 'string', 'max:255'],
            'billing_address_line2' => ['nullable', 'string', 'max:255'],
            'billing_city' => ['required', 'string', 'max:120'],
            'billing_state' => ['required', 'string', 'max:120'],
            'billing_postal_code' => ['required', 'string', 'max:20'],
            'billing_country' => ['required', 'string', 'size:2', Rule::in(array_keys(config('countries.iso_codes')))],
            'currency_code' => ['nullable', 'string', Rule::in(array_keys(config('currencies.rates')))],
            'plan_ids' => ['nullable', 'array'],
            'plan_ids.*' => ['integer', 'exists:plans,id'],
            'amount' => ['nullable', 'numeric', 'min:1'],
        ];
    }

    public function messages(): array
    {
        return [
            'course_id.required' => 'Course ID is required.',
            'course_id.exists' => 'The selected course does not exist.',
            'buyer_name.required' => 'Full name is required.',
            'buyer_email.required' => 'Email address is required.',
            'buyer_email.email' => 'Please provide a valid email address.',
            'buyer_phone.required' => 'Phone number is required.',
            'billing_address_line1.required' => 'Billing address is required.',
            'billing_city.required' => 'Billing city is required.',
            'billing_state.required' => 'Billing state/province is required.',
            'billing_postal_code.required' => 'Billing postal code is required.',
            'billing_country.required' => 'Billing country is required.',
            'billing_country.in' => 'Please select a valid billing country.',
            'currency_code.in' => 'The selected currency is not supported.',
            'amount.numeric' => 'The amount must be a valid number.',
            'amount.min' => 'The amount must be greater than zero.',
        ];
    }
}