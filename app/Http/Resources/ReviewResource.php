<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class ReviewResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id'            => $this->id,
            'restaurant_id' => $this->restaurant_id,
            'order_id'      => $this->order_id,
            'rating'        => $this->rating,
            'title'         => $this->title,
            'comment'       => $this->comment,
            'is_verified'   => $this->is_verified,
            'is_visible'    => $this->is_visible,
            'user'          => new UserResource($this->whenLoaded('user')),
            'restaurant'    => new RestaurantResource($this->whenLoaded('restaurant')),
            'created_at'    => $this->created_at,
            'updated_at'    => $this->updated_at,
        ];
    }
}
