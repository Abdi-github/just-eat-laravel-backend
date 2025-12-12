<?php

namespace App\Http\Controllers\Api;

use App\Domain\Restaurant\Services\FavoriteService;
use App\Http\Controllers\Controller;
use App\Http\Requests\Api\Favorite\StoreFavoriteRequest;
use App\Http\Resources\FavoriteResource;
use App\Http\Responses\ApiResponse;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class FavoriteController extends Controller
{
    public function __construct(private readonly FavoriteService $service) {}

    public function index(Request $request): JsonResponse
    {
        $favorites = $this->service->getForUser($request->user()->id);

        return ApiResponse::success(FavoriteResource::collection($favorites));
    }

    public function store(StoreFavoriteRequest $request): JsonResponse
    {
        $data = $request->validated();

        $favorite = $this->service->add($request->user()->id, $data['restaurant_id']);

        if (! $favorite) {
            return ApiResponse::error('Restaurant already in favorites', 409);
        }

        return ApiResponse::created(new FavoriteResource($favorite->load('restaurant')));
    }

    public function destroy(Request $request, int $restaurantId): JsonResponse
    {
        $deleted = $this->service->remove($request->user()->id, $restaurantId);

        if (! $deleted) {
            return ApiResponse::error('Favorite not found', 404);
        }

        return ApiResponse::success(null, 'Removed from favorites');
    }
}
