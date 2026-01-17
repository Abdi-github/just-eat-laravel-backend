<?php

namespace App\Domain\Address\Repositories;

use App\Domain\Address\Models\Address;
use Illuminate\Support\Collection;

interface AddressRepositoryInterface
{
    public function findById(int $id): ?Address;
    public function findByIdForUser(int $id, int $userId): ?Address;
    public function findByUser(int $userId): Collection;
    public function clearDefaultForUser(int $userId): void;
    public function userHasAddresses(int $userId): bool;
    public function create(array $data): Address;
    public function update(int $id, array $data): Address;
    public function delete(int $id): bool;
}
