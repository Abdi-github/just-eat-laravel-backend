<?php

namespace App\Http\Requests\Api\Restaurant;

use Illuminate\Foundation\Http\FormRequest;

class StoreRestaurantRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()->can('restaurants.create');
    }

    public function rules(): array
    {
        return [
            'name'                    => ['required', 'string', 'max:255'],
            'description'             => ['nullable', 'string'],
            'brand_id'                => ['nullable', 'integer', 'exists:brands,id'],
            'address_id'              => ['required', 'integer', 'exists:addresses,id'],
            'user_id'                 => ['required', 'integer', 'exists:users,id'],
            'phone'                   => ['nullable', 'string', 'max:20'],
            'email'                   => ['nullable', 'email', 'max:255'],
            'website'                 => ['nullable', 'url', 'max:255'],
            'price_range'             => ['nullable', 'string', 'in:budget,moderate,upscale,fine_dining'],
            'minimum_order'           => ['nullable', 'numeric', 'min:0'],
            'delivery_fee'            => ['nullable', 'numeric', 'min:0'],
            'estimated_delivery_time' => ['nullable', 'integer', 'min:0'],
            'accepts_pickup'          => ['boolean'],
            'accepts_delivery'        => ['boolean'],
        ];
    }
}
