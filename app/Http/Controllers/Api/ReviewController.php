<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\Api\Review\StoreReviewRequest;
use App\Http\Requests\Api\Review\UpdateReviewRequest;
use App\Http\Resources\ReviewResource;
use App\Http\Responses\ApiResponse;
use App\Domain\Review\Services\ReviewService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class ReviewController extends Controller
{
    public function __construct(private readonly ReviewService $service) {}

    public function index(Request $request): JsonResponse
    {
        $filters  = $request->only(['restaurant_id', 'is_visible']);
        $perPage  = (int) $request->get('limit', 20);
        $paginator = $this->service->paginateAll($filters, $perPage);
        return ApiResponse::paginated($paginator, ReviewResource::collection($paginator->items()));
    }

    public function byRestaurant(Request $request, int $restaurantId): JsonResponse
    {
        $perPage  = (int) $request->get('limit', 20);
        $paginator = $this->service->paginateAll(['restaurant_id' => $restaurantId, 'is_visible' => true], $perPage);
        return ApiResponse::paginated($paginator, ReviewResource::collection($paginator->items()));
    }

    public function store(StoreReviewRequest $request): JsonResponse
    {
        $data = $request->validated();

        $data['user_id'] = $request->user()->id;
        $review = $this->service->create($data);

        return ApiResponse::created(new ReviewResource($review->load('user')));
    }

    public function update(UpdateReviewRequest $request, int $id): JsonResponse
    {
        $review = $this->service->findById($id);

        if (! $review) {
            return ApiResponse::error('Review not found', 404);
        }

        if ($review->user_id !== $request->user()->id) {
            return ApiResponse::error('Forbidden', 403);
        }

        $data = $request->validated();

        $review = $this->service->update($id, $data);
        return ApiResponse::success(new ReviewResource($review));
    }

    public function destroy(Request $request, int $id): JsonResponse
    {
        $review = $this->service->findById($id);

        if (! $review) {
            return ApiResponse::error('Review not found', 404);
        }

        $user = $request->user();
        if ($review->user_id !== $user->id && ! $user->hasRole(['super_admin', 'admin'])) {
            return ApiResponse::error('Forbidden', 403);
        }

        $this->service->delete($id);
        return ApiResponse::success(null, 'Review deleted');
    }
}
