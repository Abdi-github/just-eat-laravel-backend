<?php

namespace App\Http\Controllers\Api;

use App\Domain\Order\Services\AnalyticsApiService;
use App\Http\Controllers\Controller;
use App\Http\Responses\ApiResponse;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class AnalyticsController extends Controller
{
    public function __construct(private readonly AnalyticsApiService $service) {}

    public function dashboard(Request $request): JsonResponse
    {
        if (! $request->user()->hasAnyRole(['admin', 'super_admin', 'restaurant_owner'])) {
            return ApiResponse::error('Forbidden', 403);
        }

        $user = $request->user();
        $isAdmin = $user->hasAnyRole(['admin', 'super_admin']);

        $stats = $this->service->getDashboardStats($isAdmin, $user->id);

        return ApiResponse::success($stats);
    }

    public function revenue(Request $request): JsonResponse
    {
        if (! $request->user()->hasAnyRole(['admin', 'super_admin', 'restaurant_owner'])) {
            return ApiResponse::error('Forbidden', 403);
        }

        $period = $request->query('period', 'monthly');
        $isAdmin = $request->user()->hasAnyRole(['admin', 'super_admin']);

        $data = $this->service->getRevenueByPeriod($period, $isAdmin, $request->user()->id);

        return ApiResponse::success(['period' => $period, 'data' => $data]);
    }

    public function topRestaurants(Request $request): JsonResponse
    {
        if (! $request->user()->hasAnyRole(['admin', 'super_admin'])) {
            return ApiResponse::error('Forbidden', 403);
        }

        $limit = min((int) $request->query('limit', 10), 50);

        $restaurants = $this->service->getTopRestaurants($limit);

        return ApiResponse::success($restaurants);
    }
}
