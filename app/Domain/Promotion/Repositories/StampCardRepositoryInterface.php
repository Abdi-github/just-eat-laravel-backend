<?php

namespace App\Domain\Promotion\Repositories;

use App\Domain\Promotion\Models\StampCard;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;

interface StampCardRepositoryInterface
{
    public function findById(int $id): ?StampCard;
    public function findByIdWithRestaurant(int $id): ?StampCard;
    public function paginate(array $filters = [], int $perPage = 20): LengthAwarePaginator;
    public function create(array $data): StampCard;
    public function update(int $id, array $data): StampCard;
    public function delete(int $id): bool;
}
