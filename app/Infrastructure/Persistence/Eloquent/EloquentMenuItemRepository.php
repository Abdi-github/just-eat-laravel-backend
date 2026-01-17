<?php

namespace App\Infrastructure\Persistence\Eloquent;

use App\Domain\Menu\Models\MenuItem;
use App\Domain\Menu\Repositories\MenuItemRepositoryInterface;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Support\Collection;

class EloquentMenuItemRepository implements MenuItemRepositoryInterface
{
    public function __construct(private MenuItem $model) {}

    public function findByRestaurant(int $restaurantId): Collection
    {
        return $this->model->where('restaurant_id', $restaurantId)
            ->with('category')
            ->orderBy('menu_category_id')
            ->get();
    }

    public function findByIdAndRestaurant(int $restaurantId, int $itemId): ?MenuItem
    {
        return $this->model->where('restaurant_id', $restaurantId)->find($itemId);
    }

    public function create(array $data): MenuItem
    {
        return $this->model->create($data);
    }

    public function update(MenuItem $item, array $data): MenuItem
    {
        $item->update($data);
        return $item->fresh('category');
    }

    public function delete(MenuItem $item): bool
    {
        return $item->delete();
    }

    public function searchByRestaurant(int $restaurantId, array $filters = [], int $perPage = 15): LengthAwarePaginator
    {
        $query = $this->model->where('restaurant_id', $restaurantId)->with('category');

        if (!empty($filters['q'])) {
            $search = $filters['q'];
            $query->where(function ($q) use ($search) {
                $q->whereRaw("JSON_UNQUOTE(JSON_EXTRACT(name, '$.fr')) LIKE ?", ["%{$search}%"])
                    ->orWhereRaw("JSON_UNQUOTE(JSON_EXTRACT(name, '$.de')) LIKE ?", ["%{$search}%"])
                    ->orWhereRaw("JSON_UNQUOTE(JSON_EXTRACT(name, '$.en')) LIKE ?", ["%{$search}%"]);
            });
        }

        if (!empty($filters['category_id'])) {
            $query->where('menu_category_id', $filters['category_id']);
        }

        if (isset($filters['is_available'])) {
            $query->where('is_available', $filters['is_available']);
        }

        return $query->orderBy('menu_category_id')->paginate($perPage);
    }
}
