<?php

namespace App\Domain\Admin\Services;

use App\Domain\Admin\Repositories\AnalyticsRepositoryInterface;
use Illuminate\Support\Carbon;
use Illuminate\Support\Collection;

class AnalyticsService
{
    public function __construct(private readonly AnalyticsRepositoryInterface $analytics) {}

    public function getDateRange(string $preset): array
    {
        $startDate = match ($preset) {
            'today'        => now()->startOfDay(),
            'last_7_days'  => now()->subDays(7)->startOfDay(),
            'last_30_days' => now()->subDays(30)->startOfDay(),
            'last_90_days' => now()->subDays(90)->startOfDay(),
            'this_year'    => now()->startOfYear(),
            default        => now()->subDays(30)->startOfDay(),
        };

        return [$startDate, now()->endOfDay()];
    }

    public function getStats(Carbon $start, Carbon $end): array
    {
        return [
            'totalRevenue'  => $this->analytics->getRevenue($start, $end),
            'totalOrders'   => $this->analytics->getOrderCount($start, $end),
            'avgOrderValue' => $this->analytics->getAvgOrderValue($start, $end),
            'newUsers'      => $this->analytics->getNewUserCount($start, $end),
        ];
    }

    public function getRevenueTimeSeries(Carbon $start, Carbon $end, string $period = 'daily'): Collection
    {
        $format = $period === 'monthly' ? '%Y-%m' : '%Y-%m-%d';

        return $this->analytics->getRevenueTimeSeries($start, $end, $format);
    }

    public function getOrdersByStatus(Carbon $start, Carbon $end): Collection
    {
        return $this->analytics->getOrdersByStatus($start, $end);
    }

    public function getTopRestaurants(Carbon $start, Carbon $end, int $limit = 10): Collection
    {
        return $this->analytics->getTopRestaurants($start, $end, $limit);
    }
}
