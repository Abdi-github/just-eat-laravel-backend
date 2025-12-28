<?php

namespace App\Http\Requests\Api\Address;

use Illuminate\Foundation\Http\FormRequest;

class UpdateAddressRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'street'        => ['sometimes', 'string', 'max:255'],
            'street_number' => ['nullable', 'string', 'max:20'],
            'zip_code'      => ['sometimes', 'string', 'max:10'],
            'city_id'       => ['sometimes', 'integer', 'exists:cities,id'],
            'canton_id'     => ['sometimes', 'integer', 'exists:cantons,id'],
            'latitude'      => ['nullable', 'numeric', 'between:-90,90'],
            'longitude'     => ['nullable', 'numeric', 'between:-180,180'],
            'is_default'    => ['boolean'],
        ];
    }
}
