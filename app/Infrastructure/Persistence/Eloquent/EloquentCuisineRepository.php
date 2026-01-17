<?php

namespace App\Infrastructure\Persistence\Eloquent;

use App\Domain\Cuisine\Models\Cuisine;
use App\Domain\Cuisine\Repositories\CuisineRepositoryInterface;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Support\Collection;

class EloquentCuisineRepository implements CuisineRepositoryInterface
{
    public function __construct(private Cuisine $model) {}

    public function all(): Collection
    {
        return $this->model->active()->orderBy('sort_order')->get();
    }

    public function findById(int $id): ?Cuisine
    {
        return $this->model->find($id);
    }

    public function findByIdWithRestaurantCount(int $id): ?Cuisine
    {
        return $this->model->withCount('restaurants')->find($id);
    }

    public function paginate(array $filters = [], int $perPage = 20): LengthAwarePaginator
    {
        $query = $this->model->withCount('restaurants');

        if (! empty($filters['search'])) {
            $search = strtolower($filters['search']);
            $query->whereRaw(
                "JSON_SEARCH(LOWER(name), 'one', ?) IS NOT NULL",
                ['%' . $search . '%']
            );
        }

        return $query->orderBy('sort_order')->paginate($perPage);
    }

    public function create(array $data): Cuisine
    {
        return $this->model->create($data);
    }

    public function update(int $id, array $data): Cuisine
    {
        $cuisine = $this->model->findOrFail($id);
        $cuisine->update($data);
        return $cuisine->fresh();
    }

    public function delete(int $id): bool
    {
        return $this->model->findOrFail($id)->delete();
    }
}
