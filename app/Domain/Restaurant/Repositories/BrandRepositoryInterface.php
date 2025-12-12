<?php

namespace App\Domain\Restaurant\Repositories;

use App\Domain\Restaurant\Models\Brand;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;

interface BrandRepositoryInterface
{
    public function findById(int $id): ?Brand;
    public function findByIdWithRestaurants(int $id): ?Brand;
    public function findBySlugWithRestaurants(string $slug): ?Brand;
    public function paginate(array $filters = [], int $perPage = 20): LengthAwarePaginator;
    public function create(array $data): Brand;
    public function update(int $id, array $data): Brand;
    public function delete(int $id): bool;
}
