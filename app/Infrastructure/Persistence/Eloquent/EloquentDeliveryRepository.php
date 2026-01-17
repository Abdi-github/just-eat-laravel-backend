<?php

namespace App\Infrastructure\Persistence\Eloquent;

use App\Domain\Delivery\Models\Delivery;
use App\Domain\Delivery\Repositories\DeliveryRepositoryInterface;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;

class EloquentDeliveryRepository implements DeliveryRepositoryInterface
{
    public function __construct(private Delivery $model) {}

    public function findById(int $id): ?Delivery
    {
        return $this->model->find($id);
    }

    public function findByIdWithDetails(int $id): ?Delivery
    {
        return $this->model->with(['order.user', 'restaurant', 'courier'])->find($id);
    }

    public function paginate(array $filters = [], int $perPage = 20): LengthAwarePaginator
    {
        $query = $this->model->with(['order', 'restaurant', 'courier']);

        if (! empty($filters['status'])) {
            $query->where('status', $filters['status']);
        }

        if (! empty($filters['search'])) {
            $query->whereHas('order', fn ($q) => $q->where('order_number', 'like', "%{$filters['search']}%"));
        }

        return $query->orderByDesc('created_at')->paginate($perPage);
    }

    public function create(array $data): Delivery
    {
        return $this->model->create($data);
    }

    public function update(int $id, array $data): Delivery
    {
        $delivery = $this->model->findOrFail($id);
        $delivery->update($data);
        return $delivery->fresh();
    }
}
