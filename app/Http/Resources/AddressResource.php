<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class AddressResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id'             => $this->id,
            'user_id'        => $this->user_id,
            'street'         => $this->street,
            'street_number'  => $this->street_number,
            'zip_code'       => $this->zip_code,
            'latitude'       => $this->latitude,
            'longitude'      => $this->longitude,
            'is_default'     => $this->is_default,
            'city'           => new CityResource($this->whenLoaded('city')),
            'canton'         => new CantonResource($this->whenLoaded('canton')),
            'created_at'     => $this->created_at,
        ];
    }
}
