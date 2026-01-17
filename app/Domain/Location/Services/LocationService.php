<?php

declare(strict_types=1);

namespace App\Domain\Location\Services;

use App\Domain\Location\Models\Canton;
use App\Domain\Location\Models\City;
use App\Domain\Location\Repositories\CantonRepositoryInterface;
use App\Domain\Location\Repositories\CityRepositoryInterface;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Support\Collection;

class LocationService
{
    public function __construct(
        private readonly CantonRepositoryInterface $cantons,
        private readonly CityRepositoryInterface $cities,
    ) {}

    public function getAllCantons(): Collection
    {
        return $this->cantons->all();
    }

    public function getAllCities(array $filters = []): Collection
    {
        return $this->cities->all($filters);
    }

    // Admin methods

    public function paginateCantons(array $filters = [], int $perPage = 26): LengthAwarePaginator
    {
        return $this->cantons->paginate($filters, $perPage);
    }

    public function paginateCities(array $filters = [], int $perPage = 20): LengthAwarePaginator
    {
        return $this->cities->paginate($filters, $perPage);
    }

    public function createCanton(array $data): Canton
    {
        if (isset($data['code'])) {
            $data['code'] = strtoupper($data['code']);
        }

        return $this->cantons->create($data);
    }

    public function updateCanton(int $id, array $data): Canton
    {
        if (isset($data['code'])) {
            $data['code'] = strtoupper($data['code']);
        }

        return $this->cantons->update($id, $data);
    }

    public function deleteCanton(int $id): bool|string
    {
        $canton = $this->cantons->findByIdWithCityCount($id);

        if (! $canton) {
            return 'Canton not found.';
        }

        if ($canton->cities_count > 0) {
            return 'Cannot delete canton with cities assigned.';
        }

        return $this->cantons->delete($id);
    }

    public function createCity(array $data): City
    {
        return $this->cities->create($data);
    }

    public function findCityById(int $id): ?City
    {
        return $this->cities->findById($id);
    }

    public function updateCity(int $id, array $data): City
    {
        return $this->cities->update($id, $data);
    }

    public function deleteCity(int $id): bool|string
    {
        $city = $this->cities->findByIdWithAddressCount($id);

        if (! $city) {
            return 'City not found.';
        }

        if ($city->addresses_count > 0) {
            return 'Cannot delete city that has addresses linked.';
        }

        return $this->cities->delete($id);
    }
}
