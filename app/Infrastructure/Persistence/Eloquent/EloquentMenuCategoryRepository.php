<?php

namespace App\Infrastructure\Persistence\Eloquent;

use App\Domain\Menu\Models\MenuCategory;
use App\Domain\Menu\Repositories\MenuCategoryRepositoryInterface;
use Illuminate\Support\Collection;

class EloquentMenuCategoryRepository implements MenuCategoryRepositoryInterface
{
    public function __construct(private MenuCategory $model) {}

    public function findByRestaurant(int $restaurantId): Collection
    {
        return $this->model->where('restaurant_id', $restaurantId)
            ->orderBy('sort_order')
            ->get();
    }

    public function findByIdAndRestaurant(int $restaurantId, int $catId): ?MenuCategory
    {
        return $this->model->where('restaurant_id', $restaurantId)->find($catId);
    }

    public function getWithAvailableItems(int $restaurantId): Collection
    {
        return $this->model->where('restaurant_id', $restaurantId)
            ->with(['items' => fn ($q) => $q->available()])
            ->get();
    }

    public function create(array $data): MenuCategory
    {
        return $this->model->create($data);
    }

    public function update(MenuCategory $category, array $data): MenuCategory
    {
        $category->update($data);
        return $category->fresh();
    }

    public function delete(MenuCategory $category): bool
    {
        return $category->delete();
    }
}
