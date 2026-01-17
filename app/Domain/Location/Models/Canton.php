<?php

namespace App\Domain\Location\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Spatie\Translatable\HasTranslations;

class Canton extends Model
{
    use HasTranslations;

    public array $translatable = ['name'];

    protected $fillable = [
        'code',
        'name',
        'region',
    ];

    public function cities(): HasMany
    {
        return $this->hasMany(City::class);
    }

    public function addresses(): HasMany
    {
        return $this->hasMany(\App\Domain\Address\Models\Address::class);
    }
}
