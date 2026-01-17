<?php

namespace App\Domain\User\Repositories;

use App\Domain\User\Models\User;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Support\Collection;

interface UserRepositoryInterface
{
    public function findById(int $id): ?User;
    public function findByIdWithRelations(int $id, array $relations = []): ?User;
    public function findByEmail(string $email): ?User;
    public function paginate(array $filters = [], int $perPage = 20): LengthAwarePaginator;
    public function paginateWithCounts(array $filters = [], int $perPage = 20): LengthAwarePaginator;
    public function paginateApplications(array $filters = [], int $perPage = 20): LengthAwarePaginator;
    public function getActiveUserIds(): array;
    public function getActiveCouriers(): Collection;
    public function create(array $data): User;
    public function update(int $id, array $data): User;
    public function delete(int $id): bool;
}
