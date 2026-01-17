<?php

namespace App\Infrastructure\Persistence\Eloquent;

use App\Domain\Restaurant\Models\Favorite;
use App\Domain\Restaurant\Repositories\FavoriteRepositoryInterface;
use Illuminate\Support\Collection;

class EloquentFavoriteRepository implements FavoriteRepositoryInterface
{
    public function __construct(private Favorite $model) {}

    public function findByUser(int $userId): Collection
    {
        return $this->model
            ->with('restaurant.cuisines')
            ->where('user_id', $userId)
            ->orderByDesc('created_at')
            ->get();
    }

    public function findByUserAndRestaurant(int $userId, int $restaurantId): ?Favorite
    {
        return $this->model
            ->where('user_id', $userId)
            ->where('restaurant_id', $restaurantId)
            ->first();
    }

    public function create(array $data): Favorite
    {
        return $this->model->create($data);
    }

    public function deleteByUserAndRestaurant(int $userId, int $restaurantId): bool
    {
        return (bool) $this->model
            ->where('user_id', $userId)
            ->where('restaurant_id', $restaurantId)
            ->delete();
    }
}
