<?php

namespace App\Infrastructure\Persistence\Eloquent;

use App\Domain\Location\Models\City;
use App\Domain\Location\Repositories\CityRepositoryInterface;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Support\Collection;

class EloquentCityRepository implements CityRepositoryInterface
{
    public function __construct(private City $model) {}

    public function all(array $filters = []): Collection
    {
        $query = $this->model->with('canton');

        if (!empty($filters['canton_id'])) {
            $query->where('canton_id', $filters['canton_id']);
        }

        if (!empty($filters['search'])) {
            $query->where(function ($q) use ($filters) {
                $q->where('name', 'like', "%{$filters['search']}%")
                  ->orWhere('zip_code', 'like', "%{$filters['search']}%");
            });
        }

        return $query->orderBy('name')->get();
    }

    public function findById(int $id): ?City
    {
        return $this->model->with('canton')->find($id);
    }

    public function paginate(array $filters = [], int $perPage = 20): LengthAwarePaginator
    {
        $query = $this->model->with('canton');

        if (! empty($filters['search'])) {
            $search = $filters['search'];
            $query->where(function ($q) use ($search) {
                $q->where('name', 'like', '%' . $search . '%')
                  ->orWhere('zip_code', 'like', '%' . $search . '%');
            });
        }

        if (! empty($filters['canton_id'])) {
            $query->where('canton_id', $filters['canton_id']);
        }

        return $query->orderBy('name')->paginate($perPage);
    }

    public function create(array $data): City
    {
        return $this->model->create($data);
    }

    public function update(int $id, array $data): City
    {
        $city = $this->model->findOrFail($id);
        $city->update($data);
        return $city->fresh();
    }

    public function delete(int $id): bool
    {
        return $this->model->findOrFail($id)->delete();
    }

    public function findByIdWithAddressCount(int $id): ?City
    {
        return $this->model->withCount('addresses')->find($id);
    }
}
