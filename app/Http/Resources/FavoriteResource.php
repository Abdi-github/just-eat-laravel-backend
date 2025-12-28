<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class FavoriteResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id'            => $this->id,
            'restaurant_id' => $this->restaurant_id,
            'restaurant'    => new RestaurantResource($this->whenLoaded('restaurant')),
            'created_at'    => $this->created_at,
        ];
    }
}
