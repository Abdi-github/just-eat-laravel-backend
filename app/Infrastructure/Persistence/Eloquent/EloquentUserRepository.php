<?php

namespace App\Infrastructure\Persistence\Eloquent;

use App\Domain\User\Models\User;
use App\Domain\User\Repositories\UserRepositoryInterface;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Support\Collection;

class EloquentUserRepository implements UserRepositoryInterface
{
    public function __construct(private User $model) {}

    public function findById(int $id): ?User
    {
        return $this->model->find($id);
    }

    public function findByIdWithRelations(int $id, array $relations = []): ?User
    {
        return $this->model->with($relations)->withCount($relations)->find($id);
    }

    public function findByEmail(string $email): ?User
    {
        return $this->model->where('email', $email)->first();
    }

    public function paginate(array $filters = [], int $perPage = 20): LengthAwarePaginator
    {
        $query = $this->model->newQuery();

        if (!empty($filters['search'])) {
            $query->where(function ($q) use ($filters) {
                $q->where('email', 'like', "%{$filters['search']}%")
                  ->orWhere('first_name', 'like', "%{$filters['search']}%")
                  ->orWhere('last_name', 'like', "%{$filters['search']}%")
                  ->orWhere('username', 'like', "%{$filters['search']}%");
            });
        }

        if (isset($filters['is_active'])) {
            $query->where('is_active', $filters['is_active']);
        }

        return $query->orderByDesc('created_at')->paginate($perPage);
    }

    public function paginateWithCounts(array $filters = [], int $perPage = 20): LengthAwarePaginator
    {
        $query = $this->model->withCount(['orders', 'reviews', 'favorites']);

        if (! empty($filters['search'])) {
            $query->where(function ($q) use ($filters) {
                $q->where('email', 'like', "%{$filters['search']}%")
                  ->orWhere('first_name', 'like', "%{$filters['search']}%")
                  ->orWhere('last_name', 'like', "%{$filters['search']}%");
            });
        }

        if (isset($filters['status'])) {
            $query->where('is_active', $filters['status'] === 'active');
        }

        return $query->orderByDesc('created_at')->paginate($perPage);
    }

    public function paginateApplications(array $filters = [], int $perPage = 20): LengthAwarePaginator
    {
        $query = $this->model
            ->where('application_status', '!=', 'none')
            ->with(['orders'])
            ->select([
                'id', 'first_name', 'last_name', 'email', 'phone', 'avatar',
                'application_status', 'application_type',
                'application_note', 'application_rejection_reason',
                'application_reviewed_at', 'is_verified', 'is_active', 'created_at',
            ]);

        if (! empty($filters['status'])) {
            $query->where('application_status', $filters['status']);
        }

        if (! empty($filters['type'])) {
            $query->where('application_type', $filters['type']);
        }

        if (! empty($filters['search'])) {
            $search = $filters['search'];
            $query->where(function ($q) use ($search) {
                $q->where('email', 'like', "%{$search}%")
                  ->orWhere('first_name', 'like', "%{$search}%")
                  ->orWhere('last_name', 'like', "%{$search}%");
            });
        }

        return $query->orderByDesc('created_at')->paginate($perPage);
    }

    public function getActiveUserIds(): array
    {
        return $this->model->where('is_active', true)->pluck('id')->toArray();
    }

    public function getActiveCouriers(): Collection
    {
        return $this->model->role('delivery_driver')
            ->where('is_active', true)
            ->select('id', 'first_name', 'last_name', 'email')
            ->get();
    }

    public function create(array $data): User
    {
        return $this->model->create($data);
    }

    public function update(int $id, array $data): User
    {
        $user = $this->model->findOrFail($id);
        $user->update($data);
        return $user->fresh();
    }

    public function delete(int $id): bool
    {
        return $this->model->findOrFail($id)->delete();
    }
}
