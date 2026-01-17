<?php

namespace App\Infrastructure\Persistence\Eloquent;

use App\Domain\Location\Models\Canton;
use App\Domain\Location\Repositories\CantonRepositoryInterface;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Support\Collection;

class EloquentCantonRepository implements CantonRepositoryInterface
{
    public function __construct(private Canton $model) {}

    public function all(): Collection
    {
        return $this->model->orderBy('code')->get();
    }

    public function findById(int $id): ?Canton
    {
        return $this->model->find($id);
    }

    public function findByCode(string $code): ?Canton
    {
        return $this->model->where('code', $code)->first();
    }

    public function paginate(array $filters = [], int $perPage = 26): LengthAwarePaginator
    {
        $query = $this->model->withCount('cities');

        if (! empty($filters['search'])) {
            $search = strtolower($filters['search']);
            $query->where(function ($q) use ($search) {
                $q->where('code', 'like', '%' . $search . '%')
                  ->orWhereRaw("JSON_SEARCH(LOWER(name), 'one', ?) IS NOT NULL", ['%' . $search . '%']);
            });
        }

        return $query->orderBy('code')->paginate($perPage);
    }

    public function create(array $data): Canton
    {
        return $this->model->create($data);
    }

    public function update(int $id, array $data): Canton
    {
        $canton = $this->model->findOrFail($id);
        $canton->update($data);
        return $canton->fresh();
    }

    public function delete(int $id): bool
    {
        return $this->model->findOrFail($id)->delete();
    }

    public function findByIdWithCityCount(int $id): ?Canton
    {
        return $this->model->withCount('cities')->find($id);
    }
}
