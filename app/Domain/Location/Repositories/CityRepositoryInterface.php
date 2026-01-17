<?php

namespace App\Domain\Location\Repositories;

use App\Domain\Location\Models\City;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Support\Collection;

interface CityRepositoryInterface
{
    public function all(array $filters = []): Collection;
    public function findById(int $id): ?City;
    public function paginate(array $filters = [], int $perPage = 20): LengthAwarePaginator;
    public function create(array $data): City;
    public function update(int $id, array $data): City;
    public function delete(int $id): bool;
    public function findByIdWithAddressCount(int $id): ?City;
}
