<?php

namespace App\Domain\Cuisine\Repositories;

use App\Domain\Cuisine\Models\Cuisine;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Support\Collection;

interface CuisineRepositoryInterface
{
    public function all(): Collection;
    public function findById(int $id): ?Cuisine;
    public function findByIdWithRestaurantCount(int $id): ?Cuisine;
    public function paginate(array $filters = [], int $perPage = 20): LengthAwarePaginator;
    public function create(array $data): Cuisine;
    public function update(int $id, array $data): Cuisine;
    public function delete(int $id): bool;
}
