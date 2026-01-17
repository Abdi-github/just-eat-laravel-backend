<?php

namespace App\Infrastructure\Persistence\Eloquent;

use App\Domain\Review\Models\Review;
use App\Domain\Review\Repositories\ReviewRepositoryInterface;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;

class EloquentReviewRepository implements ReviewRepositoryInterface
{
    public function __construct(private Review $model) {}

    public function findById(int $id): ?Review
    {
        return $this->model->with(['user', 'restaurant'])->find($id);
    }

    public function findByIdWithDetails(int $id): ?Review
    {
        return $this->model->with(['user', 'restaurant', 'order'])->find($id);
    }

    public function paginateByRestaurant(int $restaurantId, int $perPage = 20): LengthAwarePaginator
    {
        return $this->model->with(['user'])
            ->where('restaurant_id', $restaurantId)
            ->visible()
            ->orderByDesc('created_at')
            ->paginate($perPage);
    }

    public function paginateAll(array $filters = [], int $perPage = 20): LengthAwarePaginator
    {
        $query = $this->model->with(['user', 'restaurant']);

        if (!empty($filters['restaurant_id'])) {
            $query->where('restaurant_id', $filters['restaurant_id']);
        }

        if (isset($filters['is_visible'])) {
            $query->where('is_visible', $filters['is_visible']);
        }

        return $query->orderByDesc('created_at')->paginate($perPage);
    }

    public function create(array $data): Review
    {
        return $this->model->create($data);
    }

    public function update(int $id, array $data): Review
    {
        $review = $this->model->findOrFail($id);
        $review->update($data);
        return $review->fresh();
    }

    public function delete(int $id): bool
    {
        return $this->model->findOrFail($id)->delete();
    }
}
