<?php

namespace App\Domain\Order\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Database\Eloquent\SoftDeletes;

class Order extends Model
{
    use SoftDeletes;

    protected $fillable = [
        'user_id',
        'restaurant_id',
        'order_number',
        'status',
        'order_type',
        'items',
        'subtotal',
        'delivery_fee',
        'tax',
        'total',
        'delivery_address',
        'special_instructions',
        'estimated_delivery_time',
        'payment_method',
        'payment_status',
    ];

    protected function casts(): array
    {
        return [
            'items'                    => 'array',
            'delivery_address'         => 'array',
            'subtotal'                 => 'decimal:2',
            'delivery_fee'             => 'decimal:2',
            'tax'                      => 'decimal:2',
            'total'                    => 'decimal:2',
            'estimated_delivery_time'  => 'datetime',
        ];
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(\App\Domain\User\Models\User::class);
    }

    public function restaurant(): BelongsTo
    {
        return $this->belongsTo(\App\Domain\Restaurant\Models\Restaurant::class);
    }

    public function review(): HasOne
    {
        return $this->hasOne(\App\Domain\Review\Models\Review::class);
    }
}
