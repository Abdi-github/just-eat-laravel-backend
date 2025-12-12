<?php

namespace App\Domain\Menu\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;
use Spatie\Translatable\HasTranslations;

class MenuItem extends Model
{
    use HasTranslations, SoftDeletes;

    public array $translatable = ['name', 'description'];

    protected $fillable = [
        'restaurant_id',
        'menu_category_id',
        'name',
        'description',
        'price',
        'image',
        'is_available',
        'is_featured',
        'allergens',
        'nutritional_info',
        'preparation_time',
    ];

    protected function casts(): array
    {
        return [
            'is_available'     => 'boolean',
            'is_featured'      => 'boolean',
            'price'            => 'decimal:2',
            'allergens'        => 'array',
            'nutritional_info' => 'array',
        ];
    }

    public function restaurant(): BelongsTo
    {
        return $this->belongsTo(\App\Domain\Restaurant\Models\Restaurant::class);
    }

    public function category(): BelongsTo
    {
        return $this->belongsTo(MenuCategory::class, 'menu_category_id');
    }

    public function scopeAvailable($query)
    {
        return $query->where('is_available', true);
    }
}
