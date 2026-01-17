<?php

namespace App\Domain\Notification\Services;

use App\Domain\Notification\Models\Notification;
use App\Domain\Notification\Repositories\NotificationRepositoryInterface;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Support\Carbon;

class NotificationService
{
    public function __construct(private readonly NotificationRepositoryInterface $notifications) {}

    public function getUserNotifications(int $userId, array $filters = []): LengthAwarePaginator
    {
        $perPage = (int) ($filters['limit'] ?? 20);

        return $this->notifications->paginateForUser($userId, $filters, $perPage);
    }

    public function getCount(int $userId): array
    {
        return [
            'total'  => $this->notifications->countForUser($userId),
            'unread' => $this->notifications->countUnreadForUser($userId),
        ];
    }

    public function findByIdForUser(int $id, int $userId): ?Notification
    {
        return $this->notifications->findByIdForUser($id, $userId);
    }

    public function markAsRead(int $id, int $userId): ?Notification
    {
        $notification = $this->notifications->findByIdForUser($id, $userId);

        if (! $notification || $notification->is_read) {
            return $notification;
        }

        return $this->notifications->update($notification, [
            'is_read' => true,
            'read_at' => Carbon::now(),
        ]);
    }

    public function markAllAsRead(int $userId): array
    {
        $updated = $this->notifications->markAllAsReadForUser($userId);

        return ['updated' => $updated];
    }

    public function delete(int $id, int $userId): bool
    {
        return $this->notifications->deleteForUser($id, $userId);
    }

    public function deleteAll(int $userId): array
    {
        $deleted = $this->notifications->deleteAllForUser($userId);

        return ['deleted' => $deleted];
    }

    public function create(array $data): Notification
    {
        return $this->notifications->create($data);
    }

    public function sendToMultiple(array $userIds, array $payload): array
    {
        $count = 0;

        foreach ($userIds as $userId) {
            $this->notifications->create([
                'user_id'  => $userId,
                'type'     => $payload['type'],
                'title'    => $payload['title'],
                'body'     => $payload['body'],
                'data'     => $payload['data'] ?? null,
                'channel'  => $payload['channel'] ?? 'BOTH',
                'priority' => $payload['priority'] ?? 'NORMAL',
            ]);
            $count++;
        }

        return ['sent' => $count];
    }
}
