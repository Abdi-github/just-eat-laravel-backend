<?php

namespace App\Domain\Notification\Repositories;

use App\Domain\Notification\Models\Notification;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;

interface NotificationRepositoryInterface
{
    public function paginateForUser(int $userId, array $filters = [], int $perPage = 20): LengthAwarePaginator;
    public function countForUser(int $userId): int;
    public function countUnreadForUser(int $userId): int;
    public function findByIdForUser(int $id, int $userId): ?Notification;
    public function markAllAsReadForUser(int $userId): int;
    public function deleteForUser(int $id, int $userId): bool;
    public function deleteAllForUser(int $userId): int;
    public function create(array $data): Notification;
    public function update(Notification $notification, array $data): Notification;
}
