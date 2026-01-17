<?php

namespace App\Infrastructure\Persistence\Eloquent;

use App\Domain\Notification\Models\Notification;
use App\Domain\Notification\Repositories\NotificationRepositoryInterface;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;

class EloquentNotificationRepository implements NotificationRepositoryInterface
{
    public function __construct(private Notification $model) {}

    public function paginateForUser(int $userId, array $filters = [], int $perPage = 20): LengthAwarePaginator
    {
        $query = $this->model->forUser($userId)->orderByDesc('created_at');

        if (isset($filters['type'])) {
            $query->where('type', $filters['type']);
        }

        if (isset($filters['is_read'])) {
            $query->where('is_read', filter_var($filters['is_read'], FILTER_VALIDATE_BOOLEAN));
        }

        return $query->paginate($perPage);
    }

    public function countForUser(int $userId): int
    {
        return $this->model->forUser($userId)->count();
    }

    public function countUnreadForUser(int $userId): int
    {
        return $this->model->forUser($userId)->unread()->count();
    }

    public function findByIdForUser(int $id, int $userId): ?Notification
    {
        return $this->model->forUser($userId)->find($id);
    }

    public function markAllAsReadForUser(int $userId): int
    {
        return $this->model->forUser($userId)
            ->unread()
            ->update(['is_read' => true, 'read_at' => now()]);
    }

    public function deleteForUser(int $id, int $userId): bool
    {
        return (bool) $this->model->forUser($userId)->where('id', $id)->delete();
    }

    public function deleteAllForUser(int $userId): int
    {
        return $this->model->forUser($userId)->delete();
    }

    public function create(array $data): Notification
    {
        return $this->model->create($data);
    }

    public function update(Notification $notification, array $data): Notification
    {
        $notification->update($data);
        return $notification->fresh();
    }
}
