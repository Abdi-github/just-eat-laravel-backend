<?php

namespace App\Domain\Order\Services;

use App\Domain\Order\Models\Order;
use App\Domain\Order\Repositories\OrderRepositoryInterface;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Support\Str;

class OrderService
{
    public function __construct(private readonly OrderRepositoryInterface $orders) {}

    public function paginateForUser(int $userId, int $perPage = 20): LengthAwarePaginator
    {
        return $this->orders->paginateForUser($userId, $perPage);
    }

    public function paginateAll(array $filters = [], int $perPage = 20): LengthAwarePaginator
    {
        return $this->orders->paginateAll($filters, $perPage);
    }

    public function findById(int $id): ?Order
    {
        return $this->orders->findById($id);
    }

    public function findByIdWithReview(int $id): ?Order
    {
        return $this->orders->findByIdWithReview($id);
    }

    public function create(array $data): Order
    {
        $data['order_number']   = strtoupper(Str::random(10));
        $data['status']         = 'pending';
        $data['payment_status'] = 'pending';

        return $this->orders->create($data);
    }

    public function updateStatus(int $id, string $status): Order
    {
        return $this->orders->updateStatus($id, $status);
    }

    public function delete(int $id): bool
    {
        return $this->orders->delete($id);
    }
}
