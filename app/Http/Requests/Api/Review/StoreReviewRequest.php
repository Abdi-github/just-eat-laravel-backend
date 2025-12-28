<?php

namespace App\Http\Requests\Api\Review;

use Illuminate\Foundation\Http\FormRequest;

class StoreReviewRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'restaurant_id' => ['required', 'integer', 'exists:restaurants,id'],
            'order_id'      => ['nullable', 'integer', 'exists:orders,id'],
            'rating'        => ['required', 'integer', 'min:1', 'max:5'],
            'title'         => ['nullable', 'string', 'max:200'],
            'comment'       => ['nullable', 'string', 'max:1000'],
        ];
    }
}
