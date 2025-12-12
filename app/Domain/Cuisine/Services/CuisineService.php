<?php

declare(strict_types=1);

namespace App\Domain\Cuisine\Services;

use App\Domain\Cuisine\Models\Cuisine;
use App\Domain\Cuisine\Repositories\CuisineRepositoryInterface;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Support\Collection;

class CuisineService
{
    public function __construct(private readonly CuisineRepositoryInterface $cuisines) {}

    public function all(): Collection
    {
        return $this->cuisines->all();
    }

    public function paginate(array $filters = [], int $perPage = 20): LengthAwarePaginator
    {
        return $this->cuisines->paginate($filters, $perPage);
    }

    public function findById(int $id): ?Cuisine
    {
        return $this->cuisines->findById($id);
    }

    public function findByIdWithRestaurantCount(int $id): ?Cuisine
    {
        return $this->cuisines->findByIdWithRestaurantCount($id);
    }

    public function create(array $data): Cuisine
    {
        return $this->cuisines->create($data);
    }

    public function update(int $id, array $data): Cuisine
    {
        return $this->cuisines->update($id, $data);
    }

    public function delete(int $id): bool
    {
        return $this->cuisines->delete($id);
    }
}
