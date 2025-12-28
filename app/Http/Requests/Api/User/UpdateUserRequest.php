<?php

namespace App\Http\Requests\Api\User;

use Illuminate\Foundation\Http\FormRequest;

class UpdateUserRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        $id = $this->route('id');

        return [
            'first_name'         => ['sometimes', 'string', 'max:100'],
            'last_name'          => ['sometimes', 'string', 'max:100'],
            'phone'              => ['nullable', 'string', 'max:20'],
            'preferred_language' => ['nullable', 'string', 'in:fr,de,en'],
            'username'           => ['sometimes', 'string', 'max:50', "unique:users,username,{$id}"],
        ];
    }
}
