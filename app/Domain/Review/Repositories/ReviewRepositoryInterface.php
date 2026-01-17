<?php

namespace App\Domain\Review\Repositories;

use App\Domain\Review\Models\Review;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;

interface ReviewRepositoryInterface
{
    public function findById(int $id): ?Review;
    public function findByIdWithDetails(int $id): ?Review;
    public function paginateByRestaurant(int $restaurantId, int $perPage = 20): LengthAwarePaginator;
    public function paginateAll(array $filters = [], int $perPage = 20): LengthAwarePaginator;
    public function create(array $data): Review;
    public function update(int $id, array $data): Review;
    public function delete(int $id): bool;
}
