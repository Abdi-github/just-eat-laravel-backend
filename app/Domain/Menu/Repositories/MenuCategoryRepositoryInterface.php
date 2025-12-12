<?php

namespace App\Domain\Menu\Repositories;

use App\Domain\Menu\Models\MenuCategory;
use Illuminate\Support\Collection;

interface MenuCategoryRepositoryInterface
{
    public function findByRestaurant(int $restaurantId): Collection;
    public function findByIdAndRestaurant(int $restaurantId, int $catId): ?MenuCategory;
    public function getWithAvailableItems(int $restaurantId): Collection;
    public function create(array $data): MenuCategory;
    public function update(MenuCategory $category, array $data): MenuCategory;
    public function delete(MenuCategory $category): bool;
}
