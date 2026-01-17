<?php

namespace App\Infrastructure\Persistence\Eloquent;

use App\Domain\Admin\Repositories\AnalyticsRepositoryInterface;
use Illuminate\Support\Carbon;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;

class EloquentAnalyticsRepository implements AnalyticsRepositoryInterface
{
    public function getRevenue(Carbon $start, Carbon $end): float
    {
        return (float) DB::table('orders')
            ->whereNull('deleted_at')
            ->where('payment_status', 'paid')
            ->whereBetween('created_at', [$start, $end])
            ->sum('total');
    }

    public function getOrderCount(Carbon $start, Carbon $end): int
    {
        return DB::table('orders')
            ->whereNull('deleted_at')
            ->whereBetween('created_at', [$start, $end])
            ->count();
    }

    public function getAvgOrderValue(Carbon $start, Carbon $end): float
    {
        return (float) (DB::table('orders')
            ->whereNull('deleted_at')
            ->where('payment_status', 'paid')
            ->whereBetween('created_at', [$start, $end])
            ->avg('total') ?? 0);
    }

    public function getNewUserCount(Carbon $start, Carbon $end): int
    {
        return DB::table('users')
            ->whereNull('deleted_at')
            ->whereBetween('created_at', [$start, $end])
            ->count();
    }

    public function getRevenueTimeSeries(Carbon $start, Carbon $end, string $format): Collection
    {
        return DB::table('orders')
            ->whereNull('deleted_at')
            ->where('payment_status', 'paid')
            ->whereBetween('created_at', [$start, $end])
            ->select(
                DB::raw("DATE_FORMAT(created_at, '{$format}') as date"),
                DB::raw('SUM(total) as revenue'),
                DB::raw('COUNT(*) as orders'),
            )
            ->groupBy('date')
            ->orderBy('date')
            ->get();
    }

    public function getOrdersByStatus(Carbon $start, Carbon $end): Collection
    {
        return DB::table('orders')
            ->whereNull('deleted_at')
            ->whereBetween('created_at', [$start, $end])
            ->select('status', DB::raw('COUNT(*) as count'))
            ->groupBy('status')
            ->get();
    }

    public function getTopRestaurants(Carbon $start, Carbon $end, int $limit = 10): Collection
    {
        return DB::table('orders')
            ->join('restaurants', 'orders.restaurant_id', '=', 'restaurants.id')
            ->whereNull('orders.deleted_at')
            ->whereNull('restaurants.deleted_at')
            ->where('orders.payment_status', 'paid')
            ->whereBetween('orders.created_at', [$start, $end])
            ->select(
                'restaurants.id',
                'restaurants.name',
                DB::raw('COUNT(orders.id) as total_orders'),
                DB::raw('SUM(orders.total) as total_revenue'),
            )
            ->groupBy('restaurants.id', 'restaurants.name')
            ->orderByDesc('total_revenue')
            ->limit($limit)
            ->get();
    }
}
