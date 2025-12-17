<?php

namespace App\Domain\Delivery\Repositories;

use App\Domain\Delivery\Models\Delivery;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;

interface DeliveryRepositoryInterface
{
    public function findById(int $id): ?Delivery;
    public function findByIdWithDetails(int $id): ?Delivery;
    public function paginate(array $filters = [], int $perPage = 20): LengthAwarePaginator;
    public function create(array $data): Delivery;
    public function update(int $id, array $data): Delivery;
}
