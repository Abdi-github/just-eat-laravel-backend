<?php

declare(strict_types=1);

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Domain\Admin\Services\AnalyticsService;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

class AnalyticsAdminController extends Controller
{
    public function __construct(private readonly AnalyticsService $analyticsService) {}

    public function index(Request $request): Response
    {
        $preset = $request->input('preset', 'last_30_days');
        $period = $request->input('period', 'daily');

        [$startDate, $endDate] = $this->analyticsService->getDateRange($preset);

        $stats             = $this->analyticsService->getStats($startDate, $endDate);
        $revenueTimeSeries = $this->analyticsService->getRevenueTimeSeries($startDate, $endDate, $period);
        $ordersByStatus    = $this->analyticsService->getOrdersByStatus($startDate, $endDate)->pluck('count', 'status');
        $topRestaurants    = $this->analyticsService->getTopRestaurants($startDate, $endDate, 10);

        return Inertia::render('Analytics/Index', [
            'stats'             => $stats,
            'revenueTimeSeries' => $revenueTimeSeries,
            'ordersByStatus'    => $ordersByStatus,
            'topRestaurants'    => $topRestaurants,
            'filters'           => $request->only(['preset', 'period']),
        ]);
    }
}
