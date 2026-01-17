<?php

declare(strict_types=1);

namespace App\Domain\User\Services;

use App\Domain\User\Models\User;
use App\Domain\User\Repositories\UserRepositoryInterface;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;

class ApplicationService
{
    public function __construct(private readonly UserRepositoryInterface $users) {}

    public function paginate(array $filters = [], int $perPage = 20): LengthAwarePaginator
    {
        return $this->users->paginateApplications($filters, $perPage);
    }

    public function approve(int $userId): User
    {
        $user = $this->users->update($userId, [
            'application_status'      => 'approved',
            'application_reviewed_at' => now(),
            'is_active'               => true,
            'is_verified'             => true,
        ]);

        if ($user->application_type === 'restaurant_owner') {
            $user->syncRoles(['restaurant_owner']);
        } elseif ($user->application_type === 'courier') {
            $user->syncRoles(['delivery_driver']);
        }

        return $user;
    }

    public function reject(int $userId, ?string $reason = null): User
    {
        return $this->users->update($userId, [
            'application_status'           => 'rejected',
            'application_reviewed_at'      => now(),
            'application_rejection_reason' => $reason,
        ]);
    }
}
