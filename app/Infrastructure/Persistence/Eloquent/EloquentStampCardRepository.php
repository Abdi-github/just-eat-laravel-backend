<?php

namespace App\Infrastructure\Persistence\Eloquent;

use App\Domain\Promotion\Models\StampCard;
use App\Domain\Promotion\Repositories\StampCardRepositoryInterface;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;

class EloquentStampCardRepository implements StampCardRepositoryInterface
{
    public function __construct(private StampCard $model) {}

    public function findById(int $id): ?StampCard
    {
        return $this->model->find($id);
    }

    public function findByIdWithRestaurant(int $id): ?StampCard
    {
        return $this->model->with('restaurant')->find($id);
    }

    public function paginate(array $filters = [], int $perPage = 20): LengthAwarePaginator
    {
        $query = $this->model->with('restaurant');

        if (! empty($filters['search'])) {
            $query->where('name', 'like', '%' . $filters['search'] . '%');
        }

        if (! empty($filters['restaurant_id'])) {
            $query->where('restaurant_id', $filters['restaurant_id']);
        }

        if (isset($filters['active'])) {
            $query->where('is_active', $filters['active']);
        }

        return $query->orderByDesc('created_at')->paginate($perPage);
    }

    public function create(array $data): StampCard
    {
        return $this->model->create($data);
    }

    public function update(int $id, array $data): StampCard
    {
        $stampCard = $this->model->findOrFail($id);
        $stampCard->update($data);
        return $stampCard->fresh();
    }

    public function delete(int $id): bool
    {
        return $this->model->findOrFail($id)->delete();
    }
}
