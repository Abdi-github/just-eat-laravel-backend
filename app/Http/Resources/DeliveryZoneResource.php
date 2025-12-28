<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class DeliveryZoneResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id'             => $this->id,
            'zone_name'      => $this->zone_name,
            'radius_km'      => $this->radius_km,
            'delivery_fee'   => $this->delivery_fee,
            'minimum_order'  => $this->minimum_order,
            'estimated_time' => $this->estimated_time,
        ];
    }
}
