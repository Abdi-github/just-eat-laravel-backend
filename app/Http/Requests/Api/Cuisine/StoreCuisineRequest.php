<?php

namespace App\Http\Requests\Api\Cuisine;

use Illuminate\Foundation\Http\FormRequest;

class StoreCuisineRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()?->can('menu.create') ?? false;
    }

    public function rules(): array
    {
        return [
            'name'        => ['required', 'array'],
            'name.fr'     => ['required', 'string', 'max:100'],
            'name.de'     => ['nullable', 'string', 'max:100'],
            'name.en'     => ['nullable', 'string', 'max:100'],
            'slug'        => ['required', 'string', 'unique:cuisines'],
            'description' => ['nullable', 'array'],
            'icon'        => ['nullable', 'string'],
            'image'       => ['nullable', 'string'],
            'is_active'   => ['boolean'],
            'sort_order'  => ['integer'],
        ];
    }
}
