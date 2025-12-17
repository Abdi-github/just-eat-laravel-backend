<?php

declare(strict_types=1);

namespace App\Domain\Delivery\Models;

use App\Domain\Order\Models\Order;
use App\Domain\Restaurant\Models\Restaurant;
use App\Domain\User\Models\User;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Delivery extends Model
{
    protected $fillable = [
        'order_id',
        'restaurant_id',
        'courier_id',
        'status',
        'pickup_address',
        'delivery_address',
        'delivery_fee',
        'distance_km',
        'estimated_pickup_at',
        'estimated_delivery_at',
        'assigned_at',
        'picked_up_at',
        'in_transit_at',
        'delivered_at',
        'cancelled_at',
        'cancellation_reason',
        'courier_location',
        'notes',
    ];

    protected function casts(): array
    {
        return [
            'delivery_address'      => 'array',
            'courier_location'      => 'array',
            'delivery_fee'          => 'float',
            'distance_km'           => 'float',
            'estimated_pickup_at'   => 'datetime',
            'estimated_delivery_at' => 'datetime',
            'assigned_at'           => 'datetime',
            'picked_up_at'          => 'datetime',
            'in_transit_at'         => 'datetime',
            'delivered_at'          => 'datetime',
            'cancelled_at'          => 'datetime',
        ];
    }

    public function order(): BelongsTo
    {
        return $this->belongsTo(Order::class);
    }

    public function restaurant(): BelongsTo
    {
        return $this->belongsTo(Restaurant::class);
    }

    public function courier(): BelongsTo
    {
        return $this->belongsTo(User::class, 'courier_id');
    }
}
