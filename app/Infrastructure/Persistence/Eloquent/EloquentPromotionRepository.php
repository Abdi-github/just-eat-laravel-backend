<?php

namespace App\Infrastructure\Persistence\Eloquent;

use App\Domain\Promotion\Models\Promotion;
use App\Domain\Promotion\Repositories\PromotionRepositoryInterface;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;

class EloquentPromotionRepository implements PromotionRepositoryInterface
{
    public function __construct(private Promotion $model) {}

    public function findById(int $id): ?Promotion
    {
        return $this->model->find($id);
    }

    public function findByIdWithRestaurant(int $id): ?Promotion
    {
        return $this->model->with('restaurant')->find($id);
    }

    public function findActiveByCode(string $code): ?Promotion
    {
        return $this->model
            ->where('code', strtoupper($code))
            ->where('is_active', true)
            ->where(fn ($q) => $q->whereNull('expires_at')->orWhere('expires_at', '>=', now()))
            ->where(fn ($q) => $q->whereNull('starts_at')->orWhere('starts_at', '<=', now()))
            ->first();
    }

    public function paginate(array $filters = [], int $perPage = 20): LengthAwarePaginator
    {
        $query = $this->model->with('restaurant');

        if (! empty($filters['search'])) {
            $search = $filters['search'];
            $query->where(function ($q) use ($search) {
                $q->where('code', 'like', "%{$search}%")
                  ->orWhere('title', 'like', "%{$search}%");
            });
        }

        if (! empty($filters['restaurant_id'])) {
            $query->where(function ($q) use ($filters) {
                $q->where('restaurant_id', $filters['restaurant_id'])
                  ->orWhereNull('restaurant_id');
            });
        }

        if (! empty($filters['active_only'])) {
            $query->where('is_active', true)
                ->where(fn ($q) => $q->whereNull('expires_at')->orWhere('expires_at', '>=', now()))
                ->where(fn ($q) => $q->whereNull('starts_at')->orWhere('starts_at', '<=', now()));
        }

        return $query->orderByDesc('created_at')->paginate($perPage);
    }

    public function create(array $data): Promotion
    {
        return $this->model->create($data);
    }

    public function update(int $id, array $data): Promotion
    {
        $promotion = $this->model->findOrFail($id);
        $promotion->update($data);
        return $promotion->fresh();
    }

    public function delete(int $id): bool
    {
        return $this->model->findOrFail($id)->delete();
    }
}
