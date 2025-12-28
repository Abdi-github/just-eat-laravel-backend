<?php

namespace App\Http\Requests\Api\Auth;

use Illuminate\Foundation\Http\FormRequest;

class RegisterRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'username'           => ['required', 'string', 'max:50', 'unique:users'],
            'email'              => ['required', 'email', 'max:255', 'unique:users'],
            'password'           => ['required', 'string', 'min:8', 'confirmed'],
            'first_name'         => ['nullable', 'string', 'max:100'],
            'last_name'          => ['nullable', 'string', 'max:100'],
            'phone'              => ['nullable', 'string', 'max:20'],
            'preferred_language' => ['nullable', 'string', 'in:fr,de,en'],
        ];
    }
}
