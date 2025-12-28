<?php

namespace App\Http\Requests\Api\Cuisine;

use Illuminate\Foundation\Http\FormRequest;

class UpdateCuisineRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()?->can('menu.update') ?? false;
    }

    public function rules(): array
    {
        $id = $this->route('id');

        return [
            'name'        => ['sometimes', 'array'],
            'name.fr'     => ['sometimes', 'string', 'max:100'],
            'name.de'     => ['nullable', 'string', 'max:100'],
            'name.en'     => ['nullable', 'string', 'max:100'],
            'slug'        => ['sometimes', 'string', "unique:cuisines,slug,{$id}"],
            'description' => ['nullable', 'array'],
            'icon'        => ['nullable', 'string'],
            'image'       => ['nullable', 'string'],
            'is_active'   => ['boolean'],
            'sort_order'  => ['integer'],
        ];
    }
}
