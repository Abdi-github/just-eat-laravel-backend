<?php

namespace App\Domain\Restaurant\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

class Restaurant extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'name',
        'slug',
        'description',
        'brand_id',
        'address_id',
        'user_id',
        'phone',
        'email',
        'website',
        'logo',
        'cover_image',
        'is_active',
        'is_featured',
        'price_range',
        'average_rating',
        'total_reviews',
        'minimum_order',
        'delivery_fee',
        'estimated_delivery_time',
        'accepts_pickup',
        'accepts_delivery',
    ];

    protected function casts(): array
    {
        return [
            'is_active'              => 'boolean',
            'is_featured'            => 'boolean',
            'accepts_pickup'         => 'boolean',
            'accepts_delivery'       => 'boolean',
            'average_rating'         => 'decimal:2',
            'minimum_order'          => 'decimal:2',
            'delivery_fee'           => 'decimal:2',
        ];
    }

    public function brand(): BelongsTo
    {
        return $this->belongsTo(Brand::class);
    }

    public function address(): BelongsTo
    {
        return $this->belongsTo(\App\Domain\Address\Models\Address::class);
    }

    public function owner(): BelongsTo
    {
        return $this->belongsTo(\App\Domain\User\Models\User::class, 'user_id');
    }

    public function cuisines(): BelongsToMany
    {
        return $this->belongsToMany(
            \App\Domain\Cuisine\Models\Cuisine::class,
            'restaurant_cuisines'
        );
    }

    public function menuCategories(): HasMany
    {
        return $this->hasMany(\App\Domain\Menu\Models\MenuCategory::class);
    }

    public function menuItems(): HasMany
    {
        return $this->hasMany(\App\Domain\Menu\Models\MenuItem::class);
    }

    public function openingHours(): HasMany
    {
        return $this->hasMany(OpeningHour::class);
    }

    public function deliveryZones(): HasMany
    {
        return $this->hasMany(\App\Domain\Delivery\Models\DeliveryZone::class);
    }

    public function orders(): HasMany
    {
        return $this->hasMany(\App\Domain\Order\Models\Order::class);
    }

    public function reviews(): HasMany
    {
        return $this->hasMany(\App\Domain\Review\Models\Review::class);
    }

    public function favorites(): HasMany
    {
        return $this->hasMany(Favorite::class);
    }

    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }

    public function scopeFeatured($query)
    {
        return $query->where('is_featured', true);
    }
}
