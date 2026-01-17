<?php

namespace App\Domain\Review\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Review extends Model
{
    protected $fillable = [
        'user_id',
        'restaurant_id',
        'order_id',
        'rating',
        'title',
        'comment',
        'is_verified',
        'is_visible',
    ];

    protected function casts(): array
    {
        return [
            'rating'      => 'integer',
            'is_verified' => 'boolean',
            'is_visible'  => 'boolean',
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

    public function order(): BelongsTo
    {
        return $this->belongsTo(\App\Domain\Order\Models\Order::class);
    }

    public function scopeVisible($query)
    {
        return $query->where('is_visible', true);
    }
}
