<?php

namespace App\Domain\Restaurant\Repositories;

use App\Domain\Restaurant\Models\Favorite;
use Illuminate\Support\Collection;

interface FavoriteRepositoryInterface
{
    public function findByUser(int $userId): Collection;
    public function findByUserAndRestaurant(int $userId, int $restaurantId): ?Favorite;
    public function create(array $data): Favorite;
    public function deleteByUserAndRestaurant(int $userId, int $restaurantId): bool;
}
