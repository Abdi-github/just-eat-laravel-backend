<?php

namespace App\Domain\Order\Repositories;

use App\Domain\Order\Models\Order;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Support\Collection;

interface OrderRepositoryInterface
{
    public function findById(int $id): ?Order;
    public function findByIdWithReview(int $id): ?Order;
    public function findByOrderNumber(string $orderNumber): ?Order;
    public function paginateForUser(int $userId, int $perPage = 20): LengthAwarePaginator;
    public function paginateAll(array $filters = [], int $perPage = 20): LengthAwarePaginator;
    public function create(array $data): Order;
    public function update(int $id, array $data): Order;
    public function updateStatus(int $id, string $status): Order;
    public function delete(int $id): bool;
    public function getStatsForOwner(int $userId): array;
    public function getRevenueByPeriod(array $filters = []): Collection;
}
