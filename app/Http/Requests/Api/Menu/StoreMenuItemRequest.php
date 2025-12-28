<?php

namespace App\Http\Requests\Api\Menu;

use Illuminate\Foundation\Http\FormRequest;

class StoreMenuItemRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'menu_category_id' => ['required', 'integer', 'exists:menu_categories,id'],
            'name'             => ['required', 'array'],
            'name.fr'          => ['required', 'string', 'max:200'],
            'name.de'          => ['nullable', 'string', 'max:200'],
            'name.en'          => ['nullable', 'string', 'max:200'],
            'description'      => ['nullable', 'array'],
            'price'            => ['required', 'numeric', 'min:0'],
            'image'            => ['nullable', 'string'],
            'is_available'     => ['boolean'],
            'is_featured'      => ['boolean'],
            'allergens'        => ['nullable', 'array'],
            'nutritional_info' => ['nullable', 'array'],
            'preparation_time' => ['nullable', 'integer', 'min:0'],
        ];
    }
}
