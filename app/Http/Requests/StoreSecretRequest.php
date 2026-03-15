<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class StoreSecretRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'password' => ['required', 'string', 'max:10000'],
            'max_views' => ['nullable', 'integer', 'min:1', 'max:100'],
            'expires_at' => ['nullable', 'date', 'after:now'],
        ];
    }

    public function messages(): array
    {
        return [
            'password.required' => 'A password or secret text is required.',
            'password.max' => 'The secret may not exceed 10,000 characters.',
            'max_views.integer' => 'Maximum views must be a whole number.',
            'max_views.min' => 'Maximum views must be at least 1.',
            'max_views.max' => 'Maximum views may not exceed 100.',
            'expires_at.date' => 'Please enter a valid expiry date.',
            'expires_at.after' => 'The expiry date must be in the future.',
        ];
    }
}

