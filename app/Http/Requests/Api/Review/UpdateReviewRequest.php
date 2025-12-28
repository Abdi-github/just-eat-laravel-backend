<?php

namespace App\Http\Requests\Api\Review;

use Illuminate\Foundation\Http\FormRequest;

class UpdateReviewRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'rating'  => ['sometimes', 'integer', 'min:1', 'max:5'],
            'title'   => ['nullable', 'string', 'max:200'],
            'comment' => ['nullable', 'string', 'max:1000'],
        ];
    }
}
