<?php

namespace App\Http\Controllers\Api;

use App\Domain\Promotion\Services\PromotionService;
use App\Http\Controllers\Controller;
use App\Http\Requests\Api\Promotion\StorePromotionRequest;
use App\Http\Requests\Api\Promotion\UpdatePromotionRequest;
use App\Http\Resources\PromotionResource;
use App\Http\Responses\ApiResponse;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class PromotionController extends Controller
{
    public function __construct(private readonly PromotionService $service) {}

    public function index(Request $request): JsonResponse
    {
        $filters = $request->only(['restaurant_id', 'active_only']);
        $promotions = $this->service->paginate($filters);

        return ApiResponse::paginated($promotions, PromotionResource::collection($promotions));
    }

    public function show(int $id): JsonResponse
    {
        $promotion = $this->service->findByIdWithRestaurant($id);

        if (! $promotion) {
            return ApiResponse::error('Promotion not found.', 404);
        }

        return ApiResponse::success(new PromotionResource($promotion));
    }

    public function validate(Request $request): JsonResponse
    {
        $request->validate([
            'code'          => ['required', 'string'],
            'order_total'   => ['required', 'numeric', 'min:0'],
            'restaurant_id' => ['nullable', 'integer'],
        ]);

        $result = $this->service->validateCode(
            $request->code,
            (float) $request->order_total,
            $request->restaurant_id ? (int) $request->restaurant_id : null,
        );

        if (isset($result['promotion']) && $result['promotion'] instanceof \App\Domain\Promotion\Models\Promotion) {
            $result['promotion'] = new PromotionResource($result['promotion']);
        }

        return ApiResponse::success($result);
    }

    public function store(StorePromotionRequest $request): JsonResponse
    {
        if (! $request->user()->hasAnyRole(['admin', 'super_admin', 'restaurant_owner'])) {
            return ApiResponse::error('Forbidden', 403);
        }

        $promotion = $this->service->create($request->validated());

        return ApiResponse::created(new PromotionResource($promotion));
    }

    public function update(UpdatePromotionRequest $request, int $id): JsonResponse
    {
        if (! $request->user()->hasAnyRole(['admin', 'super_admin', 'restaurant_owner'])) {
            return ApiResponse::error('Forbidden', 403);
        }

        $promotion = $this->service->findById($id);

        if (! $promotion) {
            return ApiResponse::error('Promotion not found.', 404);
        }

        $updated = $this->service->update($id, $request->validated());

        return ApiResponse::success(new PromotionResource($updated));
    }

    public function destroy(Request $request, int $id): JsonResponse
    {
        if (! $request->user()->hasAnyRole(['admin', 'super_admin'])) {
            return ApiResponse::error('Forbidden', 403);
        }

        $promotion = $this->service->findById($id);

        if (! $promotion) {
            return ApiResponse::error('Promotion not found.', 404);
        }

        $this->service->delete($id);

        return ApiResponse::success(null, 'Promotion deleted.');
    }
}
