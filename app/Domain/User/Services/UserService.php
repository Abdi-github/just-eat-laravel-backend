<?php

namespace App\Domain\User\Services;

use App\Domain\User\Models\User;
use App\Domain\User\Repositories\UserRepositoryInterface;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;

class UserService
{
    public function __construct(private readonly UserRepositoryInterface $users) {}

    public function paginate(array $filters = [], int $perPage = 20): LengthAwarePaginator
    {
        return $this->users->paginate($filters, $perPage);
    }

    public function findById(int $id): ?User
    {
        return $this->users->findById($id);
    }

    public function update(int $id, array $data): User
    {
        return $this->users->update($id, $data);
    }

    public function delete(int $id): bool
    {
        return $this->users->delete($id);
    }

    // Admin methods

    public function paginateWithCounts(array $filters = [], int $perPage = 20): LengthAwarePaginator
    {
        return $this->users->paginateWithCounts($filters, $perPage);
    }

    public function findByIdWithRelations(int $id, array $relations = []): ?User
    {
        return $this->users->findByIdWithRelations($id, $relations);
    }

    public function getActiveUserIds(): array
    {
        return $this->users->getActiveUserIds();
    }
}
