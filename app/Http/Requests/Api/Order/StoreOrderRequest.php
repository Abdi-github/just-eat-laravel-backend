<?php

namespace App\Http\Requests\Api\Order;

use Illuminate\Foundation\Http\FormRequest;

class StoreOrderRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'restaurant_id'        => ['required', 'integer', 'exists:restaurants,id'],
            'order_type'           => ['required', 'string', 'in:delivery,pickup'],
            'items'                => ['required', 'array', 'min:1'],
            'items.*.menu_item_id' => ['required', 'integer'],
            'items.*.name'         => ['required', 'string'],
            'items.*.price'        => ['required', 'numeric', 'min:0'],
            'items.*.quantity'     => ['required', 'integer', 'min:1'],
            'subtotal'             => ['required', 'numeric', 'min:0'],
            'delivery_fee'         => ['nullable', 'numeric', 'min:0'],
            'tax'                  => ['nullable', 'numeric', 'min:0'],
            'total'                => ['required', 'numeric', 'min:0'],
            'delivery_address'     => ['required_if:order_type,delivery', 'nullable', 'array'],
            'special_instructions' => ['nullable', 'string', 'max:500'],
            'payment_method'       => ['required', 'string', 'in:credit_card,debit_card,paypal,twint,cash'],
        ];
    }
}
