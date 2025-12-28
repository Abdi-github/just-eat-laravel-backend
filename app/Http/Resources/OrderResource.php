<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class OrderResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id'                      => $this->id,
            'order_number'            => $this->order_number,
            'status'                  => $this->status,
            'order_type'              => $this->order_type,
            'items'                   => $this->items,
            'subtotal'                => $this->subtotal,
            'delivery_fee'            => $this->delivery_fee,
            'tax'                     => $this->tax,
            'total'                   => $this->total,
            'delivery_address'        => $this->delivery_address,
            'special_instructions'    => $this->special_instructions,
            'estimated_delivery_time' => $this->estimated_delivery_time,
            'payment_method'          => $this->payment_method,
            'payment_status'          => $this->payment_status,
            'user'                    => new UserResource($this->whenLoaded('user')),
            'restaurant'              => new RestaurantResource($this->whenLoaded('restaurant')),
            'created_at'              => $this->created_at,
            'updated_at'              => $this->updated_at,
        ];
    }
}
