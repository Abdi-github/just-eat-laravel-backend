<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class RestaurantResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id'                      => $this->id,
            'name'                    => $this->name,
            'slug'                    => $this->slug,
            'description'             => $this->description,
            'phone'                   => $this->phone,
            'email'                   => $this->email,
            'website'                 => $this->website,
            'logo_url'                => $this->logo,
            'cover_image_url'         => $this->cover_image,
            'is_active'               => $this->is_active,
            'is_featured'             => $this->is_featured,
            'price_range'             => $this->price_range,
            'average_rating'          => $this->average_rating,
            'total_reviews'           => $this->total_reviews,
            'minimum_order'           => $this->minimum_order,
            'delivery_fee'            => $this->delivery_fee,
            'estimated_delivery_time' => $this->estimated_delivery_time,
            'accepts_pickup'          => $this->accepts_pickup,
            'accepts_delivery'        => $this->accepts_delivery,
            'brand'                   => new BrandResource($this->whenLoaded('brand')),
            'address'                 => new AddressResource($this->whenLoaded('address')),
            'owner'                   => new UserResource($this->whenLoaded('owner')),
            'cuisines'                => CuisineResource::collection($this->whenLoaded('cuisines')),
            'menu_categories'         => MenuCategoryResource::collection($this->whenLoaded('menuCategories')),
            'delivery_zones'          => DeliveryZoneResource::collection($this->whenLoaded('deliveryZones')),
            'opening_hours'           => OpeningHourResource::collection($this->whenLoaded('openingHours')),
            'created_at'              => $this->created_at,
            'updated_at'              => $this->updated_at,
        ];
    }
}
