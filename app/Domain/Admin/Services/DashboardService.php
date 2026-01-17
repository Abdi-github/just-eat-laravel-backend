<?php

namespace App\Domain\Admin\Services;

use App\Domain\Admin\Repositories\DashboardRepositoryInterface;

class DashboardService
{
    public function __construct(private readonly DashboardRepositoryInterface $dashboard) {}

    public function getStats(): array
    {
        return [
            'totalRestaurants' => $this->dashboard->getTotalRestaurants(),
            'totalUsers'       => $this->dashboard->getTotalUsers(),
            'totalOrders'      => $this->dashboard->getTotalOrders(),
            'totalRevenue'     => $this->dashboard->getTotalRevenue(),
        ];
    }

    public function getRecentOrders(int $limit = 10): \Illuminate\Support\Collection
    {
        return $this->dashboard->getRecentOrders($limit);
    }

    public function getOrdersByStatus(): \Illuminate\Support\Collection
    {
        return $this->dashboard->getOrdersByStatus();
    }

    public function getMonthlyRevenue(int $months = 6): \Illuminate\Support\Collection
    {
        return $this->dashboard->getMonthlyRevenue($months);
    }
}
