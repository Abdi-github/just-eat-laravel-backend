<?php

declare(strict_types=1);

namespace App\Domain\Promotion\Services;

use App\Domain\Promotion\Models\StampCard;
use App\Domain\Promotion\Repositories\StampCardRepositoryInterface;
use App\Domain\Restaurant\Repositories\RestaurantRepositoryInterface;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Support\Collection;

class StampCardService
{
    public function __construct(
        private readonly StampCardRepositoryInterface $stampCards,
        private readonly RestaurantRepositoryInterface $restaurants,
    ) {}

    public function paginate(array $filters = [], int $perPage = 20): LengthAwarePaginator
    {
        return $this->stampCards->paginate($filters, $perPage);
    }

    public function findById(int $id): ?StampCard
    {
        return $this->stampCards->findById($id);
    }

    public function findByIdWithRestaurant(int $id): ?StampCard
    {
        return $this->stampCards->findByIdWithRestaurant($id);
    }

    public function create(array $data): StampCard
    {
        return $this->stampCards->create($data);
    }

    public function update(int $id, array $data): StampCard
    {
        return $this->stampCards->update($id, $data);
    }

    public function delete(int $id): bool
    {
        return $this->stampCards->delete($id);
    }

    public function getRestaurantsList(): Collection
    {
        return $this->restaurants->allNamesList();
    }
}
