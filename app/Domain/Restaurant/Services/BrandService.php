<?php

declare(strict_types=1);

namespace App\Domain\Restaurant\Services;

use App\Domain\Restaurant\Models\Brand;
use App\Domain\Restaurant\Repositories\BrandRepositoryInterface;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;

class BrandService
{
    public function __construct(private readonly BrandRepositoryInterface $brands) {}

    public function paginate(array $filters = [], int $perPage = 20): LengthAwarePaginator
    {
        return $this->brands->paginate($filters, $perPage);
    }

    public function findById(int $id): ?Brand
    {
        return $this->brands->findById($id);
    }

    public function findByIdWithRestaurants(int $id): ?Brand
    {
        return $this->brands->findByIdWithRestaurants($id);
    }

    public function findBySlugWithRestaurants(string $slug): ?Brand
    {
        return $this->brands->findBySlugWithRestaurants($slug);
    }

    public function create(array $data): Brand
    {
        return $this->brands->create($data);
    }

    public function update(int $id, array $data): Brand
    {
        return $this->brands->update($id, $data);
    }

    public function delete(int $id): bool
    {
        return $this->brands->delete($id);
    }
}
