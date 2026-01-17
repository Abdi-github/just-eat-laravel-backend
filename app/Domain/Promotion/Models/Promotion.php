<?php

namespace App\Domain\Promotion\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Promotion extends Model
{
    protected $fillable = [
        'restaurant_id',
        'code',
        'title',
        'description',
        'type',
        'value',
        'minimum_order',
        'max_discount',
        'usage_limit',
        'usage_count',
        'is_active',
        'starts_at',
        'expires_at',
    ];

    protected function casts(): array
    {
        return [
            'value'         => 'decimal:2',
            'minimum_order' => 'decimal:2',
            'max_discount'  => 'decimal:2',
            'is_active'     => 'boolean',
            'starts_at'     => 'datetime',
            'expires_at'    => 'datetime',
        ];
    }

    public function restaurant(): BelongsTo
    {
        return $this->belongsTo(\App\Domain\Restaurant\Models\Restaurant::class);
    }

    public function isExpired(): bool
    {
        return $this->expires_at !== null && $this->expires_at->isPast();
    }

    public function isUsageLimitReached(): bool
    {
        return $this->usage_limit !== null && $this->usage_count >= $this->usage_limit;
    }

    public function calculateDiscount(float $orderTotal): float
    {
        if ($orderTotal < (float) $this->minimum_order) {
            return 0;
        }

        if ($this->type === 'percentage') {
            $discount = $orderTotal * ((float) $this->value / 100);
            if ($this->max_discount !== null) {
                $discount = min($discount, (float) $this->max_discount);
            }
            return $discount;
        }

        return min((float) $this->value, $orderTotal);
    }
}
