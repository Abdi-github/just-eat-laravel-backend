<?php

namespace App\Domain\Menu\Repositories;

use App\Domain\Menu\Models\MenuItem;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Support\Collection;

interface MenuItemRepositoryInterface
{
    public function findByRestaurant(int $restaurantId): Collection;
    public function findByIdAndRestaurant(int $restaurantId, int $itemId): ?MenuItem;
    public function searchByRestaurant(int $restaurantId, array $filters = [], int $perPage = 20): LengthAwarePaginator;
    public function create(array $data): MenuItem;
    public function update(MenuItem $item, array $data): MenuItem;
    public function delete(MenuItem $item): bool;
}
