<?php

namespace App\Infrastructure\Persistence\Eloquent;

use App\Domain\Admin\Repositories\DashboardRepositoryInterface;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;

class EloquentDashboardRepository implements DashboardRepositoryInterface
{
    public function getTotalRestaurants(): int
    {
        return DB::table('restaurants')->whereNull('deleted_at')->count();
    }

    public function getTotalUsers(): int
    {
        return DB::table('users')->whereNull('deleted_at')->count();
    }

    public function getTotalOrders(): int
    {
        return DB::table('orders')->whereNull('deleted_at')->count();
    }

    public function getTotalRevenue(): float
    {
        return (float) DB::table('orders')
            ->whereNull('deleted_at')
            ->where('payment_status', 'paid')
            ->sum('total');
    }

    public function getRecentOrders(int $limit = 10): Collection
    {
        return DB::table('orders')
            ->join('users', 'orders.user_id', '=', 'users.id')
            ->join('restaurants', 'orders.restaurant_id', '=', 'restaurants.id')
            ->whereNull('orders.deleted_at')
            ->select(
                'orders.id',
                'orders.order_number',
                'orders.status',
                'orders.total',
                'orders.created_at',
                DB::raw("CONCAT(users.first_name, ' ', users.last_name) as customer_name"),
                'restaurants.name as restaurant_name',
            )
            ->orderByDesc('orders.created_at')
            ->limit($limit)
            ->get();
    }

    public function getOrdersByStatus(): Collection
    {
        return DB::table('orders')
            ->whereNull('deleted_at')
            ->select('status', DB::raw('COUNT(*) as count'))
            ->groupBy('status')
            ->get();
    }

    public function getMonthlyRevenue(int $months = 6): Collection
    {
        return DB::table('orders')
            ->whereNull('deleted_at')
            ->where('payment_status', 'paid')
            ->where('created_at', '>=', now()->subMonths($months))
            ->select(
                DB::raw("DATE_FORMAT(created_at, '%Y-%m') as month"),
                DB::raw('SUM(total) as revenue'),
                DB::raw('COUNT(*) as orders'),
            )
            ->groupBy('month')
            ->orderBy('month')
            ->get();
    }
}
