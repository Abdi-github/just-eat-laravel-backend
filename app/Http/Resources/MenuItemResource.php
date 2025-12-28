<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class MenuItemResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id'               => $this->id,
            'menu_category_id' => $this->menu_category_id,
            'name'             => $this->name,
            'description'      => $this->description,
            'price'            => $this->price,
            'image_url'        => $this->image,
            'is_available'     => $this->is_available,
            'is_featured'      => $this->is_featured,
            'allergens'        => $this->allergens,
            'nutritional_info' => $this->nutritional_info,
            'preparation_time' => $this->preparation_time,
            'category'         => new MenuCategoryResource($this->whenLoaded('category')),
        ];
    }
}
