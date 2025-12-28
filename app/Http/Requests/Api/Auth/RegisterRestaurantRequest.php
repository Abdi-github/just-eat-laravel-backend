<?php

namespace App\Http\Requests\Api\Auth;

use Illuminate\Foundation\Http\FormRequest;

class RegisterRestaurantRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            // User fields
            'username'            => ['required', 'string', 'max:50', 'unique:users'],
            'email'               => ['required', 'email', 'max:255', 'unique:users'],
            'password'            => ['required', 'string', 'min:8', 'confirmed'],
            'first_name'          => ['nullable', 'string', 'max:100'],
            'last_name'           => ['nullable', 'string', 'max:100'],
            'phone'               => ['nullable', 'string', 'max:20'],
            'preferred_language'  => ['nullable', 'string', 'in:fr,de,en'],
            // Restaurant fields
            'restaurant_name'     => ['required', 'string', 'max:255'],
            'restaurant_phone'    => ['nullable', 'string', 'max:20'],
            'restaurant_email'    => ['nullable', 'email', 'max:255'],
            'application_note'    => ['nullable', 'string', 'max:1000'],
            // Address fields
            'street'              => ['nullable', 'string', 'max:255'],
            'zip_code'            => ['nullable', 'string', 'max:10'],
            'city_id'             => ['nullable', 'integer', 'exists:cities,id'],
            'canton_id'           => ['nullable', 'integer', 'exists:cantons,id'],
        ];
    }
}
