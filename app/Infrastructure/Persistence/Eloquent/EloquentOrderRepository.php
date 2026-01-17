<?php

namespace App\Infrastructure\Persistence\Eloquent;

use App\Domain\Order\Models\Order;
use App\Domain\Order\Repositories\OrderRepositoryInterface;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Support\Collection;

class EloquentOrderRepository implements OrderRepositoryInterface
{
    public function __construct(private Order $model) {}

    public function findById(int $id): ?Order
    {
        return $this->model->with(['user', 'restaurant'])->find($id);
    }

    public function findByIdWithReview(int $id): ?Order
    {
        return $this->model->with(['user', 'restaurant', 'review'])->find($id);
    }

    public function findByOrderNumber(string $orderNumber): ?Order
    {
        return $this->model->with(['user', 'restaurant'])->where('order_number', $orderNumber)->first();
    }

    public function paginateForUser(int $userId, int $perPage = 20): LengthAwarePaginator
    {
        return $this->model->with(['restaurant'])
            ->where('user_id', $userId)
            ->orderByDesc('created_at')
            ->paginate($perPage);
    }

    public function paginateAll(array $filters = [], int $perPage = 20): LengthAwarePaginator
    {
        $query = $this->model->with(['user', 'restaurant']);

        if (!empty($filters['search'])) {
            $query->where('order_number', 'like', "%{$filters['search']}%");
        }

        if (!empty($filters['status'])) {
            $query->where('status', $filters['status']);
        }

        if (!empty($filters['restaurant_id'])) {
            $query->where('restaurant_id', $filters['restaurant_id']);
        }

        if (!empty($filters['user_id'])) {
            $query->where('user_id', $filters['user_id']);
        }

        return $query->orderByDesc('created_at')->paginate($perPage);
    }

    public function create(array $data): Order
    {
        return $this->model->create($data);
    }

    public function update(int $id, array $data): Order
    {
        $order = $this->model->findOrFail($id);
        $order->update($data);
        return $order->fresh(['user', 'restaurant']);
    }

    public function updateStatus(int $id, string $status): Order
    {
        $order = $this->model->findOrFail($id);
        $order->update(['status' => $status]);
        return $order->fresh(['user', 'restaurant']);
    }

    public function delete(int $id): bool
    {
        return $this->model->findOrFail($id)->delete();
    }

    public function getStatsForOwner(int $userId): array
    {
        $baseQuery = $this->model->whereHas('restaurant', fn ($q) => $q->where('user_id', $userId));

        return [
            'total_orders'   => (clone $baseQuery)->count(),
            'total_revenue'  => (float) (clone $baseQuery)->where('payment_status', 'paid')->sum('total'),
            'pending_orders' => (clone $baseQuery)->where('status', 'pending')->count(),
        ];
    }

    public function getRevenueByPeriod(array $filters = []): Collection
    {
        $query = $this->model->where('payment_status', 'paid')
            ->selectRaw('SUM(total) as revenue, COUNT(*) as order_count');

        if (! empty($filters['owner_id'])) {
            $query->whereHas('restaurant', fn ($q) => $q->where('user_id', $filters['owner_id']));
        }

        $period = $filters['period'] ?? 'monthly';

        return match ($period) {
            'daily' => $query->selectRaw("DATE(created_at) as period")
                ->groupByRaw("DATE(created_at)")->orderBy('period')->get(),

            'weekly' => $query->selectRaw("YEARWEEK(created_at, 1) as period")
                ->groupByRaw("YEARWEEK(created_at, 1)")->orderBy('period')->get(),

            default => $query->selectRaw("DATE_FORMAT(created_at, '%Y-%m') as period")
                ->groupByRaw("DATE_FORMAT(created_at, '%Y-%m')")->orderBy('period')->get(),
        };
    }
}
