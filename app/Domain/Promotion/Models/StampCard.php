<?php

declare(strict_types=1);

namespace App\Domain\Promotion\Models;

use App\Domain\Restaurant\Models\Restaurant;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class StampCard extends Model
{
    protected $fillable = [
        'restaurant_id',
        'name',
        'description',
        'stamps_required',
        'reward_description',
        'reward_type',
        'reward_value',
        'valid_from',
        'valid_until',
        'is_active',
    ];

    protected function casts(): array
    {
        return [
            'stamps_required' => 'integer',
            'reward_value'    => 'float',
            'is_active'       => 'boolean',
            'valid_from'      => 'datetime',
            'valid_until'     => 'datetime',
        ];
    }

    public function restaurant(): BelongsTo
    {
        return $this->belongsTo(Restaurant::class);
    }
}
