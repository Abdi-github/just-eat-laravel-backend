<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class CuisineResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id'                 => $this->id,
            'name'               => $this->name,
            'slug'               => $this->slug,
            'description'        => $this->description,
            'icon'               => $this->icon,
            'image_url'          => $this->image,
            'is_active'          => $this->is_active,
            'sort_order'         => $this->sort_order,
            'restaurants_count'  => $this->whenCounted('restaurants'),
        ];
    }
}
