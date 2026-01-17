<?php

declare(strict_types=1);

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Domain\Admin\Services\DashboardService;
use Inertia\Inertia;
use Inertia\Response;

class DashboardController extends Controller
{
    public function __construct(private readonly DashboardService $dashboardService) {}

    public function index(): Response
    {
        $stats          = $this->dashboardService->getStats();
        $recentOrders   = $this->dashboardService->getRecentOrders(10);
        $ordersByStatus = $this->dashboardService->getOrdersByStatus()->pluck('count', 'status');
        $monthlyRevenue = $this->dashboardService->getMonthlyRevenue(6);

        return Inertia::render('Dashboard/Index', [
            'stats'          => $stats,
            'recentOrders'   => $recentOrders,
            'ordersByStatus' => $ordersByStatus,
            'monthlyRevenue' => $monthlyRevenue,
        ]);
    }
}
