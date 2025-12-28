<?php

namespace App\Http\Requests\Api\Menu;

use Illuminate\Foundation\Http\FormRequest;

class StoreMenuCategoryRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'name'        => ['required', 'array'],
            'name.fr'     => ['required', 'string', 'max:150'],
            'name.de'     => ['nullable', 'string', 'max:150'],
            'name.en'     => ['nullable', 'string', 'max:150'],
            'description' => ['nullable', 'array'],
            'sort_order'  => ['integer'],
            'is_active'   => ['boolean'],
        ];
    }
}
