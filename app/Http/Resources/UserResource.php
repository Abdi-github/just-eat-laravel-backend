<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class UserResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id'                 => $this->id,
            'username'           => $this->username,
            'email'              => $this->email,
            'first_name'         => $this->first_name,
            'last_name'          => $this->last_name,
            'phone'              => $this->phone,
            'avatar'             => $this->avatar,
            'is_active'          => $this->is_active,
            'preferred_language' => $this->preferred_language,
            'email_verified_at'  => $this->email_verified_at,
            'roles'              => $this->whenLoaded('roles', fn () => $this->getRoleNames()),
            'created_at'         => $this->created_at,
        ];
    }
}
