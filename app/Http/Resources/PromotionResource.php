<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class PromotionResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id'            => $this->id,
            'restaurant_id' => $this->restaurant_id,
            'code'          => $this->code,
            'title'         => $this->title,
            'description'   => $this->description,
            'type'          => $this->type,
            'value'         => (float) $this->value,
            'minimum_order' => (float) $this->minimum_order,
            'max_discount'  => $this->max_discount ? (float) $this->max_discount : null,
            'usage_limit'   => $this->usage_limit,
            'usage_count'   => $this->usage_count,
            'is_active'     => $this->is_active,
            'starts_at'     => $this->starts_at?->toIso8601String(),
            'expires_at'    => $this->expires_at?->toIso8601String(),
            'is_expired'    => $this->isExpired(),
            'restaurant'    => new RestaurantResource($this->whenLoaded('restaurant')),
            'created_at'    => $this->created_at,
        ];
    }
}
