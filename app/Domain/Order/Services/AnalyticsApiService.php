<?php

declare(strict_types=1);

namespace App\Domain\Order\Services;

use App\Domain\Order\Repositories\OrderRepositoryInterface;
use App\Domain\Restaurant\Repositories\RestaurantRepositoryInterface;
use App\Domain\User\Repositories\UserRepositoryInterface;
use Illuminate\Support\Collection;

class AnalyticsApiService
{
    public function __construct(
        private readonly OrderRepositoryInterface $orders,
        private readonly RestaurantRepositoryInterface $restaurants,
        private readonly UserRepositoryInterface $users,
    ) {}

    public function getDashboardStats(bool $isAdmin, ?int $ownerUserId = null): array
    {
        $stats = $this->orders->getStatsForOwner($isAdmin ? null : $ownerUserId);

        if ($isAdmin) {
            $stats['total_users'] = $this->users->paginate([], 1)->total();
        } else {
            $stats['total_users'] = null;
        }

        return $stats;
    }

    public function getRevenueByPeriod(string $period, bool $isAdmin, ?int $ownerUserId = null): Collection
    {
        $filters = ['period' => $period];

        if (! $isAdmin && $ownerUserId) {
            $filters['owner_id'] = $ownerUserId;
        }

        return $this->orders->getRevenueByPeriod($filters);
    }

    public function getTopRestaurants(int $limit = 10): Collection
    {
        return $this->restaurants->topByRevenue($limit);
    }
}
