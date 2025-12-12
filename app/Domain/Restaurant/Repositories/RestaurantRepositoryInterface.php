<?php

namespace App\Domain\Restaurant\Repositories;

use App\Domain\Restaurant\Models\Restaurant;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Support\Collection;

interface RestaurantRepositoryInterface
{
    public function findById(int $id): ?Restaurant;
    public function findBySlug(string $slug): ?Restaurant;
    public function findActiveById(int $id): ?Restaurant;
    public function paginate(array $filters = [], int $perPage = 20, int $page = 1): LengthAwarePaginator;
    public function paginateForAdmin(array $filters = [], int $perPage = 20): LengthAwarePaginator;
    public function paginatePending(int $perPage = 20): LengthAwarePaginator;
    public function searchRestaurants(array $filters = [], int $perPage = 20): LengthAwarePaginator;
    public function findWithFullDetails(int $id): ?Restaurant;
    public function topByRevenue(int $limit = 10): Collection;
    public function allNamesList(): Collection;
    public function create(array $data): Restaurant;
    public function update(int $id, array $data): Restaurant;
    public function delete(int $id): bool;
}
