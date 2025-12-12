<?php

declare(strict_types=1);

namespace App\Domain\Restaurant\Services;

use App\Domain\Restaurant\Models\Favorite;
use App\Domain\Restaurant\Repositories\FavoriteRepositoryInterface;
use Illuminate\Support\Collection;

class FavoriteService
{
    public function __construct(private readonly FavoriteRepositoryInterface $favorites) {}

    public function getForUser(int $userId): Collection
    {
        return $this->favorites->findByUser($userId);
    }

    public function add(int $userId, int $restaurantId): ?Favorite
    {
        $existing = $this->favorites->findByUserAndRestaurant($userId, $restaurantId);

        if ($existing) {
            return null;
        }

        return $this->favorites->create([
            'user_id'       => $userId,
            'restaurant_id' => $restaurantId,
        ]);
    }

    public function remove(int $userId, int $restaurantId): bool
    {
        return $this->favorites->deleteByUserAndRestaurant($userId, $restaurantId);
    }
}
