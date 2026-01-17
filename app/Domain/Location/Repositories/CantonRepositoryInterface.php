<?php

namespace App\Domain\Location\Repositories;

use App\Domain\Location\Models\Canton;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Support\Collection;

interface CantonRepositoryInterface
{
    public function all(): Collection;
    public function findById(int $id): ?Canton;
    public function findByCode(string $code): ?Canton;
    public function paginate(array $filters = [], int $perPage = 26): LengthAwarePaginator;
    public function create(array $data): Canton;
    public function update(int $id, array $data): Canton;
    public function delete(int $id): bool;
    public function findByIdWithCityCount(int $id): ?Canton;
}
