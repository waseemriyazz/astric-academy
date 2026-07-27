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
            'currency_code' => ['nullable', 'string', Rule::in(array_keys(config('currencies.rates')))],
            'plan_ids' => ['nullable', 'array'],
            'plan_ids.*' => ['integer', 'exists:plans,id'],
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
            'currency_code.in' => 'The selected currency is not supported.',
        ];
    }
}