<?php

namespace App\Domain\Admin\Repositories;

use Illuminate\Support\Collection;

interface DashboardRepositoryInterface
{
    public function getTotalRestaurants(): int;
    public function getTotalUsers(): int;
    public function getTotalOrders(): int;
    public function getTotalRevenue(): float;
    public function getRecentOrders(int $limit = 10): Collection;
    public function getOrdersByStatus(): Collection;
    public function getMonthlyRevenue(int $months = 6): Collection;
}
