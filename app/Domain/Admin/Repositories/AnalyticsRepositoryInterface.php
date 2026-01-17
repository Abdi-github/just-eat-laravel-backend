<?php

namespace App\Domain\Admin\Repositories;

use Illuminate\Support\Carbon;
use Illuminate\Support\Collection;

interface AnalyticsRepositoryInterface
{
    public function getRevenue(Carbon $start, Carbon $end): float;
    public function getOrderCount(Carbon $start, Carbon $end): int;
    public function getAvgOrderValue(Carbon $start, Carbon $end): float;
    public function getNewUserCount(Carbon $start, Carbon $end): int;
    public function getRevenueTimeSeries(Carbon $start, Carbon $end, string $format): Collection;
    public function getOrdersByStatus(Carbon $start, Carbon $end): Collection;
    public function getTopRestaurants(Carbon $start, Carbon $end, int $limit = 10): Collection;
}
