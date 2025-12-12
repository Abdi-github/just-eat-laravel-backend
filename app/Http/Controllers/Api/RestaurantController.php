<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\Api\Restaurant\StoreRestaurantRequest;
use App\Http\Requests\Api\Restaurant\UpdateRestaurantRequest;
use App\Http\Resources\RestaurantResource;
use App\Http\Resources\MenuCategoryResource;
use App\Http\Resources\ReviewResource;
use App\Http\Resources\DeliveryZoneResource;
use App\Http\Resources\OpeningHourResource;
use App\Http\Responses\ApiResponse;
use App\Domain\Restaurant\Services\RestaurantService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class RestaurantController extends Controller
{
    public function __construct(private readonly RestaurantService $restaurantService) {}

    public function index(Request $request): JsonResponse
    {
        $filters  = $request->only(['search', 'cuisine_id', 'city_id', 'canton_id', 'price_range', 'is_featured', 'is_active']);
        $perPage  = (int) $request->get('limit', 20);
        $paginator = $this->restaurantService->paginate($filters, $perPage);

        return ApiResponse::paginated($paginator, RestaurantResource::collection($paginator->items()));
    }

    public function show(int $id): JsonResponse
    {
        $restaurant = $this->restaurantService->findById($id);

        if (! $restaurant) {
            return ApiResponse::error('Restaurant not found', 404);
        }

        return ApiResponse::success(new RestaurantResource($restaurant));
    }

    public function store(StoreRestaurantRequest $request): JsonResponse
    {
        $restaurant = $this->restaurantService->create($request->validated());

        return ApiResponse::created(new RestaurantResource($restaurant));
    }

    public function update(UpdateRestaurantRequest $request, int $id): JsonResponse
    {
        $restaurant = $this->restaurantService->update($id, $request->validated());

        return ApiResponse::success(new RestaurantResource($restaurant));
    }

    public function destroy(int $id): JsonResponse
    {
        $this->restaurantService->delete($id);

        return ApiResponse::success(null, 'Restaurant deleted');
    }

    public function menu(int $id): JsonResponse
    {
        $categories = $this->restaurantService->getMenu($id);

        return ApiResponse::success(MenuCategoryResource::collection($categories));
    }

    public function reviews(Request $request, int $id): JsonResponse
    {
        $perPage = (int) $request->get('limit', 20);
        $paginator = $this->restaurantService->getReviews($id, $perPage);

        return ApiResponse::paginated($paginator, ReviewResource::collection($paginator->items()));
    }

    public function showBySlug(string $slug): JsonResponse
    {
        $restaurant = $this->restaurantService->findBySlug($slug);

        if (! $restaurant) {
            return ApiResponse::error('Restaurant not found', 404);
        }

        return ApiResponse::success(new RestaurantResource($restaurant));
    }

    public function cursor(Request $request): JsonResponse
    {
        $filters = $request->only(['search', 'cuisine_id', 'city_id', 'canton_id', 'price_range', 'is_featured', 'is_active']);
        $limit = (int) $request->get('limit', 12);

        // Decode cursor → page number (base64-encoded page number)
        $cursorStr = $request->get('cursor');
        $page = 1;
        if ($cursorStr) {
            $decoded = base64_decode($cursorStr, true);
            $page = (is_numeric($decoded) && (int) $decoded > 0) ? (int) $decoded : 1;
        }

        $paginator = $this->restaurantService->paginate($filters, $limit, $page);
        $hasMore = $paginator->hasMorePages();
        $nextCursor = $hasMore ? base64_encode((string) ($page + 1)) : null;
        $prevCursor = $page > 1 ? base64_encode((string) ($page - 1)) : null;

        return ApiResponse::success([
            'restaurants' => RestaurantResource::collection($paginator->items()),
            'nextCursor'  => $nextCursor,
            'prevCursor'  => $prevCursor,
            'hasMore'     => $hasMore,
            'total'       => $paginator->total(),
        ]);
    }

    public function deliveryZones(int $id): JsonResponse
    {
        $zones = $this->restaurantService->getDeliveryZones($id);

        return ApiResponse::success(DeliveryZoneResource::collection($zones));
    }

    public function openingHours(int $id): JsonResponse
    {
        $hours = $this->restaurantService->getOpeningHours($id);

        return ApiResponse::success(OpeningHourResource::collection($hours));
    }
}
