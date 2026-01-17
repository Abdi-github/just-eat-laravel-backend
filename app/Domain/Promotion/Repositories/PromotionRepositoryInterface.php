<?php

namespace App\Domain\Promotion\Repositories;

use App\Domain\Promotion\Models\Promotion;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;

interface PromotionRepositoryInterface
{
    public function findById(int $id): ?Promotion;
    public function findByIdWithRestaurant(int $id): ?Promotion;
    public function findActiveByCode(string $code): ?Promotion;
    public function paginate(array $filters = [], int $perPage = 20): LengthAwarePaginator;
    public function create(array $data): Promotion;
    public function update(int $id, array $data): Promotion;
    public function delete(int $id): bool;
}
