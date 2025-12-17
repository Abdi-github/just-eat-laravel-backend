<?php

declare(strict_types=1);

namespace App\Domain\Delivery\Services;

use App\Domain\Delivery\Models\Delivery;
use App\Domain\Delivery\Repositories\DeliveryRepositoryInterface;
use App\Domain\Order\Repositories\OrderRepositoryInterface;
use App\Domain\User\Repositories\UserRepositoryInterface;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Support\Collection;

class DeliveryService
{
    public function __construct(
        private readonly DeliveryRepositoryInterface $deliveries,
        private readonly OrderRepositoryInterface $orders,
        private readonly UserRepositoryInterface $users,
    ) {}

    public function paginate(array $filters = [], int $perPage = 20): LengthAwarePaginator
    {
        return $this->deliveries->paginate($filters, $perPage);
    }

    public function findByIdWithDetails(int $id): ?Delivery
    {
        return $this->deliveries->findByIdWithDetails($id);
    }

    public function getActiveCouriers(): Collection
    {
        return $this->users->getActiveCouriers();
    }

    public function createForOrder(array $data): Delivery
    {
        $order = $this->orders->findById($data['order_id']);

        if (! $order) {
            throw new \RuntimeException('Order not found.');
        }

        return $this->deliveries->create([
            'order_id'      => $order->id,
            'restaurant_id' => $order->restaurant_id,
            'delivery_fee'  => $data['delivery_fee'] ?? $order->delivery_fee,
            'notes'         => $data['notes'] ?? null,
            'status'        => 'PENDING',
        ]);
    }

    public function assignCourier(int $id, int $courierId): Delivery
    {
        return $this->deliveries->update($id, [
            'courier_id'  => $courierId,
            'status'      => 'ASSIGNED',
            'assigned_at' => now(),
        ]);
    }

    public function updateStatus(int $id, string $status, ?string $reason = null): Delivery
    {
        $timestamps = [
            'PICKED_UP'  => 'picked_up_at',
            'IN_TRANSIT' => 'in_transit_at',
            'DELIVERED'  => 'delivered_at',
            'CANCELLED'  => 'cancelled_at',
        ];

        $update = ['status' => $status];

        if (isset($timestamps[$status])) {
            $update[$timestamps[$status]] = now();
        }

        if ($status === 'CANCELLED' && $reason) {
            $update['cancellation_reason'] = $reason;
        }

        return $this->deliveries->update($id, $update);
    }
}
