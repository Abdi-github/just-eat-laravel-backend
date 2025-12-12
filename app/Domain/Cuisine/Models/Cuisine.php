<?php

namespace App\Domain\Cuisine\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Spatie\Translatable\HasTranslations;

class Cuisine extends Model
{
    use HasTranslations;

    public array $translatable = ['name', 'description'];

    protected $fillable = [
        'name',
        'slug',
        'description',
        'icon',
        'image',
        'is_active',
        'sort_order',
    ];

    protected function casts(): array
    {
        return [
            'is_active'  => 'boolean',
            'sort_order' => 'integer',
        ];
    }

    public function restaurants(): BelongsToMany
    {
        return $this->belongsToMany(
            \App\Domain\Restaurant\Models\Restaurant::class,
            'restaurant_cuisines'
        );
    }

    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }
}
