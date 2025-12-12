<?php

namespace App\Http\Controllers\Api;

use App\Domain\Restaurant\Services\SearchService;
use App\Http\Controllers\Controller;
use App\Http\Resources\MenuItemResource;
use App\Http\Resources\RestaurantResource;
use App\Http\Responses\ApiResponse;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class SearchController extends Controller
{
    public function __construct(private readonly SearchService $service) {}

    public function restaurants(Request $request): JsonResponse
    {
        $perPage = min((int) $request->input('limit', 20), 100);

        $filters = $request->only([
            'q', 'cuisine_id', 'city_id', 'canton_id',
            'price_range', 'accepts_delivery', 'accepts_pickup',
            'is_featured', 'sort_by', 'sort_dir',
        ]);

        $paginator  = $this->service->searchRestaurants($filters, $perPage);
        $collection = RestaurantResource::collection($paginator);

        return ApiResponse::paginated($paginator, $collection);
    }

    public function menuItems(Request $request, int $restaurantId): JsonResponse
    {
        $restaurant = $this->service->findActiveRestaurant($restaurantId);

        if (! $restaurant) {
            return ApiResponse::error('Restaurant not found.', 404);
        }

        $perPage = min((int) $request->input('limit', 20), 100);
        $filters = $request->only(['q', 'category_id']);

        $paginator  = $this->service->searchMenuItems($restaurantId, $filters, $perPage);
        $collection = MenuItemResource::collection($paginator);

        return ApiResponse::paginated($paginator, $collection);
    }

    public function suggestions(Request $request): JsonResponse
    {
        $q     = $request->string('q')->trim()->toString();
        $limit = min((int) $request->input('limit', 5), 20);

        if ($q === '') {
            return ApiResponse::success(['restaurants' => [], 'cuisines' => []]);
        }

        $suggestions = $this->service->getSuggestions($q, $limit);

        return ApiResponse::success($suggestions);
    }
}
