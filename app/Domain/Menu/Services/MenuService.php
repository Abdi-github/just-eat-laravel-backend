<?php

namespace App\Domain\Menu\Services;

use App\Domain\Menu\Models\MenuCategory;
use App\Domain\Menu\Models\MenuItem;
use App\Domain\Menu\Repositories\MenuCategoryRepositoryInterface;
use App\Domain\Menu\Repositories\MenuItemRepositoryInterface;
use Illuminate\Support\Collection;

class MenuService
{
    public function __construct(
        private readonly MenuCategoryRepositoryInterface $categories,
        private readonly MenuItemRepositoryInterface $items,
    ) {}

    // Categories

    public function getCategories(int $restaurantId): Collection
    {
        return $this->categories->findByRestaurant($restaurantId);
    }

    public function createCategory(int $restaurantId, array $data): MenuCategory
    {
        $data['restaurant_id'] = $restaurantId;

        return $this->categories->create($data);
    }

    public function updateCategory(int $restaurantId, int $catId, array $data): ?MenuCategory
    {
        $category = $this->categories->findByIdAndRestaurant($restaurantId, $catId);

        if (! $category) {
            return null;
        }

        return $this->categories->update($category, $data);
    }

    public function deleteCategory(int $restaurantId, int $catId): bool
    {
        $category = $this->categories->findByIdAndRestaurant($restaurantId, $catId);

        if (! $category) {
            return false;
        }

        return $this->categories->delete($category);
    }

    // Items

    public function getItems(int $restaurantId): Collection
    {
        return $this->items->findByRestaurant($restaurantId);
    }

    public function createItem(int $restaurantId, array $data): MenuItem
    {
        $data['restaurant_id'] = $restaurantId;

        return $this->items->create($data);
    }

    public function updateItem(int $restaurantId, int $itemId, array $data): ?MenuItem
    {
        $item = $this->items->findByIdAndRestaurant($restaurantId, $itemId);

        if (! $item) {
            return null;
        }

        return $this->items->update($item, $data);
    }

    public function deleteItem(int $restaurantId, int $itemId): bool
    {
        $item = $this->items->findByIdAndRestaurant($restaurantId, $itemId);

        if (! $item) {
            return false;
        }

        return $this->items->delete($item);
    }
}
