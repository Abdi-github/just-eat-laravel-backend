<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\Api\Order\StoreOrderRequest;
use App\Http\Requests\Api\Order\UpdateOrderStatusRequest;
use App\Http\Resources\OrderResource;
use App\Http\Responses\ApiResponse;
use App\Domain\Order\Services\OrderService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class OrderController extends Controller
{
    public function __construct(private readonly OrderService $orderService) {}

    public function index(Request $request): JsonResponse
    {
        $user = $request->user();
        $perPage = (int) $request->get('limit', 20);

        if ($user->hasRole(['super_admin', 'admin', 'support_agent'])) {
            $filters   = $request->only(['status', 'restaurant_id', 'user_id']);
            $paginator = $this->orderService->paginateAll($filters, $perPage);
        } else {
            $paginator = $this->orderService->paginateForUser($user->id, $perPage);
        }

        return ApiResponse::paginated($paginator, OrderResource::collection($paginator->items()));
    }

    public function show(Request $request, int $id): JsonResponse
    {
        $order = $this->orderService->findById($id);

        if (! $order) {
            return ApiResponse::error('Order not found', 404);
        }

        $user = $request->user();
        if ($order->user_id !== $user->id && ! $user->hasRole(['super_admin', 'admin', 'support_agent'])) {
            return ApiResponse::error('Forbidden', 403);
        }

        return ApiResponse::success(new OrderResource($order));
    }

    public function store(StoreOrderRequest $request): JsonResponse
    {
        $data = $request->validated();
        $data['user_id'] = $request->user()->id;

        $order = $this->orderService->create($data);

        return ApiResponse::created(new OrderResource($order->load(['restaurant'])), 'Order placed successfully');
    }

    public function updateStatus(UpdateOrderStatusRequest $request, int $id): JsonResponse
    {
        $data = $request->validated();

        $order = $this->orderService->updateStatus($id, $data['status']);

        return ApiResponse::success(new OrderResource($order));
    }

    public function destroy(int $id): JsonResponse
    {
        $this->orderService->delete($id);

        return ApiResponse::success(null, 'Order deleted');
    }
}
