<?php

namespace App\Http\Requests\Api\Restaurant;

use Illuminate\Foundation\Http\FormRequest;

class UpdateRestaurantRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()->can('restaurants.update');
    }

    public function rules(): array
    {
        return [
            'name'                    => ['sometimes', 'string', 'max:255'],
            'description'             => ['nullable', 'string'],
            'brand_id'                => ['nullable', 'integer', 'exists:brands,id'],
            'phone'                   => ['nullable', 'string', 'max:20'],
            'email'                   => ['nullable', 'email', 'max:255'],
            'website'                 => ['nullable', 'url', 'max:255'],
            'price_range'             => ['nullable', 'string', 'in:budget,moderate,upscale,fine_dining'],
            'minimum_order'           => ['nullable', 'numeric', 'min:0'],
            'delivery_fee'            => ['nullable', 'numeric', 'min:0'],
            'estimated_delivery_time' => ['nullable', 'integer', 'min:0'],
            'accepts_pickup'          => ['boolean'],
            'accepts_delivery'        => ['boolean'],
            'is_active'               => ['boolean'],
            'is_featured'             => ['boolean'],
        ];
    }
}
