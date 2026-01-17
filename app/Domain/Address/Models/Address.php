<?php

namespace App\Domain\Address\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Address extends Model
{
    protected $fillable = [
        'user_id',
        'street',
        'street_number',
        'zip_code',
        'city_id',
        'canton_id',
        'latitude',
        'longitude',
        'is_default',
    ];

    protected function casts(): array
    {
        return [
            'latitude'   => 'decimal:7',
            'longitude'  => 'decimal:7',
            'is_default' => 'boolean',
        ];
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(\App\Domain\User\Models\User::class);
    }

    public function city(): BelongsTo
    {
        return $this->belongsTo(\App\Domain\Location\Models\City::class);
    }

    public function canton(): BelongsTo
    {
        return $this->belongsTo(\App\Domain\Location\Models\Canton::class);
    }
}
