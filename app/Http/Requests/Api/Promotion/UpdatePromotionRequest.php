<?php

namespace App\Http\Requests\Api\Promotion;

use Illuminate\Foundation\Http\FormRequest;

class UpdatePromotionRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        $id = $this->route('id');

        return [
            'code'          => ['sometimes', 'string', 'max:50', "unique:promotions,code,{$id}"],
            'title'         => ['sometimes', 'string', 'max:255'],
            'description'   => ['nullable', 'string'],
            'type'          => ['sometimes', 'in:percentage,fixed'],
            'value'         => ['sometimes', 'numeric', 'min:0'],
            'restaurant_id' => ['nullable', 'integer', 'exists:restaurants,id'],
            'minimum_order' => ['nullable', 'numeric', 'min:0'],
            'max_discount'  => ['nullable', 'numeric', 'min:0'],
            'usage_limit'   => ['nullable', 'integer', 'min:1'],
            'is_active'     => ['boolean'],
            'starts_at'     => ['nullable', 'date'],
            'expires_at'    => ['nullable', 'date'],
        ];
    }
}
