<?php

namespace App\Infrastructure\Persistence\Eloquent;

use App\Domain\Restaurant\Models\Brand;
use App\Domain\Restaurant\Repositories\BrandRepositoryInterface;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;

class EloquentBrandRepository implements BrandRepositoryInterface
{
    public function __construct(private Brand $model) {}

    public function findById(int $id): ?Brand
    {
        return $this->model->find($id);
    }

    public function findByIdWithRestaurants(int $id): ?Brand
    {
        return $this->model->with('restaurants')->find($id);
    }

    public function findBySlugWithRestaurants(string $slug): ?Brand
    {
        return $this->model->with('restaurants')->where('slug', $slug)->first();
    }

    public function paginate(array $filters = [], int $perPage = 20): LengthAwarePaginator
    {
        $query = $this->model->newQuery();

        if (! empty($filters['search'])) {
            $query->where('name', 'like', '%' . $filters['search'] . '%');
        }

        if (empty($filters['include_inactive'])) {
            $query->where('is_active', true);
        }

        if (! empty($filters['with_count'])) {
            $query->withCount('restaurants');
        }

        return $query->orderBy('name')->paginate($perPage);
    }

    public function create(array $data): Brand
    {
        return $this->model->create($data);
    }

    public function update(int $id, array $data): Brand
    {
        $brand = $this->model->findOrFail($id);
        $brand->update($data);
        return $brand->fresh();
    }

    public function delete(int $id): bool
    {
        return $this->model->findOrFail($id)->delete();
    }
}
