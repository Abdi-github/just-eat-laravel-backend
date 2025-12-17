<?php

namespace App\Domain\Delivery\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class DeliveryZone extends Model
{
    protected $fillable = [
        'restaurant_id',
        'zone_name',
        'radius_km',
        'delivery_fee',
        'minimum_order',
        'estimated_time',
    ];

    protected function casts(): array
    {
        return [
            'radius_km'     => 'decimal:2',
            'delivery_fee'  => 'decimal:2',
            'minimum_order' => 'decimal:2',
        ];
    }

    public function restaurant(): BelongsTo
    {
        return $this->belongsTo(\App\Domain\Restaurant\Models\Restaurant::class);
    }
}
