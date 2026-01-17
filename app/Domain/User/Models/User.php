<?php

namespace App\Domain\User\Models;

use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Laravel\Sanctum\HasApiTokens;
use Spatie\Permission\Traits\HasRoles;

class User extends Authenticatable implements MustVerifyEmail
{
    use HasApiTokens, HasFactory, HasRoles, Notifiable, SoftDeletes;

    protected $fillable = [
        'username',
        'email',
        'password',
        'first_name',
        'last_name',
        'phone',
        'avatar',
        'is_active',
        'preferred_language',
        'email_verified_at',
        'application_status',
        'application_type',
        'application_note',
        'application_reviewed_by',
        'application_reviewed_at',
        'application_rejection_reason',
        'is_verified',
    ];

    protected $hidden = [
        'password',
        'remember_token',
    ];

    protected function casts(): array
    {
        return [
            'email_verified_at'          => 'datetime',
            'application_reviewed_at'    => 'datetime',
            'is_active'                  => 'boolean',
            'is_verified'                => 'boolean',
            'password'                   => 'hashed',
        ];
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
        return $this->hasMany(\App\Domain\Restaurant\Models\Favorite::class);
    }

    public function addresses(): HasMany
    {
        return $this->hasMany(\App\Domain\Address\Models\Address::class);
    }

    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }
}
